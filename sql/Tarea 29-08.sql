create table pedidos(
    id_pedido serial not null,
    id_cliente integer not null,
    fecha_pedido date not null,
    total_pedido decimal(10, 2) not null,
    id_sucursal integer,
    estado varchar(20) not null default 'PENDIENTE',
    primary key (id_pedido),
    foreign key (id_cliente) references clientes(id_cliente),
    foreign key (id_sucursal) references sucursales(id_sucursal)
)

create table detalle_pedido(
    id_detalle_pedido serial not null,
    id_pedido integer not null,
    id_producto integer not null,
    cantidad integer not null,
    precio_unitario decimal(10, 2) not null,
    primary key (id_detalle_pedido),
    foreign key (id_pedido) references pedidos(id_pedido),
    foreign key (id_producto) references productos(id_producto)
)

-- crear la columna en la tabla de ventas
alter table ventas add column id_pedido integer;
alter table ventas add foreign key (id_pedido) references pedidos(id_pedido);


-- Crear los formulario cabecera y detalle similar a ventas
1. php artisan make:controller PedidosController
2. Crear la ruta en routes/web.php
3. Crear las vistas necesarias en resources/views/pedidos con el comando php artisan infyom.scaffold:views Pedidos --fromTable --table=pedidos
4. Crear el menu en resources/views/layouts/menu.blade.php
5. Realizar la programacion en el controlador app/Http/Controllers/PedidosController.php