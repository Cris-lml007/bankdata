<x-slot name="header">

    <h1>Nueva Fábrica</h1>

</x-slot>

<div>

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                <i class="fa fa-industry"></i>
                Datos de la fábrica
            </h3>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <div class="form-group">

                        <label>Nombre</label>

                        <input
                            type="text"
                            wire:model="industry.name"
                            class="form-control @error('industry.name') is-invalid @enderror"
                            placeholder="Nombre de la fábrica"
                        >

                        @error('industry.name')
                        <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="form-group">

                        <label>Tipo de fábrica</label>

                        <select
                            wire:model="industry.type_factory_id"
                            class="form-control @error('industry.type_factory_id') is-invalid @enderror"
                        >

                            <option value="">
                                Seleccione un tipo
                            </option>

                            @foreach ($typeFactories as $typeFactory)

                                <option value="{{ $typeFactory->id }}">
                                    {{ $typeFactory->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('industry.type_factory_id')
                        <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>

            </div>


            <div class="row">

                <div class="col-md-8">

                    <div class="form-group">

                        <label>Dirección</label>

                        <input
                            type="text"
                            wire:model="industry.address"
                            class="form-control @error('industry.address') is-invalid @enderror"
                            placeholder="Dirección de la fábrica"
                        >

                        @error('industry.address')
                        <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="form-group">

                        <label>Teléfono</label>

                        <input
                            type="text"
                            wire:model="industry.phone"
                            class="form-control @error('industry.phone') is-invalid @enderror"
                            placeholder="Teléfono"
                        >

                        @error('industry.phone')
                        <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="card">

        <div class="card-body text-right">

            <a
                href="{{ route('dashboard.industries') }}"
                class="btn btn-secondary"
            >
                <i class="fa fa-times"></i>
                Cancelar
            </a>

            <button
                type="button"
                wire:click="save"
                class="btn btn-primary"
            >
                <i class="fa fa-save"></i>
                Guardar
            </button>

        </div>

    </div>

</div>
