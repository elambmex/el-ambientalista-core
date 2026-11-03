// filtro.js
jQuery(document).ready(function($) {
    flatpickr("#elamb-fecha-inicio", { dateFormat: "Y-m-d" });
    flatpickr("#elamb-fecha-fin", { dateFormat: "Y-m-d" });

    $('#elamb-filtrar').on('click', function() {
        const data = {
            action: 'elamb_filtrar',
            orden: $('#elamb-orden').val(),
            categoria: $('#elamb-categoria').val(),
            fecha_inicio: $('#elamb-fecha-inicio').val(),
            fecha_fin: $('#elamb-fecha-fin').val()
        };

        $('#elamb-resultados').html('<p>Cargando...</p>');

        $.post(elamb_ajax.ajax_url, data, function(response) {
            $('#elamb-resultados').html(response);
        });
    });
});