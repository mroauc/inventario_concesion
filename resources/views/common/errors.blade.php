@if ($errors->any())
    <div class="alert alert-danger">
        <strong>¡Ups! Algo salió mal.</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif