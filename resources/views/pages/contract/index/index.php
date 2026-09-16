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
            ->when($this->search != '', function ($query) {
                $query->where('id', 'like', "%{$this->search}%");
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
            'Cliente' => null,
            'Entrega' => 'delivery_date',
            'Estado' => 'status',
            'Acciones' => null,
        ];

        return $this->view(compact('heads'));
    }
};
