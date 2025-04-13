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
    // สร้าง Shortcode สำหรับแสดงชุดข้อสอบ
function exam_set_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,  // ID ของชุดข้อสอบ
    ), $atts, 'exam_set');
    
    if (empty($atts['id'])) {
        return '<p>กรุณาระบุ ID ของชุดข้อสอบ [exam_set id="123"]</p>';
    }
    
    $exam_set = get_post(intval($atts['id']));
    
    if (!$exam_set || $exam_set->post_type !== 'exam_set_cpt') {
        return '<p>ไม่พบชุดข้อสอบตามที่ระบุ</p>';
    }
    
    // ดึงการตั้งค่าของชุดข้อสอบ
    $time_limit = get_post_meta($exam_set->ID, 'time_limit', true);
    $random_order = get_post_meta($exam_set->ID, 'random_order', true);
    $questions_per_page = get_post_meta($exam_set->ID, 'questions_per_page', true);
    $selected_questions = get_post_meta($exam_set->ID, 'selected_questions', true);
    
    if (empty($selected_questions)) {
        return '<p>ยังไม่มีข้อสอบในชุดนี้</p>';
    }
    
    $question_ids = explode(',', $selected_questions);
    
    // สร้าง query arguments
    $args = array(
        'post_type' => 'exam_question',
        'posts_per_page' => -1,
        'post__in' => $question_ids,
        'orderby' => ($random_order === 'yes') ? 'rand' : 'post__in'
    );
    
    $questions = get_posts($args);
    
    if (empty($questions)) {
        return '<p>ไม่พบข้อสอบในชุดนี้</p>';
    }
    
    ob_start();
    ?>
    <div class="exam-container" id="exam-container" data-exam-id="<?php echo $exam_set->ID; ?>">
        <?php if (!empty($time_limit) && intval($time_limit) > 0) : ?>
        <div class="exam-timer" id="exam-timer" data-time="<?php echo intval($time_limit) * 60; ?>">
            เวลาที่เหลือ: <span class="time-display">00:00:00</span>
        </div>
        <?php endif; ?>
        
        <h2 class="exam-title"><?php echo $exam_set->post_title; ?></h2>
        
        <?php if (!empty($exam_set->post_content)) : ?>
        <div class="exam-description">
            <?php echo wpautop($exam_set->post_content); ?>
        </div>
        <?php endif; ?>
        
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
                                <label for="q<?php echo $question->ID; ?>_a">ก. <?php echo $option_a; ?></label></div>
                            
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
                <input type="hidden" name="exam_set_id" value="<?php echo $exam_set->ID; ?>">
                <button type="submit" class="exam-submit-btn">ส่งคำตอบ</button>
            </div>
        </form>
        
        <div class="exam-results" style="display: none;"></div>
    </div>
    
    <?php if (!empty($time_limit) && intval($time_limit) > 0) : ?>
    <script>
    jQuery(document).ready(function($) {
        // ตั้งค่าการจับเวลา
        var timeLimit = <?php echo intval($time_limit) * 60; ?>;
        var timer = $('#exam-timer');
        var timeDisplay = timer.find('.time-display');
        
        var hours, minutes, seconds;
        var timerInterval;
        
        function startTimer() {
            timerInterval = setInterval(function() {
                timeLimit--;
                
                if (timeLimit <= 0) {
                    clearInterval(timerInterval);
                    timeDisplay.addClass('time-warning');
                    $('#exam-form').submit(); // ส่งฟอร์มอัตโนมัติเมื่อหมดเวลา
                    return;
                }
                
                hours = Math.floor(timeLimit / 3600);
                minutes = Math.floor((timeLimit % 3600) / 60);
                seconds = timeLimit % 60;
                
                timeDisplay.text(
                    (hours < 10 ? '0' + hours : hours) + ':' +
                    (minutes < 10 ? '0' + minutes : minutes) + ':' +
                    (seconds < 10 ? '0' + seconds : seconds)
                );
                
                // แสดงสีแดงเมื่อเวลาใกล้หมด
                if (timeLimit <= 300) { // 5 นาทีสุดท้าย
                    timeDisplay.addClass('time-warning');
                }
            }, 1000);
        }
        
        // เริ่มจับเวลาเมื่อหน้าเว็บโหลดเสร็จ
        startTimer();
    });
    </script>
    <?php endif; ?>
    
    <?php
    return ob_get_clean();
}
add_shortcode('exam_set', 'exam_set_shortcode');
    
   // AJAX สำหรับตรวจคำตอบของชุดข้อสอบ
