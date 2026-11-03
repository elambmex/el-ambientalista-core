/*
 * Funcionalidad del Banner de Última Hora
 */
(function($) { // Usamos el modo seguro de jQuery
    $(document).ready(function() {
        
        var $banner = $('#bna-top-bar');
        
        // 1. Revisar si el usuario ya cerró el banner
        // Usamos localStorage para que recuerde la acción
        if (localStorage.getItem('bnaBannerClosed') === 'true') {
            $banner.hide();
            // También necesitamos ocultar el widget del shortcode si se cerró el banner
            $('.breaking-news-widget').hide();
        }

        // 2. Funcionalidad del botón de cerrar
        $banner.find('.bna-close').on('click', function() {
            // Ocultar con una animación suave
            $banner.slideUp(200);
            
            // Ocultar también cualquier widget de shortcode en la página
            $('.breaking-news-widget').slideUp(200);

            // Guardar la preferencia del usuario
            localStorage.setItem('bnaBannerClosed', 'true');
        });

    });
})(jQuery);