<?php

use App\Enums\Role;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';


    public $industries = [];

    public $editing = false;

    public $userId = null;

    public $deleteId = null;

    public $form = [
        'name' => '',
        'username' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
        'industry_id' => '',
    ];

    public function mount()
    {
        $this->industries = Industry::orderBy('name')->get();
    }


    public function updatedSearch()
    {
        $this->resetPage();
    }


    public function openCreate()
    {
        $this->resetForm();

        $this->editing = false;

        $this->resetValidation();
    }


    public function openEdit($id)
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;

        $this->form = [
            'name' => $user->name,

            'username' => $user->username,

            'password' => '',
            'password_confirmation' => '',

            'role' => $user->role instanceof Role
                ? $user->role->value
                : $user->role,

            'industry_id' => $user->industry_id ?? '',
        ];

        $this->editing = true;

        $this->resetValidation();
    }


    public function updatedFormRole($value)
    {
        if ((int) $value !== Role::WORKER->value) {
            $this->form['industry_id'] = '';
        }

        $this->resetValidation('form.industry_id');
    }


    public function save()
    {
        $rules = [
            'form.name' => 'required|string|max:255',

            'form.username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username,' . $this->userId,
            ],

            'form.role' => [
                'required',
                'integer',
                'in:' . implode(
                    ',',
                    array_column(Role::cases(), 'value')
                ),
            ],

            'form.industry_id' => [
                'nullable',
                'integer',
                'exists:industries,id',
            ],
        ];

        if ($this->editing) {

            $rules['form.password'] = [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ];

        } else {

            $rules['form.password'] = [
                'required',
                'string',
                'min:6',
                'confirmed',
            ];
        }

        if (
            (int) $this->form['role']
            === Role::WORKER->value
        ) {

            $rules['form.industry_id'] = [
                'required',
                'integer',
                'exists:industries,id',
            ];
        }


        $this->validate($rules);


        /*
        |--------------------------------------------------------------------------
        | Si no es WORKER, no necesita fábrica
        |--------------------------------------------------------------------------
        */

        $industryId =
            (int) $this->form['role']
            === Role::WORKER->value
                ? $this->form['industry_id']
                : null;



        if ($this->editing) {

            $user = User::findOrFail($this->userId);

            $data = [
                'name' => $this->form['name'],

                'username' => $this->form['username'],

                'role' => (int) $this->form['role'],

                'industry_id' => $industryId,
            ];


            if (
                trim($this->form['password']) !== ''
            ) {

                $data['password'] =
                    Hash::make(
                        $this->form['password']
                    );
            }


            $user->update($data);

            session()->flash(
                'success',
                'Usuario actualizado correctamente.'
            );

        } else {

            User::create([
                'name' =>
                    $this->form['name'],

                'username' =>
                    $this->form['username'],

                'password' =>
                    Hash::make(
                        $this->form['password']
                    ),

                'role' =>
                    (int) $this->form['role'],

                'industry_id' =>
                    $industryId,
            ]);

            session()->flash(
                'success',
                'Usuario creado correctamente.'
            );
        }


        $this->resetForm();

        $this->js("$('#modal-user').modal('hide')");

        $this->dispatch(
            'close-user-modal'
        );
    }


    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }


    public function delete()
    {
        if (!$this->deleteId) {
            return;
        }

        User::findOrFail(
            $this->deleteId
        )->delete();

        $this->deleteId = null;

        session()->flash(
            'success',
            'Usuario eliminado correctamente.'
        );

        $this->dispatch(
            'close-delete-user-modal'
        );
        $this->js("$('#modal-delete-user').modal('hide')");
    }


    public function resetForm()
    {
        $this->userId = null;

        $this->form = [
            'name' => '',
            'username' => '',
            'password' => '',
            'password_confirmation' => '',
            'role' => '',
            'industry_id' => '',
        ];

        $this->resetValidation();
    }


    public function render()
    {
        $users = User::query()
            ->with('industry')
            ->when(
                trim($this->search) !== '',
                function ($query) {

                    $search =
                        trim($this->search);

                    $query->where(function ($query) use ($search) {

                        $query->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'username',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->orderBy('name')
            ->paginate(15);


        return $this->view([
            'users' => $users,
        ]);
    }
};
