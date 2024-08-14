<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email') !!}
    {!! Form::email('email', null, ['class' => 'form-control']) !!}
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', 'Contraseña') !!}
    {!! Form::password('password', ['class' => 'form-control']) !!}
</div>

<!-- Confirmation Password Field -->
<div class="form-group col-sm-6">
      {!! Form::label('password', 'Confirmar Contraseña') !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('Concesiones', 'Concesiones') !!}
    @foreach ($concessions as $concession)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="concession[{{$concession->id}}]" id="checkbox{{$concession->id}}">
            <label class="form-check-label" for="concession[{{$concession->id}}]">{{$concession->name}}</label>
        </div>    
    @endforeach
    
    {{-- <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checkbox2" id="checkbox2">
        <label class="form-check-label" for="checkbox2">
        Opción 2
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checkbox3" id="checkbox3">
        <label class="form-check-label" for="checkbox3">
            Opción 3
        </label>
    </div> --}}
</div>

{{-- <!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Guardar', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('users.index') !!}" class="btn btn-default">Cancelar</a>
</div> --}}
