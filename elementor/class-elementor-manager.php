<?php
namespace SJ_Reviews\Elementor;

defined('ABSPATH') || exit;

class ElementorManager {

    public static function register_category(\Elementor\Elements_Manager $manager): void {
        $manager->add_category('sj-reviews', [
            'title' => __('SJ Reviews', 'sj-reviews'),
            'icon'  => 'fa fa-star',
        ]);
    }
}
