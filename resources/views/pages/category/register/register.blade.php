<form wire:submit="{{ $editing ? 'update' : 'save' }}">

    <div class="modal-body">

        <div class="form-group">
            <label for="name">Nombre</label>

            <input
                type="text"
                id="name"
                class="form-control @error('category.name') is-invalid @enderror"
                wire:model="category.name"
                placeholder="Ingrese el nombre de la categoría"
            >

            @error('category.name')
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
