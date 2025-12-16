<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Detalles de la Compra</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- El botón para buscar productos solo aparece si NO estamos en modo "solo lectura" --}}
            @if (empty($solo_lectura))
            <div class="col-12 mb-3">
                <button type="button" class="btn btn-primary float-right" id="buscar">
                    <i class="fas fa-search" aria-hidden="true"></i> Buscar Producto
                </button>
            </div>
            @endif
            
            <div class="col-12 table-responsive">
                <table class="table item-table">
                    <thead>
                        <tr>
                            <th style="width:10%;">#</th>
                            <th style="width:40%;">Producto</th>
                            <th class="text-center" style="width:15%;">Cantidad</th>
                            <th class="text-center" style="width:15%;">Precio Unit.</th>
                            <th class="text-center" style="width:15%;">Subtotal</th>
                            @if (empty($solo_lectura))
                                <th style="width:5%;"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="selectedProducts">
                        {{-- Si la variable $detalles existe (en modo edit o show), se cargan los productos --}}
                        @if (isset($detalles))
                            @foreach ($detalles as $value)
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="codigo[]" value="{!! $value->id_producto !!}" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="producto[]" value="{!! $value->descripcion !!}" readonly>
                                </td>
                                <td>
                                    <input class="form-control text-center" type="number" min="1" name="cantidad[]" value="{!! $value->cantidad !!}" oninput="calcularSubtotal(this)" {{ !empty($solo_lectura) ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    {{-- ** CORRECCIÓN CRÍTICA: Se estandariza el nombre a 'precio[]' ** --}}
                                    <input class="form-control text-right" type="text" name="precio[]" value="{!! number_format($value->precio_unitario, 0, ',', '.') !!}" onkeyup="format(this); calcularSubtotal(this);" {{ !empty($solo_lectura) ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input class="form-control text-right" type="text" name="subtotal[]" value="{!! number_format($value->precio_unitario * $value->cantidad, 0, ',', '.') !!}" readonly>
                                </td>
                                {{-- El botón de borrar solo aparece si NO estamos en modo "solo lectura" --}}
                                @if (empty($solo_lectura))
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="borrar(this)"><i class="far fa-trash-alt"></i></button>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

