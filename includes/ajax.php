<?php
if (!defined('ABSPATH')) exit;

function psych_test_load_questions_cb() {
    check_ajax_referer('psych_test_nonce');

    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    if (!$post_id) {
        wp_send_json_error(['message' => 'invalid post']);
    }

    $per_page = intval(get_post_meta($post_id, '_psychology_test_per_page', true));
    if ($per_page <= 0) $per_page = 5;

    $questions = get_post_meta($post_id, '_psychology_test_questions', true);
    if (!is_array($questions)) $questions = [];

    $total       = count($questions);
    $total_pages = $per_page > 0 ? (int) ceil($total / $per_page) : 1;
    $start       = ($page - 1) * $per_page;
    $slice       = array_slice($questions, $start, $per_page);

    ob_start();
    foreach ($slice as $offset => $question) {
        $q_index  = $start + $offset;
        $page_num = (int) floor($q_index / $per_page) + 1;
        ?>
        <fieldset class="test-page-fieldset" data-page="<?php echo esc_attr($page_num); ?>" style="margin-bottom:20px;">
          <legend><strong><?php echo ($q_index + 1) . '. ' . esc_html($question['text'] ?? ''); ?></strong></legend>
          <?php if (!empty($question['answers']) && is_array($question['answers'])): ?>
            <?php foreach ($question['answers'] as $a_index => $answer):
              $answer_text   = isset($answer['text']) ? $answer['text'] : '';
              $answer_score  = isset($answer['score']) ? $answer['score'] : 0;
              $answer_letter = isset($answer['letter']) ? $answer['letter'] : '';
            ?>
              <label style="display:block; margin-bottom:5px;">
                <input
                  type="radio"
                  name="question_<?php echo esc_attr($q_index); ?>"
                  value="<?php echo esc_attr($answer_score); ?>"
                  data-letter="<?php echo esc_attr(strtoupper(trim((string)$answer_letter))); ?>"
                  required
                >
                <?php echo esc_html($answer_text); ?>
              </label>
            <?php endforeach; ?>
          <?php endif; ?>
        </fieldset>
        <?php
    }
    $html = ob_get_clean();

    wp_send_json_success([
        'html'        => $html,
        'page'        => $page,
        'total_pages' => $total_pages,
    ]);
}


add_action('wp_ajax_psych_test_load_questions', 'psych_test_load_questions_cb');
add_action('wp_ajax_nopriv_psych_test_load_questions', 'psych_test_load_questions_cb');

// AJAX handler برای محاسبه نتایج پیشرفته
function psych_test_calculate_result_cb() {
    check_ajax_referer('psych_test_nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];

    if (!$post_id || empty($answers)) {
        wp_send_json_error(['message' => 'داده‌های ناقص']);
    }

    // دریافت تنظیمات تست
    $settings = get_post_meta($post_id, '_psychology_test_settings', true);
    $results_config = get_post_meta($post_id, '_psychology_test_results', true);
    
    // محاسبه نتیجه بر اساس نوع محاسبه
    $calculation_type = isset($results_config['calculation_type']) ? $results_config['calculation_type'] : 'simple_sum';
    $custom_formula = isset($results_config['custom_formula']) ? $results_config['custom_formula'] : '';
    
    $calculated_result = psychology_test_calculate_result($answers, $calculation_type, $custom_formula);
    
    // بررسی شرایط و تعیین نتیجه نهایی
    $final_result = psychology_test_evaluate_conditions($calculated_result, $results_config);
    
    // ذخیره نتیجه در دیتابیس
    $result_id = psychology_test_save_result($post_id, $answers, $calculated_result, $final_result);
    
    wp_send_json_success([
        'result' => $final_result,
        'calculated_data' => $calculated_result,
        'result_id' => $result_id
    ]);
}

add_action('wp_ajax_psych_test_calculate_result', 'psych_test_calculate_result_cb');
add_action('wp_ajax_nopriv_psych_test_calculate_result', 'psych_test_calculate_result_cb');


