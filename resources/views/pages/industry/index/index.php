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
            ->when($this->search != '', function ($query) {
                $query->where(function ($query) {
                    $query->where(
                        'name',
                        'like',
                        "%{$this->search}%"
                    )
                        ->orWhere(
                            'address',
                            'like',
                            "%{$this->search}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$this->search}%"
                        );
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
            'Tipo de fábrica' => null,
            'Dirección' => 'address',
            'Teléfono' => 'phone',
            'Acciones' => null,
        ];

        return $this->view(
            compact('heads')
        );
    }
};
