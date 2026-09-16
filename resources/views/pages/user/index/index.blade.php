<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h1 class="h3 mb-0">
                Usuarios
            </h1>

            <small class="text-muted">
                Gestión de usuarios y asignación de fábricas
            </small>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            wire:click="openCreate"
            data-bs-toggle="modal"
            data-bs-target="#modal-user"
        >
            <i class="fas fa-user-plus"></i>
            Nuevo usuario
        </button>

    </div>


    {{-- MENSAJES --}}

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif


    {{-- BUSCADOR --}}

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fas fa-users"></i>
                Lista de usuarios
            </h3>

            <div class="card-tools">

                <div class="input-group input-group-sm" style="width: 300px">

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar usuario..."
                        wire:model.live="search"
                    >

                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered mb-0">

                    <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Fábrica</th>
                        <th width="130">Acciones</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse ($users ?? [] as $user)

                        <tr wire:key="user-{{ $user->id }}">

                            <td>
                                {{ $user->id }}
                            </td>

                            <td>
                                <strong>
                                    {{ $user->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $user->username }}
                            </td>

                            <td>

                                @switch($user->role)

                                    @case(\App\Enums\Role::ADMIN)
                                        <span class="badge badge-danger">
                                                <i class="fas fa-user-shield"></i>
                                                Administrador
                                            </span>
                                        @break

                                    @case(\App\Enums\Role::PRIVILEGED)
                                        <span class="badge badge-warning">
                                                <i class="fas fa-user-tie"></i>
                                                Privilegiado
                                            </span>
                                        @break

                                    @case(\App\Enums\Role::RECEPT)
                                        <span class="badge badge-info">
                                                <i class="fas fa-user-edit"></i>
                                                Recepción
                                            </span>
                                        @break

                                    @case(\App\Enums\Role::WORKER)
                                        <span class="badge badge-success">
                                                <i class="fas fa-hard-hat"></i>
                                                Trabajador
                                            </span>
                                        @break

                                @endswitch

                            </td>

                            <td>

                                @if ($user->industry)

                                    <span>
                                            <i class="fas fa-industry text-muted"></i>
                                            {{ $user->industry->name }}
                                        </span>

                                @else

                                    <span class="text-muted">
                                            Sin fábrica
                                        </span>

                                @endif

                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm"
                                    wire:click="openEdit({{ $user->id }})"
                                    data-toggle="modal"
                                    data-target="#modal-user"
                                    title="Editar"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    wire:click="confirmDelete({{ $user->id }})"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal-delete-user"
                                    title="Eliminar"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-4">

                                <i class="fas fa-users fa-2x text-muted mb-2"></i>

                                <p class="mb-0 text-muted">
                                    No hay usuarios registrados.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        @if ($users->hasPages())

            <div class="card-footer">

                {{ $users->links() }}

            </div>

        @endif

    </div>



    {{-- ========================================================= --}}
    {{-- MODAL CREAR / EDITAR --}}
    {{-- ========================================================= --}}

    <div
        wire:ignore.self
        class="modal fade"
        id="modal-user"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-md">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title">

                        @if ($editing)
                            <i class="fas fa-user-edit"></i>
                            Editar usuario
                        @else
                            <i class="fas fa-user-plus"></i>
                            Nuevo usuario
                        @endif

                    </h4>

                    <button
                        type="button"
                        class="close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>


                <div class="modal-body">

                    {{-- NOMBRE --}}

                    <div class="form-group">

                        <label>
                            Nombre
                        </label>

                        <input
                            type="text"
                            class="form-control @error('form.name') is-invalid @enderror"
                            wire:model="form.name"
                            placeholder="Nombre completo"
                        >

                        @error('form.name')
                        <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- USUARIO --}}

                    <div class="form-group">

                        <label>
                            Nombre de usuario
                        </label>

                        <input
                            type="text"
                            class="form-control @error('form.username') is-invalid @enderror"
                            wire:model="form.username"
                            placeholder="Usuario para iniciar sesión"
                        >

                        @error('form.username')
                        <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- CONTRASEÑA --}}

                    <div class="form-group">

                        <label>

                            Contraseña

                            @if ($editing)
                                <small class="text-muted">
                                    (dejar vacío para conservar la actual)
                                </small>
                            @endif

                        </label>

                        <input
                            type="password"
                            class="form-control @error('form.password') is-invalid @enderror"
                            wire:model="form.password"
                            placeholder="Contraseña"
                        >

                        @error('form.password')
                        <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>
                    <div class="form-group">

                        <label>
                            Repetir contraseña
                        </label>

                        <input
                            type="password"
                            class="form-control @error('form.password_confirmation') is-invalid @enderror"
                            wire:model="form.password_confirmation"
                            placeholder="Repita la contraseña"
                        >

                        @error('form.password_confirmation')
                        <span class="invalid-feedback">
            {{ $message }}
        </span>
                        @enderror

                    </div>


                    {{-- ROL --}}

                    <div class="form-group">

                        <label>
                            Rol
                        </label>

                        <select
                            class="form-control @error('form.role') is-invalid @enderror"
                            wire:model.live="form.role"
                        >

                            <option value="">
                                Seleccione un rol
                            </option>

                            <option value="{{ \App\Enums\Role::ADMIN->value }}">
                                Administrador
                            </option>

                            <option value="{{ \App\Enums\Role::PRIVILEGED->value }}">
                                Privilegiado
                            </option>

                            <option value="{{ \App\Enums\Role::RECEPT->value }}">
                                Recepción
                            </option>

                            <option value="{{ \App\Enums\Role::WORKER->value }}">
                                Trabajador
                            </option>

                        </select>

                        @error('form.role')
                        <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- FÁBRICA --}}

                    <div class="form-group">

                        <label>

                            Fábrica

                            @if ($form['role'] == \App\Enums\Role::WORKER->value)
                                <span class="text-danger">*</span>
                            @endif

                        </label>

                        <select
                            class="form-control @error('form.industry_id') is-invalid @enderror"
                            wire:model="form.industry_id"
                        >

                            <option value="">
                                @if ($form['role'] == \App\Enums\Role::WORKER->value)
                                    Seleccione una fábrica
                                @else
                                    Sin fábrica
                                @endif
                            </option>

                            @foreach ($industries as $industry)

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

                        @if ($form['role'] == \App\Enums\Role::WORKER->value)

                            <small class="form-text text-muted">
                                Los trabajadores solo podrán ver y gestionar
                                las tareas de la fábrica asignada.
                            </small>

                        @endif

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="save"
                    >
                        <i class="fas fa-save"></i>
                        {{ $editing ? 'Actualizar' : 'Guardar' }}
                    </button>

                </div>

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- MODAL ELIMINAR --}}
    {{-- ========================================================= --}}

    <div
        wire:ignore.self
        class="modal fade"
        id="modal-delete-user"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-sm">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Eliminar usuario
                    </h4>

                    <button
                        type="button"
                        class="close"
                        data-bs-dismiss="modal"
                    >
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <p class="mb-0">
                        ¿Está seguro de eliminar este usuario?
                    </p>

                    <small class="text-muted">
                        Esta acción no se puede deshacer.
                    </small>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        wire:click="delete"
                    >
                        <i class="fas fa-trash"></i>
                        Eliminar
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>
