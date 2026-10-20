
<div class="container-fluid">

    {{-- ENCABEZADO --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $customer->name }}</h4>
                <small class="text-muted">
                    Información comercial, compras y contratos del cliente
                </small>
            </div>

            <a href="{{ route('dashboard.customers') }}"
               class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>

    {{-- DATOS DEL CLIENTE --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user mr-1"></i>
                Datos del cliente
            </h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-3">
                    <small class="text-muted d-block">Nombre</small>
                    <strong>{{ $customer->name }}</strong>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <small class="text-muted d-block">Organización</small>
                    <strong>{{ $customer->organization ?: '—' }}</strong>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <small class="text-muted d-block">CI / NIT</small>
                    <strong>{{ $customer->ci_nit ?: '—' }}</strong>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <small class="text-muted d-block">Teléfono</small>
                    <strong>{{ $customer->phone ?: '—' }}</strong>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <small class="text-muted d-block">Correo electrónico</small>
                    <strong>{{ $customer->email ?: '—' }}</strong>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <small class="text-muted d-block">Contratos registrados</small>
                    <h5 class="mb-0">
                        <span class="badge badge-info">
                            {{ $this->contracts->count() }}
                        </span>
                    </h5>
                </div>
            </div>
        </div>
    </div>

    {{-- ESTADÍSTICAS GLOBALES --}}
    <div class="row">

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $this->contracts->count() }}</h3>
                    <p>Contratos realizados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-contract"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $this->formatAmount($this->totalAmount) }}</h3>
                    <p>Importe total contratado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($this->totalUnits, 0, ',', '.') }}</h3>
                    <p>Unidades contratadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $this->topProducts->count() }}</h3>
                    <p>Productos en el ranking</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- PRODUCTOS MÁS COMPRADOS --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trophy mr-1"></i>
                Productos más comprados
            </h3>
        </div>

        <div class="card-body">

            {{-- BUSCADOR DE PRODUCTOS --}}
            <div class="form-group">
                <label for="productSearch">
                    Buscar productos y etiquetas
                </label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>

                    <input
                        id="productSearch"
                        type="search"
                        class="form-control"
                        placeholder="Nombre, código, modelo o tags..."
                        wire:model.live.debounce.300ms="productSearch"
                    />

                    @if ($productSearch !== '')
                        <div class="input-group-append">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                wire:click="$set('productSearch', '')"
                                title="Limpiar búsqueda"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <small class="text-muted">
                    Puedes buscar varias palabras. También se buscan
                    los nombres y valores de las etiquetas de cada detalle.
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px">#</th>
                            <th>Producto</th>
                            <th class="text-center">Unidades</th>
                            <th class="text-center">Detalles contratados</th>
                            <th class="text-right">Importe acumulado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->topProducts as $index => $product)
                            <tr wire:key="top-product-{{ $index }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>
                                <td class="text-center">
                                    {{ number_format($product->quantity, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">
                                        {{ $product->purchases }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    {{ $this->formatAmount($product->amount) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="fas fa-search mb-2"></i>
                                    <p class="mb-0">
                                        No se encontraron productos con esa búsqueda.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FECHAS DE ENTREGA --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-alt mr-1"></i>
                Fechas de entrega más frecuentes
            </h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Fecha de entrega</th>
                            <th class="text-center">Contratos</th>
                            <th class="text-right">Importe contratado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->topDeliveryDates as $date)
                            <tr wire:key="delivery-date-{{ $date->date }}">
                                <td>
                                    {{ \Illuminate\Support\Carbon::parse($date->date)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">
                                        {{ $date->contracts }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    {{ $this->formatAmount($date->amount) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    No hay fechas de entrega registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CONTRATOS DEL CLIENTE --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-contract mr-1"></i>
                Contratos asociados
            </h3>

            <div class="card-tools">
                <span class="badge badge-primary">
                    {{ $this->filteredContracts->count() }}
                    de {{ $this->contracts->count() }} contratos
                </span>
            </div>
        </div>

        <div class="card-body">

            {{-- BUSCADOR DE CONTRATOS --}}
            <div class="form-group">
                <label for="contractSearch">
                    Buscar contratos
                </label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>

                    <input
                        id="contractSearch"
                        type="search"
                        class="form-control"
                        placeholder="N.º contrato, proforma, estado, producto, etiqueta..."
                        wire:model.live.debounce.300ms="contractSearch"
                    />

                    @if ($contractSearch !== '')
                        <div class="input-group-append">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                wire:click="$set('contractSearch', '')"
                                title="Limpiar búsqueda"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <small class="text-muted">
                    Busca por varios términos. Se revisan los datos del contrato,
                    su área, prioridad, estado, productos y etiquetas de los detalles.
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Contrato</th>
                            <th>Proforma externa</th>
                            <th>Área</th>
                            <th>Fecha de entrega</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Productos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->filteredContracts as $contract)
                            @php
                                $visibleDetails = $this->filteredDetails($contract);
                            @endphp

                            <tr wire:key="contract-{{ $contract->id }}">
                                <td>
                                    <strong>#{{ $contract->id }}</strong>
                                </td>

                                <td>
                                    {{ $contract->external_id ?: '—' }}
                                </td>

                                <td>
                                    {{ $contract->industry?->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $contract->delivery_date
                                        ? \Illuminate\Support\Carbon::parse($contract->delivery_date)->format('d/m/Y')
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $this->priorityLabel($contract->priority) }}
                                </td>

                                <td>
                                    {{ $this->statusLabel($contract->status) }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-info">
                                        {{ $contract->details_count }}
                                    </span>

                                    @if ($productSearch !== '')
                                        <small class="d-block text-muted">
                                            {{ $visibleDetails->count() }} coincidentes
                                        </small>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a
                                        href="{{ route('dashboard.contracts.view', $contract) }}"
                                        class="btn btn-sm btn-primary"
                                        title="Ver contrato"
                                    >
                                        <i class="fas fa-eye"></i>
                                        Ver contrato
                                    </a>
                                </td>
                            </tr>

                            {{-- DETALLES Y ETIQUETAS --}}
                            @if ($visibleDetails->isNotEmpty())
                                <tr wire:key="contract-details-{{ $contract->id }}">
                                    <td colspan="8" class="bg-light">
                                        <strong>
                                            <i class="fas fa-boxes mr-1"></i>
                                            Productos del contrato #{{ $contract->id }}
                                        </strong>

                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-right">Precio unitario</th>
                                                        <th class="text-right">Subtotal</th>
                                                        <th>Etiquetas</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($visibleDetails as $detail)
                                                        <tr wire:key="detail-{{ $detail->id }}">
                                                            <td>
                                                                {{ $detail->product?->name ?? 'Producto eliminado' }}
                                                            </td>

                                                            <td class="text-center">
                                                                {{ number_format((float) $detail->quantity, 0, ',', '.') }}
                                                            </td>

                                                            <td class="text-right">
                                                                {{ $this->formatAmount($detail->price ?? 0) }}
                                                            </td>

                                                            <td class="text-right">
                                                                {{ $this->formatAmount(
                                                                    (float) ($detail->price ?? 0)
                                                                    * (float) ($detail->quantity ?? 0)
                                                                ) }}
                                                            </td>

                                                            <td>
                                                                @forelse ($detail->tags as $tag)
                                                                    <span class="badge badge-secondary mr-1 mb-1">
                                                                        {{ $tag->name }}

                                                                        @if (filled($tag->value))
                                                                            : {{ $tag->value }}
                                                                        @endif
                                                                    </span>
                                                                @empty
                                                                    <span class="text-muted">
                                                                        Sin etiquetas
                                                                    </span>
                                                                @endforelse
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x mb-2"></i>
                                    <p class="mb-0">
                                        No se encontraron contratos con esa búsqueda.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
