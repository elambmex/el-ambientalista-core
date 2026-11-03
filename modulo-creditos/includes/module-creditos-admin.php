<?php
/**
 * Panel de administración para créditos multimedia
 */

if (!defined('ABSPATH')) exit;

class EAP_Credits_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'agregar_pagina_configuracion']);
    }

    public function agregar_pagina_configuracion() {
        add_options_page(
            'Créditos Multimedia',
            'Créditos Multimedia',
            'manage_options',
            'eap-creditos',
            [$this, 'pagina_configuracion_html']
        );
    }

    public function pagina_configuracion_html() {
        if (!current_user_can('manage_options')) return;

        if (isset($_POST['eap_guardar_creditos'])) {
            check_admin_referer('eap_guardar_creditos_action', 'eap_guardar_creditos_nonce');
            $autores = array_filter(array_map('sanitize_text_field', array_map('trim', explode("\n", $_POST['eap_autores']))));
            $medios = array_filter(array_map('sanitize_text_field', array_map('trim', explode("\n", $_POST['eap_medios']))));
            update_option('eap_credit_authors', array_values($autores));
            update_option('eap_credit_medios', array_values($medios));
            echo '<div class="updated"><p>Listas actualizadas correctamente.</p></div>';
        }

        $autores = get_option('eap_credit_authors', []);
        $medios = get_option('eap_credit_medios', []);
        ?>
        <div class="wrap">
            <h1>Configuración de Créditos Multimedia</h1>
            <form method="post">
                <?php wp_nonce_field('eap_guardar_creditos_action', 'eap_guardar_creditos_nonce'); ?>

                <h2>Autores personalizados</h2>
                <p>Escribe un autor por línea (se añadirán a los desplegables y serán sugeridos).</p>
                <textarea name="eap_autores" rows="6" style="width:100%;"><?php echo esc_textarea(implode("\n", $autores)); ?></textarea>

                <h2>Medios personalizados</h2>
                <p>Escribe un medio por línea. La lista puede ser extensa; aquí puedes administrarla.</p>
                <textarea name="eap_medios" rows="8" style="width:100%;"><?php echo esc_textarea(implode("\n", $medios)); ?></textarea>

                <p><input type="submit" name="eap_guardar_creditos" class="button button-primary" value="Guardar cambios"></p>
            </form>

            <h2>Nota</h2>
            <p>Si en el editor de Medios agregas un autor nuevo, el sistema intentará crear un usuario con rol <code>author</code>. Si necesitas más control sobre usuarios, editarlos o darles correo, hazlo desde <strong>Usuarios</strong>.</p>
        </div>
        <?php
    }
}

new EAP_Credits_Admin();