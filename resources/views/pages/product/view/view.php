<?php

use App\Models\Category;
use App\Models\ComponentProduct;
use App\Models\Product;
use App\Models\TagProduct;
use App\Models\TypeFactory;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public Product $product;

    /*
    |--------------------------------------------------------------------------
    | Producto
    |--------------------------------------------------------------------------
    */

    public $editingProduct = false;

    public $productData = [
        'cod' => '',
        'name' => '',
        'description' => '',
        'price_sale' => '',
        'price_purchase' => '',
        'model' => '',
        'type_factory_id' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | Categorías
    |--------------------------------------------------------------------------
    */

    public $selectedCategories = [];

    public $newCategory = [
        'name' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | Componentes y procesos
    |--------------------------------------------------------------------------
    */

    public $component = [
        'id' => null,
        'name' => '',
        'description' => '',
        'quantity' => '',
        'type_factory_id' => '',
    ];

    public $editingComponent = false;

    /*
    |--------------------------------------------------------------------------
    | Edición de procesos directamente en su tarjeta
    |--------------------------------------------------------------------------
    */

    public $editingInlineComponentId = null;

    public $inlineComponentEdit = [
        'name' => '',
        'description' => '',
        'quantity' => '',
        'type_factory_id' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | Etiquetas propias de cada proceso
    |--------------------------------------------------------------------------
    */

    public array $componentTagInputs = [];

    public $editingComponentTagId = null;

    public $editingComponentTagComponentId = null;

    public $componentTagEdit = [
        'name' => '',
        'value' => '',
    ];

    /*
    |--------------------------------------------------------------------------
    | Etiquetas del producto
    |--------------------------------------------------------------------------
    */

    public $tag = [
        'id' => null,
        'name' => '',
        'value' => '',
    ];

    public $editingTag = false;

    /*
    |--------------------------------------------------------------------------
    | Archivos
    |--------------------------------------------------------------------------
    */

    public $file;

    public $fileName = '';

    /*
    |--------------------------------------------------------------------------
    | Inicialización
    |--------------------------------------------------------------------------
    */

    public function mount(Product $product)
    {
        $this->product = $product;

        $this->selectedCategories = $product->categories
            ->pluck('id')
            ->toArray();

        $this->loadProductData();
    }

    /*
    |--------------------------------------------------------------------------
    | Producto
    |--------------------------------------------------------------------------
    */

    public function editProduct()
    {
        $this->loadProductData();

        $this->editingProduct = true;
    }

    public function loadProductData()
    {
        $this->productData = [
            'cod' => $this->product->cod,
            'name' => $this->product->name,
            'description' => $this->product->description,
            'price_sale' => $this->product->price_sale,
            'price_purchase' => $this->product->price_purchase,
            'model' => $this->product->model,
            'type_factory_id' => $this->product->type_factory_id,
        ];
    }

    public function updateProduct()
    {
        $this->validate([
            'productData.cod' => 'required|string|max:255|unique:products,cod,' . $this->product->id,
            'productData.name' => 'required|string|max:255',
            'productData.description' => 'nullable|string',
            'productData.price_sale' => 'required|numeric|min:0',
            'productData.price_purchase' => 'required|numeric|min:0',
            'productData.model' => 'nullable|string|max:255',
            'productData.type_factory_id' => 'required|exists:type_factories,id',
        ]);

        $this->product->update($this->productData);

        $this->product->refresh();

        $this->editingProduct = false;

        $this->loadProductData();

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Categorías
    |--------------------------------------------------------------------------
    */

    public function saveCategories()
    {
        $this->product->categories()->sync($this->selectedCategories);

        $this->product->load('categories');
    }

    public function saveCategory()
    {
        $this->validate([
            'newCategory.name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $this->newCategory['name'],
        ]);

        $this->selectedCategories[] = $category->id;

        $this->product->categories()->sync($this->selectedCategories);

        $this->newCategory = [
            'name' => '',
        ];

        $this->product->load('categories');

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Crear componentes o procesos
    |--------------------------------------------------------------------------
    */

    public function saveComponent()
    {
        $this->validate([
            'component.name' => 'required|string|max:255',
            'component.description' => 'nullable|string',
            'component.type_factory_id' => 'required|exists:type_factories,id',
        ]);

        $this->product->componentProducts()->create([
            'name' => $this->component['name'],
            'description' => $this->component['description'],
            'type_factory_id' => $this->component['type_factory_id'],
        ]);

        $this->resetComponent();

        $this->product->load([
            'componentProducts.typeFactory',
            'componentProducts.tags',
        ]);

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Edición tradicional de componentes
    |--------------------------------------------------------------------------
    | Se conserva para mantener la funcionalidad existente.
    */

    public function editComponent($id)
    {
        $component = $this->product->componentProducts()
            ->findOrFail($id);

        $this->component = [
            'id' => $component->id,
            'name' => $component->name,
            'description' => $component->description,
            'quantity' => $component->quantity,
            'type_factory_id' => $component->type_factory_id,
        ];

        $this->editingComponent = true;
    }

    public function updateComponent()
    {
        $this->validate([
            'component.name' => 'required|string|max:255',
            'component.description' => 'nullable|string',
            'component.quantity' => 'required|numeric|min:0',
            'component.type_factory_id' => 'required|exists:type_factories,id',
        ]);

        $component = $this->product->componentProducts()
            ->findOrFail($this->component['id']);

        $component->update([
            'name' => $this->component['name'],
            'description' => $this->component['description'],
            'quantity' => $this->component['quantity'],
            'type_factory_id' => $this->component['type_factory_id'],
        ]);

        $this->resetComponent();

        $this->product->load([
            'componentProducts.typeFactory',
            'componentProducts.tags',
        ]);

        $this->resetValidation();
    }

    public function resetComponent()
    {
        $this->component = [
            'id' => null,
            'name' => '',
            'description' => '',
            'quantity' => '',
            'type_factory_id' => '',
        ];

        $this->editingComponent = false;

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Edición en línea de procesos
    |--------------------------------------------------------------------------
    */

    public function startEditingComponent($id)
    {
        $component = $this->product->componentProducts()
            ->findOrFail($id);

        $this->editingInlineComponentId = $component->id;

        $this->inlineComponentEdit = [
            'name' => $component->name,
            'description' => $component->description,
            'quantity' => $component->quantity,
            'type_factory_id' => $component->type_factory_id,
        ];

        $this->resetValidation();
    }

    public function saveInlineComponent($id)
    {
        if ((int) $this->editingInlineComponentId !== (int) $id) {
            abort(403);
        }

        $this->validate([
            'inlineComponentEdit.name' => 'required|string|max:255',
            'inlineComponentEdit.description' => 'nullable|string',
            'inlineComponentEdit.quantity' => 'required|numeric|min:0',
            'inlineComponentEdit.type_factory_id' => 'required|exists:type_factories,id',
        ]);

        $component = $this->product->componentProducts()
            ->findOrFail($id);

        $component->update([
            'name' => $this->inlineComponentEdit['name'],
            'description' => $this->inlineComponentEdit['description'],
            'quantity' => $this->inlineComponentEdit['quantity'],
            'type_factory_id' => $this->inlineComponentEdit['type_factory_id'],
        ]);

        $this->cancelInlineComponentEdit();

        $this->product->load([
            'componentProducts.typeFactory',
            'componentProducts.tags',
        ]);

        $this->resetValidation();
    }

    public function cancelInlineComponentEdit()
    {
        $this->editingInlineComponentId = null;

        $this->inlineComponentEdit = [
            'name' => '',
            'description' => '',
            'quantity' => '',
            'type_factory_id' => '',
        ];

        $this->resetValidation();
    }

    public function deleteComponent($id)
    {
        $component = $this->product->componentProducts()
            ->findOrFail($id);

        $component->delete();

        if ((int) $this->editingInlineComponentId === (int) $id) {
            $this->cancelInlineComponentEdit();
        }

        if ((int) ($this->component['id'] ?? 0) === (int) $id) {
            $this->resetComponent();
        }

        $this->product->load([
            'componentProducts.typeFactory',
            'componentProducts.tags',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Etiquetas de los procesos
    |--------------------------------------------------------------------------
    */

    public function saveComponentTag($componentId)
    {
        $this->validate([
            "componentTagInputs.{$componentId}.name" => 'required|string|max:255',
            "componentTagInputs.{$componentId}.value" => 'required|string|max:255',
        ]);

        $component = $this->product->componentProducts()
            ->findOrFail($componentId);

        $component->tags()->create([
            'name' => $this->componentTagInputs[$componentId]['name'],
            'value' => $this->componentTagInputs[$componentId]['value'],
        ]);

        $this->componentTagInputs[$componentId] = [
            'name' => '',
            'value' => '',
        ];

        $this->product->load('componentProducts.tags');

        $this->resetValidation();
    }

    public function editComponentTag($componentId, $tagId)
    {
        $component = $this->product->componentProducts()
            ->findOrFail($componentId);

        $tag = $component->tags()
            ->findOrFail($tagId);

        $this->editingComponentTagId = $tag->id;
        $this->editingComponentTagComponentId = $component->id;

        $this->componentTagEdit = [
            'name' => $tag->name,
            'value' => $tag->value,
        ];

        $this->resetValidation();
    }

    public function updateComponentTag()
    {
        if (
            !$this->editingComponentTagId ||
            !$this->editingComponentTagComponentId
        ) {
            return;
        }

        $this->validate([
            'componentTagEdit.name' => 'required|string|max:255',
            'componentTagEdit.value' => 'required|string|max:255',
        ]);

        $component = $this->product->componentProducts()
            ->findOrFail($this->editingComponentTagComponentId);

        $tag = $component->tags()
            ->findOrFail($this->editingComponentTagId);

        $tag->update([
            'name' => $this->componentTagEdit['name'],
            'value' => $this->componentTagEdit['value'],
        ]);

        $this->resetComponentTagEdit();

        $this->product->load('componentProducts.tags');

        $this->resetValidation();
    }

    public function deleteComponentTag($componentId, $tagId)
    {
        $component = $this->product->componentProducts()
            ->findOrFail($componentId);

        $component->tags()
            ->findOrFail($tagId)
            ->delete();

        if (
            (int) $this->editingComponentTagId === (int) $tagId &&
            (int) $this->editingComponentTagComponentId === (int) $componentId
        ) {
            $this->resetComponentTagEdit();
        }

        $this->product->load('componentProducts.tags');
    }

    public function resetComponentTagEdit()
    {
        $this->editingComponentTagId = null;
        $this->editingComponentTagComponentId = null;

        $this->componentTagEdit = [
            'name' => '',
            'value' => '',
        ];

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Etiquetas generales del producto
    |--------------------------------------------------------------------------
    */

    public function saveTag()
    {
        $this->validate([
            'tag.name' => 'required|string|max:255',
            'tag.value' => 'required|string|max:255',
        ]);

        $this->product->tagProducts()->create([
            'name' => $this->tag['name'],
            'value' => $this->tag['value'],
        ]);

        $this->resetTag();

        $this->product->load('tagProducts');

        $this->resetValidation();
    }

    public function editTag($id)
    {
        $tag = $this->product->tagProducts()
            ->findOrFail($id);

        $this->tag = [
            'id' => $tag->id,
            'name' => $tag->name,
            'value' => $tag->value,
        ];

        $this->editingTag = true;
    }

    public function updateTag()
    {
        $this->validate([
            'tag.name' => 'required|string|max:255',
            'tag.value' => 'required|string|max:255',
        ]);

        $tag = $this->product->tagProducts()
            ->findOrFail($this->tag['id']);

        $tag->update([
            'name' => $this->tag['name'],
            'value' => $this->tag['value'],
        ]);

        $this->resetTag();

        $this->product->load('tagProducts');

        $this->resetValidation();
    }

    public function deleteTag($id)
    {
        $this->product->tagProducts()
            ->findOrFail($id)
            ->delete();

        $this->product->load('tagProducts');
    }

    public function resetTag()
    {
        $this->tag = [
            'id' => null,
            'name' => '',
            'value' => '',
        ];

        $this->editingTag = false;

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Archivos
    |--------------------------------------------------------------------------
    */

    public function saveFile()
    {
        $this->validate([
            'fileName' => 'required|string|max:255',
            'file' => 'required|file|max:51200',
        ]);

        $path = $this->file->store(
            'products/' . $this->product->id
        );

        $this->product->files()->create([
            'name' => $this->fileName,
            'path' => $path,
            'mime' => $this->file->getMimeType(),
        ]);

        $this->file = null;
        $this->fileName = '';

        $this->product->load('files');

        $this->resetValidation();
    }

    public function deleteFile($id)
    {
        $file = $this->product->files()
            ->findOrFail($id);

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        $this->product->load('files');
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $this->product->load([
            'typeFactory',
            'categories',
            'componentProducts.typeFactory',
            'componentProducts.tags',
            'tagProducts',
            'files',
        ]);

        return $this->view([
            'categories' => Category::orderBy('name')->get(),
            'typeFactories' => TypeFactory::orderBy('name')->get(),
        ]);
    }
};
