<?php
// ลงทะเบียน Custom Post Types และ Taxonomies
function exam_register_post_types() {
    // Register Exam Question Post Type
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
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'exam-question')
    ));
    
    // Register Taxonomies
    register_taxonomy('exam_set', 'exam_question', array(
        'labels' => array(
            'name' => 'ชุดข้อสอบ',
            'singular_name' => 'ชุดข้อสอบ'
        ),
        'hierarchical' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'exam-set')
    ));
    
    register_taxonomy('exam_type', 'exam_question', array(
        'labels' => array(
            'name' => 'ประเภทข้อสอบ',
            'singular_name' => 'ประเภทข้อสอบ'
        ),
        'hierarchical' => true,
        'show_admin_column' => true
    ));
    
    register_taxonomy('exam_ministry', 'exam_question', array(
        'labels' => array(
            'name' => 'กระทรวง',
            'singular_name' => 'กระทรวง'
        ),
        'hierarchical' => true,
        'show_admin_column' => true
    ));
    
    register_taxonomy('exam_level', 'exam_question', array(
        'labels' => array(
            'name' => 'ระดับ',
            'singular_name' => 'ระดับ'
        ),
        'hierarchical' => true,
        'show_admin_column' => true
    ));
    
    register_taxonomy('exam_position', 'exam_question', array(
        'labels' => array(
            'name' => 'ตำแหน่ง',
            'singular_name' => 'ตำแหน่ง'
        ),
        'hierarchical' => true,
        'show_admin_column' => true
    ));
}
add_action('init', 'exam_register_post_types');
