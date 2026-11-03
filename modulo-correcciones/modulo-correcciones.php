<?php
/*
Módulo: Correcciones de Entradas
*/

if (!defined('ABSPATH')) exit;

/**
 * Agregar el Metabox
 */
function cep_agregar_meta_box() {
    add_meta_box(
        'cep_correccion_meta_box',
        'Correcciones del artículo',
        'cep_mostrar_meta_box',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cep_agregar_meta_box');

/**
 * Mostrar el contenido del Metabox
 */
function cep_mostrar_meta_box($post) {
    $historial = get_post_meta($post->ID, '_cep_correcciones', true);
    if (!is_array($historial)) $historial = [];

    wp_nonce_field('cep_guardar_correccion', 'cep_nonce');

    echo '<p><label for="cep_nueva_correccion">Agrega una nueva corrección (se añadirá al historial):</label></p>';
    echo '<textarea style="width:100%; min-height:100px;" name="cep_nueva_correccion"></textarea>';

    echo '<hr><h4>Correcciones previas:</h4>';
    if (count($historial) > 0) {
        echo '<ul style="max-height: 300px; overflow-y:auto; background:#f9f9f9; padding: 10px; border: 1px solid #ddd;">';
        foreach ($historial as $item) {
            $fecha = date_i18n('M j, Y \a \l\a\s g:i a', $item['fecha']);
            echo '<li><strong>[' . esc_html($fecha) . ']</strong> ' . esc_html($item['texto']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No hay correcciones previas.</p>';
    }
}

/**
 * Guardar los datos del Metabox
 */
function cep_guardar_meta_box($post_id) {
    if (!isset($_POST['cep_nonce']) || !wp_verify_nonce($_POST['cep_nonce'], 'cep_guardar_correccion')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['cep_nueva_correccion']) && !empty(trim($_POST['cep_nueva_correccion']))) {
        $nueva_correccion = sanitize_textarea_field($_POST['cep_nueva_correccion']);
        
        $historial = get_post_meta($post_id, '_cep_correcciones', true);
        if (!is_array($historial)) $historial = [];

        // Añadir la nueva corrección al principio del historial
        array_unshift($historial, [
            'fecha' => current_time('timestamp'),
            'texto' => $nueva_correccion
        ]);

        update_post_meta($post_id, '_cep_correcciones', $historial);
    }
}
add_action('save_post', 'cep_guardar_meta_box');

/**
 * Mostrar las correcciones al final del contenido del post
 */
function cep_mostrar_correcciones_en_contenido($content) {
    if (is_singular('post') && in_the_loop() && is_main_query()) {
        $historial = get_post_meta(get_the_ID(), '_cep_correcciones', true);

        if (is_array($historial) && count($historial) > 0) {
            $html = '<div class="correccion-box"><h4>Correcciones</h4><ul>';
            foreach ($historial as $item) {
                $fecha = date_i18n('M j, Y', $item['fecha']);
                $html .= '<li><strong>Nota de corrección el ' . esc_html($fecha) . ':</strong> ' . wp_kses_post($item['texto']) . '</li>';
            }
            $html .= '</ul></div>';
            $content .= $html;
        }
    }
    return $content;
}
add_filter('the_content', 'cep_mostrar_correcciones_en_contenido');

/**
 * Encolar los estilos del módulo
 * Se cargan solo en las entradas (posts)
 */
function cep_enqueue_styles() {
    // Solo carga este CSS si estamos viendo un post singular
    if (is_singular('post')) {
        wp_enqueue_style(
            'cep-correcciones-style',
            ARP_CORE_URL . 'modulo-correcciones/assets/css/style.css',
            [],
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'cep_enqueue_styles');