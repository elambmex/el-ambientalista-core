<?php
/*
|--------------------------------------------------------------------------
| MÓDULO: Autor, Ubicación y Créditos Extendidos
| Versión: 2.0
|--------------------------------------------------------------------------
*/

namespace ARP\Core;

if (!defined('ABSPATH')) {
    exit;
}

final class ARP_Elementor_Main {

    const VERSION = '2.0';

    private static $_instance = null;

    public static function instance() {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {

        add_action(
            'plugins_loaded',
            [$this, 'init']
        );

    }

    public function init() {

        if (!did_action('elementor/loaded')) {
            return;
        }

        add_action(
            'elementor/widgets/register',
            [$this, 'register_widgets']
        );

        add_action(
            'elementor/elements/categories_registered',
            [$this, 'register_category']
        );

        add_action(
            'wp_enqueue_scripts',
            [$this, 'enqueue_styles']
        );

        add_action(
            'add_meta_boxes',
            [$this, 'add_metabox']
        );

        add_action(
            'save_post',
            [$this, 'save_metadata'],
            10,
            1
        );

        add_filter(
            'manage_posts_columns',
            [$this, 'add_posts_column']
        );

        add_action(
            'manage_posts_custom_column',
            [$this, 'render_posts_column'],
            10,
            2
        );

        add_action(
            'admin_enqueue_scripts',
            [$this, 'enqueue_admin_scripts']
        );

    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORÍA ELEMENTOR
    |--------------------------------------------------------------------------
    */

    public function register_category($elements_manager) {

        $elements_manager->add_category(
            'elambientalista',
            [
                'title' => esc_html__(
                    'El Ambientalista',
                    'arp-elementor'
                ),
                'icon' => 'fa fa-leaf',
            ]
        );

    }

    /*
    |--------------------------------------------------------------------------
    | REGISTRO WIDGETS
    |--------------------------------------------------------------------------
    */

    public function register_widgets($widgets_manager) {

        require_once(
            ARP_CORE_PATH .
            'modulo-autor-ubicacion/widgets/widget-autor.php'
        );

        $widgets_manager->register(
            new \ARP\Widgets\Autor_Ubicacion_Widget()
        );

    }

    /*
    |--------------------------------------------------------------------------
    | METABOX
    |--------------------------------------------------------------------------
    */

    public function add_metabox() {

        add_meta_box(
            'arp_autor_ubicacion',
            __('Autor, ubicación y créditos', 'arp-elementor'),
            [$this, 'render_metabox_content'],
            ['post'],
            'side',
            'default'
        );

    }

    public function render_metabox_content($post) {

        wp_nonce_field(
            'arp_save_metadata',
            'arp_metadata_nonce'
        );

        /*
        |--------------------------------------------------------------------------
        | UBICACIÓN
        |--------------------------------------------------------------------------
        */

        $prefijo = get_post_meta(
            $post->ID,
            '_arp_ubicacion_prefijo',
            true
        );

        $lugar = get_post_meta(
            $post->ID,
            '_arp_ubicacion_lugar',
            true
        );

        $suggested_location = get_post_meta(
            $post->ID,
            '_arp_suggested_location',
            true
        );

        $use_auto_location = get_post_meta(
            $post->ID,
            '_arp_use_auto_location',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | AUTOR
        |--------------------------------------------------------------------------
        */

        $author_role = get_post_meta(
            $post->ID,
            '_arp_author_role',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | BREAKING NEWS
        |--------------------------------------------------------------------------
        */

        $breaking_update = get_post_meta(
            $post->ID,
            '_arp_breaking_update',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | CRÉDITOS
        |--------------------------------------------------------------------------
        */

        $foto_creditos = get_post_meta(
            $post->ID,
            '_arp_creditos_fotografia',
            true
        );

        $ilustr_creditos = get_post_meta(
            $post->ID,
            '_arp_creditos_ilustracion',
            true
        );

        $datos_creditos = get_post_meta(
            $post->ID,
            '_arp_creditos_datos',
            true
        );

        $medio_creditos = get_post_meta(
            $post->ID,
            '_arp_creditos_medio',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | COLABORADORES
        |--------------------------------------------------------------------------
        */

        $collaborators = [];

        for ($i = 0; $i < 5; $i++) {

            $collaborators[$i] = [
                'name' => get_post_meta(
                    $post->ID,
                    "_arp_collab_name_$i",
                    true
                ),
                'role' => get_post_meta(
                    $post->ID,
                    "_arp_collab_role_$i",
                    true
                ),
                'url' => get_post_meta(
                    $post->ID,
                    "_arp_collab_url_$i",
                    true
                ),
            ];

        }

        $opciones = [
            '' => '-- Ninguno --',
            'Reportando desde' => 'Reportando desde',
            'Reportó desde' => 'Reportó desde',
            'Reportaron desde' => 'Reportaron desde',
            'En vivo desde' => 'En vivo desde',
            'Desde' => 'Desde',
        ];

        ?>

        <div class="arp-metabox">

            <style>
                .arp-metabox small{
                    display:block;
                    color:#666;
                    margin-top:4px;
                    font-size:12px;
                }

                .arp-metabox hr{
                    margin:12px 0;
                    border:none;
                    border-top:1px solid #ddd;
                }

                .arp-collab-row{
                    display:flex;
                    gap:6px;
                    margin-bottom:6px;
                }

                .arp-collab-row input{
                    width:100%;
                }
            </style>

            <p><strong>Ubicación</strong></p>

            <p>
                <select
                    name="arp_ubicacion_prefijo"
                    class="widefat"
                >

                    <?php foreach ($opciones as $k => $v): ?>

                        <option
                            value="<?php echo esc_attr($k); ?>"
                            <?php selected($prefijo, $k); ?>
                        >
                            <?php echo esc_html($v); ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </p>

            <p>
                <input
                    type="text"
                    name="arp_ubicacion_lugar"
                    class="widefat"
                    placeholder="Ej: Ciudad de México, México"
                    value="<?php echo esc_attr($lugar); ?>"
                >
            </p>

            <p>
                <label>
                    <input
                        type="checkbox"
                        name="arp_use_auto_location"
                        value="1"
                        <?php checked($use_auto_location, '1'); ?>
                    >
                    Sugerir ubicación automática
                </label>

                <small>
                    Detecta una sugerencia aproximada
                    usando IP.
                </small>
            </p>

            <?php if ($suggested_location): ?>

                <p>
                    <strong>Sugerencia:</strong><br>
                    <em>
                        <?php
                        echo esc_html(
                            $suggested_location
                        );
                        ?>
                    </em>
                </p>

            <?php endif; ?>

            <hr>

            <p><strong>Breaking News</strong></p>

            <p>
                <label>

                    <input
                        type="checkbox"
                        name="arp_breaking_update"
                        value="1"
                        <?php checked(
                            $breaking_update,
                            '1'
                        ); ?>
                    >

                    Última hora / Breaking News

                </label>

                <small>
                    Si está activo, las actualizaciones
                    recientes se mostrarán en rojo.
                </small>
            </p>

            <hr>

            <p><strong>Rol del autor</strong></p>

            <p>
                <input
                    type="text"
                    name="arp_author_role"
                    class="widefat"
                    placeholder="Ej: Corresponsal"
                    value="<?php echo esc_attr($author_role); ?>"
                >
            </p>

            <hr>

            <p><strong>Créditos</strong></p>

            <p>
                <input
                    type="text"
                    name="arp_creditos_fotografia"
                    class="widefat"
                    placeholder="Fotografía por..."
                    value="<?php echo esc_attr($foto_creditos); ?>"
                >
            </p>

            <p>
                <input
                    type="text"
                    name="arp_creditos_ilustracion"
                    class="widefat"
                    placeholder="Ilustración por..."
                    value="<?php echo esc_attr($ilustr_creditos); ?>"
                >
            </p>

            <p>
                <input
                    type="text"
                    name="arp_creditos_datos"
                    class="widefat"
                    placeholder="Datos por..."
                    value="<?php echo esc_attr($datos_creditos); ?>"
                >
            </p>

            <p>
                <input
                    type="text"
                    name="arp_creditos_medio"
                    class="widefat"
                    placeholder="Medio / Agencia"
                    value="<?php echo esc_attr($medio_creditos); ?>"
                >
            </p>

            <hr>

            <p><strong>Colaboradores</strong></p>

            <?php for ($i = 0; $i < 5; $i++): ?>

                <?php $c = $collaborators[$i]; ?>

                <div class="arp-collab-row">

                    <input
                        type="text"
                        name="arp_collab_name_<?php echo $i; ?>"
                        placeholder="Nombre"
                        value="<?php echo esc_attr($c['name']); ?>"
                    >

                    <input
                        type="text"
                        name="arp_collab_role_<?php echo $i; ?>"
                        placeholder="Rol"
                        value="<?php echo esc_attr($c['role']); ?>"
                    >

                    <input
                        type="url"
                        name="arp_collab_url_<?php echo $i; ?>"
                        placeholder="URL"
                        value="<?php echo esc_attr($c['url']); ?>"
                    >

                </div>

            <?php endfor; ?>

        </div>

        <?php
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE METADATA
    |--------------------------------------------------------------------------
    */

    public function save_metadata($post_id) {

        if (
            !isset($_POST['arp_metadata_nonce'])
            || !wp_verify_nonce(
                $_POST['arp_metadata_nonce'],
                'arp_save_metadata'
            )
        ) {
            return;
        }

        if (
            defined('DOING_AUTOSAVE')
            && DOING_AUTOSAVE
        ) {
            return;
        }

        if (
            !current_user_can(
                'edit_post',
                $post_id
            )
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CAMPOS BASE
        |--------------------------------------------------------------------------
        */

        $campos_map = [

            '_arp_ubicacion_prefijo' =>
                'arp_ubicacion_prefijo',

            '_arp_ubicacion_lugar' =>
                'arp_ubicacion_lugar',

            '_arp_creditos_fotografia' =>
                'arp_creditos_fotografia',

            '_arp_creditos_ilustracion' =>
                'arp_creditos_ilustracion',

            '_arp_creditos_datos' =>
                'arp_creditos_datos',

            '_arp_creditos_medio' =>
                'arp_creditos_medio',

            '_arp_author_role' =>
                'arp_author_role',

        ];

        foreach ($campos_map as $meta => $post_key) {

            if (isset($_POST[$post_key])) {

                update_post_meta(
                    $post_id,
                    $meta,
                    sanitize_text_field(
                        wp_unslash($_POST[$post_key])
                    )
                );

            }

        }

        /*
        |--------------------------------------------------------------------------
        | BREAKING NEWS
        |--------------------------------------------------------------------------
        */

        $breaking = isset($_POST['arp_breaking_update'])
            ? '1'
            : '0';

        update_post_meta(
            $post_id,
            '_arp_breaking_update',
            $breaking
        );

        /*
        |--------------------------------------------------------------------------
        | UBICACIÓN AUTOMÁTICA
        |--------------------------------------------------------------------------
        */

        $use_auto = isset($_POST['arp_use_auto_location'])
            ? '1'
            : '0';

        update_post_meta(
            $post_id,
            '_arp_use_auto_location',
            $use_auto
        );

        /*
        |--------------------------------------------------------------------------
        | COLABORADORES
        |--------------------------------------------------------------------------
        */

        for ($i = 0; $i < 5; $i++) {

            update_post_meta(
                $post_id,
                "_arp_collab_name_$i",
                isset($_POST["arp_collab_name_$i"])
                    ? sanitize_text_field(
                        wp_unslash(
                            $_POST["arp_collab_name_$i"]
                        )
                    )
                    : ''
            );

            update_post_meta(
                $post_id,
                "_arp_collab_role_$i",
                isset($_POST["arp_collab_role_$i"])
                    ? sanitize_text_field(
                        wp_unslash(
                            $_POST["arp_collab_role_$i"]
                        )
                    )
                    : ''
            );

            update_post_meta(
                $post_id,
                "_arp_collab_url_$i",
                isset($_POST["arp_collab_url_$i"])
                    ? esc_url_raw(
                        wp_unslash(
                            $_POST["arp_collab_url_$i"]
                        )
                    )
                    : ''
            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | COLUMNAS ADMIN
    |--------------------------------------------------------------------------
    */

    public function add_posts_column($columns) {

        $columns['arp_ubicacion'] =
            __('Ubicación', 'arp-elementor');

        $columns['arp_breaking'] =
            __('Breaking', 'arp-elementor');

        return $columns;
    }

    public function render_posts_column(
        $column,
        $post_id
    ) {

        if ('arp_ubicacion' === $column) {

            echo esc_html(
                arp_get_post_ubicacion($post_id)
            );

        }

        if ('arp_breaking' === $column) {

            $breaking = get_post_meta(
                $post_id,
                '_arp_breaking_update',
                true
            );

            echo ('1' === $breaking)
                ? '🚨'
                : '—';

        }

    }

    /*
    |--------------------------------------------------------------------------
    | ASSETS
    |--------------------------------------------------------------------------
    */

    public function enqueue_styles() {

        wp_enqueue_style(
            'arp-elementor-style',
            ARP_CORE_URL .
            'modulo-autor-ubicacion/assets/css/style.css',
            [],
            self::VERSION
        );

    }

    public function enqueue_admin_scripts($hook) {

        if (
            'edit.php' === $hook
            && get_post_type() === 'post'
        ) {

            wp_enqueue_script(
                'arp-admin-js',
                ARP_CORE_URL .
                'modulo-autor-ubicacion/assets/js/arp-admin.js',
                ['jquery'],
                self::VERSION,
                true
            );

        }

    }

}

ARP_Elementor_Main::instance();

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function arp_get_post_ubicacion($post_id = null) {

    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $prefijo = get_post_meta(
        $post_id,
        '_arp_ubicacion_prefijo',
        true
    );

    $lugar = get_post_meta(
        $post_id,
        '_arp_ubicacion_lugar',
        true
    );

    $suggested = get_post_meta(
        $post_id,
        '_arp_suggested_location',
        true
    );

    $display = $lugar ?: $suggested;

    return trim($prefijo . ' ' . $display);

}

function arp_get_post_creditos($post_id = null) {

    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $foto = get_post_meta(
        $post_id,
        '_arp_creditos_fotografia',
        true
    );

    $ilus = get_post_meta(
        $post_id,
        '_arp_creditos_ilustracion',
        true
    );

    $datos = get_post_meta(
        $post_id,
        '_arp_creditos_datos',
        true
    );

    $medio = get_post_meta(
        $post_id,
        '_arp_creditos_medio',
        true
    );

    $creditos = [];

    if ($foto) {
        $creditos[] =
            'Fotografía por ' . $foto;
    }

    if ($ilus) {
        $creditos[] =
            'Ilustración por ' . $ilus;
    }

    if ($datos) {
        $creditos[] =
            'Datos por ' . $datos;
    }

    if ($medio) {
        $creditos[] =
            'Para ' . $medio;
    }

    return implode(' · ', $creditos);

}

function arp_get_post_colaboradores(
    $post_id = null
) {

    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $collabs = [];

    for ($i = 0; $i < 5; $i++) {

        $name = get_post_meta(
            $post_id,
            "_arp_collab_name_$i",
            true
        );

        $role = get_post_meta(
            $post_id,
            "_arp_collab_role_$i",
            true
        );

        $url = get_post_meta(
            $post_id,
            "_arp_collab_url_$i",
            true
        );

        if ($name || $role || $url) {

            $collabs[] = [
                'name' => $name,
                'role' => $role,
                'url' => $url,
            ];

        }

    }

    return $collabs;

}