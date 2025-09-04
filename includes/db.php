<?php
register_activation_hook(plugin_basename(dirname(__DIR__) . '/psychology-test.php'), function() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $prefix = $wpdb->prefix;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // جدول نتایج قدیمی
    dbDelta("CREATE TABLE {$prefix}psychology_results (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        test_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        session_token VARCHAR(64),
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");
    
    // جدول جدید برای نتایج پیشرفته
    dbDelta("CREATE TABLE {$prefix}psychology_test_results (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        test_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        answers LONGTEXT NOT NULL,
        calculated_result LONGTEXT NOT NULL,
        final_result LONGTEXT NOT NULL,
        unique_id VARCHAR(32) UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX test_id (test_id),
        INDEX user_id (user_id),
        INDEX unique_id (unique_id),
        INDEX created_at (created_at)
    ) {$charset_collate};");
});

// تابع ایجاد جدول در صورت عدم وجود
function psychology_test_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $prefix = $wpdb->prefix;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    dbDelta("CREATE TABLE IF NOT EXISTS {$prefix}psychology_test_results (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        test_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        answers LONGTEXT NOT NULL,
        calculated_result LONGTEXT NOT NULL,
        final_result LONGTEXT NOT NULL,
        unique_id VARCHAR(32) UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX test_id (test_id),
        INDEX user_id (user_id),
        INDEX unique_id (unique_id),
        INDEX created_at (created_at)
    ) {$charset_collate};");
}

// تابع آپدیت دیتابیس برای اضافه کردن ستون unique_id
function psychology_test_update_database() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'psychology_test_results';
    
    // بررسی وجود ستون unique_id
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'unique_id'");
    
    if (empty($column_exists)) {
        // اضافه کردن ستون unique_id
        $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN unique_id VARCHAR(32) UNIQUE AFTER final_result");
        $wpdb->query("ALTER TABLE {$table_name} ADD INDEX unique_id (unique_id)");
        
        // آپدیت رکوردهای موجود با unique_id
        $existing_results = $wpdb->get_results("SELECT id FROM {$table_name} WHERE unique_id IS NULL");
        foreach ($existing_results as $result) {
            $unique_id = psychology_test_generate_unique_id();
            $wpdb->update(
                $table_name,
                ['unique_id' => $unique_id],
                ['id' => $result->id]
            );
        }
    }
}

// تابع تولید شناسه یکتا
function psychology_test_generate_unique_id() {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $unique_id = '';
    
    for ($i = 0; $i < 12; $i++) {
        $unique_id .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $unique_id;
}

// اجرای تابع در بارگذاری پلاگین
add_action('init', 'psychology_test_create_tables');
add_action('init', 'psychology_test_update_database');