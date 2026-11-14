<?php

namespace App\Livewire;

use App\Enums\Status;
use App\Models\ActivityLog;
use App\Models\Assembly;
use App\Models\AssemblyComponent;
use App\Models\BuildIndustry;
use App\Models\Industry;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filtros
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $industryFilter = '';

    // Edición
    public ?int $editingId = null;

    public $editQuantity = '';
    public $editIndustryId = '';
    public $editStatus = '';

    // Historial
    public ?int $historyId = null;

    public bool $showDeleted = false;

    public function mount(): void
    {
        // Por defecto mostrar la producción de hoy
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedIndustryFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->industryFilter = '';

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Edición
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): void
    {
        $build = BuildIndustry::with('buildeable')
            ->findOrFail($id);

        $target = $build->buildeable;

        if (!$target instanceof Assembly && !$target instanceof AssemblyComponent) {
            abort(422, 'Tipo de fabricación no permitido.');
        }

        $this->editingId = $build->id;

        $this->editQuantity = $build->quantity;
        $this->editIndustryId = $build->industry_id;
        $this->editStatus = $this->getStatusValue($target->status);

        $this->resetValidation();

        // Cerramos el historial si estaba abierto
        $this->historyId = null;
    }

    public function cancelEdit(): void
    {
        $this->reset([
            'editingId',
            'editQuantity',
            'editIndustryId',
            'editStatus',
        ]);

        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'editQuantity' => 'required|integer|min:0',
            'editIndustryId' => 'required|exists:industries,id',
            'editStatus' => 'required|in:1,3',
        ], [
            'editQuantity.required' => 'La cantidad es obligatoria.',
            'editQuantity.integer' => 'La cantidad debe ser un número entero.',
            'editQuantity.min' => 'La cantidad no puede ser negativa.',
            'editIndustryId.required' => 'Selecciona una industria.',
            'editIndustryId.exists' => 'La industria seleccionada no existe.',
            'editStatus.required' => 'Selecciona un estado.',
            'editStatus.in' => 'El estado seleccionado no es válido.',
        ]);

        DB::transaction(function () {

            $build = BuildIndustry::with('industry')
                ->lockForUpdate()
                ->findOrFail($this->editingId);

            // Recuperamos y bloqueamos el modelo polimórfico
            $target = $build->buildeable()
                ->lockForUpdate()
                ->firstOrFail();

            if (!$target instanceof Assembly && !$target instanceof AssemblyComponent) {
                abort(422, 'Tipo de fabricación no permitido.');
            }

            $newIndustry = Industry::findOrFail($this->editIndustryId);

            $oldStatus = $this->getStatusValue($target->status);
            $newStatus = (int) $this->editStatus;

            $oldValues = [];
            $newValues = [];

            /*
            |--------------------------------------------------------------------------
            | Cantidad fabricada
            |--------------------------------------------------------------------------
            */

            if ((int) $build->quantity !== (int) $this->editQuantity) {

                $oldValues['quantity'] = (int) $build->quantity;
                $newValues['quantity'] = (int) $this->editQuantity;

                $build->quantity = (int) $this->editQuantity;
            }

            /*
            |--------------------------------------------------------------------------
            | Industria
            |--------------------------------------------------------------------------
            */

            if ((int) $build->industry_id !== (int) $this->editIndustryId) {

                $oldValues['industry_id'] = $build->industry_id;
                $oldValues['industry_name'] = $build->industry?->name;

                $newValues['industry_id'] = $newIndustry->id;
                $newValues['industry_name'] = $newIndustry->name;

                $build->industry_id = $newIndustry->id;
            }

            /*
            |--------------------------------------------------------------------------
            | Estado del ensamblaje o componente
            |--------------------------------------------------------------------------
            */

            if ($oldStatus !== $newStatus) {

                $oldEnum = Status::from($oldStatus);
                $newEnum = Status::from($newStatus);

                $oldValues['status'] = $oldStatus;
                $oldValues['status_label'] = $oldEnum->label();

                $newValues['status'] = $newStatus;
                $newValues['status_label'] = $newEnum->label();

                // Assembly tiene cast a Status.
                // AssemblyComponent almacena el entero.
                $target->status = $newStatus;
                $target->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Guardar registro de fabricación
            |--------------------------------------------------------------------------
            */

            $build->save();

            /*
            |--------------------------------------------------------------------------
            | Registrar auditoría
            |--------------------------------------------------------------------------
            */

            if (!empty($oldValues) || !empty($newValues)) {

                ActivityLog::record(
                    $build,
                    'updated',
                    $oldValues,
                    $newValues,
                    'Corrección de registro de fabricación'
                );
            }
        });

        $this->cancelEdit();

        session()->flash(
            'success',
            'Registro de fabricación actualizado correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Historial de modificaciones
    |--------------------------------------------------------------------------
    */

    public function showHistory(int $id): void
    {
        BuildIndustry::withTrashed()->findOrFail($id);

        $this->historyId = $this->historyId === $id
            ? null
            : $id;

        $this->editingId = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos auxiliares
    |--------------------------------------------------------------------------
    */

    private function getStatusValue($status): int
    {
        if ($status instanceof Status) {
            return $status->value;
        }

        return (int) $status;
    }

    public function buildType(BuildIndustry $build): string
    {
        return match (true) {
            $build->buildeable instanceof Assembly => 'Ensamblaje',
            $build->buildeable instanceof AssemblyComponent => 'Componente',
            default => 'Desconocido',
        };
    }

    public function buildName(BuildIndustry $build): string
    {
        $target = $build->buildeable;

        if ($target instanceof Assembly) {
            return $target->product?->name ?? 'Producto sin nombre';
        }

        if ($target instanceof AssemblyComponent) {
            return $target->component?->name ?? 'Componente sin nombre';
        }

        return 'Registro desconocido';
    }

    public function buildContext(BuildIndustry $build): string
    {
        $target = $build->buildeable;

        if ($target instanceof Assembly) {
            return 'Ensamblaje #' . $target->id;
        }

        if ($target instanceof AssemblyComponent) {
            $productName = $target->assembly?->product?->name;

            return 'Componente del ensamblaje #' .
                $target->assembly_id .
                ($productName ? ' - ' . $productName : '');
        }

        return '-';
    }

    public function statusLabel(BuildIndustry $build): string
    {
        $target = $build->buildeable;

        if (!$target || !isset($target->status)) {
            return 'Desconocido';
        }

        $status = Status::tryFrom(
            $this->getStatusValue($target->status)
        );

        return $status?->label() ?? 'Desconocido';
    }

    public function statusBadgeClass(BuildIndustry $build): string
    {
        $target = $build->buildeable;

        if (!$target || !isset($target->status)) {
            return 'badge-secondary';
        }

        $status = Status::tryFrom(
            $this->getStatusValue($target->status)
        );

        return $status?->badgeClass() ?? 'badge-secondary';
    }

    public function toggleDeleted(): void
    {
        $this->showDeleted = !$this->showDeleted;

        $this->resetPage();
        $this->cancelEdit();

        $this->historyId = null;
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {

            $build = BuildIndustry::with('industry')
                ->lockForUpdate()
                ->findOrFail($id);

            $target = $build->buildeable;

            if (
                !$target instanceof Assembly &&
                    !$target instanceof AssemblyComponent
            ) {
                abort(422, 'Tipo de fabricación no permitido.');
            }

            // Identificar la pieza
            $name = $this->buildName($build);
            $type = $this->buildType($build);

            // Obtener estado actual
            $statusValue = $this->getStatusValue($target->status);

            $status = Status::tryFrom($statusValue);

            // Guardar fotografía de los datos antes de eliminar
            $oldValues = [
                'quantity' => (int) $build->quantity,

                'industry_id' => $build->industry_id,
                'industry_name' => $build->industry?->name,

                'status' => $statusValue,
                'status_label' => $status?->label() ?? 'Desconocido',

                'build_type' => $type,
                'piece_name' => $name,

                'buildeable_type' => $build->buildeable_type,
                'buildeable_id' => $build->buildeable_id,

                'created_at' => $build->created_at?->toDateTimeString(),
            ];

            // Registrar la eliminación ANTES del soft delete
            ActivityLog::record(
                $build,
                'deleted',
                $oldValues,
                [],
                'Se eliminó una fabricación del historial'
            );

            // Eliminación lógica
            $build->delete();
        });

        $this->cancelEdit();

        // Abrir automáticamente la auditoría de la fabricación eliminada
        $this->historyId = $id;

        session()->flash(
            'success',
            'Fabricación eliminada correctamente. La auditoría se ha conservado.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $query = BuildIndustry::query()
            ->with([
                'industry',
                'buildeable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Assembly::class => [
                            'product',
                            'contract',
                        ],

                        AssemblyComponent::class => [
                            'component',
                            'assembly.product',
                            'assembly.contract',
                        ],
                    ]);
                },
            ]);

        if ($this->showDeleted) {
            $query->onlyTrashed();
        }

        // Fecha inicial
        if ($this->dateFrom) {
            $query->whereDate(
                'created_at',
                '>=',
                $this->dateFrom
            );
        }

        // Fecha final
        if ($this->dateTo) {
            $query->whereDate(
                'created_at',
                '<=',
                $this->dateTo
            );
        }

        // Filtro por industria
        if ($this->industryFilter) {
            $query->where(
                'industry_id',
                $this->industryFilter
            );
        }

        $builds = $query
            ->latest('created_at')
            ->paginate(25);

        // Historial del registro seleccionado
        $historyBuild = $this->historyId
        ? BuildIndustry::withTrashed()->find($this->historyId)
        : null;

        $logs = $historyBuild
        ? $historyBuild->activityLogs()
            ->with('user')
            ->latest()
            ->get()
        : collect();

        return $this->view([
            'builds' => $builds,
            'industries' => Industry::orderBy('name')->get(),
            'logs' => $logs,
            'statuses' => [
                Status::ACTIVE,
                Status::FINISH,
            ],
        ]);
    }
};
