<x-slot name="header">

    <h1>Areas y Sucursales</h1>

    <a href="{{ route('dashboard.industries.register') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i>
        Nueva Area/Sucursal
    </a>

</x-slot>

<div>

    <livewire:table-card :heads="$heads" wire:model.live="list"
        title="<i class='fa fa-industry'></i> Mis Areas/Sucursales">

        @foreach ($this->industries as $industry)
            <tr>

                <td>
                    {{ $industry->id }}
                </td>

                <td>
                    {{ $industry->name }}
                </td>

                <td>
                    {{ $industry->typeFactory?->name ?? '-' }}
                </td>
                <td>{{ $industry->type->label() }}</td>

                <td>
                    {{ $industry->address ?? '-' }}
                </td>

                <td>
                    {{ $industry->phone ?? '-' }}
                </td>

                <td>

                    <a href="{{ route('dashboard.industries.view', $industry) }}"
                        class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i>
                    </a>

                </td>

            </tr>
        @endforeach

    </livewire:table-card>

</div>
