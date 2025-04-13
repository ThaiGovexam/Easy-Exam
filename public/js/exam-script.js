(function($) {
    'use strict';
    
    $(document).ready(function() {
        // จัดการฟอร์มส่งคำตอบ
        $('#exam-form').on('submit', function(e) {
            e.preventDefault();
            
            var answers = {};
            var totalQuestions = $('.question-item').length;
            var answeredQuestions = 0;
            
            // เก็บคำตอบที่ผู้ใช้เลือก
            $(this).find('input:checked').each(function() {
                var name = $(this).attr('name');
                var questionId = name.replace('q_', '');
                answers[questionId] = $(this).val();
                answeredQuestions++;
            });
            
            // ตรวจสอบว่าตอบครบทุกข้อหรือไม่
            if (answeredQuestions < totalQuestions) {
                if (!confirm('คุณยังตอบคำถามไม่ครบ (' + answeredQuestions + ' จาก ' + totalQuestions + ') ต้องการส่งคำตอบหรือไม่?')) {
                    return;
                }
            }
            
            // ส่งคำตอบไปตรวจ
            $.ajax({
                url: exam_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'check_exam_answers',
                    answers: answers,
                    nonce: exam_ajax.nonce
                },
                beforeSend: function() {
                    // แสดง loading
                    $('.exam-controls').append('<span class="loading">กำลังตรวจคำตอบ...</span>');
                },
                success: function(response) {
                    // ซ่อนฟอร์มและแสดงผลลัพธ์
                    $('.exam-form').hide();
                    $('.exam-results').html(response.data.html).fadeIn();
                    
                    // เลื่อนไปที่ผลลัพธ์
                    $('html, body').animate({
                        scrollTop: $('.exam-results').offset().top - 50
                    }, 500);
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการตรวจคำตอบ กรุณาลองใหม่อีกครั้ง');
                },
                complete: function() {
                    $('.loading').remove();
                }
            });
        });
        
        // ปุ่มทำข้อสอบใหม่
        $(document).on('click', '.retry-exam', function() {
            $('.exam-form').show();
            $('.exam-results').hide();
            
            // รีเซ็ตคำตอบทั้งหมด
            $('input[type="radio"]').prop('checked', false);
            
            // เลื่อนกลับไปด้านบน
            $('html, body').animate({
                scrollTop: $('.exam-container').offset().top - 50
            }, 500);
        });
    });
    
})(jQuery);
