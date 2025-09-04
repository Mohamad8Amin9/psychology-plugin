<?php
add_action('add_meta_boxes', 'psychology_test_add_meta_box');

// Load admin.js for meta box functionality
add_action('admin_enqueue_scripts', function($hook) {
    global $post_type;
    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'psychology_test') {
        wp_enqueue_style('psychology-test-admin-style', plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css', [], '1.0.4');
        wp_enqueue_script('psychology-test-admin-script', plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js', ['jquery'], '1.0.4', true);
    }
});

function psychology_test_add_meta_box() {
    add_meta_box(
        'psychology_test_questions',
        'سوالات تست',
        'psychology_test_meta_box_content',
        'psychology_test',
        'normal',
        'high'
    );
}

function psychology_test_meta_box_content($post) {
    $questions = get_post_meta($post->ID, '_psychology_test_questions', true);
    if (!is_array($questions)) $questions = [];

    // Debug: نمایش تعداد سوالات
    if (current_user_can('manage_options')) {
        echo '<!-- Debug: تعداد سوالات: ' . count($questions) . ' -->';
    }

    $settings = get_post_meta($post->ID, '_psychology_test_settings', true);
    $results_settings = get_post_meta($post->ID, '_psychology_test_results', true);
    $per_page = isset($settings['per_page']) ? intval($settings['per_page']) : 1;
    $time_limit = isset($settings['time_limit']) ? intval($settings['time_limit']) : 0;
    $required_mode = isset($settings['required_mode']) ? $settings['required_mode'] : 'optional';
    // Font settings
    $title_font = isset($settings['title_font']) ? $settings['title_font'] : '';
    $title_font_file = isset($settings['title_font_file']) ? $settings['title_font_file'] : '';
    $title_font_name = isset($settings['title_font_name']) ? $settings['title_font_name'] : '';
    $title_weight = isset($settings['title_weight']) ? $settings['title_weight'] : 'bold';
    
    $question_font = isset($settings['question_font']) ? $settings['question_font'] : '';
    $question_font_file = isset($settings['question_font_file']) ? $settings['question_font_file'] : '';
    $question_font_name = isset($settings['question_font_name']) ? $settings['question_font_name'] : '';
    $question_weight = isset($settings['question_weight']) ? $settings['question_weight'] : 'bold';
    
    $answer_font = isset($settings['answer_font']) ? $settings['answer_font'] : '';
    $answer_font_file = isset($settings['answer_font_file']) ? $settings['answer_font_file'] : '';
    $answer_font_name = isset($settings['answer_font_name']) ? $settings['answer_font_name'] : '';
    $answer_weight = isset($settings['answer_weight']) ? $settings['answer_weight'] : 'normal';
    
    $button_font = isset($settings['button_font']) ? $settings['button_font'] : '';
    $button_font_file = isset($settings['button_font_file']) ? $settings['button_font_file'] : '';
    $button_font_name = isset($settings['button_font_name']) ? $settings['button_font_name'] : '';
    
    $body_font = isset($settings['body_font']) ? $settings['body_font'] : '';
    $body_font_file = isset($settings['body_font_file']) ? $settings['body_font_file'] : '';
    $body_font_name = isset($settings['body_font_name']) ? $settings['body_font_name'] : '';
    
    // Legacy font settings (for backward compatibility)
    $custom_font_url = isset($settings['custom_font_url']) ? $settings['custom_font_url'] : '';
    $font_weight = isset($settings['font_weight']) ? $settings['font_weight'] : 'normal';
    $font_style = isset($settings['font_style']) ? $settings['font_style'] : 'normal';
    $primary_color = isset($settings['primary_color']) ? $settings['primary_color'] : '#0d6efd';
    $secondary_color = isset($settings['secondary_color']) ? $settings['secondary_color'] : '#0dcaf0';
    $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#212529';
    $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#f8f9fa';
    $color_type = isset($settings['color_type']) ? $settings['color_type'] : 'gradient';

    echo '<div class="psychology-tabs psychology-test-admin">
        <ul class="tab-nav">
            <li class="active" data-tab="content-tab">📄 محتوا</li>
            <li data-tab="style-tab">🎨 استایل</li>
            <li data-tab="results-tab">📊 نتایج آزمون</li>
            <li data-tab="settings-tab">⚙️ تنظیمات</li>
            <li data-tab="import-export-tab">📥 وارد/برون‌ریزی</li>
        </ul>

        <div class="tab-content active" id="content-tab">
            <div id="questions-container">';
    
    foreach ($questions as $q_index => $question) {
        $is_required = isset($question['required']) ? $question['required'] : false;
        echo '<div class="question-group" data-q="' . $q_index . '">
            <h4>سوال ' . ($q_index + 1) . '</h4>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <input type="text" name="questions[' . $q_index . '][text]" value="' . esc_attr($question['text']) . '" placeholder="متن سوال" style="flex:1;" />
                <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:#666;">
                    <input type="checkbox" name="questions[' . $q_index . '][required]" value="1" ' . ($is_required ? 'checked' : '') . ' class="question-required-checkbox" ' . ($required_mode !== 'custom' ? 'disabled' : '') . '>
                    ضروری
                </label>
            </div>
            <div class="answers-container">';

        if (!empty($question['answers'])) {
            foreach ($question['answers'] as $a_index => $answer) {
                echo '<div class="answer-row">
                        <input type="text" name="questions[' . $q_index . '][answers][' . $a_index . '][text]" value="' . esc_attr($answer['text']) . '" placeholder="پاسخ" />
                        <input type="text" name="questions[' . $q_index . '][answers][' . $a_index . '][letter]" value="' . esc_attr($answer['letter']) . '" placeholder="حرف" />
                        <input type="number" name="questions[' . $q_index . '][answers][' . $a_index . '][score]" value="' . esc_attr($answer['score']) . '" placeholder="امتیاز" />
                        <button type="button" class="remove-answer"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"><path d="M3.726563 3.023438L3.023438 3.726563L7.292969 8L3.023438 12.269531L3.726563 12.980469L8 8.707031L12.269531 12.980469L12.980469 12.269531L8.707031 8L12.980469 3.726563L12.269531 3.023438L8 7.292969Z" fill="#FFFFFF" /></svg></button>
                    </div>';
            }
        }

        echo '</div>
            <button type="button" class="add-answer">افزودن پاسخ</button>
            <button type="button" class="remove-question"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"><path d="M3.726563 3.023438L3.023438 3.726563L7.292969 8L3.023438 12.269531L3.726563 12.980469L8 8.707031L12.269531 12.980469L12.980469 12.269531L8.707031 8L12.980469 3.726563L12.269531 3.023438L8 7.292969Z" /></svg></button>
            <button type="button" class="duplicate-question">📄 کپی سوال</button>
        </div>';
    }

    echo '</div>
        <button type="button" id="add-question">افزودن سوال جدید <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M12,2C6.5,2,2,6.5,2,12s4.5,10,10,10s10-4.5,10-10S17.5,2,12,2z M16,13h-3v3c0,0.6-0.4,1-1,1s-1-0.4-1-1v-3H8c-0.6,0-1-0.4-1-1s0.4-1,1-1h3V8c0-0.6,0.4-1,1-1s1,0.4,1,1v3h3c0.6,0,1,0.4,1,1S16.6,13,16,13z" fill="#FFFFFF" /></svg></button>
        </div>

        <div class="tab-content" id="style-tab">
            <div class="style-sub-tabs">
                <ul class="sub-tab-nav">
                    <li class="active" data-sub-tab="font-sub-tab">فونت</li>
                    <li data-sub-tab="question-theme-sub-tab">قالب سوالات</li>
                    <li data-sub-tab="color-sub-tab">رنگ‌بندی</li>
                </ul>
                
                <div class="sub-tab-content active" id="font-sub-tab">
                    <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">سیستم مدیریت فونت</h3>
                    <p style="color:#666;margin-bottom:20px;">در این بخش می‌توانید فونت‌های مختلف را برای بخش‌های مختلف آزمون تنظیم کنید. از سیستم رسانه وردپرس برای آپلود فونت‌های سفارشی استفاده کنید.</p>
                    
                    <div class="font-settings">
                        <!-- فونت عنوان اصلی آزمون -->
                        <div class="setting-row">
                            <label for="psychology_test_title_font">فونت عنوان اصلی:</label>
                            <div class="setting-input">
                                <div class="font-selector">
                                    <select id="psychology_test_title_font" name="psychology_test_settings[title_font]" class="font-select">
                                        <option value="">انتخاب فونت...</option>
                                        <option value="inherit"' . (isset($settings['title_font']) && $settings['title_font'] === 'inherit' ? ' selected' : '') . '>فونت پیش‌فرض پیشخوان</option>
                                        <option value="Arial"' . (isset($settings['title_font']) && $settings['title_font'] === 'Arial' ? ' selected' : '') . '>Arial</option>
                                        <option value="Tahoma"' . (isset($settings['title_font']) && $settings['title_font'] === 'Tahoma' ? ' selected' : '') . '>Tahoma</option>
                                        <option value="Times New Roman"' . (isset($settings['title_font']) && $settings['title_font'] === 'Times New Roman' ? ' selected' : '') . '>Times New Roman</option>
                                        <option value="Georgia"' . (isset($settings['title_font']) && $settings['title_font'] === 'Georgia' ? ' selected' : '') . '>Georgia</option>
                                        <option value="Verdana"' . (isset($settings['title_font']) && $settings['title_font'] === 'Verdana' ? ' selected' : '') . '>Verdana</option>
                                        <option value="custom"' . (isset($settings['title_font']) && $settings['title_font'] === 'custom' ? ' selected' : '') . '>فونت سفارشی</option>
                                    </select>
                                    <button type="button" class="button button-secondary upload-font-btn" data-target="title_font" data-font-type="title">آپلود فونت</button>
                                </div>
                                <input type="hidden" id="psychology_test_title_font_file" name="psychology_test_settings[title_font_file]" value="' . esc_attr(isset($settings['title_font_file']) ? $settings['title_font_file'] : '') . '">
                                <input type="hidden" id="psychology_test_title_font_name" name="psychology_test_settings[title_font_name]" value="' . esc_attr(isset($settings['title_font_name']) ? $settings['title_font_name'] : '') . '">
                                <small>فونت مورد نظر برای عنوان اصلی آزمون را انتخاب کنید</small>
                            </div>
                        </div>

                        <!-- فونت سوالات -->
                        <div class="setting-row">
                            <label for="psychology_test_question_font">فونت سوالات:</label>
                            <div class="setting-input">
                                <div class="font-selector">
                                    <select id="psychology_test_question_font" name="psychology_test_settings[question_font]" class="font-select">
                                        <option value="">انتخاب فونت...</option>
                                        <option value="inherit"' . (isset($settings['question_font']) && $settings['question_font'] === 'inherit' ? ' selected' : '') . '>فونت پیش‌فرض پیشخوان</option>
                                        <option value="Arial"' . (isset($settings['question_font']) && $settings['question_font'] === 'Arial' ? ' selected' : '') . '>Arial</option>
                                        <option value="Tahoma"' . (isset($settings['question_font']) && $settings['question_font'] === 'Tahoma' ? ' selected' : '') . '>Tahoma</option>
                                        <option value="Times New Roman"' . (isset($settings['question_font']) && $settings['question_font'] === 'Times New Roman' ? ' selected' : '') . '>Times New Roman</option>
                                        <option value="Georgia"' . (isset($settings['question_font']) && $settings['question_font'] === 'Georgia' ? ' selected' : '') . '>Georgia</option>
                                        <option value="Verdana"' . (isset($settings['question_font']) && $settings['question_font'] === 'Verdana' ? ' selected' : '') . '>Verdana</option>
                                        <option value="custom"' . (isset($settings['question_font']) && $settings['question_font'] === 'custom' ? ' selected' : '') . '>فونت سفارشی</option>
                                    </select>
                                    <button type="button" class="button button-secondary upload-font-btn" data-target="question_font" data-font-type="question">آپلود فونت</button>
                                </div>
                                <input type="hidden" id="psychology_test_question_font_file" name="psychology_test_settings[question_font_file]" value="' . esc_attr(isset($settings['question_font_file']) ? $settings['question_font_file'] : '') . '">
                                <input type="hidden" id="psychology_test_question_font_name" name="psychology_test_settings[question_font_name]" value="' . esc_attr(isset($settings['question_font_name']) ? $settings['question_font_name'] : '') . '">
                                <small>فونت مورد نظر برای متن سوالات را انتخاب کنید</small>
                            </div>
                        </div>

                        <!-- فونت گزینه‌های پاسخ -->
                        <div class="setting-row">
                            <label for="psychology_test_answer_font">فونت گزینه‌های پاسخ:</label>
                            <div class="setting-input">
                                <div class="font-selector">
                                    <select id="psychology_test_answer_font" name="psychology_test_settings[answer_font]" class="font-select">
                                        <option value="">انتخاب فونت...</option>
                                        <option value="inherit"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'inherit' ? ' selected' : '') . '>فونت پیش‌فرض پیشخوان</option>
                                        <option value="Arial"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'Arial' ? ' selected' : '') . '>Arial</option>
                                        <option value="Tahoma"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'Tahoma' ? ' selected' : '') . '>Tahoma</option>
                                        <option value="Times New Roman"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'Times New Roman' ? ' selected' : '') . '>Times New Roman</option>
                                        <option value="Georgia"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'Georgia' ? ' selected' : '') . '>Georgia</option>
                                        <option value="Verdana"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'Verdana' ? ' selected' : '') . '>Verdana</option>
                                        <option value="custom"' . (isset($settings['answer_font']) && $settings['answer_font'] === 'custom' ? ' selected' : '') . '>فونت سفارشی</option>
                                    </select>
                                    <button type="button" class="button button-secondary upload-font-btn" data-target="answer_font" data-font-type="answer">آپلود فونت</button>
                                </div>
                                <input type="hidden" id="psychology_test_answer_font_file" name="psychology_test_settings[answer_font_file]" value="' . esc_attr(isset($settings['answer_font_file']) ? $settings['answer_font_file'] : '') . '">
                                <input type="hidden" id="psychology_test_answer_font_name" name="psychology_test_settings[answer_font_name]" value="' . esc_attr(isset($settings['answer_font_name']) ? $settings['answer_font_name'] : '') . '">
                                <small>فونت مورد نظر برای گزینه‌های پاسخ را انتخاب کنید</small>
                            </div>
                        </div>

                        <!-- فونت دکمه‌ها -->
                        <div class="setting-row">
                            <label for="psychology_test_button_font">فونت دکمه‌ها:</label>
                            <div class="setting-input">
                                <div class="font-selector">
                                    <select id="psychology_test_button_font" name="psychology_test_settings[button_font]" class="font-select">
                                        <option value="">انتخاب فونت...</option>
                                        <option value="inherit"' . (isset($settings['button_font']) && $settings['button_font'] === 'inherit' ? ' selected' : '') . '>فونت پیش‌فرض پیشخوان</option>
                                        <option value="Arial"' . (isset($settings['button_font']) && $settings['button_font'] === 'Arial' ? ' selected' : '') . '>Arial</option>
                                        <option value="Tahoma"' . (isset($settings['button_font']) && $settings['button_font'] === 'Tahoma' ? ' selected' : '') . '>Tahoma</option>
                                        <option value="Times New Roman"' . (isset($settings['button_font']) && $settings['button_font'] === 'Times New Roman' ? ' selected' : '') . '>Times New Roman</option>
                                        <option value="Georgia"' . (isset($settings['button_font']) && $settings['button_font'] === 'Georgia' ? ' selected' : '') . '>Georgia</option>
                                        <option value="Verdana"' . (isset($settings['button_font']) && $settings['button_font'] === 'Verdana' ? ' selected' : '') . '>Verdana</option>
                                        <option value="custom"' . (isset($settings['button_font']) && $settings['button_font'] === 'custom' ? ' selected' : '') . '>فونت سفارشی</option>
                                    </select>
                                    <button type="button" class="button button-secondary upload-font-btn" data-target="button_font" data-font-type="button">آپلود فونت</button>
                                </div>
                                <input type="hidden" id="psychology_test_button_font_file" name="psychology_test_settings[button_font_file]" value="' . esc_attr(isset($settings['button_font_file']) ? $settings['button_font_file'] : '') . '">
                                <input type="hidden" id="psychology_test_button_font_name" name="psychology_test_settings[button_font_name]" value="' . esc_attr(isset($settings['button_font_name']) ? $settings['button_font_name'] : '') . '">
                                <small>فونت مورد نظر برای دکمه‌های آزمون را انتخاب کنید</small>
                            </div>
                        </div>

                        <!-- فونت متون عمومی -->
                        <div class="setting-row">
                            <label for="psychology_test_body_font">فونت متون عمومی:</label>
                            <div class="setting-input">
                                <div class="font-selector">
                                    <select id="psychology_test_body_font" name="psychology_test_settings[body_font]" class="font-select">
                                        <option value="">انتخاب فونت...</option>
                                        <option value="inherit"' . (isset($settings['body_font']) && $settings['body_font'] === 'inherit' ? ' selected' : '') . '>فونت پیش‌فرض پیشخوان</option>
                                        <option value="Arial"' . (isset($settings['body_font']) && $settings['body_font'] === 'Arial' ? ' selected' : '') . '>Arial</option>
                                        <option value="Tahoma"' . (isset($settings['body_font']) && $settings['body_font'] === 'Tahoma' ? ' selected' : '') . '>Tahoma</option>
                                        <option value="Times New Roman"' . (isset($settings['body_font']) && $settings['body_font'] === 'Times New Roman' ? ' selected' : '') . '>Times New Roman</option>
                                        <option value="Georgia"' . (isset($settings['body_font']) && $settings['body_font'] === 'Georgia' ? ' selected' : '') . '>Georgia</option>
                                        <option value="Verdana"' . (isset($settings['body_font']) && $settings['body_font'] === 'Verdana' ? ' selected' : '') . '>Verdana</option>
                                        <option value="custom"' . (isset($settings['body_font']) && $settings['body_font'] === 'custom' ? ' selected' : '') . '>فونت سفارشی</button>
                                    </select>
                                    <button type="button" class="button button-secondary upload-font-btn" data-target="body_font" data-font-type="body">آپلود فونت</button>
                                </div>
                                <input type="hidden" id="psychology_test_body_font_file" name="psychology_test_settings[body_font_file]" value="' . esc_attr(isset($settings['body_font_file']) ? $settings['body_font_file'] : '') . '">
                                <input type="hidden" id="psychology_test_body_font_name" name="psychology_test_settings[body_font_name]" value="' . esc_attr(isset($settings['body_font_name']) ? $settings['body_font_name'] : '') . '">
                                <small>فونت مورد نظر برای متون عمومی و توضیحات را انتخاب کنید</small>
                            </div>
                        </div>

                        <!-- تنظیمات ضخامت فونت -->
                        <div class="setting-row">
                            <label>تنظیمات ضخامت فونت:</label>
                            <div class="setting-input">
                                <div class="font-weight-settings">
                                    <div class="weight-item">
                                        <label for="psychology_test_title_weight">عنوان:</label>
                                        <select id="psychology_test_title_weight" name="psychology_test_settings[title_weight]">
                                            <option value="normal"' . (isset($settings['title_weight']) && $settings['title_weight'] === 'normal' ? ' selected' : '') . '>معمولی</option>
                                            <option value="bold"' . (isset($settings['title_weight']) && $settings['title_weight'] === 'bold' ? ' selected' : '') . '>ضخیم</option>
                                            <option value="100"' . (isset($settings['title_weight']) && $settings['title_weight'] === '100' ? ' selected' : '') . '>100</option>
                                            <option value="200"' . (isset($settings['title_weight']) && $settings['title_weight'] === '200' ? ' selected' : '') . '>200</option>
                                            <option value="300"' . (isset($settings['title_weight']) && $settings['title_weight'] === '300' ? ' selected' : '') . '>300</option>
                                            <option value="400"' . (isset($settings['title_weight']) && $settings['title_weight'] === '400' ? ' selected' : '') . '>400</option>
                                            <option value="500"' . (isset($settings['title_weight']) && $settings['title_weight'] === '500' ? ' selected' : '') . '>500</option>
                                            <option value="600"' . (isset($settings['title_weight']) && $settings['title_weight'] === '600' ? ' selected' : '') . '>600</option>
                                            <option value="700"' . (isset($settings['title_weight']) && $settings['title_weight'] === '700' ? ' selected' : '') . '>700</option>
                                            <option value="800"' . (isset($settings['title_weight']) && $settings['title_weight'] === '800' ? ' selected' : '') . '>800</option>
                                            <option value="900"' . (isset($settings['title_weight']) && $settings['title_weight'] === '900' ? ' selected' : '') . '>900</option>
                                        </select>
                                    </div>
                                    <div class="weight-item">
                                        <label for="psychology_test_question_weight">سوالات:</label>
                                        <select id="psychology_test_question_weight" name="psychology_test_settings[question_weight]">
                                            <option value="normal"' . (isset($settings['question_weight']) && $settings['question_weight'] === 'normal' ? ' selected' : '') . '>معمولی</option>
                                            <option value="bold"' . (isset($settings['question_weight']) && $settings['question_weight'] === 'bold' ? ' selected' : '') . '>ضخیم</option>
                                            <option value="100"' . (isset($settings['question_weight']) && $settings['question_weight'] === '100' ? ' selected' : '') . '>100</option>
                                            <option value="200"' . (isset($settings['question_weight']) && $settings['question_weight'] === '200' ? ' selected' : '') . '>200</option>
                                            <option value="300"' . (isset($settings['question_weight']) && $settings['question_weight'] === '300' ? ' selected' : '') . '>300</option>
                                            <option value="400"' . (isset($settings['question_weight']) && $settings['question_weight'] === '400' ? ' selected' : '') . '>400</option>
                                            <option value="500"' . (isset($settings['question_weight']) && $settings['question_weight'] === '500' ? ' selected' : '') . '>500</option>
                                            <option value="600"' . (isset($settings['question_weight']) && $settings['question_weight'] === '600' ? ' selected' : '') . '>600</option>
                                            <option value="700"' . (isset($settings['question_weight']) && $settings['question_weight'] === '700' ? ' selected' : '') . '>700</option>
                                            <option value="800"' . (isset($settings['question_weight']) && $settings['question_weight'] === '800' ? ' selected' : '') . '>800</option>
                                            <option value="900"' . (isset($settings['question_weight']) && $settings['question_weight'] === '900' ? ' selected' : '') . '>900</option>
                                        </select>
                                    </div>
                                    <div class="weight-item">
                                        <label for="psychology_test_answer_weight">پاسخ‌ها:</label>
                                        <select id="psychology_test_answer_weight" name="psychology_test_settings[answer_weight]">
                                            <option value="normal"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === 'normal' ? ' selected' : '') . '>معمولی</option>
                                            <option value="bold"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === 'bold' ? ' selected' : '') . '>ضخیم</option>
                                            <option value="100"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '100' ? ' selected' : '') . '>100</option>
                                            <option value="200"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '200' ? ' selected' : '') . '>200</option>
                                            <option value="300"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '300' ? ' selected' : '') . '>300</option>
                                            <option value="400"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '400' ? ' selected' : '') . '>400</option>
                                            <option value="500"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '500' ? ' selected' : '') . '>500</option>
                                            <option value="600"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '600' ? ' selected' : '') . '>600</option>
                                            <option value="700"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '700' ? ' selected' : '') . '>700</option>
                                            <option value="800"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '800' ? ' selected' : '') . '>800</option>
                                            <option value="900"' . (isset($settings['answer_weight']) && $settings['answer_weight'] === '900' ? ' selected' : '') . '>900</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- پیش‌نمایش فونت‌ها -->
                        <div class="font-preview-section">
                            <h4>پیش‌نمایش فونت‌ها:</h4>
                            <div class="font-preview-container">
                                <div class="font-preview-item" id="title-font-preview">
                                    <h5>عنوان آزمون</h5>
                                    <p class="preview-text" style="font-size: 18px; font-weight: bold;">این یک عنوان نمونه است</p>
                                </div>
                                <div class="font-preview-item" id="question-font-preview">
                                    <h5>سوال</h5>
                                    <p class="preview-text" style="font-size: 16px;">این یک سوال نمونه است</p>
                                </div>
                                <div class="font-preview-item" id="answer-font-preview">
                                    <h5>گزینه پاسخ</h5>
                                    <p class="preview-text" style="font-size: 14px;">این یک گزینه پاسخ نمونه است</p>
                                </div>
                                <div class="font-preview-item" id="button-font-preview">
                                    <h5>دکمه</h5>
                                    <button class="preview-button">دکمه نمونه</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sub-tab-content" id="question-theme-sub-tab">
                    <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">قالب سوالات</h3>
                    <p style="color:#666;margin-bottom:20px;">قالب نمایش سوالات و گزینه‌های پاسخ را انتخاب کنید.</p>
                    
                    <div class="question-theme-settings">
                        <div class="setting-row">
                            <label for="psychology_test_question_theme">قالب سوالات:</label>
                            <div class="setting-input">
                                <select id="psychology_test_question_theme" name="psychology_test_settings[question_theme]" style="width:200px;">
                                    <option value="default"' . (isset($settings['question_theme']) && $settings['question_theme'] === 'default' ? ' selected' : '') . '>قالب پیش‌فرض (رادیو باتن)</option>
                                    <option value="big_five"' . (isset($settings['question_theme']) && $settings['question_theme'] === 'big_five' ? ' selected' : '') . '>قالب Big Five (دایره‌های رنگی)</option>
                                </select>
                                <small>قالب Big Five برای تست‌های شخصیت مناسب است</small>
                            </div>
                        </div>
                        
                        <div class="theme-preview" style="margin-top: 20px;">
                            <h4>پیش‌نمایش قالب‌ها:</h4>
                            
                            <div class="theme-preview-item" id="default-theme-preview" style="border: 2px solid #0073aa; padding: 15px; border-radius: 8px; margin-bottom: 15px; background: #f8f9fa;">
                                <h5 style="margin: 0 0 10px 0; color: #0073aa;">قالب پیش‌فرض</h5>
                                <p style="margin: 0 0 15px 0; font-size: 14px;">سوال نمونه: آیا شما فردی اجتماعی هستید؟</p>
                                <div class="answer-options">
                                    <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                        <input type="radio" name="preview" style="margin-left: 8px;"> کاملاً موافقم
                                    </label>
                                    <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                        <input type="radio" name="preview" style="margin-left: 8px;"> موافقم
                                    </label>
                                    <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                        <input type="radio" name="preview" style="margin-left: 8px;"> نظری ندارم
                                    </label>
                                    <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                        <input type="radio" name="preview" style="margin-left: 8px;"> مخالفم
                                    </label>
                                    <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                        <input type="radio" name="preview" style="margin-left: 8px;"> کاملاً مخالفم
                                    </label>
                                </div>
                            </div>
                            
                            <div class="theme-preview-item" id="big-five-theme-preview" style="border: 2px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 15px; background: #f8f9fa;">
                                <h5 style="margin: 0 0 10px 0; color: #666;">قالب Big Five</h5>
                                <p style="margin: 0 0 15px 0; font-size: 14px;">سوال نمونه: آیا شما فردی اجتماعی هستید؟</p>
                                <div class="big-five-options" style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                    <div class="big-five-circle" style="width: 50px; height: 50px; border-radius: 50%; background: #28a745; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; cursor: pointer; border: 3px solid #28a745;">1</div>
                                    <div class="big-five-circle" style="width: 45px; height: 45px; border-radius: 50%; background: #20c997; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; cursor: pointer; border: 3px solid #20c997;">2</div>
                                    <div class="big-five-circle" style="width: 40px; height: 40px; border-radius: 50%; background: #ffc107; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; cursor: pointer; border: 3px solid #ffc107;">3</div>
                                    <div class="big-five-circle" style="width: 45px; height: 45px; border-radius: 50%; background: #fd7e14; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; cursor: pointer; border: 3px solid #fd7e14;">4</div>
                                    <div class="big-five-circle" style="width: 50px; height: 50px; border-radius: 50%; background: #dc3545; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; cursor: pointer; border: 3px solid #dc3545;">5</div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #666;">
                                    <span>کاملاً موافقم</span>
                                    <span>کاملاً مخالفم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sub-tab-content" id="color-sub-tab">
                    <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">تنظیمات رنگ‌بندی</h3>
                    <p style="color:#666;margin-bottom:20px;">در این بخش می‌توانید رنگ‌های مختلف آزمون را مطابق سلیقه خود تنظیم کنید.</p>
                    
                    <div class="color-settings">
                        <div class="setting-row">
                            <label>رنگ اصلی:</label>
                            <div class="setting-input">
                                <div class="color-panel" data-color-type="primary">
                                    <div class="color-preview" style="width:60px;height:35px;border:2px solid #ddd;border-radius:4px;cursor:pointer;background:' . esc_attr(isset($settings['primary_color']) ? $settings['primary_color'] : '#0d6efd') . ';"></div>
                                    <input type="hidden" id="psychology_test_primary_color" name="psychology_test_settings[primary_color]" value="' . esc_attr(isset($settings['primary_color']) ? $settings['primary_color'] : '#0d6efd') . '">
                                    <input type="hidden" id="psychology_test_primary_type" name="psychology_test_settings[primary_type]" value="' . esc_attr(isset($settings['primary_type']) ? $settings['primary_type'] : 'solid') . '">
                                    <input type="hidden" id="psychology_test_primary_secondary" name="psychology_test_settings[primary_secondary]" value="' . esc_attr(isset($settings['primary_secondary']) ? $settings['primary_secondary'] : '#0dcaf0') . '">
                                </div>
                                <small>رنگ اصلی دکمه‌ها و المان‌های مهم</small>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <label>رنگ هاور:</label>
                            <div class="setting-input">
                                <div class="color-panel" data-color-type="hover">
                                    <div class="color-preview" style="width:60px;height:35px;border:2px solid #ddd;border-radius:4px;cursor:pointer;background:' . esc_attr(isset($settings['hover_color']) ? $settings['hover_color'] : '#0056b3') . ';"></div>
                                    <input type="hidden" id="psychology_test_hover_color" name="psychology_test_settings[hover_color]" value="' . esc_attr(isset($settings['hover_color']) ? $settings['hover_color'] : '#0056b3') . '">
                                    <input type="hidden" id="psychology_test_hover_type" name="psychology_test_settings[hover_type]" value="' . esc_attr(isset($settings['hover_type']) ? $settings['hover_type'] : 'solid') . '">
                                    <input type="hidden" id="psychology_test_hover_secondary" name="psychology_test_settings[hover_secondary]" value="' . esc_attr(isset($settings['hover_secondary']) ? $settings['hover_secondary'] : '#0ba5d4') . '">
                                </div>
                                <small>رنگ هنگام هاور روی دکمه‌ها</small>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <label>رنگ متن:</label>
                            <div class="setting-input">
                                <div class="color-panel" data-color-type="text">
                                    <div class="color-preview" style="width:60px;height:35px;border:2px solid #ddd;border-radius:4px;cursor:pointer;background:' . esc_attr($text_color) . ';"></div>
                                    <input type="hidden" id="psychology_test_text_color" name="psychology_test_settings[text_color]" value="' . esc_attr($text_color) . '">
                                    <input type="hidden" id="psychology_test_text_type" name="psychology_test_settings[text_type]" value="' . esc_attr(isset($settings['text_type']) ? $settings['text_type'] : 'solid') . '">
                                    <input type="hidden" id="psychology_test_text_secondary" name="psychology_test_settings[text_secondary]" value="' . esc_attr(isset($settings['text_secondary']) ? $settings['text_secondary'] : '#495057') . '">
                                </div>
                                <small>رنگ اصلی متن‌ها و عناوین</small>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <label>رنگ پس‌زمینه:</label>
                            <div class="setting-input">
                                <div class="color-panel" data-color-type="background">
                                    <div class="color-preview" style="width:60px;height:35px;border:2px solid #ddd;border-radius:4px;cursor:pointer;background:' . esc_attr($background_color) . ';"></div>
                                    <input type="hidden" id="psychology_test_background_color" name="psychology_test_settings[background_color]" value="' . esc_attr($background_color) . '">
                                    <input type="hidden" id="psychology_test_background_type" name="psychology_test_settings[background_type]" value="' . esc_attr(isset($settings['background_type']) ? $settings['background_type'] : 'solid') . '">
                                    <input type="hidden" id="psychology_test_background_secondary" name="psychology_test_settings[background_secondary]" value="' . esc_attr(isset($settings['background_secondary']) ? $settings['background_secondary'] : '#e9ecef') . '">
                                </div>
                                <small>رنگ پس‌زمینه اصلی صفحه آزمون</small>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <label>پیش‌نمایش:</label>
                            <div class="setting-input">
                                <div id="color-preview" style="padding:15px;border:1px solid #ddd;border-radius:8px;background:' . esc_attr($background_color) . ';color:' . esc_attr($text_color) . ';margin-top:10px;">
                                    <h4 style="margin:0 0 10px 0;color:' . esc_attr(isset($settings['primary_color']) ? $settings['primary_color'] : '#0d6efd') . ';">عنوان نمونه</h4>
                                    <p style="margin:0 0 10px 0;">این یک متن نمونه است که رنگ‌های انتخاب شده را نمایش می‌دهد.</p>
                                    <button id="preview-button" style="background:' . esc_attr(isset($settings['primary_color']) ? $settings['primary_color'] : '#0d6efd') . ';color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">دکمه نمونه</button>
                                </div>
                            </div>
                        </div>
                                         </div>
                 </div>
             </div>
             
             <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e2e2;">
                 <input type="submit" name="submit" id="submit-style" class="button button-primary" value="ذخیره تغییرات">
             </div>
         </div>

        <div class="tab-content" id="results-tab">
            <div class="results-sub-tabs">
                <ul class="sub-tab-nav">
                    <li class="active" data-sub-tab="calculation-sub-tab">محاسبات</li>
                    <li data-sub-tab="conditions-sub-tab">شرایط</li>
                    <li data-sub-tab="templates-sub-tab">قالب‌ها</li>
                </ul>
                
                <div class="sub-tab-content active" id="calculation-sub-tab">
                    <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">سیستم محاسبات پیشرفته</h3>
                    
                    <div class="calculation-settings">
                        <div class="setting-row">
                            <label for="psychology_test_calculation_type">نوع محاسبه:</label>
                            <div class="setting-input">
                                <select id="psychology_test_calculation_type" name="psychology_test_results[calculation_type]" style="width:200px;">
                                    <option value="simple_sum"' . (isset($results_settings['calculation_type']) && $results_settings['calculation_type'] === 'simple_sum' ? ' selected' : '') . '>جمع ساده امتیازات</option>
                                    <option value="weighted_average"' . (isset($results_settings['calculation_type']) && $results_settings['calculation_type'] === 'weighted_average' ? ' selected' : '') . '>میانگین وزنی</option>
                                    <option value="percentage"' . (isset($results_settings['calculation_type']) && $results_settings['calculation_type'] === 'percentage' ? ' selected' : '') . '>درصدی</option>
                                    <option value="mbti_style"' . (isset($results_settings['calculation_type']) && $results_settings['calculation_type'] === 'mbti_style' ? ' selected' : '') . '>سبک MBTI</option>
                                    <option value="big_five"' . (isset($results_settings['calculation_type']) && $results_settings['calculation_type'] === 'big_five' ? ' selected' : '') . '>Big Five</option>
                                    <option value="custom_formula"' . (isset($results_settings['calculation_type']) && $results_settings['calculation_type'] === 'custom_formula' ? ' selected' : '') . '>فرمول سفارشی</option>
                                </select>
                            </div>
                        </div>

                        <div class="setting-row" id="custom-formula-section" style="display:none;">
                            <label for="psychology_test_custom_formula">فرمول سفارشی:</label>
                            <div class="setting-input">
                                <textarea id="psychology_test_custom_formula" name="psychology_test_results[custom_formula]" rows="4" style="width:100%;" placeholder="مثال: (A_score * 0.4) + (B_score * 0.3) + (C_score * 0.3)">' . esc_textarea(isset($results_settings['custom_formula']) ? $results_settings['custom_formula'] : '') . '</textarea>
                                <small>از متغیرهای A_score, B_score, C_score و عملگرهای ریاضی استفاده کنید</small>
                            </div>
                        </div>

                        <div class="setting-row">
                            <label for="psychology_test_result_page">صفحه نمایش نتیجه:</label>
                            <div class="setting-input">
                                <select id="psychology_test_result_page" name="psychology_test_results[result_page_id]" style="width:300px;">
                                    <option value="">انتخاب کنید...</option>
                                    ' . str_replace('<select', '<option', wp_dropdown_pages([
                                        'echo' => 0,
                                        'selected' => isset($results_settings['result_page_id']) ? $results_settings['result_page_id'] : '',
                                        'show_option_none' => 'انتخاب کنید...',
                                        'option_none_value' => ''
                                    ])) . '
                                </select>
                                <small>صفحه‌ای که نتیجه آزمون در آن نمایش داده می‌شود</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sub-tab-content" id="conditions-sub-tab">
                    <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">شرایط و بازه‌بندی نتایج</h3>
                    
                    <div id="result-conditions-container">
                        ' . (isset($results_settings['conditions']) ? psychology_test_render_conditions($results_settings['conditions']) : '') . '
                    </div>
                    
                    <button type="button" id="add-result-condition" class="button button-secondary" style="margin-top:15px;">
                        ➕ افزودن شرط جدید
                    </button>
                </div>

                <div class="sub-tab-content" id="templates-sub-tab">
                    <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">قالب‌های آماده</h3>
                    
                    <div class="template-presets">
                        <div class="template-preset" data-template="mbti">
                            <h4>🔤 قالب MBTI</h4>
                            <p>برای تست‌های شخصیت‌شناسی MBTI</p>
                            <button type="button" class="button button-secondary apply-template" data-template="mbti">اعمال قالب</button>
                        </div>
                        
                        <div class="template-preset" data-template="big_five">
                            <h4>🌟 قالب Big Five</h4>
                            <p>برای تست‌های پنج عامل شخصیت</p>
                            <button type="button" class="button button-secondary apply-template" data-template="big_five">اعمال قالب</button>
                        </div>
                        
                        <div class="template-preset" data-template="iq_test">
                            <h4>🧠 قالب تست هوش</h4>
                            <p>برای تست‌های هوش و استعداد</p>
                            <button type="button" class="button button-secondary apply-template" data-template="iq_test">اعمال قالب</button>
                        </div>
                        
                        <div class="template-preset" data-template="personality">
                            <h4>👤 قالب شخصیت</h4>
                            <p>برای تست‌های شخصیت عمومی</p>
                            <button type="button" class="button button-secondary apply-template" data-template="personality">اعمال قالب</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e2e2;">
                <input type="submit" name="submit" id="submit-results" class="button button-primary" value="ذخیره تغییرات">
             </div>
         </div>

        <div class="tab-content" id="settings-tab">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:8px 0;width:180px;"><label for="psychology_test_per_page">تعداد تست در هر صفحه:</label></td>
                    <td style="padding:8px 0;">
                        <input type="number" min="1" id="psychology_test_per_page" name="psychology_test_settings[per_page]" value="' . esc_attr($per_page) . '" style="width:80px;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0;width:180px;"><label for="psychology_test_time_limit">زمان آزمون (بر حسب ثانیه):</label></td>
                    <td style="padding:8px 0;">
                        <input type="number" min="0" id="psychology_test_time_limit" name="psychology_test_settings[time_limit]" value="' . esc_attr($time_limit) . '" style="width:100px;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0;width:180px;"><label for="psychology_test_required_mode">اجباری بودن آزمون:</label></td>
                    <td style="padding:8px 0;">
                        <select id="psychology_test_required_mode" name="psychology_test_settings[required_mode]" style="width:200px;" onchange="toggleRequiredOptions()">
                            <option value="optional"' . ($required_mode === 'optional' ? ' selected' : '') . '>اختیاری</option>
                            <option value="required"' . ($required_mode === 'required' ? ' selected' : '') . '>ضروری</option>
                            <option value="custom"' . ($required_mode === 'custom' ? ' selected' : '') . '>سفارشی</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e2e2;">
                <input type="submit" name="submit" id="submit-settings" class="button button-primary" value="ذخیره تغییرات">
            </div>
        </div>

        <div class="tab-content" id="import-export-tab">
            <h3 style="margin:0 0 20px 0;color:#333;border-bottom:2px solid #0073aa;padding-bottom:10px;">📥 وارد کردن / 📤 برون‌ریزی سوالات</h3>
            
            <div class="import-export-sections">
                <!-- Import Section -->
                <div class="import-section" style="margin-bottom: 30px;">
                    <h4 style="color:#0073aa;margin-bottom:15px;">📥 وارد کردن سوالات</h4>
                    
                    <div class="import-options">
                        <div class="import-option" style="margin-bottom: 20px;">
                            <label style="display:block;margin-bottom:10px;font-weight:bold;">انتخاب نوع وارد کردن:</label>
                            <select id="import_type" style="width:200px;margin-bottom:10px;">
                                <option value="mbti_text">متن MBTI (تشخیص خودکار)</option>
                                <option value="json">فایل JSON</option>
                                <option value="csv">فایل CSV</option>
                            </select>
                        </div>
                        
                        <div id="mbti_text_import" class="import-method">
                            <label style="display:block;margin-bottom:10px;font-weight:bold;">متن سوالات MBTI:</label>
                            <textarea id="mbti_questions_text" rows="15" style="width:100%;font-family:monospace;font-size:12px;" placeholder="سوالات خود را اینجا وارد کنید... مثال:
