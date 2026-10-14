<?php

use App\Models\Customer;
use Livewire\Component;

new class extends Component {
    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getContractsProperty()
    {
        return $this->customer
            ->contracts()
            ->with(['details.product', 'industry'])
            ->withCount('details')
            ->orderByDesc('id')
            ->get();
    }

    public function statusLabel($status): string
    {
        if ($status === null || $status === '') {
            return 'Sin estado';
        }

        if ($status instanceof \BackedEnum) {
            return $status->name;
        }

        return (string) $status;
    }
};
?>

