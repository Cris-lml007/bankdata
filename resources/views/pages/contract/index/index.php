<?php

use App\Models\Contract;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public $search;

    public $list = [
        'sort_by' => 'id',
        'sort_direction' => 'desc',
        'search' => '',
    ];

    #[Computed]
    public function contracts()
    {
        return Contract::query()
            ->with('customer')
            ->when(trim($this->search) !== '', function ($query) {
                $terms = preg_split(
                    '/\s+/u',
                    trim($this->search),
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );

                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->where(function ($sub) use ($term) {
                            // N° de contrato
                            if (is_numeric($term)) {
                                $sub->orWhere('id', $term);
                            }

                            // N° Proforma externa
                            $sub->orWhere(
                                'external_id',
                                'like',
                                "%{$term}%"
                            );

                            // Cliente
                            $sub->orWhereHas('customer', function ($customer) use ($term) {
                                $customer
                                    ->where('name', 'like', "%{$term}%")
                                    ->orWhere('ci_nit', 'like', "%{$term}%")
                                    ->orWhere('organization', 'like', "%{$term}%");
                            });

                            // Fecha de entrega
                            $sub->orWhere(
                                'delivery_date',
                                'like',
                                "%{$term}%"
                            );

                            // Estado por su valor almacenado o su etiqueta
                            foreach (\App\Enums\Status::cases() as $status) {
                                if (
                                    mb_stripos($status->label(), $term) !== false
                                ) {
                                    $sub->orWhere('status', $status->value);
                                }
                            }
                        });
                    }
                });
            })
            ->orderBy(
                $this->list['sort_by'],
                $this->list['sort_direction']
            )
            ->paginate();
    }

    public function render()
    {
        $this->search = $this->list['search'];

        $heads = [
            'N°' => 'id',
            'N° Proforma Externa' => 'external_id',
            'Cliente' => null,
            'Entrega' => 'delivery_date',
            'Estado' => 'status',
            'Acciones' => null,
        ];

        return $this->view(compact('heads'));
    }
};
