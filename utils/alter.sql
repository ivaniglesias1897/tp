--agregar colummna imagen a productos
ALTER TABLE productos ADD COLUMN imagen_producto VARCHAR(199);


-- agregar columman sucursal_id en users
ALTER TABLE users ADD COLUMN id_sucursal INTEGER;
ALTER TABLE users ADD CONSTRAINT fk_sucursal_users FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal);

--agregar colummna estado en apertura_cierre_cajas
ALTER TABLE apertura_cierre_cajas ADD COLUMN estado VARCHAR(20);


--08/10-2025
-- crear metodo pago
Create table metodo_pagos(
    id_metodo_pago serial not null,
    descripcion varchar(100),
    estado boolean default true,
    primary key(id_metodo_pago)
);

--Crear tabla de cobros
Create table cobros(
    id_cobro serial not null,
    id_venta integer,
    user_id integer,
    id_metodo_pago integer,
    cobro_fecha date,
    cobro_importe float,
    cobro_estado varchar(50) default 'COBRADO',
    nro_voucher varchar(200),
    primary key (id_cobro),
    foreign key (id_venta) references ventas(id_venta),
    foreign key (user_id) references users(id),
    foreign key (id_metodo_pago) references metodo_pagos(id_metodo_pago)
);

select coalesce sum(c.cobro_importe) from cobros as total_cobros
from cobros c
join ventas ve on ve.id_venta = c.id_venta
where ve.id_apertura = 3
and ve.estado = 'COMPLETADO';