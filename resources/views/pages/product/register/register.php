<?php

use App\Models\Product;
use App\Models\TypeFactory;
use Livewire\Component;

new class extends Component
{
    public $product = [
        'cod' => '',
        'name' => '',
        'description' => '',
        'price_sale' => '',
        'price_purchase' => '',
        'model' => '',
        'type_factory_id' => '',
    ];

    public function saveProduct()
    {
        $this->validate([
            'product.cod' => 'required|string|max:255|unique:products,cod',
            'product.name' => 'required|string|max:255',
            'product.description' => 'required|string',
            'product.price_sale' => 'required|numeric|min:0',
            'product.price_purchase' => 'required|numeric|min:0',
            'product.model' => 'required|string|max:255',
            'product.type_factory_id' => 'required|exists:type_factories,id',
        ]);

        $p = Product::create($this->product);

        $this->resetProduct();

        return $this->redirectRoute('dashboard.products');
    }

    public function resetProduct()
    {
        $this->product = [
            'cod' => '',
            'name' => '',
            'description' => '',
            'price_sale' => '',
            'price_purchase' => '',
            'model' => '',
            'type_factory_id' => '',
        ];

        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'typeFactories' => TypeFactory::orderBy('name')->get(),
        ]);
    }






};
