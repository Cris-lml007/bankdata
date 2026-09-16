<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-1">
                Panel de trabajo
            </h4>

            <small class="text-muted">
                {{ auth()->user()->industry?->name }}
            </small>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- ENSAMBLAJES --}}
    {{-- ========================================================= --}}

    @if (count($assemblies))

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    Ensamblajes
                </h3>
            </div>

            <div class="card-body">

                @foreach ($assemblies as $task)

                    <div class="border rounded p-3 mb-3">

                        {{-- CABECERA --}}
                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <h5 class="mb-1">
                                    {{ $task['product_name'] }}
                                </h5>

                                <div class="text-muted">
                                    Contrato #{{ $task['contract_id'] }}
                                </div>

                                <div class="text-muted">
                                    Entrega:
                                    {{ $task['delivery_date']
                                        ? \Carbon\Carbon::parse($task['delivery_date'])->format('d/m/Y')
                                        : 'Sin fecha'
                                    }}
                                </div>

                            </div>

                            <div>

                                @if ($task['status_value'] === \App\Enums\Status::FINISH->value)

                                    <span class="badge badge-success">
                                        Finalizado
                                    </span>

                                @else

                                    <span class="badge badge-warning">
                                        En proceso
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- PROGRESO --}}
                        <div class="mt-3">

                            <div class="d-flex justify-content-between mb-1">

                                <span>
                                    Progreso
                                </span>

                                <strong>
                                    {{ $task['progress'] }} / {{ $task['quantity'] }}
                                </strong>

                            </div>

                            <div class="progress" style="height: 20px;">

                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width: {{ $task['percentage'] }}%;"
                                >
                                    {{ $task['percentage'] }}%
                                </div>

                            </div>

                        </div>


                        {{-- INFORMACIÓN --}}
                        <div class="row mt-3">

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Requerido
                                </small>

                                <strong>
                                    {{ $task['quantity'] }}
                                </strong>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Avance
                                </small>

                                <strong>
                                    {{ $task['progress'] }}
                                </strong>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Pendiente
                                </small>

                                <strong>
                                    {{ $task['pending'] }}
                                </strong>
                            </div>

                        </div>


                        {{-- REGISTRAR AVANCE --}}
                        @if ($task['status_value'] !== \App\Enums\Status::FINISH->value)

                            <div class="row mt-3">

                                <div class="col-md-4">

                                    <label>
                                        Registrar avance
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            min="1"
                                            max="{{ $task['pending'] }}"
                                            class="form-control"
                                            wire:model="progressInputs.assembly-{{ $task['id'] }}"
                                            placeholder="Cantidad"
                                        >

                                        <div class="input-group-append">

                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                wire:click="registerProgress('assembly', {{ $task['id'] }})"
                                                wire:loading.attr="disabled"
                                            >
                                                <i class="fas fa-plus"></i>
                                                Registrar
                                            </button>

                                        </div>

                                    </div>

                                    @error("progressInputs.assembly-{$task['id']}")
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                    @enderror

                                </div>

                            </div>


                            {{-- FINALIZAR --}}
                            @if ($task['progress'] >= $task['quantity'])

                                <div class="mt-3">

                                    <button
                                        type="button"
                                        class="btn btn-success"
                                        wire:click="finishTask('assembly', {{ $task['id'] }})"
                                        wire:loading.attr="disabled"
                                    >
                                        <i class="fas fa-check"></i>
                                        Finalizar tarea
                                    </button>

                                </div>

                            @endif

                        @endif

                    </div>

                @endforeach

            </div>
        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- COMPONENTES --}}
    {{-- ========================================================= --}}

    @if (count($assemblyComponents))

        <div class="card card-info">

            <div class="card-header">
                <h3 class="card-title">
                    Fabricación de componentes
                </h3>
            </div>

            <div class="card-body">

                @foreach ($assemblyComponents as $task)

                    <div class="border rounded p-3 mb-3">

                        {{-- CABECERA --}}
                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <h5 class="mb-1">
                                    {{ $task['component_name'] }}
                                </h5>

                                <div class="text-muted">
                                    Producto:
                                    {{ $task['product_name'] }}
                                </div>

                                <div class="text-muted">
                                    Contrato #{{ $task['contract_id'] }}
                                </div>

                                <div class="text-muted">
                                    Entrega:
                                    {{ $task['delivery_date']
                                        ? \Carbon\Carbon::parse($task['delivery_date'])->format('d/m/Y')
                                        : 'Sin fecha'
                                    }}
                                </div>

                            </div>

                            <div>

                                @if ($task['status_value'] === \App\Enums\Status::FINISH->value)

                                    <span class="badge badge-success">
                                        Finalizado
                                    </span>

                                @else

                                    <span class="badge badge-warning">
                                        En proceso
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- PROGRESO --}}
                        <div class="mt-3">

                            <div class="d-flex justify-content-between mb-1">

                                <span>
                                    Progreso
                                </span>

                                <strong>
                                    {{ $task['progress'] }} / {{ $task['quantity'] }}
                                </strong>

                            </div>

                            <div class="progress" style="height: 20px;">

                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width: {{ $task['percentage'] }}%;"
                                >
                                    {{ $task['percentage'] }}%
                                </div>

                            </div>

                        </div>


                        {{-- INFORMACIÓN --}}
                        <div class="row mt-3">

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Requerido
                                </small>

                                <strong>
                                    {{ $task['quantity'] }}
                                </strong>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Avance
                                </small>

                                <strong>
                                    {{ $task['progress'] }}
                                </strong>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    Pendiente
                                </small>

                                <strong>
                                    {{ $task['pending'] }}
                                </strong>
                            </div>

                        </div>


                        {{-- REGISTRAR AVANCE --}}
                        @if ($task['status_value'] !== \App\Enums\Status::FINISH->value)

                            <div class="row mt-3">

                                <div class="col-md-4">

                                    <label>
                                        Registrar avance
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            min="1"
                                            max="{{ $task['pending'] }}"
                                            class="form-control"
                                            wire:model="progressInputs.component-{{ $task['id'] }}"
                                            placeholder="Cantidad"
                                        >

                                        <div class="input-group-append">

                                            <button
                                                type="button"
                                                class="btn btn-info"
                                                wire:click="registerProgress('component', {{ $task['id'] }})"
                                                wire:loading.attr="disabled"
                                            >
                                                <i class="fas fa-plus"></i>
                                                Registrar
                                            </button>

                                        </div>

                                    </div>

                                    @error("progressInputs.component-{$task['id']}")
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                    @enderror

                                </div>

                            </div>


                            {{-- FINALIZAR --}}
                            @if ($task['progress'] >= $task['quantity'])

                                <div class="mt-3">

                                    <button
                                        type="button"
                                        class="btn btn-success"
                                        wire:click="finishTask('component', {{ $task['id'] }})"
                                        wire:loading.attr="disabled"
                                    >
                                        <i class="fas fa-check"></i>
                                        Finalizar tarea
                                    </button>

                                </div>

                            @endif

                        @endif

                    </div>

                @endforeach

            </div>
        </div>

    @endif


    {{-- SIN TAREAS --}}
    @if (!count($assemblies) && !count($assemblyComponents))

        <div class="card">

            <div class="card-body text-center py-5">

                <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>

                <h5>
                    No tienes tareas asignadas
                </h5>

                <p class="text-muted mb-0">
                    Cuando se te asigne una tarea aparecerá aquí.
                </p>

            </div>

        </div>

    @endif

</div>
