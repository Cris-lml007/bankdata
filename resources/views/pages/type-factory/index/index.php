<?php

use App\Models\TypeFactory;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public $search;

    public $list = [
        'sort_by' => 'name',
        'sort_direction' => 'asc',
        'search' => ''
    ];

    #[Computed]
    public function typeFactories()
    {
        return TypeFactory::when($this->search != '', function ($query) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%");
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
            'Nombre' => 'name',
            'Descripción' => 'description',
            'Acciones' => null,
        ];

        return $this->view(compact('heads'));
    }
};
