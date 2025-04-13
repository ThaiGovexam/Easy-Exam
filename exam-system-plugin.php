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
