<?php

namespace ARP\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if (!defined('ABSPATH')) {
    exit;
}

class Autor_Ubicacion_Widget extends Widget_Base {

    public function get_name() {
        return 'autor_ubicacion_widget';
    }

    public function get_title() {
        return __('Autor y Ubicación', 'arp-elementor');
    }

    public function get_icon() {
        return 'eicon-person';
    }

    public function get_categories() {
        return ['elambientalista'];
    }

    /*
    |--------------------------------------------------------------------------
    | CONTROLES
    |--------------------------------------------------------------------------
    */

    protected function register_controls() {

        /*
        |--------------------------------------------------------------------------
        | CONTENIDO
        |--------------------------------------------------------------------------
        */

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Contenido', 'arp-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'foto',
            [
                'label' => __('Foto personalizada', 'arp-elementor'),
                'type'  => Controls_Manager::MEDIA,
            ]
        );

        $this->add_control(
            'nombre_forzado',
            [
                'label' => __('Nombre personalizado', 'arp-elementor'),
                'type'  => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'ubicacion_forzada',
            [
                'label' => __('Ubicación personalizada', 'arp-elementor'),
                'type'  => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'fecha_forzada',
            [
                'label' => __('Texto fecha personalizado', 'arp-elementor'),
                'type'  => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'show_role',
            [
                'label' => __('Mostrar rol del autor', 'arp-elementor'),
                'type'  => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_creditos',
            [
                'label' => __('Mostrar créditos', 'arp-elementor'),
                'type'  => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_colaboradores',
            [
                'label' => __('Mostrar colaboradores', 'arp-elementor'),
                'type'  => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'foto_size',
            [
                'label' => __('Tamaño foto', 'arp-elementor'),
                'type'  => Controls_Manager::NUMBER,
                'default' => 55,
                'min' => 30,
                'max' => 200,
            ]
        );

        $this->end_controls_section();

        /*
        |--------------------------------------------------------------------------
        | ESTILO GENERAL
        |--------------------------------------------------------------------------
        */

        $this->start_controls_section(
            'section_style_general',
            [
                'label' => __('Caja General', 'arp-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_gap',
            [
                'label' => __('Separación', 'arp-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .arp-autor-inner' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'box_padding',
            [
                'label' => __('Padding', 'arp-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .arp-autor-widget' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'selector' => '{{WRAPPER}} .arp-autor-widget',
            ]
        );

        $this->add_control(
            'box_radius',
            [
                'label' => __('Border Radius', 'arp-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .arp-autor-widget' =>
                        'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
                ],
            ]
        );

        $this->end_controls_section();

        /*
        |--------------------------------------------------------------------------
        | ESTILO NOMBRE
        |--------------------------------------------------------------------------
        */

        $this->start_controls_section(
            'section_style_nombre',
            [
                'label' => __('Nombre del autor', 'arp-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'nombre_color',
            [
                'label' => __('Color', 'arp-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .arp-por-nombre' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'nombre_typography',
                'selector' => '{{WRAPPER}} .arp-por-nombre',
            ]
        );

        $this->end_controls_section();

        /*
        |--------------------------------------------------------------------------
        | ESTILO FECHA
        |--------------------------------------------------------------------------
        */

        $this->start_controls_section(
            'section_style_fecha',
            [
                'label' => __('Fecha', 'arp-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'fecha_color',
            [
                'label' => __('Color fecha normal', 'arp-elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#777777',
                'selectors' => [
                    '{{WRAPPER}} .arp-fecha' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'fecha_breaking_color',
            [
                'label' => __('Color Breaking News', 'arp-elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#cc0000',
                'selectors' => [
                    '{{WRAPPER}} .arp-fecha-breaking' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'fecha_typography',
                'selector' => '{{WRAPPER}} .arp-fecha',
            ]
        );

        $this->end_controls_section();

        /*
        |--------------------------------------------------------------------------
        | ESTILO CRÉDITOS
        |--------------------------------------------------------------------------
        */

        $this->start_controls_section(
            'section_style_creditos',
            [
                'label' => __('Créditos', 'arp-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'creditos_color',
            [
                'label' => __('Color', 'arp-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .arp-creditos' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .arp-colaboradores' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'creditos_typography',
                'selector' => '{{WRAPPER}} .arp-creditos, {{WRAPPER}} .arp-colaboradores',
            ]
        );

        $this->end_controls_section();

    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    protected function render() {

        global $post;

        if (!$post) {
            return;
        }

        $settings = $this->get_settings_for_display();

        /*
        |--------------------------------------------------------------------------
        | DATOS BASE
        |--------------------------------------------------------------------------
        */

        $author_id = $post->post_author;

        $author_name_wp = get_the_author_meta(
            'display_name',
            $author_id
        );

        $author_avatar_wp = get_avatar_url(
            $author_id,
            ['size' => 120]
        );

        $ubicacion_meta = function_exists(
            '\ARP\Core\arp_get_post_ubicacion'
        )
            ? \ARP\Core\arp_get_post_ubicacion($post->ID)
            : '';

        $creditos_text = function_exists(
            '\ARP\Core\arp_get_post_creditos'
        )
            ? \ARP\Core\arp_get_post_creditos($post->ID)
            : '';

        $colaboradores = function_exists(
            '\ARP\Core\arp_get_post_colaboradores'
        )
            ? \ARP\Core\arp_get_post_colaboradores($post->ID)
            : [];

        $author_role_meta = get_post_meta(
            $post->ID,
            '_arp_author_role',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | BREAKING NEWS
        |--------------------------------------------------------------------------
        */

        $breaking_enabled = get_post_meta(
            $post->ID,
            '_arp_breaking_update',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | OVERRIDES
        |--------------------------------------------------------------------------
        */

        $foto = !empty($settings['foto']['url'])
            ? $settings['foto']['url']
            : $author_avatar_wp;

        $author_name = !empty($settings['nombre_forzado'])
            ? $settings['nombre_forzado']
            : $author_name_wp;

        $ubicacion = !empty($settings['ubicacion_forzada'])
            ? $settings['ubicacion_forzada']
            : $ubicacion_meta;

        $fecha_override = !empty($settings['fecha_forzada'])
            ? $settings['fecha_forzada']
            : '';

        /*
        |--------------------------------------------------------------------------
        | TOGGLES
        |--------------------------------------------------------------------------
        */

        $show_role =
            ('yes' === $settings['show_role']);

        $show_creditos =
            ('yes' === $settings['show_creditos']);

        $show_colabs =
            ('yes' === $settings['show_colaboradores']);

        /*
        |--------------------------------------------------------------------------
        | FECHAS
        |--------------------------------------------------------------------------
        */

        $fecha_class = 'arp-fecha';

        if ($fecha_override) {

            $texto_fecha = $fecha_override;

        } else {

            $pub_ts = get_the_time(
                'U',
                $post->ID
            );

            $mod_ts = get_the_modified_time(
                'U',
                $post->ID
            );

            $current_ts = current_time('U');

            $is_updated =
                ($mod_ts - $pub_ts > 60);

            if ($is_updated) {

                $diff_seconds =
                    $current_ts - $mod_ts;

                /*
                |--------------------------------------------------------------------------
                | BREAKING NEWS
                |--------------------------------------------------------------------------
                */

                if (
                    '1' === $breaking_enabled
                    && $diff_seconds <= (5 * HOUR_IN_SECONDS)
                ) {

                    $fecha_class .= ' arp-fecha-breaking';

                }

                if ($diff_seconds < HOUR_IN_SECONDS) {

                    $texto_fecha =
                        sprintf(
                            __('Actualizado hace %s', 'arp-elementor'),
                            human_time_diff(
                                $mod_ts,
                                $current_ts
                            )
                        );

                } elseif ($diff_seconds < DAY_IN_SECONDS) {

                    $texto_fecha =
                        'Actualizado hoy a las '
                        . get_the_modified_time(
                            'g:i a',
                            $post
                        );

                } else {

                    $texto_fecha =
                        'Actualizado el '
                        . get_the_modified_date(
                            'j \d\e F \d\e Y \a \l\a\s g:i a',
                            $post
                        );

                }

            } else {

                $texto_fecha =
                    'Publicado el '
                    . get_the_date(
                        'j \d\e F \d\e Y \a \l\a\s g:i a',
                        $post
                    );

            }

        }

        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */

        $foto_size = intval(
            $settings['foto_size']
        ) ?: 55;

        /*
        |--------------------------------------------------------------------------
        | RENDER
        |--------------------------------------------------------------------------
        */

        ?>

        <div
            class="arp-autor-widget"
            style="margin-top:20px;"
        >

            <div
                class="arp-autor-inner"
                style="display:flex;align-items:flex-start;"
            >

                <div
                    class="arp-autor-left"
                    style="flex:0 0 auto;"
                >

                    <img
                        src="<?php echo esc_url($foto); ?>"
                        alt="<?php echo esc_attr($author_name); ?>"
                        style="
                            width:<?php echo esc_attr($foto_size); ?>px;
                            height:<?php echo esc_attr($foto_size); ?>px;
                            object-fit:cover;
                            border-radius:50%;
                            border:1px solid #ddd;
                        "
                    >

                </div>

                <div
                    class="arp-autor-right"
                    style="flex:1 1 auto;"
                >

                    <?php if ($ubicacion): ?>

                        <div
                            class="arp-ubicacion"
                            style="
                                color:#666;
                                margin-bottom:4px;
                            "
                        >
                            <?php echo esc_html($ubicacion); ?>
                        </div>

                    <?php endif; ?>

                    <div
                        class="arp-por-nombre"
                        style="
                            font-size:1rem;
                            margin-bottom:4px;
                        "
                    >

                        Por
                        <strong>
                            <?php echo esc_html($author_name); ?>
                        </strong>

                        <?php if (
                            $show_role
                            && $author_role_meta
                        ): ?>

                            <span
                                class="arp-author-role"
                                style="color:#777;"
                            >
                                —
                                <?php
                                echo esc_html(
                                    $author_role_meta
                                );
                                ?>
                            </span>

                        <?php endif; ?>

                    </div>

                    <?php if ($texto_fecha): ?>

                        <div
                            class="<?php echo esc_attr($fecha_class); ?>"
                            aria-live="polite"
                            style="
                                font-size:0.95rem;
                                margin-bottom:6px;
                            "
                        >

                            <?php
                            echo esc_html(
                                $texto_fecha
                            );
                            ?>

                        </div>

                    <?php endif; ?>

                    <?php if (
                        $show_creditos
                        && $creditos_text
                    ): ?>

                        <div
                            class="arp-creditos"
                            style="
                                font-style:italic;
                                font-size:0.92rem;
                                margin-top:6px;
                                border-top:1px solid #eee;
                                padding-top:6px;
                            "
                        >

                            <?php
                            echo esc_html(
                                $creditos_text
                            );
                            ?>

                        </div>

                    <?php endif; ?>

                    <?php if (
                        $show_colabs
                        && !empty($colaboradores)
                    ): ?>

                        <div
                            class="arp-colaboradores"
                            style="
                                margin-top:6px;
                                font-size:0.92rem;
                            "
                        >

                            <?php

                            $out = [];

                            foreach ($colaboradores as $c) {

                                if (
                                    empty($c['name'])
                                    && empty($c['role'])
                                ) {
                                    continue;
                                }

                                $name =
                                    !empty($c['name'])
                                    ? esc_html($c['name'])
                                    : '';

                                $role =
                                    !empty($c['role'])
                                    ? esc_html($c['role'])
                                    : '';

                                $url =
                                    !empty($c['url'])
                                    ? esc_url($c['url'])
                                    : '';

                                $item = '';

                                if ($role) {
                                    $item .= $role . ': ';
                                }

                                if ($url) {

                                    $item .=
                                        '<a href="' .
                                        $url .
                                        '" target="_blank" rel="noopener noreferrer">' .
                                        $name .
                                        '</a>';

                                } else {

                                    $item .= $name;

                                }

                                $out[] = $item;

                            }

                            echo wp_kses_post(
                                implode(' · ', $out)
                            );

                            ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <?php

    }

}