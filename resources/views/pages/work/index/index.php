<?php

use App\Enums\Role;
use App\Enums\Status;
use App\Models\Assembly;
use App\Models\AssemblyComponent;
use App\Models\BuildIndustry;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public $assemblies = [];
    public $assemblyComponents = [];

    public array $progressInputs = [];

    public function mount()
    {


        $this->loadTasks();
    }

    public function loadTasks()
    {
        $industryId = auth()->user()->industry_id;

        /*
        |--------------------------------------------------------------------------
        | Tareas de Assembly
        |--------------------------------------------------------------------------
        */

        $assemblies = Assembly::with([
            'product',
            'contract',
        ])
            ->where('industry_id', $industryId)
            ->where('status', Status::ACTIVE)
            ->orderBy('contract_id')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Tareas de AssemblyComponent
        |--------------------------------------------------------------------------
        */

        $assemblyComponents = AssemblyComponent::with([
            'component',
            'assembly.product',
            'assembly.contract',
        ])
            ->where('industry_id', $industryId)
            ->where('status', Status::ACTIVE)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Obtener avances acumulados
        |--------------------------------------------------------------------------
        */

        $assemblyIds = $assemblies->pluck('id');

        $componentIds = $assemblyComponents->pluck('id');

        $assemblyProgress = BuildIndustry::query()
            ->where('industry_id', $industryId)
            ->where('buildeable_type', Assembly::class)
            ->whereIn('buildeable_id', $assemblyIds)
            ->selectRaw('buildeable_id, SUM(quantity) as total')
            ->groupBy('buildeable_id')
            ->pluck('total', 'buildeable_id');

        $componentProgress = BuildIndustry::query()
            ->where('industry_id', $industryId)
            ->where('buildeable_type', AssemblyComponent::class)
            ->whereIn('buildeable_id', $componentIds)
            ->selectRaw('buildeable_id, SUM(quantity) as total')
            ->groupBy('buildeable_id')
            ->pluck('total', 'buildeable_id');

        /*
        |--------------------------------------------------------------------------
        | Preparar datos para la vista
        |--------------------------------------------------------------------------
        */

        $this->assemblies = $assemblies->map(function ($assembly) use ($assemblyProgress) {

            $progress = (int) ($assemblyProgress[$assembly->id] ?? 0);

            return [
                'id' => $assembly->id,
                'contract_id' => $assembly->contract_id,
                'delivery_date' => $assembly->contract?->delivery_date,
                'product_name' => $assembly->product?->name ?? 'Producto',
                'quantity' => (int) $assembly->quantity,
                'progress' => $progress,
                'pending' => max(0, (int) $assembly->quantity - $progress),
                'percentage' => $assembly->quantity > 0
                    ? min(100, round(($progress / $assembly->quantity) * 100))
                    : 0,
                'status' => $assembly->status,
                'status_value' => $assembly->status?->value ?? $assembly->status,
            ];
        })->values()->all();

        $this->assemblyComponents = $assemblyComponents->map(function ($item) use ($componentProgress) {

            $progress = (int) ($componentProgress[$item->id] ?? 0);

            return [
                'id' => $item->id,
                'assembly_id' => $item->assembly_id,
                'contract_id' => $item->assembly?->contract_id,
                'delivery_date' => $item->assembly?->contract?->delivery_date,
                'product_name' => $item->assembly?->product?->name ?? 'Producto',
                'component_name' => $item->component?->name ?? 'Componente',
                'quantity' => (int) $item->quantity,
                'progress' => $progress,
                'pending' => max(0, (int) $item->quantity - $progress),
                'percentage' => $item->quantity > 0
                    ? min(100, round(($progress / $item->quantity) * 100))
                    : 0,
                'status' => $item->status,
                'status_value' => $item->status?->value ?? $item->status,
            ];
        })->values()->all();
    }

    public function registerProgress(string $type, int $id)
    {
        abort_unless(auth()->user()->role === Role::WORKER, 403);

        $industryId = auth()->user()->industry_id;

        $key = "{$type}-{$id}";

        $quantity = (int) ($this->progressInputs[$key] ?? 0);

        if ($quantity <= 0) {
            $this->addError(
                "progressInputs.{$key}",
                'Ingresa una cantidad válida.'
            );

            return;
        }

        DB::transaction(function () use (
            $type,
            $id,
            $quantity,
            $industryId,
            $key
        ) {

            /*
            |--------------------------------------------------------------------------
            | Obtener la tarea verificando la fábrica
            |--------------------------------------------------------------------------
            */

            if ($type === 'assembly') {

                $task = Assembly::where('id', $id)
                    ->where('industry_id', $industryId)
                    ->firstOrFail();

                $model = Assembly::class;

            } elseif ($type === 'component') {

                $task = AssemblyComponent::where('id', $id)
                    ->where('industry_id', $industryId)
                    ->firstOrFail();

                $model = AssemblyComponent::class;

            } else {
                abort(404);
            }

            /*
            |--------------------------------------------------------------------------
            | No permitir avance sobre una tarea finalizada
            |--------------------------------------------------------------------------
            */

            if ($task->status === Status::FINISH) {
                $this->addError(
                    "progressInputs.{$key}",
                    'Esta tarea ya está finalizada.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Calcular avance acumulado
            |--------------------------------------------------------------------------
            */

            $currentProgress = BuildIndustry::query()
                ->where('industry_id', $industryId)
                ->where('buildeable_type', $model)
                ->where('buildeable_id', $task->id)
                ->sum('quantity');

            $remaining = max(0, $task->quantity - $currentProgress);

            /*
            |--------------------------------------------------------------------------
            | No permitir superar la cantidad requerida
            |--------------------------------------------------------------------------
            */

            if ($quantity > $remaining) {
                $this->addError(
                    "progressInputs.{$key}",
                    "Solo quedan {$remaining} unidades pendientes."
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Registrar avance
            |--------------------------------------------------------------------------
            */

            BuildIndustry::create([
                'industry_id' => $industryId,
                'quantity' => $quantity,
                'buildeable_type' => $model,
                'buildeable_id' => $task->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Limpiar input
            |--------------------------------------------------------------------------
            */

            $this->progressInputs[$key] = '';
        });

        $this->resetErrorBag();

        $this->loadTasks();
    }

    public function finishTask(string $type, int $id)
    {
        abort_unless(auth()->user()->role === Role::WORKER, 403);

        $industryId = auth()->user()->industry_id;

        DB::transaction(function () use ($type, $id, $industryId) {

            if ($type === 'assembly') {

                $task = Assembly::where('id', $id)
                    ->where('industry_id', $industryId)
                    ->firstOrFail();

                $model = Assembly::class;

            } elseif ($type === 'component') {

                $task = AssemblyComponent::where('id', $id)
                    ->where('industry_id', $industryId)
                    ->firstOrFail();

                $model = AssemblyComponent::class;

            } else {
                abort(404);
            }

            /*
            |--------------------------------------------------------------------------
            | Si ya está finalizada, no hacer nada
            |--------------------------------------------------------------------------
            */

            if ($task->status === Status::FINISH) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar que realmente completó la cantidad
            |--------------------------------------------------------------------------
            */

            $progress = BuildIndustry::query()
                ->where('industry_id', $industryId)
                ->where('buildeable_type', $model)
                ->where('buildeable_id', $task->id)
                ->sum('quantity');

            if ($progress < $task->quantity) {
                $this->addError(
                    'finish',
                    'La tarea todavía no está completada.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Marcar tarea como finalizada
            |--------------------------------------------------------------------------
            */

            $task->status = Status::FINISH;
            $task->save();
        });

        $this->loadTasks();
    }

    public function render()
    {
        return $this->view();
    }
};
