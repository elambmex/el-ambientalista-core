<?php
/**
 * Metabox para Créditos Multimedia
 */

if (!defined('ABSPATH')) exit;

class EAP_Credits_Metabox {

    public function __construct() {
        add_filter('attachment_fields_to_edit', [$this, 'campos_creditos'], 10, 2);
        add_filter('attachment_fields_to_save', [$this, 'guardar_desde_medios'], 10, 2);
    }

    public function campos_creditos($form_fields, $post) {

        $autores = get_option('eap_credit_authors', []);
        $medios  = get_option('eap_credit_medios', []);

        $tipo_options = [
            'foto'  => 'Foto de',
            'video' => 'Video de',
            'ilustracion' => 'Ilustración de'
        ];

        $form_fields['eap_tipo_credito'] = [
            'label' => 'Tipo de crédito',
            'input' => 'html',
            'html'  => '<select name="attachments['.$post->ID.'][eap_tipo_credito]">'.
                        $this->options_html($tipo_options, get_post_meta($post->ID, 'eap_tipo_credito', true)).
                      '</select>'
        ];

        $form_fields['eap_autor_credito'] = [
            'label' => 'Autor',
            'input' => 'html',
            'html'  => '
                <input type="text" 
                    name="attachments['.$post->ID.'][eap_autor_credito]" 
                    value="'.esc_attr(get_post_meta($post->ID, 'eap_autor_credito', true)).'"
                    list="eap_autores_list">
                <datalist id="eap_autores_list">'.$this->datalist_html($autores).'</datalist>
            '
        ];

        $form_fields['eap_medio_credito'] = [
            'label' => 'Medio',
            'input' => 'html',
            'html'  => '
                <input type="text" 
                    name="attachments['.$post->ID.'][eap_medio_credito]" 
                    value="'.esc_attr(get_post_meta($post->ID, 'eap_medio_credito', true)).'"
                    list="eap_medios_list">
                <datalist id="eap_medios_list">'.$this->datalist_html($medios).'</datalist>
            '
        ];

        $form_fields['eap_pie_credito'] = [
            'label' => 'Pie de foto / descripción',
            'input' => 'textarea',
            'value' => get_post_meta($post->ID, 'eap_pie_credito', true)
        ];

        return $form_fields;
    }

    private function options_html($options, $selected) {
        $html = '';
        foreach ($options as $value => $label) {
            $sel = selected($selected, $value, false);
            $html .= "<option value='$value' $sel>$label</option>";
        }
        return $html;
    }

    private function datalist_html($items) {
        $html = '';
        foreach ($items as $item) {
            $html .= '<option value="'.esc_attr($item).'">';
        }
        return $html;
    }

    public function guardar_desde_medios($post, $attachment) {
        $campos = ['eap_tipo_credito','eap_autor_credito','eap_medio_credito','eap_pie_credito'];
        foreach ($campos as $campo) {
            if (isset($attachment[$campo])) {
                update_post_meta($post['ID'], $campo, sanitize_text_field($attachment[$campo]));
            }
        }
        return $post;
    }
}

new EAP_Credits_Metabox();
