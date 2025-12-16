{{-- Modal Diff --}}
<div class="modal fade" id="modalDiff" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalDiffTitle">Diferencias</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
                    <div id="wrapOcultarIguales">
                        <label class="mb-0">
                            <input type="checkbox" id="ocultarIguales" checked>
                            Ocultar campos iguales
                        </label>
                    </div>
                    <div class="small text-muted">
                        <span class="badge badge-warning">Cambiado</span>
                        <span class="badge badge-success">Agregado</span>
                        <span class="badge badge-danger">Eliminado</span>
                        <span class="badge badge-secondary">Igual</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="tablaDiff">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 260px">Campo</th>
                                <th>Anterior</th>
                                <th>Nuevo</th>
                                <th style="width: 130px">Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Colores para identificar cambios rápidamente */
    #tablaDiff tbody tr.diff-changed { background: rgba(255, 193, 7, 0.10); }   /* Amarillo */
    #tablaDiff tbody tr.diff-added   { background: rgba(40, 167, 69, 0.10); }   /* Verde */
    #tablaDiff tbody tr.diff-removed { background: rgba(220, 53, 69, 0.10); }   /* Rojo */
    #tablaDiff tbody tr.diff-same    { background: rgba(108, 117, 125, 0.07); } /* Gris */
    
    /* Fuente monoespaciada para leer mejor los datos técnicos */
    #tablaDiff td:nth-child(2),
    #tablaDiff td:nth-child(3) {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>

