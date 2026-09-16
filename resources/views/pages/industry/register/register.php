<?php

use App\Models\Industry;
use App\Models\TypeFactory;
use Livewire\Component;

new class extends Component
{
    public $industry = [
        'name' => '',
        'type_factory_id' => '',
        'address' => '',
        'phone' => '',
    ];

    public function save()
    {
        $this->validate([
            'industry.name' =>
                'required|string|max:255',

            'industry.type_factory_id' =>
                'required|exists:type_factories,id',

            'industry.address' =>
                'nullable|string|max:255',

            'industry.phone' =>
                'nullable|string|max:255',
        ]);

        $industry = Industry::create(
            $this->industry
        );

        return $this->redirectRoute(
            'dashboard.industries.view',
            $industry
        );
    }

    public function render()
    {
        return $this->view([
            'typeFactories' => TypeFactory::orderBy('name')->get(),
        ]);
    }
};
