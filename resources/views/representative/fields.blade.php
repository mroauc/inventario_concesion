<div class="form-group col-sm-6">
    {!! Form::label('name', 'Nombre:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- RUT Field -->
<div class="form-group col-sm-6">
    {!! Form::label('rut', 'RUT:') !!}
    {!! Form::text('rut', null, ['class' => 'form-control']) !!}
</div>

<!-- RUT Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Telefono:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
</div>

<!-- Ciudad Field -->
<div class="form-group col-sm-6">
    {!! Form::label('city', 'Ciudad:') !!}
    {!! Form::text('city', null, ['class' => 'form-control']) !!}
</div>

<!-- Direccion Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Dirección:') !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    <input type="email" name="email" id="email" class="form-control" value="{{$representative->email ?? ''}}">
</div>