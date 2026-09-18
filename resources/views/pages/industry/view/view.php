<?php

use App\Models\Industry;
use Livewire\Component;

new class extends Component
{
    public Industry $industry;

    public $editingIndustry = false;

    public $industryData = [
        'name' => '',
        'type_factory_id' => '',
        'address' => '',
        'phone' => '',
    ];

    public function mount(Industry $industry)
    {
        $this->industry = $industry;

        $this->loadIndustry();
    }

    public function loadIndustry()
    {
        $this->industry->load([
            'typeFactory',
            'users',
            'assemblies.product',
            'assemblies.contract.customer',
            'assemblyComponents.component',
            'assemblyComponents.assembly.contract.customer',
            'builds.buildeable',
        ]);

        $this->industryData = [
            'name' => $this->industry->name,
            'type_factory_id' => $this->industry->type_factory_id,
            'address' => $this->industry->address ?? '',
            'phone' => $this->industry->phone ?? '',
        ];
    }

    public function editIndustry()
    {
        $this->industryData = [
            'name' => $this->industry->name,
            'type_factory_id' => $this->industry->type_factory_id,
            'address' => $this->industry->address ?? '',
            'phone' => $this->industry->phone ?? '',
        ];

        $this->editingIndustry = true;
    }

    public function updateIndustry()
    {
        $this->validate([
            'industryData.name' =>
                'required|string|max:255',

            'industryData.type_factory_id' =>
                'required|exists:type_factories,id',

            'industryData.address' =>
                'nullable|string|max:255',

            'industryData.phone' =>
                'nullable|string|max:255',
        ]);

        $this->industry->update(
            $this->industryData
        );

        $this->editingIndustry = false;

        $this->loadIndustry();
    }

    public function cancelEdit()
    {
        $this->editingIndustry = false;

        $this->loadIndustry();

        $this->resetValidation();
    }

    public function deleteIndustry()
    {
        $this->industry->delete();

        return $this->redirectRoute(
            'dashboard.industries'
        );
    }

    public function render()
    {
        /*
         * Contratos relacionados con los ensambles
         * realizados por esta fábrica.
         */
        $assemblyContracts = $this->industry
            ->assemblies
            ->pluck('contract')
            ->filter()
            ->unique('id');

        /*
         * Contratos relacionados con los componentes
         * realizados por esta fábrica.
         */
        $componentContracts = $this->industry
            ->assemblyComponents
            ->pluck('assembly.contract')
            ->filter()
            ->unique('id');

        /*
         * Unificamos ambos grupos.
         */
        $contracts = $assemblyContracts
            ->merge($componentContracts)
            ->unique('id')
            ->sortByDesc('id')
            ->values();

        return $this->view([
            'contracts' => $contracts,
        ]);
    }
};
