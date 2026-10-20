<?php

namespace App\Livewire;

use App\Enums\Priority;
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

    // Filtros por columna de cada tabla
    public array $orderedColumnFilters = [];
    public array $productionColumnFilters = [];
    public array $industryColumnFilters = [];
    public array $contractsColumnFilters = [];

    // Detalle del producto/DetailContract mostrado en el popup
    public ?array $productPopup = null;

    public function showProductDetails(int $contractId, int $detailId): void
    {
        $contract = Contract::query()
            ->with(['details.product', 'details.tags'])
            ->findOrFail($contractId);

        $detail = $contract->details->firstWhere('id', $detailId);

        abort_unless($detail, 404);

        $this->productPopup = [
            'contract_id' => $contract->id,
            'external_id' => $contract->external_id,
            'detail_id' => $detail->id,
            'product_id' => $detail->product_id,
            'cod' => $detail->product?->cod ?? '—',
            'name' => $detail->product?->name ?? 'Producto eliminado',
            'quantity' => (float) $detail->quantity,
            'price' => (float) $detail->price,
            'tags' => $detail->tags->map(fn ($tag) => [
                'name' => $tag->name,
                'value' => $tag->value,
            ])->values()->all(),
        ];
    }

    public function closeProductDetails(): void
    {
        $this->productPopup = null;
    }

    private function matchesColumnFilters(array $row, array $filters): bool
    {
        foreach ($filters as $key => $value) {
            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            if (!$this->containsSearch($row[$key] ?? '', $value)) {
                return false;
            }
        }

        return true;
    }

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
            'orderedColumnFilters',
            'productionColumnFilters',
            'industryColumnFilters',
            'contractsColumnFilters',
            'productPopup',
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

    /**
     * Búsqueda por términos dentro de un solo valor.
     * Todos los términos deben estar presentes, sin importar el orden.
     */
    private function containsSearch($value, string $search): bool
    {
        $terms = $this->searchTerms($search);

        if ($terms === []) {
            return true;
        }

        $haystack = mb_strtolower((string) $value);

        foreach ($terms as $term) {
            if (!str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    }

    /** Divide una búsqueda en palabras, ignorando espacios repetidos. */
    private function searchTerms(string $search): array
    {
        $search = mb_strtolower(trim($search));

        if ($search === '') {
            return [];
        }

        return preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Búsqueda múltiple entre varios campos: cada término debe coincidir
     * con al menos uno de los campos, pero no necesariamente con el mismo.
     */
    private function matchesSearchAcross(array $values, string $search): bool
    {
        $terms = $this->searchTerms($search);

        if ($terms === []) {
            return true;
        }

        $haystacks = array_map(
            fn ($value) => mb_strtolower((string) $value),
            $values
        );

        foreach ($terms as $term) {
            $found = false;

            foreach ($haystacks as $haystack) {
                if (str_contains($haystack, $term)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return false;
            }
        }

        return true;
    }

    private function priorityLabel($priority): string
    {
        if ($priority instanceof Priority) {
            return $priority->label();
        }

        if (is_numeric($priority)) {
            return Priority::tryFrom((int) $priority)?->label() ?? (string) $priority;
        }

        return (string) ($priority ?? '');
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
                'details.tags',
                'assemblies.product',
                'assemblies.industry',
            ])
            ->when($this->searchTerms($this->search) !== [], function ($query) {
                $terms = $this->searchTerms($this->search);

                // Cada palabra debe encontrarse en algún campo del contrato,
                // cliente, producto o datos personalizados del detalle.
                foreach ($terms as $term) {
                    $like = "%{$term}%";

                    $query->where(function ($q) use ($like) {
                        $q->where('id', 'like', $like)
                            ->orWhere('destination', 'like', $like)
                            ->orWhere('external_id', 'like', $like)
                            ->orWhereHas('customer', function ($customer) use ($like) {
                                $customer->where('name', 'like', $like)
                                    ->orWhere('organization', 'like', $like)
                                    ->orWhere('ci_nit', 'like', $like);
                            })
                            ->orWhereHas('details.product', function ($product) use ($like) {
                                $product->where('name', 'like', $like)
                                    ->orWhere('cod', 'like', $like);
                            })
                            ->orWhereHas('details.tags', function ($tag) use ($like) {
                                $tag->where('name', 'like', $like)
                                    ->orWhere('value', 'like', $like);
                            });
                    });
                }
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
                        'tags_text' => $detail->tags->map(fn ($tag) => $tag->name . ': ' . $tag->value)->implode(' | '),
                        'detail_id' => $detail->id,

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
                        'tags_text' => $detail->tags->map(fn ($tag) => $tag->name . ': ' . $tag->value)->implode(' | '),
                        'detail_id' => $detail->id,

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
                    'detail_id' => $detail->id,
                    'price' => (float) $detail->price,
                    'tags' => $detail->tags->map(fn ($tag) => [
                        'name' => $tag->name,
                        'value' => $tag->value,
                    ])->values()->all(),
                    'tags_text' => $detail->tags
                        ->map(fn ($tag) => trim($tag->name . ': ' . $tag->value))
                        ->implode(' | '),

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

                    'delivery_date' => $contract->delivery_date ? Carbon::parse($contract->delivery_date)->format('d/m/Y') : '',
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
                    'order_rows' => $rows->sortBy(['contract_id', 'detail_id'])->values(),

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
                    'tags_text' => $rows->pluck('tags_text')->filter()->implode(' | '),
                    'contract_id' => $first['contract_id'],
                    'detail_id' => $first['detail_id'] ?? null,

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
            ->map(function ($row) {
                $productValues = [
                    'product_id' => $row['product_id'],
                    'cod' => $row['cod'],
                    'name' => $row['name'],
                ];

                if (!$this->matchesColumnFilters($productValues, array_intersect_key(
                    $this->orderedColumnFilters,
                    array_flip(['product_id', 'cod', 'name'])
                ))) {
                    return null;
                }

                $orderRows = $row['order_rows']->filter(function ($order) {
                    $values = [
                        'contract_id' => $order['contract_id'],
                        'external_id' => $order['external_id'],
                        'tags_text' => $order['tags_text'] ?? '',
                        'requested' => $order['requested'],
                        'price' => $order['price'] ?? 0,
                        'produced' => $order['produced'],
                        'delivered' => $order['delivered'],
                        'pending' => $order['pending'],
                        'progress' => $order['requested'] > 0
                            ? round(($order['produced'] / $order['requested']) * 100, 1)
                            : 0,
                    ];

                    return $this->matchesColumnFilters($values, array_intersect_key(
                        $this->orderedColumnFilters,
                        array_flip(['contract_id', 'external_id', 'tags_text', 'requested', 'price', 'produced', 'delivered', 'pending', 'progress'])
                    ));
                })->values();

                if ($orderRows->isEmpty() && collect($this->orderedColumnFilters)->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty()) {
                    return null;
                }

                $row['visible_order_rows'] = $orderRows;

                // Los totales de la fila principal reflejan las subfilas visibles
                // después de aplicar los filtros por columna.
                $row['orders'] = $orderRows->count();
                $row['requested'] = $orderRows->sum('requested');
                $row['produced'] = $orderRows->sum('produced');
                $row['delivered'] = $orderRows->sum('delivered');
                $row['pending'] = $orderRows->sum('pending');
                $row['progress'] = $row['requested'] > 0
                    ? min(100, round(($row['produced'] / $row['requested']) * 100, 1))
                    : 0;

                return $row;
            })
            ->filter()
            ->filter(fn ($row) => $this->matchesSearchAcross([
                $row['name'], $row['cod'], $row['product_id'],
                $row['order_rows']->pluck('tags_text')->implode(' '),
                $row['order_rows']->pluck('external_id')->implode(' '),
                $row['order_rows']->pluck('contract_id')->implode(' '),
            ], $this->searchOrderedProducts))
            ->values();

        $filteredProductionRows = $productionRows
            ->filter(function ($row) {
                $search = $this->searchProduction;

                $matchesSearch = $this->matchesSearchAcross([
                    $row['contract_id'], $row['external_id'], $row['customer'],
                    $row['destination'], $row['product_name'], $row['product_cod'],
                    $row['status_label'], $this->priorityLabel($row['priority']),
                    $row['tags_text'] ?? '',
                ], $search);

                $columnValues = $row;
                $columnValues['priority'] = $this->priorityLabel($row['priority'] ?? null);

                return $matchesSearch && $this->matchesColumnFilters($columnValues, $this->productionColumnFilters);
            })
            ->values();

        $filteredIndustryRows = $industryRows
            ->filter(function ($row) {
                $matchesSearch = $this->matchesSearchAcross([
                    $row['industry_id'], $row['industry_name'], $row['product_id'],
                    $row['product_name'], $row['product_cod'], $row['contract_id'],
                ], $this->searchIndustry);

                return $matchesSearch && $this->matchesColumnFilters($row, $this->industryColumnFilters);
            })
            ->values();

        $filteredContracts = $contracts
            ->filter(function ($contract) {
                $search = $this->searchContracts;

                $customerName = $contract->customer?->name
                    ?? $contract->customer?->organization
                    ?? 'Cliente anónimo';

                $statusLabel = $this->statusLabel($contract->status);

                $matchesSearch = $this->matchesSearchAcross([
                    $contract->id,
                    $contract->external_id,
                    $customerName,
                    $statusLabel,
                    $this->priorityLabel($contract->priority),
                    $contract->destination,
                    $contract->details->pluck('product.name')->implode(' '),
                    $contract->details->pluck('product.cod')->implode(' '),
                    $contract->details->map(fn ($detail) => $detail->tags
                        ->map(fn ($tag) => $tag->name . ' ' . $tag->value)
                        ->implode(' '))->implode(' '),
                ], $search);

                $contractValues = [
                    'contract_id' => $contract->id,
                    'external_id' => $contract->external_id,
                    'customer' => $customerName,
                    'created_at' => $contract->created_at?->format('d/m/Y'),
                    'delivery_date' => $contract->delivery_date ? Carbon::parse($contract->delivery_date)->format('d/m/Y') : '',
                    'products' => $contract->details->map(fn ($detail) => ($detail->product?->cod ?? '') . ' ' . ($detail->product?->name ?? ''))->implode(' '),
                    'units' => $contract->details->sum('quantity'),
                    'amount' => $contract->details->sum(fn ($detail) => (float) $detail->quantity * (float) $detail->price),
                    'priority' => $this->priorityLabel($contract->priority),
                    'status_label' => $statusLabel,
                ];

                return $matchesSearch && $this->matchesColumnFilters($contractValues, $this->contractsColumnFilters);
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
