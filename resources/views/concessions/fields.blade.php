<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('representative', 'Representante:') !!}
    <select name="id_representative" id="id_representative">
        @foreach ($representatives as $representative)
            <option value="{{$representative->id}}">{{$representative->name}}</option>
        @endforeach
    </select>
</div>

@push('page_scripts')
    <script>
        $("#id_representative").selectize();
    </script>
@endpush