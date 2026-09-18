<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- ENCABEZADO --}}
    {{-- ========================================================= --}}

    <div class="row mb-3">

        <div class="col-md-7">

            <h4 class="mb-1">
                Dashboard de contratos
            </h4>

            <small class="text-muted">
                Seguimiento general de producción
            </small>

        </div>

        <div class="col-md-5 text-md-right mt-2 mt-md-0">

            <span class="badge badge-warning mr-1">
                Activos
            </span>

            <span class="badge badge-primary">
                Finalizados
            </span>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FILTROS --}}
    {{-- ========================================================= --}}

    <div class="card mb-3">

        <div class="card-body">

            <div class="row">

                {{-- BUSCADOR --}}
                <div class="col-md-8">

                    <label>
                        Buscar contrato
                    </label>

                    <div class="input-group">

                        <div class="input-group-prepend">

                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>

                        </div>

                        <input
                            type="text"
                            class="form-control"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Contrato, cliente, CI/NIT, producto, pieza o destino..."
                        >

                        @if ($search !== '')

                            <div class="input-group-append">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    wire:click="$set('search', '')"
                                >
                                    <i class="fas fa-times"></i>
                                </button>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- ESTADO --}}
                <div class="col-md-4">

                    <label>
                        Estado
                    </label>

                    <select
                        class="form-control"
                        wire:model.live="statusFilter"
                    >

                        <option value="all">
                            Todos
                        </option>

                        <option value="active">
                            Activos
                        </option>

                        <option value="finish">
                            Finalizados
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- RESULTADOS --}}
    {{-- ========================================================= --}}

    @forelse ($contracts as $contract)

        <a
            href="{{ route('dashboard.contracts.view', $contract['id']) }}"
            class="text-dark"
            style="text-decoration: none;"
        >

            <div class="card mb-3 shadow-sm">

                {{-- ================================================= --}}
                {{-- CABECERA --}}
                {{-- ================================================= --}}

                <div class="card-header">

                    <div class="row align-items-center">

                        <div class="col-md-4">

                            <h5 class="mb-1">
                                Contrato #{{ $contract['id'] }}
                            </h5>

                            <div class="text-muted">
                                {{ $contract['customer'] }}
                            </div>

                        </div>


                        <div class="col-md-2">

                            <small class="text-muted d-block">
                                Entrega
                            </small>

                            <strong>

                                {{ $contract['delivery_date']
                                    ? \Carbon\Carbon::parse(
                                        $contract['delivery_date']
                                    )->format('d/m/Y')
                                    : 'Sin fecha'
                                }}

                            </strong>

                        </div>


                        <div class="col-md-2">

                            <small class="text-muted d-block">
                                Prioridad
                            </small>

                            @if ($contract['priority'] === 1)

                                <span class="badge badge-danger">
        1 - Muy alta
    </span>

                            @elseif ($contract['priority'] === 2)

                                <span class="badge badge-warning">
        2 - Alta
    </span>

                            @elseif ($contract['priority'] === 3)

                                <span class="badge badge-info">
        3 - Media
    </span>

                            @elseif ($contract['priority'] === 4)

                                <span class="badge badge-secondary">
        4 - Baja
    </span>

                            @elseif ($contract['priority'] === 5)

                                <span class="badge badge-success border">
        5 - Normal
    </span>

                            @endif


                        </div>


                        <div class="col-md-2">

                            <small class="text-muted d-block">
                                Destino
                            </small>

                            <strong>
                                {{ $contract['destination'] ?: 'Sin destino' }}
                            </strong>

                        </div>


                        <div class="col-md-2 text-md-right">

                            @if (
                                $contract['status_value']
                                === \App\Enums\Status::ACTIVE->value
                            )

                                <span class="badge badge-warning">
                                    Activo
                                </span>

                            @elseif (
                                $contract['status_value']
                                === \App\Enums\Status::FINISH->value
                            )

                                <span class="badge badge-primary">
                                    Finalizado
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- PRODUCTOS --}}
                {{-- ================================================= --}}

                <div class="card-body">

                    @foreach ($contract['products'] as $product)

                        <div class="border rounded p-3 mb-3">

                            <div class="row">

                                <div class="col-md-5">

                                    <h5 class="mb-1">
                                        {{ $product['name'] }}
                                    </h5>

                                    @if ($product['description'])

                                        <small class="text-muted">
                                            {{ $product['description'] }}
                                        </small>

                                    @endif

                                </div>


                                <div class="col-md-7">

                                    <div class="d-flex justify-content-between mb-1">

                                        <span>
                                            Progreso
                                        </span>

                                        <strong>
                                            {{ $product['progress'] }}
                                            /
                                            {{ $product['required'] }}
                                        </strong>

                                    </div>

                                    <div
                                        class="progress"
                                        style="height: 18px;"
                                    >

                                        <div
                                            class="progress-bar"
                                            role="progressbar"
                                            style="width: {{ $product['percentage'] }}%;"
                                        >
                                            {{ $product['percentage'] }}%
                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- RESUMEN --}}
                            <div class="row mt-3">

                                <div class="col-md-4">

                                    <small class="text-muted d-block">
                                        Requerido
                                    </small>

                                    <strong>
                                        {{ $product['required'] }}
                                    </strong>

                                </div>

                                <div class="col-md-4">

                                    <small class="text-muted d-block">
                                        Producido
                                    </small>

                                    <strong>
                                        {{ $product['progress'] }}
                                    </strong>

                                </div>

                                <div class="col-md-4">

                                    <small class="text-muted d-block">
                                        Pendiente
                                    </small>

                                    <strong>
                                        {{ $product['pending'] }}
                                    </strong>

                                </div>

                            </div>


                            {{-- ================================================= --}}
                            {{-- PIEZAS --}}
                            {{-- ================================================= --}}

                            @if (count($product['components']))

                                <div class="mt-4">

                                    <h6 class="mb-3">
                                        <i class="fas fa-puzzle-piece mr-1"></i>
                                        Piezas
                                    </h6>


                                    @foreach ($product['components'] as $component)

                                        <div class="border-left pl-3 mb-3">

                                            <div class="row align-items-center">

                                                <div class="col-md-4">

                                                    <strong>
                                                        {{ $component['name'] }}
                                                    </strong>

                                                    @if (
                                                        $component['description']
                                                    )

                                                        <small class="text-muted d-block">
                                                            {{ $component['description'] }}
                                                        </small>

                                                    @endif

                                                </div>


                                                <div class="col-md-5">

                                                    <div
                                                        class="d-flex justify-content-between mb-1"
                                                    >

                                                        <small>
                                                            Progreso
                                                        </small>

                                                        <small>

                                                            <strong>
                                                                {{ $component['progress'] }}
                                                                /
                                                                {{ $component['required'] }}
                                                            </strong>

                                                        </small>

                                                    </div>

                                                    <div
                                                        class="progress"
                                                        style="height: 14px;"
                                                    >

                                                        <div
                                                            class="progress-bar"
                                                            role="progressbar"
                                                            style="width: {{ $component['percentage'] }}%;"
                                                        >
                                                        </div>

                                                    </div>

                                                </div>


                                                <div class="col-md-3 text-md-right">

                                                    @if (
                                                        $component['percentage']
                                                        >= 100
                                                    )

                                                        <span class="badge badge-success">
                                                            Completa
                                                        </span>

                                                    @else

                                                        <span class="text-muted">
                                                            Pendiente:
                                                            {{ $component['pending'] }}
                                                        </span>

                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>


                {{-- ================================================= --}}
                {{-- FOOTER --}}
                {{-- ================================================= --}}

                <div class="card-footer text-muted">

                    <i class="fas fa-external-link-alt mr-1"></i>

                    Ver contrato #{{ $contract['id'] }}

                </div>

            </div>

        </a>

    @empty

        <div class="card">

            <div class="card-body text-center py-5">

                <i class="fas fa-search fa-3x text-muted mb-3"></i>

                @if ($search !== '')

                    <h5>
                        No se encontraron contratos
                    </h5>

                    <p class="text-muted mb-0">
                        No hay contratos que coincidan con
                        "{{ $search }}".
                    </p>

                @else

                    <h5>
                        No hay contratos
                    </h5>

                    <p class="text-muted mb-0">
                        No existen contratos con el estado seleccionado.
                    </p>

                @endif

            </div>

        </div>

    @endforelse

</div>
