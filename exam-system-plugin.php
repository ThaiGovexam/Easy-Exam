<?php
/**
 * Plugin Name: ระบบข้อสอบพร้อม Taxonomies
 * Description: ระบบข้อสอบอัตโนมัติพร้อมระบบจัดหมวดหมู่ข้อสอบ
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
            'add_new' => 'เพิ่มข้อสอบใหม่',
            'add_new_item' => 'เพิ่มข้อสอบใหม่',
            'edit_item' => 'แก้ไขข้อสอบ',
            'all_items' => 'ข้อสอบทั้งหมด'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-editor-help',
        'supports' => array('title'),
        'rewrite' => array('slug' => 'exam-question')
    ));
}
add_action('init', 'exam_register_post_type');

// ลงทะเบียน Taxonomies
function exam_register_taxonomies() {
    // หมวดหมู่: ชุดข้อสอบ
    register_taxonomy('exam_set', 'exam_question', array(
        'labels' => array(
            'name' => 'ชุดข้อสอบ',
            'singular_name' => 'ชุดข้อสอบ',
            'search_items' => 'ค้นหาชุดข้อสอบ',
            'all_items' => 'ชุดข้อสอบทั้งหมด',
            'parent_item' => 'ชุดข้อสอบหลัก',
            'parent_item_colon' => 'ชุดข้อสอบหลัก:',
            'edit_item' => 'แก้ไขชุดข้อสอบ',
            'update_item' => 'อัปเดตชุดข้อสอบ',
            'add_new_item' => 'เพิ่มชุดข้อสอบใหม่',
            'new_item_name' => 'ชื่อชุดข้อสอบใหม่',
            'menu_name' => 'ชุดข้อสอบ'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'exam-set')
    ));
    
    // หมวดหมู่: ประเภทข้อสอบ
    register_taxonomy('exam_type', 'exam_question', array(
        'labels' => array(
            'name' => 'ประเภทข้อสอบ',
            'singular_name' => 'ประเภทข้อสอบ',
            'search_items' => 'ค้นหาประเภทข้อสอบ',
            'all_items' => 'ประเภทข้อสอบทั้งหมด',
            'parent_item' => 'ประเภทข้อสอบหลัก',
            'parent_item_colon' => 'ประเภทข้อสอบหลัก:',
            'edit_item' => 'แก้ไขประเภทข้อสอบ',
            'update_item' => 'อัปเดตประเภทข้อสอบ',
            'add_new_item' => 'เพิ่มประเภทข้อสอบใหม่',
            'new_item_name' => 'ชื่อประเภทข้อสอบใหม่',
            'menu_name' => 'ประเภทข้อสอบ'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'exam-type')
    ));
    
    // หมวดหมู่: กระทรวง
    register_taxonomy('exam_ministry', 'exam_question', array(
        'labels' => array(
            'name' => 'กระทรวง',
            'singular_name' => 'กระทรวง',
            'search_items' => 'ค้นหากระทรวง',
            'all_items' => 'กระทรวงทั้งหมด',
            'edit_item' => 'แก้ไขกระทรวง',
            'update_item' => 'อัปเดตกระทรวง',
            'add_new_item' => 'เพิ่มกระทรวงใหม่',
            'new_item_name' => 'ชื่อกระทรวงใหม่',
            'menu_name' => 'กระทรวง'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'exam-ministry')
    ));
    
    // หมวดหมู่: กรม
    register_taxonomy('exam_department', 'exam_question', array(
        'labels' => array(
            'name' => 'กรม',
            'singular_name' => 'กรม',
            'search_items' => 'ค้นหากรม',
            'all_items' => 'กรมทั้งหมด',
            'parent_item' => 'กระทรวง',
            'parent_item_colon' => 'กระทรวง:',
            'edit_item' => 'แก้ไขกรม',
            'update_item' => 'อัปเดตกรม',
            'add_new_item' => 'เพิ่มกรมใหม่',
            'new_item_name' => 'ชื่อกรมใหม่',
            'menu_name' => 'กรม'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'exam-department')
    ));
    
    // หมวดหมู่: ระดับตำแหน่ง
    register_taxonomy('exam_level', 'exam_question', array(
        'labels' => array(
            'name' => 'ระดับตำแหน่ง',
            'singular_name' => 'ระดับตำแหน่ง',
            'search_items' => 'ค้นหาระดับตำแหน่ง',
            'all_items' => 'ระดับตำแหน่งทั้งหมด',
            'edit_item' => 'แก้ไขระดับตำแหน่ง',
            'update_item' => 'อัปเดตระดับตำแหน่ง',
            'add_new_item' => 'เพิ่มระดับตำแหน่งใหม่',
            'new_item_name' => 'ชื่อระดับตำแหน่งใหม่',
            'menu_name' => 'ระดับตำแหน่ง'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'exam-level')
    ));
    
    // หมวดหมู่: ตำแหน่ง
    register_taxonomy('exam_position', 'exam_question', array(
        'labels' => array(
            'name' => 'ตำแหน่ง',
            'singular_name' => 'ตำแหน่ง',
            'search_items' => 'ค้นหาตำแหน่ง',
            'all_items' => 'ตำแหน่งทั้งหมด',
            'edit_item' => 'แก้ไขตำแหน่ง',
            'update_item' => 'อัปเดตตำแหน่ง',
            'add_new_item' => 'เพิ่มตำแหน่งใหม่',
            'new_item_name' => 'ชื่อตำแหน่งใหม่',
            'menu_name' => 'ตำแหน่ง'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'exam-position')
    ));
}
add_action('init', 'exam_register_taxonomies');

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
    $explanation = get_post_meta($post->ID, 'explanation', true);
    $unique_id = get_post_meta($post->ID, 'unique_id', true);
    ?>
    
    <p>
        <label for="unique_id">รหัสข้อสอบ:</label><br>
        <input type="text" id="unique_id" name="unique_id" value="<?php echo esc_attr($unique_id); ?>" style="width: 100%;">
        <span class="description">รหัสที่ใช้อ้างอิงข้อสอบ (ถ้ามี)</span>
    </p>
    
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
    
    <p>
        <label for="explanation">คำอธิบาย:</label><br>
        <textarea id="explanation" name="explanation" style="width: 100%; height: 100px;"><?php echo esc_textarea($explanation); ?></textarea>
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
    if (isset($_POST['unique_id'])) {
        update_post_meta($post_id, 'unique_id', sanitize_text_field($_POST['unique_id']));
    }
    
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
    
    if (isset($_POST['explanation'])) {
        update_post_meta($post_id, 'explanation', wp_kses_post($_POST['explanation']));
    }
}
add_action('save_post_exam_question', 'exam_save_meta_boxes');

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
    
    add_submenu_page(
        'exam-system',
        'นำเข้าข้อสอบ',
        'นำเข้าข้อสอบ',
        'manage_options',
        'exam-import',
        'exam_import_page'
    );
}
add_action('admin_menu', 'exam_admin_menu');

// หน้าแอดมิน
function exam_admin_page() {
    ?>
    <div class="wrap">
        <h1>ระบบข้อสอบ</h1>
        
        <div class="card">
            <h2>คำแนะนำการใช้งาน</h2>
            <p>ยินดีต้อนรับสู่ระบบข้อสอบ! ระบบนี้ช่วยให้คุณสามารถจัดการข้อสอบและแสดงผลบนเว็บไซต์ได้ง่าย</p>
            
            <h3>การใช้งานเบื้องต้น</h3>
            <ol>
                <li>เพิ่มข้อสอบใหม่ผ่านเมนู "ข้อสอบ" ด้านซ้าย</li>
                <li>จัดหมวดหมู่ข้อสอบด้วย ชุดข้อสอบ, ประเภท, กระทรวง, กรม, ระดับตำแหน่ง และตำแหน่ง</li>
                <li>นำเข้าข้อสอบจำนวนมากผ่านเมนู "นำเข้าข้อสอบ"</li>
                <li>แสดงข้อสอบบนหน้าเว็บด้วย Shortcode [exam]</li>
            </ol>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Shortcode พื้นฐาน</h2>
            <p>ใช้ Shortcode ต่อไปนี้เพื่อแสดงข้อสอบบนหน้าเว็บไซต์:</p>
            
            <code>[exam limit="10"]</code> - แสดงข้อสอบล่าสุด 10 ข้อ<br>
            <code>[exam set="ชื่อชุดข้อสอบ" limit="20"]</code> - แสดงข้อสอบ 20 ข้อจากชุดข้อสอบที่ระบุ<br>
            <code>[exam ministry="กระทรวงแรงงาน" random="yes"]</code> - แสดงข้อสอบจากกระทรวงแรงงานแบบสุ่ม
        </div>
    </div>
    <?php
}

// หน้านำเข้าข้อสอบ
function exam_import_page() {
    ?>
    <div class="wrap">
        <h1>นำเข้าข้อสอบ</h1>
        
        <div class="card">
            <h2>นำเข้าด้วยไฟล์ CSV</h2>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('exam_import_csv', 'exam_import_nonce'); ?>
                
                <p>
                    <label for="csv_file">เลือกไฟล์ CSV:</label><br>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv">
                </p>
                
                <p>
                    <input type="submit" name="import_csv" class="button button-primary" value="นำเข้าข้อมูล">
                </p>
            </form>
            
            <h3>รูปแบบไฟล์ CSV</h3>
            <p>ไฟล์ CSV ต้องมีคอลัมน์ต่อไปนี้:</p>
            <pre style="background: #f5f5f5; padding: 10px; overflow: auto;">unique_id,question,option_a,option_b,option_c,option_d,correct_answer,explanation,exam_set,exam_type,exam_ministry,exam_department,exam_level,exam_position</pre>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>นำเข้าจาก Google Sheets</h2>
            
            <form method="post">
                <?php wp_nonce_field('exam_import_sheet', 'exam_sheet_nonce'); ?>
                
                <p>
                    <label for="sheet_id">Google Sheet ID:</label><br>
                    <input type="text" name="sheet_id" id="sheet_id" class="regular-text" placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms">
                    <p class="description">ID ของ Google Sheet (ส่วนที่อยู่ในลิงก์ https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit)</p>
                </p>
                
                <p>
                    <label for="sheet_name">ชื่อ Sheet:</label><br>
                    <input type="text" name="sheet_name" id="sheet_name" class="regular-text" placeholder="Sheet1">
                    <p class="description">ชื่อของ Sheet ที่ต้องการนำเข้า (ชื่อแท็บด้านล่าง)</p>
                </p>
                
                <p>
                    <input type="submit" name="import_sheet" class="button button-primary" value="นำเข้าข้อมูล">
                </p>
            </form>
            
            <h3>การเตรียม Google Sheet</h3>
            <ol>
                <li>สร้าง Google Sheet ใหม่หรือใช้ Sheet ที่มีอยู่แล้ว</li>
                <li>ตั้งชื่อคอลัมน์แถวแรกให้ตรงตามรูปแบบข้างต้น</li>
                <li>ตั้งค่าการแชร์เป็น "ผู้ที่มีลิงก์สามารถดูได้"</li>
                <li>คัดลอก ID ของ Sheet (ส่วนที่อยู่ในลิงก์ระหว่าง /d/ และ /edit)</li>
            </ol>
        </div>
    </div>
    <?php
    
    // ตรวจสอบการนำเข้าจาก CSV
    if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
        if (check_admin_referer('exam_import_csv', 'exam_import_nonce')) {
            $file = $_FILES['csv_file'];
            
            if ($file['error'] == 0) {
                $count = exam_import_csv($file['tmp_name']);
                
                if ($count !== false) {
                    echo '<div class="notice notice-success is-dismissible"><p>นำเข้าข้อสอบสำเร็จ ' . $count . ' ข้อ</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>เกิดข้อผิดพลาดในการนำเข้าข้อมูล</p></div>';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>เกิดข้อผิดพลาดในการอัปโหลดไฟล์</p></div>';
            }
        }
    }
    
    // ตรวจสอบการนำเข้าจาก Google Sheet
    if (isset($_POST['import_sheet'])) {
        if (check_admin_referer('exam_import_sheet', 'exam_sheet_nonce')) {
            $sheet_id = sanitize_text_field($_POST['sheet_id']);
            $sheet_name = sanitize_text_field($_POST['sheet_name']);
            
            if (!empty($sheet_id) && !empty($sheet_name)) {
                $count = exam_import_from_google_sheet($sheet_id, $sheet_name);
                
                if ($count !== false) {
                    echo '<div class="notice notice-success is-dismissible"><p>นำเข้าข้อสอบจาก Google Sheet สำเร็จ ' . $count . ' ข้อ</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>เกิดข้อผิดพลาดในการนำเข้าข้อมูลจาก Google Sheet</p></div>';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>กรุณาระบุ Google Sheet ID และชื่อ Sheet</p></div>';
            }
        }
    }
}

// ฟังก์ชันนำเข้าข้อมูลจากไฟล์ CSV
function exam_import_csv($file) {
    if (!file_exists($file)) {
        return false;
    }
    
    $handle = fopen($file, 'r');
    if (!$handle) {
        return false;
    }
    
    // ข้ามแถวหัวข้อ
    $header = fgetcsv($handle);
    
    $count = 0;
    
    while (($data = fgetcsv($handle)) !== FALSE) {
        // ตรวจสอบว่ามีข้อมูลที่จำเป็นหรือไม่
        if (empty($data[1])) {
            continue; // ข้ามหากไม่มีคำถาม
        }
        
        // สร้างข้อสอบใหม่
        $post_data = array(
            'post_title'   => wp_strip_all_tags($data[1]), // คำถาม
            'post_content' => '', // ไม่จำเป็นต้องมี content
            'post_status'  => 'publish',
            'post_type'    => 'exam_question'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id) {
            // บันทึก meta data
            if (!empty($data[0])) update_post_meta($post_id, 'unique_id', sanitize_text_field($data[0]));
            if (!empty($data[2])) update_post_meta($post_id, 'option_a', sanitize_text_field($data[2]));
            if (!empty($data[3])) update_post_meta($post_id, 'option_b', sanitize_text_field($data[3]));
            if (!empty($data[4])) update_post_meta($post_id, 'option_c', sanitize_text_field($data[4]));
            if (!empty($data[5])) update_post_meta($post_id, 'option_d', sanitize_text_field($data[5]));
            if (!empty($data[6])) update_post_meta($post_id, 'correct_answer', sanitize_text_field($data[6]));
            if (!empty($data[7])) update_post_meta($post_id, 'explanation', sanitize_text_field($data[7]));
            
            // กำหนด terms
            if (!empty($data[8])) wp_set_object_terms($post_id, explode(',', $data[8]), 'exam_set', false);
            if (!empty($data[9])) wp_set_object_terms($post_id, explode(',', $data[9]), 'exam_type', false);
            if (!empty($data[10])) wp_set_object_terms($post_id, explode(',', $data[10]), 'exam_ministry', false);
            if (!empty($data[11])) wp_set_object_terms($post_id, explode(',', $data[11]), 'exam_department', false);
            if (!empty($data[12])) wp_set_object_terms($post_id, explode(',', $data[12]), 'exam_level', false);
            if (!empty($data[13])) wp_set_object_terms($post_id, explode(',', $data[13]), 'exam_position', false);
            
            $count++;
        }
    }
    
    fclose($handle);
    
    return $count; // ส่งคืนจำนวนข้อที่นำเข้าสำเร็จ
}

// ฟังก์ชันนำเข้าข้อมูลจาก Google Sheet
function exam_import_from_google_sheet($sheet_id, $sheet_name) {
    $url = 'https://docs.google.com/spreadsheets/d/' . $sheet_id . '/gviz/tq?tqx=out:csv&sheet=' . urlencode($sheet_name);
    
    $response = wp_remote_get($url);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
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
