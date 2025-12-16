-- alter table user agregar la columman role_id
alter table users add column role_id integer
alter table users add constraint fk_role_id 
foreign key (role_id) references roles(id);