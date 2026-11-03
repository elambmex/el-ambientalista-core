<?php
/*
|--------------------------------------------------------------------------
| MÓDULO 11: Historial de Comentarios del Autor (Página de Autor)
|--------------------------------------------------------------------------
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EAC_Modulo_Comentarios_Autor {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'elementor/widgets/register', [ $this, 'registrar_widget_elementor' ] );
    }

    public function enqueue_assets() {
        if ( is_author() || is_singular() ) {
            // Validar que la constante exista para evitar errores fatales
            $base_url = defined( 'ARP_CORE_URL' ) ? ARP_CORE_URL : plugin_dir_url( __FILE__ );
            
            wp_enqueue_style(
                'eac-autor-comentarios-style',
                $base_url . 'assets/css/style.css',
                [],
                '1.0.0'
            );
        }
    }

    public function registrar_widget_elementor( $widgets_manager ) {
        $base_path = defined( 'ARP_CORE_PATH' ) ? ARP_CORE_PATH : plugin_dir_path( __FILE__ );
        $widget_file = $base_path . 'widgets/widget-autor-comentarios.php';

        if ( file_exists( $widget_file ) ) {
            require_once $widget_file;
            if ( class_exists( 'EAC_Widget_Autor_Comentarios' ) ) {
                $widgets_manager->register( new EAC_Widget_Autor_Comentarios() );
            }
        }
    }
}

new EAC_Modulo_Comentarios_Autor();