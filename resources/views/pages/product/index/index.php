<?php

use App\Models\Product;
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
    public function products(){
        if($this->search != ''){
            return Product::where('cod','like',"%{ $this->search }%")
                ->orWhere('name','like',"%{ $this->search }%")
                ->orWhere('model','like',"%{ $this->search }%")
                ->orWhere('price_sale','like',"%{ $this->search }%")
                ->orderBy($this->list['sort_by'],$this->list['sort_direction'])
                ->paginate();
        }else{
            return Product::orderBy($this->list['sort_by'],$this->list['sort_direction'])
                ->paginate();
        }
    }




    public function render(){
        $this->search = $this->list['search'];
        $heads = [
            'COD' => 'cod',
            'Nombre' => 'name',
            'Modelo' => 'model',
            'Precio de Compra' => 'price_sale',
            'Acciones' => null
        ];
        return $this->view(compact(['heads']));
    }
};
