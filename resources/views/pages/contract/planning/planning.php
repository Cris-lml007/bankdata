<?php

use App\Models\Assembly;
use App\Models\AssemblyComponent;
use App\Models\Contract;
use App\Models\Industry;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public Contract $contract;

    public bool $planningContract = false;

    public array $planning = [];

    public $industries = [];

    public function mount(Contract $contract)
    {
        $this->contract = $contract;

        $this->industries = Industry::orderBy('name')->get();

        $this->loadContract();
    }

    /*
    |--------------------------------------------------------------------------
    | CARGAR CONTRATO
    |--------------------------------------------------------------------------
    */

    public function loadContract()
    {
        $this->contract->load([
            'details.product.componentProducts',

            'assemblies.product',
            'assemblies.industry',

            'assemblies.components.component',
            'assemblies.components.industry',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INICIAR / REPLANIFICAR
    |--------------------------------------------------------------------------
    */

    public function planContract()
    {
        $this->loadContract();

        $this->planning = [];

        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS DEL CONTRATO
        |--------------------------------------------------------------------------
        */

        foreach ($this->contract->details as $detail) {

            /*
             * Assemblies existentes de este producto.
             */

            $assemblies = $this->contract->assemblies
                ->where('product_id', $detail->product_id);

            /*
             |--------------------------------------------------------------------------
             | Si ya existen assemblies
             |--------------------------------------------------------------------------
             */

            if ($assemblies->count()) {

                $assemblyPlans = [];

                foreach ($assemblies as $assembly) {

                    $components = [];

                    foreach ($assembly->components as $assemblyComponent) {

                        $components[] = [
                            'assembly_component_id' =>
                                $assemblyComponent->id,

                            'component_id' =>
                                $assemblyComponent->component_id,

                            'name' =>
                                $assemblyComponent->component->name,

                            'quantity' =>
                                $assemblyComponent->quantity,

                            'industry_id' =>
                                $assemblyComponent->industry_id,
                        ];
                    }

                    /*
                     * Por si el producto tiene nuevos
                     * ComponentProduct que todavía no están
                     * en este Assembly.
                     */

                    $existingComponentIds = collect($components)
                        ->pluck('component_id')
                        ->toArray();

                    foreach (
                        $detail->product->componentProducts
                        as $componentProduct
                    ) {

                        if (
                            in_array(
                                $componentProduct->id,
                                $existingComponentIds
                            )
                        ) {
                            continue;
                        }

                        $components[] = [
                            'assembly_component_id' => null,

                            'component_id' =>
                                $componentProduct->id,

                            'name' =>
                                $componentProduct->name,

                            /*
                             * 1 pieza por unidad del Assembly.
                             */

                            'quantity' =>
                                $assembly->quantity,

                            'industry_id' => '',
                        ];
                    }

                    $assemblyPlans[] = [
                        'assembly_id' =>
                            $assembly->id,

                        'industry_id' =>
                            $assembly->industry_id,

                        'quantity' =>
                            $assembly->quantity,

                        'components' =>
                            $components,
                    ];
                }

                $this->planning[] = [
                    'product_id' =>
                        $detail->product_id,

                    'product_name' =>
                        $detail->product->name,

                    'required_quantity' =>
                        $detail->quantity,

                    'assemblies' =>
                        $assemblyPlans,
                ];

                continue;
            }

            /*
             |--------------------------------------------------------------------------
             | Producto sin planificación
             |--------------------------------------------------------------------------
             */

            $components = [];

            foreach (
                $detail->product->componentProducts
                as $componentProduct
            ) {

                $components[] = [
                    'assembly_component_id' => null,

                    'component_id' =>
                        $componentProduct->id,

                    'name' =>
                        $componentProduct->name,

                    'quantity' => 0,

                    'industry_id' => '',
                ];
            }

            $this->planning[] = [
                'product_id' =>
                    $detail->product_id,

                'product_name' =>
                    $detail->product->name,

                'required_quantity' =>
                    $detail->quantity,

                'assemblies' => [
                    [
                        'assembly_id' => null,

                        'industry_id' => '',

                        'quantity' => 0,

                        'components' => $components,
                    ],
                ],
            ];
        }

        $this->planningContract = true;
    }

    /*
    |--------------------------------------------------------------------------
    | AGREGAR ASSEMBLY
    |--------------------------------------------------------------------------
    */

    public function addAssembly($productIndex)
    {
        $productId =
            $this->planning[$productIndex]['product_id'];

        $detail =
            $this->contract->details
                ->firstWhere('product_id', $productId);

        if (!$detail) {
            return;
        }

        $product =
            $detail->product;

        $components = [];

        foreach ($product->componentProducts as $componentProduct) {

            $components[] = [
                'assembly_component_id' => null,

                'component_id' =>
                    $componentProduct->id,

                'name' =>
                    $componentProduct->name,

                /*
                 * Todavía no tiene cantidad asignada.
                 */

                'quantity' => 0,

                'industry_id' => '',
            ];
        }

        $this->planning[$productIndex]['assemblies'][] = [
            'assembly_id' => null,

            'industry_id' => '',

            'quantity' => 0,

            'components' => $components,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR ASSEMBLY
    |--------------------------------------------------------------------------
    */

    public function removeAssembly(
        $productIndex,
        $assemblyIndex
    ) {

        $assemblies =
            $this->planning[$productIndex]['assemblies'];

        /*
         * Siempre dejamos al menos un Assembly
         * para poder asignar la producción.
         */

        if (count($assemblies) <= 1) {
            return;
        }

        unset(
            $this->planning[$productIndex]['assemblies']
            [$assemblyIndex]
        );

        $this->planning[$productIndex]['assemblies'] =
            array_values(
                $this->planning[$productIndex]['assemblies']
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR CANTIDAD DEL ASSEMBLY
    |--------------------------------------------------------------------------
    */

    public function updatedPlanning($value, $key)
    {
        /*
         * Detectar:
         *
         * assemblies.X.quantity
         */

        if (
            preg_match(
                '/^(\d+)\.assemblies\.(\d+)\.quantity$/',
                $key,
                $matches
            )
        ) {

            $productIndex =
                (int) $matches[1];

            $assemblyIndex =
                (int) $matches[2];

            $quantity =
                max(0, (int) $value);

            /*
             * La cantidad de cada componente es igual
             * a la cantidad del Assembly.
             */

            foreach (
                $this->planning[$productIndex]['assemblies']
                [$assemblyIndex]['components']
                as $componentIndex => $component
            ) {

                $this->planning
                [$productIndex]
                ['assemblies']
                [$assemblyIndex]
                ['components']
                [$componentIndex]
                ['quantity'] = $quantity;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CANTIDAD TOTAL DISTRIBUIDA
    |--------------------------------------------------------------------------
    */

    public function distributedQuantity($assemblies)
    {
        return collect($assemblies)
            ->sum(function ($assembly) {

                return (int) (
                    $assembly['quantity'] ?? 0
                );
            });
    }

    /*
    |--------------------------------------------------------------------------
    | PENDIENTE
    |--------------------------------------------------------------------------
    */

    public function pendingQuantity(
        $required,
        $assemblies
    ) {

        return max(
            0,
            (int) $required
            - $this->distributedQuantity($assemblies)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR
    |--------------------------------------------------------------------------
    */

    public function savePlanning()
    {
        /*
         |--------------------------------------------------------------------------
         | VALIDAR DISTRIBUCIONES
         |--------------------------------------------------------------------------
         */

        foreach ($this->planning as $productIndex => $productPlan) {

            $required =
                (int) $productPlan['required_quantity'];

            $distributed =
                $this->distributedQuantity(
                    $productPlan['assemblies']
                );

            /*
             * Cada Assembly con cantidad > 0 debe tener fábrica.
             */

            foreach (
                $productPlan['assemblies']
                as $assemblyIndex => $assembly
            ) {

                $quantity =
                    (int) ($assembly['quantity'] ?? 0);

                if ($quantity > 0) {

                    $this->validate([
                        "planning.$productIndex.assemblies.$assemblyIndex.industry_id"
                        => 'required|exists:industries,id',

                        "planning.$productIndex.assemblies.$assemblyIndex.quantity"
                        => 'required|integer|min:1',
                    ]);

                    /*
                     * Cada componente también necesita fábrica.
                     */

                    foreach (
                        $assembly['components']
                        as $componentIndex => $component
                    ) {

                        $this->validate([
                            "planning.$productIndex.assemblies.$assemblyIndex.components.$componentIndex.industry_id"
                            => 'required|exists:industries,id',
                        ]);
                    }
                }
            }

            /*
             * La distribución debe ser exactamente igual
             * a la cantidad del contrato.
             */

            if ($distributed !== $required) {

                $this->addError(
                    "planning.$productIndex.assemblies",
                    "La cantidad distribuida debe ser exactamente {$required} unidades."
                );

                return;
            }
        }

        /*
         |--------------------------------------------------------------------------
         | GUARDAR EN TRANSACCIÓN
         |--------------------------------------------------------------------------
         */

        DB::transaction(function () {

            foreach ($this->planning as $productPlan) {

                $productId =
                    $productPlan['product_id'];

                /*
                 * Assemblies existentes de este producto.
                 */

                $existingAssemblies = Assembly::where(
                    'contract_id',
                    $this->contract->id
                )
                    ->where(
                        'product_id',
                        $productId
                    )
                    ->get()
                    ->keyBy('id');

                $usedAssemblyIds = [];

                foreach (
                    $productPlan['assemblies']
                    as $assemblyPlan
                ) {

                    $quantity =
                        (int) $assemblyPlan['quantity'];

                    /*
                     * Ignorar filas sin cantidad.
                     */

                    if ($quantity <= 0) {
                        continue;
                    }

                    /*
                     |--------------------------------------------------------------------------
                     | ASSEMBLY
                     |--------------------------------------------------------------------------
                     */

                    if (
                        !empty(
                        $assemblyPlan['assembly_id']
                        )
                    ) {

                        $assembly =
                            $existingAssemblies->get(
                                $assemblyPlan['assembly_id']
                            );

                        if (!$assembly) {
                            continue;
                        }

                        $assembly->update([
                            'quantity' =>
                                $quantity,

                            'industry_id' =>
                                $assemblyPlan['industry_id'],
                        ]);

                    } else {

                        $assembly = Assembly::create([
                            'product_id' =>
                                $productId,

                            'contract_id' =>
                                $this->contract->id,

                            'quantity' =>
                                $quantity,

                            'industry_id' =>
                                $assemblyPlan['industry_id'],

                            'status' =>
                                \App\Enums\Status::ACTIVE,
                        ]);
                    }

                    $usedAssemblyIds[] =
                        $assembly->id;

                    /*
                     |--------------------------------------------------------------------------
                     | COMPONENTES
                     |--------------------------------------------------------------------------
                     */

                    $usedComponentIds = [];

                    foreach (
                        $assemblyPlan['components']
                        as $componentPlan
                    ) {

                        $componentId =
                            $componentPlan['component_id'];

                        /*
                         * 1 pieza por cada unidad
                         * del Assembly.
                         */

                        $componentQuantity =
                            $quantity;

                        $assemblyComponent =
                            AssemblyComponent::where(
                                'assembly_id',
                                $assembly->id
                            )
                                ->where(
                                    'component_id',
                                    $componentId
                                )
                                ->first();

                        if ($assemblyComponent) {

                            $assemblyComponent->update([
                                'quantity' =>
                                    $componentQuantity,

                                'industry_id' =>
                                    $componentPlan['industry_id'],
                            ]);

                        } else {

                            $assemblyComponent =
                                AssemblyComponent::create([
                                    'assembly_id' =>
                                        $assembly->id,

                                    'component_id' =>
                                        $componentId,

                                    'quantity' =>
                                        $componentQuantity,

                                    'industry_id' =>
                                        $componentPlan['industry_id'],

                                    'status' =>
                                        \App\Enums\Status::ACTIVE,
                                ]);
                        }

                        $usedComponentIds[] =
                            $assemblyComponent->id;
                    }

                    /*
                     * Eliminar componentes que ya no existen
                     * en el Product.
                     */

                    if (!empty($usedComponentIds)) {

                        $assembly->components()
                            ->whereNotIn(
                                'id',
                                $usedComponentIds
                            )
                            ->delete();

                    } else {

                        $assembly->components()->delete();
                    }
                }

                /*
                 |--------------------------------------------------------------------------
                 | ELIMINAR ASSEMBLIES QUITADOS
                 |--------------------------------------------------------------------------
                 */

                if (!empty($usedAssemblyIds)) {

                    Assembly::where(
                        'contract_id',
                        $this->contract->id
                    )
                        ->where(
                            'product_id',
                            $productId
                        )
                        ->whereNotIn(
                            'id',
                            $usedAssemblyIds
                        )
                        ->delete();

                } else {

                    Assembly::where(
                        'contract_id',
                        $this->contract->id
                    )
                        ->where(
                            'product_id',
                            $productId
                        )
                        ->delete();
                }
            }
        });

        $this->planningContract = false;

        $this->planning = [];

        $this->resetValidation();

        $this->loadContract();

        session()->flash(
            'success',
            'La planificación fue guardada correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELAR
    |--------------------------------------------------------------------------
    */

    public function cancelPlanning()
    {
        $this->planningContract = false;

        $this->planning = [];

        $this->resetValidation();

        $this->loadContract();
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return $this->view();
    }
};
