<?php
add_action('init', function() {
    register_post_type('psychology_test', [
        'labels' => [
            'name' => 'تست‌های روانشناسی',
            'singular_name' => 'تست روانشناسی',
            'add_new' => 'افزودن تست جدید',
            'add_new_item' => 'افزودن تست جدید',
            'edit_item' => 'ویرایش تست',
            'new_item' => 'تست جدید',
            'view_item' => 'مشاهده تست',
            'search_items' => 'جستجوی تست‌ها',
            'not_found' => 'موردی یافت نشد',
            'menu_name' => 'تست روانشناسی',
        ],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => ['title', 'custom-fields'],
        'has_archive' => false,
        'capability_type' => 'post',
    ]);
});
add_filter('post_updated_messages', function ($messages) {
    global $post, $post_ID;

    if (get_post_type($post_ID) !== 'psychology_test') {
        return $messages;
    }

    $permalink    = get_permalink($post_ID);
    $preview_link = get_preview_post_link($post_ID);
    $date_label   = isset($post->post_date) ? date_i18n('j F Y ساعت H:i', strtotime($post->post_date)) : '';

    $view_html    = $permalink    ? ' <a href="' . esc_url($permalink) . '" target="_blank">نمایش</a>'       : '';
    $preview_html = $preview_link ? ' <a href="' . esc_url($preview_link) . '" target="_blank">پیش‌نمایش</a>' : '';

    $messages['psychology_test'] = [
        0  => '',
        1  => 'تست شما بروزرسانی شد.' . $view_html,
        2  => 'فیلد سفارشی بروزرسانی شد.',
        3  => 'فیلد سفارشی حذف شد.',
        4  => 'تست شما بروزرسانی شد.' . $view_html,
        5  => isset($_GET['revision']) ? sprintf('به نسخه %s بازگردانی شد.', wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6  => 'تست شما منتشر شد.' . $view_html,
        7  => 'تست شما ذخیره شد.' . $preview_html,             // معمولاً برای پیش‌نویس/در انتظار بررسی
        8  => 'تست شما ارسال شد.' . $preview_html,              // حالت «submitted»
        9  => sprintf('تست شما زمان‌بندی شد برای: <strong>%s</strong>.', $date_label) . $view_html,
        10 => 'پیش‌نویس تست بروزرسانی شد.' . $preview_html,
    ];

    return $messages;
});
