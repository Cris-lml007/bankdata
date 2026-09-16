<?php

use App\Models\Category;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $category = [
        'id' => null,
        'name' => '',
    ];

    public $editing = false;

    #[On('getCategory')]
    public function getCategory($id)
    {
        $category = Category::findOrFail($id);

        $this->category = [
            'id' => $category->id,
            'name' => $category->name,
        ];

        $this->editing = true;
    }

    public function save()
    {
        $this->validate([
            'category.name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $this->category['name'],
        ]);

        $this->resetCategory();
        $this->js("$('#modal-category').modal('hide')");
        return $this->redirectRoute('dashboard.categories');
    }

    public function update()
    {
        $this->validate([
            'category.name' => 'required|string|max:255|unique:categories,name,' . $this->category['id'],
        ]);

        Category::findOrFail($this->category['id'])->update([
            'name' => $this->category['name'],
        ]);

        $this->resetCategory();
        $this->js("$('#modal-category').modal('hide')");
        return $this->redirectRoute('dashboard.categories');
    }

    public function resetCategory()
    {
        $this->category = [
            'id' => null,
            'name' => '',
        ];

        $this->editing = false;

        $this->resetValidation();
    }

    public function render()
    {
        return $this->view();
    }
};
