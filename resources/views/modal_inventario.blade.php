<form id="form-modal-product" method="POST" action="{{route('representative.storeModal')}}">
@csrf
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="modalProdSelectedLabel">{{$product->name}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-6"><label>Producto:</label></div>
                <div class="col-6"><span>{{$product->name}}</span></div>
            </div>
            <div class="row">
                <div class="col-6"><label>Categoría:</label></div>
                <div class="col-6"><span>{{$product->category}}</span></div>
            </div>
            {{-- <div class="row">
                <div class="col-6"><label>Descripción:</label></div>
                <div class="col-6"><span>{{$product->description}}</span></div>
            </div> --}}
            <div class="row">
                <div class="col-6"><label>Posición:</label></div>
                <div class="col-6"><span>{{$product->position}}</span></div>
            </div>
            <div class="row">
                <div class="col-6"><label>Stock:</label></div>
                <div class="col-6"><span>{{$product->stock}}</span></div>
            </div>
            <div class="row" style="align-items: center; justify-content: center;">
                <button type="button" class="btn btn-success" onclick="restarStock()"><i class="fas fa-minus"></i></button>
                <input type="number" name="stock_product" class="form-control col-6" style="display: inline-block;" value="{{$product->stock}}" id="stock_product_modal">
                <button type="button" class="btn btn-success"  onclick="sumarStock()"><i class="fas fa-plus"></i></button>
            </div>
            <input type="text" style="display: none;" name="id_store" value="{{$product->store}}">
            <input type="text" style="display: none;" name="id_product" value="{{$product->id}}">
            {{-- <table class="table table-sm filtroTabla" id="tabla_products">
                <thead>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Posición</th>
                    <th>Stock</th>
                </thead>
                <tbody>
                    <tr>
                        <td class="td_product">{{$product->name}}</td>
                        <td class="td_product">{{$product->description}}</td>
                        <td class="td_product">{{$product->position}}</td>
                        <td class="td_product"><button class="btn btn-success">+</button><input type="number" name="stock_product" value="{{$product->stock}}"></td>
                        <input type="text" style="display: none;" name="id_store" value="{{$product->store}}">
                        <input type="text" style="display: none;" name="id_product" value="{{$product->id}}">
                    </tr>
                </tbody>
            </table> --}}
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-secondary" id="btnCerrarModalInv">Cerrar</button>
        </div>
    </div>
</div>
</form>

<script>
    function restarStock(){
        var valor_actual = parseInt($("#stock_product_modal").val());
        $("#stock_product_modal").val(valor_actual-1);
    }

    function sumarStock(){
        var valor_actual = parseInt($("#stock_product_modal").val());
        $("#stock_product_modal").val(valor_actual+1);
    }
</script>