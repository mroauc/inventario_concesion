<script>
$(function () {
    // ponytail: una casilla = un input. Sin librerías; createObjectURL es nativo.
    var MAX_MB = 5;
    var urls   = {};   // slot -> objectURL, para liberarlos al reemplazar

    function aviso(msg) {
        var $a = $('#preview-aviso');
        msg ? $a.removeClass('d-none').text(msg) : $a.addClass('d-none').empty();
    }

    function vaciar($slot) {
        $slot.removeClass('foto-slot--llena');
        $slot.find('.foto-slot__img').attr('src', '').prop('hidden', true);
        $slot.find('.foto-slot__vacio').prop('hidden', false);
        $slot.find('.foto-slot__quitar').prop('hidden', true);
        $slot.find('.foto-slot__nombre').empty();
    }

    function liberar(slot) {
        if (urls[slot]) {
            URL.revokeObjectURL(urls[slot]);
            delete urls[slot];
        }
    }

    $('.foto-slot__input').on('change', function () {
        var $slot   = $(this).closest('.foto-slot');
        var slot    = $slot.data('slot');
        var archivo = this.files && this.files[0];

        aviso('');
        liberar(slot);

        if (!archivo) { return; }   // el usuario canceló el diálogo

        if (!archivo.type.startsWith('image/')) {
            aviso('"' + archivo.name + '" no es una imagen.');
            this.value = '';
            return;
        }

        var mb = archivo.size / 1048576;
        if (mb > MAX_MB) {
            aviso('"' + archivo.name + '" pesa ' + mb.toFixed(1) + ' MB; el máximo es ' + MAX_MB + ' MB.');
            this.value = '';
            return;
        }

        urls[slot] = URL.createObjectURL(archivo);

        $slot.addClass('foto-slot--llena');
        $slot.find('.foto-slot__img').attr('src', urls[slot]).prop('hidden', false);
        $slot.find('.foto-slot__vacio').prop('hidden', true);
        $slot.find('.foto-slot__quitar').prop('hidden', false);
        $slot.find('.foto-slot__nombre').text(archivo.name + ' · ' + mb.toFixed(1) + ' MB');

        // Elegir un archivo anula un borrado pendiente de esta misma casilla
        $slot.find('.foto-slot__eliminar').val('0');
    });

    $('.foto-slot__quitar').on('click', function () {
        var $slot = $(this).closest('.foto-slot');
        var slot  = $slot.data('slot');

        liberar(slot);
        $slot.find('.foto-slot__input').val('');
        // Marca el borrado para el backend: sirve tanto para una foto guardada
        // como para una recién elegida (en ese caso el flag es inofensivo).
        $slot.find('.foto-slot__eliminar').val('1');
        vaciar($slot);
        aviso('');
    });
});
</script>
