<?php
/*
Módulo: Breaking News Alert (Widget y Banner)
Shortcode: [breaking_news]
*/

if (!defined('ABSPATH')) exit;

/**
 * Función central: Obtiene la última noticia de "Última hora"
 * si tiene menos de 6 horas.
 */
function bna_get_latest_ultima_hora() {
    // Usar un 'transient' para cachear el resultado por 5 minutos
    $cached_news = get_transient('bna_latest_news');
    if (false !== $cached_news) {
        return $cached_news;
    }

    $news_data = false;
    $query = new WP_Query([
        'category_name' => 'ultima-hora',
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    if ($query->have_posts()) {
        $query->the_post();
        $post_time = get_the_time('U');
        $current_time = current_time('timestamp');
        $time_diff = ($current_time - $post_time) / 3600; // Diferencia en horas

        // Solo mostrar si tiene menos de 6 horas
        if ($time_diff < 6) {
            $news_data = [
                'title' => get_the_title(),
                'link' => get_permalink()
            ];
        }
        wp_reset_postdata();
    }
    
    // Guardar en caché (incluso si es 'false') por 5 minutos
    set_transient('bna_latest_news', $news_data, 5 * MINUTE_IN_SECONDS);
    
    return $news_data;
}

/**
 * Encolar los assets (CSS y JS) del módulo
 * Se llama solo si la noticia existe.
 */
function bna_enqueue_assets() {
    // Usar 'wp_enqueue_style' asegura que solo se cargue una vez
    wp_enqueue_style(
        'bna-style',
        ARP_CORE_URL . 'modulo-breaking-news/assets/css/style.css',
        [],
        '1.0'
    );
    
    // Cargar el JS solo en el footer
    wp_enqueue_script(
        'bna-script',
        ARP_CORE_URL . 'modulo-breaking-news/assets/js/script.js',
        [],
        '1.0',
        true // Cargar en el footer
    );
}

/**
 * Módulo 1: Shortcode [breaking_news]
 */
function bna_render_breaking_news() {
    $news = bna_get_latest_ultima_hora();
    if ($news) {
        // Encolar los assets solo si el shortcode se usa y hay noticia
        bna_enqueue_assets(); 

        ob_start(); ?>
        <div id="breaking-news-widget" class="breaking-news">
            <span class="blink">Última hora:</span>
            <a href="<?php echo esc_url($news['link']); ?>"><?php echo esc_html($news['title']); ?></a>
        </div>
        <?php
        return ob_get_clean();
    }
    return ''; // No mostrar nada si no hay noticia
}
add_shortcode('breaking_news', 'bna_render_breaking_news');

/**
 * Módulo 2: Banner en el Footer
 */
function bna_banner_html() {
    // Solo mostrar en páginas singulares (posts, páginas)
    if (!is_singular()) return;

    $news = bna_get_latest_ultima_hora();
    if ($news) {
        // Encolar los assets solo si el banner se va a mostrar
        bna_enqueue_assets();
        ?>
        <div id="bna-banner">
            <div class="bna-content">
                Última hora: 
                <a href="<?php echo esc_url($news['link']); ?>"><?php echo esc_html($news['title']); ?></a>
                <span class="bna-close" id="bna-close-btn" title="Cerrar">❌</span>
            </div>
        </div>
        <?php
    }
}
add_action('wp_footer', 'bna_banner_html');