
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-1">Clientes</h4>
            <small class="text-muted">
                Gestión de clientes y sus contratos
            </small>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users mr-1"></i>
                Listado de clientes
            </h3>

            <div class="card-tools">
                <span class="badge badge-primary">
                    {{ $this->customers->total() }} clientes
                </span>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Buscar cliente..."
                            wire:model.live.debounce.300ms="search"
                        >

                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Organización</th>
                            <th>CI / NIT</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th class="text-center">Contratos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->customers as $customer)
                            <tr wire:key="customer-{{ $customer->id }}">
                                <td>
                                    <strong>{{ $customer->name }}</strong>
                                </td>

                                <td>
                                    {{ $customer->organization ?: '—' }}
                                </td>

                                <td>
                                    {{ $customer->ci_nit ?: '—' }}
                                </td>

                                <td>
                                    {{ $customer->phone ?: '—' }}
                                </td>

                                <td>
                                    {{ $customer->email ?: '—' }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-info">
                                        {{ $customer->contracts_count }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a
                                        href="{{ route('dashboard.customers.view', $customer) }}"
                                        class="btn btn-sm btn-primary"
                                        title="Ver cliente y contratos"
                                    >
                                        <i class="fas fa-eye"></i>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p class="mb-0">
                                        No se encontraron clientes.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $this->customers->links() }}
            </div>

        </div>
    </div>
</div>
