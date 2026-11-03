<?php
namespace WTEW\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if (!defined('ABSPATH')) exit;

class World_Time_Elementor extends Widget_Base {
    public function get_name() { return 'world_time_widget'; }
    public function get_title() { return 'Hora internacional (EAP)'; }
    public function get_icon() { return 'eicon-clock-o'; }
    public function get_categories() { return ['general']; }

    protected function register_controls() {
        $this->start_controls_section('section_content', ['label' => 'Hora internacional']);

        $this->add_control('enable_world_time', [
            'label' => '¿Mostrar la franja de hora internacional?',
            'type' => Controls_Manager::SWITCHER,
            'label_on' => 'Sí',
            'label_off' => 'No',
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $repeater = new Repeater();
        $repeater->add_control('city_timezone', [
            'label' => 'País y localidad',
            'type' => Controls_Manager::SELECT,
            'label_block' => true,
            'options' => self::get_timezone_options(),
            'default' => 'America/Mexico_City',
            'description' => 'Selecciona la localidad. La zona horaria técnica se configura automáticamente.',
        ]);
        $repeater->add_control('city_label', [
            'label' => 'Nombre que verá el lector (opcional)',
            'type' => Controls_Manager::TEXT,
            'label_block' => true,
            'placeholder' => 'Se usará el nombre de la localidad si se deja vacío',
            'description' => 'Puedes personalizar el nombre, por ejemplo: “Brasil” o “Monterrey”.',
        ]);

        $this->add_control('city_list', [
            'label' => 'Localidades (máximo 10)',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'title_field' => '{{{ city_label || city_timezone }}}',
            'max_items' => 10,
            'default' => [
                ['city_timezone' => 'America/Mexico_City', 'city_label' => 'México'],
                ['city_timezone' => 'America/Sao_Paulo', 'city_label' => 'Brasil'],
                ['city_timezone' => 'America/New_York', 'city_label' => 'Nueva York'],
            ],
        ]);
        $this->end_controls_section();
    }

    /** Compatibilidad con versiones antiguas de Elementor. */
    protected function _register_controls() {
        $this->register_controls();
    }

    private static function get_timezone_options() {
        return [
            'America/Mexico_City' => 'México — Ciudad de México',
            'America/Monterrey' => 'México — Monterrey, Nuevo León',
            'America/Tijuana' => 'México — Tijuana, Baja California',
            'America/Chihuahua' => 'México — Chihuahua',
            'America/Ciudad_Juarez' => 'México — Ciudad Juárez',
            'America/Hermosillo' => 'México — Hermosillo, Sonora',
            'America/Mazatlan' => 'México — Mazatlán, Sinaloa',
            'America/Cancun' => 'México — Cancún, Quintana Roo',
            'America/Matamoros' => 'México — Matamoros, Tamaulipas',
            'America/Merida' => 'México — Mérida, Yucatán',
            'America/Sao_Paulo' => 'Brasil — São Paulo',
            'America/Manaus' => 'Brasil — Manaus',
            'America/Rio_Branco' => 'Brasil — Rio Branco',
            'America/Noronha' => 'Brasil — Fernando de Noronha',
            'America/New_York' => 'Estados Unidos — Nueva York',
            'America/Chicago' => 'Estados Unidos — Chicago',
            'America/Denver' => 'Estados Unidos — Denver',
            'America/Los_Angeles' => 'Estados Unidos — Los Ángeles',
            'America/Phoenix' => 'Estados Unidos — Phoenix',
            'America/Anchorage' => 'Estados Unidos — Anchorage',
            'Pacific/Honolulu' => 'Estados Unidos — Honolulu, Hawái',
            'America/Toronto' => 'Canadá — Toronto',
            'America/Vancouver' => 'Canadá — Vancouver',
            'America/Edmonton' => 'Canadá — Edmonton',
            'America/St_Johns' => 'Canadá — San Juan de Terranova',
            'America/Bogota' => 'Colombia — Bogotá',
            'America/Lima' => 'Perú — Lima',
            'America/Santiago' => 'Chile — Santiago',
            'America/Argentina/Buenos_Aires' => 'Argentina — Buenos Aires',
            'America/Montevideo' => 'Uruguay — Montevideo',
            'America/La_Paz' => 'Bolivia — La Paz',
            'America/Guayaquil' => 'Ecuador — Quito y Guayaquil',
            'America/Costa_Rica' => 'Costa Rica — San José',
            'America/Guatemala' => 'Guatemala — Ciudad de Guatemala',
            'America/Panama' => 'Panamá — Ciudad de Panamá',
            'Europe/London' => 'Reino Unido — Londres',
            'Europe/Madrid' => 'España — Madrid',
            'Europe/Paris' => 'Francia — París',
            'Europe/Berlin' => 'Alemania — Berlín',
            'Europe/Rome' => 'Italia — Roma',
            'Europe/Kyiv' => 'Ucrania — Kyiv',
            'Europe/Istanbul' => 'Turquía — Estambul',
            'Africa/Cairo' => 'Egipto — El Cairo',
            'Africa/Johannesburg' => 'Sudáfrica — Johannesburgo',
            'Africa/Lagos' => 'Nigeria — Lagos',
            'Asia/Dubai' => 'Emiratos Árabes Unidos — Dubái',
            'Asia/Kolkata' => 'India — Nueva Delhi',
            'Asia/Shanghai' => 'China — Pekín / Shanghái',
            'Asia/Hong_Kong' => 'China — Hong Kong',
            'Asia/Tokyo' => 'Japón — Tokio',
            'Asia/Seoul' => 'Corea del Sur — Seúl',
            'Asia/Singapore' => 'Singapur — Singapur',
            'Asia/Bangkok' => 'Tailandia — Bangkok',
            'Asia/Jakarta' => 'Indonesia — Yakarta',
            'Australia/Perth' => 'Australia — Perth',
            'Australia/Adelaide' => 'Australia — Adelaida',
            'Australia/Sydney' => 'Australia — Sídney',
            'Pacific/Auckland' => 'Nueva Zelanda — Auckland',
            'UTC' => 'UTC — Tiempo universal coordinado',
        ];
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if (($settings['enable_world_time'] ?? 'yes') !== 'yes') return;
        $cities = $settings['city_list'] ?? [];
        if (!is_array($cities) || !$cities) return;
        $cities = array_slice($cities, 0, 10);

        wp_enqueue_style('wtew-style', plugins_url('../assets/css/world-time.css', __FILE__), [], '2.1.1');
        wp_enqueue_script('wtew-script', plugins_url('../assets/js/world-time.js', __FILE__), [], '2.1.1', true);

        $options = self::get_timezone_options();
        $instance_id = 'wt-container-' . $this->get_id();
        echo '<section class="wt-container" id="' . esc_attr($instance_id) . '" aria-label="Hora actual en distintas localidades">';
        echo '<span class="wt-heading">Hora actual en:</span>';
        echo '<div class="wt-scroll" tabindex="0" aria-label="Desliza horizontalmente para consultar las localidades">';
        echo '<div class="wt-city-list">';

        foreach ($cities as $index => $item) {
            $timezone = isset($item['city_timezone']) ? sanitize_text_field($item['city_timezone']) : '';
            if (!$timezone || !isset($options[$timezone]) || !in_array($timezone, \DateTimeZone::listIdentifiers(), true)) continue;
            $label = !empty($item['city_label']) ? sanitize_text_field($item['city_label']) : $options[$timezone];
            $clock_id = 'wt-clock-' . $this->get_id() . '-' . $index;
            echo '<div class="wt-city-item">';
            echo '<span class="wt-city-label">' . esc_html($label) . '</span>';
            echo '<span class="wt-time-display" id="' . esc_attr($clock_id) . '" data-timezone="' . esc_attr($timezone) . '">--:--</span>';
            echo '<span class="wt-date-display" data-date-for="' . esc_attr($clock_id) . '">--</span>';
            echo '</div>';
        }
        echo '</div></div></section>';
    }
}
