// ── Alta rápida de cliente ──────────────────────────────────────────────────
const $modalCliente = $('#modal-nuevo-cliente');
const $formCliente = $('#form-nuevo-cliente');
const $guardarCliente = $('#btn-guardar-cliente');

function limpiarErroresCliente() {
    $formCliente.find('.is-invalid').removeClass('is-invalid');
    $formCliente.find('.invalid-feedback').text('');
    $('#nuevo-cliente-error').addClass('d-none').empty();
}

function mostrarErroresCliente(errors) {
    limpiarErroresCliente();
    let primero = null;

    Object.keys(errors || {}).forEach(function (campo) {
        const $input = $formCliente.find('[name="' + campo + '"]');
        if (!$input.length) return;
        $input.addClass('is-invalid');
        $input.siblings('.invalid-feedback').text(errors[campo][0]);
        primero = primero || $input;
    });

    if (primero) {
        primero.trigger('focus');
    } else {
        $('#nuevo-cliente-error')
            .removeClass('d-none')
            .text('No fue posible crear el cliente. Revisa los datos e inténtalo nuevamente.');
    }
}

$modalCliente.on('shown.bs.modal', function () {
    $('#nuevo-cliente-nombre').trigger('focus');
});

$modalCliente.on('hidden.bs.modal', function () {
    $formCliente[0].reset();
    limpiarErroresCliente();
    $('#nuevo-cliente-opcionales').collapse('hide');
});

$formCliente.on('submit', function (event) {
    event.preventDefault();
    limpiarErroresCliente();

    if (!$formCliente[0].checkValidity()) {
        $formCliente.find(':invalid').each(function () {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Este campo es obligatorio.');
        }).first().trigger('focus');
        return;
    }

    const textoOriginal = $guardarCliente.find('span').text();
    $guardarCliente.prop('disabled', true)
        .find('i').attr('class', 'fas fa-spinner fa-spin mr-1');
    $guardarCliente.find('span').text('Creando...');

    $.ajax({
        url: @json(route('ordenes_servicio.clientes.store')),
        method: 'POST',
        data: $formCliente.serialize(),
        dataType: 'json'
    }).done(function (response) {
        const cliente = response.cliente;
        const etiqueta = (cliente.rut ? cliente.rut + ' – ' : '') + cliente.nombre;
        const opcion = new Option(etiqueta, cliente.id, true, true);

        $('#cliente_id').append(opcion).trigger('change');
        $modalCliente.modal('hide');

        $(document).Toasts('create', {
            class: 'bg-success',
            title: 'Cliente creado',
            body: 'El cliente quedó seleccionado en la orden.',
            autohide: true,
            delay: 3500
        });
    }).fail(function (xhr) {
        mostrarErroresCliente(xhr.status === 422 ? xhr.responseJSON.errors : null);
    }).always(function () {
        $guardarCliente.prop('disabled', false)
            .find('i').attr('class', 'fas fa-save mr-1');
        $guardarCliente.find('span').text(textoOriginal);
    });
});
