
<?php

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\Status;
use App\Models\Assembly;
use App\Models\AssemblyComponent;
use App\Models\BuildIndustry;
use App\Models\Contract;
use App\Models\DetailContract;
use App\Models\Industry;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public array $assemblies = [];
    public array $assemblyComponents = [];
    public array $assemblyGroups = [];
    public array $receptionContracts = [];
    public array $progressInputs = [];
    public array $specificTasks = [];
    public array $workerOrderedProducts = [];
    public array $managementContractGroups = [];

    public string $managementView = 'cards';

    public array $pendingProgressData = [];
    public bool $showProgressConfirm = false;

    public bool $showTagsModal = false;
    public string $tagsModalTitle = '';
    public array $tagsModalGroups = [];

    public string $search = '';
    public string $priorityFilter = 'all';

    public string $workerView = 'cards';

    // Búsqueda general del panel del trabajador.
    public string $workerSearch = '';

    // Búsquedas independientes.
    public string $workerOrderedSearch = '';
    public string $workerProcessSearch = '';

    public string $workerPriority = '';

    public array $workerAssemblies = [];
    public array $workerAssemblyComponents = [];
    public array $workerAssemblyProcesses = [];

    /*
    |--------------------------------------------------------------------------
    | PAGINACIÓN
    |--------------------------------------------------------------------------
    */

    public function paginateItems(
        $items,
        int $perPage = 8,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $collection = collect($items);

        $perPage = max(1, $perPage);

        $currentPage = max(1, $this->getPage($pageName));

        $total = $collection->count();

        $lastPage = max(
            1,
            (int) ceil($total / $perPage)
        );

        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;

            $this->setPage($currentPage, $pageName);
        }

        $itemsForPage = $collection
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        return new LengthAwarePaginator(
            $itemsForPage,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZACIÓN DE FILTROS
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        abort_unless($this->isManagement(), 403);

        $this->resetPage('managementTablePage');
        $this->resetPage('managementCardsPage');

        $this->buildAssemblyGroups();
    }

    public function updatedPriorityFilter(): void
    {
        abort_unless($this->isManagement(), 403);

        $this->resetPage('managementTablePage');
        $this->resetPage('managementCardsPage');

        $this->buildAssemblyGroups();
    }

    public function updatedWorkerSearch(): void
    {
        abort_unless($this->isWorker(), 403);

        $this->resetWorkerPagination();

        $this->refreshWorkerViews();
    }

    public function updatedWorkerPriority(): void
    {
        abort_unless($this->isWorker(), 403);

        $this->resetWorkerPagination();

        $this->refreshWorkerViews();
    }

    public function updatedWorkerOrderedSearch(): void
    {
        abort_unless($this->isWorker(), 403);

        $this->resetPage('workerOrdersTablePage');
        $this->resetPage('workerAssembliesCardsPage');

        $this->buildWorkerOrderedProducts();
    }

    public function updatedWorkerProcessSearch(): void
    {
        abort_unless($this->isWorker(), 403);

        $this->resetPage('workerProcessesTablePage');
        $this->resetPage('workerProcessesCardsPage');

        $this->buildWorkerAssemblyProcesses();
    }

    private function resetWorkerPagination(): void
    {
        $this->resetPage('workerOrdersTablePage');
        $this->resetPage('workerProcessesTablePage');
        $this->resetPage('workerAssembliesCardsPage');
        $this->resetPage('workerProcessesCardsPage');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLES Y PERMISOS
    |--------------------------------------------------------------------------
    */

    private function roleValue(): int
    {
        $role = auth()->user()->role;

        return (int) (
            $role instanceof \BackedEnum
                ? $role->value
                : $role
        );
    }

    private function isManagement(): bool
    {
        return in_array($this->roleValue(), [
            Role::ADMIN->value,
            Role::PRIVILEGED->value,
            Role::SUPERVISOR->value,
        ], true);
    }

    private function isWorker(): bool
    {
        return $this->roleValue() === Role::WORKER->value;
    }

    private function isReception(): bool
    {
        return $this->roleValue() === Role::RECEPT->value;
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADOS Y PRIORIDADES
    |--------------------------------------------------------------------------
    */

    private function statusValue($status): ?int
    {
        if ($status instanceof \BackedEnum) {
            return (int) $status->value;
        }

        return is_numeric($status)
            ? (int) $status
            : null;
    }

    private function isFinished($status): bool
    {
        return $this->statusValue($status) === Status::FINISH->value;
    }

    private function priorityData($priority): array
    {
        $value = $priority instanceof \BackedEnum
            ? (int) $priority->value
            : (int) ($priority ?? Priority::NORMAL->value);

        $enum = Priority::tryFrom($value)
            ?? Priority::NORMAL;

        $class = match ($enum) {
            Priority::URGENT => 'danger',
            Priority::HIGH => 'warning',
            Priority::MEDIUM => 'info',
            Priority::LOW => 'secondary',
            Priority::NORMAL => 'success',
        };

        return [
            'priority' => $value,
            'priority_label' => $enum->label(),
            'priority_badge' => $class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ETIQUETAS
    |--------------------------------------------------------------------------
    */

    private function tagsToArray($tags): array
    {
        if (!$tags) {
            return [];
        }

        return $tags->map(fn ($tag) => [
            'name' => (string) $tag->name,
            'value' => (string) $tag->value,
        ])->values()->all();
    }

    private function detailContractTags(
        ?int $detailContractId
    ): array {
        if (!$detailContractId) {
            return [];
        }

        return DetailContract::query()
            ->where('id', $detailContractId)
            ->with('tags')
            ->get()
            ->flatMap(fn ($detail) => $detail->tags)
            ->unique(fn ($tag) => implode('|', [
                $tag->name,
                $tag->value,
            ]))
            ->map(fn ($tag) => [
                'name' => (string) $tag->name,
                'value' => (string) $tag->value,
            ])
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | AVANCES DE PRODUCCIÓN
    |--------------------------------------------------------------------------
    */

    private function progressMapForItems(
        Collection $items,
        string $model
    ): array {
        $ids = $items
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return BuildIndustry::query()
            ->where('buildeable_type', $model)
            ->whereIn('buildeable_id', $ids)
            ->selectRaw(
                'buildeable_id, industry_id, SUM(quantity) as total'
            )
            ->groupBy('buildeable_id', 'industry_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->buildeable_id . ':' . $row->industry_id
                    => (int) $row->total,
            ])
            ->all();
    }

    private function progressFor(
        string $model,
        int $id,
        int $industryId
    ): int {
        return (int) BuildIndustry::query()
            ->where('buildeable_type', $model)
            ->where('buildeable_id', $id)
            ->where('industry_id', $industryId)
            ->sum('quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | INICIALIZACIÓN
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->loadTasks();
    }

    /*
    |--------------------------------------------------------------------------
    | CARGA PRINCIPAL
    |--------------------------------------------------------------------------
    */

    public function loadTasks(): void
    {
        $user = auth()->user();

        $industryId = $user->industry_id;

        $this->assemblies = [];
        $this->assemblyComponents = [];
        $this->assemblyGroups = [];
        $this->receptionContracts = [];
        $this->specificTasks = [];
        $this->workerOrderedProducts = [];
        $this->workerAssemblies = [];
        $this->workerAssemblyComponents = [];
        $this->workerAssemblyProcesses = [];

        if ($this->isReception()) {
            $this->loadReceptionContracts();

            return;
        }

        abort_unless(
            $this->isManagement() || $this->isWorker(),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | TAREAS ESPECÍFICAS DEL TRABAJADOR
        |--------------------------------------------------------------------------
        */

        if ($this->isWorker() && $industryId) {
            $this->specificTasks = Task::query()
                ->where('industry_id', $industryId)
                ->where(function ($query) {
                    $query->whereNull('to')
                        ->orWhereDate('to', '>=', today());
                })
                ->orderByRaw('`to` IS NULL')
                ->orderBy('to')
                ->orderBy('id')
                ->get()
                ->map(fn ($task) => [
                    'id' => (int) $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'to' => $task->to,
                ])
                ->values()
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | CONSULTAS
        |--------------------------------------------------------------------------
        */

        $assemblyQuery = Assembly::query()
            ->where('status', Status::ACTIVE)
            ->with([
                'product.tagProducts',
                'contract',
            ]);

        $componentQuery = AssemblyComponent::query()
            ->where('status', Status::ACTIVE)
            ->whereHas('assembly', fn ($query) =>
                $query->where('status', Status::ACTIVE)
            )
            ->with([
                'component.tags',
                'assembly.product.tagProducts',
                'assembly.contract',
            ]);

        if ($this->isWorker()) {
            $assemblyQuery->where('industry_id', $industryId);

            $componentQuery->where('industry_id', $industryId);
        }

        $assemblyModels = $assemblyQuery->get();

        $componentModels = $componentQuery->get();

        /*
        |--------------------------------------------------------------------------
        | ÁREAS
        |--------------------------------------------------------------------------
        */

        $industryIds = $assemblyModels
            ->pluck('industry_id')
            ->merge($componentModels->pluck('industry_id'))
            ->filter()
            ->unique();

        $industries = Industry::query()
            ->whereIn('id', $industryIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | AVANCES AGRUPADOS
        |--------------------------------------------------------------------------
        */

        $assemblyProgress = $this->progressMapForItems(
            $assemblyModels,
            Assembly::class
        );

        $componentProgress = $this->progressMapForItems(
            $componentModels,
            AssemblyComponent::class
        );

        /*
        |--------------------------------------------------------------------------
        | ENSAMBLAJES
        |--------------------------------------------------------------------------
        */

        $this->assemblies = $assemblyModels
            ->sortBy(fn ($item) => [
                $item->contract?->priority
                    ?? Priority::NORMAL->value,
                $item->contract?->delivery_date
                    ?? '9999-12-31',
                $item->contract_id ?? 0,
                $item->id,
            ])
            ->map(function ($item) use (
                $assemblyProgress,
                $industries
            ) {
                $progress = (int) (
                    $assemblyProgress[
                        $item->id . ':' . $item->industry_id
                    ] ?? 0
                );

                $quantity = (int) $item->quantity;

                $product = $item->product;

                $contract = $item->contract;

                $detailId = $item->detail_contract_id
                    ? (int) $item->detail_contract_id
                    : (int) (
                        DetailContract::query()
                            ->where('contract_id', $item->contract_id)
                            ->where('product_id', $item->product_id)
                            ->value('id') ?? 0
                    );

                return [
                    'id' => (int) $item->id,

                    'industry_id' => (int) $item->industry_id,

                    'industry_name' => $industries[
                        $item->industry_id
                    ]?->name ?? 'Área',

                    'contract_id' => $item->contract_id,
                    'external_id' => $contract?->external_id,
                    'delivery_date' => $contract?->delivery_date,

                    ...$this->priorityData($contract?->priority),

                    'product_id' => $item->product_id,
                    'product_cod' => $product?->cod ?? '',
                    'detail_id' => $detailId,

                    'product_name' => $product?->name ?? 'Producto',
                    'product_description' => $product?->description ?? '',

                    'product_tags' => $this->tagsToArray(
                        $product?->tagProducts
                    ),

                    'detail_contract_tags' => $this->detailContractTags(
                        $detailId ?: null
                    ),

                    'quantity' => $quantity,
                    'progress' => $progress,
                    'pending' => max(0, $quantity - $progress),

                    'percentage' => $quantity > 0
                        ? min(
                            100,
                            (int) round(
                                $progress / $quantity * 100
                            )
                        )
                        : 0,

                    'finished' => $this->isFinished($item->status),
                ];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | PROCESOS / COMPONENTES
        |--------------------------------------------------------------------------
        */

        $this->assemblyComponents = $componentModels
            ->sortBy(fn ($item) => [
                $item->assembly?->contract?->priority
                    ?? Priority::NORMAL->value,
                $item->assembly?->contract?->delivery_date
                    ?? '9999-12-31',
                $item->assembly_id ?? 0,
                $item->id,
            ])
            ->map(function ($item) use (
                $componentProgress,
                $industries
            ) {
                $industry = $industries[$item->industry_id] ?? null;

                $component = $item->component;

                $assembly = $item->assembly;

                $product = $assembly?->product;

                $contract = $assembly?->contract;

                $progress = (int) (
                    $componentProgress[
                        $item->id . ':' . $item->industry_id
                    ] ?? 0
                );

                $quantity = (int) $item->quantity;

                $detailId = $item->detail_contract_id
                    ? (int) $item->detail_contract_id
                    : (int) (
                        DetailContract::query()
                            ->where('contract_id', $assembly?->contract_id)
                            ->where('product_id', $assembly?->product_id)
                            ->value('id') ?? 0
                    );

                $showProcessTags = $industry
                    && $component
                    && $component->type_factory_id !== null
                    && (int) $component->type_factory_id
                        === (int) $industry->type_factory_id;

                return [
                    'id' => (int) $item->id,
                    'assembly_id' => (int) $item->assembly_id,
                    'industry_id' => (int) $item->industry_id,

                    'industry_name' => $industry?->name ?? 'Área',

                    'contract_id' => $assembly?->contract_id,
                    'external_id' => $contract?->external_id,
                    'delivery_date' => $contract?->delivery_date,

                    ...$this->priorityData($contract?->priority),

                    'product_name' => $product?->name ?? 'Producto',

                    'product_tags' => $this->tagsToArray(
                        $product?->tagProducts
                    ),

                    'detail_contract_tags' => $this->detailContractTags(
                        $detailId ?: null
                    ),

                    'component_name' => $component?->name ?? 'Componente',

                    'component_description' => $component?->description ?? '',

                    'process_tags' => $showProcessTags
                        ? $this->tagsToArray($component->tags)
                        : [],

                    'quantity' => $quantity,
                    'progress' => $progress,
                    'pending' => max(0, $quantity - $progress),

                    'percentage' => $quantity > 0
                        ? min(
                            100,
                            (int) round(
                                $progress / $quantity * 100
                            )
                        )
                        : 0,

                    'finished' => $this->isFinished($item->status),

                    'component_id' => (int) $item->component_id,
                ];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | PREPARAR VISTAS SEGÚN ROL
        |--------------------------------------------------------------------------
        */

        if ($this->isManagement()) {
            $this->buildAssemblyGroups();
        }

        if ($this->isWorker()) {
            $this->refreshWorkerViews();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA
    |--------------------------------------------------------------------------
    */

    private function searchableText(array $item): string
    {
        $values = [];

        foreach ($item as $key => $value) {
            if (in_array($key, [
                'product_tags',
                'detail_contract_tags',
                'component_tags',
                'process_tags',
                'tags',
            ], true)) {
                foreach ($value ?? [] as $tag) {
                    $values[] = $tag['name'] ?? '';
                    $values[] = $tag['value'] ?? '';
                    $values[] = $tag['source'] ?? '';
                }

                continue;
            }

            if (is_scalar($value) || $value === null) {
                $values[] = (string) ($value ?? '');
            }
        }

        return mb_strtolower(implode(' ', $values));
    }

    private function matchesSearch(
        array $item,
        array $terms
    ): bool {
        $text = $this->searchableText($item);

        foreach ($terms as $term) {
            if (!str_contains($text, $term)) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN: AGRUPACIÓN DE ENSAMBLAJES
    |--------------------------------------------------------------------------
    */

    private function buildAssemblyGroups(): void
    {
        $componentsByAssembly = collect($this->assemblyComponents)
            ->groupBy('assembly_id');

        $groups = collect($this->assemblies)
            ->map(function ($assembly) use ($componentsByAssembly) {
                $processes = $componentsByAssembly
                    ->get($assembly['id'], collect())
                    ->values();

                return [
                    ...$assembly,
                    'processes' => $processes->all(),
                    'process_count' => $processes->count(),
                ];
            });

        // FILTRO DE PRIORIDAD
        if ($this->priorityFilter !== 'all') {
            $priority = (int) $this->priorityFilter;

            $groups = $groups->filter(
                fn ($assembly) =>
                    (int) (
                        $assembly['priority']
                        ?? Priority::NORMAL->value
                    ) === $priority
            );
        }

        $terms = preg_split(
            '/\s+/u',
            mb_strtolower(trim($this->search)),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        if ($terms !== []) {
            $groups = $groups->filter(function ($assembly) use ($terms) {
                if ($this->matchesSearch($assembly, $terms)) {
                    return true;
                }

                foreach ($assembly['processes'] as $process) {
                    if ($this->matchesSearch($process, $terms)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $this->assemblyGroups = $groups
            ->values()
            ->all();

        $this->managementContractGroups = $groups
            ->groupBy(fn ($assembly) => $assembly['contract_id'] ?? 0)
            ->map(function ($items, $contractId) {
                $first = $items->first();

                return [
                    'contract_id' => $contractId ?: null,
                    'external_id' => $first['external_id'] ?? null,
                    'delivery_date' => $first['delivery_date'] ?? null,
                    'priority_label' => $first['priority_label'] ?? '',
                    'priority_badge' => $first['priority_badge'] ?? 'secondary',
                    'assemblies' => $items->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | TRABAJADOR: ACTUALIZAR VISTAS
    |--------------------------------------------------------------------------
    */

    private function refreshWorkerViews(): void
    {
        $this->filterWorkerTasks();

        $this->buildWorkerOrderedProducts();

        $this->buildWorkerAssemblyProcesses();
    }

    private function filterWorkerTasks(): void
    {
        $terms = preg_split(
            '/\s+/u',
            mb_strtolower(trim($this->workerSearch)),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $selectedPriority = $this->workerPriority === ''
            ? null
            : (int) $this->workerPriority;

        $filter = function (array $item) use (
            $terms,
            $selectedPriority
        ) {
            $itemPriority = (int) (
                $item['priority'] ?? Priority::NORMAL->value
            );

            if (
                $selectedPriority !== null
                && $itemPriority !== $selectedPriority
            ) {
                return false;
            }

            return $terms === []
                || $this->matchesSearch($item, $terms);
        };

        $this->workerAssemblies = collect($this->assemblies)
            ->filter($filter)
            ->values()
            ->all();

        $this->workerAssemblyComponents = collect($this->assemblyComponents)
            ->filter($filter)
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | TRABAJADOR: ÓRDENES DE PRODUCTOS
    |--------------------------------------------------------------------------
    */

    private function buildWorkerOrderedProducts(): void
    {
        $assemblies = collect($this->workerAssemblies);

        // Esta búsqueda es independiente de workerSearch.
        $terms = preg_split(
            '/\s+/u',
            mb_strtolower(trim($this->workerOrderedSearch)),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $groups = $assemblies
            ->groupBy(
                fn ($assembly) =>
                    $assembly['product_id']
                    ?? $assembly['product_name']
            )
            ->map(function ($items) use ($terms) {
                $first = $items->first();

                $orderRows = $items->map(fn ($assembly) => [
                    'assembly_id' => $assembly['id'] ?? null,
                    'product_id' => $assembly['product_id'] ?? '',
                    'product_cod' => $assembly['product_cod'] ?? '',
                    'product_name' => $assembly['product_name'] ?? 'Producto',

                    'contract_id' => $assembly['contract_id'] ?? null,
                    'external_id' => $assembly['external_id'] ?? null,
                    'detail_id' => $assembly['detail_id'] ?? null,

                    'tags' => collect([
                        ...($assembly['product_tags'] ?? []),
                        ...($assembly['detail_contract_tags'] ?? []),
                    ])
                        ->unique(
                            fn ($tag) =>
                                ($tag['name'] ?? '')
                                . '|'
                                . ($tag['value'] ?? '')
                        )
                        ->values()
                        ->all(),

                    'product_tags' => $assembly['product_tags'] ?? [],
                    'detail_contract_tags' => $assembly['detail_contract_tags'] ?? [],

                    'priority' => (int) (
                        $assembly['priority']
                        ?? Priority::NORMAL->value
                    ),

                    'priority_label' => $assembly['priority_label'] ?? '',

                    'requested' => (int) ($assembly['quantity'] ?? 0),
                    'produced' => (int) ($assembly['progress'] ?? 0),
                    'pending' => (int) ($assembly['pending'] ?? 0),
                    'progress' => (int) ($assembly['percentage'] ?? 0),
                ])->values();

                if ($terms !== []) {
                    $orderRows = $orderRows
                        ->filter(function ($order) use ($terms) {
                            $searchableOrder = [
                                'product_id' => $order['product_id'],
                                'product_cod' => $order['product_cod'],
                                'product_name' => $order['product_name'],
                                'contract_id' => $order['contract_id'],
                                'external_id' => $order['external_id'],
                                'priority' => $order['priority'],
                                'priority_label' => $order['priority_label'],
                                'product_tags' => $order['product_tags'],
                                'detail_contract_tags' => $order['detail_contract_tags'],
                                'tags' => $order['tags'],
                            ];

                            return $this->matchesSearch(
                                $searchableOrder,
                                $terms
                            );
                        })
                        ->values();
                }

                if ($orderRows->isEmpty()) {
                    return null;
                }

                $requested = (int) $orderRows->sum('requested');
                $produced = (int) $orderRows->sum('produced');
                $pending = (int) $orderRows->sum('pending');

                return [
                    'product_id' => $first['product_id'] ?? '',
                    'cod' => $first['product_cod'] ?? '',
                    'name' => $first['product_name'] ?? 'Producto',

                    'orders' => $orderRows->count(),
                    'order_rows' => $orderRows->all(),

                    'requested' => $requested,
                    'produced' => $produced,
                    'pending' => $pending,

                    'progress' => $requested > 0
                        ? min(
                            100,
                            round(($produced / $requested) * 100, 1)
                        )
                        : 0,
                ];
            })
            ->filter()
            ->values();

        $this->workerOrderedProducts = $groups->all();
    }

    /*
    |--------------------------------------------------------------------------
    | TRABAJADOR: PROCESOS POR COMPONENTE
    |--------------------------------------------------------------------------
    */

    private function buildWorkerAssemblyProcesses(): void
    {
        $processes = collect($this->workerAssemblyComponents);

        // Esta búsqueda es independiente de workerSearch.
        $terms = preg_split(
            '/\s+/u',
            mb_strtolower(trim($this->workerProcessSearch)),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        $groups = $processes
            ->groupBy(
                fn ($process) =>
                    $process['component_id']
                    ?? $process['component_name']
            )
            ->map(function ($items) {
                $first = $items->first();

                $processRows = $items->map(function ($process) {
                    $tags = collect([
                        'detail_contract_tags' => 'Contrato',
                        'product_tags' => 'Producto',
                        'process_tags' => 'Proceso',
                    ])
                        ->flatMap(function ($source, $key) use ($process) {
                            return collect($process[$key] ?? [])
                                ->map(fn ($tag) => [
                                    'source' => $source,
                                    'name' => $tag['name'] ?? '',
                                    'value' => $tag['value'] ?? '',
                                ]);
                        })
                        ->values()
                        ->all();

                    return [
                        'process_id' => $process['id'],

                        'component_id' => $process['component_id'] ?? null,
                        'component_name' => $process['component_name'] ?? 'Componente',
                        'component_description' => $process['component_description'] ?? '',

                        'product_name' => $process['product_name'] ?? 'Producto',

                        'assembly_id' => $process['assembly_id'] ?? null,

                        'contract_id' => $process['contract_id'] ?? null,
                        'external_id' => $process['external_id'] ?? null,

                        'industry_id' => $process['industry_id'] ?? null,
                        'industry_name' => $process['industry_name'] ?? 'Área',

                        'tags' => $tags,

                        'requested' => (int) ($process['quantity'] ?? 0),
                        'produced' => (int) ($process['progress'] ?? 0),
                        'pending' => (int) ($process['pending'] ?? 0),
                        'progress' => (int) ($process['percentage'] ?? 0),

                        'priority_label' => $process['priority_label'] ?? '',

                        'finished' => $process['finished'] ?? false,
                    ];
                })->values();

                return [
                    'component_id' => $first['component_id'] ?? null,
                    'name' => $first['component_name'] ?? 'Componente',
                    'description' => $first['component_description'] ?? '',

                    'process_count' => $processRows->count(),
                    'process_rows' => $processRows->all(),

                    'requested' => (int) $processRows->sum('requested'),
                    'produced' => (int) $processRows->sum('produced'),
                    'pending' => (int) $processRows->sum('pending'),

                    'progress' => $processRows->sum('requested') > 0
                        ? min(
                            100,
                            round(
                                $processRows->sum('produced')
                                / $processRows->sum('requested') * 100,
                                1
                            )
                        )
                        : 0,
                ];
            })
            ->values();

        if ($terms !== []) {
            $groups = $groups
                ->filter(function ($group) use ($terms) {
                    $matchingRows = collect($group['process_rows'])
                        ->filter(
                            fn ($process) =>
                                $this->matchesSearch($process, $terms)
                        )
                        ->values();

                    if ($matchingRows->isEmpty()) {
                        return false;
                    }

                    $group['process_rows'] = $matchingRows->all();

                    $group['process_count'] = $matchingRows->count();

                    $group['requested'] = (int) $matchingRows->sum('requested');
                    $group['produced'] = (int) $matchingRows->sum('produced');
                    $group['pending'] = (int) $matchingRows->sum('pending');

                    $group['progress'] = $group['requested'] > 0
                        ? min(
                            100,
                            round(
                                $group['produced']
                                / $group['requested'] * 100,
                                1
                            )
                        )
                        : 0;

                    return true;
                })
                ->values();
        }

        $this->workerAssemblyProcesses = $groups->all();
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA DEL TRABAJADOR
    |--------------------------------------------------------------------------
    */

    public function toggleWorkerView(): void
    {
        abort_unless($this->isWorker(), 403);

        $this->workerView = $this->workerView === 'cards'
            ? 'table'
            : 'cards';
    }

    /*
    |--------------------------------------------------------------------------
    | RECEPCIÓN
    |--------------------------------------------------------------------------
    */

    private function loadReceptionContracts(): void
    {
        $industryId = auth()->user()->industry_id;

        if (!$industryId) {
            return;
        }

        $contracts = Contract::query()
            ->where('industry_id', $industryId)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere(
                        'status',
                        '!=',
                        Status::DELIVERED->value
                    );
            })
            ->with('industry')
            ->orderByRaw('priority IS NULL')
            ->orderBy('priority')
            ->orderByRaw('delivery_date IS NULL')
            ->orderBy('delivery_date')
            ->get();

        $assemblies = Assembly::query()
            ->whereIn('contract_id', $contracts->pluck('id'))
            ->with(['product.tagProducts'])
            ->get();

        $progressMap = $this->progressMapForItems(
            $assemblies,
            Assembly::class
        );

        $progress = $assemblies
            ->mapWithKeys(fn ($assembly) => [
                $assembly->id => (int) (
                    $progressMap[
                        $assembly->id . ':' . $assembly->industry_id
                    ] ?? 0
                ),
            ])
            ->all();

        $assembliesByContract = $assemblies->groupBy('contract_id');

        $this->receptionContracts = $contracts
            ->filter(fn ($contract) =>
                $this->statusValue($contract->status)
                    !== Status::DELIVERED->value
            )
            ->map(function ($contract) use (
                $assembliesByContract,
                $progress
            ) {
                $items = $assembliesByContract->get(
                    $contract->id,
                    collect()
                );

                $required = (int) $items->sum('quantity');

                $completed = (int) $items->sum(
                    fn ($item) => min(
                        (int) $item->quantity,
                        (int) ($progress[$item->id] ?? 0)
                    )
                );

                return [
                    'id' => (int) $contract->id,
                    'external_id' => $contract->external_id,
                    'industry_name' => $contract->industry?->name ?? 'Área',
                    'delivery_date' => $contract->delivery_date,

                    ...$this->priorityData($contract->priority),

                    'quantity' => $required,
                    'progress' => $completed,
                    'pending' => max(0, $required - $completed),

                    'percentage' => $required > 0
                        ? min(
                            100,
                            (int) round($completed / $required * 100)
                        )
                        : 0,

                    'products' => $items
                        ->map(function ($item) use ($progress) {
                            $quantity = (int) $item->quantity;

                            $done = (int) ($progress[$item->id] ?? 0);

                            return [
                                'name' => $item->product?->name ?? 'Producto',
                                'quantity' => $quantity,
                                'progress' => $done,
                                'pending' => max(0, $quantity - $done),

                                'percentage' => $quantity > 0
                                    ? min(
                                        100,
                                        (int) round(
                                            $done / $quantity * 100
                                        )
                                    )
                                    : 0,
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
    | REGISTRO DE AVANCES
    |--------------------------------------------------------------------------
    */

    public function registerProgress(
        string $type,
        int $id
    ): void {
        abort_unless($this->isManagement(), 403);

        abort_unless(
            in_array($type, ['assembly', 'component'], true),
            404
        );

        $key = "{$type}-{$id}";

        $validated = $this->validate([
            "progressInputs.{$key}" => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            "progressInputs.{$key}.required" => 'Ingresa una cantidad.',
            "progressInputs.{$key}.integer" => 'La cantidad debe ser un entero.',
            "progressInputs.{$key}.min" => 'La cantidad debe ser mayor que cero.',
        ]);

        $quantity = (int) $validated['progressInputs'][$key];

        if ($type === 'assembly') {
            $task = Assembly::query()
                ->where('status', Status::ACTIVE)
                ->findOrFail($id);

            $label = $task->product?->name ?? 'Producto';

            $contractId = $task->contract_id;

            $industryId = $task->industry_id;
        } else {
            $task = AssemblyComponent::query()
                ->where('status', Status::ACTIVE)
                ->whereHas('assembly', fn ($query) =>
                    $query->where('status', Status::ACTIVE)
                )
                ->with(['component', 'assembly.product'])
                ->findOrFail($id);

            $label = ($task->component?->name ?? 'Proceso')
                . ' — '
                . ($task->assembly?->product?->name ?? 'Producto');

            $contractId = $task->assembly?->contract_id;

            $industryId = $task->industry_id;
        }

        $model = $type === 'assembly'
            ? Assembly::class
            : AssemblyComponent::class;

        $current = $this->progressFor(
            $model,
            (int) $task->id,
            (int) $industryId
        );

        $remaining = max(
            0,
            (int) $task->quantity - $current
        );

        if ($quantity > $remaining) {
            $this->addError(
                "progressInputs.{$key}",
                "Solo quedan {$remaining} unidades pendientes."
            );

            return;
        }

        $this->pendingProgressData = [
            'type' => $type,
            'id' => $id,
            'key' => $key,
            'quantity' => $quantity,
            'label' => $label,
            'contract_id' => $contractId,
            'industry_id' => $industryId,
            'remaining' => $remaining,
        ];

        $this->showProgressConfirm = true;
    }

    public function cancelProgressConfirmation(): void
    {
        $this->showProgressConfirm = false;

        $this->pendingProgressData = [];
    }

    public function confirmProgressRegistration(): void
    {
        abort_unless($this->isManagement(), 403);

        $data = $this->pendingProgressData;

        abort_unless(
            isset(
                $data['type'],
                $data['id'],
                $data['quantity']
            ) && in_array(
                $data['type'],
                ['assembly', 'component'],
                true
            ),
            422
        );

        DB::transaction(function () use ($data) {
            if ($data['type'] === 'assembly') {
                $task = Assembly::query()
                    ->where('status', Status::ACTIVE)
                    ->lockForUpdate()
                    ->findOrFail((int) $data['id']);

                $model = Assembly::class;
            } else {
                $task = AssemblyComponent::query()
                    ->where('status', Status::ACTIVE)
                    ->whereHas('assembly', fn ($query) =>
                        $query->where('status', Status::ACTIVE)
                    )
                    ->lockForUpdate()
                    ->findOrFail((int) $data['id']);

                $model = AssemblyComponent::class;
            }

            $current = $this->progressFor(
                $model,
                (int) $task->id,
                (int) $task->industry_id
            );

            $remaining = max(
                0,
                (int) $task->quantity - $current
            );

            $quantity = (int) $data['quantity'];

            if ($quantity < 1 || $quantity > $remaining) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'progress-confirm' => 'El avance cambió y la cantidad ya no está disponible. Vuelve a preparar el registro.',
                ]);
            }

            BuildIndustry::create([
                'industry_id' => $task->industry_id,
                'quantity' => $quantity,
                'buildeable_type' => $model,
                'buildeable_id' => $task->id,
            ]);
        });

        unset($this->progressInputs[$data['key']]);

        $this->showProgressConfirm = false;

        $this->pendingProgressData = [];

        $this->resetValidation();

        $this->loadTasks();
    }

    /*
    |--------------------------------------------------------------------------
    | FINALIZAR TAREAS
    |--------------------------------------------------------------------------
    */

    public function finishTask(
        string $type,
        int $id
    ): void {
        abort_unless($this->isManagement(), 403);

        abort_unless(
            in_array($type, ['assembly', 'component'], true),
            404
        );

        DB::transaction(function () use ($type, $id) {
            if ($type === 'assembly') {
                $task = Assembly::query()
                    ->where('status', Status::ACTIVE)
                    ->lockForUpdate()
                    ->findOrFail($id);

                $model = Assembly::class;
            } else {
                $task = AssemblyComponent::query()
                    ->where('status', Status::ACTIVE)
                    ->whereHas('assembly', fn ($query) =>
                        $query->where('status', Status::ACTIVE)
                    )
                    ->lockForUpdate()
                    ->findOrFail($id);

                $model = AssemblyComponent::class;
            }

            $progress = $this->progressFor(
                $model,
                (int) $task->id,
                (int) $task->industry_id
            );

            if ($progress < (int) $task->quantity) {
                $this->addError(
                    'finish',
                    'La tarea todavía no está completada.'
                );

                return;
            }

            $task->status = Status::FINISH;

            $task->save();
        });

        $this->loadTasks();
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL DE ETIQUETAS
    |--------------------------------------------------------------------------
    */

    public function openProductTags(
        string $type,
        int $id
    ): void {
        abort_unless(
            $this->isManagement() || $this->isWorker(),
            403
        );

        abort_unless(
            in_array($type, ['assembly', 'component'], true),
            404
        );

        $source = $type === 'assembly'
            ? $this->assemblies
            : $this->assemblyComponents;

        $item = collect($source)->firstWhere('id', $id);

        abort_unless($item, 404);

        $this->tagsModalTitle = $item['product_name'] ?? 'Producto';

        $this->tagsModalGroups = [
            'Etiquetas globales del producto' =>
                $item['product_tags'] ?? [],

            'Especificaciones del contrato' =>
                $item['detail_contract_tags'] ?? [],
        ];

        if ($type === 'component') {
            $this->tagsModalGroups['Etiquetas del proceso/área'] =
                $item['process_tags'] ?? [];
        }

        $this->showTagsModal = true;
    }

    public function closeTagsModal(): void
    {
        $this->showTagsModal = false;

        $this->tagsModalGroups = [];
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
