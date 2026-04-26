<div class="table-responsive">
    <table class="table table-hover" id="users-table">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th colspan="3">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                        @php
                            $badge = match($role->name) {
                                'super_admin'      => 'danger',
                                'administrador'    => 'primary',
                                'operador_servicio'=> 'info',
                                default            => 'secondary',
                            };
                            $label = match($role->name) {
                                'super_admin'      => 'Super Admin',
                                'administrador'    => 'Administrador',
                                'operador_servicio'=> 'Operador de Servicio',
                                default            => $role->name,
                            };
                        @endphp
                        <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                    @endforeach
                    @if($user->roles->isEmpty())
                        <span class="badge badge-warning">Sin rol</span>
                    @endif
                </td>
                <td>
                    {!! Form::open(['route' => ['users.destroy', $user->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <a href="{!! route('users.show', [$user->id]) !!}" class='btn btn-default btn-xs'>
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{!! route('users.edit', [$user->id]) !!}" class='btn btn-default btn-xs'>
                            <i class="fas fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('¿Eliminar este usuario?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
