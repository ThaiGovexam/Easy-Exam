<?php
/**
 * Plugin Name: ระบบข้อสอบขั้นพื้นฐาน
 * Description: เฉพาะส่วน Custom Post Type และ Meta Box
 * Version: 0.1.0
 * Author: Your Name
 */

// ป้องกันการเข้าถึงไฟล์โดยตรง
if (!defined('ABSPATH')) {
    exit;
}

// ลงทะเบียน Custom Post Type
function exam_register_post_type() {
    register_post_type('exam_question', array(
        'labels' => array(
            'name' => 'ข้อสอบ',
            'singular_name' => 'ข้อสอบ',
            'add_new' => 'เพิ่มข้อสอบใหม่',
            'add_new_item' => 'เพิ่มข้อสอบใหม่',
            'edit_item' => 'แก้ไขข้อสอบ',
            'all_items' => 'ข้อสอบทั้งหมด'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-editor-help',
        'supports' => array('title'),
    ));
}
add_action('init', 'exam_register_post_type');

// เพิ่ม Meta Box สำหรับข้อมูลข้อสอบ
function exam_add_meta_boxes() {
    add_meta_box(
        'exam_question_meta',
        'รายละเอียดข้อสอบ',
        'exam_question_meta_callback',
        'exam_question',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'exam_add_meta_boxes');

// แสดงฟอร์มสำหรับกรอกข้อมูลข้อสอบ
function exam_question_meta_callback($post) {
    wp_nonce_field('exam_question_meta', 'exam_question_meta_nonce');
    
    // ดึงข้อมูลที่บันทึกไว้
    $option_a = get_post_meta($post->ID, 'option_a', true);
    $option_b = get_post_meta($post->ID, 'option_b', true);
    $option_c = get_post_meta($post->ID, 'option_c', true);
    $option_d = get_post_meta($post->ID, 'option_d', true);
    $correct_answer = get_post_meta($post->ID, 'correct_answer', true);
    ?>
    
    <p>
        <label for="option_a">ตัวเลือก ก:</label><br>
        <input type="text" id="option_a" name="option_a" value="<?php echo esc_attr($option_a); ?>" style="width: 100%;">
    </p>
    
    <p>
        <label for="option_b">ตัวเลือก ข:</label><br>
        <input type="text" id="option_b" name="option_b" value="<?php echo esc_attr($option_b); ?>" style="width: 100%;">
    </p>
    
    <p>
        <label for="option_c">ตัวเลือก ค:</label><br>
        <input type="text" id="option_c" name="option_c" value="<?php echo esc_attr($option_c); ?>" style="width: 100%;">
    </p>
    
    <p>
        <label for="option_d">ตัวเลือก ง:</label><br>
        <input type="text" id="option_d" name="option_d" value="<?php echo esc_attr($option_d); ?>" style="width: 100%;">
    </p>
    
    <p>
        <label for="correct_answer">คำตอบที่ถูกต้อง:</label><br>
        <select id="correct_answer" name="correct_answer">
            <option value="a" <?php selected($correct_answer, 'a'); ?>>ก</option>
            <option value="b" <?php selected($correct_answer, 'b'); ?>>ข</option>
            <option value="c" <?php selected($correct_answer, 'c'); ?>>ค</option>
            <option value="d" <?php selected($correct_answer, 'd'); ?>>ง</option>
        </select>
    </p>
    <?php
}

// บันทึกข้อมูล Meta Box
function exam_save_meta_boxes($post_id) {
    // ตรวจสอบสิทธิ์และความปลอดภัย
    if (!isset($_POST['exam_question_meta_nonce']) || !wp_verify_nonce($_POST['exam_question_meta_nonce'], 'exam_question_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // บันทึกข้อมูล
    if (isset($_POST['option_a'])) {
        update_post_meta($post_id, 'option_a', sanitize_text_field($_POST['option_a']));
    }
    
    if (isset($_POST['option_b'])) {
        update_post_meta($post_id, 'option_b', sanitize_text_field($_POST['option_b']));
    }
    
    if (isset($_POST['option_c'])) {
        update_post_meta($post_id, 'option_c', sanitize_text_field($_POST['option_c']));
    }
    
    if (isset($_POST['option_d'])) {
        update_post_meta($post_id, 'option_d', sanitize_text_field($_POST['option_d']));
    }
    
    if (isset($_POST['correct_answer'])) {
        update_post_meta($post_id, 'correct_answer', sanitize_text_field($_POST['correct_answer']));
    }
}
add_action('save_post_exam_question', 'exam_save_meta_boxes');
