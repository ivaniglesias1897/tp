CREATE TABLE cuentas_a_pagar (
    id_cta SERIAL PRIMARY KEY,
    id_proveedor INTEGER NOT NULL REFERENCES proveedores(id_proveedor),
    id_compra INTEGER NOT NULL REFERENCES compras(id_compra),
    vencimiento DATE NOT NULL,
    importe NUMERIC(19,2) NOT NULL,
    nro_cuenta INTEGER NOT NULL,
    estado VARCHAR(20) DEFAULT 'PENDIENTE'
);


CREATE OR REPLACE FUNCTION generar_ctas_pagar()
RETURNS TRIGGER AS
$BODY$
DECLARE 
    monto_a_pagar NUMERIC(19,2);
    vencimiento DATE;
    cont INTEGER;
    dias INTERVAL;
    primer_vto DATE;
BEGIN 
    -- Solo para compras al crédito
    IF NEW.condicion_compra = 'CREDITO' THEN
        -- Validar que haya cantidad de cuotas > 0 y intervalo válido
        IF COALESCE(NEW.cantidad_cuotas, 0) > 0 AND COALESCE(NEW.intervalo, 0) > 0 THEN
            -- Calcular monto por cuota
            monto_a_pagar := ROUND(NEW.total / NEW.cantidad_cuotas);
            
            -- Calcular intervalo dinámico
            dias := (NEW.intervalo || ' days')::INTERVAL;
            primer_vto := NEW.fecha_compra + dias;
            
            -- Generar cuotas
            FOR cont IN 1..NEW.cantidad_cuotas LOOP
                vencimiento := primer_vto + ((cont - 1) * dias);
                
                INSERT INTO cuentas_a_pagar (
                    id_proveedor, 
                    id_compra, 
                    vencimiento, 
                    importe, 
                    nro_cuenta, 
                    estado
                ) VALUES (
                    NEW.id_proveedor, 
                    NEW.id_compra, 
                    vencimiento, 
                    monto_a_pagar, 
                    cont, 
                    'PENDIENTE'
                );
            END LOOP;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;

CREATE TRIGGER trg_ctas_a_pagar
AFTER INSERT ON compras
FOR EACH ROW EXECUTE PROCEDURE generar_ctas_pagar();