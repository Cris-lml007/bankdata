<x-slot name="header">
    <h1>Contratos</h1>

    <a
        href="{{ route('dashboard.contracts.register') }}"
        class="btn btn-primary"
    >
        <i class="fa fa-plus"></i>
        Nuevo Contrato
    </a>
</x-slot>

<div>
    <livewire:table-card
        :heads="$heads"
        wire:model.live="list"
        title="<i class='fa fa-file-signature'></i> Mis Contratos"
    >

        @foreach ($this->contracts as $contract)

            <tr>

                <td>
                    {{ $contract->id }}
                </td>

                <td>
                    {{ $contract->customer?->name ?? 'Sin cliente' }}
                </td>

                <td>
                    @if ($contract->delivery_date)
                        {{ \Carbon\Carbon::parse($contract->delivery_date)->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>

                <td>
                    <span class="badge {{ $contract->status->badgeClass() }}">
                    {{ $contract->status->label() }}
                    </span>
                </td>

                <td>
                    <a
                        href="{{ route('dashboard.contracts.view', $contract) }}"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="fa fa-eye"></i>
                    </a>
                </td>

            </tr>

        @endforeach

    </livewire:table-card>
</div>
