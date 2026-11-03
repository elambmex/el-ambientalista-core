<?php
if (!defined('ABSPATH')) exit;

class EAP_Credits_Save {

    public function __construct() {
        add_action('edit_attachment', [$this, 'agregar_a_listas']);
    }

    public function agregar_a_listas($id) {

        $autor = get_post_meta($id, 'eap_autor_credito', true);
        $medio = get_post_meta($id, 'eap_medio_credito', true);

        $autores = get_option('eap_credit_authors', []);
        $medios  = get_option('eap_credit_medios', []);

        if ($autor && !in_array($autor, $autores)) {
            $autores[] = $autor;
            update_option('eap_credit_authors', $autores);
        }

        if ($medio && !in_array($medio, $medios)) {
            $medios[] = $medio;
            update_option('eap_credit_medios', $medios);
        }
    }
}

new EAP_Credits_Save();
