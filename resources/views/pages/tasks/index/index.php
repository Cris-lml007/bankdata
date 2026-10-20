<?php

use App\Models\Industry;
use App\Models\Task;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public ?int $taskId = null;

    public string $search = '';

    public string $industryFilter = '';

    public bool $showForm = false;

    public array $form = [
        'name' => '',
        'description' => '',
        'industry_id' => '',
        'to' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.industry_id' => [
                'required',
                'integer',
                Rule::exists('industries', 'id'),
            ],
            'form.to' => ['nullable', 'date'],
        ];
    }

    protected function messages(): array
    {
        return [
            'form.name.required' => 'El nombre de la tarea es obligatorio.',
            'form.name.max' => 'El nombre no puede superar los 255 caracteres.',
            'form.industry_id.required' => 'Debes seleccionar un área.',
            'form.industry_id.exists' => 'El área seleccionada no existe.',
            'form.to.date' => 'La fecha límite no es válida.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingIndustryFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function industries()
    {
        return Industry::query()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function tasks()
    {
        return Task::query()
            ->with('industry')
            ->when(trim($this->search) !== '', function ($query) {
                $terms = preg_split(
                    '/\s+/u',
                    trim($this->search),
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );

                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->where(function ($sub) use ($term) {
                            $sub->where('name', 'like', "%{$term}%")
                                ->orWhere('description', 'like', "%{$term}%");
                        });
                    }
                });
            })
            ->when($this->industryFilter !== '', function ($query) {
                $query->where('industry_id', $this->industryFilter);
            })
            ->orderByRaw('CASE WHEN `to` IS NULL THEN 1 ELSE 0 END')
            ->orderBy('to')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $task = Task::findOrFail($id);

        $this->taskId = $task->id;

        $this->form = [
            'name' => $task->name,
            'description' => $task->description ?? '',
            'industry_id' => (string) $task->industry_id,
            'to' => $task->to
            ? $task->to->format('Y-m-d')
            : '',
        ];

        $this->resetValidation();

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = $validated['form'];
        $editing = $this->taskId !== null;

        $data['industry_id'] = (int) $data['industry_id'];
        $data['description'] = $data['description'] ?: null;
        $data['to'] = $data['to'] ?: null;

        if ($editing) {
            Task::findOrFail($this->taskId)->update($data);
        } else {
            Task::create($data);
        }

        $this->resetForm();

        unset($this->tasks);

        session()->flash(
            'success',
            $editing
            ? 'Tarea actualizada correctamente.'
            : 'Tarea creada correctamente.'
        );
    }

    public function delete(int $id): void
    {
        $task = Task::findOrFail($id);

        $task->delete();

        unset($this->tasks);

        session()->flash('success', 'Tarea eliminada correctamente.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->taskId = null;

        $this->form = [
            'name' => '',
            'description' => '',
            'industry_id' => '',
            'to' => '',
        ];

        $this->showForm = false;

        $this->resetValidation();
    }
};
?>
