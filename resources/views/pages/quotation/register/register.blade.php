<x-slot name="header">
    <h1>Nueva Cotización</h1>
</x-slot>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-file-invoice"></i>
                Datos de la Cotización
            </h3>
        </div>

        <div class="card-body">

            {{-- Fechas --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Válida desde</label>

                        <input
                            type="date"
                            wire:model="quotation.valid_from"
                            class="form-control @error('quotation.valid_from') is-invalid @enderror"
                        >

                        @error('quotation.valid_from')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Válida hasta</label>

                        <input
                            type="date"
                            wire:model="quotation.valid_to"
                            class="form-control @error('quotation.valid_to') is-invalid @enderror"
                        >

                        @error('quotation.valid_to')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha de entrega</label>

                        <input
                            type="date"
                            wire:model="quotation.delivery_date"
                            class="form-control @error('quotation.delivery_date') is-invalid @enderror"
                        >

                        @error('quotation.delivery_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <hr>

            <h5>Agregar producto</h5>

            <div class="row align-items-end">

                {{-- Agregar producto --}}
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
                                            <strong>{{ $product->cod }}</strong>

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
                                        <strong>{{ $selectedProduct->cod }}</strong>
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

            @error('products')
            <div class="alert alert-danger mt-2">
                {{ $message }}
            </div>
            @enderror

            {{-- Detalle --}}
            @if (count($products))
                <hr>

                <h5>Productos de la cotización</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th width="120">Cantidad</th>
                            <th width="160">Precio</th>
                            <th width="160">Subtotal</th>
                            <th width="80">Acción</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($products as $index => $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['cod'] }}</strong>
                                    <br>
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
                        @endforeach
                        </tbody>

                        <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">
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
                    </table>
                </div>
            @endif

        </div>

        <div class="card-footer text-right">
            <a
                href="{{ route('dashboard.quotations') }}"
                class="btn btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="button"
                wire:click="save"
                class="btn btn-success"
            >
                <i class="fa fa-save"></i>
                Guardar Cotización
            </button>
        </div>
    </div>
</div>
