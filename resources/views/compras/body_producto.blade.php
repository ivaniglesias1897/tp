<table class="table">
    <thead>
        <tr>
            <th>Código de Producto</th>
            <th>Producto</th>
            <th>Precio</th>
        </tr>
    </thead>
    <tbody>
        @forelse($productos as $product)
            <tr onclick="seleccionarProducto('{{ $product->id_producto }}', '{{ $product->descripcion }}', '{{ $product->precio }}')">
                <td>{{ $product->id_producto }}</td>
                <td>{{ $product->descripcion }}</td>
                <td>{{ number_format($product->precio, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No se encontraron productos.</td>
            </tr>
        @endforelse
    </tbody>
</table>
