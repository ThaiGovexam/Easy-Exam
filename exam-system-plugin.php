if (empty($csv_data)) {
        return false;
    }
    
    // สร้างไฟล์ชั่วคราวเพื่อใช้ในการนำเข้า
    $temp_file = wp_tempnam('exam_import');
    file_put_contents($temp_file, $csv_data);
    
    // นำเข้าข้อมูลด้วยฟังก์ชันที่มีอยู่แล้ว
    $result = exam_import_csv($temp_file);
    
    // ลบไฟล์ชั่วคราว
    @unlink($temp_file);
    
    return $result;
}

// สร้าง Shortcode สำหรับแสดงข้อสอบ
function exam_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'set' => '',
        'type' => '',
        'ministry' => '',
        'department' => '',
        'level' => '',
        'position' => '',
        'random' => 'no'
    ), $atts, 'exam');
    
    // สร้าง query arguments
    $args = array(
        'post_type' => 'exam_question',
        'posts_per_page' => intval($atts['limit']),
        'orderby' => ($atts['random'] === 'yes') ? 'rand' : 'date',
        'order' => 'DESC',
        'tax_query' => array()
    );
    
    // เพิ่มเงื่อนไขตาม taxonomy
    $taxonomies = array(
        'set' => 'exam_set',
        'type' => 'exam_type',
        'ministry' => 'exam_ministry',
        'department' => 'exam_department',
        'level' => 'exam_level',
        'position' => 'exam_position'
    );
    
    foreach ($taxonomies as $param => $taxonomy) {
        if (!empty($atts[$param])) {
            $args['tax_query'][] = array(
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => explode(',', $atts[$param])
            );
        }
    }
    
    // ถ้ามีมากกว่า 1 taxonomy ให้กำหนดความสัมพันธ์แบบ AND
    if (count($args['tax_query']) > 1) {
        $args['tax_query']['relation'] = 'AND';
    }
    
    $questions = get_posts($args);
    
    if (empty($questions)) {
        return '<div class="exam-notice">ไม่พบข้อสอบตามเงื่อนไขที่ระบุ</div>';
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
add_shortcode('exam', 'exam_shortcode');

// ลงทะเบียน JavaScript และ CSS
function exam_enqueue_scripts() {
    wp_enqueue_style('exam-style', plugins_url('css/exam-style.css', __FILE__));
    wp_enqueue_script('exam-script', plugins_url('js/exam-script.js', __FILE__), array('jquery'), '1.0', true);
    
    wp_localize_script('exam-script', 'exam_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('exam_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'exam_enqueue_scripts');

// สร้างโฟลเดอร์ CSS และ JavaScript หากยังไม่มี
function exam_create_folders() {
    $css_folder = plugin_dir_path(__FILE__) . 'css';
    $js_folder = plugin_dir_path(__FILE__) . 'js';
    
    if (!file_exists($css_folder)) {
        mkdir($css_folder, 0755, true);
    }
    
    if (!file_exists($js_folder)) {
        mkdir($js_folder, 0755, true);
    }
    
    // สร้างไฟล์ CSS พื้นฐาน
    $css_file = $css_folder . '/exam-style.css';
    if (!file_exists($css_file)) {
        $css_content = '/* CSS สำหรับระบบข้อสอบ */
.exam-container {
    max-width: 800px;
    margin: 0 auto;
    font-family: Arial, sans-serif;
}

.question-item {
    margin-bottom: 30px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.question-number {
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.question-text {
    margin-bottom: 15px;
    font-size: 16px;
    line-height: 1.5;
}

.options-list {
    margin-left: 20px;
}

.option {
    margin-bottom: 10px;
}

.option label {
    cursor: pointer;
    display: inline-block;
    padding-left: 5px;
}

.option input:checked + label {
    font-weight: bold;
    color: #337ab7;
}

.exam-controls {
    margin: 20px 0;
    text-align: center;
}

.exam-submit-btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.exam-submit-btn:hover {
    background-color: #45a049;
}

/* สำหรับผลลัพธ์ */
.results-summary {
    background-color: #f0f0f0;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

.result-item {
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 5px;
}

.result-item.correct {
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
}

.result-item.incorrect {
    background-color: #f2dede;
    border: 1px solid #ebccd1;
}

.answer-detail {
    margin-left: 20px;
    margin-top: 10px;
}

.explanation {
    margin-top: 10px;
    padding: 10px;
    background-color: #f8f8f8;
    border-left: 3px solid #ccc;
}

.retry-exam {
    background-color: #337ab7;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 20px;
    display: inline-block;
}

.retry-exam:hover {
    background-color: #286090;
}

/* การตอบสนองบนมือถือ */
@media (max-width: 768px) {
    .question-item {
        padding: 10px;
    }
    
    .question-text {
        font-size: 14px;
    }
    
    .option {
        margin-bottom: 8px;
    }
    
    .exam-submit-btn {
        width: 100%;
    }
}';
        
        file_put_contents($css_file, $css_content);
    }
    
    // สร้างไฟล์ JavaScript พื้นฐาน
    $js_file = $js_folder . '/exam-script.js';
    if (!file_exists($js_file)) {
        $js_content = '(function($) {
    \'use strict\';
    
    $(document).ready(function() {
        // จัดการฟอร์มส่งคำตอบ
        $(\'#exam-form\').on(\'submit\', function(e) {
            e.preventDefault();
            
            var answers = {};
            var totalQuestions = $(\'.question-item\').length;
            var answeredQuestions = 0;
            
            // เก็บคำตอบที่ผู้ใช้เลือก
            $(this).find(\'input:checked\').each(function() {
                var name = $(this).attr(\'name\');
                var questionId = name.replace(\'q_\', \'\');
                answers[questionId] = $(this).val();
                answeredQuestions++;
            });
            
            // ตรวจสอบว่าตอบครบทุกข้อหรือไม่
            if (answeredQuestions < totalQuestions) {
                if (!confirm(\'คุณยังตอบคำถามไม่ครบ (\' + answeredQuestions + \' จาก \' + totalQuestions + \') ต้องการส่งคำตอบหรือไม่?\')) {
                    return;
                }
            }
            
            // ส่งคำตอบไปตรวจ
            $.ajax({
                url: exam_ajax.ajax_url,
                type: \'POST\',
                data: {
                    action: \'check_exam_answers\',
                    answers: answers,
                    nonce: exam_ajax.nonce
                },
                beforeSend: function() {
                    // แสดง loading
                    $(\'.exam-controls\').append(\'<span class="loading">กำลังตรวจคำตอบ...</span>\');
                    
                    // ปิดการทำงานของปุ่มส่ง
                    $(\'.exam-submit-btn\').prop(\'disabled\', true);
                },
                success: function(response) {
                    if (response.success) {
                        // ซ่อนฟอร์มและแสดงผลลัพธ์
                        $(\'.exam-form\').hide();
                        $(\'.exam-results\').html(response.data.html).fadeIn();
                        
                        // เลื่อนไปที่ผลลัพธ์
                        $(\'html, body\').animate({
                            scrollTop: $(\'.exam-results\').offset().top - 50
                        }, 500);
                    } else {
                        alert(\'เกิดข้อผิดพลาด: \' + response.data);
                        $(\'.exam-submit-btn\').prop(\'disabled\', false);
                    }
                },
                error: function() {
                    alert(\'เกิดข้อผิดพลาดในการตรวจคำตอบ กรุณาลองใหม่อีกครั้ง\');
                    $(\'.exam-submit-btn\').prop(\'disabled\', false);
                },
                complete: function() {
                    $(\'.loading\').remove();
                }
            });
        });
        
        // ปุ่มทำข้อสอบใหม่
        $(document).on(\'click\', \'.retry-exam\', function() {
            $(\'.exam-form\').show();
            $(\'.exam-results\').hide().empty();
            
            // รีเซ็ตคำตอบทั้งหมด
            $(\'input[type="radio"]\').prop(\'checked\', false);
            
            // เปิดการทำงานของปุ่มส่ง
            $(\'.exam-submit-btn\').prop(\'disabled\', false);
            
            // เลื่อนกลับไปด้านบน
            $(\'html, body\').animate({
                scrollTop: $(\'.exam-container\').offset().top - 50
            }, 500);
        });
    });
    
})(jQuery);';
        
        file_put_contents($js_file, $js_content);
    }
}
register_activation_hook(__FILE__, 'exam_create_folders');

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
    
    $html .= '<div class="exam-actions">';
    $html .= '<button type="button" class="retry-exam">ทำข้อสอบใหม่</button>';
    $html .= '</div>';
    
    wp_send_json_success(array(
        'html' => $html,
        'results' => $results
    ));
}
add_action('wp_ajax_check_exam_answers', 'exam_check_answers');
add_action('wp_ajax_nopriv_check_exam_answers', 'exam_check_answers');

// เพิ่มปุ่มและฟิลเตอร์ในหน้าจัดการข้อสอบ
function exam_add_admin_filters() {
    global $typenow;
    
    if ($typenow === 'exam_question') {
        // เพิ่มฟิลเตอร์สำหรับแต่ละ taxonomy
        $taxonomies = array(
            'exam_set' => 'ชุดข้อสอบ',
            'exam_type' => 'ประเภทข้อสอบ',
            'exam_ministry' => 'กระทรวง',
            'exam_department' => 'กรม',
            'exam_level' => 'ระดับตำแหน่ง',
            'exam_position' => 'ตำแหน่ง'
        );
        
        foreach ($taxonomies as $taxonomy => $label) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            
            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<select name="' . $taxonomy . '" id="' . $taxonomy . '" class="postform">';
                echo '<option value="">' . $label . ': ทั้งหมด</option>';
                
                foreach ($terms as $term) {
                    $selected = isset($_GET[$taxonomy]) && $_GET[$taxonomy] === $term->slug ? ' selected="selected"' : '';
                    echo '<option value="' . $term->slug . '"' . $selected . '>' . $term->name . ' (' . $term->count . ')</option>';
                }
                
                echo '</select>';
            }
        }
    }
}
add_action('restrict_manage_posts', 'exam_add_admin_filters');

// เพิ่มคอลัมน์แสดงคำตอบที่ถูกต้องในหน้าจัดการข้อสอบ
function exam_add_custom_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // เพิ่มคอลัมน์หลังจากชื่อ
        if ($key === 'title') {
            $new_columns['correct_answer'] = 'คำตอบที่ถูกต้อง';
            $new_columns['unique_id'] = 'รหัสข้อสอบ';
        }
    }
    
    return $new_columns;
}
add_filter('manage_exam_question_posts_columns', 'exam_add_custom_columns');

// แสดงข้อมูลในคอลัมน์ที่เพิ่ม
function exam_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'correct_answer':
            $correct_answer = get_post_meta($post_id, 'correct_answer', true);
            
            switch ($correct_answer) {
                case 'a':
                    echo 'ก';
                    break;
                case 'b':
                    echo 'ข';
                    break;
                case 'c':
                    echo 'ค';
                    break;
                case 'd':
                    echo 'ง';
                    break;
                default:
                    echo '-';
            }
            break;
            
        case 'unique_id':
            $unique_id = get_post_meta($post_id, 'unique_id', true);
            echo !empty($unique_id) ? $unique_id : '-';
            break;
    }
}
add_action('manage_exam_question_posts_custom_column', 'exam_custom_column_content', 10, 2);

// ทำให้คอลัมน์คำตอบที่ถูกต้องสามารถจัดเรียงได้
function exam_sortable_columns($columns) {
    $columns['correct_answer'] = 'correct_answer';
    $columns['unique_id'] = 'unique_id';
    return $columns;
}
add_filter('manage_edit-exam_question_sortable_columns', 'exam_sortable_columns');

// จัดการการเรียงลำดับตามคอลัมน์ที่กำหนด
function exam_sort_by_custom_column($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('correct_answer' === $orderby) {
        $query->set('meta_key', 'correct_answer');
        $query->set('orderby', 'meta_value');
    }
    
    if ('unique_id' === $orderby) {
        $query->set('meta_key', 'unique_id');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'exam_sort_by_custom_column');
