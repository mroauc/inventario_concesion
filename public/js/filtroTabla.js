// const tabla = document.getElementById('miTabla');
$(document).ready(function() {
  // Seleccionamos el input que usaremos para filtrar
  let filtro = $('.inputFiltroTabla');

  // Seleccionamos la tabla a la que aplicaremos el filtro
  let tabla = $('.filtroTabla');

  // Obtenemos todas las filas de la tabla, excepto la primera (que es la cabecera)
  let filas = $('tr', tabla).slice(1);

  // Agregamos un evento al input para que se actualice el filtro al escribir
  filtro.on('keyup', function() {
    // Convertimos a minúsculas el valor del input para hacer una comparación más precisa
    let valorFiltro = filtro.val().toLowerCase();

    // Recorremos las filas de la tabla
    filas.each(function() {
      // Seleccionamos la celda que queremos filtrar (en este ejemplo, la primera)
      let celda = $(this).children('td').eq(0);

      // Obtenemos el texto de la celda y lo convertimos a minúsculas para hacer la comparación
      let textoCelda = celda.text().toLowerCase();

      // Verificamos si el texto de la celda incluye el valor del filtro
      if (textoCelda.includes(valorFiltro)) {
        // Si el texto de la celda incluye el valor del filtro, mostramos la fila
        $(this).show();
      } else {
        // Si el texto de la celda no incluye el valor del filtro, ocultamos la fila
        $(this).hide();
      }
    });
  });
});