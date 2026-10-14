
<?php

use App\Enums\Role;
use App\Enums\Status;
use App\Models\Assembly;
use App\Models\AssemblyComponent;
use App\Models\BuildIndustry;
use App\Models\Contract;
use App\Models\Industry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public array $assemblies = [];
    public array $assemblyComponents = [];
    public array $assemblyGroups = [];
    public array $receptionContracts = [];
    public array $progressInputs = [];

    public string $search = '';

    public function mount(): void
    {
        $this->loadTasks();
    }

    private function roleValue(): int
    {
        $role = auth()->user()->role;

        return (int) (
            $role instanceof \BackedEnum
                ? $role->value
                : $role
        );
    }

    private function isManagement(): bool
    {
        return in_array($this->roleValue(), [
            Role::ADMIN->value,
            Role::PRIVILEGED->value,
            Role::SUPERVISOR->value,
        ], true);
    }

    private function isWorker(): bool
    {
        return $this->roleValue() === Role::WORKER->value;
    }

    private function isReception(): bool
    {
        return $this->roleValue() === Role::RECEPT->value;
    }

    private function statusValue($status): ?int
    {
        if ($status instanceof \BackedEnum) {
            return (int) $status->value;
        }

        return is_numeric($status) ? (int) $status : null;
    }

    private function isFinished($status): bool
    {
        return $this->statusValue($status) === Status::FINISH->value;
    }

    private function tagsToArray($tags): array
    {
        return $tags
            ? $tags->map(fn ($tag) => [
                'name' => $tag->name,
                'value' => $tag->value,
            ])->values()->all()
            : [];
    }

    private function progressFor(
        string $model,
        int $id,
        int $industryId
    ): int {
        return (int) BuildIndustry::query()
            ->where('buildeable_type', $model)
            ->where('buildeable_id', $id)
            ->where('industry_id', $industryId)
            ->sum('quantity');
    }

    public function updatedSearch(): void
    {
        if ($this->isManagement()) {
            $this->buildAssemblyGroups();
        }
    }

    public function loadTasks(): void
    {
        $industryId = auth()->user()->industry_id;

        $this->assemblies = [];
        $this->assemblyComponents = [];
        $this->assemblyGroups = [];
        $this->receptionContracts = [];

        if ($this->isReception()) {
            $this->loadReceptionContracts();
            return;
        }

        abort_unless(
            $this->isManagement() || $this->isWorker(),
            403
        );

        /*
         * Gestión y trabajadores solo ven ensamblajes activos.
         * Los procesos también deben estar activos y pertenecer
         * a un ensamblaje activo.
         */
        $assemblyQuery = Assembly::query()
            ->where('status', Status::ACTIVE)
            ->with([
                'product.tagProducts',
                'contract',
            ]);

        $componentQuery = AssemblyComponent::query()
            ->where('status', Status::ACTIVE)
            ->whereHas('assembly', function ($query) {
                $query->where('status', Status::ACTIVE);
            })
            ->with([
                'component.tags',
                'assembly.product.tagProducts',
                'assembly.contract',
            ]);

        if ($this->isWorker()) {
            $assemblyQuery->where('industry_id', $industryId);
            $componentQuery->where('industry_id', $industryId);
        }

        $assemblyModels = $assemblyQuery->get();
        $componentModels = $componentQuery->get();

        $industryIds = $assemblyModels->pluck('industry_id')
            ->merge($componentModels->pluck('industry_id'))
            ->filter()
            ->unique();

        $industries = Industry::query()
            ->whereIn('id', $industryIds)
            ->get()
            ->keyBy('id');

        $assemblyProgress = [];
        foreach ($assemblyModels as $assembly) {
            $assemblyProgress[$assembly->id] = $this->progressFor(
                Assembly::class,
                (int) $assembly->id,
                (int) $assembly->industry_id
            );
        }

        $componentProgress = [];
        foreach ($componentModels as $component) {
            $componentProgress[$component->id] = $this->progressFor(
                AssemblyComponent::class,
                (int) $component->id,
                (int) $component->industry_id
            );
        }

        $this->assemblies = $assemblyModels
            ->sortBy(fn ($item) => [
                $item->contract?->priority ?? 5,
                $item->contract?->delivery_date ?? '9999-12-31',
                $item->contract_id ?? 0,
                $item->id,
            ])
            ->map(function ($item) use ($assemblyProgress, $industries) {
                $progress = (int) ($assemblyProgress[$item->id] ?? 0);
                $quantity = (int) $item->quantity;

                return [
                    'id' => (int) $item->id,
                    'industry_id' => (int) $item->industry_id,
                    'industry_name' => $industries[$item->industry_id]?->name
                        ?? 'Área',

                    'contract_id' => $item->contract_id,
                    'external_id' => $item->contract?->external_id,
                    'delivery_date' => $item->contract?->delivery_date,
                    'priority' => (int) ($item->contract?->priority ?? 5),

                    'product_name' => $item->product?->name ?? 'Producto',
                    'product_description' => $item->product?->description ?? '',
                    'product_tags' => $this->tagsToArray(
                        $item->product?->tagProducts
                    ),

                    'quantity' => $quantity,
                    'progress' => $progress,
                    'pending' => max(0, $quantity - $progress),
                    'percentage' => $quantity > 0
                        ? min(100, (int) round($progress / $quantity * 100))
                        : 0,

                    'finished' => $this->isFinished($item->status),
                ];
            })
            ->values()
            ->all();

        $this->assemblyComponents = $componentModels
            ->sortBy(fn ($item) => [
                $item->assembly?->contract?->priority ?? 5,
                $item->assembly?->contract?->delivery_date ?? '9999-12-31',
                $item->assembly_id ?? 0,
                $item->id,
            ])
            ->map(function ($item) use ($componentProgress, $industries) {
                $industry = $industries[$item->industry_id] ?? null;
                $component = $item->component;
                $product = $item->assembly?->product;

                $progress = (int) ($componentProgress[$item->id] ?? 0);
                $quantity = (int) $item->quantity;

                $showProcessTags = $industry
                    && $component
                    && $component->type_factory_id !== null
                    && (int) $component->type_factory_id
                        === (int) $industry->type_factory_id;

                return [
                    'id' => (int) $item->id,
                    'assembly_id' => (int) $item->assembly_id,
                    'industry_id' => (int) $item->industry_id,
                    'industry_name' => $industry?->name ?? 'Área',

                    'contract_id' => $item->assembly?->contract_id,
                    'external_id' => $item->assembly?->contract?->external_id,
                    'delivery_date' => $item->assembly?->contract?->delivery_date,
                    'priority' => (int) ($item->assembly?->contract?->priority ?? 5),

                    'product_name' => $product?->name ?? 'Producto',
                    'product_tags' => $this->tagsToArray(
                        $product?->tagProducts
                    ),

                    'component_name' => $component?->name ?? 'Componente',
                    'component_description' => $component?->description ?? '',
                    'component_tags' => $this->tagsToArray(
                        $component?->tagProducts
                    ),

                    'process_tags' => $showProcessTags
                        ? $this->tagsToArray($component->tags)
                        : [],

                    'quantity' => $quantity,
                    'progress' => $progress,
                    'pending' => max(0, $quantity - $progress),
                    'percentage' => $quantity > 0
                        ? min(100, (int) round($progress / $quantity * 100))
                        : 0,

                    'finished' => $this->isFinished($item->status),
                ];
            })
            ->values()
            ->all();

        if ($this->isManagement()) {
            $this->buildAssemblyGroups();
        }
    }

    private function buildAssemblyGroups(): void
    {
        $componentsByAssembly = collect($this->assemblyComponents)
            ->groupBy('assembly_id');

        $search = mb_strtolower(trim($this->search));

        $groups = collect($this->assemblies)->map(function ($assembly) use (
            $componentsByAssembly
        ) {
            $processes = $componentsByAssembly->get(
                $assembly['id'],
                collect()
            )->values();

            return [
                ...$assembly,
                'processes' => $processes->all(),
                'process_count' => $processes->count(),
            ];
        });

        if ($search !== '') {
            $groups = $groups->filter(function ($assembly) use ($search) {
                foreach ([
                    $assembly['id'],
                    $assembly['contract_id'] ?? '',
                    $assembly['external_id'] ?? '',
                    $assembly['product_name'],
                    $assembly['product_description'],
                    $assembly['industry_name'],
                ] as $field) {
                    if (str_contains(
                        mb_strtolower((string) $field),
                        $search
                    )) {
                        return true;
                    }
                }

                foreach ($assembly['processes'] as $process) {
                    foreach ([
                        $process['component_name'],
                        $process['component_description'],
                        $process['industry_name'],
                    ] as $field) {
                        if (str_contains(
                            mb_strtolower((string) $field),
                            $search
                        )) {
                            return true;
                        }
                    }
                }

                return false;
            });
        }

        $this->assemblyGroups = $groups->values()->all();
    }

    private function loadReceptionContracts(): void
    {
        $industryId = auth()->user()->industry_id;

        if (!$industryId) {
            return;
        }

        $contracts = Contract::query()
            ->where('industry_id', $industryId)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', Status::DELIVERED->value);
            })
            ->with('industry')
            ->orderByRaw('priority IS NULL')
            ->orderBy('priority')
            ->orderByRaw('delivery_date IS NULL')
            ->orderBy('delivery_date')
            ->get();

        $assemblies = Assembly::query()
            ->whereIn('contract_id', $contracts->pluck('id'))
            ->with('product')
            ->get();

        $progress = [];
        foreach ($assemblies as $assembly) {
            $progress[$assembly->id] = $this->progressFor(
                Assembly::class,
                (int) $assembly->id,
                (int) $assembly->industry_id
            );
        }

        $assembliesByContract = $assemblies->groupBy('contract_id');

        $this->receptionContracts = $contracts->where('status','!=',Status::DELIVERED)->map(
            function ($contract) use ($assembliesByContract, $progress) {
                $items = $assembliesByContract->get(
                    $contract->id,
                    collect()
                );

                $required = (int) $items->sum('quantity');

                $completed = (int) $items->sum(
                    fn ($item) => min(
                        (int) $item->quantity,
                        (int) ($progress[$item->id] ?? 0)
                    )
                );

                return [
                    'id' => $contract->id,
                    'external_id' => $contract->external_id,
                    'industry_name' => $contract->industry?->name ?? 'Área',
                    'delivery_date' => $contract->delivery_date,
                    'priority' => (int) ($contract->priority ?? 5),
                    'quantity' => $required,
                    'progress' => $completed,
                    'pending' => max(0, $required - $completed),
                    'percentage' => $required > 0
                        ? min(100, (int) round($completed / $required * 100))
                        : 0,

                    'products' => $items->map(function ($item) use ($progress) {
                        $quantity = (int) $item->quantity;
                        $done = (int) ($progress[$item->id] ?? 0);

                        return [
                            'name' => $item->product?->name ?? 'Producto',
                            'quantity' => $quantity,
                            'progress' => $done,
                            'pending' => max(0, $quantity - $done),
                            'percentage' => $quantity > 0
                                ? min(100, (int) round($done / $quantity * 100))
                                : 0,
                        ];
                    })->values()->all(),
                ];
            }
        )->values()->all();
    }

    public function registerProgress(string $type, int $id): void
    {
        abort_unless($this->isManagement(), 403);

        $key = "{$type}-{$id}";

        $validated = $this->validate([
            "progressInputs.{$key}" => ['required', 'integer', 'min:1'],
        ], [
            "progressInputs.{$key}.required" => 'Ingresa una cantidad.',
            "progressInputs.{$key}.integer" => 'La cantidad debe ser un entero.',
            "progressInputs.{$key}.min" => 'La cantidad debe ser mayor que cero.',
        ]);

        $quantity = (int) $validated['progressInputs'][$key];

        DB::transaction(function () use ($type, $id, $quantity, $key) {
            if ($type === 'assembly') {
                $task = Assembly::query()
                    ->where('status', Status::ACTIVE)
                    ->lockForUpdate()
                    ->findOrFail($id);

                $model = Assembly::class;
            } elseif ($type === 'component') {
                $task = AssemblyComponent::query()
                    ->where('status', Status::ACTIVE)
                    ->whereHas('assembly', function ($query) {
                        $query->where('status', Status::ACTIVE);
                    })
                    ->lockForUpdate()
                    ->findOrFail($id);

                $model = AssemblyComponent::class;
            } else {
                abort(404);
            }

            $current = $this->progressFor(
                $model,
                (int) $task->id,
                (int) $task->industry_id
            );

            $remaining = max(0, (int) $task->quantity - $current);

            if ($quantity > $remaining) {
                $this->addError(
                    "progressInputs.{$key}",
                    "Solo quedan {$remaining} unidades pendientes."
                );
                return;
            }

            BuildIndustry::create([
                'industry_id' => $task->industry_id,
                'quantity' => $quantity,
                'buildeable_type' => $model,
                'buildeable_id' => $task->id,
            ]);
        });

        unset($this->progressInputs[$key]);

        // Volver a consultar para mostrar el avance actualizado.
        $this->loadTasks();
    }

    public function finishTask(string $type, int $id): void
    {
        abort_unless($this->isManagement(), 403);

        DB::transaction(function () use ($type, $id) {
            if ($type === 'assembly') {
                $task = Assembly::query()
                    ->where('status', Status::ACTIVE)
                    ->lockForUpdate()
                    ->findOrFail($id);

                $model = Assembly::class;
            } elseif ($type === 'component') {
                $task = AssemblyComponent::query()
                    ->where('status', Status::ACTIVE)
                    ->whereHas('assembly', function ($query) {
                        $query->where('status', Status::ACTIVE);
                    })
                    ->lockForUpdate()
                    ->findOrFail($id);

                $model = AssemblyComponent::class;
            } else {
                abort(404);
            }

            $progress = $this->progressFor(
                $model,
                (int) $task->id,
                (int) $task->industry_id
            );

            if ($progress < (int) $task->quantity) {
                $this->addError(
                    'finish',
                    'La tarea todavía no está completada.'
                );
                return;
            }

            $task->status = Status::FINISH;
            $task->save();
        });

        // La consulta posterior excluye la tarea finalizada.
        $this->loadTasks();
    }

    public function render()
    {
        return $this->view();
    }
};
