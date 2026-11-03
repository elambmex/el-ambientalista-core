jQuery(document).ready(function($){
    // Inicializar select2 en selects creados en attachment_fields_to_edit
    function initSelects(context){
        $('.eap-select2', context).each(function(){
            if (!$(this).data('select2')) {
                $(this).select2({
                    width: '100%',
                    placeholder: 'Buscar o seleccionar',
                    allowClear: true
                }).on('change', function(){
                    var val = $(this).val();
                    var custom = $(this).next('.eap-custom-input');
                    if (val === '__nuevo__') {
                        custom.show().focus();
                    } else {
                        custom.hide();
                        custom.val('');
                    }
                });
            }
        });

        // Mostrar custom inputs si tienen valor
        $('.eap-custom-input', context).each(function(){
            if ($(this).val() !== '') $(this).show();
        });
    }

    // Inicial al cargar (para la pantalla de Biblioteca / modal)
    initSelects(document);

    // También inicial cuando el modal de edición de medios se carga dinámicamente
    $(document).ajaxComplete(function(event, xhr, settings){
        initSelects(document);
    });
});