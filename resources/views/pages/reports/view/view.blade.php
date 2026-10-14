<x-slot name="header">
    <h1>
        <i class="fas fa-chart-bar mr-2"></i>
        Reportes de producción
    </h1>
</x-slot>

<div>
    {{-- ========================================================= --}}
    {{-- FILTROS GENERALES                                         --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter mr-1"></i>
                Filtros generales
            </h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="search">Buscar contratos</label>
                    <input
                        id="search"
                        type="text"
                        class="form-control"
                        placeholder="Código, proforma, cliente o producto..."
                        wire:model.live.debounce.400ms="search"
                    >
                </div>

                <div class="col-md-2">
                    <label for="statusFilter">Estado del contrato</label>
                    <select
                        id="statusFilter"
                        class="form-control"
                        wire:model.live="statusFilter"
                    >
                        <option value="">Todos los estados</option>
                        @foreach (\App\Enums\Status::cases() as $status)
                            <option value="{{ $status->value }}">
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="dateFrom">Desde</label>
                    <input
                        id="dateFrom"
                        type="date"
                        class="form-control"
                        wire:model.live="dateFrom"
                    >
                </div>

                <div class="col-md-2">
                    <label for="dateTo">Hasta</label>
                    <input
                        id="dateTo"
                        type="date"
                        class="form-control"
                        wire:model.live="dateTo"
                    >
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-secondary btn-block"
                        wire:click="clearFilters"
                    >
                        <i class="fas fa-eraser mr-1"></i>
                        Limpiar
                    </button>
                </div>
            </div>

            <small class="text-muted d-block mt-2">
                Estos filtros afectan los datos generales y los reportes.
                Cada tabla dispone, además, de su propia búsqueda.
            </small>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- INDICADORES GENERALES                                     --}}
    {{-- ========================================================= --}}

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalRequested) }}</h3>
                    <p>Unidades solicitadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalProduced) }}</h3>
                    <p>Unidades fabricadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($totalPending) }}</h3>
                    <p>Unidades faltantes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($totalDelivered) }}</h3>
                    <p>Unidades entregadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- CONTRATOS POR ESTADO                                      --}}
    {{-- ========================================================= --}}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks mr-1"></i>
                Contratos por estado
            </h3>
        </div>

        <div class="card-body">
            <div class="row justify-content-between">
                @foreach ($contractsByStatus as $item)
                    <div class="col-md-4 col-lg-2 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <span class="badge {{ $item['badge'] }}">
                                {{ $item['label'] }}
                            </span>

                            <h3 class="mt-2 mb-0">
                                {{ number_format($item['count']) }}
                            </h3>
                            <small class="text-muted">Contratos</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TABLA 1: PRODUCTOS ORDENADOS                              --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-boxes mr-1"></i>
                Productos ordenados
            </h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input
                        type="search"
                        class="form-control"
                        placeholder="Buscar producto, código o ID..."
                        wire:model.live.debounce.300ms="searchOrderedProducts"
                    >
                </div>

                <div class="col-md-7 text-md-right mt-2 mt-md-0">
                    <span class="text-muted">
                        Productos encontrados:
                        <strong>{{ $filteredOrderedProducts->count() }}</strong>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-center">Líneas de pedido</th>
                            <th class="text-right">Solicitado</th>
                            <th class="text-right">Fabricado</th>
                            <th class="text-right">Entregado</th>
                            <th class="text-right">Faltante</th>
                            <th style="min-width: 150px;">Avance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($filteredOrderedProducts as $product)
                            <tr>
                                <td>{{ $product['product_id'] }}</td>
                                <td>{{ $product['cod'] }}</td>
                                <td>{{ $product['name'] }}</td>

                                <td class="text-center">
                                    {{ number_format($product['orders']) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($product['requested']) }}
                                </td>

                                <td class="text-right text-success">
                                    {{ number_format($product['produced']) }}
                                </td>

                                <td class="text-right text-primary">
                                    {{ number_format($product['delivered']) }}
                                </td>

                                <td class="text-right text-danger">
                                    {{ number_format($product['pending']) }}
                                </td>

                                <td>
                                    <div class="progress">
                                        <div
                                            class="progress-bar bg-success"
                                            role="progressbar"
                                            style="width: {{ $product['progress'] }}%"
                                            aria-valuenow="{{ $product['progress'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        ></div>
                                    </div>

                                    <small>
                                        {{ number_format($product['progress'], 1) }}%
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    No se encontraron productos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($filteredOrderedProducts->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="4">Totales mostrados</td>

                                <td class="text-right">
                                    {{ number_format($filteredOrderedProducts->sum('requested')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredOrderedProducts->sum('produced')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredOrderedProducts->sum('delivered')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredOrderedProducts->sum('pending')) }}
                                </td>

                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TABLA 2: PRODUCCIÓN POR CONTRATO                          --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clipboard-check mr-1"></i>
                Producción por contrato
            </h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input
                        type="search"
                        class="form-control"
                        placeholder="Buscar contrato, proforma externa, cliente, producto, estado o prioridad..."
                        wire:model.live.debounce.300ms="searchProduction"
                    >
                </div>

                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="text-muted">
                        Registros:
                        <strong>{{ $filteredProductionRows->count() }}</strong>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Contrato</th>
                            <th>Proforma externa</th>
                            <th>Cliente</th>
                            <th>Destino</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-right">Solicitado</th>
                            <th class="text-right">Fabricado</th>
                            <th class="text-right">Entregado</th>
                            <th class="text-right">Faltante</th>
                            <th>Prioridad</th>
                            <th>Estado de producción</th>
                            <th>Entrega prevista</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($filteredProductionRows as $row)
                            <tr>
                                <td>#{{ $row['contract_id'] }}</td>

                                <td>
                                    {{ filled($row['external_id']) ? $row['external_id'] : '—' }}
                                </td>

                                <td>{{ $row['customer'] }}</td>
                                <td>{{ $row['destination'] }}</td>
                                <td>{{ $row['product_cod'] }}</td>
                                <td>{{ $row['product_name'] }}</td>

                                <td class="text-right">
                                    {{ number_format($row['requested']) }}
                                </td>

                                <td class="text-right text-success">
                                    {{ number_format($row['produced']) }}
                                </td>

                                <td class="text-right text-primary">
                                    {{ number_format($row['delivered']) }}
                                </td>

                                <td class="text-right text-danger">
                                    {{ number_format($row['pending']) }}
                                </td>

                                <td>
                                    @if ($row['priority'] !== null && $row['priority'] !== '')
                                        {{ $row['priority'] }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $row['status_badge'] }}">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>

                                <td>
                                    {{ $row['delivery_date'] ? \Carbon\Carbon::parse($row['delivery_date'])->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">
                                    No se encontraron registros de producción.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($filteredProductionRows->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="5">Totales mostrados</td>

                                <td class="text-right">
                                    {{ number_format($filteredProductionRows->sum('requested')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredProductionRows->sum('produced')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredProductionRows->sum('delivered')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredProductionRows->sum('pending')) }}
                                </td>

                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TABLA 3: PRODUCCIÓN POR INDUSTRIA                         --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-industry mr-1"></i>
                Productos solicitados por Areas
            </h3>
        </div>

        <div class="card-body">
            <p class="text-muted">
                Consolidado por area y producto. Las cantidades faltantes
                corresponden a unidades solicitadas que todavía no se han fabricado.
            </p>

            <div class="row mb-3">
                <div class="col-md-6">
                    <input
                        type="search"
                        class="form-control"
                        placeholder="Buscar ID/nombre de area, código o producto..."
                        wire:model.live.debounce.300ms="searchIndustry"
                    >
                </div>

                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="text-muted">
                        Registros:
                        <strong>{{ $filteredIndustryRows->count() }}</strong>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Para Area</th>
                            <th>Area Final</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-center">Contratos</th>
                            <th class="text-right">Solicitado</th>
                            <th class="text-right">Fabricado</th>
                            <th class="text-right">Entregado</th>
                            <th class="text-right">Faltante</th>
                            <th style="min-width: 150px;">Avance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($filteredIndustryRows as $row)
                            <tr>
                                <td>
                                    {{ $row['industry_id'] ?? '—' }}
                                </td>

                                <td>
                                    {{ $row['industry_name'] }}
                                </td>

                                <td>{{ $row['product_cod'] }}</td>
                                <td>{{ $row['product_name'] }}</td>

                                <td class="text-center">
                                    {{ number_format($row['contracts']) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($row['requested']) }}
                                </td>

                                <td class="text-right text-success">
                                    {{ number_format($row['produced']) }}
                                </td>

                                <td class="text-right text-primary">
                                    {{ number_format($row['delivered']) }}
                                </td>

                                <td class="text-right text-danger">
                                    {{ number_format($row['pending']) }}
                                </td>

                                <td>
                                    <div class="progress">
                                        <div
                                            class="progress-bar bg-success"
                                            role="progressbar"
                                            style="width: {{ $row['progress'] }}%"
                                            aria-valuenow="{{ $row['progress'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        ></div>
                                    </div>

                                    <small>
                                        {{ number_format($row['progress'], 1) }}%
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No se encontraron productos para las industrias.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($filteredIndustryRows->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="5">Totales mostrados</td>

                                <td class="text-right">
                                    {{ number_format($filteredIndustryRows->sum('requested')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredIndustryRows->sum('produced')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredIndustryRows->sum('delivered')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($filteredIndustryRows->sum('pending')) }}
                                </td>

                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- GRÁFICO 1: PRODUCTOS MÁS SOLICITADOS                      --}}
    {{-- ========================================================= --}}

    <div class="row">
        <div class="col-lg-6">
            <div class="card card-outline card-success h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Productos más solicitados
                    </h3>
                </div>

                <div class="card-body">
                    @forelse ($topProducts as $product)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>
                                    <strong>{{ $product['cod'] }}</strong>
                                    — {{ $product['name'] }}
                                </span>

                                <strong>
                                    {{ number_format($product['quantity']) }}
                                </strong>
                            </div>

                            <div class="progress">
                                <div
                                    class="progress-bar bg-success"
                                    role="progressbar"
                                    style="width: {{ min(100, ($product['quantity'] / $maxProductQuantity) * 100) }}%"
                                    aria-valuenow="{{ $product['quantity'] }}"
                                    aria-valuemin="0"
                                    aria-valuemax="{{ $maxProductQuantity }}"
                                ></div>
                            </div>

                            <small class="text-muted">
                                En {{ number_format($product['orders']) }}
                                líneas de pedido.
                            </small>
                        </div>
                    @empty
                        <p class="text-muted text-center">
                            No hay productos para mostrar.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- GRÁFICO 2: CONTRATOS POR FECHA                        --}}
        {{-- ===================================================== --}}

        <div class="col-lg-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Contratos por fecha
                    </h3>

                    <div class="card-tools">
                        <select
                            class="form-control form-control-sm"
                            wire:model.live="chartGroup"
                        >
                            <option value="month">Agrupar por mes</option>
                            <option value="day">Agrupar por día</option>
                        </select>
                    </div>
                </div>

                <div class="card-body">
                    @forelse ($dateOrders as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ $item['label'] }}</span>

                                <strong>
                                    {{ number_format($item['count']) }}
                                </strong>
                            </div>

                            <div class="progress">
                                <div
                                    class="progress-bar bg-primary"
                                    role="progressbar"
                                    style="width: {{ min(100, ($item['count'] / $maxDateOrders) * 100) }}%"
                                    aria-valuenow="{{ $item['count'] }}"
                                    aria-valuemin="0"
                                    aria-valuemax="{{ $maxDateOrders }}"
                                ></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">
                            No hay contratos para mostrar.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TABLA 4: CONTRATOS                                        --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-contract mr-1"></i>
                Contratos
            </h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input
                        type="search"
                        class="form-control"
                        placeholder="Buscar por ID, proforma externa, cliente, producto, prioridad o estado..."
                        wire:model.live.debounce.300ms="searchContracts"
                    >
                </div>

                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="text-muted">
                        Contratos encontrados:
                        <strong>{{ $filteredContracts->count() }}</strong>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID contrato</th>
                            <th>Proforma externa</th>
                            <th>Cliente</th>
                            <th>Fecha de creación</th>
                            <th>Entrega prevista</th>
                            <th>Productos</th>
                            <th class="text-right">Unidades</th>
                            <th class="text-right">Importe</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($filteredContracts as $contract)
                            <tr>
                                <td>#{{ $contract->id }}</td>

                                <td>
                                    {{ filled($contract->external_id) ? $contract->external_id : '—' }}
                                </td>

                                <td>
                                    {{ $contract->customer?->name
                                        ?? $contract->customer?->organization
                                        ?? 'Cliente anónimo' }}
                                </td>

                                <td>
                                    {{ $contract->created_at
                                        ? $contract->created_at->format('d/m/Y')
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $contract->delivery_date
                                        ? \Carbon\Carbon::parse($contract->delivery_date)->format('d/m/Y')
                                        : '—' }}
                                </td>

                                <td>
                                    @forelse ($contract->details as $detail)
                                        <div class="mb-1">
                                            <span class="badge badge-light">
                                                {{ $detail->product?->cod ?? '—' }}
                                            </span>

                                            {{ $detail->product?->name ?? 'Producto eliminado' }}
                                            <small class="text-muted">
                                                ({{ number_format($detail->quantity) }})
                                            </small>
                                        </div>
                                    @empty
                                        <span class="text-muted">
                                            Sin productos
                                        </span>
                                    @endforelse
                                </td>

                                <td class="text-right">
                                    {{ number_format($contract->details->sum('quantity')) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $contract->details->sum(
                                            fn ($detail) =>
                                                (float) $detail->quantity
                                                * (float) $detail->price
                                        ),
                                        2
                                    ) }}
                                </td>

                                <td>
                                    {{ $contract->priority ?? '—' }}
                                </td>

                                <td>
                                    @php
                                        $statusValue = $contract->status instanceof \App\Enums\Status
                                            ? $contract->status->value
                                            : (is_numeric($contract->status)
                                                ? (int) $contract->status
                                                : null);

                                        $contractStatus = $statusValue !== null
                                            ? \App\Enums\Status::tryFrom($statusValue)
                                            : null;
                                    @endphp

                                    @if ($contractStatus)
                                        <span class="badge {{ $contractStatus->badgeClass() }}">
                                            {{ $contractStatus->label() }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            Desconocido
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No se encontraron contratos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($filteredContracts->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="6">Totales mostrados</td>

                                <td class="text-right">
                                    {{ number_format(
                                        $filteredContracts->sum(
                                            fn ($contract) =>
                                                $contract->details->sum('quantity')
                                        )
                                    ) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format(
                                        $filteredContracts->sum(
                                            fn ($contract) =>
                                                $contract->details->sum(
                                                    fn ($detail) =>
                                                        (float) $detail->quantity
                                                        * (float) $detail->price
                                                )
                                        ),
                                        2
                                    ) }}
                                </td>

                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="mt-3 text-right">
                <strong>Importe total de los contratos filtrados:</strong>
                {{ number_format(
                    $filteredContracts->sum(
                        fn ($contract) =>
                            $contract->details->sum(
                                fn ($detail) =>
                                    (float) $detail->quantity
                                    * (float) $detail->price
                            )
                    ),
                    2
                ) }}
            </div>
        </div>
    </div>
</div>
