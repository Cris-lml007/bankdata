<?php

use App\Enums\Status;
use App\Models\Assembly;
use App\Models\AssemblyComponent;
use App\Models\BuildIndustry;
use App\Models\Contract;
use Livewire\Component;

new class extends Component
{
    public $contracts = [];

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount()
    {
        $this->loadContracts();
    }

    public function updatedSearch()
    {
        $this->loadContracts();
    }

    public function updatedStatusFilter()
    {
        $this->loadContracts();
    }

    public function loadContracts()
    {
        $query = Contract::with([
            'customer',
            'details.product',
            'assemblies.product',
            'assemblies.components.component',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ESTADOS
        |--------------------------------------------------------------------------
        */

        if ($this->statusFilter === 'active') {

            $query->where('status', Status::ACTIVE);

        } elseif ($this->statusFilter === 'finish') {

            $query->where('status', Status::FINISH);

        } else {

            $query->whereIn('status', [
                Status::ACTIVE,
                Status::FINISH,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BUSCADOR
        |--------------------------------------------------------------------------
        */

        $search = trim($this->search);

        if ($search !== '') {
            $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where(function ($sub) use ($term) {

                        /*
                | Contrato
                */
                        if (is_numeric($term)) {
                            $sub->orWhere('id', $term);
                        }

                        /*
                | Destino
                */
                        $sub->orWhere('destination', 'like', "%{$term}%");

                        /*
                | Cliente
                */
                        $sub->orWhereHas('customer', function ($customer) use ($term) {
                            $customer
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('ci_nit', 'like', "%{$term}%")
                                ->orWhere('organization', 'like', "%{$term}%");
                        });

                        /*
                | Productos
                */
                        $sub->orWhereHas('details.product', function ($product) use ($term) {
                            $product
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('cod', 'like', "%{$term}%");
                        });

                        /*
                | Piezas
                */
                        $sub->orWhereHas(
                            'assemblies.components.component',
                            function ($component) use ($term) {
                                $component
                                    ->where('name', 'like', "%{$term}%")
                                    ->orWhere('description', 'like', "%{$term}%");
                            }
                        );
                    });
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ORDEN
        |--------------------------------------------------------------------------
        */

        $contracts = $query
            ->orderBy('priority')
            ->orderBy('delivery_date')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | IDS DE ASSEMBLIES
        |--------------------------------------------------------------------------
        */

        $assemblyIds = $contracts
            ->flatMap(fn ($contract) => $contract->assemblies)
            ->pluck('id')
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | IDS DE COMPONENTES
        |--------------------------------------------------------------------------
        */

        $assemblyComponentIds = $contracts
            ->flatMap(fn ($contract) => $contract->assemblies)
            ->flatMap(fn ($assembly) => $assembly->components)
            ->pluck('id')
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | AVANCE DE ENSAMBLAJES
        |--------------------------------------------------------------------------
        */

        $assemblyProgress = BuildIndustry::query()
            ->where(
                'buildeable_type',
                Assembly::class
            )
            ->whereIn(
                'buildeable_id',
                $assemblyIds
            )
            ->selectRaw(
                'buildeable_id, SUM(quantity) as total'
            )
            ->groupBy('buildeable_id')
            ->pluck(
                'total',
                'buildeable_id'
            );

        /*
        |--------------------------------------------------------------------------
        | AVANCE DE COMPONENTES
        |--------------------------------------------------------------------------
        */

        $componentProgress = BuildIndustry::query()
            ->where(
                'buildeable_type',
                AssemblyComponent::class
            )
            ->whereIn(
                'buildeable_id',
                $assemblyComponentIds
            )
            ->selectRaw(
                'buildeable_id, SUM(quantity) as total'
            )
            ->groupBy('buildeable_id')
            ->pluck(
                'total',
                'buildeable_id'
            );

        /*
        |--------------------------------------------------------------------------
        | PREPARAR CONTRATOS
        |--------------------------------------------------------------------------
        */

        $this->contracts = $contracts
            ->map(function ($contract) use (
                $assemblyProgress,
                $componentProgress
            ) {

                    $products = $contract->details
                        ->map(function ($detail) use (
                            $contract,
                            $assemblyProgress,
                            $componentProgress
                        ) {

                                $assemblies = $contract->assemblies
                                    ->where(
                                        'product_id',
                                        $detail->product_id
                                    );

                                /*
                        |--------------------------------------------------------------------------
                        | PROGRESO DEL PRODUCTO
                        |--------------------------------------------------------------------------
                        */

                                $progress = $assemblies
                                    ->sum(function ($assembly) use (
                                        $assemblyProgress
                                    ) {

                                            return (int) (
                                                $assemblyProgress[
                                                    $assembly->id
                                                ] ?? 0
                                            );
                                        });

                                $required = (int) $detail->quantity;

                                $pending = max(
                                    0,
                                    $required - $progress
                                );

                                $percentage = $required > 0
                                ? min(
                                    100,
                                    round(
                                        ($progress / $required) * 100
                                    )
                                )
                                : 0;

                                /*
                        |--------------------------------------------------------------------------
                        | PIEZAS
                        |--------------------------------------------------------------------------
                        */

                                $components = $assemblies
                                    ->flatMap(
                                        fn ($assembly) =>
                                        $assembly->components
                                    )
                                    ->groupBy('component_id')
                                    ->map(function ($items) use (
                                        $componentProgress
                                    ) {

                                            $first = $items->first();

                                            $required = (int) $items->sum(
                                                'quantity'
                                            );

                                            $progress = (int) $items->sum(
                                                function ($component) use (
                                                $componentProgress
                                            ) {

                                                    return (int) (
                                                        $componentProgress[
                                                            $component->id
                                                        ] ?? 0
                                                    );
                                                }
                                            );

                                            $pending = max(
                                                0,
                                                $required - $progress
                                            );

                                            $percentage = $required > 0
                                            ? min(
                                                100,
                                                round(
                                                    ($progress / $required) * 100
                                                )
                                            )
                                            : 0;

                                            return [
                                                'id' => $first->component_id,

                                                'name' =>
                                                $first->component?->name
                                                    ?? 'Pieza',

                                                'description' =>
                                                $first->component?->description
                                                    ?? '',

                                                'required' => $required,

                                                'progress' => $progress,

                                                'pending' => $pending,

                                                'percentage' => $percentage,
                                            ];
                                        })
                                    ->sortBy('name')
                                    ->values()
                                    ->all();

                                return [
                                    'id' => $detail->product_id,

                                    'name' =>
                                    $detail->product?->name
                                        ?? 'Producto',

                                    'description' =>
                                    $detail->product?->description
                                        ?? '',

                                    'required' => $required,

                                    'progress' => $progress,

                                    'pending' => $pending,

                                    'percentage' => $percentage,

                                    'components' => $components,
                                ];
                            })
                        ->values()
                        ->all();

                    return [
                        'id' => $contract->id,

                        'customer' =>
                        $contract->customer?->name
                            ?? 'Cliente anónimo',

                        'delivery_date' =>
                        $contract->delivery_date,

                        'destination' =>
                        $contract->destination,

                        'priority' =>
                        ($contract->priority->value ?? 5),

                        'status' =>
                        $contract->status,

                        'status_value' =>
                        $contract->status?->value
                            ?? $contract->status,

                        'products' => $products,
                    ];
                })
            ->values()
            ->all();
    }

    public function render()
    {
        return $this->view();
    }
};
