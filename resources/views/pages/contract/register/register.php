<?php

use App\Enums\TypePayment;
use App\Enums\Status;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Industry;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public $contract = [
        'customer_id' => '',
        'delivery_date' => '',
        'destination' => '',
        'method_payment' => '',
        'priority' => 5,
        'external_id' => '',
        'industry_id' => '',
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

        $this->contract['method_payment'] = TypePayment::NONE->value;

        $this->contract['priority'] = 5;
    }

    public function searchCustomer()
    {
        $ciNit = trim($this->customer['ci_nit']);

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

        $this->productSearch = $product->cod . ' - ' . $product->name;

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
        if (!isset($this->products[$index])) {
            return;
        }

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
            // Información del contrato
            'contract.delivery_date' => 'required|date',

            'contract.destination' => 'nullable|string|max:255',

            'contract.method_payment' => [
                'required',
                'integer',
                Rule::enum(TypePayment::class),
            ],

            'contract.priority' => 'required|integer|between:1,5',

            // Nuevos campos
            'contract.external_id' => 'nullable|string|max:255',

            'contract.industry_id' => [
                'nullable',
                'integer',
                'exists:industries,id',
            ],

            // Información del cliente
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

        /*
         * Crear o actualizar el cliente.
         * Si no tiene CI/NIT, el contrato será anónimo.
         */
        $customer = null;

        if (trim($this->customer['ci_nit']) !== '') {
            $customer = Customer::updateOrCreate(
                [
                    'ci_nit' => trim($this->customer['ci_nit']),
                ],
                [
                    'name' => $this->customer['name'],
                    'email' => $this->customer['email'] ?: null,
                    'phone' => $this->customer['phone'] ?: null,
                    'organization' => $this->customer['organization'] ?: null,
                ]
            );
        }

        /*
         * Crear contrato.
         */
        $contract = Contract::create([
            'customer_id' => $customer?->id,

            'delivery_date' => $this->contract['delivery_date'],

            'destination' => $this->contract['destination'] ?: null,

            'method_payment' => $this->contract['method_payment'],

            'priority' => $this->contract['priority'],

            'external_id' => trim(
                (string) $this->contract['external_id']
            ) ?: null,

            'industry_id' => $this->contract['industry_id'] ?: null,

            'status' => Status::ACTIVE,
        ]);

        /*
         * Registrar los productos del contrato.
         */
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
                    $query->where('cod', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(8)
                ->get();
        }

        return $this->view([
            'productResults' => $productResults,

            'industries' => Industry::orderBy('name')->get(),
        ]);
    }
};
