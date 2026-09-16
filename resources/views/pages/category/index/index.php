<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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
    public function categories()
    {
        return Category::when($this->search != '', function ($query) {
            $query->where('name', 'like', "%{$this->search}%");
        })
            ->orderBy($this->list['sort_by'], $this->list['sort_direction'])
            ->paginate();
    }

    #[On('category-saved')]
    public function refresh()
    {
        unset($this->categories);
    }

    public function render()
    {
        $this->search = $this->list['search'];

        $heads = [
            'Nombre' => 'name',
            'Acciones' => null,
        ];

        return $this->view(compact('heads'));
    }
};
