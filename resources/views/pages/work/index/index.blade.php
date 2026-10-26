
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

<div class="container-fluid" wire:poll.60s="loadTasks">

    {{-- ENCABEZADO --}}
    <div class="row mb-3">
        <div class="col-md-8 col-12">
            <h1 class="mb-1">Panel de trabajo</h1>
            <small class="text-muted">
                {{ auth()->user()->industry?->name }}
            </small>
        </div>

        @if ($isWorker)
            <div class="col-md-4 col-12 text-md-right mt-2 mt-md-0">
                <div class="card card-outline card-info mb-0">
                    <div class="card-body py-2">
                        <div class="text-muted small">
                            <i class="far fa-calendar-alt mr-1"></i>
                            Fecha y hora actual
                        </div>

                        <div x-data="{
                            now: new Date(),
                            init() {
                                setInterval(() => this.now = new Date(), 1000);
                            },
                            get dateText() {
                                return this.now.toLocaleDateString('es-BO', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                });
                            },
                            get timeText() {
                                return this.now.toLocaleTimeString('es-BO', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: false
                                });
                            }
                        }">
                            <div class="font-weight-bold text-capitalize"
                                 x-text="dateText"></div>
                            <h4 class="mb-0 text-info">
                                <i class="far fa-clock mr-1"></i>
                                <span x-text="timeText"></span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- RECEPCIÓN --}}
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
                        <span class="badge badge-{{ $contract['priority_badge'] }}">
                            {{ $contract['priority_label'] }}
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

    {{-- GESTIÓN --}}
    @elseif ($isManagement)

        <div class="card">
            <div class="card-body">
                <label for="assemblySearch">
                    Buscar ensamblajes, contratos, proformas, procesos o etiquetas
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
                        placeholder="Ej.: cuello alto, mesa, proforma 102..."
                        wire:model.live.debounce.350ms="search"
                    />

                    @if ($search !== '')
                        <div class="input-group-append">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    wire:click="$set('search', '')">
                                Limpiar
                            </button>
                        </div>
                    @endif
                </div>

                <small class="form-text text-muted">
                    Puedes combinar términos. Por ejemplo, «cuello alto»
                    busca coincidencias entre nombres y valores de etiquetas.
                </small>
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
                        <span class="badge badge-{{ $assembly['priority_badge'] }}">
                            {{ $assembly['priority_label'] }}
                        </span>

                        <span class="badge badge-primary">
                            {{ $assembly['percentage'] }}%
                        </span>

                        <button type="button"
                                class="btn btn-tool"
                                data-card-widget="collapse"
                                aria-label="Mostrar u ocultar ensamblaje">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
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

                    {{-- ETIQUETAS DEL DETALLE DEL CONTRATO --}}
                    @if (!empty($assembly['detail_contract_tags']))
                        <div class="mb-3">
                            <small class="font-weight-bold">
                                <i class="fas fa-file-contract mr-1"></i>
                                Especificaciones del contrato
                            </small>
                            <div>
                                @foreach ($assembly['detail_contract_tags'] as $tag)
                                    <span class="badge badge-primary mr-1 mb-1"
                                          wire:key="assembly-detail-tag-{{ $assembly['id'] }}-{{ $loop->index }}">
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ETIQUETAS DEL PRODUCTO --}}
                    @if (!empty($assembly['product_tags']))
                        <div class="mb-3">
                            <small class="font-weight-bold">
                                Etiquetas globales del producto
                            </small>
                            <div>
                                @foreach ($assembly['product_tags'] as $tag)
                                    <span class="badge badge-info mr-1 mb-1"
                                          wire:key="assembly-product-tag-{{ $assembly['id'] }}-{{ $loop->index }}">
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-primary"
                             style="width: {{ $assembly['percentage'] }}%">
                        </div>
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

                    @if ($assembly['pending'] > 0)
                        <div class="border rounded p-3 mb-3">
                            <label>Registrar unidades terminadas del ensamblaje</label>

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
                                    <button type="button"
                                            class="btn btn-primary"
                                            wire:click="registerProgress('assembly', {{ $assembly['id'] }})"
                                            wire:loading.attr="disabled">
                                        <i class="fas fa-plus mr-1"></i>
                                        Registrar
                                    </button>
                                </div>
                            </div>

                            @error('progressInputs.assembly-' . $assembly['id'])
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    @else
                        <div class="text-right mb-3">
                            <button type="button"
                                    class="btn btn-success"
                                    wire:click="finishTask('assembly', {{ $assembly['id'] }})"
                                    wire:loading.attr="disabled">
                                <i class="fas fa-check mr-1"></i>
                                Finalizar ensamblaje
                            </button>
                        </div>
                    @endif

                    {{-- PROCESOS --}}
                    <div class="border-top pt-3 mt-3">
                        <h5 class="mb-3">
                            <i class="fas fa-tools mr-1"></i>
                            Procesos del ensamblaje
                            <span class="badge badge-secondary">
                                {{ $assembly['process_count'] }}
                            </span>
                        </h5>

                        @forelse ($assembly['processes'] as $process)
                            <div class="border rounded p-3 mb-3"
                                 wire:key="management-process-{{ $process['id'] }}">

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

                                    <div>
                                        <span class="badge badge-{{ $process['priority_badge'] }}">
                                            {{ $process['priority_label'] }}
                                        </span>
                                        <span class="badge badge-info">
                                            {{ $process['percentage'] }}%
                                        </span>
                                    </div>
                                </div>

                                {{-- ETIQUETAS DEL DETALLE --}}
                                @if (!empty($process['detail_contract_tags']))
                                    <div class="mt-2">
                                        <small class="font-weight-bold">
                                            Especificaciones del contrato
                                        </small>
                                        <div>
                                            @foreach ($process['detail_contract_tags'] as $tag)
                                                <span class="badge badge-sucess mr-1 mb-1"
                                                      wire:key="process-detail-tag-{{ $process['id'] }}-{{ $loop->index }}">
                                                    {{ $tag['name'] }}: {{ $tag['value'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- ETIQUETAS DEL PRODUCTO --}}
                                @if (!empty($process['product_tags']))
                                    <div class="mt-2">
                                        <small class="font-weight-bold">
                                            Etiquetas globales del producto
                                        </small>
                                        <div>
                                            @foreach ($process['product_tags'] as $tag)
                                                <span class="badge badge-info mr-1 mb-1"
                                                      wire:key="process-product-tag-{{ $process['id'] }}-{{ $loop->index }}">
                                                    {{ $tag['name'] }}: {{ $tag['value'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- ETIQUETAS DEL COMPONENTE --}}
                                @if (!empty($process['component_tags']))
                                    <div class="mt-2">
                                        <small class="font-weight-bold">
                                            Etiquetas globales del componente
                                        </small>
                                        <div>
                                            @foreach ($process['component_tags'] as $tag)
                                                <span class="badge badge-info mr-1 mb-1"
                                                      wire:key="process-component-tag-{{ $process['id'] }}-{{ $loop->index }}">
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
                                            Etiquetas del proceso/área
                                        </small>
                                        <div>
                                            @foreach ($process['process_tags'] as $tag)
                                                <span class="badge badge-secondary mr-1 mb-1"
                                                      wire:key="process-own-tag-{{ $process['id'] }}-{{ $loop->index }}">
                                                    {{ $tag['name'] }}: {{ $tag['value'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="progress my-3" style="height: 8px;">
                                    <div class="progress-bar bg-info"
                                         style="width: {{ $process['percentage'] }}%">
                                    </div>
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
                                            <button type="button"
                                                    class="btn btn-info"
                                                    wire:click="registerProgress('component', {{ $process['id'] }})"
                                                    wire:loading.attr="disabled">
                                                <i class="fas fa-plus mr-1"></i>
                                                Registrar proceso
                                            </button>
                                        </div>
                                    </div>

                                    @error('progressInputs.component-' . $process['id'])
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                @else
                                    <div class="text-right">
                                        <button type="button"
                                                class="btn btn-success btn-sm"
                                                wire:click="finishTask('component', {{ $process['id'] }})"
                                                wire:loading.attr="disabled">
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
                    «{{ $search }}».
                @else
                    No hay ensamblajes ni procesos pendientes de registro.
                @endif
            </div>
        @endforelse

    {{-- TRABAJADORES --}}
    @elseif ($isWorker)

        {{-- TAREAS ASIGNADAS --}}
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list mr-1"></i>
                    Mis tareas
                </h3>
                <div class="card-tools">
                    <span class="badge badge-warning">
                        {{ count($specificTasks) }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                @forelse ($specificTasks as $task)
                    <div class="border rounded p-3 mb-3"
                         wire:key="worker-specific-task-{{ $task['id'] }}">
                        <div class="d-flex justify-content-between flex-wrap">
                            <h5 class="mb-1">
                                <i class="fas fa-tasks text-warning mr-1"></i>
                                {{ $task['name'] }}
                            </h5>

                            @if ($task['to'])
                                <span class="badge badge-secondary align-self-start">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    Límite:
                                    {{ \Illuminate\Support\Carbon::parse($task['to'])->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="badge badge-info align-self-start">
                                    Sin fecha límite
                                </span>
                            @endif
                        </div>

                        @if ($task['description'])
                            <p class="text-muted mb-0 mt-2">
                                {!! nl2br(e($task['description'])) !!}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="text-muted">
                        No tienes tareas pendientes asignadas a tu área.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- CAMBIO DE VISTA PARA TRABAJADORES --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h5 class="mb-2 mb-md-0">
                <i class="fas fa-cubes mr-1"></i>
                Productos de mi área
            </h5>

            <div class="btn-group" role="group" aria-label="Cambiar vista de productos">
                <button type="button"
                        class="btn btn-sm {{ $workerView === 'cards' ? 'btn-primary' : 'btn-outline-primary' }}"
                        wire:click="$set('workerView', 'cards')">
                    <i class="fas fa-th-large mr-1"></i> Vista actual
                </button>
                <button type="button"
                        class="btn btn-sm {{ $workerView === 'table' ? 'btn-primary' : 'btn-outline-primary' }}"
                        wire:click="$set('workerView', 'table')">
                    <i class="fas fa-table mr-1"></i> Tabla de productos
                </button>
            </div>
        </div>

        @if ($workerView === 'table')
            {{-- TABLA DE PRODUCTOS ORDENADOS PARA TRABAJADORES --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes mr-1"></i>
                        Productos ordenados
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <input type="search"
                                   class="form-control"
                                   placeholder="Buscar producto, código, orden o datos..."
                                   wire:model.live.debounce.300ms="workerOrderedSearch">
                        </div>
                        <div class="col-md-7 text-md-right mt-2 mt-md-0">
                            <span class="text-muted">
                                Productos encontrados:
                                <strong>{{ count($workerOrderedProducts) }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID producto</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>ID orden</th>
                                    <th>ID externo</th>
                                    <th>Datos</th>
                                    <th class="text-right">Solicitado</th>
                                    <th class="text-right">Fabricado</th>
                                    <th class="text-right">Faltante</th>
                                    <th style="min-width:150px">Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($workerOrderedProducts as $product)
                                    @php
                                        $firstOrder = $product['order_rows'][0] ?? [];
                                    @endphp
                                    <tr class="table-info font-weight-bold">
                                        <td>{{ $product['product_id'] ?: '—' }}</td>
                                        <td>{{ $product['cod'] ?: '—' }}</td>
                                        <td>
                                            {{ $product['name'] }}
                                            <small class="d-block text-muted">
                                                {{ $product['orders'] }} línea(s) de orden
                                            </small>
                                        </td>
                                        <td colspan="3" class="text-muted">Total del producto</td>
                                        <td class="text-right">{{ number_format($product['requested']) }}</td>
                                        <td class="text-right text-success">{{ number_format($product['produced']) }}</td>
                                        <td class="text-right text-danger">{{ number_format($product['pending']) }}</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-success"
                                                     role="progressbar"
                                                     style="width: {{ $product['progress'] }}%"
                                                     aria-valuenow="{{ $product['progress'] }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100"></div>
                                            </div>
                                            <small>{{ number_format($product['progress'], 1) }}%</small>
                                        </td>
                                    </tr>

                                    @foreach ($product['order_rows'] as $order)
                                        @php
                                            $orderProgress = $order['requested'] > 0
                                                ? min(100, round(($order['produced'] / $order['requested']) * 100, 1))
                                                : 0;
                                        @endphp
                                        <tr wire:key="worker-ordered-{{ $order['contract_id'] }}-{{ $order['product_id'] }}-{{ $loop->index }}">
                                            <td>{{ $order['product_id'] ?: '—' }}</td>
                                            <td>{{ $order['product_cod'] ?: '—' }}</td>
                                            <td>{{ $order['product_name'] }}</td>
                                            <td>
                                                @if ($order['contract_id'])
                                                    #{{ $order['contract_id'] }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ filled($order['external_id'] ?? null) ? $order['external_id'] : '—' }}</td>
                                            <td>
                                                @forelse ($order['tags'] ?? [] as $tag)
                                                    <span class="badge badge-secondary mr-1 mb-1">
                                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted">—</span>
                                                @endforelse
                                            </td>
                                            <td class="text-right">{{ number_format($order['requested']) }}</td>
                                            <td class="text-right text-success">{{ number_format($order['produced']) }}</td>
                                            <td class="text-right text-danger">{{ number_format($order['pending']) }}</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success"
                                                         role="progressbar"
                                                         style="width: {{ $orderProgress }}%"
                                                         aria-valuenow="{{ $orderProgress }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <small>{{ number_format($orderProgress, 1) }}%</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            No se encontraron productos ordenados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
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

                        <div>
                            <span class="badge badge-{{ $assembly['priority_badge'] }}">
                                {{ $assembly['priority_label'] }}
                            </span>
                            <span class="badge badge-info">
                                {{ $assembly['percentage'] }}%
                            </span>
                        </div>
                    </div>

                    @if (!empty($assembly['detail_contract_tags']))
                        <div class="mt-3">
                            <small class="font-weight-bold">
                                Especificaciones del contrato
                            </small>
                            <div>
                                @foreach ($assembly['detail_contract_tags'] as $tag)
                                    <span class="badge badge-warning mr-1 mb-1"
                                          wire:key="worker-assembly-detail-tag-{{ $assembly['id'] }}-{{ $loop->index }}">
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (!empty($assembly['product_tags']))
                        <div class="mt-2">
                            <small class="font-weight-bold">
                                Etiquetas globales del producto
                            </small>
                            <div>
                                @foreach ($assembly['product_tags'] as $tag)
                                    <span class="badge badge-info mr-1 mb-1"
                                          wire:key="worker-assembly-product-tag-{{ $assembly['id'] }}-{{ $loop->index }}">
                                        {{ $tag['name'] }}: {{ $tag['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="progress my-3" style="height: 8px;">
                        <div class="progress-bar bg-success"
                             style="width: {{ $assembly['percentage'] }}%">
                        </div>
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

        @endif

        @if ($workerView === 'cards')
        {{-- PROCESOS DEL ÁREA --}}
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

                        <div>
                            <span class="badge badge-{{ $process['priority_badge'] }}">
                                {{ $process['priority_label'] }}
                            </span>
                            <span class="badge badge-info">
                                {{ $process['percentage'] }}%
                            </span>
                        </div>
                    </div>

                    @foreach ([
                        'detail_contract_tags' => ['Especificaciones del contrato', 'warning'],
                        'product_tags' => ['Etiquetas globales del producto', 'info'],
                        'component_tags' => ['Etiquetas globales del componente', 'info'],
                        'process_tags' => ['Etiquetas de mi área', 'secondary'],
                    ] as $tagKey => [$tagTitle, $tagColor])
                        @if (!empty($process[$tagKey]))
                            <div class="mt-2">
                                <small class="font-weight-bold">{{ $tagTitle }}</small>
                                <div>
                                    @foreach ($process[$tagKey] as $tag)
                                        <span class="badge badge-{{ $tagColor }} mr-1 mb-1"
                                              wire:key="worker-process-{{ $tagKey }}-{{ $process['id'] }}-{{ $loop->index }}">
                                            {{ $tag['name'] }}: {{ $tag['value'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="progress my-3" style="height: 8px;">
                        <div class="progress-bar bg-success"
                             style="width: {{ $process['percentage'] }}%">
                        </div>
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

    @endif
</div>
