<?php
/**
 * Archivo: modules/modulo-ia-noticia/widgets/ia-widget.php
 * Versión: NYT Style + Multi-color Siri Animation
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class EA_IA_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'ea_ia_notice'; }
    public function get_title() { return 'Aviso IA (Estilo NYT)'; }
    public function get_icon() { return 'eicon-alert'; } // Icono cambiado a alerta
    public function get_categories() { return [ 'general' ]; }

    protected function register_controls() {

        // --- Contenido ---
        $this->start_controls_section(
            'section_content',
            [ 'label' => 'Contenido', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]
        );

        $this->add_control(
            'notice_label',
            [
                'label' => 'Etiqueta Principal',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'AVISO DE IA',
                'placeholder' => 'Ej. AVISO DE IA',
                'description' => 'Texto pequeño en mayúsculas tipo etiqueta.',
            ]
        );

        $this->add_control(
            'notice_text',
            [
                'label' => 'Texto del Aviso',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 2,
                'default' => 'Partes de este contenido fueron asistidas por inteligencia artificial.',
                'placeholder' => 'Escribe el texto del aviso',
            ]
        );

        $this->end_controls_section();

        // --- Estilo: Contenedor (Estilo NYT) ---
        $this->start_controls_section(
            'section_style_container',
            [ 'label' => 'Estilo del Contenedor (NYT)', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
        );

        $this->add_control(
            'container_bg_color',
            [
                'label' => 'Color de Fondo',
                'type' => \Elementor\Controls_Manager::COLOR,
                // Por defecto blanco para estilo NYT limpio
                'default' => '#ffffff',
                'selectors' => [ '{{WRAPPER}} .ea-ia-container' => 'background-color: {{VALUE}}' ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'label' => 'Borde',
                'selector' => '{{WRAPPER}} .ea-ia-container',
                // Configuración por defecto estilo NYT: borde fino gris claro
                'fields_options' => [
                    'border' => [ 'default' => 'solid' ],
                    'width'  => [ 'default' => [ 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true ] ],
                    'color'  => [ 'default' => '#e0e0e0' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => 'Relleno (Padding)',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default' => [ 'top' => '15', 'right' => '15', 'bottom' => '15', 'left' => '15', 'isLinked' => true ],
                'selectors' => [ '{{WRAPPER}} .ea-ia-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->end_controls_section();

        // --- Estilo: Animación Siri Multicolor ---
        $this->start_controls_section(
            'section_style_siri_multi',
            [ 'label' => 'Animación IA Multicolor', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
        );
        
        $this->add_control(
            'siri_description',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<small>Selecciona 3 colores para crear el efecto de mezcla giratoria.</small>',
                'content_classes' => 'elementor-descriptor',
            ]
        );

        $this->add_control(
            'siri_color_1',
            [
                'label' => 'Color 1 (Inicio)',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff00cc', // Magenta
            ]
        );

        $this->add_control(
            'siri_color_2',
            [
                'label' => 'Color 2 (Medio)',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#00d2ff', // Cian
            ]
        );
        
         $this->add_control(
            'siri_color_3',
            [
                'label' => 'Color 3 (Final)',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#fcfc00', // Amarillo
            ]
        );

        $this->end_controls_section();

        // --- Estilo: Tipografía ---
        $this->start_controls_section(
            'section_style_typo',
            [ 'label' => 'Tipografía', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
        );

        // Estilo para la etiqueta pequeña (ej. AVISO IA)
        $this->add_control(
            'heading_label_style',
            [
                'label' => 'Estilo Etiqueta',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => 'Color Etiqueta',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#777777', // Gris estilo NYT
                'selectors' => [ '{{WRAPPER}} .ea-ia-label' => 'color: {{VALUE}}' ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => 'Tipografía Etiqueta',
                'selector' => '{{WRAPPER}} .ea-ia-label',
                'fields_options' => [
                    'typography' => [ 'default' => 'custom' ],
                    'font_weight' => [ 'default' => '700' ],
                    'font_size' => [ 'default' => [ 'size' => 11, 'unit' => 'px' ] ],
                    'text_transform' => [ 'default' => 'uppercase' ],
                    'letter_spacing' => [ 'default' => [ 'size' => 1, 'unit' => 'px' ] ],
                ],
            ]
        );

        // Estilo para el texto principal
        $this->add_control(
            'heading_text_style',
            [
                'label' => 'Estilo Texto Principal',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => 'Color de Texto',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#121212', // Casi negro NYT
                'selectors' => [ '{{WRAPPER}} .ea-ia-main-text, {{WRAPPER}} .ea-ia-details' => 'color: {{VALUE}}' ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => 'Tipografía Texto',
                'selector' => '{{WRAPPER}} .ea-ia-main-text',
                'fields_options' => [
                     'typography' => [ 'default' => 'custom' ],
                     // Idealmente una serif para NYT style, pero sans-serif es más seguro si no tienen fuentes cargadas
                     'font_size' => [ 'size' => 15, 'unit' => 'px' ],
                     'line_height' => [ 'size' => 1.4, 'unit' => 'em' ],
                ]
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $post_id = get_the_ID();
        $is_active = get_post_meta( $post_id, '_ea_ia_active', true );
        $reason = get_post_meta( $post_id, '_ea_ia_reason', true );

        // Lógica de visualización en editor vs frontend
        if ( $is_active !== '1' && ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return;
        }

        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() && $is_active !== '1' ) {
            $reason = "Modo Vista Previa (Activa la opción en la entrada para ver el motivo real)";
        }

        // Colores para el gradiente cónico
        $c1 = $settings['siri_color_1'];
        $c2 = $settings['siri_color_2'];
        $c3 = $settings['siri_color_3'];

        // Generamos el CSS del gradiente cónico.
        // Se repite c1 al final para que el giro sea suave sin cortes.
        $gradient_css = "conic-gradient(from 0deg, $c1, $c2, $c3, $c1)";

        ?>
        <style>
            /* Estructura Principal estilo NYT */
            .ea-ia-container {
                display: flex;
                flex-direction: column;
                /* Bordes y colores definidos en los controles de Elementor */
                transition: all 0.3s ease;
                cursor: pointer;
                position: relative;
                overflow: hidden;
                /* Estilo cuadrado, sin border-radius por defecto */
            }
            
            .ea-ia-header {
                display: flex;
                align-items: center;
                width: 100%;
            }

            /* --- Nueva Animación Multicolor "Siri" --- */
            /* Usamos un wrapper para contener el efecto de desenfoque (blur) */
            .ea-siri-wrapper {
                width: 24px;
                height: 24px;
                flex-shrink: 0;
                margin-right: 12px;
                position: relative;
                border-radius: 50%;
                overflow: hidden; /* Necesario para que el blur no se salga del círculo */
                 /* Sutil sombra estática para dar profundidad */
                 box-shadow: inset 0 0 4px rgba(0,0,0,0.1);
            }

            /* La esfera que gira internamente */
            .ea-siri-orb-multi {
                position: absolute;
                top: -50%; left: -50%;
                width: 200%;
                height: 200%;
                /* El gradiente cónico inyectado por PHP */
                background: <?php echo $gradient_css; ?>;
                /* Desenfoque alto para mezclar los colores y parecer "energía" */
                filter: blur(6px); 
                animation: ea-siri-spin 3s linear infinite;
            }

            @keyframes ea-siri-spin {
                100% { transform: rotate(360deg); }
            }
            /* ------------------------------------ */

            /* Textos Estilo NYT */
            .ea-ia-content-col {
                display: flex;
                flex-direction: column;
            }

            .ea-ia-label {
                /* Tipografía definida en controles */
                margin-bottom: 4px;
                display: block;
            }

            .ea-ia-main-text {
                /* Tipografía definida en controles */
                margin: 0;
            }

            /* Sección Desplegable Estilo NYT */
            .ea-ia-details {
                max-height: 0;
                opacity: 0;
                overflow: hidden;
                transition: max-height 0.5s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.4s ease; /* Animación más suave */
                
                /* Diseño limpio para los detalles */
                margin-top: 0;
                padding-top: 0;
                border-top: 1px solid transparent;
                
                font-family: sans-serif; /* Asegurar legibilidad en detalles */
                font-size: 13px;
                line-height: 1.5;
            }

            /* Estado Activo (Desplegado) */
            .ea-ia-container.active .ea-ia-details {
                max-height: 100px; /* Altura suficiente */
                opacity: 1;
                /* Añadir separación y línea superior sutil al desplegar */
                margin-top: 12px;
                padding-top: 12px;
                border-top-color: #e0e0e0; /* Color de línea sutil */
            }

            .ea-ia-reason-row {
                margin-bottom: 6px;
                display: flex;
            }

            .ea-ia-reason-label {
                font-weight: 700;
                margin-right: 5px;
                color: #333;
            }

            .ea-ia-link {
                font-size: 12px;
                text-decoration: none;
                color: #000; /* Enlace negro estilo periódico */
                border-bottom: 1px solid #ccc; /* Subrayado sutil */
                transition: all 0.2s;
                display: inline-block;
                margin-top: 4px;
            }
            .ea-ia-link:hover {
                border-bottom-color: #000;
                background-color: #f5f5f5;
            }
            
            /* Icono de flecha pequeño para indicar despliegue */
            .ea-toggle-icon {
                margin-left: auto;
                font-size: 12px;
                color: #999;
                transition: transform 0.3s ease;
            }
            .ea-ia-container.active .ea-toggle-icon {
                transform: rotate(180deg);
            }

        </style>

        <div class="ea-ia-container <?php echo (\Elementor\Plugin::$instance->editor->is_edit_mode()) ? 'active' : ''; ?>" onclick="this.classList.toggle('active')">
            
            <div class="ea-ia-header">
                <div class="ea-siri-wrapper">
                    <div class="ea-siri-orb-multi"></div>
                </div>
                
                <div class="ea-ia-content-col">
                    <?php if ( ! empty( $settings['notice_label'] ) ) : ?>
                        <span class="ea-ia-label"><?php echo esc_html( $settings['notice_label'] ); ?></span>
                    <?php endif; ?>
                    <p class="ea-ia-main-text"><?php echo esc_html( $settings['notice_text'] ); ?></p>
                </div>
                 <i class="ea-toggle-icon eicon-chevron-down"></i>
            </div>
            
            <div class="ea-ia-details">
                <div class="ea-ia-reason-row">
                    <span class="ea-ia-reason-label">Transparencia:</span>
                    <span>Se utilizó IA para: <?php echo esc_html( $reason ); ?>.</span>
                </div>
                <div>
                     <a href="https://elambmex.com/politica-IA" target="_blank" class="ea-ia-link">Leer nuestra Política de IA</a>
                </div>
            </div>
        </div>

        <?php
    }
}
?>