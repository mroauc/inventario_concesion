<div class="table-responsive">
    <table class="table" id="vendedors-table">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Rut</th>
            <th>Telefono</th>
            <th>Ciudad</th>
            <th>Dirección</th>
            <th>Email</th>
            {{-- <th colspan="3">Acción</th> --}}
        </tr>
        </thead>
        <tbody>
        @foreach($representatives as $representative)
            <tr>
                <td>{{ $representative->name }}</td>
                <td>{{ $representative->rut }}</td>
                <td>{{ $representative->phone }}</td>
                <td>{{ $representative->city }}</td>
                <td>{{ $representative->address }}</td>
                <td>{{ $representative->email }}</td>
                <td width="120">
                    {!! Form::open(['route' => ['representative.destroy', $representative->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        {{-- <a href="{{ route('representative.show', [$representative->id]) }}"
                           class='btn btn-default btn-xs'>
                            <i class="far fa-eye"></i>
                        </a> --}}
                        <a href="{{ route('representative.edit', [$representative->id]) }}"
                           class='btn btn-default btn-xs'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Estas sseguro?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
