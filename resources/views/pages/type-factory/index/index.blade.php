<x-slot name="header">
    <h1>Tipos de Area</h1>

    <button
        data-bs-toggle="modal"
        data-bs-target="#modal-type-factory"
        class="btn btn-primary"
    >
        <i class="fa fa-plus"></i>
        Añadir Nuevo Tipo
    </button>
</x-slot>

<div>

    <livewire:table-card
        :heads="$heads"
        wire:model.live="list"
        title="<i class='fa fa-industry'></i> Tipos de Fábrica"
    >

        @foreach ($this->typeFactories as $typeFactory)
            <tr>
                <td>{{ $typeFactory->name }}</td>

                <td>{{ $typeFactory->description }}</td>

                <td>
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-type-factory"
                        wire:click="$dispatch('getTypeFactory', { id: {{ $typeFactory->id }} })"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
            </tr>
        @endforeach

    </livewire:table-card>

    <x-modal
        id="modal-type-factory"
        title="Tipo de Fábrica"
    >
        <livewire:pages::type-factory.register />
    </x-modal>

</div>