function exam_check_set_answers() {
    check_ajax_referer('exam_nonce', 'nonce');
    
    $answers = isset($_POST['answers']) ? $_POST['answers'] : array();
    $exam_set_id = isset($_POST['exam_set_id']) ? intval($_POST['exam_set_id']) : 0;
    
    if (empty($answers) || empty($exam_set_id)) {
        wp_send_json_error('ข้อมูลไม่ครบถ้วน');
    }
    
    // ดึงข้อมูลชุดข้อสอบ
    $passing_score = get_post_meta($exam_set_id, 'passing_score', true);
    $show_answers = get_post_meta($exam_set_id, 'show_answers', true);
    $selected_questions = get_post_meta($exam_set_id, 'selected_questions', true);
    
    if (empty($selected_questions)) {
        wp_send_json_error('ไม่พบข้อสอบในชุดนี้');
    }
    
    $question_ids = explode(',', $selected_questions);
    
    $results = array(
        'total' => count($question_ids),
        'answered' => count($answers),
        'correct' => 0,
        'incorrect' => 0,
        'score_percentage' => 0,
        'passed' => false,
        'questions' => array()
    );
    
    // ตรวจคำตอบแต่ละข้อ
    foreach ($question_ids as $question_id) {
        $correct_answer = get_post_meta($question_id, 'correct_answer', true);
        $user_answer = isset($answers[$question_id]) ? $answers[$question_id] : '';
        $explanation = get_post_meta($question_id, 'explanation', true);
        $question_title = get_the_title($question_id);
        
        $is_answered = !empty($user_answer);
        $is_correct = ($user_answer === $correct_answer);
        
        if ($is_answered) {
            if ($is_correct) {
                $results['correct']++;
            } else {
                $results['incorrect']++;
            }
        }
        
        $results['questions'][] = array(
            'id' => $question_id,
            'question' => $question_title,
            'your_answer' => $user_answer,
            'correct_answer' => $correct_answer,
            'is_answered' => $is_answered,
            'is_correct' => $is_correct,
            'explanation' => $explanation
        );
    }
    
    // คำนวณผลลัพธ์
    $results['score_percentage'] = ($results['total'] > 0) ? round(($results['correct'] / $results['total']) * 100, 2) : 0;
    $results['passed'] = ($results['score_percentage'] >= intval($passing_score));
    
    // สร้าง HTML สำหรับแสดงผล
    $html = '<div class="results-summary">';
    $html .= '<h3>ผลการทดสอบ</h3>';
    
    $html .= '<div class="score-display">';
    $html .= '<div class="score-value">' . $results['score_percentage'] . '%</div>';
    $html .= '<div class="score-details">คะแนนที่ได้: ' . $results['correct'] . ' / ' . $results['total'] . '</div>';
    $html .= '</div>';
    
    if ($passing_score > 0) {
        $html .= '<div class="pass-fail-status ' . ($results['passed'] ? 'passed' : 'failed') . '">';
        $html .= $results['passed'] ? 'ผ่าน' : 'ไม่ผ่าน';
        $html .= '</div>';
        $html .= '<div class="passing-score">เกณฑ์ผ่าน: ' . $passing_score . '%</div>';
    }
    
    $html .= '</div>';
    
    // แสดงรายละเอียดแต่ละข้อหากตั้งค่าให้แสดง
    if ($show_answers === 'yes') {
        $html .= '<div class="results-detail">';
        $html .= '<h3>รายละเอียดคำตอบ</h3>';
        
        foreach ($results['questions'] as $index => $question) {
            $status_class = !$question['is_answered'] ? 'unanswered' : ($question['is_correct'] ? 'correct' : 'incorrect');
            
            $html .= '<div class="result-item ' . $status_class . '">';
            $html .= '<div class="question-number">ข้อ ' . ($index + 1) . '</div>';
            $html .= '<div class="question-text">' . $question['question'] . '</div>';
            
            $html .= '<div class="answer-detail">';
            
            if ($question['is_answered']) {
                $html .= '<p>คำตอบของคุณ: ' . strtoupper($question['your_answer']) . '</p>';
            } else {
                $html .= '<p>คุณไม่ได้ตอบข้อนี้</p>';
            }
            
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
    }
    
    // ปุ่มลองใหม่
    $html .= '<div class="exam-actions">';
    $html .= '<button type="button" class="retry-exam">ทำข้อสอบใหม่</button>';
    $html .= '</div>';
    
    wp_send_json_success(array(
        'html' => $html,
        'results' => $results
    ));
}
add_action('wp_ajax_check_exam_answers', 'exam_check_set_answers');
add_action('wp_ajax_nopriv_check_exam_answers', 'exam_check_set_answers');
