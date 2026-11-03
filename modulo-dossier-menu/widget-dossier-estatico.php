<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class El_Ambientalista_Widget_Dossier_Estatico extends Widget_Base {

    public function get_name() { return 'elamb_dossier_menu'; }
    public function get_title() { return 'Menú Dossier Histórico (NYT Style)'; }
    public function get_icon() { return 'eicon-archive'; }
    public function get_categories() { return [ 'general' ]; }

    protected function _register_controls() {
        
        // ==========================================
        // SECCIÓN: CONTENIDO
        // ==========================================
        $this->start_controls_section('section_info', [
            'label' => 'Configuración de Dossier',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        
        $this->add_control('site_logo', [
            'label' => 'Ícono Institucional (T del NYT o Logo)',
            'type' => Controls_Manager::MEDIA,
            'description' => 'Sube un ícono pequeño para mostrar a la izquierda del tema.'
        ]);

        $this->add_control('preview_mode', [
            'label' => 'Modo Previsualizador (Editor)',
            'type' => Controls_Manager::SWITCHER,
            'label_on' => 'Sí',
            'label_off' => 'No',
            'return_value' => 'yes',
            'default' => 'yes',
            'description' => 'Muestra datos de prueba en Elementor para facilitar el diseño visual si la entrada actual no tiene un dossier asignado.',
        ]);

        $this->end_controls_section();

        // ==========================================
        // SECCIÓN: ESTILOS - BARRA GENERAL
        // ==========================================
        $this->start_controls_section('section_style_bar', [
            'label' => 'Barra y Fondo',
            'tab' => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('bar_bg_color', [
            'label' => 'Color de Fondo',
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elamb-dossier-nav-wrapper' => 'background-color: {{VALUE}};',
            ],
            'default' => '#ffffff',
        ]);

        $this->add_control('bar_border_color', [
            'label' => 'Color de Bordes',
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elamb-dossier-nav-wrapper' => 'border-top-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
                '{{WRAPPER}} .elamb-dossier-brand-area::after' => 'background-color: {{VALUE}};',
            ],
            'default' => '#e2e2e2',
        ]);

        $this->end_controls_section();

        // ==========================================
        // SECCIÓN: ESTILOS - TIPOGRAFÍA Y COLORES
        // ==========================================
        $this->start_controls_section('section_style_typography', [
            'label' => 'Textos y Enlaces',
            'tab' => Controls_Manager::TAB_STYLE,
        ]);

        // --- Título del Dossier ---
        $this->add_control('heading_title_style', [
            'label' => 'Título 1: Principal (Hub)',
            'type' => Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_control('title_color', [
            'label' => 'Color del Título',
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elamb-dossier-title' => 'color: {{VALUE}};',
            ],
            'default' => '#111111',
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name' => 'title_typography',
            'selector' => '{{WRAPPER}} .elamb-dossier-title',
        ]);

        // --- Enlaces ---
        $this->add_control('heading_links_style', [
            'label' => 'Título 2: Enlaces de Navegación',
            'type' => Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name' => 'links_typography',
            'selector' => '{{WRAPPER}} .elamb-dossier-links a',
        ]);

        $this->start_controls_tabs('tabs_links_style');

        // Estado Normal
        $this->start_controls_tab('tab_link_normal', ['label' => 'Normal']);
        $this->add_control('link_color', [
            'label' => 'Color del Enlace',
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elamb-dossier-links a' => 'color: {{VALUE}};',
            ],
            'default' => '#555555',
        ]);
        $this->end_controls_tab();

        // Estado Hover
        $this->start_controls_tab('tab_link_hover', ['label' => 'Hover']);
        $this->add_control('link_hover_color', [
            'label' => 'Color Hover',
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elamb-dossier-links a:hover' => 'color: {{VALUE}};',
            ],
            'default' => '#111111',
        ]);
        $this->end_controls_tab();

        // Estado Activo
        $this->start_controls_tab('tab_link_active', ['label' => 'Activo']);
        $this->add_control('link_active_color', [
            'label' => 'Color Activo',
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elamb-dossier-links a.elamb-nav-active' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
            ],
            'default' => '#111111',
        ]);
        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $logo_url = !empty($settings['site_logo']['url']) ? $settings['site_logo']['url'] : '';
        $current_id = get_queried_object_id();
        $is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        
        // 1. LÓGICA PADRE/HIJITO
        $rol = get_post_meta($current_id, '_elamb_dossier_rol', true);
        $padre_id = ($rol === 'padre') ? $current_id : get_post_meta($current_id, '_elamb_dossier_padre_id', true);

        $titulo_hub = '';
        $notas_a_mostrar = [];

        if (!empty($padre_id)) {
            $padre_post = get_post($padre_id);
            if ($padre_post) {
                // TÍTULO 1 (Hub): Extraído del Padre
                $titulo_hub = get_post_meta($padre_id, '_elamb_dossier_hub_title', true);
                
                // TÍTULO 2 (Pestaña del Padre): Extraído del Padre
                $titulo_2_padre = get_post_meta($padre_id, '_elb_short_menu_title', true);
                if (empty($titulo_2_padre)) $titulo_2_padre = wp_trim_words($padre_post->post_title, 4, '');
                
                $notas_a_mostrar[] = [
                    'id' => $padre_id,
                    'title' => $titulo_2_padre,
                    'url' => get_permalink($padre_id)
                ];
            }

            // Buscar hijitos
            $hijitos = get_posts([
                'post_type' => ['post', 'page'],
                'meta_key' => '_elamb_dossier_padre_id',
                'meta_value' => $padre_id,
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'ASC'
            ]);

            foreach ($hijitos as $hijo) {
                // TÍTULO 2 (Pestaña de cada Hijito)
                $t2_hijo = get_post_meta($hijo->ID, '_elb_short_menu_title', true);
                if (empty($t2_hijo)) $t2_hijo = wp_trim_words($hijo->post_title, 4, '');
                
                $notas_a_mostrar[] = [
                    'id' => $hijo->ID,
                    'title' => $t2_hijo,
                    'url' => get_permalink($hijo->ID)
                ];
            }
        } elseif ($is_edit_mode && $settings['preview_mode'] === 'yes') {
            // MODO PREVISUALIZADOR CON DATOS DE PRUEBA
            $titulo_hub = '6 años sin Irma';
            $notas_a_mostrar = [
                ['id' => $current_id, 'title' => 'La memoria viva', 'url' => '#'],
                ['id' => 9991, 'title' => 'Perfil del activista', 'url' => '#'],
                ['id' => 9992, 'title' => 'Entrevista (2016)', 'url' => '#'],
            ];
        } else {
            return;
        }

        if(count($notas_a_mostrar) < 1) return;

        // Clase dinámica para manejar el espacio del Logo vs Título 1 en móviles
        $hub_class = !empty($titulo_hub) ? 'has-hub-title' : 'no-hub-title';

        // 2. ESTRUCTURA HTML/CSS IDÉNTICA A EASY-LIVEBLOG (NYT)
        ?>
        <style>
            .elamb-dossier-nav-wrapper { 
                width: 100vw !important; 
                max-width: 100vw !important;
                margin-left: calc(-50vw + 50%) !important; 
                margin-right: calc(-50vw + 50%) !important;
                border-top: 1px solid; 
                border-bottom: 1px solid; 
                margin-bottom: 24px; 
                position: sticky; 
                top: 0; 
                z-index: 999999; 
                overflow: hidden; 
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            
            .elamb-dossier-nav-inner { 
                display: flex; align-items: center; max-width: 1200px; margin: 0 auto; 
                overflow-x: auto; overflow-y: hidden; scrollbar-width: none; -webkit-overflow-scrolling: touch; 
            }
            .elamb-dossier-nav-inner::-webkit-scrollbar { display: none; }
            
            .elamb-dossier-brand-area { 
                display: flex; align-items: center; flex-shrink: 0; position: relative; background: transparent; padding-right: 14px; 
            }
            .elamb-dossier-brand-area::after { 
                content: ''; position: absolute; right: 0; top: 12px; bottom: 12px; width: 1px; 
            }
            
            .elamb-dossier-logo { 
                max-height: 18px; width: auto; object-fit: contain; margin-right: 12px; margin-left: 16px; 
            }
            
            /* Ajuste si no existe el Título 1 */
            .no-hub-title .elamb-dossier-logo { 
                max-height: 22px; margin-right: 16px; 
            }
            
            .elamb-dossier-title { 
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
                font-size: 14px; 
                font-weight: 700; 
                padding: 12px 0; 
                line-height: 1.2;
                white-space: normal; 
                max-width: 350px; 
                display: -webkit-box;
                -webkit-line-clamp: 2; 
                -webkit-box-orient: vertical;
                overflow: hidden;
                word-wrap: break-word;
            }
            
            .elamb-dossier-links { 
                display: flex; list-style: none; padding: 0 0 0 10px; margin: 0; flex-wrap: nowrap; 
            }
            .elamb-dossier-links li { 
                margin: 0; padding: 0; flex-shrink: 0; display: flex; align-items: center; 
            }
            .elamb-dossier-links a { 
                display: flex; align-items: center; 
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
                font-size: 14px; font-weight: 400; 
                padding: 14px 16px; white-space: nowrap; border-bottom: 2px solid transparent; transition: color 0.2s; text-decoration: none;
            }
            
            .elamb-dossier-links a.elamb-nav-active { 
                font-weight: 700; 
                border-bottom: 2px solid; 
            }

            /* Reglas específicas para pantallas móviles */
            @media (max-width: 768px) { 
                .elamb-dossier-logo { margin-left: 12px; }
                .elamb-dossier-title { font-size: 13px; margin-left: 16px; max-width: 150px; } 
                .elamb-dossier-links a { font-size: 13px; padding: 14px 12px; } 
                
                /* Si existe Título 1, ocultamos el logo para mantener limpieza y priorizar texto */
                .has-hub-title .elamb-dossier-logo { display: none !important; }
            }
        </style>

        <div class="elamb-dossier-nav-wrapper <?php echo esc_attr($hub_class); ?>">
            <div class="elamb-dossier-nav-inner">
                
                <div class="elamb-dossier-brand-area">
                    <?php if (!empty($logo_url)): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" class="elamb-dossier-logo" alt="Logo Editorial">
                    <?php endif; ?>
                    
                    <?php if (!empty($titulo_hub)): ?>
                        <div class="elamb-dossier-title"><?php echo esc_html($titulo_hub); ?></div>
                    <?php endif; ?>
                </div>
                
                <ul class="elamb-dossier-links">
                    <?php foreach ($notas_a_mostrar as $nota): ?>
                        <li>
                            <a href="<?php echo esc_url($nota['url']); ?>" class="<?php echo ($current_id == $nota['id']) ? 'elamb-nav-active' : ''; ?>">
                                <?php echo esc_html($nota['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
    }
}
