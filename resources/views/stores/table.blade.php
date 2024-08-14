<div class="table-responsive">
    <table class="table" id="stores-table">
        <thead>
        <tr>
            <th>Name</th>
        <th>Address</th>
            <th colspan="3">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($stores as $store)
            <tr>
                <td>{{ $store->name }}</td>
            <td>{{ $store->address }}</td>
                <td width="120">
                    {!! Form::open(['route' => ['stores.destroy', $store->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <a href="{{ route('stores.show', [$store->id]) }}"
                           class='btn btn-default btn-xs'>
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('stores.edit', [$store->id]) }}"
                           class='btn btn-default btn-xs'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
