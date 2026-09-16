<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h1 class="h3 mb-0">
                Planificación del contrato #{{ $contract->id }}
            </h1>

            <small class="text-muted">
                Distribución de ensamblaje y fabricación de componentes
            </small>

        </div>

        <a
            href="{{ route('dashboard.contracts.view', $contract) }}"
            class="btn btn-secondary"
        >

            <i class="fas fa-arrow-left"></i>

            Volver

        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- MENSAJE --}}
    {{-- ========================================================= --}}

    @if (session('success'))

        <div class="alert alert-success">

            <i class="fas fa-check-circle"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- PLANNING --}}
    {{-- ========================================================= --}}

    @if ($planningContract)

        <div class="card card-warning">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-tasks"></i>

                    Designación de tareas

                </h3>

            </div>


            <div class="card-body">

                <div class="alert alert-info">

                    <i class="fas fa-info-circle"></i>

                    Primero distribuye las unidades del producto entre
                    las fábricas de ensamblaje. Después, para cada
                    ensamblaje, asigna la fábrica que fabricará cada
                    componente.

                </div>


                {{-- ================================================= --}}
                {{-- PRODUCTOS --}}
                {{-- ================================================= --}}

                @foreach ($planning as $productIndex => $product)

                    <div
                        class="card card-outline card-primary mb-4"
                        wire:key="planning-product-{{ $product['product_id'] }}"
                    >

                        {{-- PRODUCT HEADER --}}

                        <div class="card-header">

                            <div class="d-flex justify-content-between align-items-center">

                                <strong>

                                    <i class="fas fa-box"></i>

                                    {{ $product['product_name'] }}

                                </strong>


                                <span class="badge badge-primary">

                                    {{ $product['required_quantity'] }}

                                    unidades requeridas

                                </span>

                            </div>

                        </div>


                        <div class="card-body">


                            {{-- ================================================= --}}
                            {{-- ASSEMBLIES --}}
                            {{-- ================================================= --}}

                            @foreach (
                                $product['assemblies']
                                as $assemblyIndex => $assembly
                            )

                                <div
                                    class="card card-outline card-secondary mb-4"
                                    wire:key="assembly-plan-{{ $productIndex }}-{{ $assemblyIndex }}"
                                >

                                    {{-- ASSEMBLY HEADER --}}

                                    <div class="card-header">

                                        <div class="d-flex justify-content-between">

                                            <strong>

                                                <i class="fas fa-industry"></i>

                                                Ensamblaje
                                                #{{ $assemblyIndex + 1 }}

                                            </strong>


                                            @if (
                                                count($product['assemblies']) > 1
                                            )

                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    wire:click="removeAssembly({{ $productIndex }}, {{ $assemblyIndex }})"
                                                >

                                                    <i class="fas fa-trash"></i>

                                                    Eliminar

                                                </button>

                                            @endif

                                        </div>

                                    </div>


                                    <div class="card-body">


                                        {{-- ================================================= --}}
                                        {{-- FÁBRICA Y CANTIDAD DEL ASSEMBLY --}}
                                        {{-- ================================================= --}}

                                        <div class="row">

                                            <div class="col-md-8">

                                                <label>
                                                    Fábrica de ensamblaje
                                                </label>

                                                <select
                                                    class="form-control"
                                                    wire:model="planning.{{ $productIndex }}.assemblies.{{ $assemblyIndex }}.industry_id"
                                                >

                                                    <option value="">
                                                        Seleccione una fábrica
                                                    </option>

                                                    @foreach ($industries as $industry)

                                                        <option
                                                            value="{{ $industry->id }}"
                                                        >
                                                            {{ $industry->name }}
                                                        </option>

                                                    @endforeach

                                                </select>


                                                @error(
                                                    "planning.$productIndex.assemblies.$assemblyIndex.industry_id"
                                                )

                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>

                                                @enderror

                                            </div>


                                            <div class="col-md-4">

                                                <label>
                                                    Cantidad a ensamblar
                                                </label>

                                                <input
                                                    type="number"
                                                    min="0"
                                                    class="form-control"
                                                    wire:model.live="planning.{{ $productIndex }}.assemblies.{{ $assemblyIndex }}.quantity"
                                                >


                                                @error(
                                                    "planning.$productIndex.assemblies.$assemblyIndex.quantity"
                                                )

                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>

                                                @enderror

                                            </div>

                                        </div>


                                        {{-- ================================================= --}}
                                        {{-- COMPONENTES --}}
                                        {{-- ================================================= --}}

                                        <hr>

                                        <h6 class="mb-3">

                                            <i class="fas fa-puzzle-piece"></i>

                                            Componentes necesarios

                                        </h6>


                                        @if (count($assembly['components']))

                                            <div class="table-responsive">

                                                <table class="table table-bordered table-sm">

                                                    <thead>

                                                    <tr>

                                                        <th>
                                                            Componente
                                                        </th>

                                                        <th width="180">
                                                            Cantidad
                                                        </th>

                                                        <th>
                                                            Fábrica de fabricación
                                                        </th>

                                                    </tr>

                                                    </thead>


                                                    <tbody>

                                                    @foreach (
                                                        $assembly['components']
                                                        as $componentIndex => $component
                                                    )

                                                        <tr
                                                            wire:key="assembly-component-plan-{{ $productIndex }}-{{ $assemblyIndex }}-{{ $componentIndex }}"
                                                        >

                                                            <td>

                                                                <strong>

                                                                    {{ $component['name'] }}

                                                                </strong>

                                                            </td>


                                                            <td>

                                                                    <span class="badge badge-primary">

                                                                        {{ $component['quantity'] }}

                                                                    </span>

                                                                <small class="text-muted">

                                                                    piezas

                                                                </small>

                                                            </td>


                                                            <td>

                                                                <select
                                                                    class="form-control"
                                                                    wire:model="planning.{{ $productIndex }}.assemblies.{{ $assemblyIndex }}.components.{{ $componentIndex }}.industry_id"
                                                                >

                                                                    <option value="">
                                                                        Seleccione una fábrica
                                                                    </option>

                                                                    @foreach ($industries as $industry)

                                                                        <option
                                                                            value="{{ $industry->id }}"
                                                                        >

                                                                            {{ $industry->name }}

                                                                        </option>

                                                                    @endforeach

                                                                </select>


                                                                @error(
                                                                    "planning.$productIndex.assemblies.$assemblyIndex.components.$componentIndex.industry_id"
                                                                )

                                                                <small class="text-danger">

                                                                    {{ $message }}

                                                                </small>

                                                                @enderror

                                                            </td>

                                                        </tr>

                                                    @endforeach

                                                    </tbody>

                                                </table>

                                            </div>

                                        @else

                                            <div class="alert alert-secondary">

                                                <i class="fas fa-info-circle"></i>

                                                Este producto no tiene
                                                componentes definidos.

                                            </div>

                                        @endif


                                        {{-- ================================================= --}}
                                        {{-- RESUMEN ASSEMBLY --}}
                                        {{-- ================================================= --}}

                                        <div class="alert alert-light border mt-3 mb-0">

                                            <div class="row text-center">

                                                <div class="col-md-4">

                                                    <small class="text-muted">
                                                        Fábrica
                                                    </small>

                                                    <div>

                                                        @php
                                                            $assemblyIndustry =
                                                                $industries->firstWhere(
                                                                    'id',
                                                                    $assembly['industry_id']
                                                                );
                                                        @endphp

                                                        <strong>

                                                            {{ $assemblyIndustry?->name ?? 'Sin asignar' }}

                                                        </strong>

                                                    </div>

                                                </div>


                                                <div class="col-md-4">

                                                    <small class="text-muted">
                                                        Ensamblaje
                                                    </small>

                                                    <div>

                                                        <strong>
                                                            {{ $assembly['quantity'] }}
                                                        </strong>

                                                        unidades

                                                    </div>

                                                </div>


                                                <div class="col-md-4">

                                                    <small class="text-muted">
                                                        Componentes
                                                    </small>

                                                    <div>

                                                        <strong>
                                                            {{ count($assembly['components']) }}
                                                        </strong>

                                                        tipos

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach


                            {{-- ================================================= --}}
                            {{-- AGREGAR ASSEMBLY --}}
                            {{-- ================================================= --}}

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                wire:click="addAssembly({{ $productIndex }})"
                            >

                                <i class="fas fa-plus"></i>

                                Agregar fábrica de ensamblaje

                            </button>


                            {{-- ================================================= --}}
                            {{-- RESUMEN DEL PRODUCTO --}}
                            {{-- ================================================= --}}

                            @php

                                $distributed =
                                    $this->distributedQuantity(
                                        $product['assemblies']
                                    );

                                $required =
                                    (int) $product['required_quantity'];

                                $pending =
                                    $required - $distributed;

                            @endphp


                            <div class="row mt-4">

                                <div class="col-md-4">

                                    <div class="info-box">

                                        <span class="info-box-icon bg-primary">

                                            <i class="fas fa-boxes"></i>

                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                Requerido
                                            </span>

                                            <span class="info-box-number">

                                                {{ $required }}

                                            </span>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="info-box">

                                        <span class="info-box-icon bg-success">

                                            <i class="fas fa-check"></i>

                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                Distribuido
                                            </span>

                                            <span class="info-box-number">

                                                {{ $distributed }}

                                            </span>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="info-box">

                                        <span
                                            class="info-box-icon
                                            {{ $pending == 0
                                                ? 'bg-success'
                                                : 'bg-warning' }}"
                                        >

                                            <i class="fas fa-clock"></i>

                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                Pendiente
                                            </span>

                                            <span class="info-box-number">

                                                {{ max(0, $pending) }}

                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            @error(
                                "planning.$productIndex.assemblies"
                            )

                            <div class="alert alert-danger">

                                <i class="fas fa-exclamation-triangle"></i>

                                {{ $message }}

                            </div>

                            @enderror

                        </div>

                    </div>

                @endforeach


                {{-- ================================================= --}}
                {{-- ACCIONES --}}
                {{-- ================================================= --}}

                <div class="d-flex justify-content-between">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        wire:click="cancelPlanning"
                    >

                        <i class="fas fa-times"></i>

                        Cancelar

                    </button>


                    <button
                        type="button"
                        class="btn btn-warning"
                        wire:click="savePlanning"
                    >

                        <i class="fas fa-save"></i>

                        Guardar planificación

                    </button>

                </div>

            </div>

        </div>


    @else


        {{-- ========================================================= --}}
        {{-- TAREAS YA ASIGNADAS --}}
        {{-- ========================================================= --}}

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-industry"></i>

                    Tareas asignadas

                </h3>


                <div class="card-tools">

                    <button
                        type="button"
                        class="btn btn-warning btn-sm"
                        wire:click="planContract"
                    >

                        <i class="fas fa-edit"></i>

                        {{ $contract->assemblies->count()
                            ? 'Replanificar'
                            : 'Asignar tareas' }}

                    </button>

                </div>

            </div>


            <div class="card-body">

                @if ($contract->assemblies->count())

                    @foreach ($contract->assemblies as $assembly)

                        <div
                            class="card card-outline card-primary mb-4"
                            wire:key="assembly-{{ $assembly->id }}"
                        >

                            <div class="card-header">

                                <div class="d-flex justify-content-between">

                                    <strong>

                                        <i class="fas fa-box"></i>

                                        {{ $assembly->product->name }}

                                    </strong>


                                    <span class="badge badge-primary">

                                        {{ $assembly->quantity }}

                                        unidades

                                    </span>

                                </div>

                            </div>


                            <div class="card-body">

                                {{-- ASSEMBLY --}}

                                <div class="alert alert-info">

                                    <i class="fas fa-industry"></i>

                                    <strong>
                                        Ensamblaje:
                                    </strong>

                                    {{ $assembly->industry->name }}

                                    —

                                    {{ $assembly->quantity }}
                                    unidades

                                </div>


                                {{-- COMPONENTES --}}

                                @if ($assembly->components->count())

                                    <h6>

                                        <i class="fas fa-puzzle-piece"></i>

                                        Componentes

                                    </h6>


                                    <div class="table-responsive">

                                        <table class="table table-bordered table-sm">

                                            <thead>

                                            <tr>

                                                <th>
                                                    Componente
                                                </th>

                                                <th width="180">
                                                    Cantidad
                                                </th>

                                                <th>
                                                    Fábrica
                                                </th>

                                            </tr>

                                            </thead>


                                            <tbody>

                                            @foreach (
                                                $assembly->components
                                                as $component
                                            )

                                                <tr
                                                    wire:key="component-{{ $component->id }}"
                                                >

                                                    <td>

                                                        {{ $component->component->name }}

                                                    </td>


                                                    <td>

                                                        <strong>

                                                            {{ $component->quantity }}

                                                        </strong>

                                                        piezas

                                                    </td>


                                                    <td>

                                                            <span class="badge badge-info">

                                                                <i class="fas fa-industry"></i>

                                                                {{ $component->industry->name }}

                                                            </span>

                                                    </td>

                                                </tr>

                                            @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                @else

                                    <div class="alert alert-secondary mb-0">

                                        <i class="fas fa-info-circle"></i>

                                        Este producto no tiene
                                        componentes definidos.

                                    </div>

                                @endif

                            </div>

                        </div>

                    @endforeach

                @else

                    <div class="alert alert-warning">

                        <i class="fas fa-exclamation-triangle"></i>

                        Este contrato todavía no tiene tareas asignadas.

                    </div>

                @endif

            </div>

        </div>

    @endif

</div>
