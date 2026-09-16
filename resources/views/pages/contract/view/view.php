<?php

use App\Enums\Status;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Product;
use Livewire\Component;

new class extends Component
{
    public Contract $contract;

    public $editingContract = false;

    public $contractData = [
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

    public $productionProgress = [];
    public $productionTotal = 0;
    public $productionCompleted = 0;
    public $productionPercentage = 0;

    public function loadProductionProgress()
    {
        $this->contract->load([
            'details.product',
            'assemblies',
        ]);

        $this->productionProgress = $this->contract->details
            ->map(function ($detail) {

                $assemblies = $this->contract->assemblies
                    ->where('product_id', $detail->product_id);

                $required = (int) $detail->quantity;

                /*
                |--------------------------------------------------------------------------
                | Solo las Assembly finalizadas cuentan como producción terminada
                |--------------------------------------------------------------------------
                */

                $completed = $assemblies
                    ->where('status', \App\Enums\Status::FINISH)
                    ->sum('quantity');

                $pending = max(0, $required - $completed);

                $percentage = $required > 0
                    ? min(100, round(($completed / $required) * 100))
                    : 0;

                return [
                    'product_id' => $detail->product_id,
                    'product_name' => $detail->product?->name ?? 'Producto',
                    'required' => $required,
                    'completed' => $completed,
                    'pending' => $pending,
                    'percentage' => $percentage,
                ];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Progreso general
        |--------------------------------------------------------------------------
        */

        $this->productionTotal = collect($this->productionProgress)
            ->sum('required');

        $this->productionCompleted = collect($this->productionProgress)
            ->sum('completed');

        $this->productionPercentage = $this->productionTotal > 0
            ? min(
                100,
                round(
                    ($this->productionCompleted / $this->productionTotal) * 100
                )
            )
            : 0;
    }

    public function mount(Contract $contract)
    {
        $this->contract = $contract;

        $this->loadContract();
    }

    public function markAsDelivered()
    {
        if ($this->contract->status !== Status::FINISH) {
            return;
        }

        $this->contract->status = Status::DELIVERED;
        $this->contract->save();

        $this->loadContract();
    }

    public function finishContract()
    {
        if ($this->contract->status !== Status::ACTIVE) {
            return;
        }

        if (!$this->canFinishContract()) {
            return;
        }

        $this->contract->status = Status::FINISH;
        $this->contract->save();

        $this->loadContract();
    }

    public function canFinishContract(): bool
    {
        $this->contract->load([
            'details',
            'assemblies',
        ]);

        foreach ($this->contract->details as $detail) {

            $required = (int) $detail->quantity;

            $finished = $this->contract->assemblies
                ->where('product_id', $detail->product_id)
                ->where('status', Status::FINISH)
                ->sum('quantity');

            if ($finished < $required) {
                return false;
            }
        }

        return true;
    }

    public function getProductionProgress()
    {
        $this->contract->load([
            'details.product',
            'assemblies.product',
            'assemblies.components',
        ]);

        return $this->contract->details->map(function ($detail) {

            $assemblies = $this->contract->assemblies
                ->where('product_id', $detail->product_id);

            $required = (int) $detail->quantity;

            $finished = $assemblies
                ->where('status', Status::FINISH)
                ->sum('quantity');

            $pending = max(0, $required - $finished);

            $percentage = $required > 0
                ? min(100, round(($finished / $required) * 100))
                : 0;

            return [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product?->name ?? 'Producto',
                'required' => $required,
                'finished' => $finished,
                'pending' => $pending,
                'percentage' => $percentage,
            ];
        })->values()->all();
    }

    public function loadContract()
    {
        $this->contract->load([
            'customer',
            'quotation',
            'details.product',
            'assemblies.product',
            'assemblies.components.component'
        ]);

        $this->contractData = [
            'delivery_date' => $this->contract->delivery_date,
        ];

        if ($this->contract->customer) {

            $this->customer = [
                'ci_nit' => $this->contract->customer->ci_nit,
                'name' => $this->contract->customer->name,
                'email' => $this->contract->customer->email ?? '',
                'phone' => $this->contract->customer->phone ?? '',
                'organization' => $this->contract->customer->organization ?? '',
            ];

            $this->existingCustomer = true;

        } else {

            $this->customer = [
                'ci_nit' => '',
                'name' => '',
                'email' => '',
                'phone' => '',
                'organization' => '',
            ];

            $this->existingCustomer = false;
        }

        $this->products = $this->contract->details
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

        $this->loadProductionProgress();
    }

    public function editContract()
    {
        $this->loadContract();

        $this->editingContract = true;
    }

    public function searchCustomer()
    {
        $ciNit = trim($this->customer['ci_nit']);

        /*
         * CI/NIT vacío:
         * el contrato será anónimo.
         */
        if ($ciNit === '') {

            $this->contract->customer_id = null;

            $this->customer = [
                'ci_nit' => '',
                'name' => '',
                'email' => '',
                'phone' => '',
                'organization' => '',
            ];

            $this->existingCustomer = false;

            return;
        }

        $customer = Customer::where(
            'ci_nit',
            $ciNit
        )->first();

        if ($customer) {

            $this->contract->customer_id = $customer->id;

            $this->customer = [
                'ci_nit' => $customer->ci_nit,
                'name' => $customer->name,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
                'organization' => $customer->organization ?? '',
            ];

            $this->existingCustomer = true;

        } else {

            $this->contract->customer_id = null;

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
    }

    public function updateContract()
    {
        $this->validate([
            'contractData.delivery_date' => 'required|date',

            'customer.ci_nit' => 'nullable|string|max:255',
            'customer.name' => 'nullable|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.phone' => 'nullable|string|max:255',
            'customer.organization' => 'nullable|string|max:255',

            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        if (empty($this->products)) {

            $this->addError(
                'products',
                'Debe existir al menos un producto.'
            );

            return;
        }

        /*
         * Cliente
         *
         * CI/NIT vacío = contrato anónimo.
         */
        $customer = null;

        if (trim($this->customer['ci_nit']) !== '') {

            $customer = Customer::updateOrCreate(
                [
                    'ci_nit' => trim(
                        $this->customer['ci_nit']
                    ),
                ],
                [
                    'name' => $this->customer['name'],
                    'email' => $this->customer['email'] ?: null,
                    'phone' => $this->customer['phone'] ?: null,
                    'organization' => $this->customer['organization'],
                ]
            );
        }

        /*
         * Actualizar contrato.
         */
        $this->contract->update([
            'customer_id' => $customer?->id,
            'delivery_date' => $this->contractData['delivery_date'],
        ]);

        /*
         * Guardamos los detalles existentes
         * y creamos los nuevos.
         */
        $detailIds = [];

        foreach ($this->products as $product) {

            if ($product['id']) {

                $detail = $this->contract->details()
                    ->findOrFail($product['id']);

                $detail->update([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                ]);

                $detailIds[] = $detail->id;

            } else {

                $detail = $this->contract->details()->create([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                ]);

                $detailIds[] = $detail->id;
            }
        }

        /*
         * Eliminar de la BD los detalles
         * que fueron quitados de la interfaz.
         */
        $this->contract->details()
            ->whereNotIn('id', $detailIds)
            ->delete();

        $this->editingContract = false;

        $this->loadContract();

        $this->resetDetail();

        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->editingContract = false;

        $this->loadContract();

        $this->resetDetail();

        $this->resetValidation();
    }

    public function deleteContract()
    {
        $this->contract->delete();

        return $this->redirectRoute(
            'dashboard.contracts'
        );
    }

    public function render()
    {
        $productResults = [];

        if (
            $this->editingContract &&
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
