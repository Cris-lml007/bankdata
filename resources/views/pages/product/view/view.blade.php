<x-slot name="header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>{{ $product->name }}</h1>
            <small class="text-muted">
                Código: {{ $product->cod }}
            </small>
        </div>

        <a href="{{ route('dashboard.products') }}" class="btn btn-secondary">
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
                    <button type="button" class="btn btn-primary btn-sm"
                            wire:click="editProduct">
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
                                <input type="text"
                                       class="form-control @error('productData.cod') is-invalid @enderror"
                                       wire:model="productData.cod">
                                @error('productData.cod')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text"
                                       class="form-control @error('productData.name') is-invalid @enderror"
                                       wire:model="productData.name">
                                @error('productData.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Modelo</label>
                                <input type="text" class="form-control"
                                       wire:model="productData.model">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de área de ensamblaje</label>
                                <select
                                    class="form-control @error('productData.type_factory_id') is-invalid @enderror"
                                    wire:model="productData.type_factory_id">
                                    <option value="">Seleccione un tipo de área</option>
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
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('productData.price_purchase') is-invalid @enderror"
                                       wire:model="productData.price_purchase">
                                @error('productData.price_purchase')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Precio de venta</label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('productData.price_sale') is-invalid @enderror"
                                       wire:model="productData.price_sale">
                                @error('productData.price_sale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea rows="3" class="form-control"
                                          wire:model="productData.description"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar cambios
                    </button>

                    <button type="button" class="btn btn-secondary"
                            wire:click="$set('editingProduct', false)">
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
                        <strong>Tipo de área de ensamblaje</strong>
                        <p>{{ $product->typeFactory?->name ?? 'Sin asignar' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Costo de producción</strong>
                        <p>Bs. {{ number_format($product->price_purchase, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Precio de venta</strong>
                        <p>Bs. {{ number_format($product->price_sale, 2) }}</p>
                    </div>
                    <div class="col-12">
                        <strong>Descripción</strong>
                        <p>{{ $product->description ?: 'Sin descripción' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- CATEGORÍAS --}}
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
                                    <div class="col-md-4 mb-2" wire:key="category-{{ $category->id }}">
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="category-{{ $category->id }}"
                                                value="{{ $category->id }}"
                                                wire:model="selectedCategories">

                                            <label class="custom-control-label"
                                                   for="category-{{ $category->id }}">
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
                            <input type="text"
                                   class="form-control @error('newCategory.name') is-invalid @enderror"
                                   wire:model="newCategory.name"
                                   placeholder="Nombre de la categoría">

                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary"
                                        wire:click="saveCategory">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        @error('newCategory.name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fa fa-save"></i> Guardar categorías
                </button>
            </form>
        </div>
    </div>

    {{-- PROCESO DE PRODUCCIÓN --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-cubes"></i>
                Proceso de Producción
            </h3>
        </div>

        {{-- FORMULARIO PARA CREAR UN PROCESO --}}
        <div class="card-body border-bottom">
            <h5 class="mb-3">
                <i class="fa fa-plus-circle text-primary"></i>
                Añadir proceso
            </h5>

            <form wire:submit="saveComponent">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text"
                                   class="form-control @error('component.name') is-invalid @enderror"
                                   wire:model="component.name"
                                   placeholder="Ej. Marco">

                            @error('component.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de área</label>
                            <select
                                class="form-control @error('component.type_factory_id') is-invalid @enderror"
                                wire:model="component.type_factory_id">
                                <option value="">Seleccione un tipo de área</option>
                                @foreach ($typeFactories as $typeFactory)
                                    <option value="{{ $typeFactory->id }}">
                                        {{ $typeFactory->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('component.type_factory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 d-none">
                        <div class="form-group">
                            <label>Cantidad</label>
                            <input type="number" min="0" step="any"
                                   class="form-control @error('component.quantity') is-invalid @enderror"
                                   wire:model="component.quantity">

                            @error('component.quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <label>Descripción</label>
                            <input type="text" class="form-control"
                                   wire:model="component.description"
                                   placeholder="Descripción del proceso">
                            @error('component.description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="saveComponent">
                    <i class="fa fa-plus"></i> Añadir proceso
                </button>
            </form>
        </div>

        {{-- TARJETAS DE LOS PROCESOS --}}
        @forelse ($product->componentProducts as $component)
            <div class="card card-outline card-primary m-3"
                 wire:key="component-{{ $component->id }}">

                {{-- ENCABEZADO DEL PROCESO --}}
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-cubes mr-1"></i>
                        {{ $component->name }}
                    </h3>

                    <div class="card-tools">
                        @if ((int) $editingInlineComponentId === (int) $component->id)
                            <button type="button" class="btn btn-success btn-sm"
                                    wire:click="saveInlineComponent({{ $component->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="saveInlineComponent">
                                <i class="fa fa-save"></i> Guardar
                            </button>

                            <button type="button" class="btn btn-secondary btn-sm"
                                    wire:click="cancelInlineComponentEdit">
                                <i class="fa fa-times"></i> Cancelar
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-sm"
                                    wire:click="startEditingComponent({{ $component->id }})">
                                <i class="fa fa-edit"></i> Editar proceso
                            </button>

                            <button type="button" class="btn btn-danger btn-sm"
                                    wire:click="deleteComponent({{ $component->id }})"
                                    wire:confirm="¿Está seguro de eliminar este proceso?">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    {{-- DATOS DEL PROCESO: EDICIÓN EN LÍNEA --}}
                    @if ((int) $editingInlineComponentId === (int) $component->id)
                        <div class="border rounded p-3 mb-4 bg-light">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fa fa-edit mr-1"></i>
                                Editar proceso de producción
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text"
                                               class="form-control @error('inlineComponentEdit.name') is-invalid @enderror"
                                               wire:model="inlineComponentEdit.name">

                                        @error('inlineComponentEdit.name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tipo de área</label>
                                        <select
                                            class="form-control @error('inlineComponentEdit.type_factory_id') is-invalid @enderror"
                                            wire:model="inlineComponentEdit.type_factory_id">
                                            <option value="">Seleccione un tipo de área</option>
                                            @foreach ($typeFactories as $typeFactory)
                                                <option value="{{ $typeFactory->id }}">
                                                    {{ $typeFactory->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('inlineComponentEdit.type_factory_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cantidad</label>
                                        <input type="number" min="0" step="any"
                                               class="form-control @error('inlineComponentEdit.quantity') is-invalid @enderror"
                                               wire:model="inlineComponentEdit.quantity">

                                        @error('inlineComponentEdit.quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text"
                                               class="form-control @error('inlineComponentEdit.description') is-invalid @enderror"
                                               wire:model="inlineComponentEdit.description">

                                        @error('inlineComponentEdit.description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-success"
                                    wire:click="saveInlineComponent({{ $component->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="saveInlineComponent">
                                <i class="fa fa-save"></i> Guardar cambios
                            </button>

                            <button type="button" class="btn btn-secondary"
                                    wire:click="cancelInlineComponentEdit">
                                Cancelar
                            </button>
                        </div>
                    @else
                        <div class="row mb-4">
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Tipo de área</small>
                                <strong>{{ $component->typeFactory?->name ?? '-' }}</strong>
                            </div>

                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Cantidad</small>
                                <strong>{{ $component->quantity ?? '-' }}</strong>
                            </div>

                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Descripción</small>
                                <span>{{ $component->description ?: '-' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- ETIQUETAS DEL PROCESO --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="fa fa-tags text-primary mr-1"></i>
                            Etiquetas del proceso
                        </h5>
                        <span class="badge badge-secondary">
                            {{ $component->tags->count() }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-3">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 35%">Nombre</th>
                                    <th>Valor</th>
                                    <th style="width: 115px">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($component->tags as $componentTag)
                                    <tr wire:key="component-tag-{{ $componentTag->id }}">
                                        @if (
                                            (int) $editingComponentTagId === (int) $componentTag->id &&
                                            (int) $editingComponentTagComponentId === (int) $component->id
                                        )
                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm @error('componentTagEdit.name') is-invalid @enderror"
                                                       wire:model="componentTagEdit.name">
                                                @error('componentTagEdit.name')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm @error('componentTagEdit.value') is-invalid @enderror"
                                                       wire:model="componentTagEdit.value">
                                                @error('componentTagEdit.value')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td class="text-nowrap">
                                                <button type="button"
                                                        class="btn btn-success btn-sm"
                                                        title="Guardar etiqueta"
                                                        wire:click="updateComponentTag">
                                                    <i class="fa fa-check"></i>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-secondary btn-sm"
                                                        title="Cancelar edición"
                                                        wire:click="resetComponentTagEdit">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        @else
                                            <td>{{ $componentTag->name }}</td>
                                            <td>{{ $componentTag->value }}</td>
                                            <td class="text-nowrap">
                                                <button type="button"
                                                        class="btn btn-primary btn-sm"
                                                        title="Editar etiqueta"
                                                        wire:click="editComponentTag({{ $component->id }}, {{ $componentTag->id }})">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-danger btn-sm"
                                                        title="Eliminar etiqueta"
                                                        wire:click="deleteComponentTag({{ $component->id }}, {{ $componentTag->id }})"
                                                        wire:confirm="¿Está seguro de eliminar esta etiqueta?">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            Este proceso todavía no tiene etiquetas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- FORMULARIO PARA AÑADIR ETIQUETAS --}}
                    @if (
                        (int) $editingComponentTagComponentId !== (int) $component->id ||
                        $editingComponentTagId === null
                    )
                        <form wire:submit="saveComponentTag({{ $component->id }})"
                              class="border rounded p-3 bg-light">

                            <h6 class="font-weight-bold mb-3">
                                <i class="fa fa-plus-circle text-primary mr-1"></i>
                                Añadir etiqueta
                            </h6>

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text"
                                               class="form-control @error('componentTagInputs.' . $component->id . '.name') is-invalid @enderror"
                                               wire:model="componentTagInputs.{{ $component->id }}.name"
                                               placeholder="Ej. Material">

                                        @error('componentTagInputs.' . $component->id . '.name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Valor</label>
                                        <input type="text"
                                               class="form-control @error('componentTagInputs.' . $component->id . '.value') is-invalid @enderror"
                                               wire:model="componentTagInputs.{{ $component->id }}.value"
                                               placeholder="Ej. Aluminio">

                                        @error('componentTagInputs.' . $component->id . '.value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-group w-100">
                                        <button type="submit"
                                                class="btn btn-primary btn-block"
                                                wire:loading.attr="disabled"
                                                wire:target="saveComponentTag">
                                            <i class="fa fa-plus"></i>
                                            Añadir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card-body text-center text-muted">
                <i class="fa fa-cubes fa-2x mb-2"></i>
                <p class="mb-0">Todavía no se han registrado procesos de producción.</p>
            </div>
        @endforelse
    </div>

    {{-- INFORMACIÓN TÉCNICA DEL PRODUCTO --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-list"></i>
                Información técnica del producto
            </h3>
        </div>

        <div class="card-body">
            <form wire:submit="{{ $editingTag ? 'updateTag' : 'saveTag' }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text"
                                   class="form-control @error('tag.name') is-invalid @enderror"
                                   wire:model="tag.name"
                                   placeholder="Ej. Material">

                            @error('tag.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Valor</label>
                            <input type="text"
                                   class="form-control @error('tag.value') is-invalid @enderror"
                                   wire:model="tag.value"
                                   placeholder="Ej. Aluminio">

                            @error('tag.value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if ($editingTag)
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Actualizar
                    </button>

                    <button type="button" class="btn btn-secondary"
                            wire:click="resetTag">
                        Cancelar
                    </button>
                @else
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Añadir información
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
                                <th style="width: 115px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($product->tagProducts as $tag)
                                <tr wire:key="product-tag-{{ $tag->id }}">
                                    <td>{{ $tag->name }}</td>
                                    <td>{{ $tag->value }}</td>
                                    <td class="text-nowrap">
                                        <button type="button"
                                                class="btn btn-primary btn-sm"
                                                wire:click="editTag({{ $tag->id }})">
                                            <i class="fa fa-edit"></i>
                                        </button>

                                        <button type="button"
                                                class="btn btn-danger btn-sm"
                                                wire:click="deleteTag({{ $tag->id }})"
                                                wire:confirm="¿Está seguro de eliminar esta información?">
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
                No existen etiquetas generales para este producto.
            </div>
        @endif
    </div>

    {{-- ARCHIVOS --}}
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
                            <input type="text"
                                   class="form-control @error('fileName') is-invalid @enderror"
                                   wire:model="fileName"
                                   placeholder="Ej. Plano técnico">

                            @error('fileName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Archivo</label>
                            <input type="file"
                                   class="form-control-file @error('file') is-invalid @enderror"
                                   wire:model="file">

                            @error('file')
                                <div class="text-danger small">{{ $message }}</div>
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

                <button type="submit" class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="saveFile">
                    <i class="fa fa-upload"></i> Subir archivo
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
                                <th style="width: 115px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($product->files as $file)
                                <tr wire:key="product-file-{{ $file->id }}">
                                    <td>
                                        <i class="fa fa-file mr-1"></i>
                                        {{ $file->name }}
                                    </td>
                                    <td>{{ $file->mime }}</td>
                                    <td>{{ basename($file->path) }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('dashboard.products.files', $file->id) }}"
                                           target="_blank"
                                           class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        <button type="button"
                                                class="btn btn-danger btn-sm"
                                                wire:click="deleteFile({{ $file->id }})"
                                                wire:confirm="¿Está seguro de eliminar este archivo?">
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
