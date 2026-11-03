<?php
/**
 * Archivo: modules/modulo-ia-noticia/ia-manager.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class EA_IA_Manager {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_meta_data' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widget' ] );
    }

    // 1. Crear Meta Box en el Editor de Entradas
    public function add_meta_box() {
        add_meta_box(
            'ea_ia_meta_box',
            '🤖 Aviso de uso de IA',
            [ $this, 'render_meta_box' ],
            'post',
            'side',
            'high'
        );
    }

    // 2. Renderizar el HTML de la Meta Box
    public function render_meta_box( $post ) {
        $is_active = get_post_meta( $post->ID, '_ea_ia_active', true );
        $reason = get_post_meta( $post->ID, '_ea_ia_reason', true );
        ?>
        <p>
            <label>
                <input type="checkbox" name="ea_ia_active" value="1" <?php checked( $is_active, '1' ); ?> />
                Activar aviso "Esta entrada tiene IA"
            </label>
        </p>
        <p>
            <label for="ea_ia_reason">Motivo del uso:</label>
            <select name="ea_ia_reason" id="ea_ia_reason" style="width:100%">
                <option value="">-- Selecciona --</option>
                <option value="Mejora de redacción y estilo" <?php selected( $reason, 'Mejora de redacción y estilo' ); ?>>Mejora de redacción</option>
                <option value="Análisis de datos" <?php selected( $reason, 'Análisis de datos' ); ?>>Análisis de datos</option>
                <option value="Generación de imagen" <?php selected( $reason, 'Generación de imagen' ); ?>>Generación de imagen</option>
                <option value="Resumen de documento" <?php selected( $reason, 'Resumen de documento' ); ?>>Resumen de documento</option>
                <option value="Traducción asistida" <?php selected( $reason, 'Traducción asistida' ); ?>>Traducción asistida</option>
            </select>
        </p>
        <?php
    }

    // 3. Guardar datos
    public function save_meta_data( $post_id ) {
        if ( array_key_exists( 'ea_ia_active', $_POST ) ) {
            update_post_meta( $post_id, '_ea_ia_active', '1' );
        } else {
            update_post_meta( $post_id, '_ea_ia_active', '0' );
        }

        if ( array_key_exists( 'ea_ia_reason', $_POST ) ) {
            update_post_meta( $post_id, '_ea_ia_reason', sanitize_text_field( $_POST['ea_ia_reason'] ) );
        }
    }

    // 4. Registrar Widget de Elementor
    public function register_elementor_widget( $widgets_manager ) {
        require_once( __DIR__ . '/widgets/ia-widget.php' );
        $widgets_manager->register( new \EA_IA_Widget() );
    }
}

new EA_IA_Manager();