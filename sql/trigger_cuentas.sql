-- crear la funcion
CREATE OR REPLACE FUNCTION generar_ctas_cobrar()
  RETURNS trigger AS
$BODY$ 
DECLARE 
	-- se declaran variable a utilizar
    monto_a_cobrar integer;
    vencimiento date;
    cont integer; -- contador
    dias interval; -- Cambiado el tipo de datos a interval
    primer_vto date;
BEGIN 

	-- Solo si la condicion venta = CREDITO DEBE EJECUTARSE
	if new.condicion_venta = 'CREDITO' THEN
	    monto_a_cobrar = round(new.total / new.cantidad_cuota);--round es redondeo
	    -- Construir el intervalo a partir de la variable días
	    dias = (new.intervalo || ' days')::interval;

	    select NEW.fecha_venta + dias into primer_vto;

		-- Recorrer deacuerdo a la cantidad cuota para realizar los insert en cuentas_a_cobrar
	    FOR cont IN 1..new.cantidad_cuota LOOP
			-- calcular vencimiento segun intervalo y fecha de venta
	        vencimiento = primer_vto + ((cont - 1) * dias);

	        INSERT INTO cuentas_a_cobrar(id_cliente, id_venta, vencimiento, importe, nro_cuota, estado)
	        VALUES(new.id_cliente, new.id_venta, vencimiento, monto_a_cobrar, cont, 'PENDIENTE');

	    END LOOP;
	   
    	RETURN NEW;
   	end if;
	
	return null;
END; 
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;


-- crear el trigger para eso primero debe ejecutarse la funcion
create trigger trg_ctas_a_cobrar after
insert
on
public.ventas for each row execute procedure generar_ctas_cobrar()


select NOW() + INTERVAL '30 days'

-- Forma 2

CREATE OR REPLACE FUNCTION generar_ctas_cobrar()
RETURNS TRIGGER AS
$BODY$
DECLARE 
    monto_a_cobrar NUMERIC(19,2);  -- Mantenemos precisión decimal para cálculos
    vencimiento DATE;
    cont INTEGER;
    dias INTERVAL;
    primer_vto DATE;
    monto_redondeado INTEGER;       -- Variable específica para el valor entero
BEGIN 
    -- Solo para ventas al crédito
    IF NEW.condicion_venta = 'CREDITO' THEN
        -- Validar que haya cantidad de cuotas > 0 y intervalo válido
        IF COALESCE(NEW.cantidad_cuota, 0) > 0 AND COALESCE(NEW.intervalo, 0) > 0 THEN
            -- Calcular monto por cuota y redondear a entero
            monto_redondeado := ROUND(NEW.total / NEW.cantidad_cuota);
            
            -- Calcular intervalo dinámico
            dias := (NEW.intervalo || ' days')::INTERVAL;
            primer_vto := NEW.fecha_venta + dias;
            
            -- Generar cuotas
            FOR cont IN 1..NEW.cantidad_cuota LOOP
                vencimiento := primer_vto + ((cont - 1) * dias);
                
                -- Insertar con valor entero (convertido a DOUBLE PRECISION)
                INSERT INTO cuentas_a_cobrar (
                    id_cliente, 
                    id_venta, 
                    vencimiento, 
                    importe, 
                    nro_cuenta, 
                    estado
                ) VALUES (
                    NEW.id_cliente, 
                    NEW.id_venta, 
                    vencimiento, 
                    monto_redondeado::DOUBLE PRECISION,  -- Conversión explícita
                    cont, 
                    'PENDIENTE'
                );
            END LOOP;
        END IF;
    END IF;
    
    RETURN NEW;  -- Siempre retornar NEW en triggers AFTER
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;


CREATE TRIGGER trg_ctas_a_cobrar
AFTER INSERT ON ventas
FOR EACH ROW EXECUTE PROCEDURE generar_ctas_cobrar();