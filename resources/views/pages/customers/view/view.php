
<?php

use App\Enums\Priority;
use App\Enums\Status;
use App\Models\Customer;
use Illuminate\Support\Carbon;
use Livewire\Component;

new class extends Component {
    public Customer $customer;

    public string $productSearch = '';

    public string $contractSearch = '';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Todos los contratos del cliente con sus relaciones.
     */
    public function getContractsProperty()
    {
        return $this->customer
            ->contracts()
            ->with([
                'details.product',
                'details.tags',
                'industry',
            ])
            ->withCount('details')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Divide una búsqueda en términos independientes.
     */
    private function searchTerms(string $search): array
    {
        return preg_split(
            '/\s+/u',
            trim($search),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];
    }

    /**
     * Todos los términos deben coincidir con al menos un campo.
     */
    private function matchesTerms(array $terms, array $fields): bool
    {
        foreach ($terms as $term) {
            $matched = false;

            foreach ($fields as $field) {
                if (
                    $field !== null
                    && mb_stripos((string) $field, $term) !== false
                ) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                return false;
            }
        }

        return true;
    }

    /**
     * Etiqueta legible de estado.
     */
    public function statusLabel($status): string
    {
        if ($status === null || $status === '') {
            return 'Sin estado';
        }

        if ($status instanceof \App\Enums\Status) {
            return $status->label();
        }

        if ($status instanceof \BackedEnum) {
            return method_exists($status, 'label')
                ? $status->label()
                : $status->name;
        }

        if (is_numeric($status)) {
            $enum = \App\Enums\Status::tryFrom((int) $status);

            if ($enum !== null) {
                return $enum->label();
            }
        }

        return (string) $status;
    }

    /**
     * Etiqueta legible de prioridad.
     */
    public function priorityLabel($priority): string
    {
        if ($priority === null || $priority === '') {
            return '—';
        }

        if ($priority instanceof \BackedEnum) {
            return method_exists($priority, 'label')
                ? $priority->label()
                : $priority->name;
        }

        if (is_numeric($priority)) {
            $enum = Priority::tryFrom((int) $priority);

            if ($enum) {
                return $enum->label();
            }
        }

        return (string) $priority;
    }

    /**
     * Campos de búsqueda de un detalle, incluidos sus tags.
     */
    private function detailSearchFields($detail): array
    {
        $fields = [
            $detail->product?->name,
            $detail->product?->cod,
            $detail->product?->model,
            $detail->quantity,
            $detail->price,
        ];

        foreach ($detail->tags as $tag) {
            $fields[] = $tag->name;
            $fields[] = $tag->value;
        }

        return $fields;
    }

    /**
     * Indica si el detalle coincide con la búsqueda de productos.
     */
    public function matchesProductSearch($detail): bool
    {
        $terms = $this->searchTerms($this->productSearch);

        if (empty($terms)) {
            return true;
        }

        return $this->matchesTerms(
            $terms,
            $this->detailSearchFields($detail)
        );
    }

    /**
     * Detalles visibles dentro de un contrato.
     */
    public function filteredDetails($contract)
    {
        return $contract->details
            ->filter(fn ($detail) => $this->matchesProductSearch($detail))
            ->values();
    }

    /**
     * Contratos que coinciden con todos los términos buscados.
     *
     * Busca también en productos y tags de DetailContract.
     */
    public function getFilteredContractsProperty()
    {
        $terms = $this->searchTerms($this->contractSearch);

        if (empty($terms)) {
            return $this->contracts;
        }

        return $this->contracts
            ->filter(function ($contract) use ($terms) {
                $fields = [
                    $contract->id,
                    $contract->external_id,
                    $contract->industry?->name,
                    $contract->delivery_date,
                    $contract->delivery_date
                    ? Carbon::parse($contract->delivery_date)->format('d/m/Y')
                    : null,
                    $this->statusLabel($contract->status),
                    $this->priorityLabel($contract->priority),
                    $this->customer->name,
                    $this->customer->organization,
                    $this->customer->ci_nit,
                ];

                // Agregar datos de los detalles y sus etiquetas.
                foreach ($contract->details as $detail) {
                    $fields = array_merge(
                        $fields,
                        $this->detailSearchFields($detail)
                    );
                }

                return $this->matchesTerms($terms, $fields);
            })
            ->values();
    }

    /**
     * Total contratado histórico del cliente.
     *
     * Se asume que DetailContract usa la columna "price".
     */
    public function getTotalAmountProperty(): float
    {
        return (float) $this->contracts->sum(
            fn ($contract) => $contract->details->sum(
                fn ($detail) =>
                (float) ($detail->price ?? 0)
                    * (float) ($detail->quantity ?? 0)
            )
        );
    }

    /**
     * Unidades contratadas en toda la historia.
     */
    public function getTotalUnitsProperty(): float
    {
        return (float) $this->contracts->sum(
            fn ($contract) => $contract->details->sum(
                fn ($detail) => (float) ($detail->quantity ?? 0)
            )
        );
    }

    /**
     * Productos más comprados.
     *
     * Este ranking sí responde a productSearch.
     */
    public function getTopProductsProperty()
    {
        return $this->contracts
            ->flatMap(fn ($contract) => $contract->details)
            ->filter(fn ($detail) => $this->matchesProductSearch($detail))
            ->groupBy(fn ($detail) =>
                $detail->product_id
                    ?? $detail->product?->id
                        ?? 'deleted-' . $detail->id
            )
            ->map(function ($details) {
                $first = $details->first();

                return (object) [
                    'name' => $first->product?->name ?? 'Producto eliminado',
                    'quantity' => $details->sum(
                        fn ($detail) => (float) ($detail->quantity ?? 0)
                    ),
                    'purchases' => $details->count(),
                    'amount' => $details->sum(
                        fn ($detail) =>
                        (float) ($detail->price ?? 0)
                            * (float) ($detail->quantity ?? 0)
                    ),
                ];
            })
            ->sortByDesc('quantity')
            ->take(10)
            ->values();
    }

    /**
     * Fechas de entrega más frecuentes.
     * Se mantiene como estadística global.
     */
    public function getTopDeliveryDatesProperty()
    {
        return $this->contracts
            ->filter(fn ($contract) => filled($contract->delivery_date))
            ->groupBy(fn ($contract) =>
                Carbon::parse($contract->delivery_date)->format('Y-m-d')
            )
            ->map(fn ($contracts, $date) => (object) [
                'date' => $date,
                'contracts' => $contracts->count(),
                'amount' => $contracts->sum(
                    fn ($contract) => $contract->details->sum(
                        fn ($detail) =>
                        (float) ($detail->price ?? 0)
                            * (float) ($detail->quantity ?? 0)
                    )
                ),
            ])
            ->sortByDesc('contracts')
            ->take(10)
            ->values();
    }

    public function formatAmount($amount): string
    {
        return number_format((float) $amount, 2, ',', '.');
    }
};
?>
