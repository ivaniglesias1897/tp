Examen Final Proyecto
1. Validaciones de descripciones: evitar duplicados  OK
2. Tener sección de cobros de cuentas a cobrar: debe poder pagar la deuda parcial o total y validar el estado de cuenta, utilizar la misma logica de cobros de ventas. OK
3. Tener sección de pagos a proveedores: tener la posibilidad de registrar pagos parciales o totales y guardar el nro de recibo o factura del cual se pago. Para ello se debe crear la tabla de pagos_proveedores OK
4. Tener la posibilidad de anular pagos a proveedores, y validar que el saldo se actualice segun lo anulado OK
5. Posibilidad de anular un cobro de cuentas a cobrar. Ejemplo: si el pago fue 50000 y el saldo era anteriormente 100mil, entonces actualizar a 100mil devuelta el saldo. Pendiente OK
6. Agregar algo personalizado al proyecto que podria ser ejemplo: pedidos de ventas, orden de compras y asociar a compras, transferencias de mercaderias entre sucursales o ajustes de stock. OK
*****
7. Agregar ayuda interactiva que debe estar incluida dentro del sistema, puede ser un pdf o video instructivo de como utilizar el sistema desde el inicio a fin. pendiente
*****
8. Tener bien configurado los roles y permisos. Todas las vistas deben depender de ello. ok
*****
9. Manejar errores de borrado de registros que siendo utilizados en otro lugar. Utililzar los try catch para en destroy ok

10. Todas las vistas deben tener el buscador generico. OK

11. Reportes de movimientos con filtros. OK
12. Todos los html del index deben poseer paginadores
13. Todos los html del index deben poseer el buscador, y debe filtrar por todas las columnas visibles del index ok



Falta implementar:
1.Auditoría Visual en: 
Ventas (Para detectar fraudes o cambios en facturación).
Usuarios (Para seguridad y control de accesos).
Clientes (Para proteger datos fiscales y créditos).
Proveedores.
********************************************************
En productos: no deja eliminar productos si existen ventas asociadas.
********************************************************
1. Tablas Auxiliares (Máxima Prioridad - Falla de Reportes si hay duplicados)
Prioridad,Controlador,Método(s),Campo(s) a Validar,Tabla/PK
Alta,MarcaController,"store, update",descripcion,marcas (id_marca) 
Alta,CargoController,"store, update",descripcion,cargos (id_cargo)
Alta,DepartamentoController,"store, update",descripcion,departamentos (id_departamento)
Alta,CiudadController,"store, update",descripcion,ciudades (id_ciudad) 
Alta,SucursalController,"store, update",descripcion,sucursales (id_sucursal) 
Alta,CajaController,"store, update",descripcion,cajas (id_caja) ya estaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa*******************
Alta,MetodoPagoController,"store, update",descripcion,metodo_pagos (id_metodo_pago)

2. Tablas de Catálogo Principal (Alta Prioridad - Falla de Operación)
Prioridad,Controlador,Método(s),Campo(s) a Validar,Tabla/PK
Media,ProductoController,"store, update",descripcion,productos (id_producto)
Media,ProveedorController,"store, update",descripcion,proveedores (id_proveedor)
*****************************************************
3. Tablas de Persona (Prioridad en Documento)
Prioridad,Controlador,Método(s),Campo(s) a Validar,Tabla/PK
Media,ClienteController,"store, update",clie_ci,clientes (id_cliente)
Media,UserController,"store, update","email, ci",users (id)