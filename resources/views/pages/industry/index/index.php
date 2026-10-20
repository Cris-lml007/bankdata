<?php

use App\Models\Industry;
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
    public function industries()
    {
        return Industry::query()
            ->with('typeFactory')
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
                            $sub->where('name', 'like', "%{$term}%")
                                ->orWhere('address', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%");

                            foreach (\App\Enums\Type::cases() as $type) {
                                if (
                                    mb_stripos($type->label(), $term) !== false
                                ) {
                                    $sub->orWhere('type', $type->value);
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
            'Nombre' => 'name',
            'Tipo de Area' => null,
            'Area/Sucursal' => 'type',
            'Dirección' => 'address',
            'Teléfono' => 'phone',
            'Acciones' => null,
        ];

        return $this->view(
            compact('heads')
        );
    }
};
