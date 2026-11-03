<?php
/**
 * Plugin Name: El Ambientalista Core
 * Plugin URI: https://github.com/elambmex/el-ambientalista-core
 * Description: Núcleo de módulos y funcionalidades para El Ambientalista. Como Comenatrios, Complementos de elementos a Live´s Blog´s, enttre otros
 * Version: 1.2.0
 * Author: Marcos Abraham 
 * Author URI: https://elambientalistapost.com
 */

if (!defined('ABSPATH')) exit;

final class El_Ambientalista_Core {

    const VERSION = '2.0';

    private static $_instance = null;

    public $modules = [];

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->define_constants();
        $this->load_modules();
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    private function define_constants() {
        if (!defined('ARP_CORE_PATH')) {
            define('ARP_CORE_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('ARP_CORE_URL')) {
            define('ARP_CORE_URL', plugin_dir_url(__FILE__));
        }
    }

    private function load_modules() {
        // Módulo 1
        require_once( ARP_CORE_PATH . 'modulo-autor-ubicacion/modulo-autor-ubicacion.php' );
        $this->modules['autor_ubicacion'] = 'Autor y Ubicación (Elementor)';

        // Módulo 2
        require_once( ARP_CORE_PATH . 'modulo-validador-eml/modulo-validador-eml.php' );
        $this->modules['validador_eml'] = 'Validador de Correos EML';

        // Módulo 3
        require_once( ARP_CORE_PATH . 'modulo-filtro-ajax/modulo-filtro-ajax.php' );
        $this->modules['filtro_ajax'] = 'Filtro de Noticias AJAX';

        // Módulo 4
        require_once( ARP_CORE_PATH . 'modulo-correcciones/modulo-correcciones.php' );
        $this->modules['correcciones'] = 'Correcciones de Entradas';

        // Módulo 5
        require_once( ARP_CORE_PATH . 'modulo-breaking-news/modulo-breaking-news.php' );
        $this->modules['breaking_news'] = 'Breaking News (Widget y Banner)';
        
        // Módulo 6
        require_once( ARP_CORE_PATH . 'modulo-world-time/world-time-elementor-widget.php' );
        $this->modules['Hora_reporta_localizacion'] = 'Hora de donde se reporta';
        
        // Módulo 7
        require_once( ARP_CORE_PATH . 'modulo-creditos/module-creditos.php' );
        $this->modules['Modulo_creditos_pie_pagina'] = 'Modulo Pie de Página';

        // Módulo 8
        require_once( ARP_CORE_PATH . 'modulo-ia-noticia/ia-manager.php' );
        $this->modules['modulo_ia_aviso'] = 'Modulo Aviso de Uso de Inteligencia Artificial';
        
        // Módulo 9: Dossier Histórico y Puente
        require_once( ARP_CORE_PATH . 'modulo-dossier-menu/modulo-dossier.php' );
        $this->modules['modulo_dossier_puente'] = 'Dossier Histórico y Puente Liveblog';

        // Módulo 10: Comentarios Editoriales (Estilo NYT)
        require_once( ARP_CORE_PATH . 'modulo-comentarios/modulo-comentarios.php' );
        $this->modules['modulo_comentarios_nyt'] = 'Comentarios Editoriales (Estilo NYT)';

        // Módulo 11: Comentarios Contestados / Creados por el Autor
        $mod_comentarios_autor = ARP_CORE_PATH . 'modulo-comentarios-autor/modulo-comentarios-autor.php';
        if ( file_exists( $mod_comentarios_autor ) ) {
            require_once( $mod_comentarios_autor );
            $this->modules['modulo_comentarios_autor'] = 'Comentarios de Autor (Página de Autor)';
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            'El Ambientalista Core',
            'Ambientalista Core',
            'manage_options',
            'el-ambientalista-core',
            [$this, 'render_dashboard_page'],
            'dashicons-leaf',
            25
        );
    }

    public function register_settings() {
        register_setting(
            'el_ambientalista_core_options_group',
            'el_ambientalista_settings',
            [$this, 'sanitize_settings']
        );

        add_settings_section(
            'arp_core_settings_section_main',
            'Ajustes Generales',
            null,
            'el-ambientalista-core-settings'
        );

        add_settings_field(
            'ejemplo_api_key',
            'Ejemplo de Clave API',
            [$this, 'render_field_text'],
            'el-ambientalista-core-settings',
            'arp_core_settings_section_main',
            ['id' => 'ejemplo_api_key', 'label_for' => 'ejemplo_api_key']
        );
    }

    public function render_field_text($args) {
        $options = get_option('el_ambientalista_settings');
        $id = $args['id'];
        $value = isset($options[$id]) ? $options[$id] : '';
        printf(
            '<input type="text" name="el_ambientalista_settings[%s]" id="%s" value="%s" class="regular-text">',
            esc_attr($id),
            esc_attr($id),
            esc_attr($value)
        );
        echo '<p class="description">Este es un campo de ejemplo. Lo usaremos para ajustes reales pronto.</p>';
    }

    public function sanitize_settings($input) {
        $sanitized_input = [];
        if (isset($input['ejemplo_api_key'])) {
            $sanitized_input['ejemplo_api_key'] = sanitize_text_field($input['ejemplo_api_key']);
        }
        return $sanitized_input;
    }

    public function render_dashboard_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p>Panel de control central para todas las funcionalidades de El Ambientalista Post.</p>

            <h2 class="nav-tab-wrapper">
                <a href="?page=el-ambientalista-core&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=el-ambientalista-core&tab=modulos" class="nav-tab <?php echo $active_tab == 'modulos' ? 'nav-tab-active' : ''; ?>">Módulos Activos</a>
                <a href="?page=el-ambientalista-core&tab=ajustes" class="nav-tab <?php echo $active_tab == 'ajustes' ? 'nav-tab-active' : ''; ?>">Ajustes</a>
                <a href="?page=el-ambientalista-core&tab=diagnostico" class="nav-tab <?php echo $active_tab == 'diagnostico' ? 'nav-tab-active' : ''; ?>">Diagnóstico</a>
            </h2>

            <?php
            if ($active_tab == 'general') {
                $this->render_tab_general();
            } elseif ($active_tab == 'modulos') {
                $this->render_tab_modulos();
            } elseif ($active_tab == 'ajustes') {
                $this->render_tab_ajustes();
            } elseif ($active_tab == 'diagnostico') {
                $this->render_tab_diagnostico();
            }
            ?>
        </div>
        <?php
    }

