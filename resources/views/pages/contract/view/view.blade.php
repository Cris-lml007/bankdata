<x-slot name="header">
    <h1>Contrato #{{ $contract->id }}</h1>
</x-slot>

<div>

    {{-- Barra de acciones --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <a
            href="{{ route('dashboard.contracts') }}"
            class="btn btn-secondary"
        >
            <i class="fa fa-arrow-left"></i>
            Volver
        </a>

        @if (!$editingContract && $contract->status == \App\Enums\Status::DELIVERED)

            <div class="">
                <a
                    type="button"
                    href="{{ route('dashboard.contracts.planning',$contract->id) }}"
                    class="btn btn-primary"
                >
                    <i class="fa fa-edit"></i>
                    Planificar
                </a>
                <button
                    type="button"
                    wire:click="editContract"
                    class="btn btn-warning"
                >
                    <i class="fa fa-edit"></i>
                    Editar
                </button>

            </div>

        @endif

    </div>


    {{-- Cliente --}}
    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-user"></i>
                Cliente
            </h3>
        </div>

        <div class="card-body">

            @if ($editingContract)

                {{-- CI / NIT --}}
                <div class="row">

                    <div class="col-md-5">

                        <div class="form-group">

                            <label>CI / NIT</label>

                            <div class="input-group">

                                <input
                                    type="text"
                                    wire:model="customer.ci_nit"
                                    class="form-control @error('customer.ci_nit') is-invalid @enderror"
                                    placeholder="Ingrese CI o NIT"
                                >

                                <div class="input-group-append">

                                    <button
                                        type="button"
                                        wire:click="searchCustomer"
                                        class="btn btn-primary"
                                    >
                                        <i class="fa fa-search"></i>
                                        Buscar
                                    </button>

                                </div>

                            </div>

                            @error('customer.ci_nit')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                            <div style="height: 21px;">

                                @if ($existingCustomer)

                                    <small class="text-success">
                                        <i class="fa fa-check-circle"></i>
                                        Cliente encontrado
                                    </small>

                                @elseif (trim($customer['ci_nit']) !== '')

                                    <small class="text-info">
                                        <i class="fa fa-info-circle"></i>
                                        Cliente nuevo
                                    </small>

                                @else

                                    <small class="text-muted">
                                        Sin CI/NIT = cliente anónimo
                                    </small>

                                @endif

                            </div>

                        </div>

                    </div>

                    {{-- Nombre --}}
                    <div class="col-md-7">

                        <div class="form-group">

                            <label>Nombre</label>

                            <input
                                type="text"
                                wire:model="customer.name"
                                class="form-control @error('customer.name') is-invalid @enderror"
                                placeholder="Nombre del cliente"
                            >

                            @error('customer.name')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                </div>

                <div class="row">

                    {{-- Organización --}}
                    <div class="col-md-5">

                        <div class="form-group">

                            <label>Organización</label>

                            <input
                                type="text"
                                wire:model="customer.organization"
                                class="form-control @error('customer.organization') is-invalid @enderror"
                                placeholder="Empresa / Organización"
                            >

                            @error('customer.organization')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                    {{-- Email --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Email</label>

                            <input
                                type="email"
                                wire:model="customer.email"
                                class="form-control @error('customer.email') is-invalid @enderror"
                                placeholder="correo@ejemplo.com"
                            >

                            @error('customer.email')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                    {{-- Teléfono --}}
                    <div class="col-md-4">

                        <div class="form-group">

                            <label>Teléfono</label>

                            <input
                                type="text"
                                wire:model="customer.phone"
                                class="form-control @error('customer.phone') is-invalid @enderror"
                                placeholder="Teléfono"
                            >

                            @error('customer.phone')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                </div>

            @else

                @if ($contract->customer)

                    <div class="row">

                        <div class="col-md-3">

                            <strong>CI / NIT</strong>

                            <div class="text-muted">
                                {{ $contract->customer->ci_nit }}
                            </div>

                        </div>

                        <div class="col-md-3">

                            <strong>Nombre</strong>

                            <div class="text-muted">
                                {{ $contract->customer->name }}
                            </div>

                        </div>

                        <div class="col-md-3">

                            <strong>Organización</strong>

                            <div class="text-muted">
                                {{ $contract->customer->organization }}
                            </div>

                        </div>

                        <div class="col-md-3">

                            <strong>Teléfono</strong>

                            <div class="text-muted">
                                {{ $contract->customer->phone ?? '-' }}
                            </div>

                        </div>

                    </div>

                    @if ($contract->customer->email)

                        <div class="mt-3">

                            <strong>Email:</strong>

                            <span class="text-muted">
                                {{ $contract->customer->email }}
                            </span>

                        </div>

                    @endif

                @else

                    <div class="alert alert-secondary mb-0">

                        <i class="fa fa-user-slash"></i>

                        Este contrato corresponde a un
                        <strong>cliente anónimo</strong>.

                    </div>

                @endif

            @endif

        </div>

    </div>


    {{-- Información del contrato --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-file-signature"></i>
                Información del Contrato
            </h3>

        </div>

        <div class="card-body">

            <div class="row">

                {{-- Fecha de entrega --}}
                <div class="col-md-4">

                    <div class="form-group">

                        <label>Fecha de entrega: Entregado</label>

                        @if ($editingContract)

                            <input
                                type="date"
                                wire:model="contractData.delivery_date"
                                class="form-control @error('contractData.delivery_date') is-invalid @enderror"
                            >

                            @error('contractData.delivery_date')
                            <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror

                        @else

                            <div class="text-muted">

                                @if ($contract->delivery_date)

                                {{ \Carbon\Carbon::parse($contract->delivery_date)->format('d/m/Y') }}:@if($contract->status == \App\Enums\Status::DELIVERED) {{ \Carbon\Carbon::parse($contract->updated_at)->format('d/m/Y') }}@else --- @endif

                                @else

                                    Sin fecha

                                @endif

                            </div>

                        @endif

                    </div>

                </div>

                {{-- Estado --}}
                <div class="col-md-4">

                    <div class="form-group">

                        <label>Estado</label>

                        <div>
            <span class="badge {{ $contract->status->badgeClass() }}">
                {{ $contract->status->label() }}
            </span>
                        </div>

                    </div>

                </div>

                {{-- Cotización --}}
                <div class="col-md-4">

                    <div class="form-group">

                        <label>Cotización de origen</label>

                        @if ($contract->quotation)

                            <div>

                                <a
                                    href="{{ route('dashboard.quotations.view', $contract->quotation) }}"
                                >
                                    Cotización #{{ $contract->quotation->id }}
                                </a>

                            </div>

                        @else

                            <div class="text-muted">
                                Contrato directo
                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card card-primary">

        <div class="card-header">
            <h3 class="card-title">
                Progreso de producción
            </h3>
        </div>

        <div class="card-body">

            <div class="d-flex justify-content-between mb-1">
                <strong>
                    Producción total
                </strong>

                <span>
                {{ $productionCompleted }} / {{ $productionTotal }}
                ({{ $productionPercentage }}%)
            </span>
            </div>

            <div class="progress mb-4" style="height: 25px;">

                <div
                    class="progress-bar
                    @if ($productionPercentage >= 100)
                        bg-success
                    @elseif ($productionPercentage > 0)
                        bg-primary
                    @else
                        bg-secondary
                    @endif"
                    role="progressbar"
                    style="width: {{ $productionPercentage }}%;"
                >
                    {{ $productionPercentage }}%
                </div>

            </div>

            @foreach ($productionProgress as $item)

                <div class="mb-4">

                    <div class="d-flex justify-content-between mb-1">

                        <strong>
                            {{ $item['product_name'] }}
                        </strong>

                        <span>
                        {{ $item['completed'] }}
                        /
                        {{ $item['required'] }}
                    </span>

                    </div>

                    <div class="progress" style="height: 20px;">

                        <div
                            class="progress-bar
                            @if ($item['percentage'] >= 100)
                                bg-success
                            @elseif ($item['percentage'] > 0)
                                bg-info
                            @else
                                bg-secondary
                            @endif"
                            role="progressbar"
                            style="width: {{ $item['percentage'] }}%;"
                        >
                            {{ $item['percentage'] }}%
                        </div>

                    </div>

                    <small class="text-muted">
                        Pendiente: {{ $item['pending'] }}
                    </small>

                </div>

            @endforeach

        </div>

    </div>

    {{-- Productos --}}
    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-box"></i>
                Productos
            </h3>

        </div>

        <div class="card-body p-0">

            @error('products')
            <div class="alert alert-danger m-3">
                {{ $message }}
            </div>
            @enderror

            <div class="table-responsive">

                <table class="table table-bordered table-hover mb-0">

                    <thead>

                    <tr>

                        <th>Código</th>
                        <th>Producto</th>
                        <th width="140">Cantidad</th>
                        <th width="180">Precio unitario</th>
                        <th width="180">Subtotal</th>

                        @if ($editingContract)
                            <th width="80">Acción</th>
                        @endif

                    </tr>

                    </thead>

                    <tbody>

                    @forelse ($products as $index => $item)

                        <tr wire:key="contract-product-{{ $index }}">

                            <td>
                                {{ $item['cod'] }}
                            </td>

                            <td>
                                {{ $item['name'] }}
                            </td>

                            @if ($editingContract)

                                <td>

                                    <input
                                        type="number"
                                        min="1"
                                        wire:model.live="products.{{ $index }}.quantity"
                                        class="form-control"
                                    >

                                </td>

                                <td>

                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model.live="products.{{ $index }}.price"
                                        class="form-control"
                                    >

                                </td>

                            @else

                                <td>
                                    {{ $item['quantity'] }}
                                </td>

                                <td>
                                    Bs {{ number_format($item['price'], 2) }}
                                </td>

                            @endif

                            <td class="align-middle">

                                Bs {{ number_format(
                                        $item['quantity'] * $item['price'],
                                        2
                                    ) }}

                            </td>

                            @if ($editingContract)

                                <td class="align-middle">

                                    <button
                                        type="button"
                                        wire:click="removeProduct({{ $index }})"
                                        class="btn btn-danger btn-sm"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>

                                </td>

                            @endif

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="{{ $editingContract ? 6 : 5 }}"
                                class="text-center text-muted py-4"
                            >
                                No hay productos en este contrato.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                    @if (count($products))

                        <tfoot>

                        <tr>

                            <th colspan="4" class="text-right">
                                Total
                            </th>

                            <th>

                                Bs {{ number_format(
                                        collect($products)->sum(
                                            fn ($item) =>
                                                $item['quantity'] * $item['price']
                                        ),
                                        2
                                    ) }}

                            </th>

                            @if ($editingContract)
                                <th></th>
                            @endif

                        </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>


    {{-- Agregar producto --}}
    @if ($editingContract)

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">
                    <i class="fa fa-plus"></i>
                    Agregar producto
                </h3>

            </div>

            <div class="card-body">

                <div class="row">

                    {{-- Producto --}}
                    <div class="col-md-5">

                        <div class="form-group position-relative">

                            <label>Producto</label>

                            <input
                                type="text"
                                wire:model.live.debounce.300ms="productSearch"
                                class="form-control @error('detail.product_id') is-invalid @enderror"
                                placeholder="Buscar por código o nombre..."
                                autocomplete="off"
                            >

                            @if (count($productResults))

                                <div
                                    class="list-group position-absolute w-100"
                                    style="z-index: 1000;"
                                >

                                    @foreach ($productResults as $product)

                                        <button
                                            type="button"
                                            wire:click="selectProduct({{ $product->id }})"
                                            class="list-group-item list-group-item-action text-left"
                                        >
                                            <strong>
                                                {{ $product->cod }}
                                            </strong>

                                            <span class="ml-2">
                                                {{ $product->name }}
                                            </span>
                                        </button>

                                    @endforeach

                                </div>

                            @endif

                            <div style="height: 21px;">

                                @if ($selectedProduct)

                                    <small class="text-muted">

                                        Producto:
                                        <strong>
                                            {{ $selectedProduct->cod }}
                                        </strong>

                                    </small>

                                @endif

                            </div>

                            @error('detail.product_id')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>


                    {{-- Cantidad --}}
                    <div class="col-md-2">

                        <div class="form-group">

                            <label>Cantidad</label>

                            <input
                                type="number"
                                min="1"
                                wire:model="detail.quantity"
                                class="form-control @error('detail.quantity') is-invalid @enderror"
                            >

                            <div style="height: 21px;"></div>

                            @error('detail.quantity')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>


                    {{-- Precio --}}
                    <div class="col-md-3">

                        <div class="form-group">

                            <label>Precio unitario</label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model="detail.price"
                                class="form-control @error('detail.price') is-invalid @enderror"
                                placeholder="{{ $selectedProduct
                                    ? 'Referencial: Bs ' . number_format($selectedProduct->price_sale, 2)
                                    : 'Precio del contrato' }}"
                            >

                            <div style="height: 21px;">

                                @if ($selectedProduct)

                                    <small class="text-muted">
                                        Referencial:
                                        <strong>
                                            Bs {{ number_format($selectedProduct->price_sale, 2) }}
                                        </strong>
                                    </small>

                                @endif

                            </div>

                            @error('detail.price')
                            <span class="invalid-feedback d-block">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>


                    {{-- Agregar --}}
                    <div class="col-md-2">

                        <div class="form-group">

                            <label>&nbsp;</label>

                            <button
                                type="button"
                                wire:click="addProduct"
                                class="btn btn-primary btn-block"
                            >
                                <i class="fa fa-plus"></i>
                                Agregar
                            </button>

                            <div style="height: 21px;"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    @endif


    {{-- Acciones --}}
    <div class="card">

        <div class="card-body text-right">

            @if ($editingContract)

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
                    wire:click="updateContract"
                    class="btn btn-success"
                >
                    <i class="fa fa-save"></i>
                    Guardar cambios
                </button>

            @else
                @if ($contract->status === \App\Enums\Status::FINISH)

                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="markAsDelivered"
                    >
                        <i class="fas fa-truck"></i>
                        Marcar como entregado
                    </button>

                @endif

                @if ($contract->status === \App\Enums\Status::ACTIVE && $this->canFinishContract())

                    <button
                        type="button"
                        class="btn btn-success"
                        wire:click="finishContract"
                    >
                        <i class="fas fa-check"></i>
                        Finalizar producción
                    </button>

                @endif

                <button
                    type="button"
                    wire:click="deleteContract"
                    class="btn btn-danger"
                >
                    <i class="fa fa-trash"></i>
                    Eliminar
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                >
                    <i class="fa fa-file-pdf"></i>
                    Generar PDF
                </button>

            @endif

        </div>

    </div>

</div>
