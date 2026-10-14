<div class="container-fluid">

    {{-- ENCABEZADO --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">
                    {{ $customer->name }}
                </h4>

                <small class="text-muted">
                    Información del cliente y sus contratos
                </small>
            </div>

            <a
                href="{{ route('dashboard.customers') }}"
                class="btn btn-secondary"
            >
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>

    {{-- INFORMACIÓN DEL CLIENTE --}}
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
                    <small class="text-muted d-block">Total de contratos</small>
                    <h5 class="mb-0">
                        <span class="badge badge-info">
                            {{ $this->contracts->count() }}
                        </span>
                    </h5>
                </div>

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
                    {{ $this->contracts->count() }} contratos
                </span>
            </div>
        </div>

        <div class="card-body">

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
                        @forelse ($this->contracts as $contract)
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
                                    {{ $contract->priority ?? '—' }}
                                </td>

                                <td>
                                    {{ $this->statusLabel($contract->status) }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-info">
                                        {{ $contract->details_count }}
                                    </span>
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

                            @if ($contract->details->isNotEmpty())
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
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($contract->details as $detail)
                                                        <tr wire:key="detail-{{ $detail->id }}">
                                                            <td>
                                                                {{ $detail->product?->name ?? 'Producto eliminado' }}
                                                            </td>

                                                            <td class="text-center">
                                                                {{ $detail->quantity }}
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
                                    <i class="fas fa-file-contract fa-2x mb-2"></i>
                                    <p class="mb-0">
                                        Este cliente todavía no tiene contratos asociados.
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
