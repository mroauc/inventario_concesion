@push('page_css')
    <style>
        .tabla_products td{
            padding: 5px 10px;
        }

        .fila_prod{
            cursor: pointer;
        }

        .fila_prod:hover{
            background-color: rgba(214, 214, 214, 0.281);
        }
    </style>
@endpush
<div class="mt-4">
    @include('flash::message')
    <div id="messagges"></div>
    {{-- <div id="qr-reader" style="width: 600px"></div> --}}
    {{-- <div id="reader" style="width: 600px;"></div> --}}
    @foreach ($warehouse as $key => $store)
        <div class="card">
            <div class="card-body col-sm-12" style="overflow: auto;">
                <div style="display: flex; padding: 5px 0; justify-content: space-between;">
                    <h5 class="h4">{{$store->name}}</h5>
                    <div class="botonera" style="">
                        <button class="btn btn-success" onclick="openModalScanner({{$store->id}})">ESCANEAR</button>
                    </div>
                </div>
                
                <table class="table table-sm table-bordered mt-4 tabla_products" id="tabla_products_{{$store->id}}" style="width: 100%; overflow: auto;">
                    <thead>
                        <tr style="background-color: #efefef">
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Posición</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($store->products as $product)
                            <tr class="fila_prod" onclick="openModalInventario({{$product->id}}, {{$store->id}})">
                                <td class="td_product">{{$product->code}}</td>
                                <td class="td_product">{{$product->name}}</td>
                                <td class="td_product">{{$product->category->name}}</td>
                                <td class="td_product">{{$product->pivot->stock}}</td>
                                <td class="td_product">{{ $product->pivot->positions()->pluck('position')->implode(' - ') }}</td>
                            </tr>
                        @endforeach
                        @if (count($store->products) == 0)
                            <tr class="fila_prod">
                                <td colspan="3"><div style="text-align: center">No hay productos en {{$store->name}}</div></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
    {{-- MODAL PRODUCTO SELECCIONADO --}}
    <div class="modal fade" id="modalProdSelected" tabindex="-1" role="dialog" aria-labelledby="modalProdSelectedLabel" aria-hidden="true"></div>
    <div class="modal" tabindex="-1" role="dialog" id="modal-scanner">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">SCANNER</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div id="codigo-encontrado"></div>
                <div id="reader" style="width: 100%; text-align: center;"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
    </div>
</div>

@push('page_scripts')
    <script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>
    <script>

        var stores = {!! $warehouse !!};
        var tablas_stores = [];
        $(document).ready(function(){
            stores.map(store =>{
                var tabla = $('#tabla_products_'+store.id).DataTable({
                    ordering: true,
                    responsive: true,
                    'order': [1, 'asc'],
                    'language': {
                        "sProcessing":     "Procesando...",
                        "sLengthMenu":     "Mostrar _MENU_ registros",
                        "sZeroRecords":    "No se encontraron resultados",
                        "sEmptyTable":     "Ningún dato disponible en esta tabla",
                        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                        "sInfoPostFix":    "",
                        "sSearch":         "Buscar:",
                        "sUrl":            "",
                        "sInfoThousands":  ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst":    "Primero",
                            "sLast":     "Último",
                            "sNext":     "Siguiente",
                            "sPrevious": "Anterior"
                        },
                        "oAria": {
                            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        }
                    }
                });
                tablas_stores.push({id_store: store.id, table: tabla});
            });
        });

        function openModalInventario(id_product, id_store){
            
            var url = "{!! route('products.getInfo') !!}";
            $.ajax({
                url: url,
                method: "GET",
                data: {id_product: id_product, id_store: id_store}
            }).done(function(res){
                if(res){
                    $("#modalProdSelected").html(res);
                    $("#modalProdSelected").modal('show');
                }
            });
        }

        $(document).on('click', '#btnCerrarModalInv', function(){
            $("#modalProdSelected").modal('hide');
        });

        // function onScanSuccess(decodedText, decodedResult) {
        //     $('#codigo-encontrado').html(decodedText);
        //     console.log(`Code matched = ${decodedText}`);
        // }

        function onScanFailure(error) {
            console.warn(`Code scan error = ${error}`);
        }

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            /* handle success */
            playSound();
            $('#codigo-encontrado').html(decodedText);
            var tabla = tablas_stores.find(tabla => tabla.id_store == id_store_scanner_selected);
            if(tabla){
                $("#modal-scanner").modal('hide');
                console.log(tabla);
                $("#messagges").html(decodedText);
                tabla.table.search(decodedText).draw();
                $('#codigo-encontrado').html('  -  '+decodedText);
                
                stopScanner();
            }
            else{
                console.log('no se ha encontrado la tabla');
                $('#codigo-encontrado').append('  -  '+decodedText);
                $('#codigo-encontrado').append('  -  '+id_store_scanner_selected);
                $('#codigo-encontrado').append('  -  '+scanner_codigo_encontrado);
            }
            console.log(`Code matched = ${decodedText}`);
        };

        $('#modal-scanner').on('hide.bs.modal', function (e) {
            stopScanner();
        });

        // let html5QrcodeScanner = new Html5QrcodeScanner(
        //     "reader",
        //     { fps: 10, qrbox: {width: 250, height: 250} },
        //     /* verbose= */ false);
        
        // html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        const html5QrCode = new Html5Qrcode("reader");
        var id_store_scanner_selected = '';
        var scanner_codigo_encontrado = '';

        function openModalScanner(id_store){
            var config = { fps: 10, qrbox: { width: 250, height: 250 } };
            id_store_scanner_selected = id_store;
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
            $("#modal-scanner").modal('show');

        };

        function stopScanner(){
            html5QrCode.stop().then((ignore) => {
            // QR Code scanning is stopped.
            }).catch((err) => {
            // Stop failed, handle it.
            });
        }


        function playSound(hz = 1500) {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)(); // Crear contexto de audio
            const oscillator = audioContext.createOscillator(); // Crear oscilador

            oscillator.type = 'triangle'; // Tipo de onda: 'sine', 'square', 'sawtooth', 'triangle'
            oscillator.frequency.setValueAtTime(hz, audioContext.currentTime); // Frecuencia en Hz (A4 = 440 Hz)

            oscillator.connect(audioContext.destination); // Conectar el oscilador a los altavoces
            oscillator.start(); // Iniciar el oscilador

            // Detener el sonido después de 1 segundo
            setTimeout(() => {
                oscillator.stop();
            }, 400);
        }
    </script>
@endpush