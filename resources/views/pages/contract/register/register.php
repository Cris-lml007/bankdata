<?php

use App\Enums\TypePayment;
use App\Enums\Status;
use App\Enums\Type;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Industry;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
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

    /**
     * Etiquetas del producto que se está preparando para agregar.
     *
     * Estas etiquetas todavía no se guardan en la base de datos.
     */
    public $tagsDraft = [];

    public function mount()
    {
        $this->contract['delivery_date'] = now()->format('Y-m-d');

        $this->contract['method_payment'] = TypePayment::NONE->value;

        $this->contract['priority'] = 5;

        $this->tagsDraft = [
            ['name' => '', 'value' => ''],
        ];
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

        // Cada producto nuevo comienza con sus propias etiquetas.
        $this->tagsDraft = [
            ['name' => '', 'value' => ''],
        ];

        $this->resetValidation();
    }

    public function updatedProductSearch()
    {
        $this->selectedProduct = null;

        $this->detail['product_id'] = '';
        $this->detail['price'] = '';

        // Evitar que las etiquetas de un producto anterior se reutilicen.
        $this->tagsDraft = [
            ['name' => '', 'value' => ''],
        ];

        $this->resetValidation([
            'detail.product_id',
            'detail.price',
            'tagsDraft',
        ]);
    }

    public function addTag(): void
    {
        $this->tagsDraft[] = [
            'name' => '',
            'value' => '',
        ];
    }

    public function removeTag(int $index): void
    {
        if (!array_key_exists($index, $this->tagsDraft)) {
            return;
        }

        unset($this->tagsDraft[$index]);

        $this->tagsDraft = array_values($this->tagsDraft);

        if (empty($this->tagsDraft)) {
            $this->tagsDraft = [
                ['name' => '', 'value' => ''],
            ];
        }

        $this->resetValidation('tagsDraft');
    }

    public function addProduct()
    {
        $this->validate([
            'detail.product_id' => 'required|exists:products,id',
            'detail.quantity' => 'required|integer|min:1',
            'detail.price' => 'required|numeric|min:0',

            'tagsDraft' => 'nullable|array',
            'tagsDraft.*.name' => 'nullable|string|max:255',
            'tagsDraft.*.value' => 'nullable|string|max:255',
        ], [
            'detail.product_id.required' => 'Selecciona un producto.',
            'detail.product_id.exists' => 'El producto seleccionado no existe.',
            'detail.quantity.required' => 'Ingresa la cantidad.',
            'detail.quantity.integer' => 'La cantidad debe ser un entero.',
            'detail.quantity.min' => 'La cantidad debe ser mayor que cero.',
            'detail.price.required' => 'Ingresa el precio unitario.',
            'detail.price.numeric' => 'El precio debe ser numérico.',
            'detail.price.min' => 'El precio no puede ser negativo.',
        ]);

        /*
         * Si una etiqueta tiene nombre o valor, ambos son obligatorios.
         * Las filas completamente vacías se ignoran.
         */
        $tags = [];

        foreach ($this->tagsDraft as $index => $tag) {
            $name = trim((string) ($tag['name'] ?? ''));
            $value = trim((string) ($tag['value'] ?? ''));

            if ($name === '' && $value === '') {
                continue;
            }

            if ($name === '') {
                $this->addError(
                    "tagsDraft.{$index}.name",
                    'Ingresa el nombre de la etiqueta.'
                );

                return;
            }

            if ($value === '') {
                $this->addError(
                    "tagsDraft.{$index}.value",
                    'Ingresa el valor de la etiqueta.'
                );

                return;
            }

            $tags[] = [
                'name' => $name,
                'value' => $value,
            ];
        }

        $product = Product::findOrFail(
            $this->detail['product_id']
        );

        $this->products[] = [
            'product_id' => $product->id,
            'cod' => $product->cod,
            'name' => $product->name,
            'quantity' => (int) $this->detail['quantity'],
            'price' => (float) $this->detail['price'],
            'tags' => $tags,
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

        $this->tagsDraft = [
            ['name' => '', 'value' => ''],
        ];

        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'contract.delivery_date' => 'required|date',

            'contract.destination' => 'nullable|string|max:255',

            'contract.method_payment' => [
                'required',
                'integer',
                Rule::enum(TypePayment::class),
            ],

            'contract.priority' => 'required|integer|between:1,5',

            'contract.external_id' => 'nullable|string|max:255',

            'contract.industry_id' => [
                'nullable',
                'integer',
                'exists:industries,id',
            ],

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

        DB::transaction(function () {
            /*
             * Crear o actualizar cliente.
             * Sin CI/NIT, el contrato será anónimo.
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
             * Crear detalles y sus etiquetas personalizadas.
             */
            foreach ($this->products as $product) {
                $detail = $contract->details()->create([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                ]);

                foreach ($product['tags'] ?? [] as $tag) {
                    $detail->tags()->create([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                    ]);
                }
            }

            $this->redirectRoute(
                'dashboard.contracts.view',
                $contract
            );
        });
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
            'industries' => Industry::where('type',Type::SUCURSAL)->orderBy('name')->get(),
        ]);
    }
};
