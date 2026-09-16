<x-slot name="header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            Cotización #{{ $quotation->id }}
        </h1>
    </div>
</x-slot>

<div>
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <a
                href="{{ route('dashboard.quotations') }}"
                class="btn btn-secondary"
            >
                <i class="fa fa-arrow-left"></i>
                Volver
            </a>
        </div>

        <div>
            @if (!$editingQuotation)
                <button
                    type="button"
                    wire:click="editQuotation"
                    class="btn btn-warning"
                >
                    <i class="fa fa-edit"></i>
                    Editar
                </button>
            @endif
        </div>

    </div>

    {{-- Información --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-file-invoice"></i>
                Información de la Cotización
            </h3>
        </div>

        <div class="card-body">

            @if ($editingQuotation)

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Válida desde</label>

                            <input
                                type="date"
                                wire:model="quotationData.valid_from"
                                class="form-control @error('quotationData.valid_from') is-invalid @enderror"
                            >

                            @error('quotationData.valid_from')
                            <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Válida hasta</label>

                            <input
                                type="date"
                                wire:model="quotationData.valid_to"
                                class="form-control @error('quotationData.valid_to') is-invalid @enderror"
                            >

                            @error('quotationData.valid_to')
                            <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha de entrega</label>

                            <input
                                type="date"
                                wire:model="quotationData.delivery_date"
                                class="form-control @error('quotationData.delivery_date') is-invalid @enderror"
                            >

                            @error('quotationData.delivery_date')
                            <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                </div>

            @else

                <div class="row">

                    <div class="col-md-3">
                        <strong>Fecha de emisión</strong>

                        <div class="text-muted">
                            {{ $quotation->created_at->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <strong>Válida desde</strong>

                        <div class="text-muted">
                            {{ \Carbon\Carbon::parse($quotation->valid_from)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <strong>Válida hasta</strong>

                        <div class="text-muted">
                            {{ \Carbon\Carbon::parse($quotation->valid_to)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <strong>Fecha de entrega</strong>

                        <div class="text-muted">
                            {{ \Carbon\Carbon::parse($quotation->delivery_date)->format('d/m/Y') }}
                        </div>
                    </div>

                </div>

            @endif

        </div>
    </div>

    {{-- Agregar producto --}}
    @if ($editingQuotation)
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
                                    : 'Precio de cotización' }}"
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
    @endif

    @if ($convertingToContract)

        {{-- Cliente para el contrato --}}
        <div class="card">

            <div class="card-header">

                <h3 class="card-title">
                    <i class="fa fa-user"></i>
                    Datos del cliente
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
                                    wire:model="contractCustomer.ci_nit"
                                    class="form-control @error('contractCustomer.ci_nit') is-invalid @enderror"
                                    placeholder="Ingrese CI o NIT"
                                >

                                <div class="input-group-append">

                                    <button
                                        type="button"
                                        wire:click="searchContractCustomer"
                                        class="btn btn-primary"
                                    >
                                        <i class="fa fa-search"></i>
                                        Buscar
                                    </button>

                                </div>

                            </div>

                            @error('contractCustomer.ci_nit')

                            <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>

                            @enderror

                            <div style="height: 21px;">

                                @if ($contractCustomerFound)

                                    <small class="text-success">

                                        <i class="fa fa-check-circle"></i>
                                        Cliente encontrado

                                    </small>

                                @elseif (trim($contractCustomer['ci_nit']) !== '')

                                    <small class="text-info">

                                        <i class="fa fa-info-circle"></i>
                                        Cliente nuevo

                                    </small>

                                @else

                                    <small class="text-muted">

                                        <i class="fa fa-user-slash"></i>
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
                                wire:model="contractCustomer.name"
                                class="form-control @error('contractCustomer.name') is-invalid @enderror"
                                placeholder="Nombre del cliente"
                            >

                            @error('contractCustomer.name')

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
                                wire:model="contractCustomer.organization"
                                class="form-control @error('contractCustomer.organization') is-invalid @enderror"
                                placeholder="Empresa / Organización"
                            >

                            @error('contractCustomer.organization')

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
                                wire:model="contractCustomer.email"
                                class="form-control @error('contractCustomer.email') is-invalid @enderror"
                                placeholder="correo@ejemplo.com"
                            >

                            @error('contractCustomer.email')

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
                                wire:model="contractCustomer.phone"
                                class="form-control @error('contractCustomer.phone') is-invalid @enderror"
                                placeholder="Teléfono"
                            >

                            @error('contractCustomer.phone')

                            <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Fecha de entrega --}}
                <div class="row">

                    <div class="col-md-4">

                        <div class="form-group mb-0">

                            <label>Fecha de entrega</label>

                            <input
                                type="date"
                                wire:model="contractDeliveryDate"
                                class="form-control @error('contractDeliveryDate') is-invalid @enderror"
                            >

                            @error('contractDeliveryDate')

                            <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Acciones de conversión --}}
        <div class="card">

            <div class="card-body text-right">

                @error('conversion')

                <div class="alert alert-danger text-left">
                    {{ $message }}
                </div>

                @enderror

                <button
                    type="button"
                    wire:click="cancelConversion"
                    class="btn btn-secondary"
                >
                    <i class="fa fa-times"></i>
                    Cancelar
                </button>

                <button
                    type="button"
                    wire:click="createContractFromQuotation"
                    class="btn btn-success"
                >
                    <i class="fa fa-file-signature"></i>
                    Crear contrato
                </button>

            </div>

        </div>

    @endif

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

                        @if ($editingQuotation)
                            <th width="80">Acción</th>
                        @endif
                    </tr>
                    </thead>

                    <tbody>

                    @forelse ($products as $index => $item)

                        <tr wire:key="quotation-product-{{ $index }}">

                            <td>
                                {{ $item['cod'] }}
                            </td>

                            <td>
                                {{ $item['name'] }}
                            </td>

                            @if ($editingQuotation)

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

                            @if ($editingQuotation)
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
                                colspan="{{ $editingQuotation ? 6 : 5 }}"
                                class="text-center text-muted py-4"
                            >
                                No hay productos en esta cotización.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                    @if (count($products))

                        <tfoot>
                        <tr>
                            <th
                                colspan="{{ $editingQuotation ? 4 : 4 }}"
                                class="text-right"
                            >
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

                            @if ($editingQuotation)
                                <th></th>
                            @endif
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

            @if ($editingQuotation)

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
                    wire:click="updateQuotation"
                    class="btn btn-success"
                >
                    <i class="fa fa-save"></i>
                    Guardar cambios
                </button>

            @else

                <button
                    type="button"
                    wire:click="deleteQuotation"
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

                <button
                    type="button"
                    wire:click="convertToContract"
                    class="btn btn-success"
                >
                    <i class="fa fa-file-signature"></i>
                    Convertir a contrato
                </button>
            @endif

        </div>

    </div>

</div>
