<x-slot name="header">
    <h1>Cotizaciones</h1>

    <a
        href="{{ route('dashboard.quotations.register') }}"
        class="btn btn-primary"
    >
        <i class="fa fa-plus"></i>
        Nueva Cotización
    </a>
</x-slot>

<div>
    <livewire:table-card
        :heads="$heads"
        wire:model.live="list"
        title="<i class='fa fa-file-invoice'></i> Mis Cotizaciones"
    >
        @foreach ($this->quotations as $quotation)
            <tr>
                <td>
                    {{ $quotation->id }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($quotation->valid_from)->format('d/m/Y') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($quotation->valid_to)->format('d/m/Y') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($quotation->delivery_date)->format('d/m/Y') }}
                </td>

                <td>
                    <a
                        href="{{ route('dashboard.quotations.view', $quotation) }}"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
        @endforeach
    </livewire:table-card>
</div>
