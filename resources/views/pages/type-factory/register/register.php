<?php

use App\Models\TypeFactory;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $typeFactory = [
        'id' => null,
        'name' => '',
        'description' => '',
    ];

    public $editing = false;

    #[On('getTypeFactory')]
    public function getTypeFactory($id)
    {
        $typeFactory = TypeFactory::findOrFail($id);

        $this->typeFactory = [
            'id' => $typeFactory->id,
            'name' => $typeFactory->name,
            'description' => $typeFactory->description,
        ];

        $this->editing = true;
    }

    public function save()
    {
        $this->validate([
            'typeFactory.name' => 'required|string|max:255|unique:type_factories,name',
            'typeFactory.description' => 'nullable|string',
        ]);

        TypeFactory::create([
            'name' => $this->typeFactory['name'],
            'description' => $this->typeFactory['description'],
        ]);

        $this->js("$('#modal-type-factory').modal('hide')");

        return $this->redirectRoute('dashboard.type-factories');
    }

    public function update()
    {
        $this->validate([
            'typeFactory.name' => 'required|string|max:255|unique:type_factories,name,' . $this->typeFactory['id'],
            'typeFactory.description' => 'nullable|string',
        ]);

        TypeFactory::findOrFail($this->typeFactory['id'])->update([
            'name' => $this->typeFactory['name'],
            'description' => $this->typeFactory['description'],
        ]);

        $this->js("$('#modal-type-factory').modal('hide')");

        return $this->redirectRoute('dashboard.type-factories');
    }

    public function resetTypeFactory()
    {
        $this->typeFactory = [
            'id' => null,
            'name' => '',
            'description' => '',
        ];

        $this->editing = false;

        $this->resetValidation();
    }

    public function render()
    {
        return $this->view();
    }
};
