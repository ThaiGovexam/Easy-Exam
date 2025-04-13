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
