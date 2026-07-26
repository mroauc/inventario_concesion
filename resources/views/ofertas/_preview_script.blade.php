<script>
$(function () {
    // ponytail: URL.createObjectURL es nativo; no hace falta FileReader ni librerías.
    var MAX_FOTOS = 3;
    var MAX_MB    = 5;
    var $input    = $('#fotos');
    var $preview  = $('#preview-fotos');
    var $aviso    = $('#preview-aviso');
    var urls      = [];

    $input.on('change', function () {
        urls.forEach(URL.revokeObjectURL);   // liberar las miniaturas anteriores
        urls = [];
        $preview.empty();
        $aviso.addClass('d-none').empty();

        var archivos = Array.from(this.files || []);
        if (!archivos.length) {
            $('#fotos-actuales').css('opacity', '');
            return;
        }

        var errores = [];
        if (archivos.length > MAX_FOTOS) {
            errores.push('Seleccionaste ' + archivos.length + ' imágenes; el máximo es ' + MAX_FOTOS + '.');
        }

        archivos.slice(0, MAX_FOTOS).forEach(function (archivo, i) {
            if (!archivo.type.startsWith('image/')) {
                errores.push('"' + archivo.name + '" no es una imagen.');
                return;
            }
            var mb = archivo.size / 1048576;
            if (mb > MAX_MB) {
                errores.push('"' + archivo.name + '" pesa ' + mb.toFixed(1) + ' MB; el máximo es ' + MAX_MB + ' MB.');
                return;
            }

            var url = URL.createObjectURL(archivo);
            urls.push(url);

            $preview.append(
                $('<div>').css({position: 'relative', width: '120px'}).append(
                    $('<img>').attr({src: url, alt: archivo.name}).css({
                        width: '120px', height: '120px', objectFit: 'cover',
                        borderRadius: '6px', border: '1px solid #dee2e6'
                    }),
                    $('<span>').addClass('badge badge-dark').css({
                        position: 'absolute', top: '4px', left: '4px'
                    }).text(i + 1),
                    $('<small>').addClass('d-block text-muted text-truncate mt-1')
                        .attr('title', archivo.name)
                        .text(archivo.name + ' · ' + mb.toFixed(1) + ' MB')
                )
            );
        });

        if (errores.length) {
            $aviso.removeClass('d-none').html(errores.join('<br>'));
        }

        // Aviso visual de que las fotos actuales serán reemplazadas
        $('#fotos-actuales').css('opacity', $preview.children().length ? '.4' : '');
    });
});
</script>
