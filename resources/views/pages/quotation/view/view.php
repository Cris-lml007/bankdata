<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use Livewire\Component;

new class extends Component
{
    public Quotation $quotation;

    public $editingQuotation = false;

    public $quotationData = [
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

    public $convertingToContract = false;

    public $contractCustomer = [
        'ci_nit' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'organization' => '',
    ];

    public $contractCustomerFound = false;

    public $contractDeliveryDate = '';

    public function convertToContract()
    {
        $this->contractDeliveryDate =
            $this->quotation->delivery_date;

        $this->contractCustomer = [
            'ci_nit' => '',
            'name' => '',
            'email' => '',
            'phone' => '',
            'organization' => '',
        ];

        $this->contractCustomerFound = false;

        $this->convertingToContract = true;

        $this->resetValidation();
    }

    public function searchContractCustomer()
    {
        $ciNit = trim($this->contractCustomer['ci_nit']);

        if ($ciNit === '') {

            $this->contractCustomerFound = false;

            $this->contractCustomer = [
                'ci_nit' => '',
                'name' => '',
                'email' => '',
                'phone' => '',
                'organization' => '',
            ];

            $this->resetValidation();

            return;
        }

        $customer = Customer::where(
            'ci_nit',
            $ciNit
        )->first();

        if ($customer) {

            $this->contractCustomer = [
                'ci_nit' => $customer->ci_nit,
                'name' => $customer->name,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
                'organization' => $customer->organization ?? '',
            ];

            $this->contractCustomerFound = true;

        } else {

            $this->contractCustomer['name'] = '';
            $this->contractCustomer['email'] = '';
            $this->contractCustomer['phone'] = '';
            $this->contractCustomer['organization'] = '';

            $this->contractCustomerFound = false;
        }

        $this->resetValidation();
    }

    public function cancelConversion()
    {
        $this->convertingToContract = false;

        $this->resetValidation();
    }

    public function createContractFromQuotation()
    {
        $this->validate([
            'contractDeliveryDate' => 'required|date',

            'contractCustomer.ci_nit' =>
                'nullable|string|max:255',

            'contractCustomer.name' =>
                'nullable|string|max:255',

            'contractCustomer.email' =>
                'nullable|email|max:255',

            'contractCustomer.phone' =>
                'nullable|string|max:255',

            'contractCustomer.organization' =>
                'nullable|string|max:255',
        ]);

        if ($this->quotation->details()->count() === 0) {

            $this->addError(
                'conversion',
                'La proforma no tiene productos.'
            );

            return;
        }

        $customer = null;

        /*
         * Si existe CI/NIT, buscamos o creamos
         * el cliente.
         */
        if (trim($this->contractCustomer['ci_nit']) !== '') {

            $customer = Customer::updateOrCreate(
                [
                    'ci_nit' => trim(
                        $this->contractCustomer['ci_nit']
                    ),
                ],
                [
                    'name' =>
                        $this->contractCustomer['name'],

                    'email' =>
                        $this->contractCustomer['email'] ?: null,

                    'phone' =>
                        $this->contractCustomer['phone'] ?: null,

                    'organization' =>
                        $this->contractCustomer['organization'],
                ]
            );
        }

        /*
         * Crear contrato.
         */
        $contract = Contract::create([
            'customer_id' => $customer?->id,
            'quotation_id' => $this->quotation->id,
            'delivery_date' => $this->contractDeliveryDate,
            'status' => \App\Enums\Status::ACTIVE,
        ]);

        /*
         * Copiar los productos de la proforma
         * al contrato.
         */
        foreach ($this->quotation->details as $detail) {

            $contract->details()->create([
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
            ]);
        }

        $this->convertingToContract = false;

        return $this->redirectRoute(
            'dashboard.contracts.view',
            $contract
        );
    }

    public function mount(Quotation $quotation)
    {
        $this->quotation = $quotation;

        $this->loadQuotation();
    }

    public function loadQuotation()
    {
        $this->quotation->load([
            'details.product',
        ]);

        $this->quotationData = [
            'valid_from' => $this->quotation->valid_from,
            'valid_to' => $this->quotation->valid_to,
            'delivery_date' => $this->quotation->delivery_date,
        ];

        $this->products = $this->quotation->details
            ->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'cod' => $detail->product->cod,
                    'name' => $detail->product->name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                ];
            })
            ->toArray();
    }

    public function editQuotation()
    {
        $this->loadQuotation();

        $this->editingQuotation = true;
    }

    public function updateQuotation()
    {
        $this->validate([
            'quotationData.valid_from' => 'required|date',
            'quotationData.valid_to' => 'required|date|after_or_equal:quotationData.valid_from',
            'quotationData.delivery_date' => 'required|date',
        ]);

        if (empty($this->products)) {
            $this->addError(
                'products',
                'Debe agregar al menos un producto.'
            );

            return;
        }

        $this->quotation->update([
            'valid_from' => $this->quotationData['valid_from'],
            'valid_to' => $this->quotationData['valid_to'],
            'delivery_date' => $this->quotationData['delivery_date'],
        ]);

        $detailIds = [];

        foreach ($this->products as $product) {

            if ($product['id']) {

                $detail = $this->quotation->details()
                    ->findOrFail($product['id']);

                $detail->update([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                ]);

                $detailIds[] = $detail->id;

            } else {

                $detail = $this->quotation->details()->create([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                ]);

                $detailIds[] = $detail->id;
            }
        }

        $this->quotation->details()
            ->whereNotIn('id', $detailIds)
            ->delete();

        $this->editingQuotation = false;

        $this->loadQuotation();

        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->editingQuotation = false;

        $this->loadQuotation();

        $this->resetValidation();

        $this->resetDetail();
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

        $this->productSearch =
            $product->cod . ' - ' . $product->name;

        $this->resetValidation('detail.product_id');
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
            'id' => null,
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

    public function deleteQuotation()
    {
        $this->quotation->delete();

        return $this->redirectRoute(
            'dashboard.quotations'
        );
    }

    public function render()
    {
        $productResults = [];

        if (
            $this->editingQuotation &&
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
