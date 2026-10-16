<div>
    <x-slot name="header">
        <h1>
            <i class="fas fa-tasks mr-2"></i>
            Gestión de tareas
        </h1>
    </x-slot>

    <div class="container-fluid">

        {{-- Mensajes --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle mr-1"></i>
                {{ session('success') }}

                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        {{-- Formulario --}}
        @if ($showForm)
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-{{ $taskId ? 'edit' : 'plus' }} mr-1"></i>
                        {{ $taskId ? 'Editar tarea' : 'Nueva tarea' }}
                    </h3>

                    <div class="card-tools">
                        <button
                            type="button"
                            class="btn btn-tool"
                            wire:click="cancel"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit="save">
                    <div class="card-body">
                        <div class="row">

                            {{-- Nombre --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-name">
                                        Nombre de la tarea
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        id="task-name"
                                        type="text"
                                        class="form-control @error('form.name') is-invalid @enderror"
                                        wire:model="form.name"
                                        placeholder="Ej. Preparar estructura metálica"
                                    >

                                    @error('form.name')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Área --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task-industry">
                                        Área responsable
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        id="task-industry"
                                        class="form-control @error('form.industry_id') is-invalid @enderror"
                                        wire:model="form.industry_id"
                                    >
                                        <option value="">Seleccionar área...</option>

                                        @foreach ($this->industries as $industry)
                                            <option value="{{ $industry->id }}">
                                                {{ $industry->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('form.industry_id')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Fecha límite --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="task-date">
                                        Fecha límite
                                    </label>

                                    <input
                                        id="task-date"
                                        type="date"
                                        class="form-control @error('form.to') is-invalid @enderror"
                                        wire:model="form.to"
                                    >

                                    @error('form.to')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror

                                    <small class="text-muted">
                                        Campo opcional.
                                    </small>
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="task-description">
                                        Descripción
                                    </label>

                                    <textarea
                                        id="task-description"
                                        rows="3"
                                        class="form-control @error('form.description') is-invalid @enderror"
                                        wire:model="form.description"
                                        placeholder="Describe qué se debe realizar..."
                                    ></textarea>

                                    @error('form.description')
                                        <span class="invalid-feedback">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-1"></i>
                                {{ $taskId ? 'Guardar cambios' : 'Crear tarea' }}
                            </span>

                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Guardando...
                            </span>
                        </button>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            wire:click="cancel"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Listado --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Tareas registradas
                </h3>

                <div class="card-tools">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        wire:click="create"
                    >
                        <i class="fas fa-plus mr-1"></i>
                        Nueva tarea
                    </button>
                </div>
            </div>

            <div class="card-body">

                {{-- Buscador y filtros --}}
                <div class="row mb-3">
                    <div class="col-md-5 mb-2">
                        <div class="input-group">
                            <input
                                type="search"
                                class="form-control"
                                placeholder="Buscar por nombre o descripción..."
                                wire:model.live.debounce.300ms="search"
                            >

                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <select
                            class="form-control"
                            wire:model.live="industryFilter"
                        >
                            <option value="">Todas las áreas</option>

                            @foreach ($this->industries as $industry)
                                <option value="{{ $industry->id }}">
                                    {{ $industry->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px">#</th>
                                <th>Tarea</th>
                                <th>Área responsable</th>
                                <th>Fecha límite</th>
                                <th>Estado</th>
                                <th style="width: 130px" class="text-center">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($this->tasks as $task)
                                <tr wire:key="task-{{ $task->id }}">
                                    <td>{{ $task->id }}</td>

                                    <td>
                                        <strong>{{ $task->name }}</strong>

                                        @if ($task->description)
                                            <p class="text-muted small mb-0 mt-1">
                                                {{ $task->description }}
                                            </p>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $task->industry?->name ?? 'Sin área' }}
                                    </td>

                                    <td>
                                        @if ($task->to)
                                            {{ $task->to->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">
                                                Sin fecha
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if (!$task->to)
                                            <span class="badge badge-secondary">
                                                Sin fecha límite
                                            </span>
                                        @elseif ($task->to->lt(today()))
                                            <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Vencida
                                            </span>
                                        @elseif ($task->to->isToday())
                                            <span class="badge badge-warning">
                                                Vence hoy
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm"
                                            title="Editar"
                                            wire:click="edit({{ $task->id }})"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            title="Eliminar"
                                            wire:click="delete({{ $task->id }})"
                                            wire:confirm="¿Seguro que deseas eliminar esta tarea?"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>

                                        <p class="text-muted mb-0">
                                            No hay tareas registradas.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $this->tasks->links() }}
                </div>

            </div>
        </div>

    </div>
</div>
