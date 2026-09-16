<x-slot name="header">
    <h1>Categorías</h1>

    <button
        data-bs-toggle="modal"
        data-bs-target="#modal-category"
        class="btn btn-primary"
    >
        <i class="fa fa-plus"></i>
        Añadir Nueva Categoría
    </button>
</x-slot>

<div>

    <livewire:table-card
        :heads="$heads"
        wire:model.live="list"
        title="<i class='fa fa-tags'></i> Categorías"
    >

        @foreach ($this->categories as $category)
            <tr>
                <td>{{ $category->name }}</td>

                <td>
                    <button
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-category"
                        wire:click="$dispatch('getCategory', { id: {{ $category->id }} })"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
            </tr>
        @endforeach

    </livewire:table-card>

    <x-modal id="modal-category" title="Categoría">
        <livewire:pages::category.register />
    </x-modal>

</div>
