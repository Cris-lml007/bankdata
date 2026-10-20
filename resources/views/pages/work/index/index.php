
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
use Livewire\Component;

new class extends Component
{
    public array $assemblies = [];
    public array $assemblyComponents = [];
    public array $assemblyGroups = [];
    public array $receptionContracts = [];
    public array $progressInputs = [];
    public array $specificTasks = [];

    public string $search = '';

    public function mount(): void
    {
        $this->loadTasks();
    }

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

    private function statusValue($status): ?int
    {
        if ($status instanceof \BackedEnum) {
            return (int) $status->value;
        }

        return is_numeric($status) ? (int) $status : null;
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
            Priority::NORMAL => 'light',
        };

        return [
            'priority' => $value,
            'priority_label' => $enum->label(),
            'priority_badge' => $class,
        ];
    }

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

    /**
     * Etiquetas del detalle del contrato correspondiente
     * al producto solicitado en el contrato.
     */
    private function detailContractTags(
        ?int $contractId,
        ?int $productId
    ): array {
        if (!$contractId || !$productId) {
            return [];
        }

        return DetailContract::query()
            ->where('contract_id', $contractId)
            ->where('product_id', $productId)
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

    public function updatedSearch(): void
    {
        if ($this->isManagement()) {
            $this->buildAssemblyGroups();
        }
    }

    public function loadTasks(): void
    {
        $user = auth()->user();
        $industryId = $user->industry_id;

        $this->assemblies = [];
        $this->assemblyComponents = [];
        $this->assemblyGroups = [];
        $this->receptionContracts = [];
        $this->specificTasks = [];

        if ($this->isReception()) {
            $this->loadReceptionContracts();
            return;
        }

        abort_unless(
            $this->isManagement() || $this->isWorker(),
            403
        );

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

        $industryIds = $assemblyModels->pluck('industry_id')
            ->merge($componentModels->pluck('industry_id'))
            ->filter()
            ->unique();

        $industries = Industry::query()
            ->whereIn('id', $industryIds)
            ->get()
            ->keyBy('id');

        $assemblyProgress = [];

        foreach ($assemblyModels as $assembly) {
            $assemblyProgress[$assembly->id] = $this->progressFor(
                Assembly::class,
                (int) $assembly->id,
                (int) $assembly->industry_id
            );
        }

        $componentProgress = [];

        foreach ($componentModels as $component) {
            $componentProgress[$component->id] = $this->progressFor(
                AssemblyComponent::class,
                (int) $component->id,
                (int) $component->industry_id
            );
        }

        /*
         * ENSAMBLAJES
         */
        $this->assemblies = $assemblyModels
            ->sortBy(fn ($item) => [
                $item->contract?->priority ?? Priority::NORMAL->value,
                $item->contract?->delivery_date ?? '9999-12-31',
                $item->contract_id ?? 0,
                $item->id,
            ])
            ->map(function ($item) use ($assemblyProgress, $industries) {
                $progress = (int) ($assemblyProgress[$item->id] ?? 0);
                $quantity = (int) $item->quantity;
                $product = $item->product;
                $contract = $item->contract;

                return [
                    'id' => (int) $item->id,
                    'industry_id' => (int) $item->industry_id,
                    'industry_name' => $industries[$item->industry_id]?->name
                        ?? 'Área',

                    'contract_id' => $item->contract_id,
                    'external_id' => $contract?->external_id,
                    'delivery_date' => $contract?->delivery_date,

                    ...$this->priorityData($contract?->priority),

                    'product_name' => $product?->name ?? 'Producto',
                    'product_description' => $product?->description ?? '',

                    'product_tags' => $this->tagsToArray(
                        $product?->tagProducts
                    ),

                    'detail_contract_tags' => $this->detailContractTags(
                        $item->contract_id
                            ? (int) $item->contract_id
                            : null,
                        $item->product_id
                            ? (int) $item->product_id
                            : null
                    ),

                    'quantity' => $quantity,
                    'progress' => $progress,
                    'pending' => max(0, $quantity - $progress),
                    'percentage' => $quantity > 0
                        ? min(100, (int) round($progress / $quantity * 100))
                        : 0,

                    'finished' => $this->isFinished($item->status),
                ];
            })
            ->values()
            ->all();

        /*
         * PROCESOS / COMPONENTES
         */
        $this->assemblyComponents = $componentModels
            ->sortBy(fn ($item) => [
                $item->assembly?->contract?->priority
                    ?? Priority::NORMAL->value,
                $item->assembly?->contract?->delivery_date ?? '9999-12-31',
                $item->assembly_id ?? 0,
                $item->id,
            ])
            ->map(function ($item) use ($componentProgress, $industries) {
                $industry = $industries[$item->industry_id] ?? null;
                $component = $item->component;
                $assembly = $item->assembly;
                $product = $assembly?->product;
                $contract = $assembly?->contract;

                $progress = (int) ($componentProgress[$item->id] ?? 0);
                $quantity = (int) $item->quantity;

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
                        $assembly?->contract_id
                            ? (int) $assembly->contract_id
                            : null,
                        $assembly?->product_id
                            ? (int) $assembly->product_id
                            : null
                    ),

                    'component_name' => $component?->name ?? 'Componente',
                    'component_description' => $component?->description ?? '',

                    'component_tags' => $this->tagsToArray(
                        $component?->tags
                    ),

                    'process_tags' => $showProcessTags
                        ? $this->tagsToArray($component->tags)
                        : [],

                    'quantity' => $quantity,
                    'progress' => $progress,
                    'pending' => max(0, $quantity - $progress),
                    'percentage' => $quantity > 0
                        ? min(100, (int) round($progress / $quantity * 100))
                        : 0,

                    'finished' => $this->isFinished($item->status),
                ];
            })
            ->values()
            ->all();

        if ($this->isManagement()) {
            $this->buildAssemblyGroups();
        }
    }

    /**
     * Devuelve un texto normalizado con los datos de búsqueda.
     * Cada término se comprobará por separado, de modo que
     * "cuello alto" coincida aunque sean campos diferentes.
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
            ], true)) {
                foreach ($value ?? [] as $tag) {
                    $values[] = $tag['name'] ?? '';
                    $values[] = $tag['value'] ?? '';
                }

                continue;
            }

            if (is_scalar($value) || $value === null) {
                $values[] = (string) ($value ?? '');
            }
        }

        return mb_strtolower(implode(' ', $values));
    }

    private function matchesSearch(array $item, array $terms): bool
    {
        $text = $this->searchableText($item);

        foreach ($terms as $term) {
            if (!str_contains($text, $term)) {
                return false;
            }
        }

        return true;
    }

    private function buildAssemblyGroups(): void
    {
        $componentsByAssembly = collect($this->assemblyComponents)
            ->groupBy('assembly_id');

        $groups = collect($this->assemblies)->map(function ($assembly) use (
            $componentsByAssembly
        ) {
            $processes = $componentsByAssembly
                ->get($assembly['id'], collect())
                ->values();

            return [
                ...$assembly,
                'processes' => $processes->all(),
                'process_count' => $processes->count(),
            ];
        });

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

        $this->assemblyGroups = $groups->values()->all();
    }

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
                    ->orWhere('status', '!=', Status::DELIVERED->value);
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

        $progress = [];

        foreach ($assemblies as $assembly) {
            $progress[$assembly->id] = $this->progressFor(
                Assembly::class,
                (int) $assembly->id,
                (int) $assembly->industry_id
            );
        }

        $assembliesByContract = $assemblies->groupBy('contract_id');

        $this->receptionContracts = $contracts
            ->filter(fn ($contract) =>
                $this->statusValue($contract->status)
                    !== Status::DELIVERED->value
            )
            ->map(function ($contract) use ($assembliesByContract, $progress) {
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
                        ? min(100, (int) round($completed / $required * 100))
                        : 0,

                    'products' => $items->map(function ($item) use ($progress) {
                        $quantity = (int) $item->quantity;
                        $done = (int) ($progress[$item->id] ?? 0);

                        return [
                            'name' => $item->product?->name ?? 'Producto',
                            'quantity' => $quantity,
                            'progress' => $done,
                            'pending' => max(0, $quantity - $done),
                            'percentage' => $quantity > 0
                                ? min(100, (int) round($done / $quantity * 100))
                                : 0,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function registerProgress(string $type, int $id): void
    {
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

        DB::transaction(function () use ($type, $id, $quantity, $key) {
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

            $current = $this->progressFor(
                $model,
                (int) $task->id,
                (int) $task->industry_id
            );

            $remaining = max(0, (int) $task->quantity - $current);

            if ($quantity > $remaining) {
                $this->addError(
                    "progressInputs.{$key}",
                    "Solo quedan {$remaining} unidades pendientes."
                );

                return;
            }

            BuildIndustry::create([
                'industry_id' => $task->industry_id,
                'quantity' => $quantity,
                'buildeable_type' => $model,
                'buildeable_id' => $task->id,
            ]);
        });

        unset($this->progressInputs[$key]);

        $this->loadTasks();
    }

    public function finishTask(string $type, int $id): void
    {
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

    public function render()
    {
        return $this->view();
    }
};
