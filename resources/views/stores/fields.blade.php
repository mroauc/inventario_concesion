@push('page_css')
    <style>
        #tabla_products{
            cursor: pointer;
        }
    </style>
@endpush
<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Nombre:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Dirección:') !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<h5>Productos</h5>
<div class="form-group col-sm-12">
    <input type="text" class="inputFiltroTabla" id="filtro">
    <table class="table table-sm filtroTabla" id="tabla_products">
        <thead>
            <th>.</th>
            <th>Producto</th>
            <th>Descripción</th>
            <th>Stock</th>
            <th>Posición</th>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td class="td_product"><input type="checkbox" name="products[{{$product->id}}][check]" id="prod{{$product->id}}" {{isset($store) && $store->products()->where('id_product', $product->id)->exists() ? 'checked' : ''}}></td>
                    <td class="td_product" onclick="check_td_product({{$product->id}})">{{$product->name}}</td>
                    <td class="td_product" onclick="check_td_product({{$product->id}})">{{$product->description}}</td>
                    {{-- <td class="td_product" onclick="check_td_product({{$product->id}})"><input type="number" name="products[{{$product->id}}][stock]" value="{{isset($store) && isset($store->products()->where('id_product', $product->id)->first()->pivot) ? $store->products()->where('id_product', $product->id)->first()->pivot->stock : ''}}" id="input_stock_prod{{$product->id}}"></td> --}}
                    {{-- <td class="td_product"><input type="number" name="products[{{$product->id}}][stock]" value="{{$stock_products->where('id_product', $product->id)->first()->stock ?? ''}}"></td> --}}
                    <td style="width: 15%" class="td_product" onclick="check_td_product({{$product->id}})"><input type="number" name="products[{{$product->id}}][stock]" id="input_stock_prod{{$product->id}}" value="{{$stock_products->where('id_product', $product->id)->first()->stock ?? ''}}"></td>
                    <td style="width: 15%" class="td_product" onclick="check_td_product({{$product->id}})"><input type="text" name="products[{{$product->id}}][positions][]" id="input_position_prod{{$product->id}}" value="{{$stock_products->where('id_product', $product->id)->first() ? $stock_products->where('id_product', $product->id)->first()->positions()->first()->position ?? '' : ''}}"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('page_scripts')
    <script>
        $('.td_product').hover(function(){
            $(this).closest('tr').css('background-color', 'rgba(209, 209, 209, 0.397)');
        }, function(){
            $(this).closest('tr').css('background-color', 'white');
        });

        check_td_product = (id) => {
            // SI NO ESTAN LOS INPUTS CON FOCUS ENTONCES SE CAMBIA EL ESTADO DEL CHECK
            if($('#prod'+id).is(':checked')){
                if(!$("#input_stock_prod"+id).is(':focus') && !$("#input_position_prod"+id).is(':focus')){
                    $('#prod'+id).prop('checked', false);
                    $('#prod'+id).trigger('change');
                }
            }
            else{
                $('#prod'+id).prop('checked', true);
                $('#prod'+id).trigger('change');
            }
        }
    </script>
@endpush

