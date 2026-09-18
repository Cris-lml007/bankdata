<x-slot name="header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>{{ $product->name }}</h1>

            <small class="text-muted">
                Código: {{ $product->cod }}
            </small>
        </div>

        <a
            href="{{ route('dashboard.products') }}"
            class="btn btn-secondary"
        >
            <i class="fa fa-arrow-left"></i>
            Volver
        </a>
    </div>
</x-slot>

<div>

    {{-- INFORMACIÓN DEL PRODUCTO --}}
    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-box"></i>
                Información del Producto
            </h3>

            @if (!$editingProduct)
                <div class="card-tools">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        wire:click="editProduct"
                    >
                        <i class="fa fa-edit"></i>
                        Editar
                    </button>
                </div>
            @endif
        </div>

        <div class="card-body">

            @if ($editingProduct)

                <form wire:submit="updateProduct">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Código</label>

                                <input
                                    type="text"
                                    class="form-control @error('productData.cod') is-invalid @enderror"
                                    wire:model="productData.cod"
                                >

                                @error('productData.cod')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Nombre</label>

                                <input
                                    type="text"
                                    class="form-control @error('productData.name') is-invalid @enderror"
                                    wire:model="productData.name"
                                >

                                @error('productData.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Modelo</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model="productData.model"
                                >

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Tipo de fábrica</label>

                                <select
                                    class="form-control @error('productData.type_factory_id') is-invalid @enderror"
                                    wire:model="productData.type_factory_id"
                                >
                                    <option value="">
                                        Seleccione un tipo de fábrica
                                    </option>

                                    @foreach ($typeFactories as $typeFactory)
                                        <option value="{{ $typeFactory->id }}">
                                            {{ $typeFactory->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('productData.type_factory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Costo de producción</label>

                                <input
                                    type="text"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('productData.price_purchase') is-invalid @enderror"
                                    wire:model="productData.price_purchase"
                                >

                                @error('productData.price_purchase')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Precio de venta</label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('productData.price_sale') is-invalid @enderror"
                                    wire:model="productData.price_sale"
                                >

                                @error('productData.price_sale')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">

                                <label>Descripción</label>

                                <textarea
                                    rows="3"
                                    class="form-control"
                                    wire:model="productData.description"
                                ></textarea>

                            </div>
                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-save"></i>
                        Guardar cambios
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        wire:click="$set('editingProduct', false)"
                    >
                        Cancelar
                    </button>

                </form>

            @else

                <div class="row">

                    <div class="col-md-6">
                        <strong>Código</strong>
                        <p>{{ $product->cod }}</p>
                    </div>

                    <div class="col-md-6">
                        <strong>Nombre</strong>
                        <p>{{ $product->name }}</p>
                    </div>

                    <div class="col-md-6">
                        <strong>Modelo</strong>
                        <p>{{ $product->model ?: 'Sin modelo' }}</p>
                    </div>

                    <div class="col-md-6">
                        <strong>Tipo de fábrica</strong>
                        <p>{{ $product->typeFactory?->name ?? 'Sin asignar' }}</p>
                    </div>

                    <div class="col-md-6">
                        <strong>Costo de producción</strong>
                        <p>
                            Bs. {{ number_format($product->price_purchase, 2) }}
                        </p>
                    </div>

                    <div class="col-md-6">
                        <strong>Precio de venta</strong>
                        <p>
                            Bs. {{ number_format($product->price_sale, 2) }}
                        </p>
                    </div>

                    <div class="col-12">
                        <strong>Descripción</strong>
                        <p>
                            {{ $product->description ?: 'Sin descripción' }}
                        </p>
                    </div>

                </div>

            @endif

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- CATEGORÍAS --}}
    {{-- ========================================================= --}}

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-tags"></i>
                Categorías
            </h3>
        </div>

        <div class="card-body">

            <form wire:submit="saveCategories">

                <div class="row">

                    <div class="col-md-8">

                        <label>Categorías</label>

                        <div class="border rounded p-3">

                            <div class="row">

                                @forelse ($categories as $category)

                                    <div class="col-md-4 mb-2">

                                        <div class="custom-control custom-checkbox">

                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="category-{{ $category->id }}"
                                                value="{{ $category->id }}"
                                                wire:model="selectedCategories"
                                            >

                                            <label
                                                class="custom-control-label"
                                                for="category-{{ $category->id }}"
                                            >
                                                {{ $category->name }}
                                            </label>

                                        </div>

                                    </div>

                                @empty

                                    <div class="col-12 text-muted">
                                        No existen categorías registradas.
                                    </div>

                                @endforelse

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <label>Crear categoría</label>

                        <div class="input-group">

                            <input
                                type="text"
                                class="form-control @error('newCategory.name') is-invalid @enderror"
                                wire:model="newCategory.name"
                                placeholder="Nombre de la categoría"
                            >

                            <div class="input-group-append">

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    wire:click="saveCategory"
                                >
                                    <i class="fa fa-plus"></i>
                                </button>

                            </div>

                        </div>

                        @error('newCategory.name')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>

                <div class="mt-3">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-save"></i>
                        Guardar categorías
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- COMPONENTES --}}
    {{-- ========================================================= --}}

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-cubes"></i>
                Proceso de Producción
            </h3>

        </div>

        <div class="card-body">

            <form wire:submit="{{ $editingComponent ? 'updateComponent' : 'saveComponent' }}">
                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">

                            <label>Nombre</label>

                            <input
                                type="text"
                                class="form-control @error('component.name') is-invalid @enderror"
                                wire:model="component.name"
                                placeholder="Ej. Marco"
                            >

                            @error('component.name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">

                            <label>Tipo de fábrica</label>

                            <select
                                class="form-control @error('component.type_factory_id') is-invalid @enderror"
                                wire:model="component.type_factory_id"
                            >
                                <option value="">
                                    Seleccione un tipo de fábrica
                                </option>

                                @foreach ($typeFactories as $typeFactory)

                                    <option value="{{ $typeFactory->id }}">
                                        {{ $typeFactory->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('component.type_factory_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">

                            <label>Descripción</label>

                            <input
                                type="text"
                                class="form-control"
                                wire:model="component.description"
                                placeholder="Descripción del componente"
                            >

                        </div>
                    </div>

                </div>

                @if ($editingComponent)
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-save"></i>
                        Actualizar componente
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        wire:click="resetComponent"
                    >
                        Cancelar
                    </button>
                @else
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-plus"></i>
                        Añadir componente
                    </button>
                @endif
            </form>

        </div>


        @if ($product->componentProducts->count())

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>
                        <tr>
                            <th>Componente</th>
                            <th>Tipo de fábrica</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach ($product->componentProducts as $component)

                            <tr>

                                <td>
                                    {{ $component->name }}
                                </td>

                                <td>
                                    {{ $component->typeFactory?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $component->description ?: '-' }}
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        wire:click="editComponent({{ $component->id }})"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        wire:click="deleteComponent({{ $component->id }})"
                                        wire:confirm="¿Está seguro de eliminar este componente?"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- INFORMACIÓN TÉCNICA --}}
    {{-- ========================================================= --}}

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-list"></i>
                Información técnica
            </h3>

        </div>

        <div class="card-body">

            <form wire:submit="{{ $editingTag ? 'updateTag' : 'saveTag' }}">
                <div class="row">

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>Nombre</label>

                            <input
                                type="text"
                                class="form-control @error('tag.name') is-invalid @enderror"
                                wire:model="tag.name"
                                placeholder="Ej. Material"
                            >

                            @error('tag.name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>Valor</label>

                            <input
                                type="text"
                                class="form-control @error('tag.value') is-invalid @enderror"
                                wire:model="tag.value"
                                placeholder="Ej. Aluminio"
                            >

                            @error('tag.value')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                </div>

                @if ($editingTag)

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        Actualizar
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        wire:click="resetTag"
                    >
                        Cancelar
                    </button>

                @else

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        Añadir información
                    </button>

                @endif
            </form>

        </div>


        @if ($product->tagProducts->count())

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach ($product->tagProducts as $tag)

                            <tr>
                                <td>
                                    {{ $tag->name }}
                                </td>

                                <td>
                                    {{ $tag->value }}
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        wire:click="editTag({{ $tag->id }})"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        wire:click="deleteTag({{ $tag->id }})"
                                        wire:confirm="¿Está seguro de eliminar esta información?"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- ARCHIVOS --}}
    {{-- ========================================================= --}}

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-paperclip"></i>
                Archivos
            </h3>

        </div>

        <div class="card-body">

            <form wire:submit="saveFile">

                <div class="row">

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>Nombre del archivo</label>

                            <input
                                type="text"
                                class="form-control @error('fileName') is-invalid @enderror"
                                wire:model="fileName"
                                placeholder="Ej. Plano técnico"
                            >

                            @error('fileName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>Archivo</label>

                            <input
                                type="file"
                                class="form-control-file @error('file') is-invalid @enderror"
                                wire:model="file"
                            >

                            @error('file')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                </div>

                @if ($file)

                    <div class="mb-3 text-muted">
                        <i class="fa fa-file"></i>
                        {{ $file->getClientOriginalName() }}
                    </div>

                @endif

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa fa-upload"></i>
                    Subir archivo
                </button>

            </form>

        </div>


        @if ($product->files->count())

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach ($product->files as $file)

                            <tr>

                                <td>
                                    <i class="fa fa-file mr-1"></i>
                                    {{ $file->name }}
                                </td>

                                <td>
                                    {{ $file->mime }}
                                </td>

                                <td>
                                    {{ basename($file->path) }}
                                </td>

                                <td>

                                    <a
                                        href="{{ route('dashboard.products.files',$file->id) }}"
                                        target="_blank"
                                        class="btn btn-primary btn-sm"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        wire:click="deleteFile({{ $file->id }})"
                                        wire:confirm="¿Está seguro de eliminar este archivo?"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @else

            <div class="card-body text-muted">
                No existen archivos asociados a este producto.
            </div>

        @endif

    </div>

</div>
