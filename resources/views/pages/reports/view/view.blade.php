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
    {{-- TABLA 1: PRODUCTOS ORDENADOS CON SUBFILAS POR ORDEN       --}}
    {{-- ========================================================= --}}

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Productos ordenados</h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="search" class="form-control"
                        placeholder="Buscar producto, código o ID..."
                        wire:model.live.debounce.300ms="searchOrderedProducts">
                </div>
                <div class="col-md-7 text-md-right mt-2 mt-md-0">
                    <span class="text-muted">Productos encontrados:
                        <strong>{{ $filteredOrderedProducts->count() }}</strong>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID producto</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>ID orden</th>
                            <th>ID externo</th>
                            <th>Datos</th>
                            <th class="text-right">Solicitado</th>
                            <th class="text-right">Precio</th>
                            <th class="text-right">Fabricado</th>
                            <th class="text-right">Entregado</th>
                            <th class="text-right">Faltante</th>
                            <th style="min-width:150px">Progreso</th>
                        </tr>
                        <tr class="bg-light">
                            @foreach ([
                                'product_id' => 'ID producto',
                                'cod' => 'Código',
                                'name' => 'Producto',
                                'contract_id' => 'ID orden',
                                'external_id' => 'ID externo',
                                'tags_text' => 'Datos',
                                'requested' => 'Solicitado',
                                'price' => 'Precio',
                                'produced' => 'Fabricado',
                                'delivered' => 'Entregado',
                                'pending' => 'Faltante',
                                'progress' => 'Progreso',
                            ] as $key => $placeholder)
                                <th>
                                    <input type="search" class="form-control form-control-sm"
                                        placeholder="Filtrar {{ $placeholder }}"
                                        aria-label="Filtrar {{ $placeholder }}"
                                        wire:model.live.debounce.300ms="orderedColumnFilters.{{ $key }}">
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filteredOrderedProducts as $product)
                            <tr class="table-info font-weight-bold">
                                <td>{{ $product['product_id'] }}</td>
                                <td>
                                    <a href="#" wire:click.prevent="showProductDetails({{ $product['order_rows']->first()['contract_id'] }}, {{ $product['order_rows']->first()['detail_id'] }})">
                                        {{ $product['cod'] }}
                                    </a>
                                </td>
                                <td>
                                    <a href="#" wire:click.prevent="showProductDetails({{ $product['order_rows']->first()['contract_id'] }}, {{ $product['order_rows']->first()['detail_id'] }})">
                                        {{ $product['name'] }}
                                    </a>
                                    <small class="d-block text-muted">{{ $product['orders'] }} línea(s) de orden</small>
                                </td>
                                <td colspan="3" class="text-muted">Total del producto</td>
                                <td class="text-right">{{ number_format($product['requested']) }}</td>
                                <td class="text-right">—</td>
                                <td class="text-right text-success">{{ number_format($product['produced']) }}</td>
                                <td class="text-right text-primary">{{ number_format($product['delivered']) }}</td>
                                <td class="text-right text-danger">{{ number_format($product['pending']) }}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $product['progress'] }}%"
                                            aria-valuenow="{{ $product['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small>{{ number_format($product['progress'], 1) }}%</small>
                                </td>
                            </tr>

                            @foreach (($product['visible_order_rows'] ?? $product['order_rows']) as $order)
                                @php
                                    $orderProgress = $order['requested'] > 0
                                        ? min(100, round(($order['produced'] / $order['requested']) * 100, 1))
                                        : 0;
                                @endphp
                                <tr>
                                    <td>{{ $order['product_id'] }}</td>
                                    <td>
                                        <a href="#" wire:click.prevent="showProductDetails({{ $order['contract_id'] }}, {{ $order['detail_id'] }})">
                                            {{ $order['product_cod'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#" wire:click.prevent="showProductDetails({{ $order['contract_id'] }}, {{ $order['detail_id'] }})">
                                            {{ $order['product_name'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#" wire:click.prevent="showProductDetails({{ $order['contract_id'] }}, {{ $order['detail_id'] }})">
                                            #{{ $order['contract_id'] }}
                                        </a>
                                    </td>
                                    <td>{{ filled($order['external_id']) ? $order['external_id'] : '—' }}</td>
                                    <td>
                                        @forelse ($order['tags'] ?? [] as $tag)
                                            <span class="badge badge-secondary mr-1 mb-1">{{ $tag['name'] }}: {{ $tag['value'] }}</span>
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </td>
                                    <td class="text-right">{{ number_format($order['requested']) }}</td>
                                    <td class="text-right">{{ number_format($order['price'], 2) }}</td>
                                    <td class="text-right text-success">{{ number_format($order['produced']) }}</td>
                                    <td class="text-right text-primary">{{ number_format($order['delivered']) }}</td>
                                    <td class="text-right text-danger">{{ number_format($order['pending']) }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $orderProgress }}%"
                                                aria-valuenow="{{ $orderProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small>{{ number_format($orderProgress, 1) }}%</small>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="12" class="text-center text-muted">No se encontraron productos.</td></tr>
                        @endforelse
                    </tbody>
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
                        <tr class="bg-light">
                            @foreach ([
                                'contract_id' => 'Contrato',
                                'external_id' => 'Proforma externa',
                                'customer' => 'Cliente',
                                'destination' => 'Destino',
                                'product_cod' => 'Código',
                                'product_name' => 'Producto',
                                'requested' => 'Solicitado',
                                'produced' => 'Fabricado',
                                'delivered' => 'Entregado',
                                'pending' => 'Faltante',
                                'priority' => 'Prioridad',
                                'status_label' => 'Estado',
                                'delivery_date' => 'Entrega',
                            ] as $key => $placeholder)
                                <th><input type="search" class="form-control form-control-sm"
                                    placeholder="Filtrar {{ $placeholder }}"
                                    wire:model.live.debounce.300ms="productionColumnFilters.{{ $key }}"></th>
                            @endforeach
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
                                <td><a href="#" wire:click.prevent="showProductDetails({{ $row['contract_id'] }}, {{ $row['detail_id'] }})">{{ $row['product_cod'] }}</a></td>
                                <td><a href="#" wire:click.prevent="showProductDetails({{ $row['contract_id'] }}, {{ $row['detail_id'] }})">{{ $row['product_name'] }}</a></td>

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
                            {{ $row['priority']->label() }}
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
                                    {{ $row['delivery_date'] ? $row['delivery_date'] : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted">
                                    No se encontraron registros de producción.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($filteredProductionRows->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="6">Totales mostrados</td>

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
                        <tr class="bg-light">
                            @foreach ([
                                'industry_id' => 'Para área',
                                'industry_name' => 'Área final',
                                'product_cod' => 'Código',
                                'product_name' => 'Producto',
                                'contracts' => 'Contratos',
                                'requested' => 'Solicitado',
                                'produced' => 'Fabricado',
                                'delivered' => 'Entregado',
                                'pending' => 'Faltante',
                                'progress' => 'Progreso',
                            ] as $key => $placeholder)
                                <th><input type="search" class="form-control form-control-sm"
                                    placeholder="Filtrar {{ $placeholder }}"
                                    wire:model.live.debounce.300ms="industryColumnFilters.{{ $key }}"></th>
                            @endforeach
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

                                <td><a href="#" wire:click.prevent="showProductDetails({{ $row['contract_id'] }}, {{ $row['detail_id'] }})">{{ $row['product_cod'] }}</a></td>
                                <td><a href="#" wire:click.prevent="showProductDetails({{ $row['contract_id'] }}, {{ $row['detail_id'] }})">{{ $row['product_name'] }}</a></td>

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
                        <tr class="bg-light">
                            @foreach ([
                                'contract_id' => 'ID contrato',
                                'external_id' => 'Proforma externa',
                                'customer' => 'Cliente',
                                'created_at' => 'Creación',
                                'delivery_date' => 'Entrega',
                                'products' => 'Productos',
                                'units' => 'Unidades',
                                'amount' => 'Importe',
                                'priority' => 'Prioridad',
                                'status_label' => 'Estado',
                            ] as $key => $placeholder)
                                <th><input type="search" class="form-control form-control-sm"
                                    placeholder="Filtrar {{ $placeholder }}"
                                    wire:model.live.debounce.300ms="contractsColumnFilters.{{ $key }}"></th>
                            @endforeach
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
                                            <a href="#" wire:click.prevent="showProductDetails({{ $contract->id }}, {{ $detail->id }})">
                                                <span class="badge badge-primary">{{ $detail->product?->cod ?? '—' }}</span>
                                            </a>
                                            <a href="#" wire:click.prevent="showProductDetails({{ $contract->id }}, {{ $detail->id }})">
                                                {{ $detail->product?->name ?? 'Producto eliminado' }}
                                            </a>
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
                                        {{ $contract->priority->label() ?? '—' }}
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


    {{-- Popup con los datos del producto y las etiquetas de DetailContract --}}
    @if ($productPopup)
        <div class="position-fixed w-100 h-100 d-flex align-items-center justify-content-center"
            style="inset:0; z-index:1050; background:rgba(0,0,0,.55); padding:1rem;"
            wire:click.self="closeProductDetails">
            <div class="card card-primary card-outline shadow-lg" style="width:100%; max-width:650px; max-height:90vh; overflow:auto;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-box-open mr-2"></i>Detalle del producto en la orden #{{ $productPopup['contract_id'] }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" wire:click="closeProductDetails" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-3">
                        <dt class="col-sm-4">ID de orden</dt><dd class="col-sm-8">#{{ $productPopup['contract_id'] }}</dd>
                        <dt class="col-sm-4">ID externo</dt><dd class="col-sm-8">{{ filled($productPopup['external_id']) ? $productPopup['external_id'] : '—' }}</dd>
                        <dt class="col-sm-4">ID DetailContract</dt><dd class="col-sm-8">{{ $productPopup['detail_id'] }}</dd>
                        <dt class="col-sm-4">ID producto</dt><dd class="col-sm-8">{{ $productPopup['product_id'] }}</dd>
                        <dt class="col-sm-4">Código</dt><dd class="col-sm-8">{{ $productPopup['cod'] }}</dd>
                        <dt class="col-sm-4">Producto</dt><dd class="col-sm-8">{{ $productPopup['name'] }}</dd>
                        <dt class="col-sm-4">Cantidad solicitada</dt><dd class="col-sm-8">{{ number_format($productPopup['quantity']) }}</dd>
                        <dt class="col-sm-4">Precio unitario</dt><dd class="col-sm-8">{{ number_format($productPopup['price'], 2) }}</dd>
                    </dl>
                    <h5><i class="fas fa-tags mr-1"></i>Datos (etiquetas del DetailContract)</h5>
                    @forelse ($productPopup['tags'] as $tag)
                        <div class="d-flex justify-content-between border rounded p-2 mb-2">
                            <strong>{{ $tag['name'] }}</strong>
                            <span>{{ $tag['value'] }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Este producto no tiene datos/etiquetas personalizados en esta orden.</p>
                    @endforelse
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-secondary" wire:click="closeProductDetails">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