<script>
(function() {
    // Lista de campos sensibles que preferimos no mostrar
    const SENSITIVE_KEYS = ['password', 'remember_token', 'token', 'secret', 'api_key'];

    // --- FUNCIÓN PRINCIPAL DE DECODIFICACIÓN ---
    // Esta función recibe el Base64, lo abre y arregla los caracteres extraños
    function b64ToUtf8(str) {
        if (!str) return '{}';
        try {
            // 1. Decodificar Base64
            const binary = atob(str);
            // 2. Arreglar codificación UTF-8 (Esto soluciona lo de 'Ã³' -> 'ó')
            return decodeURIComponent(escape(binary));
        } catch (e) {
            console.error("Error al decodificar Base64:", e);
            return '{}'; 
        }
    }

    // Función para formatear valores (null, booleanos, objetos)
    function pretty(v) {
        if (v === null || v === undefined) return '<em class="text-muted">null</em>';
        if (typeof v === 'boolean') return v ? 'true' : 'false';
        if (typeof v === 'number') return String(v);
        if (typeof v === 'object') return JSON.stringify(v, null, 2);
        return String(v);
    }

    // Ocultar contraseñas o tokens
    function maskIfSensitive(key, raw) {
        return key && SENSITIVE_KEYS.includes(String(key).toLowerCase()) ? '••••••••' : raw;
    }

    // Comparar si dos valores son iguales (profundamente)
    function deepEqual(x, y) {
        if (x === y) return true;
        if (typeof x !== typeof y) return false;
        if (x && y && typeof x === 'object') {
            if (Array.isArray(x) !== Array.isArray(y)) return false;
            if (Array.isArray(x)) {
                if (x.length !== y.length) return false;
                for (let i = 0; i < x.length; i++) if (!deepEqual(x[i], y[i])) return false;
                return true;
            }
            const kx = Object.keys(x), ky = Object.keys(y);
            if (kx.length !== ky.length) return false;
            for (const k of kx) if (!deepEqual(x[k], y[k])) return false;
            return true;
        }
        return false;
    }

    // Generar las filas comparando objeto A (Anterior) y B (Nuevo)
    function diffObjects(a, b) {
        const keys = new Set([...(Object.keys(a || {})), ...(Object.keys(b || {}))]);
        const rows = [];
        Array.from(keys).sort().forEach(key => {
            const va = a ? a[key] : undefined;
            const vb = b ? b[key] : undefined;
            let estado = 'same';
            if (va === undefined && vb !== undefined) estado = 'added';
            else if (va !== undefined && vb === undefined) estado = 'removed';
            else if (!deepEqual(va, vb)) estado = 'changed';
            rows.push({ key, va, vb, estado });
        });
        return rows;
    }

    // Escapar HTML para evitar inyección de código visual
    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Renderizar las filas en la tabla
    function renderRows(rows, ocultarIguales) {
        const $tbody = $('#tablaDiff tbody');
        $tbody.empty();
        rows.forEach(r => {
            if (ocultarIguales && r.estado === 'same') return;
            
            let trClass = 'diff-same', badge = '<span class="badge badge-secondary">Igual</span>';
            if (r.estado === 'changed') { trClass = 'diff-changed'; badge = '<span class="badge badge-warning">Cambiado</span>'; }
            if (r.estado === 'added')   { trClass = 'diff-added';   badge = '<span class="badge badge-success">Agregado</span>'; }
            if (r.estado === 'removed') { trClass = 'diff-removed'; badge = '<span class="badge badge-danger">Eliminado</span>'; }
            
            $tbody.append(`
                <tr class="${trClass}">
                    <td><code>${escapeHtml(r.key)}</code></td>
                    <td>${maskIfSensitive(r.key, pretty(r.va))}</td>
                    <td>${maskIfSensitive(r.key, pretty(r.vb))}</td>
                    <td>${badge}</td>
                </tr>
            `);
        });
        
        if (!$tbody.children().length) {
            $tbody.append(`<tr><td colspan="4" class="text-center text-muted py-4">No hay diferencias para mostrar.</td></tr>`);
        }
    }

    function normalizeOp(txt) {
        return (txt || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase().trim();
    }

    // === EVENTO AL HACER CLIC EN EL OJO ===
    window.verDiferencias = function(el) {
        const titulo = el.getAttribute('data-titulo') || 'Diferencias';
        const operRaw = el.getAttribute('data-operacion') || '';
        const operacion = normalizeOp(operRaw);

        // 1. LEER LOS ATRIBUTOS BASE64 (Que pusimos en el Paso 1)
        const b64Anterior = el.getAttribute('data-anterior-b64');
        const b64Nuevo    = el.getAttribute('data-nuevo-b64');

        // 2. DECODIFICAR Y LIMPIAR
        const anteriorTxt = b64ToUtf8(b64Anterior);
        const nuevoTxt    = b64ToUtf8(b64Nuevo);

        // 3. PARSEAR JSON
        let objA = {}, objB = {};
        try {
            objA = JSON.parse(anteriorTxt);
            objB = JSON.parse(nuevoTxt);
        } catch (e) {
            console.error("Error parseando JSON", e);
        }

        // Lógica de visualización según el tipo de operación
        const isAgrega = operacion.includes('AGREGA');
        const isElimina = operacion.includes('ELIMINA');
        const isModifica = operacion.includes('MODIFICA');

        let rows = [];
        const $wrapChk = $('#wrapOcultarIguales');
        $wrapChk.removeClass('d-none invisible');

        if (isAgrega) {
            rows = Object.keys(objB || {}).sort().map(k => ({ key: k, va: undefined, vb: objB[k], estado: 'added' }));
            $wrapChk.toggle(false);
            $('#ocultarIguales').prop('checked', true).prop('disabled', true);
        } else if (isElimina) {
            rows = Object.keys(objA || {}).sort().map(k => ({ key: k, va: objA[k], vb: undefined, estado: 'removed' }));
            $wrapChk.toggle(false);
            $('#ocultarIguales').prop('checked', true).prop('disabled', true);
        } else {
            rows = diffObjects(objA, objB);
            $wrapChk.toggle(isModifica);
            $('#ocultarIguales').prop('checked', true).prop('disabled', !isModifica);
        }

        $('#modalDiffTitle').html(`${escapeHtml(titulo)} <small class="text-muted">[${escapeHtml(operRaw)}]</small>`);

        const usarChk = $wrapChk.is(':visible');
        const ocultar = usarChk ? $('#ocultarIguales').prop('checked') : true;
        renderRows(rows, ocultar);

        if ($('#modalDiff').modal) { $('#modalDiff').modal('show'); }
        else { (new bootstrap.Modal(document.getElementById('modalDiff'))).show(); }

        $('#ocultarIguales').off('change').on('change', function() {
            if (!$('#wrapOcultarIguales').is(':visible')) return;
            renderRows(rows, this.checked);
        });
    };
})();
</script>