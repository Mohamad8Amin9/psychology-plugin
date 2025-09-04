<?php
add_shortcode('psychology_test', function ($atts) {
    $atts = shortcode_atts([
        'id' => 0
    ], $atts);

    $post_id = intval($atts['id']);
    if (!$post_id) return '';

    // بررسی URL برای نمایش نتیجه
    $result_id = isset($_GET['result']) ? sanitize_text_field($_GET['result']) : '';
    if ($result_id) {
        return psychology_test_display_result_by_id($result_id, $post_id);
    }

    // بررسی ارسال فرم و پردازش نتایج
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_answers'])) {
        $result_html = psychology_test_process_results($post_id);
        
        // اضافه کردن JavaScript برای جلوگیری از reload
        $result_html .= '<script>
            // Prevent form resubmission
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>';
        
        return $result_html;
    }
    


    $questions = get_post_meta($post_id, '_psychology_test_questions', true);
    if (!is_array($questions) || empty($questions)) {
        return '<div class="psychology-test-error">
            <div class="error-icon">⚠️</div>
            <h3>خطا</h3>
            <p>سوالی برای این تست ثبت نشده است.</p>
        </div>';
    }

    $settings = get_post_meta($post_id, '_psychology_test_settings', true);
    $per_page = isset($settings['per_page']) ? max(1, intval($settings['per_page'])) : 1;
    $time_limit = isset($settings['time_limit']) ? intval($settings['time_limit']) : 0;
    $required_mode = isset($settings['required_mode']) ? $settings['required_mode'] : 'optional';
    // New Font System
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
    $font_family = isset($settings['font_family']) && !empty($settings['font_family']) ? $settings['font_family'] : 'inherit';
    $font_weight = isset($settings['font_weight']) ? $settings['font_weight'] : 'normal';
    
    // Color settings
    $primary_color = isset($settings['primary_color']) ? $settings['primary_color'] : '#0d6efd';
    $primary_type = isset($settings['primary_type']) ? $settings['primary_type'] : 'solid';
    $primary_secondary = isset($settings['primary_secondary']) ? $settings['primary_secondary'] : '#0dcaf0';
    
    $hover_color = isset($settings['hover_color']) ? $settings['hover_color'] : '#0056b3';
    $hover_type = isset($settings['hover_type']) ? $settings['hover_type'] : 'solid';
    $hover_secondary = isset($settings['hover_secondary']) ? $settings['hover_secondary'] : '#0ba5d4';
    
    $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#212529';
    $text_type = isset($settings['text_type']) ? $settings['text_type'] : 'solid';
    $text_secondary = isset($settings['text_secondary']) ? $settings['text_secondary'] : '#495057';
    
    $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#f8f9fa';
    $background_type = isset($settings['background_type']) ? $settings['background_type'] : 'solid';
    $background_secondary = isset($settings['background_secondary']) ? $settings['background_secondary'] : '#e9ecef';
    
    // قالب سوالات
    $question_theme = isset($settings['question_theme']) ? $settings['question_theme'] : 'default';
    
    $test_title = get_the_title($post_id);

    $all_letters = [];
    foreach ($questions as $question) {
        if (!empty($question['answers']) && is_array($question['answers'])) {
            foreach ($question['answers'] as $answer) {
                if (!empty($answer['letter'])) {
                    $letter = strtoupper(trim($answer['letter']));
                    if ($letter && !in_array($letter, $all_letters)) {
                        $all_letters[] = $letter;
                    }
                }
            }
        }
    }

    $total_pages = ceil(count($questions) / $per_page);
    $total_questions = count($questions);

    ob_start();
    ?>
    <!-- Mobile Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- Custom Fonts -->
    <style>
        <?php
        // Title font
        if (!empty($title_font) && $title_font === 'custom' && !empty($title_font_file) && !empty($title_font_name)) {
            $ext = pathinfo($title_font_file, PATHINFO_EXTENSION);
            $format = strtolower($ext) === 'ttf' ? 'truetype' : 
                     (strtolower($ext) === 'otf' ? 'opentype' : 
                     (strtolower($ext) === 'woff' ? 'woff' : 'woff2'));
            echo "@font-face {\n";
            echo "    font-family: '" . esc_attr($title_font_name) . "';\n";
            echo "    src: url('" . esc_url($title_font_file) . "') format('" . $format . "');\n";
            echo "    font-weight: " . esc_attr($title_weight) . ";\n";
            echo "    font-style: normal;\n";
            echo "    font-display: swap;\n";
            echo "}\n";
        }
        
        // Question font
        if (!empty($question_font) && $question_font === 'custom' && !empty($question_font_file) && !empty($question_font_name)) {
            $ext = pathinfo($question_font_file, PATHINFO_EXTENSION);
            $format = strtolower($ext) === 'ttf' ? 'truetype' : 
                     (strtolower($ext) === 'otf' ? 'opentype' : 
                     (strtolower($ext) === 'woff' ? 'woff' : 'woff2'));
            echo "@font-face {\n";
            echo "    font-family: '" . esc_attr($question_font_name) . "';\n";
            echo "    src: url('" . esc_url($question_font_file) . "') format('" . $format . "');\n";
            echo "    font-weight: " . esc_attr($question_weight) . ";\n";
            echo "    font-style: normal;\n";
            echo "    font-display: swap;\n";
            echo "}\n";
        }
        
        // Answer font
        if (!empty($answer_font) && $answer_font === 'custom' && !empty($answer_font_file) && !empty($answer_font_name)) {
            $ext = pathinfo($answer_font_file, PATHINFO_EXTENSION);
            $format = strtolower($ext) === 'ttf' ? 'truetype' : 
                     (strtolower($ext) === 'otf' ? 'opentype' : 
                     (strtolower($ext) === 'woff' ? 'woff' : 'woff2'));
            echo "@font-face {\n";
            echo "    font-family: '" . esc_attr($answer_font_name) . "';\n";
            echo "    src: url('" . esc_url($answer_font_file) . "') format('" . $format . "');\n";
            echo "    font-weight: " . esc_attr($answer_weight) . ";\n";
            echo "    font-style: normal;\n";
            echo "    font-display: swap;\n";
            echo "}\n";
        }
        
        // Button font
        if (!empty($button_font) && $button_font === 'custom' && !empty($button_font_file) && !empty($button_font_name)) {
            $ext = pathinfo($button_font_file, PATHINFO_EXTENSION);
            $format = strtolower($ext) === 'ttf' ? 'truetype' : 
                     (strtolower($ext) === 'otf' ? 'opentype' : 
                     (strtolower($ext) === 'woff' ? 'woff' : 'woff2'));
            echo "@font-face {\n";
            echo "    font-family: '" . esc_attr($button_font_name) . "';\n";
            echo "    src: url('" . esc_url($button_font_file) . "') format('" . $format . "');\n";
            echo "    font-weight: normal;\n";
            echo "    font-style: normal;\n";
            echo "    font-display: swap;\n";
            echo "}\n";
        }
        
        // Body font
        if (!empty($body_font) && $body_font === 'custom' && !empty($body_font_file) && !empty($body_font_name)) {
            $ext = pathinfo($body_font_file, PATHINFO_EXTENSION);
            $format = strtolower($ext) === 'ttf' ? 'truetype' : 
                     (strtolower($ext) === 'otf' ? 'opentype' : 
                     (strtolower($ext) === 'woff' ? 'woff' : 'woff2'));
            echo "@font-face {\n";
            echo "    font-family: '" . esc_attr($body_font_name) . "';\n";
            echo "    src: url('" . esc_url($body_font_file) . "') format('" . $format . "');\n";
            echo "    font-weight: normal;\n";
            echo "    font-style: normal;\n";
            echo "    font-display: swap;\n";
            echo "}\n";
        }
        
        // Legacy font support
        if (!empty($custom_font_url)) {
            if (preg_match('/\.(ttf|otf|woff|woff2)$/i', $custom_font_url)) {
                $ext = pathinfo($custom_font_url, PATHINFO_EXTENSION);
                $format = strtolower($ext) === 'ttf' ? 'truetype' : 
                         (strtolower($ext) === 'otf' ? 'opentype' : 
                         (strtolower($ext) === 'woff' ? 'woff' : 'woff2'));
                echo "@font-face {\n";
                echo "    font-family: '" . esc_attr($font_family) . "';\n";
                echo "    src: url('" . esc_url($custom_font_url) . "') format('" . $format . "');\n";
                echo "    font-weight: " . esc_attr($font_weight) . ";\n";
                echo "    font-style: normal;\n";
                echo "    font-display: swap;\n";
                echo "}\n";
            } else {
                echo '<link rel="stylesheet" href="' . esc_url($custom_font_url) . '">';
            }
        }
        ?>
    </style>
    
    <style>
        /* Apply custom font to all elements */
        .psychology-test-container,
        .psychology-test-container *,
        .psychology-test-container h1,
        .psychology-test-container h2,
        .psychology-test-container h3,
        .psychology-test-container h4,
        .psychology-test-container h5,
        .psychology-test-container h6,
        .psychology-test-container p,
        .psychology-test-container span,
        .psychology-test-container div,
        .psychology-test-container label,
        .psychology-test-container button,
        .psychology-test-container input,
        .psychology-test-container textarea,
        .psychology-test-container select,
        .psychology-test-container .test-title,
        .psychology-test-container .test-info-item,
        .psychology-test-container .timer-label,
        .psychology-test-container .timer-display,
        .psychology-test-container .progress-label,
        .psychology-test-container .progress-text,
        .psychology-test-container .question-text,
        .psychology-test-container .answer-text,
        .psychology-test-container .nav-btn,
        /* Title Font */
        .psychology-test-container .test-title,
        .psychology-test-container h1,
        .psychology-test-container h2,
        .psychology-test-container h3 {
            font-family: <?php 
                if (!empty($title_font) && $title_font === 'custom' && !empty($title_font_name)) {
                    echo "'" . esc_attr($title_font_name) . "'";
                } elseif (!empty($title_font) && $title_font !== 'inherit') {
                    echo $title_font;
                } else {
                    echo 'inherit';
                }
            ?> !important;
            font-weight: <?php echo esc_attr($title_weight); ?> !important;
        }
        
        /* Question Font */
        .psychology-test-container .question-text,
        .psychology-test-container .question-card h4 {
            font-family: <?php 
                if (!empty($question_font) && $question_font === 'custom' && !empty($question_font_name)) {
                    echo "'" . esc_attr($question_font_name) . "'";
                } elseif (!empty($question_font) && $question_font !== 'inherit') {
                    echo $question_font;
                } else {
                    echo 'inherit';
                }
            ?> !important;
            font-weight: <?php echo esc_attr($question_weight); ?> !important;
        }
        
        /* Answer Font */
        .psychology-test-container .answer-text,
        .psychology-test-container .answer-label {
            font-family: <?php 
                if (!empty($answer_font) && $answer_font === 'custom' && !empty($answer_font_name)) {
                    echo "'" . esc_attr($answer_font_name) . "'";
                } elseif (!empty($answer_font) && $answer_font !== 'inherit') {
                    echo $answer_font;
                } else {
                    echo 'inherit';
                }
            ?> !important;
            font-weight: <?php echo esc_attr($answer_weight); ?> !important;
        }
        
        /* Button Font */
        .psychology-test-container .nav-btn,
        .psychology-test-container .submit-btn,
        .psychology-test-container button {
            font-family: <?php 
                if (!empty($button_font) && $button_font === 'custom' && !empty($button_font_name)) {
                    echo "'" . esc_attr($button_font_name) . "'";
                } elseif (!empty($button_font) && $button_font !== 'inherit') {
                    echo $button_font;
                } else {
                    echo 'inherit';
                }
            ?> !important;
        }
        
        /* Body Font */
        .psychology-test-container,
        .psychology-test-container p,
        .psychology-test-container span,
        .psychology-test-container div,
        .psychology-test-container label,
        .psychology-test-container .test-info-item,
        .psychology-test-container .timer-label,
        .psychology-test-container .timer-display,
        .psychology-test-container .progress-label,
        .psychology-test-container .progress-text,
        .psychology-test-container .page-indicator {
            font-family: <?php 
                if (!empty($body_font) && $body_font === 'custom' && !empty($body_font_name)) {
                    echo "'" . esc_attr($body_font_name) . "'";
                } elseif (!empty($body_font) && $body_font !== 'inherit') {
                    echo $body_font;
                } else {
                    echo 'inherit';
                }
            ?> !important;
        }
        
        /* Legacy font support */
        .psychology-test-container * {
            font-family: inherit !important;
        }
        
        /* Override any existing font-family declarations */
        .psychology-test-container * {
            font-family: inherit !important;
            font-weight: inherit !important;
        }

        /* Custom Color Variables */
        .psychology-test-container {
            --bs-primary: <?php echo esc_attr($primary_color); ?>;
            --bs-primary-rgb: <?php echo esc_attr(implode(', ', sscanf($primary_color, "#%02x%02x%02x"))); ?>;
            --bs-info: <?php echo esc_attr($primary_type === 'gradient' ? $primary_secondary : $primary_color); ?>;
            --bs-info-rgb: <?php echo esc_attr(implode(', ', sscanf($primary_type === 'gradient' ? $primary_secondary : $primary_color, "#%02x%02x%02x"))); ?>;
            --bs-gray-900: <?php echo esc_attr($text_color); ?>;
            --bs-gray-100: <?php echo esc_attr($background_color); ?>;
            <?php if ($background_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($background_color); ?> 0%, <?php echo esc_attr($background_secondary); ?> 100%);
            <?php else: ?>
            background-color: <?php echo esc_attr($background_color); ?>;
            <?php endif; ?>
            color: <?php echo esc_attr($text_color); ?>;
        }

        /* Override specific color elements */
        .psychology-test-container .test-header {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .test-timer {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .progress-bar {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(90deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .timer-progress-bar {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(90deg, <?php echo esc_attr($primary_color); ?>, <?php echo esc_attr($primary_secondary); ?>) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .badge-primary,
        .psychology-test-container .badge-success {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .question-number {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .answer-option input[type="radio"]:checked + .answer-label {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .answer-option input[type="radio"]:checked + .answer-label .answer-marker::after {
            <?php if ($primary_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .btn-primary:hover,
        .psychology-test-container .btn-success:hover,
        .psychology-test-container .btn-secondary:hover {
            <?php if ($hover_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($hover_color); ?> 0%, <?php echo esc_attr($hover_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($hover_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .submit-btn:hover {
            <?php if ($hover_type === 'gradient'): ?>
            background: linear-gradient(135deg, <?php echo esc_attr($hover_color); ?> 0%, <?php echo esc_attr($hover_secondary); ?> 100%) !important;
            <?php else: ?>
            background: <?php echo esc_attr($hover_color); ?> !important;
            <?php endif; ?>
        }

        .psychology-test-container .test-result li {
            <?php if ($primary_type === 'gradient'): ?>
            border-image: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_secondary); ?> 100%) 1 !important;
            <?php else: ?>
            border-right: 4px solid <?php echo esc_attr($primary_color); ?> !important;
            <?php endif; ?>
        }

        <?php if ($question_theme === 'big_five'): ?>
        /* قالب Big Five - دایره‌های رنگی */
        .psychology-test-container .big-five-answers {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .psychology-test-container .big-five-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .psychology-test-container .big-five-circle:nth-child(1) {
            border-color: #28a745;
        }

        .psychology-test-container .big-five-circle:nth-child(2) {
            border-color: #20c997;
        }

        .psychology-test-container .big-five-circle:nth-child(3) {
            border-color: #ffc107;
        }

        .psychology-test-container .big-five-circle:nth-child(4) {
            border-color: #fd7e14;
        }

        .psychology-test-container .big-five-circle:nth-child(5) {
            border-color: #dc3545;
        }

        .psychology-test-container .big-five-circle:hover,
        .psychology-test-container .big-five-circle.selected {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .psychology-test-container .big-five-circle:nth-child(1):hover,
        .psychology-test-container .big-five-circle:nth-child(1).selected {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .psychology-test-container .big-five-circle:nth-child(2):hover,
        .psychology-test-container .big-five-circle:nth-child(2).selected {
            background: linear-gradient(135deg, #20c997, #17a2b8);
        }

        .psychology-test-container .big-five-circle:nth-child(3):hover,
        .psychology-test-container .big-five-circle:nth-child(3).selected {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .psychology-test-container .big-five-circle:nth-child(4):hover,
        .psychology-test-container .big-five-circle:nth-child(4).selected {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
        }

        .psychology-test-container .big-five-circle:nth-child(5):hover,
        .psychology-test-container .big-five-circle:nth-child(5).selected {
            background: linear-gradient(135deg, #dc3545, #6f42c1);
        }

        .psychology-test-container .big-five-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .psychology-test-container .big-five-circle input[type="radio"] {
            display: none;
        }

        /* مخفی کردن رادیو باتن‌های معمولی در قالب Big Five */
        .psychology-test-container.big-five-theme .answer-option {
            display: none;
        }

        .psychology-test-container.big-five-theme .big-five-answers {
            display: flex;
        }
        <?php endif; ?>
    </style>
    
    <div class="psychology-test-container <?php echo $question_theme === 'big_five' ? 'big-five-theme' : ''; ?>" dir="rtl">
        <div class="container">
            <!-- Test Header -->
            <div class="card test-header mb-4">
                <div class="card-body text-center">
                    <h2 class="test-title mb-3"><?php echo esc_html($test_title); ?></h2>
                    <div class="test-info d-flex justify-content-center flex-wrap">
                        <span class="badge badge-primary test-info-item me-2">
                            <i class="icon">📋</i>
                            تعداد سوالات: <?php echo $total_questions; ?>
                        </span>
                        <?php if ($time_limit > 0): ?>
                        <span class="badge badge-warning test-info-item">
                            <i class="icon">⏱️</i>
                            زمان: <?php echo sprintf('%d دقیقه', ceil($time_limit/60)); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Timer Section -->
            <?php if ($time_limit > 0): ?>
            <div class="card test-timer mb-4">
                <div class="card-body text-center">
                    <div class="timer-label mb-2">
                        <i class="timer-icon">⏰</i>
                        زمان باقی‌مانده
                    </div>
                    <div id="timer-display" class="timer-display mb-3">
                        <?php echo sprintf('%02d:%02d', floor($time_limit/60), $time_limit%60); ?>
                    </div>
                    <div class="timer-progress">
                        <div class="timer-progress-bar" id="timer-progress-bar"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Progress Section -->
            <div class="card test-progress mb-4">
                <div class="card-body">
                    <div class="progress-label mb-2">پیشرفت آزمون</div>
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="test-progress-fill" 
                             role="progressbar" 
                             style="width: 0%">
                        </div>
                    </div>
                    <div class="progress-text text-center">
                        <span id="answered-count">0</span> از <?php echo $total_questions; ?> سوال پاسخ داده شده
                    </div>
                </div>
            </div>

            <!-- Test Form -->
            <form class="card psychology-test-form" 
                  data-test-id="<?php echo $post_id; ?>" 
                  data-letters="<?php echo esc_attr(json_encode($all_letters)); ?>" 
                  data-per-page="<?php echo esc_attr($per_page); ?>" 
                  data-time-limit="<?php echo esc_attr($time_limit); ?>"
                  data-total-questions="<?php echo $total_questions; ?>"
                  data-required-mode="<?php echo esc_attr($required_mode); ?>"
                  data-required-questions="<?php echo esc_attr(json_encode(array_map(function($q) { return isset($q['required']) ? $q['required'] : false; }, $questions))); ?>">
                
                <!-- Questions Container -->
                <div class="card-body questions-container">
                    <?php foreach ($questions as $q_index => $question): 
                        $pp = (isset($per_page) && intval($per_page) > 0) ? intval($per_page) : 5;
                        $page = (int) floor($q_index / $pp) + 1;
                        $question_number = $q_index + 1;
                    ?>
                        <div class="card question-card" 
                             data-question="<?php echo $q_index; ?>"
                             data-page="<?php echo esc_attr($page); ?>"
                             style="<?php echo ($page === 1) ? '' : 'display:none;'; ?>">
                            
                            <div class="card-body">
                                <div class="question-header d-flex align-items-start">
                                    <span class="question-number badge badge-primary me-3"><?php echo $question_number; ?></span>
                                    <h3 class="question-text mb-3"><?php echo esc_html($question['text'] ?? ''); ?></h3>
                                </div>

                                <?php if (!empty($question['answers']) && is_array($question['answers'])): ?>
                                    <?php if ($question_theme === 'big_five'): ?>
                                        <!-- قالب Big Five - دایره‌های رنگی -->
                                        <div class="big-five-answers">
                                            <?php foreach ($question['answers'] as $a_index => $answer): 
                                                $answer_text = isset($answer['text']) ? $answer['text'] : '';
                                                $answer_score = isset($answer['score']) ? $answer['score'] : 0;
                                                $answer_letter = isset($answer['letter']) ? $answer['letter'] : '';
                                                $answer_id = "q{$q_index}_a{$a_index}";
                                            ?>
                                                <div class="big-five-circle" data-answer-id="<?php echo $answer_id; ?>">
                                                    <input 
                                                        type="radio"
                                                        id="<?php echo $answer_id; ?>"
                                                        name="question_<?php echo esc_attr($q_index); ?>"
                                                        value="<?php echo esc_attr($answer_score); ?>"
                                                        data-letter="<?php echo esc_attr(strtoupper(trim((string)$answer_letter))); ?>" 
                                                        required
                                                    >
                                                    <span><?php echo $a_index + 1; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="big-five-labels">
                                            <span><?php echo esc_html($question['answers'][0]['text'] ?? 'کاملاً موافقم'); ?></span>
                                            <span><?php echo esc_html($question['answers'][count($question['answers'])-1]['text'] ?? 'کاملاً مخالفم'); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <!-- قالب پیش‌فرض - رادیو باتن -->
                                        <div class="answers-container">
                                            <?php foreach ($question['answers'] as $a_index => $answer): 
                                                $answer_text = isset($answer['text']) ? $answer['text'] : '';
                                                $answer_score = isset($answer['score']) ? $answer['score'] : 0;
                                                $answer_letter = isset($answer['letter']) ? $answer['letter'] : '';
                                                $answer_id = "q{$q_index}_a{$a_index}";
                                            ?>
                                                <div class="form-check answer-option">
                                                    <input 
                                                        type="radio"
                                                        class="form-check-input"
                                                        id="<?php echo $answer_id; ?>"
                                                        name="question_<?php echo esc_attr($q_index); ?>"
                                                        value="<?php echo esc_attr($answer_score); ?>"
                                                        data-letter="<?php echo esc_attr(strtoupper(trim((string)$answer_letter))); ?>" 
                                                        required
                                                    >
                                                    <label for="<?php echo $answer_id; ?>" class="form-check-label answer-label">
                                                        <span class="answer-marker"></span>
                                                        <span class="answer-text"><?php echo esc_html($answer_text); ?></span>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navigation Section -->
                <div class="card-footer test-navigation">
                    <div class="navigation-buttons d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-secondary nav-btn prev-btn" id="prev-btn" disabled>
                            <i class="btn-icon">→</i>
                            قبلی
                        </button>
                        
                        <div class="page-indicator badge badge-light">
                            صفحه <span class="current-page">1</span> از <?php echo $total_pages; ?>
                        </div>
                        
                        <button type="button" class="btn btn-primary nav-btn next-btn" id="next-btn" <?php echo $total_pages <= 1 ? 'disabled' : ''; ?>>
                            بعدی
                            <i class="btn-icon">←</i>
                        </button>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg submit-btn w-100" id="submit-btn" style="display:none;">
                        <i class="btn-icon">✓</i>
                        ثبت نهایی پاسخ‌ها
                    </button>
                </div>
            </form>

            <!-- Test Result -->
            <div class="card test-result mt-4" id="test-result">
                <div class="card-body">
                    <div class="result-loading text-center" style="display:none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">در حال محاسبه...</span>
                        </div>
                        <p class="mt-3">در حال محاسبه نتیجه...</p>
                    </div>
                    
                    <div class="result-content" style="display:none;">
                        <div class="result-header text-center mb-4">
                            <h2 class="result-title"></h2>
                            <p class="result-subtitle"></p>
                        </div>
                        
                        <div class="result-details">
                            <div class="result-description mb-4"></div>
                            <div class="result-charts"></div>
                            <div class="result-breakdown"></div>
                        </div>
                        
                        <div class="result-actions text-center mt-4">
                            <button type="button" class="btn btn-primary retake-test">تست مجدد</button>
                            <button type="button" class="btn btn-secondary share-result">اشتراک‌گذاری</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

// تابع پردازش نتایج
function psychology_test_process_results($post_id) {
    $answers = json_decode(stripslashes($_POST['test_answers']), true);
    $time_expired = isset($_POST['time_expired']) && $_POST['time_expired'] === '1';
    
    if (!is_array($answers)) {
        return '<div class="psychology-test-error">
            <div class="error-icon">⚠️</div>
            <h3>خطا</h3>
            <p>داده‌های ارسالی نامعتبر است.</p>
        </div>';
    }

    // دریافت تنظیمات نتایج
    $settings = get_post_meta($post_id, '_psychology_test_settings', true);
    $results_settings = get_post_meta($post_id, '_psychology_test_results', true);
    
    // محاسبه نتیجه
    $calculation_type = isset($results_settings['calculation_type']) ? $results_settings['calculation_type'] : 'simple_sum';
    $custom_formula = isset($results_settings['custom_formula']) ? $results_settings['custom_formula'] : '';
    
    $calculated_data = psychology_test_calculate_result($answers, $calculation_type, $custom_formula);
    
    // ارزیابی شرایط و تعیین نتیجه نهایی
    $final_result = psychology_test_evaluate_conditions($calculated_data, $results_settings);
    
    // ذخیره نتیجه در دیتابیس
    $unique_id = psychology_test_save_result($post_id, $answers, $calculated_data, $final_result);
    
    // ایجاد URL جدید برای نتیجه
    $result_url = add_query_arg('result', $unique_id, get_permalink($post_id));
    
    // نمایش نتیجه
    ob_start();
    ?>
    <div class="psychology-test-container">
        <div class="card test-result">
            <div class="card-body">
                <div class="result-content">
                    <div class="result-header text-center mb-4">
                        <h2 class="result-title" style="color: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>">
                            <?php echo esc_html($final_result['title'] ?? 'نتیجه آزمون'); ?>
                        </h2>
                        
                        <?php if ($time_expired): ?>
                            <div class="alert alert-warning" style="background: linear-gradient(135deg, #fef3c7, #fed7aa); border: 2px solid #f59e0b; color: #92400e;">
                                <div class="d-flex align-items-center">
                                    <span style="font-size: 1.5rem; margin-left: 0.75rem;">⏰</span>
                                    <div>
                                        <strong>زمان تمام شد!</strong><br>
                                        <small>پاسخ‌های شما تا این لحظه ثبت شده است.</small>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center mb-4">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                                <h3>آزمون تکمیل شد!</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="result-details">
                        <div class="result-description mb-4">
                            <?php echo wp_kses_post($final_result['description'] ?? ''); ?>
                        </div>
                        
                        <?php if (isset($calculated_data['mbti_type'])): ?>
                            <!-- نمودار MBTI -->
                            <div class="mbti-chart mb-4">
                                <h4>نوع شخصیت شما: <strong style="color: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>"><?php echo esc_html($calculated_data['mbti_type']); ?></strong></h4>
                                <div class="mbti-dimensions">
                                    <?php foreach ($calculated_data['percentages'] as $dimension => $percentage): ?>
                                        <div class="dimension-bar mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo esc_html($dimension); ?></span>
                                                <span><?php echo esc_html($percentage); ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo esc_attr($percentage); ?>%; background: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php elseif (isset($calculated_data['factors'])): ?>
                            <!-- نمودار Big Five -->
                            <div class="big-five-chart mb-4">
                                <h4>پنج عامل شخصیت شما:</h4>
                                <div class="factors-chart">
                                    <?php foreach ($calculated_data['factors'] as $factor => $score): 
                                        $percentage = $calculated_data['percentages'][$factor];
                                        $factor_name = psychology_test_get_factor_name($factor);
                                    ?>
                                        <div class="factor-item mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo esc_html($factor_name); ?></span>
                                                <span><?php echo esc_html($percentage); ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo esc_attr($percentage); ?>%; background: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- نمودار ساده -->
                            <div class="simple-chart mb-4">
                                <h4>امتیازات شما:</h4>
                                <div class="scores-chart">
                                    <?php 
                                    $sorted_scores = $calculated_data;
                                    arsort($sorted_scores);
                                    foreach ($sorted_scores as $letter => $score): 
                                    ?>
                                        <div class="score-item mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span><?php echo esc_html($letter); ?></span>
                                                <span><?php echo esc_html($score); ?> امتیاز</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="result-actions text-center mt-4">
                        <button type="button" class="btn btn-primary retake-test">تست مجدد</button>
                        <button type="button" class="btn btn-secondary" onclick="shareResult()">اشتراک‌گذاری</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // تغییر URL به صفحه نتیجه
    if (window.history && window.history.pushState) {
        window.history.pushState(null, '', '<?php echo esc_js($result_url); ?>');
    }
    
    function shareResult() {
        const text = 'نتیجه تست من: <?php echo esc_js($final_result['title'] ?? 'نتیجه آزمون'); ?>';
        const url = '<?php echo esc_js($result_url); ?>';
        
        if (navigator.share) {
            navigator.share({
                title: 'نتیجه تست روانشناسی',
                text: text,
                url: url
            });
        } else {
            navigator.clipboard.writeText(text + '\n' + url).then(() => {
                alert('لینک نتیجه کپی شد!');
            });
        }
    }
    </script>
    <?php
    return ob_get_clean();
}

// تابع کمکی برای نام‌گذاری عوامل Big Five
function psychology_test_get_factor_name($factor) {
    $factor_names = [
        'O' => 'باز بودن به تجربه',
        'C' => 'وجدان‌مندی',
        'E' => 'برون‌گرایی',
        'A' => 'توافق‌پذیری',
        'N' => 'روان‌رنجوری'
    ];
    return $factor_names[$factor] ?? $factor;
}

// تابع ارزیابی شرایط و تعیین نتیجه نهایی
function psychology_test_evaluate_conditions($calculated_data, $results_settings) {
    $conditions = isset($results_settings['conditions']) ? $results_settings['conditions'] : [];
    
    if (empty($conditions)) {
        // اگر شرطی تعریف نشده، نتیجه پیش‌فرض
        return [
            'title' => 'نتیجه آزمون',
            'description' => 'آزمون شما با موفقیت تکمیل شد.',
            'color' => '#0073aa'
        ];
    }
    
    foreach ($conditions as $condition) {
        if (psychology_test_check_condition($calculated_data, $condition)) {
            return [
                'title' => $condition['title'] ?? 'نتیجه آزمون',
                'description' => $condition['description'] ?? '',
                'color' => $condition['color'] ?? '#0073aa'
            ];
        }
    }
    
    // اگر هیچ شرطی برآورده نشد
    return [
        'title' => 'نتیجه آزمون',
        'description' => 'آزمون شما با موفقیت تکمیل شد.',
        'color' => '#0073aa'
    ];
}

// تابع بررسی شرط
function psychology_test_check_condition($data, $condition) {
    $type = $condition['type'] ?? 'score_range';
    $operator = $condition['operator'] ?? '>=';
    $value1 = $condition['value1'] ?? 0;
    $value2 = $condition['value2'] ?? 0;
    
    switch ($type) {
        case 'score_range':
            $total_score = array_sum($data);
            return psychology_test_compare_values($total_score, $operator, $value1, $value2);
            
        case 'percentage':
            $total_score = array_sum($data);
            $max_possible = count($data) * 5; // فرض بر حداکثر 5 امتیاز
            $percentage = $max_possible > 0 ? ($total_score / $max_possible) * 100 : 0;
            return psychology_test_compare_values($percentage, $operator, $value1, $value2);
            
        case 'letter_count':
            $letter_counts = [];
            foreach ($data as $letter => $score) {
                $letter_counts[$letter] = $score;
            }
            if (empty($letter_counts)) {
                return false;
            }
            $max_letter = array_keys($letter_counts, max($letter_counts))[0];
            return psychology_test_compare_values($letter_counts[$max_letter], $operator, $value1, $value2);
            
        default:
            return false;
    }
}

// تابع مقایسه مقادیر
function psychology_test_compare_values($value, $operator, $value1, $value2) {
    switch ($operator) {
        case '>=':
            return $value >= $value1;
        case '>':
            return $value > $value1;
        case '<=':
            return $value <= $value1;
        case '<':
            return $value < $value1;
        case '==':
            return $value == $value1;
        case '!=':
            return $value != $value1;
        case 'between':
            return $value >= $value1 && $value <= $value2;
        default:
            return false;
    }
}

// تابع ذخیره نتیجه در دیتابیس
function psychology_test_save_result($post_id, $answers, $calculated_data, $final_result) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'psychology_test_results';
    
    // ایجاد شناسه یکتا برای نتیجه
    $unique_id = psychology_test_generate_unique_id();
    
    $data = [
        'test_id' => $post_id,
        'user_id' => get_current_user_id() ?: 0,
        'answers' => json_encode($answers),
        'calculated_result' => json_encode($calculated_data),
        'final_result' => json_encode($final_result),
        'unique_id' => $unique_id,
        'created_at' => current_time('mysql')
    ];
    
    $wpdb->insert($table_name, $data);
    
    return $unique_id;
}

// تابع نمایش نتیجه بر اساس ID
function psychology_test_display_result_by_id($result_id, $post_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'psychology_test_results';
    
    // جستجو بر اساس unique_id یا id
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE (unique_id = %s OR id = %d) AND test_id = %d",
        $result_id,
        intval($result_id),
        $post_id
    ));
    
    if (!$result) {
        return '<div class="psychology-test-error">
            <div class="error-icon">⚠️</div>
            <h3>نتیجه یافت نشد</h3>
            <p>نتیجه مورد نظر شما یافت نشد یا منقضی شده است.</p>
            <a href="' . esc_url(remove_query_arg('result')) . '" class="btn btn-primary">بازگشت به آزمون</a>
        </div>';
    }
    
    $final_result = json_decode($result->final_result, true);
    $calculated_data = json_decode($result->calculated_result, true);
    
    // دریافت تنظیمات تست
    $settings = get_post_meta($post_id, '_psychology_test_settings', true);
    
    ob_start();
    ?>
    <div class="psychology-test-container">
        <div class="card test-result">
            <div class="card-body">
                <div class="result-content">
                    <div class="result-header text-center mb-4">
                        <h2 class="result-title" style="color: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>">
                            <?php echo esc_html($final_result['title'] ?? 'نتیجه آزمون'); ?>
                        </h2>
                        
                        <div class="text-center mb-4">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                            <h3>نتیجه آزمون شما</h3>
                            <p class="text-muted">تاریخ آزمون: <?php echo esc_html(date_i18n('j F Y', strtotime($result->created_at))); ?></p>
                        </div>
                    </div>
                    
                    <div class="result-details">
                        <div class="result-description mb-4">
                            <?php echo wp_kses_post($final_result['description'] ?? ''); ?>
                        </div>
                        
                        <?php if (isset($calculated_data['mbti_type'])): ?>
                            <!-- نمودار MBTI -->
                            <div class="mbti-chart mb-4">
                                <h4>نوع شخصیت شما: <strong style="color: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>"><?php echo esc_html($calculated_data['mbti_type']); ?></strong></h4>
                                <div class="mbti-dimensions">
                                    <?php foreach ($calculated_data['percentages'] as $dimension => $percentage): ?>
                                        <div class="dimension-bar mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo esc_html($dimension); ?></span>
                                                <span><?php echo esc_html($percentage); ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo esc_attr($percentage); ?>%; background: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php elseif (isset($calculated_data['factors'])): ?>
                            <!-- نمودار Big Five -->
                            <div class="big-five-chart mb-4">
                                <h4>پنج عامل شخصیت شما:</h4>
                                <div class="factors-chart">
                                    <?php foreach ($calculated_data['factors'] as $factor => $score): ?>
                                        <div class="factor-bar mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo esc_html(psychology_test_get_factor_name($factor)); ?></span>
                                                <span><?php echo esc_html($score); ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo esc_attr($score); ?>%; background: <?php echo esc_attr($final_result['color'] ?? '#0073aa'); ?>"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- نمودار ساده -->
                            <div class="simple-chart mb-4">
                                <h4>امتیازات شما:</h4>
                                <div class="scores-chart">
                                    <?php 
                                    $sorted_scores = $calculated_data;
                                    arsort($sorted_scores);
                                    foreach ($sorted_scores as $letter => $score): 
                                    ?>
                                        <div class="score-item mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span><?php echo esc_html($letter); ?></span>
                                                <span><?php echo esc_html($score); ?> امتیاز</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="result-actions text-center mt-4">
                        <a href="<?php echo esc_url(remove_query_arg('result')); ?>" class="btn btn-primary">تست مجدد</a>
                        <button type="button" class="btn btn-secondary" onclick="shareResult()">اشتراک‌گذاری</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function shareResult() {
        const text = 'نتیجه تست من: <?php echo esc_js($final_result['title'] ?? 'نتیجه آزمون'); ?>';
        const url = window.location.href;
        
        if (navigator.share) {
            navigator.share({
                title: 'نتیجه تست روانشناسی',
                text: text,
                url: url
            });
        } else {
            navigator.clipboard.writeText(text + '\n' + url).then(() => {
                alert('لینک نتیجه کپی شد!');
            });
        }
    }
    </script>
    <?php
    return ob_get_clean();
}

