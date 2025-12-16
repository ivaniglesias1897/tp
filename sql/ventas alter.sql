-- Alter ventas
Alter table ventas add column condicion_venta varchar(50);
Alter table ventas add column intervalo integer default 0
comment on column ventas.intervalo 
is 'intervalo para saber cada cuendo sera el vencimiento solo para creditos';
Alter table ventas add column cantidad_cuotas integer default 0;
Alter table ventas add column estado varchar(30);


--
Alter table ventas Alter column id_apertura drop not null;

-- crear columna sucursales en ventas
Alter table ventas add column id_sucursal integer;
-- agregar foreing en ventas
ALTER TABLE ventas ADD CONSTRAINT fk_id_sucursal
FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal);
