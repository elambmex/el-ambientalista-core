<?php
/*
Módulo: Filtro de Noticias AJAX
Shortcode: [elamb_filtro_shortcode]
*/

if (!defined('ABSPATH')) exit;

/**
 * Encolar los scripts y estilos del filtro
 * Rutas actualizadas para usar las constantes del plugin Core.
 */
function elamb_filtro_enqueue_scripts() {
    // CDN de Flatpickr (sin cambios)
    wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);

    // Scripts y Estilos locales (Rutas modificadas)
    wp_enqueue_script('elamb-filtro-ajax', ARP_CORE_URL . 'modulo-filtro-ajax/assets/js/filtro.js', ['jquery'], '1.0', true);
    wp_localize_script('elamb-filtro-ajax', 'elamb_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
    wp_enqueue_style('elamb-filtro-css', ARP_CORE_URL . 'modulo-filtro-ajax/assets/css/filtro.css', [], '1.0');
}
add_action('wp_enqueue_scripts', 'elamb_filtro_enqueue_scripts');

/**
 * Shortcode para mostrar el formulario de filtro
 */
function elamb_filtro_shortcode() {
    ob_start();
    ?>
    <div id="elamb-filtro">
        <div class="elamb-filtro-contenedor">
            <div class="elamb-campo">
                <label>Ordenar por:</label>
                <select id="elamb-orden">
                    <option value="desc">Más reciente</option>
                    <option value="asc">Más antiguo</option>
                </select>
            </div>
            <div class="elamb-campo">
                <label>Categoría:</label>
                <select id="elamb-categoria">
                    <option value="">Todas</option>
                    <?php
                    $categories = get_categories(['hide_empty' => 1]);
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="elamb-campo">
                <label>Fecha Inicio:</label>
                <input type="text" id="elamb-fecha-inicio" placeholder="Seleccionar...">
            </div>
            <div class="elamb-campo">
                <label>Fecha Fin:</label>
                <input type="text" id="elamb-fecha-fin" placeholder="Seleccionar...">
            </div>
            <div class="elamb-campo">
                <button id="elamb-filtrar">Filtrar</button>
            </div>
        </div>
        
        <div id="elamb-resultados">
            </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('elamb_filtro_shortcode', 'elamb_filtro_shortcode');

/**
 * Callback de AJAX para manejar la solicitud de filtro
 */
function elamb_filtrar_callback() {
    // Sanitizar y validar los datos de entrada
    $orden = sanitize_text_field($_POST['orden']) === 'asc' ? 'ASC' : 'DESC';
    
    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => $orden,
    ];

    if (!empty($_POST['categoria'])) {
        $args['category_name'] = sanitize_text_field($_POST['categoria']);
    }

    if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
        $args['date_query'] = [
            [
                'after' => sanitize_text_field($_POST['fecha_inicio']),
                'before' => sanitize_text_field($_POST['fecha_fin']),
                'inclusive' => true,
            ]
        ];
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Esta plantilla de resultados se puede mover a un archivo de plantilla si se desea
            ?>
            <div class="nyt-article">
                <div class="nyt-content">
                    <div class="nyt-meta"><?php echo get_the_date('M. j, Y'); ?> | <?php $cat = get_the_category(); echo esc_html($cat[0]->name); ?></div>
                    <div class="nyt-title"><?php the_title(); ?></div>
                    <div class="nyt-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?></div>
                    <div class="nyt-author">By <?php the_author(); ?></div>
                    <?php 
                    $pagina_impresa = get_post_meta(get_the_ID(), 'pagina_impresa', true);
                    if ($pagina_impresa) : ?>
                        <div class="nyt-meta">Print Edition | <?php echo esc_html($pagina_impresa); ?></div>
                    <?php endif; ?>
                </div>
                <?php if (has_post_thumbnail()): ?>
                    <div class="nyt-thumbnail">
                        <img src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title_attribute(); ?>">
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        echo '<p>No se encontraron noticias con esos criterios.</p>';
    }
    
    wp_reset_postdata();
    die(); // Es importante terminar la ejecución de AJAX
}
add_action('wp_ajax_elamb_filtrar', 'elamb_filtrar_callback');
add_action('wp_ajax_nopriv_elamb_filtrar', 'elamb_filtrar_callback'); // Para usuarios no logueados