<?php

use App\Models\Product;
use App\Models\Quotation;
use Livewire\Component;

new class extends Component
{
    public $quotation = [
        'valid_from' => '',
        'valid_to' => '',
        'delivery_date' => '',
    ];

    public $products = [];

    public $productSearch = '';

    public $selectedProduct = null;

    public $detail = [
        'product_id' => '',
        'quantity' => 1,
        'price' => '',
    ];

    public function mount()
    {
        $this->quotation['valid_from'] = now()->format('Y-m-d');
    }

    public function updatedProductSearch()
    {
        $this->selectedProduct = null;
        $this->detail['product_id'] = '';
        $this->detail['price'] = '';
    }

    public function selectProduct($id)
    {
        $product = Product::findOrFail($id);

        $this->selectedProduct = $product;

        $this->detail['product_id'] = $product->id;
        $this->detail['price'] = '';
        $this->productSearch = $product->cod . ' - ' . $product->name;

        $this->resetValidation('detail.product_id');
    }

    public function addProduct()
    {
        $this->validate([
            'detail.product_id' => 'required|exists:products,id',
            'detail.quantity' => 'required|integer|min:1',
            'detail.price' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($this->detail['product_id']);

        $this->products[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'cod' => $product->cod,
            'quantity' => $this->detail['quantity'],
            'price' => $this->detail['price'],
        ];

        $this->resetDetail();
    }

    public function removeProduct($index)
    {
        unset($this->products[$index]);

        $this->products = array_values($this->products);
    }

    public function save()
    {
        $this->validate([
            'quotation.valid_from' => 'required|date',
            'quotation.valid_to' => 'required|date|after_or_equal:quotation.valid_from',
            'quotation.delivery_date' => 'required|date',
        ]);

        if (empty($this->products)) {
            $this->addError(
                'products',
                'Debe agregar al menos un producto.'
            );

            return;
        }

        $quotation = Quotation::create([
            'valid_from' => $this->quotation['valid_from'],
            'valid_to' => $this->quotation['valid_to'],
            'delivery_date' => $this->quotation['delivery_date'],
        ]);

        foreach ($this->products as $product) {
            $quotation->details()->create([
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]);
        }

        return $this->redirectRoute(
            'dashboard.quotations.view',
            $quotation
        );
    }

    public function resetDetail()
    {
        $this->detail = [
            'product_id' => '',
            'quantity' => 1,
            'price' => '',
        ];

        $this->productSearch = '';
        $this->selectedProduct = null;

        $this->resetValidation();
    }

    public function render()
    {
        $productResults = [];

        if (strlen(trim($this->productSearch)) >= 1 && !$this->selectedProduct) {
            $search = trim($this->productSearch);

            $productResults = Product::query()
                ->where(function ($query) use ($search) {
                    $query->where('cod', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(8)
                ->get();
        }

        return $this->view([
            'productResults' => $productResults,
        ]);
    }
};
