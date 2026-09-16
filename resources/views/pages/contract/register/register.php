<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Product;
use Livewire\Component;

new class extends Component
{
    public $contract = [
        'customer_id' => '',
        'delivery_date' => '',
    ];

    public $customer = [
        'ci_nit' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'organization' => '',
    ];

    public $existingCustomer = false;

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
        $this->contract['delivery_date'] = now()->format('Y-m-d');
    }

    public function searchCustomer()
    {
        $ciNit = trim($this->customer['ci_nit']);

        // Si se limpia el CI/NIT, el contrato será para cliente anónimo
        if ($ciNit === '') {
            $this->contract['customer_id'] = '';

            $this->existingCustomer = false;

            $this->customer = [
                'ci_nit' => '',
                'name' => '',
                'email' => '',
                'phone' => '',
                'organization' => '',
            ];

            return;
        }

        $customer = Customer::where('ci_nit', $ciNit)->first();

        if ($customer) {

            $this->contract['customer_id'] = $customer->id;

            $this->customer = [
                'ci_nit' => $customer->ci_nit,
                'name' => $customer->name,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
                'organization' => $customer->organization ?? '',
            ];

            $this->existingCustomer = true;

        } else {

            $this->contract['customer_id'] = '';

            $this->customer['name'] = '';
            $this->customer['email'] = '';
            $this->customer['phone'] = '';
            $this->customer['organization'] = '';

            $this->existingCustomer = false;
        }

        $this->resetValidation();
    }

    public function selectProduct($id)
    {
        $product = Product::findOrFail($id);

        $this->selectedProduct = $product;

        $this->detail['product_id'] = $product->id;
        $this->detail['price'] = '';

        $this->productSearch =
            $product->cod . ' - ' . $product->name;

        $this->resetValidation('detail.product_id');
    }

    public function updatedProductSearch()
    {
        $this->selectedProduct = null;

        $this->detail['product_id'] = '';
        $this->detail['price'] = '';
    }

    public function addProduct()
    {
        $this->validate([
            'detail.product_id' => 'required|exists:products,id',
            'detail.quantity' => 'required|integer|min:1',
            'detail.price' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail(
            $this->detail['product_id']
        );

        $this->products[] = [
            'product_id' => $product->id,
            'cod' => $product->cod,
            'name' => $product->name,
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

    public function save()
    {
        $this->validate([
            'contract.delivery_date' => 'required|date',

            'customer.ci_nit' => 'nullable|string|max:255',
            'customer.name' => 'nullable|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.phone' => 'nullable|string|max:255',
            'customer.organization' => 'nullable|string|max:255',
        ]);

        if (empty($this->products)) {
            $this->addError(
                'products',
                'Debe agregar al menos un producto.'
            );

            return;
        }

        $customer = null;

        /*
         * Si existe CI/NIT:
         *
         * 1. Busca el cliente.
         * 2. Si existe, actualiza sus datos.
         * 3. Si no existe, crea uno nuevo.
         *
         * Si no existe CI/NIT:
         * contrato anónimo.
         */
        if (trim($this->customer['ci_nit']) !== '') {

            $customer = Customer::updateOrCreate(
                [
                    'ci_nit' => trim($this->customer['ci_nit']),
                ],
                [
                    'name' => $this->customer['name'],
                    'email' => $this->customer['email'] ?: null,
                    'phone' => $this->customer['phone'] ?: null,
                    'organization' => $this->customer['organization'],
                ]
            );
        }

        $contract = Contract::create([
            'customer_id' => $customer?->id,
            'delivery_date' => $this->contract['delivery_date'],
            'status' => \App\Enums\Status::ACTIVE,
        ]);

        foreach ($this->products as $product) {
            $contract->details()->create([
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]);
        }

        return $this->redirectRoute(
            'dashboard.contracts.view',
            $contract
        );
    }

    public function render()
    {
        $productResults = [];

        if (
            strlen(trim($this->productSearch)) >= 1 &&
            !$this->selectedProduct
        ) {
            $search = trim($this->productSearch);

            $productResults = Product::query()
                ->where(function ($query) use ($search) {
                    $query->where(
                        'cod',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'name',
                            'like',
                            "%{$search}%"
                        );
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
