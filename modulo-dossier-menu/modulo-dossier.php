<?php
/*
 * Módulo: Dossier Histórico (Lógica Padre/Hijito, Título Hub y Título Pestaña)
 * Integra un sistema de jerarquía para coberturas documentales y puente a Liveblog.
 */

if (!defined('ABSPATH')) exit;

class El_Ambientalista_Dossier_Modulo {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'agregar_metabox_dossier']);
        add_action('save_post', [$this, 'guardar_relaciones_dossier']);
        add_action('elementor/widgets/register', [$this, 'registrar_widget_estatico']);
    }

    /**
     * Agrega el Meta Box en el editor de Entradas, Páginas y Liveblogs
     */
    public function agregar_metabox_dossier() {
        add_meta_box(
            'elamb_dossier_constructor',
            '📚 Dossier Histórico (Contexto)',
            [$this, 'renderizar_metabox'],
            ['post', 'page', 'elb_liveblog'],
            'side',
            'high'
        );
    }

    public function renderizar_metabox($post) {
        wp_nonce_field('guardar_dossier_nonce', 'dossier_seguridad');

        // 1. Obtener valores actuales
        $rol_actual = get_post_meta($post->ID, '_elamb_dossier_rol', true);
        $padre_id_actual = get_post_meta($post->ID, '_elamb_dossier_padre_id', true);
        
        // TÍTULO 1 (Hub/Dossier): Solo lo define el Padre
        $titulo_hub = get_post_meta($post->ID, '_elamb_dossier_hub_title', true); 
        
        // TÍTULO 2 (Pestaña): Compartido con easy-liveblogs
        $titulo_pestaña = get_post_meta($post->ID, '_elb_short_menu_title', true); 

        // Buscar posibles padres (excluyendo la nota actual)
        $posibles_padres = get_posts([
            'post_type' => ['post', 'page'],
            'meta_key' => '_elamb_dossier_rol',
            'meta_value' => 'padre',
            'numberposts' => -1,
            'exclude' => [$post->ID]
        ]);

        ?>
        <div style="margin-bottom: 15px;">
            <p style="font-size: 13px; color: #555; margin-top:0; font-weight:bold;">Rol en la cobertura:</p>
            
            <label style="display:block; margin-bottom: 5px;">
                <input type="radio" name="elamb_dossier_rol" value="" <?php checked(empty($rol_actual)); ?>>
                Independiente (Normal)
            </label>
            
            <label style="display:block; margin-bottom: 5px; font-weight: bold;">
                <input type="radio" name="elamb_dossier_rol" value="padre" <?php checked($rol_actual, 'padre'); ?>>
                👑 Nota Principal (PADRE)
            </label>
            
            <label style="display:block; margin-bottom: 5px;">
                <input type="radio" name="elamb_dossier_rol" value="hijo" <?php checked($rol_actual, 'hijo'); ?>>
                📄 Contexto (HIJITO)
            </label>
        </div>

        <div id="wrapper-titulo-hub" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; <?php echo ($rol_actual !== 'padre') ? 'display:none;' : ''; ?>">
            <p style="margin:0 0 5px 0; font-size:11px; font-weight:bold; color:#d93025;">TÍTULO 1: Nombre de la Cobertura (Hub)</p>
            <input type="text" name="elamb_dossier_hub_title" value="<?php echo esc_attr($titulo_hub); ?>" style="width: 100%; border-color:#d93025;" placeholder="Ej. 6 años sin Irma" />
            <p style="font-size: 10px; color: #666; margin-top: 4px;">Es el nombre que aparecerá junto al logo.</p>
        </div>

        <div id="dossier-selector-padre" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; <?php echo ($rol_actual !== 'hijo') ? 'display:none;' : ''; ?>">
            <p style="margin:0 0 5px 0; font-size:11px; font-weight:bold;">Vincular a Nota Padre:</p>
            <select name="elamb_dossier_padre_id" style="width: 100%;">
                <option value="">-- Selecciona el Padre --</option>
                <?php foreach ($posibles_padres as $padre): ?>
                    <option value="<?php echo esc_attr($padre->ID); ?>" <?php selected($padre_id_actual, $padre->ID); ?>>
                        <?php echo esc_html(wp_trim_words($padre->post_title, 6)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
            <p style="margin:0 0 5px 0; font-size:11px; font-weight:bold;">TÍTULO 2: Texto en el Menú (Pestaña)</p>
            <input type="text" name="elamb_dossier_short_title" value="<?php echo esc_attr($titulo_pestaña); ?>" style="width: 100%;" placeholder="Ej. Memoria, Entrevista..." />
            <p style="font-size: 10px; color: #666; margin-top: 4px;">Cómo se llamará este enlace específico en la barra.</p>
        </div>

        <?php if ($rol_actual === 'padre'): ?>
            <div style="margin-top: 15px; padding: 10px; background: #fff5f5; border-left: 3px solid #d93025;">
                <label style="color: #d93025; font-weight: bold; font-size: 11px; display:flex; align-items:center;">
                    <input type="checkbox" name="elamb_puente_liveblog" value="1" style="margin-right: 5px;">
                    🔴 CONVERTIR A LIVEBLOG
                </label>
                <p style="font-size: 10px; margin-top:5px; color:#666;">Vinculará automáticamente a todos los hijitos al sistema de noticias en vivo.</p>
            </div>
        <?php endif; ?>

        <script>
            document.querySelectorAll('input[name="elamb_dossier_rol"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    // Mostrar Hub Title solo si es padre
                    document.getElementById('wrapper-titulo-hub').style.display = (this.value === 'padre') ? 'block' : 'none';
                    // Mostrar Selector de Padre solo si es hijo
                    document.getElementById('dossier-selector-padre').style.display = (this.value === 'hijo') ? 'block' : 'none';
                });
            });
        </script>
        <?php
    }

    public function guardar_relaciones_dossier($post_id) {
        if (!isset($_POST['dossier_seguridad']) || !wp_verify_nonce($_POST['dossier_seguridad'], 'guardar_dossier_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $rol = sanitize_text_field($_POST['elamb_dossier_rol']);
        $padre_id = sanitize_text_field($_POST['elamb_dossier_padre_id']);

        // Guardar Rol y Títulos
        update_post_meta($post_id, '_elamb_dossier_rol', $rol);
        update_post_meta($post_id, '_elamb_dossier_hub_title', sanitize_text_field($_POST['elamb_dossier_hub_title']));
        update_post_meta($post_id, '_elb_short_menu_title', sanitize_text_field($_POST['elamb_dossier_short_title']));

        // Manejar relación jerárquica
        if ($rol === 'hijo' && !empty($padre_id)) {
            update_post_meta($post_id, '_elamb_dossier_padre_id', $padre_id);
        } else {
            delete_post_meta($post_id, '_elamb_dossier_padre_id');
        }

        // LÓGICA DEL PUENTE: Si es Padre y activó el Checkbox rojo
        if ($rol === 'padre' && isset($_POST['elamb_puente_liveblog']) && $_POST['elamb_puente_liveblog'] == '1') {
            update_post_meta($post_id, '_elb_is_liveblog', '1');

            $hijitos = get_posts([
                'post_type' => ['post', 'page'],
                'meta_key' => '_elamb_dossier_padre_id',
                'meta_value' => $post_id,
                'numberposts' => -1,
            ]);

            foreach ($hijitos as $hijo) {
                update_post_meta($hijo->ID, '_elb_linked_coverage', $post_id);
                update_post_meta($hijo->ID, '_elb_liveblog', $post_id);
            }
        }
    }

    public function registrar_widget_estatico($widgets_manager) {
        $file = __DIR__ . '/widget-dossier-estatico.php';
        if (file_exists($file)) {
            require_once($file);
            if (method_exists($widgets_manager, 'register')) {
                $widgets_manager->register(new \El_Ambientalista_Widget_Dossier_Estatico());
            } else {
                $widgets_manager->register_widget_type(new \El_Ambientalista_Widget_Dossier_Estatico());
            }
        }
    }
}

new El_Ambientalista_Dossier_Modulo();
