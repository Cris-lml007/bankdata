
@php
    $role = auth()->user()->role;

    $isManagement = in_array($role, [
        \App\Enums\Role::ADMIN,
        \App\Enums\Role::PRIVILEGED,
        \App\Enums\Role::SUPERVISOR,
    ], true);

    $isWorker = $role === \App\Enums\Role::WORKER;
    $isReception = $role === \App\Enums\Role::RECEPT;
@endphp

<div class="container-fluid">

    {{-- ENCABEZADO --}}
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-1">Panel de trabajo</h4>
            <small class="text-muted">
                {{ auth()->user()->industry?->name }}
            </small>
        </div>
    </div>

    {{-- ====================================================== --}}
    {{-- RECEPCIÓN                                              --}}
    {{-- ====================================================== --}}
    @if ($isReception)

        @forelse ($receptionContracts as $contract)
            <div class="card card-outline card-primary"
                 wire:key="reception-contract-{{ $contract['id'] }}">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-contract mr-1"></i>
                        Contrato #{{ $contract['id'] }}

                        @if ($contract['external_id'])
                            <small class="text-muted ml-2">
                                Proforma {{ $contract['external_id'] }}
                            </small>
                        @endif
                    </h3>

                    <div class="card-tools">
                        <span class="badge badge-info">
                            Prioridad {{ $contract['priority'] }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 col-6">
                            <small class="text-muted">Solicitado</small>
                            <h5>{{ $contract['quantity'] }}</h5>
                        </div>

                        <div class="col-md-3 col-6">
                            <small class="text-muted">Fabricado</small>
                            <h5 class="text-success">{{ $contract['progress'] }}</h5>
                        </div>

                        <div class="col-md-3 col-6">
                            <small class="text-muted">Pendiente</small>
                            <h5 class="text-danger">{{ $contract['pending'] }}</h5>
                        </div>

                        <div class="col-md-3 col-6">
                            <small class="text-muted">Avance</small>
                            <h5>{{ $contract['percentage'] }}%</h5>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success"
                             style="width: {{ $contract['percentage'] }}%">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Solicitado</th>
                                    <th class="text-center">Fabricado</th>
                                    <th class="text-center">Pendiente</th>
                                    <th class="text-center">Avance</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($contract['products'] as $product)
                                    <tr>
                                        <td>{{ $product['name'] }}</td>
                                        <td class="text-center">{{ $product['quantity'] }}</td>
                                        <td class="text-center text-success">{{ $product['progress'] }}</td>
                                        <td class="text-center text-danger">{{ $product['pending'] }}</td>
                                        <td class="text-center">{{ $product['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No hay productos en este contrato.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                No hay contratos pendientes asignados a esta área.
            </div>
        @endforelse

    {{-- ====================================================== --}}
    {{-- GESTIÓN: ENSAMBLAJES Y PROCESOS                        --}}
    {{-- ====================================================== --}}
    @elseif ($isManagement)

        <div class="card">
            <div class="card-body">
                <label for="assemblySearch">
                    Buscar ensamblaje, contrato, proforma o proceso
                </label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>

                    <input
                        id="assemblySearch"
                        type="search"
                        class="form-control"
                        placeholder="Ej.: mesa, proforma 102, corte..."
                        wire:model.live.debounce.350ms="search"
                    />

                    @if ($search !== '')
                        <div class="input-group-append">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                wire:click="$set('search', '')"
                            >
                                Limpiar
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @error('finish')
            <div class="alert alert-warning">{{ $message }}</div>
        @enderror

        @forelse ($assemblyGroups as $assembly)
            <div class="card card-outline card-primary"
                 wire:key="assembly-{{ $assembly['id'] }}">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cubes mr-1"></i>
                        Ensamblaje #{{ $assembly['id'] }} —
                        {{ $assembly['product_name'] }}

                        @if ($assembly['external_id'])
                            <small class="text-muted ml-2">
                                Proforma {{ $assembly['external_id'] }}
                            </small>
                        @endif
                    </h3>

                    <div class="card-tools">
                        <span class="badge badge-primary">
                            {{ $assembly['percentage'] }}%
                        </span>

                        <button
                            type="button"
                            class="btn btn-tool"
                            data-card-widget="collapse"
                            aria-label="Mostrar u ocultar ensamblaje"
                        >
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    {{-- INFORMACIÓN DEL ENSAMBLAJE --}}
                    <div class="mb-2">
                        <small class="text-muted">
                            Área: {{ $assembly['industry_name'] }}
                        </small>

                        @if ($assembly['delivery_date'])
                            <br>
                            <small class="text-muted">
                                Entrega: {{ $assembly['delivery_date'] }}
                            </small>
                        @endif

                        @if ($assembly['product_description'])
                            <p class="text-muted mt-2 mb-1">
                                {{ $assembly['product_description'] }}
                            </p>
                        @endif
                    </div>

                    {{-- ETIQUETAS GLOBALES --}}
                    @if (!empty($assembly['product_tags']))
                        <div class="mb-3">
                            <small class="font-weight-bold">
                                Etiquetas globales del producto
                            </small>
                            <div>
                                @foreach ($assembly['product_tags'] as $tag)
                                    <span
                                        class="badge badge-info mr-1 mb-1"
                                        wire:key="assembly-tag-{{ $assembly['id'] }}-{{ $loop->index }}"
                                    >
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- AVANCE DEL ENSAMBLAJE --}}
                    <div class="progress mb-3" style="height: 10px;">
                        <div
                            class="progress-bar bg-primary"
                            style="width: {{ $assembly['percentage'] }}%"
                        ></div>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <small class="text-muted">Solicitado</small>
                            <h5>{{ $assembly['quantity'] }}</h5>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Terminado</small>
                            <h5 class="text-success">{{ $assembly['progress'] }}</h5>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Pendiente</small>
                            <h5 class="text-danger">{{ $assembly['pending'] }}</h5>
                        </div>
                    </div>

                    {{-- REGISTRAR ENSAMBLAJE --}}
                    @if ($assembly['pending'] > 0)
                        <div class="border rounded p-3 mb-3">
                            <label>
                                Registrar unidades terminadas del ensamblaje
                            </label>

                            <div class="input-group">
                                <input
                                    type="number"
                                    min="1"
                                    max="{{ $assembly['pending'] }}"
                                    step="1"
                                    class="form-control"
                                    placeholder="Cantidad terminada"
                                    wire:model="progressInputs.assembly-{{ $assembly['id'] }}"
                                />

                                <div class="input-group-append">
                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        wire:click="registerProgress('assembly', {{ $assembly['id'] }})"
                                        wire:loading.attr="disabled"
                                    >
                                        <i class="fas fa-plus mr-1"></i>
                                        Registrar
                                    </button>
                                </div>
                            </div>

                            @error('progressInputs.assembly-' . $assembly['id'])
                                <small class="text-danger d-block mt-1">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>
                    @else
                        <div class="text-right mb-3">
                            <button
                                type="button"
                                class="btn btn-success"
                                wire:click="finishTask('assembly', {{ $assembly['id'] }})"
                                wire:loading.attr="disabled"
                            >
                                <i class="fas fa-check mr-1"></i>
                                Finalizar ensamblaje
                            </button>
                        </div>
                    @endif

                    {{-- PROCESOS DEL ENSAMBLAJE --}}
                    <div class="border-top pt-3 mt-3">
                        <h5 class="mb-3">
                            <i class="fas fa-tools mr-1"></i>
                            Procesos del ensamblaje
                            <span class="badge badge-secondary">
                                {{ $assembly['process_count'] }}
                            </span>
                        </h5>

                        @forelse ($assembly['processes'] as $process)
                            <div
                                class="border rounded p-3 mb-3"
                                wire:key="process-{{ $process['id'] }}"
                            >
                                <div class="d-flex flex-wrap justify-content-between">
                                    <div>
                                        <h6 class="font-weight-bold mb-1">
                                            {{ $process['component_name'] }}
                                        </h6>

                                        @if ($process['component_description'])
                                            <p class="text-muted mb-1">
                                                {{ $process['component_description'] }}
                                            </p>
                                        @endif

                                        <small class="text-muted">
                                            Área: {{ $process['industry_name'] }}
                                        </small>
                                    </div>

                                    <span class="badge badge-info align-self-start">
                                        {{ $process['percentage'] }}%
                                    </span>
                                </div>

                                {{-- ETIQUETAS GLOBALES DEL COMPONENTE --}}
                                @if (!empty($process['component_tags']))
                                    <div class="mt-2">
                                        <small class="font-weight-bold">
                                            Etiquetas globales del componente
                                        </small>
                                        <div>
                                            @foreach ($process['component_tags'] as $tag)
                                                <span
                                                    class="badge badge-info mr-1 mb-1"
                                                    wire:key="component-tag-{{ $process['id'] }}-{{ $loop->index }}"
                                                >
                                                    {{ $tag['name'] }}: {{ $tag['value'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- ETIQUETAS DEL PROCESO --}}
                                @if (!empty($process['process_tags']))
                                    <div class="mt-2">
                                        <small class="font-weight-bold">
                                            Etiquetas de su proceso/área
                                        </small>
                                        <div>
                                            @foreach ($process['process_tags'] as $tag)
                                                <span
                                                    class="badge badge-warning mr-1 mb-1"
                                                    wire:key="process-tag-{{ $process['id'] }}-{{ $loop->index }}"
                                                >
                                                    {{ $tag['name'] }}: {{ $tag['value'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- AVANCE DEL PROCESO --}}
                                <div class="progress my-3" style="height: 8px;">
                                    <div
                                        class="progress-bar bg-info"
                                        style="width: {{ $process['percentage'] }}%"
                                    ></div>
                                </div>

                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <small class="text-muted">Solicitado</small>
                                        <div>{{ $process['quantity'] }}</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Procesado</small>
                                        <div class="text-success">{{ $process['progress'] }}</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Pendiente</small>
                                        <div class="text-danger">{{ $process['pending'] }}</div>
                                    </div>
                                </div>

                                {{-- REGISTRAR PROCESO --}}
                                @if ($process['pending'] > 0)
                                    <label>Registrar unidades procesadas</label>

                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="1"
                                            max="{{ $process['pending'] }}"
                                            step="1"
                                            class="form-control"
                                            placeholder="Cantidad procesada"
                                            wire:model="progressInputs.component-{{ $process['id'] }}"
                                        />

                                        <div class="input-group-append">
                                            <button
                                                type="button"
                                                class="btn btn-info"
                                                wire:click="registerProgress('component', {{ $process['id'] }})"
                                                wire:loading.attr="disabled"
                                            >
                                                <i class="fas fa-plus mr-1"></i>
                                                Registrar proceso
                                            </button>
                                        </div>
                                    </div>

                                    @error('progressInputs.component-' . $process['id'])
                                        <small class="text-danger d-block mt-1">
                                            {{ $message }}
                                        </small>
                                    @enderror
                                @else
                                    <div class="text-right">
                                        <button
                                            type="button"
                                            class="btn btn-success btn-sm"
                                            wire:click="finishTask('component', {{ $process['id'] }})"
                                            wire:loading.attr="disabled"
                                        >
                                            <i class="fas fa-check mr-1"></i>
                                            Finalizar proceso
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted">
                                Este ensamblaje no tiene procesos pendientes.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                @if ($search !== '')
                    No se encontraron ensamblajes ni procesos para
                    "{{ $search }}".
                @else
                    No hay ensamblajes ni procesos pendientes de registro.
                @endif
            </div>
        @endforelse

    {{-- ====================================================== --}}
    {{-- TRABAJADORES                                           --}}
    {{-- ====================================================== --}}
    @elseif ($isWorker)

        <h5 class="mb-3">
            <i class="fas fa-cubes mr-1"></i>
            Ensamblajes de mi área
        </h5>

        @forelse ($assemblies as $assembly)
            <div class="card" wire:key="worker-assembly-{{ $assembly['id'] }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div>
                            <h5>{{ $assembly['product_name'] }}</h5>

                            @if ($assembly['external_id'])
                                <small class="text-muted">
                                    Proforma: {{ $assembly['external_id'] }}
                                </small>
                            @endif
                        </div>

                        <span class="badge badge-info">
                            {{ $assembly['percentage'] }}%
                        </span>
                    </div>

                    @if (!empty($assembly['product_tags']))
                        <div class="mt-3">
                            <small class="font-weight-bold">
                                Etiquetas globales del producto
                            </small>
                            <div>
                                @foreach ($assembly['product_tags'] as $tag)
                                    <span
                                        class="badge badge-info mr-1 mb-1"
                                        wire:key="worker-assembly-tag-{{ $assembly['id'] }}-{{ $loop->index }}"
                                    >
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="progress my-3" style="height: 8px;">
                        <div
                            class="progress-bar bg-success"
                            style="width: {{ $assembly['percentage'] }}%"
                        ></div>
                    </div>

                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted">Solicitado</small>
                            <div>{{ $assembly['quantity'] }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Terminado</small>
                            <div class="text-success">{{ $assembly['progress'] }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Pendiente</small>
                            <div class="text-danger">{{ $assembly['pending'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light border">
                No hay ensamblajes activos asignados a tu área.
            </div>
        @endforelse

        <h5 class="mb-3 mt-4">
            <i class="fas fa-tools mr-1"></i>
            Procesos de mi área
        </h5>

        @forelse ($assemblyComponents as $process)
            <div class="card" wire:key="worker-process-{{ $process['id'] }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div>
                            <h5>{{ $process['component_name'] }}</h5>

                            <small class="text-muted">
                                Producto: {{ $process['product_name'] }}
                            </small>

                            @if ($process['external_id'])
                                <br>
                                <small class="text-muted">
                                    Proforma: {{ $process['external_id'] }}
                                </small>
                            @endif
                        </div>

                        <span class="badge badge-info">
                            {{ $process['percentage'] }}%
                        </span>
                    </div>

                    @if (!empty($process['product_tags']))
                        <div class="mt-3">
                            <small class="font-weight-bold">
                                Etiquetas globales del producto
                            </small>
                            <div>
                                @foreach ($process['product_tags'] as $tag)
                                    <span
                                        class="badge badge-info mr-1 mb-1"
                                        wire:key="worker-process-product-tag-{{ $process['id'] }}-{{ $loop->index }}"
                                    >
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (!empty($process['component_tags']))
                        <div class="mt-2">
                            <small class="font-weight-bold">
                                Etiquetas globales del componente
                            </small>
                            <div>
                                @foreach ($process['component_tags'] as $tag)
                                    <span
                                        class="badge badge-info mr-1 mb-1"
                                        wire:key="worker-process-component-tag-{{ $process['id'] }}-{{ $loop->index }}"
                                    >
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (!empty($process['process_tags']))
                        <div class="mt-2">
                            <small class="font-weight-bold">
                                Etiquetas de mi área
                            </small>
                            <div>
                                @foreach ($process['process_tags'] as $tag)
                                    <span
                                        class="badge badge-warning mr-1 mb-1"
                                        wire:key="worker-process-own-tag-{{ $process['id'] }}-{{ $loop->index }}"
                                    >
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="progress my-3" style="height: 8px;">
                        <div
                            class="progress-bar bg-success"
                            style="width: {{ $process['percentage'] }}%"
                        ></div>
                    </div>

                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted">Solicitado</small>
                            <div>{{ $process['quantity'] }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Procesado</small>
                            <div class="text-success">{{ $process['progress'] }}</div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Pendiente</small>
                            <div class="text-danger">{{ $process['pending'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light border">
                No hay procesos activos asignados a tu área.
            </div>
        @endforelse

    @endif
</div>
