<?php

namespace App\Livewire;

use App\Enums\Status;
use App\Models\Assembly;
use App\Models\BuildIndustry;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    // Filtros generales
    public string $search = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $chartGroup = 'month';

    // Búsquedas independientes para cada tabla
    public string $searchOrderedProducts = '';
    public string $searchProduction = '';
    public string $searchIndustry = '';
    public string $searchContracts = '';

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'statusFilter',
            'dateFrom',
            'dateTo',
            'searchOrderedProducts',
            'searchProduction',
            'searchIndustry',
            'searchContracts',
        ]);
    }

    private function statusValue($status): ?int
    {
        if ($status instanceof Status) {
            return $status->value;
        }

        return is_numeric($status) ? (int) $status : null;
    }

    private function statusLabel($status): string
    {
        $value = $this->statusValue($status);

        return $value !== null
            ? (Status::tryFrom($value)?->label() ?? 'Desconocido')
            : 'Desconocido';
    }

    private function statusBadge($status): string
    {
        $value = $this->statusValue($status);

        return $value !== null
            ? (Status::tryFrom($value)?->badgeClass() ?? 'badge-secondary')
            : 'badge-secondary';
    }

    private function containsSearch($value, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        return str_contains(
            mb_strtolower((string) $value),
            mb_strtolower(trim($search))
        );
    }

    /**
     * Consulta base de contratos.
     *
     * Los filtros generales se aplican a todos los reportes.
     */
    private function contractsQuery()
    {
        return Contract::query()
            ->with([
                'customer',
                'details.product',
                'assemblies.product',
                'assemblies.industry',
            ])
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('destination','like',"%{$search}%")
                        ->orWhere('external_id', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%")
                                ->orWhere('organization', 'like', "%{$search}%")
                                ->orWhere('ci_nit', 'like', "%{$search}%");
                        })
                        ->orWhereHas('details.product', function ($product) use ($search) {
                            $product->where('name', 'like', "%{$search}%")
                                ->orWhere('cod', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                $this->statusFilter !== '',
                fn ($query) => $query->where('status', (int) $this->statusFilter)
            )
            ->when(
                $this->dateFrom !== '',
                fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom)
            )
            ->when(
                $this->dateTo !== '',
                fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo)
            );
    }

    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Contratos
        |--------------------------------------------------------------------------
        */

        $contracts = $this->contractsQuery()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. Ensamblajes y cantidades registradas de producción
        |--------------------------------------------------------------------------
        */

        $assemblies = $contracts
            ->flatMap(fn ($contract) => $contract->assemblies)
            ->filter(
                fn ($assembly) =>
                    $this->statusValue($assembly->status) !== Status::CANCELED->value
            );

        $assemblyIds = $assemblies->pluck('id');

        $progress = BuildIndustry::query()
            ->where('buildeable_type', Assembly::class)
            ->whereIn('buildeable_id', $assemblyIds)
            ->selectRaw('buildeable_id, SUM(quantity) as total')
            ->groupBy('buildeable_id')
            ->pluck('total', 'buildeable_id');

        /*
        |--------------------------------------------------------------------------
        | 3. Producción por producto y contrato
        |--------------------------------------------------------------------------
        */

        $productionRows = collect();
        $industryRawRows = collect();

        foreach ($contracts as $contract) {
            foreach ($contract->details as $detail) {
                $requested = max(0, (int) $detail->quantity);

                $productAssemblies = $assemblies
                    ->where('product_id', $detail->product_id)
                    ->filter(
                        fn ($assembly) =>
                            (int) $assembly->contract_id === (int) $contract->id
                    )
                    ->sortBy('id');

                $produced = 0;
                $delivered = 0;
                $remainingRequested = $requested;

                /*
                 * Distribuimos la cantidad solicitada entre los ensamblajes.
                 * Así evitamos contar más unidades de las solicitadas.
                 */
                foreach ($productAssemblies as $assembly) {
                    $assemblyQuantity = max(0, (int) $assembly->quantity);

                    $allocated = min(
                        $remainingRequested,
                        $assemblyQuantity
                    );

                    if ($allocated <= 0) {
                        continue;
                    }

                    $remainingRequested -= $allocated;

                    $status = $this->statusValue($assembly->status);

                    $registered = max(
                        0,
                        (int) ($progress[$assembly->id] ?? 0)
                    );

                    if ($status === Status::DELIVERED->value) {
                        $assemblyProduced = $allocated;
                        $assemblyDelivered = $allocated;
                    } elseif ($status === Status::FINISH->value) {
                        $assemblyProduced = $allocated;
                        $assemblyDelivered = 0;
                    } else {
                        $assemblyProduced = min($allocated, $registered);
                        $assemblyDelivered = 0;
                    }

                    $assemblyProduced = min(
                        $allocated,
                        $assemblyProduced
                    );

                    $assemblyDelivered = min(
                        $assemblyProduced,
                        $assemblyDelivered
                    );

                    $produced += $assemblyProduced;
                    $delivered += $assemblyDelivered;

                    /*
                     * Datos para el reporte agrupado por industria.
                     */
                    $industry = $assembly->industry;

                    $industryRawRows->push([
                        'industry_id' => $contract->industry->name ?? '---',
                        'industry_name' => $industry?->name
                            ?? ($assembly->industry_id
                                ? 'Industria #' . $assembly->industry_id
                                : 'Sin industria'),

                        'product_id' => $detail->product_id,
                        'product_name' => $detail->product?->name
                            ?? 'Producto eliminado',
                        'product_cod' => $detail->product?->cod ?? '-',

                        'contract_id' => $contract->id,
                        'external_id' => $contract->external_id,

                        'requested' => $allocated,
                        'produced' => $assemblyProduced,
                        'delivered' => $assemblyDelivered,
                        'pending' => max(
                            0,
                            $allocated - $assemblyProduced
                        ),
                    ]);
                }

                /*
                 * Si la cantidad solicitada supera la cantidad asignada
                 * a ensamblajes, la diferencia queda bajo "Sin industria".
                 */
                if ($remainingRequested > 0) {
                    $industryRawRows->push([
                        'industry_id' => null,
                        'industry_name' => 'Sin industria',

                        'product_id' => $detail->product_id,
                        'product_name' => $detail->product?->name
                            ?? 'Producto eliminado',
                        'product_cod' => $detail->product?->cod ?? '-',

                        'contract_id' => $contract->id,
                        'external_id' => $contract->external_id,

                        'requested' => $remainingRequested,
                        'produced' => 0,
                        'delivered' => 0,
                        'pending' => $remainingRequested,
                    ]);
                }

                $produced = min($requested, $produced);
                $delivered = min($produced, $delivered);

                $productionStatus = match (true) {
                    $requested > 0 && $produced >= $requested
                        => Status::FINISH,

                    $produced > 0
                        => Status::ACTIVE,

                    default
                        => Status::INACTIVE,
                };

                $productionRows->push([
                    'contract_id' => $contract->id,
                    'external_id' => $contract->external_id,

                    'product_id' => $detail->product_id,
                    'product_name' => $detail->product?->name
                        ?? 'Producto eliminado',
                    'product_cod' => $detail->product?->cod ?? '-',

                    'customer' => $contract->customer?->name
                        ?? $contract->customer?->organization
                        ?? 'Cliente anónimo',

                    'requested' => $requested,
                    'destination' => $contract->destination,
                    'produced' => $produced,
                    'delivered' => $delivered,
                    'pending' => max(0, $requested - $produced),

                    'priority' => $contract->priority ?? null,

                    'status' => $productionStatus,
                    'status_label' => $this->statusLabel($productionStatus),
                    'status_badge' => $this->statusBadge($productionStatus),

                    'delivery_date' => $contract->delivery_date,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Productos ordenados: consolidado por producto
        |--------------------------------------------------------------------------
        */

        $orderedProducts = $productionRows
            ->groupBy('product_id')
            ->map(function (Collection $rows) {
                $first = $rows->first();

                $requested = $rows->sum('requested');
                $produced = $rows->sum('produced');
                $delivered = $rows->sum('delivered');

                return [
                    'product_id' => $first['product_id'],
                    'name' => $first['product_name'],
                    'cod' => $first['product_cod'],

                    'orders' => $rows->count(),
                    'requested' => $requested,
                    'produced' => $produced,
                    'delivered' => $delivered,
                    'pending' => max(0, $requested - $produced),

                    'progress' => $requested > 0
                        ? min(100, round(($produced / $requested) * 100, 1))
                        : 0,
                ];
            })
            ->sortByDesc('requested')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 5. Reporte agrupado por industry_id y producto
        |--------------------------------------------------------------------------
        */

        $industryRows = $industryRawRows
            ->groupBy(fn ($row) =>
                ($row['industry_id'] ?? 'none') . '|' . $row['product_id']
            )
            ->map(function (Collection $rows) {
                $first = $rows->first();

                $requested = $rows->sum('requested');
                $produced = $rows->sum('produced');
                $delivered = $rows->sum('delivered');

                return [
                    'industry_id' => $first['industry_id'],
                    'industry_name' => $first['industry_name'],

                    'product_id' => $first['product_id'],
                    'product_name' => $first['product_name'],
                    'product_cod' => $first['product_cod'],

                    'contracts' => $rows
                        ->pluck('contract_id')
                        ->unique()
                        ->count(),

                    'requested' => $requested,
                    'produced' => $produced,
                    'delivered' => $delivered,
                    'pending' => max(0, $requested - $produced),

                    'progress' => $requested > 0
                        ? min(100, round(($produced / $requested) * 100, 1))
                        : 0,
                ];
            })
            ->sortBy([
                ['industry_id', 'asc'],
                ['product_name', 'asc'],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 6. Totales generales
        |--------------------------------------------------------------------------
        */

        $totalRequested = $productionRows->sum('requested');
        $totalProduced = $productionRows->sum('produced');
        $totalDelivered = $productionRows->sum('delivered');
        $totalPending = $productionRows->sum('pending');

        /*
        |--------------------------------------------------------------------------
        | 7. Contratos por estado
        |--------------------------------------------------------------------------
        */

        $contractsByStatus = collect(Status::cases())
            ->map(function (Status $status) use ($contracts) {
                return [
                    'value' => $status->value,
                    'label' => $status->label(),
                    'badge' => $status->badgeClass(),

                    'count' => $contracts->filter(
                        fn ($contract) =>
                            $this->statusValue($contract->status) === $status->value
                    )->count(),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 8. Datos para el gráfico de productos más solicitados
        |--------------------------------------------------------------------------
        */

        $topProducts = $orderedProducts
            ->sortByDesc('requested')
            ->take(10)
            ->values()
            ->map(fn ($product) => [
                'name' => $product['name'],
                'cod' => $product['cod'],
                'quantity' => $product['requested'],
                'orders' => $product['orders'],
            ]);

        $maxProductQuantity = max(
            1,
            (int) $topProducts->max('quantity')
        );

        /*
        |--------------------------------------------------------------------------
        | 9. Contratos por fecha
        |--------------------------------------------------------------------------
        */

        $dateOrders = $contracts
            ->groupBy(function ($contract) {
                if (!$contract->created_at) {
                    return 'Sin fecha';
                }

                return $this->chartGroup === 'day'
                    ? $contract->created_at->format('Y-m-d')
                    : $contract->created_at->format('Y-m');
            })
            ->map(function (Collection $items, string $key) {
                if ($key === 'Sin fecha') {
                    $label = $key;
                } elseif ($this->chartGroup === 'day') {
                    $label = Carbon::parse($key)->format('d/m/Y');
                } else {
                    $label = Carbon::parse($key . '-01')->format('m/Y');
                }

                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => $items->count(),
                ];
            })
            ->sortBy('key')
            ->values();

        $maxDateOrders = max(
            1,
            (int) $dateOrders->max('count')
        );

        /*
        |--------------------------------------------------------------------------
        | 10. Importe total de los contratos filtrados
        |--------------------------------------------------------------------------
        */

        $totalAmount = $contracts->sum(
            fn ($contract) => $contract->details->sum(
                fn ($detail) =>
                    (float) $detail->quantity * (float) $detail->price
            )
        );

        /*
        |--------------------------------------------------------------------------
        | 11. Filtros independientes de las tablas
        |--------------------------------------------------------------------------
        */

        $filteredOrderedProducts = $orderedProducts
            ->filter(fn ($row) =>
                $this->containsSearch($row['name'], $this->searchOrderedProducts)
                || $this->containsSearch($row['cod'], $this->searchOrderedProducts)
                || $this->containsSearch($row['product_id'], $this->searchOrderedProducts)
            )
            ->values();

        $filteredProductionRows = $productionRows
            ->filter(function ($row) {
                $search = $this->searchProduction;

                return $this->containsSearch($row['contract_id'], $search)
                    || $this->containsSearch($row['external_id'], $search)
                    || $this->containsSearch($row['customer'], $search)
                    || $this->containsSearch($row['destination'], $search)
                    || $this->containsSearch($row['product_name'], $search)
                    || $this->containsSearch($row['product_cod'], $search)
                    || $this->containsSearch($row['status_label'], $search)
                    || $this->containsSearch($row['priority'], $search);
            })
            ->values();

        $filteredIndustryRows = $industryRows
            ->filter(function ($row) {
                $search = $this->searchIndustry;

                return $this->containsSearch($row['industry_id'], $search)
                    || $this->containsSearch($row['industry_name'], $search)
                    || $this->containsSearch($row['product_id'], $search)
                    || $this->containsSearch($row['product_name'], $search)
                    || $this->containsSearch($row['product_cod'], $search);
            })
            ->values();

        $filteredContracts = $contracts
            ->filter(function ($contract) {
                $search = $this->searchContracts;

                if ($search === '') {
                    return true;
                }

                $customerName = $contract->customer?->name
                    ?? $contract->customer?->organization
                    ?? 'Cliente anónimo';

                $statusLabel = $this->statusLabel($contract->status);

                if (
                    $this->containsSearch($contract->id, $search)
                    || $this->containsSearch($contract->external_id, $search)
                    || $this->containsSearch($customerName, $search)
                    || $this->containsSearch($statusLabel, $search)
                    || $this->containsSearch($contract->priority, $search)
                ) {
                    return true;
                }

                return $contract->details->contains(
                    fn ($detail) =>
                        $this->containsSearch(
                            $detail->product?->name,
                            $search
                        )
                        || $this->containsSearch(
                            $detail->product?->cod,
                            $search
                        )
                );
            })
            ->values();

        return $this->view([
            'contracts' => $contracts,
            'filteredContracts' => $filteredContracts,

            'productionRows' => $productionRows,
            'filteredProductionRows' => $filteredProductionRows,

            'orderedProducts' => $orderedProducts,
            'filteredOrderedProducts' => $filteredOrderedProducts,

            'industryRows' => $industryRows,
            'filteredIndustryRows' => $filteredIndustryRows,

            'totalRequested' => $totalRequested,
            'totalProduced' => $totalProduced,
            'totalDelivered' => $totalDelivered,
            'totalPending' => $totalPending,

            'contractsByStatus' => $contractsByStatus,

            'topProducts' => $topProducts,
            'maxProductQuantity' => $maxProductQuantity,

            'dateOrders' => $dateOrders,
            'maxDateOrders' => $maxDateOrders,

            'totalAmount' => $totalAmount,
        ]);
    }
};
