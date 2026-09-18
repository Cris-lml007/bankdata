<x-slot name="header">
    <h1>Productos</h1>
    <button data-bs-toggle="modal" data-bs-target="#modal-register" class="btn btn-primary"><i class="fa fa-plus"></i> Añadir Nuevo Producto</button>
</x-slot>

<div>
    <livewire:table-card :heads="$heads" wire:model.live="list" title="<i class='fa fa-box'></i> Mis Productos">
        @foreach ($this->products as $product)
        <tr>
            <td>{{ $product->cod }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->model }}</td>
            <td>{{ Number::format($product->price_sale,2) }}</td>
            <td>
                <a href="{{ route('dashboard.products.view',$product->id) }}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
            </td>
        </tr>
        @endforeach
    </livewire:table-card>

    <x-modal id="modal-register" title="Nuevo Producto">
        <livewire:pages::product.register></livewire:pages::product.register>
    </x-modal>
</div>
