<div>
    {{-- ====================================================== --}}
    {{-- ENCABEZADO --}}
    {{-- ====================================================== --}}

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Historial de fabricación
            </h3>

            <div class="card-tools">
                <span class="badge badge-primary">
                    {{ $builds->total() }} registros
                </span>
            </div>
        </div>

        <div class="card-body">

            {{-- ====================================================== --}}
            {{-- MENSAJES --}}
            {{-- ====================================================== --}}

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible">
                    <i class="fas fa-check-circle mr-1"></i>
                    {{ session('success') }}

                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            {{-- ====================================================== --}}
            {{-- FILTROS --}}
            {{-- ====================================================== --}}

            <div class="card card-light">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-2">
                            <label>Desde</label>

                            <input type="date" class="form-control" wire:model.live="dateFrom">
                        </div>

                        <div class="col-md-2">
                            <label>Hasta</label>

                            <input type="date" class="form-control" wire:model.live="dateTo">
                        </div>

                        <div class="col-md-3">
                            <label>Area</label>

                            <select class="form-control" wire:model.live="industryFilter">
                                <option value="">Todas las Areas</option>

                                @foreach ($industries as $industry)
                                    <option value="{{ $industry->id }}">
                                        {{ $industry->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-secondary btn-block" wire:click="clearFilters">
                                <i class="fas fa-eraser mr-1"></i>
                                Limpiar
                            </button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button"
                                class="btn {{ $showDeleted ? 'btn-danger' : 'btn-outline-danger' }} btn-block"
                                wire:click="toggleDeleted">
                                <i class="fas fa-trash-alt mr-1"></i>

                                {{ $showDeleted ? 'Ver activas' : 'Ver eliminadas' }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- FORMULARIO DE EDICIÓN --}}
            {{-- ====================================================== --}}

            @if ($editingId)
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit mr-1"></i>
                            Corregir registro de fabricación #{{ $editingId }}
                        </h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" wire:click="cancelEdit">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <form wire:submit="save">

                            <div class="row">

                                <div class="col-md-4">
                                    <label>Cantidad fabricada</label>

                                    <input type="number" min="0"
                                        class="form-control @error('editQuantity') is-invalid @enderror"
                                        wire:model="editQuantity">

                                    @error('editQuantity')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label>Area</label>

                                    <select class="form-control @error('editIndustryId') is-invalid @enderror" disabled
                                        wire:model="editIndustryId">
                                        <option value="">Seleccionar industria</option>

                                        @foreach ($industries as $industry)
                                            <option value="{{ $industry->id }}">
                                                {{ $industry->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('editIndustryId')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label>Estado</label>

                                    <select class="form-control @error('editStatus') is-invalid @enderror"
                                        wire:model="editStatus">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}">
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('editStatus')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                            </div>

                            <div class="mt-3 text-right">

                                <button type="button" class="btn btn-secondary" wire:click="cancelEdit">
                                    Cancelar
                                </button>

                                <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="save">
                                        <i class="fas fa-save mr-1"></i>
                                        Guardar corrección
                                    </span>

                                    <span wire:loading wire:target="save">
                                        <i class="fas fa-spinner fa-spin mr-1"></i>
                                        Guardando...
                                    </span>
                                </button>

                            </div>

                        </form>
                    </div>
                </div>
            @endif

            {{-- ====================================================== --}}
            {{-- HISTORIAL DE CAMBIOS --}}
            {{-- ====================================================== --}}

            @if ($historyId)
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-list mr-1"></i>
                            Auditoría del registro #{{ $historyId }}
                        </h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" wire:click="showHistory({{ $historyId }})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">

                        @forelse ($logs as $log)
                            <div class="border rounded p-3 mb-3">

                                <div class="d-flex justify-content-between flex-wrap">
                                    <div>
                                        <strong>
                                            <i class="fas fa-user-circle mr-1"></i>

                                            {{ $log->user?->name ?? 'Usuario eliminado' }}
                                        </strong>

                                        @if ($log->event === 'deleted')
                                            <span class="badge badge-danger ml-2">
                                                Fabricación eliminada
                                            </span>
                                        @elseif ($log->event === 'updated')
                                            <span class="badge badge-warning ml-2">
                                                Modificación
                                            </span>
                                        @else
                                            <span class="badge badge-secondary ml-2">
                                                {{ ucfirst($log->event) }}
                                            </span>
                                        @endif
                                    </div>

                                    <small class="text-muted">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </small>
                                </div>

                                @if ($log->description)
                                    <p class="text-muted mb-2 mt-2">
                                        {{ $log->description }}
                                    </p>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">

                                        <thead>
                                            <tr>
                                                <th>Campo</th>
                                                <th>Valor anterior</th>
                                                <th>Valor nuevo</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php
                                                $fieldLabels = [
                                                    'quantity' => 'Cantidad fabricada',
                                                    'industry_name' => 'Area',
                                                    'status_label' => 'Estado',
                                                    'build_type' => 'Tipo de fabricación',
                                                    'piece_name' => 'Pieza',
                                                ];
                                            @endphp

                                            @foreach ($log->old_values ?? [] as $field => $oldValue)
                                                @continue(!isset($fieldLabels[$field]))

                                                <tr>
                                                    <td>
                                                        {{ $fieldLabels[$field] }}
                                                    </td>

                                                    <td>
                                                        <span class="text-danger">
                                                            {{ $oldValue }}
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <span class="text-success">
                                                            {{ $log->new_values[$field] ?? '-' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                            </div>
                        @empty
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Este registro todavía no tiene modificaciones.
                            </div>
                        @endforelse

                    </div>
                </div>
            @endif

            {{-- ====================================================== --}}
            {{-- TABLA DE FABRICACIÓN AGRUPADA POR DÍA --}}
            {{-- ====================================================== --}}

            @forelse ($builds->getCollection()->groupBy(fn ($item) => $item->created_at->format('Y-m-d')) as $day => $items)

                <div class="card card-outline card-secondary">

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day mr-1"></i>

                            {{ \Carbon\Carbon::parse($day)->locale('es')->translatedFormat('l d \d\e F \d\e Y') }}
                        </h3>

                        <div class="card-tools">
                            <span class="badge badge-info">
                                {{ $items->count() }} registros
                            </span>

                            <span class="badge badge-primary">
                                {{ $items->sum('quantity') }} piezas
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">

                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Pieza / Producto</th>
                                        <th>Contexto</th>
                                        <th>Area</th>
                                        <th class="text-center">Cantidad</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($items as $build)
                                        <tr wire:key="build-{{ $build->id }}">

                                            <td>
                                                <strong>
                                                    {{ $build->created_at->format('H:i') }}
                                                </strong>
                                            </td>

                                            <td>
                                                @if ($build->buildeable instanceof \App\Models\Assembly)
                                                    <span class="badge badge-primary">
                                                        Ensamblaje
                                                    </span>
                                                @elseif ($build->buildeable instanceof \App\Models\AssemblyComponent)
                                                    <span class="badge badge-info">
                                                        Componente
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        Desconocido
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                <strong>
                                                    {{ $this->buildName($build) }}
                                                </strong>
                                            </td>

                                            <td>
                                                <small class="text-muted">
                                                    {{ $this->buildContext($build) }}
                                                </small>
                                            </td>

                                            <td>
                                                {{ $build->industry?->name ?? '-' }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge badge-dark">
                                                    {{ number_format($build->quantity) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="badge {{ $this->statusBadgeClass($build) }}">
                                                    {{ $this->statusLabel($build) }}
                                                </span>
                                            </td>

                                            <td class="text-center">

                                                @if (!$build->trashed())
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        title="Editar fabricación"
                                                        wire:click="edit({{ $build->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        title="Eliminar fabricación"
                                                        wire:click="delete({{ $build->id }})"
                                                        wire:confirm="¿Estás seguro de eliminar esta fabricación? Se conservará su historial de auditoría.">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="badge badge-danger mr-1">
                                                        Eliminada
                                                    </span>
                                                @endif

                                                <button type="button" class="btn btn-sm btn-info"
                                                    title="Ver auditoría"
                                                    wire:click="showHistory({{ $build->id }})">
                                                    <i class="fas fa-history"></i>
                                                </button>

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            @empty

                <div class="card">
                    <div class="card-body text-center text-muted p-5">
                        <i class="fas fa-box-open fa-3x mb-3"></i>

                        <h5>No se encontraron registros de fabricación</h5>

                        <p class="mb-0">
                            Intenta seleccionar otro día o rango de fechas.
                        </p>
                    </div>
                </div>

            @endforelse

            {{-- ====================================================== --}}
            {{-- PAGINACIÓN --}}
            {{-- ====================================================== --}}

            <div class="mt-3">
                {{ $builds->links() }}
            </div>

        </div>
    </div>
</div>

