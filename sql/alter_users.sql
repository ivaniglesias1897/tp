-- columnas agregadas a la tabla users
alter table users add column ci varchar(20);
alter table users add column direccion text;
alter table users add column telefono varchar(20);
alter table users add column fecha_ingreso date;
alter table users add column estado boolean default true;
ALTER table clientes add column clie_fecha_nac date;
ALTER table clientes add column id_departamento integer
--agregar clave foranea
ALTER table clientes add constraint fk_clientes_departamento foreign key(id_departamento)
references departamentos(id_departamento);