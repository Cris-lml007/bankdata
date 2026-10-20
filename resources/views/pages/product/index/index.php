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
        $search = trim($this->search);

        $query = Product::query();

        if ($search !== '') {
            $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('cod', 'like', "%{$term}%")
                            ->orWhere('name', 'like', "%{$term}%")
                            ->orWhere('model', 'like', "%{$term}%")
                            ->orWhere('price_sale', 'like', "%{$term}%")
                            ->orWhereHas('categories',function ($w) use($term){
                                $w->where('name','like',"%{$term}%");
                            });
                    });
                }
            });
        }

        return $query
            ->orderBy(
                $this->list['sort_by'],
                $this->list['sort_direction']
            )
            ->paginate();
    }




    public function render(){
        $this->search = $this->list['search'];
        $heads = [
            'COD' => 'cod',
            'Nombre' => 'name',
            'Modelo' => 'model',
            'Categorias' => null,
            'Precio de Compra' => 'price_sale',
            'Acciones' => null
        ];
        return $this->view(compact(['heads']));
    }
};
