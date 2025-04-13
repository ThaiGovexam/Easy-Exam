<?php
/**
 * Plugin Name: ระบบข้อสอบอัตโนมัติ (แบบเรียบง่าย)
 * Description: ระบบนำเข้าและแสดงข้อสอบ 
 * Version: 1.0.0
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
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-editor-help',
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'exam_register_post_type');

// เพิ่มเมนูในแอดมิน
function exam_admin_menu() {
    add_menu_page(
        'ระบบข้อสอบ',
        'ระบบข้อสอบ',
        'manage_options',
        'exam-system',
        'exam_admin_page',
        'dashicons-welcome-learn-more',
        30
    );
}
add_action('admin_menu', 'exam_admin_menu');

// หน้าแอดมิน
function exam_admin_page() {
    ?>
    <div class="wrap">
        <h1>ระบบข้อสอบ</h1>
        <p>ยินดีต้อนรับสู่ระบบข้อสอบอัตโนมัติ</p>
    </div>
    <?php
}
