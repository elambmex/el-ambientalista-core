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
            wp_enqueue_style(
                'eac-autor-comentarios-style',
                plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
                [],
                '1.0.0'
            );
        }
    }

    public function registrar_widget_elementor( $widgets_manager ) {
        $widget_file = plugin_dir_path( __FILE__ ) . 'widgets/widget-autor-comentarios.php';

        if ( file_exists( $widget_file ) ) {
            require_once $widget_file;
            if ( class_exists( 'EAC_Widget_Autor_Comentarios' ) ) {
                $widgets_manager->register( new EAC_Widget_Autor_Comentarios() );
            }
        }
    }
}

new EAC_Modulo_Comentarios_Autor();