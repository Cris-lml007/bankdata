<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    public $title;
    public $heads;

    #[Modelable]
    public $list;

    public $search;
    public $sort_by;
    public $sort_direction;

    public function mount(){
        $this->sort_by = $this->list['sort_by'];
        $this->sort_direction = $this->list['sort_direction'];
    }

    public function updatedSearch(){
        $this->list['search'] = $this->search;
    }


    public function sortBy($field)
    {
        if ($this->sort_by == $field) {
            $this->sort_direction = $this->sort_direction == 'asc' ? 'desc' : 'asc';
            $this->list['sort_direction'] = $this->sort_direction;
        } else {
            $this->sort_by = $field;
            $this->sort_direction = 'asc';
            $this->list['sort_by'] = $field;
            $this->list['sort_direction'] = 'asc';
        }
    }
};