1. شما در مهمانی‌ها معمولاً:
   A) با افراد زیادی صحبت می‌کنید. (E)
   B) با چند نفر محدود و صمیمی تعامل دارید. (I)
2. زمان خستگی:
   A) با بودن در جمع انرژی می‌گیرید. (E)
   B) با تنها بودن و استراحت. (I)"></textarea>
                            <p style="color:#666;font-size:12px;margin-top:5px;">سیستم به طور خودکار سوالات، گزینه‌ها و حروف MBTI را تشخیص می‌دهد.</p>
                        </div>
                        
                        <div id="json_import" class="import-method" style="display:none;">
                            <label style="display:block;margin-bottom:10px;font-weight:bold;">فایل JSON:</label>
                            <input type="file" id="json_file" accept=".json" style="margin-bottom:10px;">
                            <p style="color:#666;font-size:12px;">فایل JSON با ساختار سوالات را انتخاب کنید.</p>
                        </div>
                        
                        <div id="csv_import" class="import-method" style="display:none;">
                            <label style="display:block;margin-bottom:10px;font-weight:bold;">فایل CSV:</label>
                            <input type="file" id="csv_file" accept=".csv" style="margin-bottom:10px;">
                            <p style="color:#666;font-size:12px;">فایل CSV با ستون‌های: سوال، گزینه A، حرف A، گزینه B، حرف B</p>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <button type="button" id="import_questions" class="button button-primary" style="margin-right:10px;">📥 وارد کردن سوالات</button>
                            <button type="button" id="preview_import" class="button button-secondary">👁️ پیش‌نمایش</button>
                        </div>
                    </div>
                </div>
                
                <!-- Export Section -->
                <div class="export-section">
                    <h4 style="color:#0073aa;margin-bottom:15px;">📤 برون‌ریزی سوالات</h4>
                    
                    <div class="export-options">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block;margin-bottom:10px;font-weight:bold;">فرمت برون‌ریزی:</label>
                            <select id="export_format" style="width:200px;margin-bottom:10px;">
                                <option value="json">JSON (کامل)</option>
                                <option value="csv">CSV (جدولی)</option>
                                <option value="mbti_text">متن MBTI</option>
                            </select>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <button type="button" id="export_questions" class="button button-primary">📤 برون‌ریزی سوالات</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Preview Section -->
            <div id="import_preview" style="display:none;margin-top:30px;padding:20px;background:#f8f9fa;border:1px solid #ddd;border-radius:8px;">
                <h4 style="margin:0 0 15px 0;color:#0073aa;">👁️ پیش‌نمایش سوالات وارد شده</h4>
                <div id="preview_content"></div>
                <div style="margin-top:15px;">
                    <button type="button" id="confirm_import" class="button button-primary">✅ تایید و وارد کردن</button>
                    <button type="button" id="cancel_import" class="button button-secondary" style="margin-right:10px;">❌ لغو</button>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e2e2;">
                <input type="submit" name="submit" id="submit-import-export" class="button button-primary" value="ذخیره تغییرات">
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle required checkboxes based on required mode
        function toggleRequiredOptions() {
            var requiredMode = $(\'#psychology_test_required_mode\').val();
            var $checkboxes = $(\'.question-required-checkbox\');
            
            if (requiredMode === \'custom\') {
                $checkboxes.prop(\'disabled\', false);
            } else {
                $checkboxes.prop(\'disabled\', true);
                if (requiredMode === \'required\') {
                    $checkboxes.prop(\'checked\', true);
                } else {
                    $checkboxes.prop(\'checked\', false);
                }
            }
        }
        
        // Initial call
        toggleRequiredOptions();
    });
    </script>
    </div>';
}

add_action('save_post', 'psychology_test_save_meta_box_data');

// تابع نمایش شرایط نتیجه
function psychology_test_render_conditions($conditions = []) {
    if (!is_array($conditions)) $conditions = [];
    
    $html = '';
    foreach ($conditions as $index => $condition) {
        $html .= '<div class="result-condition" data-condition="' . $index . '" style="border:1px solid #ddd;padding:15px;margin-bottom:15px;border-radius:8px;background:#f9f9f9;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                <h4 style="margin:0;">شرط ' . ($index + 1) . '</h4>
                <button type="button" class="remove-condition" style="background:#dc3545;color:white;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;">حذف</button>
            </div>
            
            <div class="condition-settings">
                <div class="setting-row">
                    <label>نوع شرط:</label>
                    <select name="psychology_test_results[conditions][' . $index . '][type]" style="width:150px;">
                        <option value="score_range"' . (isset($condition['type']) && $condition['type'] === 'score_range' ? ' selected' : '') . '>بازه امتیاز</option>
                        <option value="percentage"' . (isset($condition['type']) && $condition['type'] === 'percentage' ? ' selected' : '') . '>درصد</option>
                        <option value="letter_count"' . (isset($condition['type']) && $condition['type'] === 'letter_count' ? ' selected' : '') . '>تعداد حروف</option>
                        <option value="custom"' . (isset($condition['type']) && $condition['type'] === 'custom' ? ' selected' : '') . '>سفارشی</option>
                    </select>
                </div>
                
                <div class="setting-row">
                    <label>شرط:</label>
                    <select name="psychology_test_results[conditions][' . $index . '][operator]" style="width:100px;">
                        <option value=">="' . (isset($condition['operator']) && $condition['operator'] === '>=' ? ' selected' : '') . '>>=</option>
                        <option value=">"' . (isset($condition['operator']) && $condition['operator'] === '>' ? ' selected' : '') . '>></option>
                        <option value="<="' . (isset($condition['operator']) && $condition['operator'] === '<=' ? ' selected' : '') . '><=</option>
                        <option value="<"' . (isset($condition['operator']) && $condition['operator'] === '<' ? ' selected' : '') . '><</option>
                        <option value="=="' . (isset($condition['operator']) && $condition['operator'] === '==' ? ' selected' : '') . '>==</option>
                        <option value="!="' . (isset($condition['operator']) && $condition['operator'] === '!=' ? ' selected' : '') . '>!=</option>
                        <option value="between"' . (isset($condition['operator']) && $condition['operator'] === 'between' ? ' selected' : '') . '>بین</option>
                    </select>
                    <input type="number" name="psychology_test_results[conditions][' . $index . '][value1]" value="' . esc_attr(isset($condition['value1']) ? $condition['value1'] : '') . '" placeholder="مقدار 1" style="width:100px;">
                    <input type="number" name="psychology_test_results[conditions][' . $index . '][value2]" value="' . esc_attr(isset($condition['value2']) ? $condition['value2'] : '') . '" placeholder="مقدار 2" style="width:100px;">
                </div>
                
                <div class="setting-row">
                    <label>عنوان نتیجه:</label>
                    <input type="text" name="psychology_test_results[conditions][' . $index . '][title]" value="' . esc_attr(isset($condition['title']) ? $condition['title'] : '') . '" placeholder="مثال: شخصیت برون‌گرا" style="width:300px;">
                </div>
                
                <div class="setting-row">
                    <label>توضیحات:</label>
                    <textarea name="psychology_test_results[conditions][' . $index . '][description]" rows="3" style="width:100%;" placeholder="توضیحات کامل نتیجه...">' . esc_textarea(isset($condition['description']) ? $condition['description'] : '') . '</textarea>
                </div>
                
                <div class="setting-row">
                    <label>رنگ نتیجه:</label>
                    <input type="color" name="psychology_test_results[conditions][' . $index . '][color]" value="' . esc_attr(isset($condition['color']) ? $condition['color'] : '#0073aa') . '" style="width:60px;">
                </div>
            </div>
        </div>';
    }
    
    return $html;
}

// تابع محاسبه نتیجه بر اساس نوع محاسبه
function psychology_test_calculate_result($answers, $calculation_type, $custom_formula = '') {
    $result = [];
    
    switch ($calculation_type) {
        case 'simple_sum':
            $result = psychology_test_simple_sum($answers);
            break;
        case 'weighted_average':
            $result = psychology_test_weighted_average($answers);
            break;
        case 'percentage':
            $result = psychology_test_percentage_calculation($answers);
            break;
        case 'mbti_style':
            $result = psychology_test_mbti_calculation($answers);
            break;
        case 'big_five':
            $result = psychology_test_big_five_calculation($answers);
            break;
        case 'custom_formula':
            $result = psychology_test_custom_formula_calculation($answers, $custom_formula);
            break;
    }
    
    return $result;
}

// محاسبه جمع ساده
function psychology_test_simple_sum($answers) {
    $scores = [];
    foreach ($answers as $answer) {
        $letter = strtoupper(trim($answer['letter']));
        if (!isset($scores[$letter])) {
            $scores[$letter] = 0;
        }
        $scores[$letter] += intval($answer['score']);
    }
    return $scores;
}

// محاسبه میانگین وزنی
function psychology_test_weighted_average($answers) {
    $scores = [];
    $counts = [];
    
    foreach ($answers as $answer) {
        $letter = strtoupper(trim($answer['letter']));
        if (!isset($scores[$letter])) {
            $scores[$letter] = 0;
            $counts[$letter] = 0;
        }
        $scores[$letter] += intval($answer['score']);
        $counts[$letter]++;
    }
    
    foreach ($scores as $letter => $score) {
        $scores[$letter] = $score / $counts[$letter];
    }
    
    return $scores;
}

// محاسبه درصدی
function psychology_test_percentage_calculation($answers) {
    $total_questions = count($answers);
    $scores = psychology_test_simple_sum($answers);
    
    foreach ($scores as $letter => $score) {
        $scores[$letter] = ($score / $total_questions) * 100;
    }
    
    return $scores;
}

// محاسبه MBTI
function psychology_test_mbti_calculation($answers) {
    $dimensions = [
        'E' => 0, 'I' => 0, // Extraversion vs Introversion
        'S' => 0, 'N' => 0, // Sensing vs Intuition
        'T' => 0, 'F' => 0, // Thinking vs Feeling
        'J' => 0, 'P' => 0  // Judging vs Perceiving
    ];
    
    foreach ($answers as $answer) {
        $letter = strtoupper(trim($answer['letter']));
        if (isset($dimensions[$letter])) {
            $dimensions[$letter] += intval($answer['score']);
        }
    }
    
    $mbti = '';
    $mbti .= ($dimensions['E'] >= $dimensions['I']) ? 'E' : 'I';
    $mbti .= ($dimensions['S'] >= $dimensions['N']) ? 'S' : 'N';
    $mbti .= ($dimensions['T'] >= $dimensions['F']) ? 'T' : 'F';
    $mbti .= ($dimensions['J'] >= $dimensions['P']) ? 'J' : 'P';
    
    // محاسبه درصدها با جلوگیری از تقسیم بر صفر
    $percentages = [];
    
    // E/I
    $total_ei = $dimensions['E'] + $dimensions['I'];
    $percentages['E/I'] = $total_ei > 0 ? round(($dimensions['E'] / $total_ei) * 100, 1) : 50.0;
    
    // S/N
    $total_sn = $dimensions['S'] + $dimensions['N'];
    $percentages['S/N'] = $total_sn > 0 ? round(($dimensions['S'] / $total_sn) * 100, 1) : 50.0;
    
    // T/F
    $total_tf = $dimensions['T'] + $dimensions['F'];
    $percentages['T/F'] = $total_tf > 0 ? round(($dimensions['T'] / $total_tf) * 100, 1) : 50.0;
    
    // J/P
    $total_jp = $dimensions['J'] + $dimensions['P'];
    $percentages['J/P'] = $total_jp > 0 ? round(($dimensions['J'] / $total_jp) * 100, 1) : 50.0;
    
    return [
        'mbti_type' => $mbti,
        'dimensions' => $dimensions,
        'percentages' => $percentages
    ];
}

// محاسبه Big Five
function psychology_test_big_five_calculation($answers) {
    $factors = [
        'O' => 0, // Openness
        'C' => 0, // Conscientiousness
        'E' => 0, // Extraversion
        'A' => 0, // Agreeableness
        'N' => 0  // Neuroticism
    ];
    
    foreach ($answers as $answer) {
        $letter = strtoupper(trim($answer['letter']));
        if (isset($factors[$letter])) {
            $factors[$letter] += intval($answer['score']);
        }
    }
    
    return [
        'factors' => $factors,
        'percentages' => array_map(function($score) {
            return round(($score / 20) * 100, 1); // فرض بر 20 سوال برای هر عامل
        }, $factors)
    ];
}

// محاسبه فرمول سفارشی
function psychology_test_custom_formula_calculation($answers, $formula) {
    $scores = psychology_test_simple_sum($answers);
    
    // جایگزینی متغیرها در فرمول
    $formula = str_replace(['A_score', 'B_score', 'C_score', 'D_score', 'E_score'], 
                          [$scores['A'] ?? 0, $scores['B'] ?? 0, $scores['C'] ?? 0, $scores['D'] ?? 0, $scores['E'] ?? 0], 
                          $formula);
    
    // محاسبه فرمول (با احتیاط)
    try {
        $result = eval('return ' . $formula . ';');
        return ['custom_result' => $result, 'formula' => $formula];
    } catch (Exception $e) {
        return ['error' => 'خطا در محاسبه فرمول: ' . $e->getMessage()];
    }
}

function psychology_test_save_meta_box_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if ('psychology_test' != get_post_type($post_id)) return $post_id;
    
    // Handle import/export operations
    if (isset($_POST['import_questions'])) {
        $import_data = $_POST['import_questions'];
        $questions = psychology_test_parse_import_data($import_data);
        update_post_meta($post_id, '_psychology_test_questions', $questions);
        return $post_id;
    }
    
    if (isset($_POST['questions'])) {
        $questions = $_POST['questions'];
        update_post_meta($post_id, '_psychology_test_questions', $questions);
    }
    if (isset($_POST['psychology_test_settings'])) {
        $settings = $_POST['psychology_test_settings'];
        update_post_meta($post_id, '_psychology_test_settings', $settings);
    }
    if (isset($_POST['psychology_test_results'])) {
        $results = $_POST['psychology_test_results'];
        update_post_meta($post_id, '_psychology_test_results', $results);
    }
    
    return $post_id;
}

// تابع پردازش متن MBTI
function psychology_test_parse_mbti_text($text) {
    $questions = [];
    $lines = explode("\n", trim($text));
    $current_question = null;
    $question_index = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // تشخیص سوال (شماره + متن)
        if (preg_match('/^(\d+)\.\s*(.+)$/', $line, $matches)) {
            if ($current_question) {
                $questions[] = $current_question;
                $question_index++;
            }
            
            $current_question = [
                'text' => trim($matches[2]),
                'answers' => [],
                'required' => false
            ];
        }
        // تشخیص گزینه (A) یا B) با حرف MBTI
        elseif (preg_match('/^([AB])\)\s*(.+?)\s*\(([EISNTFJP])\)$/', $line, $matches)) {
            if ($current_question) {
                $current_question['answers'][] = [
                    'text' => trim($matches[2]),
                    'letter' => $matches[3],
                    'score' => 1
                ];
            }
        }
        // تشخیص گزینه بدون حرف MBTI (فقط A) یا B))
        elseif (preg_match('/^([AB])\)\s*(.+)$/', $line, $matches)) {
            if ($current_question) {
                $current_question['answers'][] = [
                    'text' => trim($matches[2]),
                    'letter' => '',
                    'score' => 1
                ];
            }
        }
    }
    
    // اضافه کردن آخرین سوال
    if ($current_question) {
        $questions[] = $current_question;
    }
    
    return $questions;
}

// تابع پردازش داده‌های وارد شده
function psychology_test_parse_import_data($import_data) {
    if (isset($import_data['type']) && $import_data['type'] === 'mbti_text') {
        return psychology_test_parse_mbti_text($import_data['text']);
    }
    
    // برای JSON و CSV در آینده
    return [];
}

// تابع برون‌ریزی سوالات
function psychology_test_export_questions($questions, $format = 'json') {
    switch ($format) {
        case 'json':
            return json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        case 'csv':
            $csv = "سوال,گزینه A,حرف A,امتیاز A,گزینه B,حرف B,امتیاز B\n";
            foreach ($questions as $question) {
                $answer_a = isset($question['answers'][0]) ? $question['answers'][0] : ['text' => '', 'letter' => '', 'score' => 1];
                $answer_b = isset($question['answers'][1]) ? $question['answers'][1] : ['text' => '', 'letter' => '', 'score' => 1];
                
                $csv .= sprintf(
                    '"%s","%s","%s",%d,"%s","%s",%d' . "\n",
                    str_replace('"', '""', $question['text']),
                    str_replace('"', '""', $answer_a['text']),
                    $answer_a['letter'],
                    $answer_a['score'],
                    str_replace('"', '""', $answer_b['text']),
                    $answer_b['letter'],
                    $answer_b['score']
                );
            }
            return $csv;
            
        case 'mbti_text':
            $text = '';
            foreach ($questions as $index => $question) {
                $text .= ($index + 1) . ". " . $question['text'] . "\n";
                foreach ($question['answers'] as $answer_index => $answer) {
                    $letter = chr(65 + $answer_index); // A, B, C, ...
                    $mbti_letter = !empty($answer['letter']) ? " (" . $answer['letter'] . ")" : "";
                    $text .= "   " . $letter . ") " . $answer['text'] . $mbti_letter . "\n";
                }
                $text .= "\n";
            }
            return $text;
            
        default:
            return json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

