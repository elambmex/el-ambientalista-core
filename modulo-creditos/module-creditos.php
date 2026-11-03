<?php
/**
 * Módulo de Créditos de Imágenes
 */

if (!defined('ABSPATH')) exit;

// Cargar Admin
require_once __DIR__ . '/includes/module-creditos-admin.php';

// Cargar Metabox
require_once __DIR__ . '/includes/module-creditos-metabox.php';

// Guardado de metadatos
require_once __DIR__ . '/includes/module-creditos-save.php';

// Mostrar créditos en front-end
require_once __DIR__ . '/includes/module-creditos-display.php';

// Cargar CSS/JS
function eap_creditos_assets() {
    wp_enqueue_style('eap-creditos-css', plugins_url('assets/creditos.css', __FILE__), [], '1.0');
    wp_enqueue_script('eap-creditos-js', plugins_url('assets/creditos.js', __FILE__), ['jquery'], '1.0', true);
}
add_action('admin_enqueue_scripts', 'eap_creditos_assets');
