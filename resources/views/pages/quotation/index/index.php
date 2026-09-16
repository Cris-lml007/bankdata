<?php

use App\Models\Quotation;
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
    public function quotations()
    {
        return Quotation::query()
            ->when($this->search != '', function ($query) {
                $query->where(
                    'id',
                    'like',
                    "%{$this->search}%"
                );
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
            'Desde' => 'valid_from',
            'Hasta' => 'valid_to',
            'Entrega' => 'delivery_date',
            'Acciones' => null,
        ];

        return $this->view(compact('heads'));
    }
};
