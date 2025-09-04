<?php
add_filter('manage_psychology_test_posts_columns', function ($columns) {
    $columns['shortcode'] = 'شورتکد';
    return $columns;
});

add_action('manage_psychology_test_posts_custom_column', function ($column, $post_id) {
    if ($column === 'shortcode') {
        $shortcode = '[psychology_test id="' . $post_id . '"]';
        echo '<code style="background:#f4f4f4;padding:6px 10px;border:1px solid #ddd;border-radius:4px;margin:0;display:inline-block;">'
            . esc_html($shortcode) .
            '</code>';
    }
}, 10, 2);
