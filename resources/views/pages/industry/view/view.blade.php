<x-slot name="header">

    <h1>Fábrica #{{ $industry->id }}</h1>

</x-slot>

<div>

    {{-- Barra de acciones --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <a
            href="{{ route('dashboard.industries') }}"
            class="btn btn-secondary"
        >
            <i class="fa fa-arrow-left"></i>
            Volver
        </a>

        @if (!$editingIndustry)

            <button
                type="button"
                wire:click="editIndustry"
                class="btn btn-warning"
            >
                <i class="fa fa-edit"></i>
                Editar
            </button>

        @endif

    </div>


    {{-- Información de la fábrica --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-industry"></i>
                Datos de la fábrica
            </h3>

        </div>

        <div class="card-body">

            @if ($editingIndustry)

                <div class="row">

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>Nombre</label>

                            <input
                                type="text"
                                wire:model="industryData.name"
                                class="form-control @error('industryData.name') is-invalid @enderror"
                            >

                            @error('industryData.name')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>Tipo de fábrica</label>

                            <select
                                wire:model="industryData.type_factory_id"
                                class="form-control @error('industryData.type_factory_id') is-invalid @enderror"
                            >

                                @foreach (
                                    \App\Models\TypeFactory::orderBy('name')->get()
                                    as $typeFactory
                                )

                                    <option value="{{ $typeFactory->id }}">
                                        {{ $typeFactory->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('industryData.type_factory_id')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                </div>


                <div class="row">

                    <div class="col-md-8">

                        <div class="form-group">

                            <label>Dirección</label>

                            <input
                                type="text"
                                wire:model="industryData.address"
                                class="form-control @error('industryData.address') is-invalid @enderror"
                            >

                            @error('industryData.address')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Teléfono</label>

                            <input
                                type="text"
                                wire:model="industryData.phone"
                                class="form-control @error('industryData.phone') is-invalid @enderror"
                            >

                            @error('industryData.phone')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                </div>

            @else

                <div class="row">

                    <div class="col-md-4">

                        <strong>Nombre</strong>

                        <div class="text-muted">
                            {{ $industry->name }}
                        </div>

                    </div>

                    <div class="col-md-4">

                        <strong>Tipo</strong>

                        <div class="text-muted">
                            {{ $industry->typeFactory?->name ?? '-' }}
                        </div>

                    </div>

                    <div class="col-md-4">

                        <strong>Teléfono</strong>

                        <div class="text-muted">
                            {{ $industry->phone ?? '-' }}
                        </div>

                    </div>

                </div>

                <div class="mt-3">

                    <strong>Dirección</strong>

                    <div class="text-muted">
                        {{ $industry->address ?? '-' }}
                    </div>

                </div>

            @endif

        </div>

    </div>


    {{-- Usuarios --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-users"></i>
                Usuarios de la fábrica
            </h3>

        </div>

        <div class="card-body p-0">

            @if ($industry->users->count())

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>

                        <tr>
                            <th width="80">N°</th>
                            <th>Nombre</th>
                            <th>Nombre de Usuario</th>
                        </tr>

                        </thead>

                        <tbody>

                        @foreach ($industry->users as $user)

                            <tr>

                                <td>
                                    {{ $user->id }}
                                </td>

                                <td>
                                    {{ $user->name }}
                                </td>

                                <td>
                                    {{ $user->username }}
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-4">

                    <i class="fa fa-users fa-2x mb-2"></i>

                    <p class="mb-0">
                        No hay usuarios asignados a esta fábrica.
                    </p>

                </div>

            @endif

        </div>

    </div>


    {{-- Contratos --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-file-signature"></i>
                Contratos relacionados
            </h3>

        </div>

        <div class="card-body p-0">

            @if ($contracts->count())

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>

                        <tr>
                            <th width="80">N°</th>
                            <th>Cliente</th>
                            <th>Entrega</th>
                            <th>Estado</th>
                            <th width="80">Acción</th>
                        </tr>

                        </thead>

                        <tbody>

                        @foreach ($contracts as $contract)

                            <tr>

                                <td>
                                    {{ $contract->id }}
                                </td>

                                <td>
                                    {{ $contract->customer?->name ?? 'Cliente anónimo' }}
                                </td>

                                <td>

                                    @if ($contract->delivery_date)

                                        {{ \Carbon\Carbon::parse(
                                            $contract->delivery_date
                                        )->format('d/m/Y') }}

                                    @else

                                        -

                                    @endif

                                </td>

                                <td>
                                    {{ $contract->status->label() }}
                                </td>

                                <td>

                                    <a
                                        href="{{ route(
                                                'dashboard.contracts.view',
                                                $contract
                                            ) }}"
                                        class="btn btn-primary btn-sm"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-4">

                    <i class="fa fa-file-signature fa-2x mb-2"></i>

                    <p class="mb-0">
                        No hay contratos relacionados con esta fábrica.
                    </p>

                </div>

            @endif

        </div>

    </div>


    {{-- Tareas de ensamble --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-cubes"></i>
                Tareas de ensamble
            </h3>

        </div>

        <div class="card-body p-0">

            @if ($industry->assemblies->count())

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>

                        <tr>
                            <th>N°</th>
                            <th>Contrato</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                        </tr>

                        </thead>

                        <tbody>

                        @foreach ($industry->assemblies as $assembly)

                            <tr>

                                <td>
                                    {{ $assembly->id }}
                                </td>

                                <td>

                                    <a
                                        href="{{ route(
                                                'dashboard.contracts.view',
                                                $assembly->contract
                                            ) }}"
                                    >
                                        Contrato #{{ $assembly->contract_id }}
                                    </a>

                                </td>

                                <td>
                                    {{ $assembly->product?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $assembly->quantity }}
                                </td>

                                <td>
                                    {{ $assembly->status->label() }}
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-4">

                    No hay tareas de ensamble asignadas.

                </div>

            @endif

        </div>

    </div>


    {{-- Tareas de componentes --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-puzzle-piece"></i>
                Tareas de componentes
            </h3>

        </div>

        <div class="card-body p-0">

            @if ($industry->assemblyComponents->count())

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>

                        <tr>
                            <th>N°</th>
                            <th>Contrato</th>
                            <th>Componente</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                        </tr>

                        </thead>

                        <tbody>

                        @foreach (
                            $industry->assemblyComponents
                            as $assemblyComponent
                        )

                            <tr>

                                <td>
                                    {{ $assemblyComponent->id }}
                                </td>

                                <td>

                                    <a
                                        href="{{ route(
                                                'dashboard.contracts.view',
                                                $assemblyComponent->assembly->contract
                                            ) }}"
                                    >
                                        Contrato #{{ $assemblyComponent->assembly->contract_id }}
                                    </a>

                                </td>

                                <td>
                                    {{ $assemblyComponent->component?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $assemblyComponent->quantity }}
                                </td>

                                <td>
                                    {{ $assemblyComponent->status }}
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-4">

                    No hay tareas de componentes asignadas.

                </div>

            @endif

        </div>

    </div>


    {{-- Acciones --}}
    @if (!$editingIndustry)

        <div class="card">

            <div class="card-body text-right">

                <button
                    type="button"
                    wire:click="deleteIndustry"
                    class="btn btn-danger"
                >
                    <i class="fa fa-trash"></i>
                    Eliminar fábrica
                </button>

            </div>

        </div>

    @else

        <div class="card">

            <div class="card-body text-right">

                <button
                    type="button"
                    wire:click="cancelEdit"
                    class="btn btn-secondary"
                >
                    <i class="fa fa-times"></i>
                    Cancelar
                </button>

                <button
                    type="button"
                    wire:click="updateIndustry"
                    class="btn btn-success"
                >
                    <i class="fa fa-save"></i>
                    Guardar cambios
                </button>

            </div>

        </div>

    @endif

</div>
