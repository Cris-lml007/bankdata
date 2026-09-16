<x-slot name="header">
    <h1>Nuevo Contrato</h1>
</x-slot>

<div>

    {{-- Información del cliente --}}
    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-user"></i>
                Cliente
            </h3>
        </div>

        <div class="card-body">

            <div class="row">

                {{-- CI / NIT --}}
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
                                    Si deja este campo vacío,
                                    el contrato será para cliente anónimo.
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

                <div class="col-md-4">

                    <div class="form-group">

                        <label>Fecha de entrega</label>

                        <input
                            type="date"
                            wire:model="contract.delivery_date"
                            class="form-control @error('contract.delivery_date') is-invalid @enderror"
                        >

                        @error('contract.delivery_date')
                        <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>

            </div>

        </div>
    </div>


    {{-- Agregar producto --}}
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


                {{-- Botón --}}
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
                        <th width="80">Acción</th>
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

                            <td>
                                {{ $item['quantity'] }}
                            </td>

                            <td>
                                Bs {{ number_format($item['price'], 2) }}
                            </td>

                            <td>
                                Bs {{ number_format(
                                        $item['quantity'] * $item['price'],
                                        2
                                    ) }}
                            </td>

                            <td>
                                <button
                                    type="button"
                                    wire:click="removeProduct({{ $index }})"
                                    class="btn btn-danger btn-sm"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="text-center text-muted py-4"
                            >
                                No hay productos agregados.
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

                            <th></th>

                        </tr>

                        </tfoot>

                    @endif

                </table>

            </div>
        </div>
    </div>


    {{-- Acciones --}}
    <div class="card">

        <div class="card-body text-right">

            <a
                href="{{ route('dashboard.contracts') }}"
                class="btn btn-secondary"
            >
                <i class="fa fa-times"></i>
                Cancelar
            </a>

            <button
                type="button"
                wire:click="save"
                class="btn btn-success"
            >
                <i class="fa fa-save"></i>
                Crear Contrato
            </button>

        </div>

    </div>

</div>
