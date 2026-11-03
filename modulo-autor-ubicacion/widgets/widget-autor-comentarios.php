<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EAC_Widget_Autor_Comentarios extends \Elementor\Widget_Base {

    public function get_name() {
        return 'eac_autor_comentarios';
    }

    public function get_title() {
        return 'Comentarios del Autor';
    }

    public function get_icon() {
        return 'eicon-comments';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        // Controles de configuración del widget si se requieren
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Obtener el ID del autor actual si estamos en una página de archivo de autor
        $author_id = get_query_var( 'author' );

        if ( ! $author_id ) {
            echo '<p>Este widget debe usarse en una plantilla de archivo de autor.</p>';
            return;
        }

        echo '<div class="eac-autor-comentarios-wrapper">';
        echo '<h3>Historial de comentarios respondidos</h3>';
        // Aquí puedes implementar la consulta de comentarios del autor
        echo '</div>';
    }
}