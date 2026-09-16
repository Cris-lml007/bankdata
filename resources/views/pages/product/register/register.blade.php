
<div>
    <form wire:submit="saveProduct">

        <div class="modal-body">

            {{-- Código / Nombre --}}
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cod">Código</label>

                        <input
                            type="text"
                            id="cod"
                            class="form-control @error('product.cod') is-invalid @enderror"
                            wire:model="product.cod"
                            placeholder="Ingrese el código del producto"
                        >

                        @error('product.cod')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nombre</label>

                        <input
                            type="text"
                            id="name"
                            class="form-control @error('product.name') is-invalid @enderror"
                            wire:model="product.name"
                            placeholder="Ingrese el nombre del producto"
                        >

                        @error('product.name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- Modelo / Tipo de fábrica --}}
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="model">Modelo</label>

                        <input
                            type="text"
                            id="model"
                            class="form-control @error('product.model') is-invalid @enderror"
                            wire:model="product.model"
                            placeholder="Ingrese el modelo"
                        >

                        @error('product.model')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type_factory_id">Tipo de fábrica</label>

                        <select
                            id="type_factory_id"
                            class="form-control @error('product.type_factory_id') is-invalid @enderror"
                            wire:model="product.type_factory_id"
                        >
                            <option value="">Seleccione el tipo de fábrica</option>

                            @foreach ($typeFactories ?? [] as $typeFactory)
                                <option value="{{ $typeFactory->id }}">
                                    {{ $typeFactory->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('product.type_factory_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- Costo de producción / Precio de venta --}}
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="purchase_price">Costo de producción</label>

                        <input
                            type="number"
                            step="0.01"
                            id="purchase_price"
                            class="form-control @error('product.purchase_price') is-invalid @enderror"
                            wire:model="product.price_purchase"
                            placeholder="Ingrese el costo de producción"
                        >

                        @error('product.price_purchase')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price">Precio de venta</label>

                        <input
                            type="number"
                            step="0.01"
                            id="price"
                            class="form-control @error('product.price') is-invalid @enderror"
                            wire:model="product.price_sale"
                            placeholder="Ingrese el precio de venta"
                        >

                        @error('product.price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- Descripción --}}
            <div class="row">

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="description">Descripción</label>

                        <textarea
                            id="description"
                            rows="4"
                            class="form-control @error('product.description') is-invalid @enderror"
                            wire:model="product.description"
                            placeholder="Ingrese una descripción del producto"
                        ></textarea>

                        @error('product.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

        </div>

        <div class="modal-footer">

            <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Guardar
            </button>

        </div>

    </form>


</div>
