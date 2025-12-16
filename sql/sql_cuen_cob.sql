Create table cuentas_a_cobrar(
    id_cta serial,
    id_cliente integer not null,
    id_venta integer not null,
    vencimiento date not null,
    importe float not null,
    nro_cuenta integer not null,
    estado varchar(20) default 'PENDIENTE',
    foreign key(id_cliente) references clientes(id_cliente),
    foreign key(id_venta) references ventas(id_venta),
    primary key(id_cta)
    )


Create table cuentas_a_pagar(
    id_cta serial,
    id_proveedor integer not null,
    id_compra integer not null,
    vencimiento date not null,
    importe float not null,
    nro_cuenta integer not null,
    estado varchar(20) default 'PENDIENTE',
    foreign key(id_proveedor) references proveedores(id_proveedor),
    foreign key(id_compra) references compras(id_compra),
    primary key(id_cta)
    )