    private function render_tab_general() {
        ?>
        <h2>¡Bienvenido al Panel de Control Core!</h2>
        <p>Desde aquí puedes gestionar todos los módulos personalizados de tu sitio.</p>
        <p>Usa las pestañas de arriba para ver los módulos cargados, configurar ajustes generales o revisar el diagnóstico del sistema.</p>
        <?php
    }
    
    private function render_tab_modulos() {
        ?>
        <h2>Módulos Cargados (<?php echo count($this->modules); ?>)</h2>
        <p>Estos son los componentes que se han integrado en el plugin principal.</p>
        <ul style="list-style:disc; padding-left:20px;">
            <?php foreach ($this->modules as $key => $name): ?>
                <li><strong><?php echo esc_html($name); ?></strong> (ID: <code><?php echo esc_html($key); ?></code>)</li>
            <?php endforeach; ?>
        </ul>
        <?php
    }
    
    private function render_tab_ajustes() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('el_ambientalista_core_options_group');
            do_settings_sections('el-ambientalista-core-settings');
            submit_button('Guardar Cambios');
            ?>
        </form>
        <?php
    }

    private function render_tab_diagnostico() {
        $elementor_status = did_action('elementor/loaded') 
            ? '<span style="color:green; font-weight:bold;">Activo y Cargado</span>' 
            : '<span style="color:red; font-weight:bold;">No Detectado</span>';

        $debug_status = (defined('WP_DEBUG') && WP_DEBUG)
            ? '<span style="color:orange; font-weight:bold;">Activado</span> (Recomendado desactivar en producción)'
            : '<span style="color:green; font-weight:bold;">Desactivado</span>';
        
        ?>
        <h2>Diagnóstico del Sistema</h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">Estado de Elementor</th>
                    <td><?php echo $elementor_status; ?></td>
                </tr>
                <tr>
                    <th scope="row">Modo de Depuración (WP_DEBUG)</th>
                    <td><?php echo $debug_status; ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}

El_Ambientalista_Core::instance();