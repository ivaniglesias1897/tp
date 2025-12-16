<form id="form-busqueda">
    <div class="input-group mb-3">
        <input type="text" class="form-control buscar" name="buscar" 
            value="{{ request()->get('buscar', null) }}"
            placeholder="Buscar..."
            data-url="{{ $url }}"
            aria-describedby="button-addon2">
        <button class="btn btn-outline-secondary"
        type="submit" id="button-addon2">Buscar</button>
    </div>
</form>