<?php

use App\Enums\Status;
use App\Enums\TypePayment;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BuildIndustry;
use App\Models\Assembly;
use App\Models\AssemblyComponent;
use Livewire\Component;

new class extends Component
{
    public Contract $contract;

    public $editingContract = false;

    public $contractData = [
        'delivery_date' => '',
        'destination' => '',
        'method_payment' => '',
        'priority' => 5,
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

    /*
    |--------------------------------------------------------------------------
    | Progreso general
    |--------------------------------------------------------------------------
    */

    public $productionProgress = [];

    public $productionTotal = 0;

    public $productionCompleted = 0;

    public $productionPercentage = 0;

    /*
    |--------------------------------------------------------------------------
    | Árbol de fabricación
    |--------------------------------------------------------------------------
    */

    public $productionTree = [];


    public function mount(Contract $contract)
    {
        $this->contract = $contract;

        $this->loadContract();
    }


    /*
    |--------------------------------------------------------------------------
    | Cargar progreso y árbol de fabricación
    |--------------------------------------------------------------------------
    */

    public function loadProductionProgress()
    {
        $this->contract->load([
            'details.product',
            'assemblies.product',
            'assemblies.industry',
            'assemblies.components.component',
            'assemblies.components.industry',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Obtener todos los IDs
        |--------------------------------------------------------------------------
        */

        $assemblyIds = $this->contract->assemblies
            ->pluck('id');

        $componentIds = $this->contract->assemblies
            ->flatMap(fn ($assembly) =>
            $assembly->components->pluck('id')
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Progreso de ensamblajes
        |--------------------------------------------------------------------------
        */

        $assemblyProgress = BuildIndustry::query()
            ->where('buildeable_type', Assembly::class)
            ->whereIn('buildeable_id', $assemblyIds)
            ->selectRaw(
                'buildeable_id, SUM(quantity) as total'
            )
            ->groupBy('buildeable_id')
            ->pluck('total', 'buildeable_id');


        /*
        |--------------------------------------------------------------------------
        | Progreso de componentes
        |--------------------------------------------------------------------------
        */

        $componentProgress = BuildIndustry::query()
            ->where('buildeable_type', AssemblyComponent::class)
            ->whereIn('buildeable_id', $componentIds)
            ->selectRaw(
                'buildeable_id, SUM(quantity) as total'
            )
            ->groupBy('buildeable_id')
            ->pluck('total', 'buildeable_id');


        /*
        |--------------------------------------------------------------------------
        | Progreso resumido por producto
        |--------------------------------------------------------------------------
        */

        $this->productionProgress = $this->contract->details
            ->map(function ($detail) {

                $assemblies = $this->contract->assemblies
                    ->where('product_id', $detail->product_id);

                $required = (int) $detail->quantity;

                $completed = $assemblies
                    ->where('status', Status::FINISH)
                    ->sum('quantity');

                $pending = max(
                    0,
                    $required - $completed
                );

                $percentage = $required > 0
                    ? min(
                        100,
                        round(
                            ($completed / $required) * 100
                        )
                    )
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

        $this->productionTotal = collect(
            $this->productionProgress
        )->sum('required');

        $this->productionCompleted = collect(
            $this->productionProgress
        )->sum('completed');

        $this->productionPercentage =
            $this->productionTotal > 0
                ? min(
                100,
                round(
                    (
                        $this->productionCompleted
                        / $this->productionTotal
                    ) * 100
                )
            )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Construir árbol de fabricación
        |--------------------------------------------------------------------------
        */

        $this->productionTree = $this->contract->details
            ->map(function ($detail) use (
                $assemblyProgress,
                $componentProgress
            ) {

                $assemblies = $this->contract->assemblies
                    ->where(
                        'product_id',
                        $detail->product_id
                    );


                return [
                    'product_id' => $detail->product_id,

                    'product_name' =>
                        $detail->product?->name
                        ?? 'Producto',

                    'required' => (int) $detail->quantity,

                    'assemblies' => $assemblies
                        ->map(function ($assembly) use (
                            $assemblyProgress,
                            $componentProgress
                        ) {

                            $progress = (int) (
                                $assemblyProgress[$assembly->id]
                                ?? 0
                            );

                            $quantity = (int) $assembly->quantity;

                            $pending = max(
                                0,
                                $quantity - $progress
                            );

                            $percentage = $quantity > 0
                                ? min(
                                    100,
                                    round(
                                        ($progress / $quantity) * 100
                                    )
                                )
                                : 0;


                            /*
                            |--------------------------------------------------------------------------
                            | Componentes de este ensamblaje
                            |--------------------------------------------------------------------------
                            */

                            $components = $assembly->components
                                ->map(function ($component) use (
                                    $componentProgress
                                ) {

                                    $componentProgressValue =
                                        (int) (
                                            $componentProgress[
                                            $component->id
                                            ] ?? 0
                                        );

                                    $quantity =
                                        (int) $component->quantity;

                                    $pending = max(
                                        0,
                                        $quantity
                                        - $componentProgressValue
                                    );

                                    $percentage = $quantity > 0
                                        ? min(
                                            100,
                                            round(
                                                (
                                                    $componentProgressValue
                                                    / $quantity
                                                ) * 100
                                            )
                                        )
                                        : 0;


                                    if (
                                        $component->status
                                        == Status::FINISH->value
                                    ) {
                                        $state = 'Finalizado';
                                        $stateClass = 'success';

                                    } elseif (
                                        $componentProgressValue > 0
                                    ) {
                                        $state = 'En fabricación';
                                        $stateClass = 'info';

                                    } else {
                                        $state = 'Pendiente';
                                        $stateClass = 'secondary';
                                    }


                                    return [
                                        'id' => $component->id,

                                        'component_id' =>
                                            $component->component_id,

                                        'name' =>
                                            $component
                                                ->component
                                                ?->name
                                            ?? 'Componente',

                                        'quantity' => $quantity,

                                        'progress' =>
                                            $componentProgressValue,

                                        'pending' => $pending,

                                        'percentage' =>
                                            $percentage,

                                        'industry_name' =>
                                            $component
                                                ->industry
                                                ?->name
                                            ?? 'Sin fábrica',

                                        'status' =>
                                            $component->status,

                                        'state' => $state,

                                        'state_class' =>
                                            $stateClass,
                                    ];
                                })
                                ->values()
                                ->all();

                            /*
                            |--------------------------------------------------------------------------
                            | Estado del ensamblaje
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $assembly->status
                                === Status::FINISH
                            ) {
                                $state = 'Finalizado';
                                $stateClass = 'success';

                            } elseif ($progress > 0) {
                                $state = 'En fabricación';
                                $stateClass = 'info';

                            } else {
                                $state = 'Pendiente';
                                $stateClass = 'secondary';
                            }


                            return [
                                'id' => $assembly->id,

                                'quantity' => $quantity,

                                'progress' => $progress,

                                'pending' => $pending,

                                'percentage' => $percentage,

                                'industry_name' =>
                                    $assembly
                                        ->industry
                                        ?->name
                                    ?? 'Sin fábrica',

                                'status' =>
                                    $assembly->status,

                                'state' => $state,

                                'state_class' =>
                                    $stateClass,

                                'components' =>
                                    $components,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

    }


    /*
    |--------------------------------------------------------------------------
    | Cargar contrato
    |--------------------------------------------------------------------------
    */

    public function loadContract()
    {
        $this->contract->load([
            'customer',
            'quotation',
            'details.product',
            'assemblies.product',
            'assemblies.industry',
            'assemblies.components.component',
            'assemblies.components.industry',
        ]);


        $this->contractData = [
            'delivery_date' =>
                $this->contract->delivery_date,

            'destination' =>
                $this->contract->destination ?? '',

            'method_payment' =>
                $this->contract->method_payment instanceof TypePayment
                    ? $this->contract->method_payment->value
                    : $this->contract->method_payment,

            'priority' =>
                $this->contract->priority ?? 5,
        ];


        if ($this->contract->customer) {

            $this->customer = [
                'ci_nit' =>
                    $this->contract->customer->ci_nit,

                'name' =>
                    $this->contract->customer->name,

                'email' =>
                    $this->contract->customer->email ?? '',

                'phone' =>
                    $this->contract->customer->phone ?? '',

                'organization' =>
                    $this->contract
                        ->customer
                        ->organization ?? '',
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

                    'product_id' =>
                        $detail->product_id,

                    'cod' =>
                        $detail->product->cod,

                    'name' =>
                        $detail->product->name,

                    'quantity' =>
                        $detail->quantity,

                    'price' =>
                        $detail->price,
                ];
            })
            ->toArray();


        $this->loadProductionProgress();
    }


    /*
    |--------------------------------------------------------------------------
    | Finalizar contrato
    |--------------------------------------------------------------------------
    */

    public function canFinishContract(): bool
    {
        $this->contract->load([
            'details',
            'assemblies',
        ]);

        foreach ($this->contract->details as $detail) {

            $required = (int) $detail->quantity;

            $finished = $this->contract->assemblies
                ->where(
                    'product_id',
                    $detail->product_id
                )
                ->where(
                    'status',
                    Status::FINISH
                )
                ->sum('quantity');

            if ($finished < $required) {
                return false;
            }
        }

        return true;
    }


    public function finishContract()
    {
        if (
            $this->contract->status !==
            Status::ACTIVE
        ) {
            return;
        }

        if (!$this->canFinishContract()) {
            return;
        }

        $this->contract->status =
            Status::FINISH;

        $this->contract->save();

        $this->loadContract();
    }


    /*
    |--------------------------------------------------------------------------
    | Entregar contrato
    |--------------------------------------------------------------------------
    */

    public function markAsDelivered()
    {
        if (
            $this->contract->status !==
            Status::FINISH
        ) {
            return;
        }

        $this->contract->status =
            Status::DELIVERED;

        $this->contract->save();

        $this->loadContract();
    }


    /*
    |--------------------------------------------------------------------------
    | Edición
    |--------------------------------------------------------------------------
    */

    public function editContract()
    {
        $this->loadContract();

        $this->editingContract = true;
    }


    public function updateContract()
    {
        $this->validate([

            'contractData.delivery_date' =>
                'required|date',

            'contractData.destination' =>
                'nullable|string|max:255',

            'contractData.method_payment' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::enum(
                    TypePayment::class
                ),
            ],

            'contractData.priority' =>
                'required|integer|between:1,5',


            'customer.ci_nit' =>
                'nullable|string|max:255',

            'customer.name' =>
                'nullable|string|max:255',

            'customer.email' =>
                'nullable|email|max:255',

            'customer.phone' =>
                'nullable|string|max:255',

            'customer.organization' =>
                'nullable|string|max:255',


            'products.*.quantity' =>
                'required|integer|min:1',

            'products.*.price' =>
                'required|numeric|min:0',
        ]);


        if (empty($this->products)) {

            $this->addError(
                'products',
                'Debe existir al menos un producto.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Cliente
        |--------------------------------------------------------------------------
        */

        $customer = null;

        if (
            trim(
                $this->customer['ci_nit']
            ) !== ''
        ) {

            $customer = Customer::updateOrCreate(
                [
                    'ci_nit' => trim(
                        $this->customer['ci_nit']
                    ),
                ],
                [
                    'name' =>
                        $this->customer['name'],

                    'email' =>
                        $this->customer['email']
                            ?: null,

                    'phone' =>
                        $this->customer['phone']
                            ?: null,

                    'organization' =>
                        $this->customer['organization'],
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Actualizar contrato
        |--------------------------------------------------------------------------
        */

        $this->contract->update([

            'customer_id' =>
                $customer?->id,

            'delivery_date' =>
                $this->contractData[
                'delivery_date'
                ],

            'destination' =>
                $this->contractData[
                'destination'
                ] ?: null,

            'method_payment' =>
                $this->contractData[
                'method_payment'
                ],

            'priority' =>
                $this->contractData[
                'priority'
                ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Detalles
        |--------------------------------------------------------------------------
        */

        $detailIds = [];

        foreach ($this->products as $product) {

            if ($product['id']) {

                $detail =
                    $this->contract
                        ->details()
                        ->findOrFail(
                            $product['id']
                        );

                $detail->update([
                    'product_id' =>
                        $product['product_id'],

                    'quantity' =>
                        $product['quantity'],

                    'price' =>
                        $product['price'],
                ]);

                $detailIds[] =
                    $detail->id;

            } else {

                $detail =
                    $this->contract
                        ->details()
                        ->create([

                            'product_id' =>
                                $product['product_id'],

                            'quantity' =>
                                $product['quantity'],

                            'price' =>
                                $product['price'],
                        ]);

                $detailIds[] =
                    $detail->id;
            }
        }


        $this->contract
            ->details()
            ->whereNotIn(
                'id',
                $detailIds
            )
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


    /*
    |--------------------------------------------------------------------------
    | Clientes
    |--------------------------------------------------------------------------
    */

    public function searchCustomer()
    {
        $ciNit =
            trim(
                $this->customer['ci_nit']
            );


        if ($ciNit === '') {

            $this->contract->customer_id =
                null;

            $this->customer = [
                'ci_nit' => '',
                'name' => '',
                'email' => '',
                'phone' => '',
                'organization' => '',
            ];

            $this->existingCustomer =
                false;

            return;
        }


        $customer =
            Customer::where(
                'ci_nit',
                $ciNit
            )->first();


        if ($customer) {

            $this->contract->customer_id =
                $customer->id;

            $this->customer = [
                'ci_nit' =>
                    $customer->ci_nit,

                'name' =>
                    $customer->name,

                'email' =>
                    $customer->email ?? '',

                'phone' =>
                    $customer->phone ?? '',

                'organization' =>
                    $customer->organization ?? '',
            ];

            $this->existingCustomer =
                true;

        } else {

            $this->contract->customer_id =
                null;

            $this->customer['name'] = '';
            $this->customer['email'] = '';
            $this->customer['phone'] = '';
            $this->customer['organization'] = '';

            $this->existingCustomer =
                false;
        }

        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | Productos
    |--------------------------------------------------------------------------
    */

    public function selectProduct($id)
    {
        $product =
            Product::findOrFail($id);

        $this->selectedProduct =
            $product;

        $this->detail['product_id'] =
            $product->id;

        $this->detail['price'] =
            '';

        $this->productSearch =
            $product->cod
            . ' - '
            . $product->name;

        $this->resetValidation(
            'detail.product_id'
        );
    }


    public function updatedProductSearch()
    {
        $this->selectedProduct =
            null;

        $this->detail['product_id'] =
            '';

        $this->detail['price'] =
            '';
    }


    public function addProduct()
    {
        $this->validate([
            'detail.product_id' =>
                'required|exists:products,id',

            'detail.quantity' =>
                'required|integer|min:1',

            'detail.price' =>
                'required|numeric|min:0',
        ]);


        $product =
            Product::findOrFail(
                $this->detail['product_id']
            );


        $this->products[] = [

            'id' => null,

            'product_id' =>
                $product->id,

            'cod' =>
                $product->cod,

            'name' =>
                $product->name,

            'quantity' =>
                $this->detail['quantity'],

            'price' =>
                $this->detail['price'],
        ];


        $this->resetDetail();
    }


    public function removeProduct($index)
    {
        unset(
            $this->products[$index]
        );

        $this->products =
            array_values(
                $this->products
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
    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar
    |--------------------------------------------------------------------------
    */

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
            strlen(
                trim(
                    $this->productSearch
                )
            ) >= 1 &&
            !$this->selectedProduct
        ) {

            $search =
                trim(
                    $this->productSearch
                );

            $productResults =
                Product::query()
                    ->where(function ($query) use (
                        $search
                    ) {

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
            'productResults' =>
                $productResults,
        ]);
    }
};
