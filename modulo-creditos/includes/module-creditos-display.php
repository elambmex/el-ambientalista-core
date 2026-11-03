<?php
if (!defined('ABSPATH')) exit;

class EAP_Credits_Display {

    public function __construct() {
        add_filter('wp_get_attachment_image_attributes', [$this, 'agregar_clase'], 10, 3);
        add_filter('the_content', [$this, 'insertar_creditos_en_contenido'], 20);
        add_filter('post_thumbnail_html', [$this, 'insertar_creditos_destacada'], 20, 3);
    }

    public function agregar_clase($attr, $attachment, $size) {
        $attr['class'] .= ' eap-con-credito';
        return $attr;
    }

    private function generar_credito($id) {
        $tipo  = get_post_meta($id, 'eap_tipo_credito', true);
        $autor = get_post_meta($id, 'eap_autor_credito', true);
        $medio = get_post_meta($id, 'eap_medio_credito', true);
        $pie   = get_post_meta($id, 'eap_pie_credito', true);

        if (!$tipo && !$autor && !$medio) {
            return '';
        }

        $tipo_txt = [
            'foto' => 'Foto de',
            'video' => 'Video de',
            'ilustracion' => 'Ilustración de'
        ];

        $credito = $tipo_txt[$tipo] ?? '';

        if ($autor) $credito .= " $autor";
        if ($medio) $credito .= " para $medio";

        $html = '<div class="eap-credito">';
        if ($pie) {
            $html .= '<div class="eap-pie">'.esc_html($pie).'</div>';
        }
        $html .= '<div class="eap-linea">'.$credito.'</div></div>';

        return $html;
    }

    public function insertar_creditos_destacada($html, $post_id, $thumb_id) {
        return $html . $this->generar_credito($thumb_id);
    }

    public function insertar_creditos_en_contenido($content) {
        return preg_replace_callback(
            '/<img[^>]+wp-image-(\d+)[^>]*>/i',
            function($matches) {
                $id = $matches[1];
                return $matches[0] . $this->generar_credito($id);
            },
            $content
        );
    }
}

new EAP_Credits_Display();
