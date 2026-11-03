<?php
/*
Plugin Name: World Time Elementor Widget
Description: Franja editorial de hora local para localidades internacionales en Elementor.
Version: 2.1.1
Author: El Ambientalista Post
Text Domain: eap-world-time
*/

if (!defined('ABSPATH')) exit;

function wtew_register_widget($widgets_manager) {
    require_once __DIR__ . '/widgets/world-time-elementor.php';
    $widgets_manager->register(new \WTEW\Widgets\World_Time_Elementor());
}
add_action('elementor/widgets/register', 'wtew_register_widget');
