@push('page_css')
    <style>
        #tabla_products td{
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
    @foreach ($warehouse as $key => $store)
        <div class="card">
            <div class="card-body col-sm-12" style="overflow: auto;">
                <h5 class="h4">{{$store->name}}</h5>
                <table class="table table-sm table-bordered mt-4 tablaData" id="tabla_products" style="width: 100%; overflow: auto;">
                    <thead>
                        <tr style="background-color: #efefef">
                            {{-- <th>.</th> --}}
                            <th>Código</th>
                            <th>Producto</th>
                            {{-- <th>Descripción</th> --}}
                            <th>Stock</th>
                            <th>Posición</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($store->products as $product)
                            <tr class="fila_prod" onclick="openModalInventario({{$product->id}}, {{$store->id}})">
                                {{-- <td class="td_product"><input type="checkbox" name="products[{{$product->id}}][check]" id="prod{{$product->id}}" {{isset($store) && $store->products()->where('id_product', $product->id)->exists() ? 'checked' : ''}}></td> --}}
                                <td class="td_product">{{$product->code}}</td>
                                <td class="td_product">{{$product->name}}</td>
                                {{-- <td class="td_product">{{$product->description}}</td> --}}
                                {{-- <td class="td_product" onclick="check_td_product({{$product->id}})"><input type="number" name="products[{{$product->id}}][stock]" value="{{isset($store) && isset($store->products()->where('id_product', $product->id)->first()->pivot) ? $store->products()->where('id_product', $product->id)->first()->pivot->stock : ''}}" id="input_stock_prod{{$product->id}}"></td> --}}
                                <td class="td_product">{{$product->pivot->stock}}</td>
                                {{-- <td class="td_product">{{}}</td> --}}
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
</div>

@push('page_scripts')
    <script>
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
    </script>
@endpush