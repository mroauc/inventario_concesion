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
    {!! Form::password('password', ['class' => 'form-control', 'autocomplete' => 'new-password']) !!}
    @isset($user)
        <small class="text-muted">Dejar en blanco para mantener la contraseña actual.</small>
    @endisset
</div>

<!-- Confirmation Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', 'Confirmar Contraseña') !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
</div>

<!-- Rol Field -->
<div class="form-group col-sm-6">
    {!! Form::label('role', 'Rol') !!}
    <select name="role" class="form-control">
        @foreach($roles as $role)
            @php
                $label = match($role) {
                    'super_admin'      => 'Super Admin',
                    'administrador'    => 'Administrador',
                    'operador_servicio'=> 'Operador de Servicio',
                    default            => $role,
                };
                $selected = isset($user) && $user->hasRole($role) ? 'selected' : '';
            @endphp
            <option value="{{ $role }}" {{ $selected }}>{{ $label }}</option>
        @endforeach
    </select>
</div>

<!-- Concesiones Field -->
<div class="form-group col-sm-6">
    {!! Form::label('concesion', 'Concesión') !!}
    @foreach ($concessions as $concession)
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
                   name="concession[{{ $concession->id }}]"
                   id="checkbox{{ $concession->id }}"
                   {{ isset($user) && $user->id_concession == $concession->id ? 'checked' : '' }}>
            <label class="form-check-label" for="checkbox{{ $concession->id }}">
                {{ $concession->name }}
            </label>
        </div>
    @endforeach
</div>
