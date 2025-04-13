<?php
/**
 * Plugin Name: ระบบข้อสอบอัตโนมัติ
 * Description: ระบบนำเข้าและแสดงข้อสอบสำหรับธุรกิจออนไลน์
 * Version: 1.0.0
 * Author: Your Name
 */

// ป้องกันการเข้าถึงไฟล์โดยตรง
if (!defined('ABSPATH')) {
    exit;
}

// กำหนดค่าคงที่
define('EXAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXAM_PLUGIN_URL', plugin_dir_url(__FILE__));

// โหลดไฟล์ที่จำเป็น
require_once EXAM_PLUGIN_DIR . 'includes/post-types.php';
require_once EXAM_PLUGIN_DIR . 'includes/metaboxes.php';
require_once EXAM_PLUGIN_DIR . 'includes/csv-import.php';
require_once EXAM_PLUGIN_DIR . 'public/shortcodes.php';

// ลงทะเบียน assets
function exam_enqueue_scripts() {
    wp_enqueue_style('exam-styles', EXAM_PLUGIN_URL . 'public/css/exam-style.css');
    wp_enqueue_script('exam-script', EXAM_PLUGIN_URL . 'public/js/exam-script.js', array('jquery'), '1.0.0', true);
    
    wp_localize_script('exam-script', 'exam_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('exam_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'exam_enqueue_scripts');

// เพิ่มเมนูในแอดมิน
function exam_admin_menu() {
    add_menu_page(
        'ระบบข้อสอบ',
        'ระบบข้อสอบ',
        'manage_options',
        'exam-system',
        'exam_import_page',
        'dashicons-welcome-learn-more',
        30
    );
}
add_action('admin_menu', 'exam_admin_menu');

// หน้านำเข้าข้อมูล
function exam_import_page() {
    ?>
    <div class="wrap">
        <h1>นำเข้าข้อสอบ</h1>
        
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('exam_import_nonce', 'exam_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">อัปโหลดไฟล์ CSV</th>
                    <td>
                        <input type="file" name="exam_csv_file" accept=".csv">
                        <p class="description">เลือกไฟล์ CSV สำหรับนำเข้าข้อสอบ</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('นำเข้าข้อมูล'); ?>
        </form>
    </div>
    <?php
    // เพิ่มหน้านำเข้าจาก Google Sheets
function exam_add_sheets_import_page() {
    add_submenu_page(
        'exam-system',
        'นำเข้าจาก Google Sheets',
        'นำเข้าจาก Google Sheets',
        'manage_options',
        'exam-sheets-import',
        'exam_sheets_import_page'
    );
}
add_action('admin_menu', 'exam_add_sheets_import_page');

// แสดงหน้านำเข้าจาก Google Sheets
function exam_sheets_import_page() {
    ?>
    <div class="wrap">
        <h1>นำเข้าข้อสอบจาก Google Sheets</h1>
        
        <form method="post">
            <?php wp_nonce_field('exam_sheets_import_nonce', 'exam_sheets_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Google Sheet ID</th>
                    <td>
                        <input type="text" name="sheet_id" class="regular-text" placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms">
                        <p class="description">ID ของ Google Sheet (ส่วนที่อยู่ในลิงก์ https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">ชื่อ Sheet</th>
                    <td>
                        <input type="text" name="sheet_name" class="regular-text" placeholder="Sheet1">
                        <p class="description">ชื่อของ Sheet ที่ต้องการนำเข้า (ชื่อแท็บด้านล่าง)</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('นำเข้าข้อมูล'); ?>
        </form>
        
        <div class="import-instructions">
            <h3>วิธีการเตรียมข้อมูล Google Sheets</h3>
            <ol>
                <li>สร้าง Google Sheets โดยมีหัวข้อคอลัมน์ตามนี้: unique_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation, exam_set, exam_type, exam_ministry, exam_department, exam_level, exam_position</li>
                <li>ตั้งค่าการแชร์ Google Sheets เป็น "ผู้ที่มีลิงก์สามารถดูได้"</li>
                <li>คัดลอก Sheet ID (ส่วนที่อยู่ระหว่าง /d/ และ /edit ในลิงก์)</li>
                <li>ระบุชื่อ Sheet (แท็บด้านล่าง) ที่ต้องการนำเข้า</li>
            </ol>
        </div>
    </div>
    <?php

    // สร้าง Custom Post Type สำหรับชุดข้อสอบ
function exam_register_exam_set_post_type() {
    register_post_type('exam_set_cpt', array(
        'labels' => array(
            'name' => 'ชุดข้อสอบ',
            'singular_name' => 'ชุดข้อสอบ',
            'add_new' => 'เพิ่มชุดข้อสอบใหม่',
            'add_new_item' => 'เพิ่มชุดข้อสอบใหม่',
            'edit_item' => 'แก้ไขชุดข้อสอบ',
            'all_items' => 'ชุดข้อสอบทั้งหมด'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book-alt',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'exam-set-cpt')
    ));
}
add_action('init', 'exam_register_exam_set_post_type');

// เพิ่ม Meta Box สำหรับชุดข้อสอบ
function exam_set_meta_boxes() {
    add_meta_box(
        'exam_set_meta',
        'ตั้งค่าชุดข้อสอบ',
        'exam_set_meta_callback',
        'exam_set_cpt',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'exam_set_meta_boxes');

// แสดงฟอร์มตั้งค่าชุดข้อสอบ
function exam_set_meta_callback($post) {
    wp_nonce_field('exam_set_meta', 'exam_set_meta_nonce');
    
    $time_limit = get_post_meta($post->ID, 'time_limit', true);
    $passing_score = get_post_meta($post->ID, 'passing_score', true);
    $random_order = get_post_meta($post->ID, 'random_order', true);
    $show_answers = get_post_meta($post->ID, 'show_answers', true);
    $questions_per_page = get_post_meta($post->ID, 'questions_per_page', true);
    
    ?>
    <p>
        <label for="time_limit">เวลาในการทำข้อสอบ (นาที):</label><br>
        <input type="number" id="time_limit" name="time_limit" value="<?php echo esc_attr($time_limit); ?>" min="0" step="1">
        <p class="description">กำหนด 0 เพื่อไม่จำกัดเวลา</p>
    </p>
    
    <p>
        <label for="passing_score">คะแนนผ่าน (%):</label><br>
        <input type="number" id="passing_score" name="passing_score" value="<?php echo esc_attr($passing_score); ?>" min="0" max="100" step="1">
    </p>
    
    <p>
        <label for="random_order">สุ่มลำดับข้อสอบ:</label><br>
        <select id="random_order" name="random_order">
            <option value="no" <?php selected($random_order, 'no'); ?>>ไม่</option>
            <option value="yes" <?php selected($random_order, 'yes'); ?>>ใช่</option>
        </select>
    </p>
    
    <p>
        <label for="show_answers">แสดงเฉลยทันทีหลังทำข้อสอบ:</label><br>
        <select id="show_answers" name="show_answers">
            <option value="yes" <?php selected($show_answers, 'yes'); ?>>ใช่</option>
            <option value="no" <?php selected($show_answers, 'no'); ?>>ไม่</option>
        </select>
    </p>
    
    <p>
        <label for="questions_per_page">จำนวนข้อต่อหน้า:</label><br>
        <select id="questions_per_page" name="questions_per_page">
            <option value="all" <?php selected($questions_per_page, 'all'); ?>>ทั้งหมด</option>
            <option value="10" <?php selected($questions_per_page, '10'); ?>>10</option>
            <option value="20" <?php selected($questions_per_page, '20'); ?>>20</option>
            <option value="50" <?php selected($questions_per_page, '50'); ?>>50</option>
        </select>
    </p>
    
    <div class="exam-questions-selector">
        <h4>เลือกข้อสอบที่อยู่ในชุดนี้</h4>
        <?php
        // แสดงตัวเลือกเพื่อกรองข้อสอบตาม taxonomy
        $taxonomies = array('exam_type', 'exam_ministry', 'exam_level', 'exam_position');
        
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            
            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<p>';
                echo '<label for="filter_' . $taxonomy . '">' . get_taxonomy($taxonomy)->labels->name . ':</label><br>';
                echo '<select id="filter_' . $taxonomy . '" class="taxonomy-filter" data-taxonomy="' . $taxonomy . '">';
                echo '<option value="">ทั้งหมด</option>';
                
                foreach ($terms as $term) {
                    echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                }
                
                echo '</select>';
                echo '</p>';
            }
        }
        ?>
        
        <div class="selected-questions">
            <h4>ข้อสอบที่เลือก</h4>
            <?php
            // แสดงข้อสอบที่เลือกไว้แล้ว
            $selected_questions = get_post_meta($post->ID, 'selected_questions', true);
            if (!empty($selected_questions)) {
                $selected_questions = explode(',', $selected_questions);
                
                echo '<ul class="questions-list">';
                foreach ($selected_questions as $question_id) {
                    $question = get_post($question_id);
                    if ($question) {
                        echo '<li data-id="' . $question_id . '">';
                        echo '<span class="question-title">' . $question->post_title . '</span>';
                        echo '<a href="#" class="remove-question">ลบ</a>';
                        echo '</li>';
                    }
                }
                echo '</ul>';
                
                echo '<input type="hidden" name="selected_questions" id="selected_questions" value="' . esc_attr(implode(',', $selected_questions)) . '">';
            } else {
                echo '<p>ยังไม่มีข้อสอบที่เลือก</p>';
                echo '<input type="hidden" name="selected_questions" id="selected_questions" value="">';
            }
            ?>
        </div>
        
        <div class="available-questions">
            <h4>ข้อสอบทั้งหมด</h4>
            <div class="questions-filter">
                <input type="text" id="questions-search" placeholder="ค้นหาข้อสอบ...">
                <button type="button" id="filter-questions" class="button">กรอง</button>
            </div>
            
            <div class="questions-container">
                <!-- ข้อสอบจะถูกโหลดผ่าน AJAX -->
                <p>กรุณาใช้ตัวกรองเพื่อแสดงข้อสอบ</p>
            </div>
        </div>
    </div>
    
    <style>
        .exam-questions-selector {
            margin-top: 20px;
        }
        
        .questions-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .questions-list li {
            padding: 5px;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .remove-question {
            color: #a00;
        }
        
        .questions-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
        }
        
        .questions-filter {
            margin-bottom: 10px;
            display: flex;
        }
        
        #questions-search {
            flex-grow: 1;
            margin-right: 5px;
        }
    </style>
    
    <script>
        jQuery(document).ready(function($) {
            // AJAX เพื่อโหลดข้อสอบตามตัวกรอง
            $('#filter-questions').on('click', function() {
                var filters = {};
                $('.taxonomy-filter').each(function() {
                    var taxonomy = $(this).data('taxonomy');
                    var value = $(this).val();
                    
                    if (value) {
                        filters[taxonomy] = value;
                    }
                });
                
                var search = $('#questions-search').val();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_filtered_questions',
                        filters: filters,
                        search: search,
                        nonce: '<?php echo wp_create_nonce("get_questions_nonce"); ?>'
                    },
                    beforeSend: function() {
                        $('.questions-container').html('<p>กำลังโหลด...</p>');
                    },
                    success: function(response) {
                        $('.questions-container').html(response);
                        
                        // เพิ่มการจัดการเหตุการณ์เมื่อคลิกเลือกข้อสอบ
                        $('.add-question').on('click', function(e) {
                            e.preventDefault();
                            
                            var questionId = $(this).data('id');
                            var questionTitle = $(this).data('title');
                            
                            // ตรวจสอบว่าข้อสอบถูกเลือกไปแล้วหรือยัง
                            var selectedIds = $('#selected_questions').val();
                            var selectedArray = selectedIds ? selectedIds.split(',') : [];
                            
                            if (selectedArray.indexOf(questionId.toString()) === -1) {
                                selectedArray.push(questionId);
                                $('#selected_questions').val(selectedArray.join(','));
                                
                                // เพิ่มข้อสอบลงในรายการที่เลือก
                                var questionItem = '<li data-id="' + questionId + '"><span class="question-title">' + questionTitle + '</span><a href="#" class="remove-question">ลบ</a></li>';
                                
                                if ($('.questions-list').length === 0) {
                                    $('.selected-questions').html('<ul class="questions-list">' + questionItem + '</ul>');
                                } else {
                                    $('.questions-list').append(questionItem);
                                }
                                
                                // เพิ่มการจัดการเหตุการณ์สำหรับปุ่มลบ
                                $('.remove-question').on('click', function(e) {
                                    e.preventDefault();
                                    
                                    var listItem = $(this).parent();
                                    var removeId = listItem.data('id');
                                    
                                    // ลบข้อสอบออกจากรายการ
                                    listItem.remove();
                                    
                                    // อัปเดตค่าในฟิลด์ซ่อน
                                    var currentIds = $('#selected_questions').val().split(',');
                                    var index = currentIds.indexOf(removeId.toString());
                                    
                                    if (index !== -1) {
                                        currentIds.splice(index, 1);
                                        $('#selected_questions').val(currentIds.join(','));
                                    }
                                });
                            } else {
                                alert('ข้อสอบนี้ถูกเลือกไปแล้ว');
                            }
                        });
                    }
                });
            });
            
            // จัดการการลบข้อสอบ
            $(document).on('click', '.remove-question', function(e) {
                e.preventDefault();
                
                var listItem = $(this).parent();
                var removeId = listItem.data('id');
                
                // ลบข้อสอบออกจากรายการ
                listItem.remove();
                
                // อัปเดตค่าในฟิลด์ซ่อน
                var currentIds = $('#selected_questions').val().split(',');
                var index = currentIds.indexOf(removeId.toString());
                
                if (index !== -1) {
                    currentIds.splice(index, 1);
                    $('#selected_questions').val(currentIds.join(','));
                }
            });
        });
    </script>
    <?php
}

// บันทึกข้อมูลชุดข้อสอบ
function exam_save_set_meta($post_id) {
    if (!isset($_POST['exam_set_meta_nonce']) || !wp_verify_nonce($_POST['exam_set_meta_nonce'], 'exam_set_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // บันทึกค่าต่างๆ
    if (isset($_POST['time_limit'])) {
        update_post_meta($post_id, 'time_limit', intval($_POST['time_limit']));
    }
    
    if (isset($_POST['passing_score'])) {
        update_post_meta($post_id, 'passing_score', intval($_POST['passing_score']));
    }
    
    if (isset($_POST['random_order'])) {
        update_post_meta($post_id, 'random_order', sanitize_text_field($_POST['random_order']));
    }
    
    if (isset($_POST['show_answers'])) {
        update_post_meta($post_id, 'show_answers', sanitize_text_field($_POST['show_answers']));
    }
    
    if (isset($_POST['questions_per_page'])) {
        update_post_meta($post_id, 'questions_per_page', sanitize_text_field($_POST['questions_per_page']));
    }
    
    if (isset($_POST['selected_questions'])) {
        update_post_meta($post_id, 'selected_questions', sanitize_text_field($_POST['selected_questions']));
    }
}
add_action('save_post_exam_set_cpt', 'exam_save_set_meta');

// AJAX เพื่อดึงข้อสอบตามตัวกรอง
function exam_get_filtered_questions() {
    check_ajax_referer('get_questions_nonce', 'nonce');
    
    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    
    $args = array(
        'post_type' => 'exam_question',
        'posts_per_page' => 50,
        'tax_query' => array(),
        's' => $search
    );
    
    foreach ($filters as $taxonomy => $term) {
        $args['tax_query'][] = array(
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => $term
        );
    }
    
    $questions = get_posts($args);
    
    if (empty($questions)) {
        echo '<p>ไม่พบข้อสอบตามเงื่อนไขที่กำหนด</p>';
    } else {
        echo '<ul class="questions-list">';
        
        foreach ($questions as $question) {
            echo '<li>';
            echo '<span class="question-title">' . $question->post_title . '</span>';
            echo '<a href="#" class="add-question" data-id="' . $question->ID . '" data-title="' . esc_attr($question->post_title) . '">เลือก</a>';
            echo '</li>';
        }
        
        echo '</ul>';
    }
    
    wp_die();
}
add_action('wp_ajax_get_filtered_questions', 'exam_get_filtered_questions');
    
    // ตรวจสอบการนำเข้าข้อมูล
    if (isset($_POST['submit']) && isset($_POST['sheet_id']) && isset($_POST['sheet_name'])) {
        if (check_admin_referer('exam_sheets_import_nonce', 'exam_sheets_nonce')) {
            $sheet_id = sanitize_text_field($_POST['sheet_id']);
            $sheet_name = sanitize_text_field($_POST['sheet_name']);
            
            if (!empty($sheet_id) && !empty($sheet_name)) {
                $result = exam_import_from_google_sheet($sheet_id, $sheet_name);
                
                if ($result === false) {
                    echo '<div class="notice notice-error"><p>เกิดข้อผิดพลาดในการนำเข้าข้อมูล ตรวจสอบว่า Sheet ID และชื่อ Sheet ถูกต้อง</p></div>';
                } else {
                    echo '<div class="notice notice-success"><p>นำเข้าข้อมูลสำเร็จ! นำเข้าทั้งหมด ' . $result . ' ข้อ</p></div>';
                }
            }
        }
    }
}

// ฟังก์ชันนำเข้าข้อมูลจาก Google Sheet
function exam_import_from_google_sheet($sheet_id, $sheet_name) {
    $url = 'https://docs.google.com/spreadsheets/d/' . $sheet_id . '/gviz/tq?tqx=out:csv&sheet=' . urlencode($sheet_name);
    
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $csv_data = wp_remote_retrieve_body($response);
    
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
    
    // ตรวจสอบการนำเข้าข้อมูล
    if (isset($_POST['submit']) && isset($_FILES['exam_csv_file'])) {
        if (check_admin_referer('exam_import_nonce', 'exam_nonce')) {
            $file = $_FILES['exam_csv_file'];
            if ($file['error'] == 0) {
                $result = exam_import_csv($file['tmp_name']);
                if ($result) {
                    echo '<div class="notice notice-success"><p>นำเข้าข้อมูลสำเร็จ!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>เกิดข้อผิดพลาดในการนำเข้าข้อมูล</p></div>';
                }
            }
        }
    }
}
