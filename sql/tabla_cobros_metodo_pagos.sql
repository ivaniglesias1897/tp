-- crear tabla metodo pago

create table metodo_pagos(
	id_metodo_pago serial not null,
	descripcion varchar(100),
	estado boolean default true,
	primary key(id_metodo_pago)
);

-- crear tabla de cobros
create table cobros(
	id_cobro  serial not null,
	id_venta integer,
	user_id integer,
	id_metodo_pago integer,
	cobro_fecha date,
	cobro_importe float,
	cobro_estado varchar(50) default 'COBRADO',
	nro_voucher varchar(200),
	primary key(id_cobro),
	foreign key(id_venta) references ventas(id_venta),
	foreign key(user_id) references users(id),
	foreign key(id_metodo_pago) references metodo_pagos(id_metodo_pago)
);