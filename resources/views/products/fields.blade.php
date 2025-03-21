<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Nombre:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! Form::label('description', 'Descripción:') !!}
    {!! Form::text('description', null, ['class' => 'form-control']) !!}
</div>

<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Código:') !!}
    {!! Form::text('code', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('id_category', 'Categoría:') !!}
    <select name="id_category" id="id_category">
        @foreach ($category_products as $category)
            <option value="{{$category->id}}" {{isset($product) && $product->id_category == $category->id ? 'selected' : ''}}>{{$category->name}}</option>
        @endforeach
    </select>
</div>

@push('page_scripts')
    <script>
        $("#id_category").selectize();
    </script>
@endpush