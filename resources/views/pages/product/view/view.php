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
    | Componentes
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
    | Tags
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
    | Componentes
    |--------------------------------------------------------------------------
    */

    public function saveComponent()
    {
        $this->validate([
            'component.name' => 'required|string|max:255',
            'component.description' => 'nullable|string',
            'component.quantity' => 'required|numeric|min:0',
            'component.type_factory_id' => 'required|exists:type_factories,id',
        ]);

        ComponentProduct::create([
            'name' => $this->component['name'],
            'description' => $this->component['description'],
            'quantity' => $this->component['quantity'],
            'type_factory_id' => $this->component['type_factory_id'],
            'product_id' => $this->product->id,
        ]);

        $this->resetComponent();

        $this->product->load('components.typeFactory');

        $this->resetValidation();
    }

    public function editComponent($id)
    {
        $component = $this->product->components()->findOrFail($id);

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

        $component = $this->product->components()
            ->findOrFail($this->component['id']);

        $component->update([
            'name' => $this->component['name'],
            'description' => $this->component['description'],
            'quantity' => $this->component['quantity'],
            'type_factory_id' => $this->component['type_factory_id'],
        ]);

        $this->resetComponent();

        $this->product->load('components.typeFactory');

        $this->resetValidation();
    }

    public function deleteComponent($id)
    {
        $this->product->components()
            ->findOrFail($id)
            ->delete();

        $this->product->load('components.typeFactory');
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
    | Tags
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
            'components.typeFactory',
            'tagProducts',
            'files',
        ]);

        return $this->view([
            'categories' => Category::orderBy('name')->get(),
            'typeFactories' => TypeFactory::orderBy('name')->get(),
        ]);
    }
};
