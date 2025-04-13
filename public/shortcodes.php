<?php
// สร้าง Shortcode สำหรับแสดงข้อสอบ
function exam_display_shortcode($atts) {
    $atts = shortcode_atts(array(
        'set' => '',         // ชุดข้อสอบ
        'limit' => 20,       // จำนวนข้อที่แสดง
        'random' => 'no',    // สุ่มลำดับหรือไม่
        'pagination' => 'no' // แบ่งหน้าหรือไม่
    ), $atts, 'exam');
    
    // สร้าง query arguments
    $args = array(
        'post_type' => 'exam_question',
        'posts_per_page' => intval($atts['limit']),
        'orderby' => ($atts['random'] === 'yes') ? 'rand' : 'date',
        'order' => 'ASC'
    );
    
    // กรองตาม taxonomy หากระบุ
    if (!empty($atts['set'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'exam_set',
                'field' => 'slug',
                'terms' => $atts['set']
            )
        );
    }
    
    $questions = get_posts($args);
    
    if (empty($questions)) {
        return '<p>ไม่พบข้อสอบตามเงื่อนไขที่ระบุ</p>';
    }
    
    ob_start();
    ?>
    <div class="exam-container" id="exam-container">
        <form id="exam-form" class="exam-form">
            <div class="questions-wrapper">
                <?php foreach ($questions as $index => $question) : 
                    $option_a = get_post_meta($question->ID, 'option_a', true);
                    $option_b = get_post_meta($question->ID, 'option_b', true);
                    $option_c = get_post_meta($question->ID, 'option_c', true);
                    $option_d = get_post_meta($question->ID, 'option_d', true);
                ?>
                    <div class="question-item" id="question-<?php echo $index + 1; ?>">
                        <div class="question-number">ข้อ <?php echo $index + 1; ?></div>
                        <div class="question-text"><?php echo $question->post_title; ?></div>
                        
                        <div class="options-list">
                            <div class="option">
                                <input type="radio" id="q<?php echo $question->ID; ?>_a" name="q_<?php echo $question->ID; ?>" value="a">
                                <label for="q<?php echo $question->ID; ?>_a">ก. <?php echo $option_a; ?></label>
                            </div>
                            
                            <div class="option">
                                <input type="radio" id="q<?php echo $question->ID; ?>_b" name="q_<?php echo $question->ID; ?>" value="b">
                                <label for="q<?php echo $question->ID; ?>_b">ข. <?php echo $option_b; ?></label>
                            </div>
                            
                            <div class="option">
                                <input type="radio" id="q<?php echo $question->ID; ?>_c" name="q_<?php echo $question->ID; ?>" value="c">
                                <label for="q<?php echo $question->ID; ?>_c">ค. <?php echo $option_c; ?></label>
                            </div>
                            
                            <div class="option">
                                <input type="radio" id="q<?php echo $question->ID; ?>_d" name="q_<?php echo $question->ID; ?>" value="d">
                                <label for="q<?php echo $question->ID; ?>_d">ง. <?php echo $option_d; ?></label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="exam-controls">
                <button type="submit" class="exam-submit-btn">ส่งคำตอบ</button>
            </div>
        </form>
        
        <div class="exam-results" style="display: none;"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('exam', 'exam_display_shortcode');

// AJAX สำหรับตรวจคำตอบ
function exam_check_answers() {
    check_ajax_referer('exam_nonce', 'nonce');
    
    $answers = isset($_POST['answers']) ? $_POST['answers'] : array();
    
    if (empty($answers)) {
        wp_send_json_error('ไม่พบคำตอบที่ส่งมา');
    }
    
    $results = array(
        'total' => 0,
        'correct' => 0,
        'incorrect' => 0,
        'questions' => array()
    );
    
    foreach ($answers as $question_id => $answer) {
        $correct_answer = get_post_meta($question_id, 'correct_answer', true);
        $explanation = get_post_meta($question_id, 'explanation', true);
        $question_title = get_the_title($question_id);
        
        $is_correct = ($answer === $correct_answer);
        
        $results['total']++;
        if ($is_correct) {
            $results['correct']++;
        } else {
            $results['incorrect']++;
        }
        
        $results['questions'][] = array(
            'id' => $question_id,
            'question' => $question_title,
            'your_answer' => $answer,
            'correct_answer' => $correct_answer,
            'is_correct' => $is_correct,
            'explanation' => $explanation
        );
    }
    
    // สร้าง HTML สำหรับแสดงผล
    $html = '<div class="results-summary">';
    $html .= '<h3>ผลการทดสอบ</h3>';
    $html .= '<p>คะแนนที่ได้: ' . $results['correct'] . ' / ' . $results['total'] . '</p>';
    $html .= '<p>คิดเป็น: ' . round(($results['correct'] / $results['total']) * 100, 2) . '%</p>';
    $html .= '</div>';
    
    $html .= '<div class="results-detail">';
    $html .= '<h3>รายละเอียด</h3>';
    
    foreach ($results['questions'] as $question) {
        $html .= '<div class="result-item ' . ($question['is_correct'] ? 'correct' : 'incorrect') . '">';
        $html .= '<div class="question-text">' . $question['question'] . '</div>';
        
        $html .= '<div class="answer-detail">';
        $html .= '<p>คำตอบของคุณ: ' . strtoupper($question['your_answer']) . '</p>';
        $html .= '<p>คำตอบที่ถูกต้อง: ' . strtoupper($question['correct_answer']) . '</p>';
        
        if (!empty($question['explanation'])) {
            $html .= '<div class="explanation">';
            $html .= '<h4>คำอธิบาย:</h4>';
            $html .= '<p>' . $question['explanation'] . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    wp_send_json_success(array(
        'html' => $html,
        'results' => $results
    ));
}
add_action('wp_ajax_check_exam_answers', 'exam_check_answers');
add_action('wp_ajax_nopriv_check_exam_answers', 'exam_check_answers');
