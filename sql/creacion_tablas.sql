create table cargos(
    id_cargo  serial not null,
    descripcion varchar(50) not null,
    primary key(id_cargo)
);

create table departamentos(
    id_departamento  serial not null,
    descripcion varchar(100) not null,
    primary key(id_departamento)
);

create table ciudades(
    id_ciudad  serial not null,
    descripcion varchar(100) not null,
    id_departamento integer not null,
    primary key(id_ciudad),
    foreign key(id_departamento) references departamentos(id_departamento)
);

create table clientes(
    id_cliente  serial not null,
    clie_nombre varchar(255) not null,
    clie_apellido varchar(255) not null,
    clie_ci varchar(40) not null,
    clie_telefono varchar(40),
    clie_direccion text,
    id_ciudad integer not null,
    primary key(id_cliente),
    foreign key(id_ciudad) references ciudades(id_ciudad)
);

create table marcas(
    id_marca  serial not null,
    descripcion varchar(100) not null,
    primary key(id_marca)
);

create table productos(
    id_producto  serial not null,
    descripcion varchar(255) not null,
    precio numeric(10,2) not null,
    tipo_iva integer,
    id_marca integer,
    primary key(id_producto),
    foreign key(id_marca) references marcas(id_marca)
);

create table sucursales(
    id_sucursal  serial not null,
    descripcion varchar(255) not null,
    direccion text,
    telefono varchar(40),
    id_ciudad integer,
    primary key(id_sucursal),
    foreign key(id_ciudad) references ciudades(id_ciudad)
);

create table proveedores(
    id_proveedor  serial not null,
    descripcion varchar(255) not null,
    direccion text,
    telefono varchar(40),
    primary key(id_proveedor)
);

create table stocks(
    id_stock  serial not null,
    id_producto integer not null,
    id_sucursal integer not null,
    cantidad integer not null,
    primary key(id_stock),
    foreign key(id_producto) references productos(id_producto),
    foreign key(id_sucursal) references sucursales(id_sucursal)
);

create table cajas(
    id_caja  serial not null,
    descripcion varchar(255) not null,
    id_sucursal integer not null,
    punto_expedicion integer not null,
    ultima_factura_impresa integer not null,
    primary key(id_caja),
    foreign key(id_sucursal) references sucursales(id_sucursal)
);

create table apertura_cierre_cajas(
    id_apertura  serial not null,
    id_caja integer not null,
    fecha_apertura date not null,
    fecha_cierre date,
    monto_apertura numeric(19,2) not null,
    monto_cierre numeric(19,2) not null,
    user_id integer not null,
    primary key(id_apertura),
    foreign key(id_caja) references cajas(id_caja)
);

create table ventas(
    id_venta  serial not null,
    id_apertura integer not null,
    id_cliente integer not null,
    total numeric(19,2) not null,
    fecha_venta date not null,
    factura_nro varchar(20) not null,
    user_id integer not null,
    primary key(id_venta),
    foreign key(id_apertura) references apertura_cierre_cajas(id_apertura),
    foreign key(id_cliente) references clientes(id_cliente)
);

create table detalle_ventas(
    id_detalle_venta  serial not null,
    id_venta integer not null,
    id_producto integer not null,
    cantidad integer not null,
    precio numeric(19,2) not null,
    primary key(id_detalle_venta),
    foreign key(id_venta) references ventas(id_venta),
    foreign key(id_producto) references productos(id_producto)
);

create table compras(
    id_compra  serial not null,
    id_proveedor integer not null,
    fecha_compra date not null,
    total numeric(19,2) not null,
    user_id integer not null,
    primary key(id_compra),
    foreign key(id_proveedor) references proveedores(id_proveedor)
);

create table detalle_compras(
    id_detalle_compra  serial not null,
    id_compra integer not null,
    id_producto integer not null,
    cantidad integer not null,
    precio_unitario numeric(19,2) not null,
    primary key(id_detalle_compra),
    foreign key(id_compra) references compras(id_compra),
    foreign key(id_producto) references productos(id_producto)
);




