@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Ventas</h1>
                </div>
                <div class="col-sm-6">
                    <!-- Boton Nueva Venta -->
                    <!-- Verificar si la caja esta abierta y es del dia actual entonces muestro el boton nueva venta -->
                    @if (
                        !empty($caja_abierta) &&
                            \Carbon\Carbon::parse($caja_abierta->fecha_apertura)->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d'))
                        <a class="btn btn-primary float-right" href="{{ route('ventas.create') }}">
                            <i class="fas fa-plus"></i>
                            Nueva Venta
                        </a>
                    @endif

                    <!-- Boton Abrir Caja -->
                    <!-- Verificar si la caja esta cerrada entonces muestro el boton abrir caja -->
                    @if (empty($caja_abierta))
                        <a class="btn btn-default float-right mr-2" data-toggle="modal" data-target="#apertura"
                            href="#">
                            <i class="fas fa-cart-plus"></i>
                            Abrir Caja
                        </a>
                    @endif

                    <!-- Boton Cerrar Caja -->
                    <!-- Verificar si la caja esta abierta y es del dia actual entonces muestro el boton cerrar caja -->
                    @if (
                        !empty($caja_abierta) &&
                            \Carbon\Carbon::parse($caja_abierta->fecha_apertura)->format('Y-m-d') <= \Carbon\Carbon::now()->format('Y-m-d'))
                        <a class="btn btn-success float-right mr-2" href="#" 
                            id="cerrar" 
                            data-id="{{ isset($caja_abierta) ? $caja_abierta->id_apertura : null }}">
                            <i class="fas fa-cart-plus"></i>
                            Cerrar Caja
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        {{-- @include('flash::message') --}}
        @include('adminlte-templates::common.errors')
        @include('sweetalert::alert')

        <div class="clearfix">
            @includeIf('layouts.buscador', ['url' => url()->current()])
        </div>

        <div class="card tabla-container">
            @include('ventas.table')
        </div>
    </div>

    <!-- Modal Apertura Caja y compartir la variable cajas-->
    @includeIf('ventas.modal_apertura', ['cajas' => $cajas])

    <!-- llamar a modal cerrar -->
    @include('ventas.modal_cerrar', ['cajas' => $cajas])

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $("#cerrar").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                let $this = this;

                if ($this.classList.contains('disabled')) {
                    return false;
                }
                // Modal
                let modalRef = document.getElementById('cerrar-caja');
                const id = $this.getAttribute('data-id');
                // Obtener datos del cierre de caja a través de la API que esta en el controlador de apertura y cierre caja
                const url = "{{ url('apertura_cierre/editCierre') }}/" + id;

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(res => {
                        /** recuperar valores y mostrarlos */
                        // Si la respuesta es success True proceso el resultado devuelto por la funcion
                        if (res.success) {
                            document.getElementById("fecha").value = res.apertura.fecha_apertura;
                            document.getElementById("monto_cierre").value = formatMoney(res.total_ventas);
                            document.getElementById("monto_apertura").value = formatMoney(res.apertura
                                .monto_apertura);
                            document.getElementById("caj_cod").value = res.apertura.id_caja;
                            // Dispara el evento 'change' manualmente
                            const event = new Event('change');
                            document.getElementById("caj_cod").dispatchEvent(event);

                            // url para cerrar caja y enviar los datos a la funcion cerrar_caja del controlador aperturacierre
                            const formUrl = "{{ url('apertura_cierre/cerrar_caja') }}/" + id;
                            // Asignar la URL del formulario al modal para que redireccione al método PATCH de la API de cierre de caja
                            modalRef.querySelector('#form-apertura').setAttribute("action", formUrl);
                            $("#cerrar-caja").modal("show"); //mostrar  el  modal
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: res.message,
                                icon: 'error',
                                confirmButtonText: 'Ok'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Hubo un problema al procesar la solicitud.',
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    });
            });
        });

         /** esta funcion nos ayudara a dar el formato a nuestros precios en javacript y colocar el separador de miles correspondientes */
        function formatMoney (n, c, d, t) {
            let s, i, j;
            c = isNaN(c = Math.abs(c)) ? 0 : c;
            d = d === undefined ? "," : d;
            t = t === undefined ? "." : t;
            s = n < 0 ? "-" : "";
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c)));
            j = (j = i.length) > 3 ? j % 3 : 0;
            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
                (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
        }

    </script>
@endpush