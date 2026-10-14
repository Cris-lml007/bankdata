<?php

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->withCount('contracts')
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('ci_nit', 'like', "%{$search}%")
                        ->orWhere('organization', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }
};
?>
