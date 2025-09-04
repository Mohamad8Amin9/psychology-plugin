<?php
/*
Plugin Name: Psychology Test
Description: پلاگین مدیریت و ساخت تست‌های روانشناسی
Version: 1.0.4
Author: محمدامین میرحیدری
*/

if (!defined('ABSPATH')) exit;

define('PSYCH_TEST_VER', '1.0.4');
define('PSYCH_TEST_DIR', plugin_dir_path(__FILE__));
define('PSYCH_TEST_URL', plugin_dir_url(__FILE__));

require_once PSYCH_TEST_DIR . 'includes/post-type.php';
require_once PSYCH_TEST_DIR . 'includes/meta-box.php';
require_once PSYCH_TEST_DIR . 'includes/shortcode.php';
require_once PSYCH_TEST_DIR . 'includes/db.php';
require_once PSYCH_TEST_DIR . 'includes/admin-columns.php';
require_once PSYCH_TEST_DIR . 'includes/ajax.php';

// حذف بارگذاری تکراری JavaScript - این کار در meta-box.php انجام می‌شود
// add_action('admin_enqueue_scripts', function () {
//     wp_enqueue_style('psychology-test-admin-style', PSYCH_TEST_URL . 'assets/css/admin.css', [], PSYCH_TEST_VER);
//     wp_enqueue_script('psychology-test-admin-script', PSYCH_TEST_URL . 'assets/js/admin.js', ['jquery'], PSYCH_TEST_VER, true);
// });

add_action('wp_enqueue_scripts', function () {
    
    wp_enqueue_style(
        'psychology-test-shortcode-style',
        PSYCH_TEST_URL . 'assets/css/shortcode.css',
        [],
        PSYCH_TEST_VER
    );

    wp_enqueue_style(
        'psychology-test-results-style',
        PSYCH_TEST_URL . 'assets/css/results.css',
        [],
        PSYCH_TEST_VER
    );

    wp_enqueue_script(
        'psychology-test-shortcode',
        PSYCH_TEST_URL . 'assets/js/shortcode.js',
        ['jquery'],
        PSYCH_TEST_VER,
        true
    );

    wp_localize_script('psychology-test-shortcode', 'PsychTest', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('psych_test_nonce'),
    ]);
});

// Single Test Render
add_filter('the_content', function ($content) {
    if (is_singular('psychology_test') && in_the_loop() && is_main_query()) {
        return do_shortcode('[psychology_test id="' . get_the_ID() . '"]');
    }
    return $content;
}, 20);
