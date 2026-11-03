<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class EAC_Widget_Comentarios extends \Elementor\Widget_Base {

    public function get_name() {
        return 'eac_editorial_comments';
    }

    public function get_title() {
        return 'Comentarios Editoriales (NYT)';
    }

    public function get_icon() {
        return 'eicon-comments';
    }

    public function get_categories() {
        return [ 'elambientalista', 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => 'Configuración del Bloque',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label'   => 'Título del encabezado',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'De los comentarios',
            ]
        );

        $this->add_control(
            'show_form',
            [
                'label'        => 'Mostrar formulario de participación',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Sí',
                'label_off'    => 'No',
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $GLOBALS['eac_comments_settings'] = [
            'title'     => $settings['title'],
            'show_form' => $settings['show_form'],
        ];

        include ARP_CORE_PATH . 'modulo-comentarios/templates/comments-template.php';

        unset( $GLOBALS['eac_comments_settings'] );
    }
}
