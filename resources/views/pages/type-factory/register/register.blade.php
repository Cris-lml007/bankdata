<form wire:submit="{{ $editing ? 'update' : 'save' }}">

    <div class="modal-body">

        <div class="form-group">
            <label for="name">Nombre</label>

            <input
                type="text"
                id="name"
                class="form-control @error('typeFactory.name') is-invalid @enderror"
                wire:model="typeFactory.name"
                placeholder="Ingrese el nombre del tipo de fábrica"
            >

            @error('typeFactory.name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Descripción</label>

            <textarea
                id="description"
                rows="4"
                class="form-control @error('typeFactory.description') is-invalid @enderror"
                wire:model="typeFactory.description"
                placeholder="Describa el tipo de fábrica"
            ></textarea>

            @error('typeFactory.description')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

    </div>

    <div class="modal-footer">

        <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
        >
            Cancelar
        </button>

        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i>
            {{ $editing ? 'Actualizar' : 'Guardar' }}
        </button>

    </div>

</form>
