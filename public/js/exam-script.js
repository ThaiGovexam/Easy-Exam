(function($) {
    'use strict';
    
    $(document).ready(function() {
        // ตัวแปรสำหรับการจับเวลา
        var timerInterval;
        
        // จัดการการแบ่งหน้าข้อสอบ (pagination)
        var currentPage = 1;
        var questionsPerPage = parseInt($('#exam-form').data('per-page')) || 0;
        var totalQuestions = $('.question-item').length;
        
        // ฟังก์ชันสำหรับตั้งค่าระบบแบ่งหน้า
        function setupPagination() {
            if (questionsPerPage > 0 && totalQuestions > questionsPerPage) {
                var totalPages = Math.ceil(totalQuestions / questionsPerPage);
                
                // สร้าง pagination
                var paginationHtml = '<div class="exam-pagination">';
                for (var i = 1; i <= totalPages; i++) {
                    paginationHtml += '<button type="button" class="page-button" data-page="' + i + '">' + i + '</button>';
                }
                paginationHtml += '</div>';
                
                // เพิ่ม pagination ก่อนและหลังข้อสอบ
                $('.questions-wrapper').before(paginationHtml);
                $('.exam-controls').before(paginationHtml.clone());
                
                // แสดงเฉพาะข้อสอบในหน้าแรก
                showPage(1);
                
                // เพิ่ม event listener สำหรับปุ่มหน้า
                $(document).on('click', '.page-button', function() {
                    var page = $(this).data('page');
                    showPage(page);
                });
            }
        }
        
        // ฟังก์ชันแสดงข้อสอบตามหน้าที่เลือก
        function showPage(page) {
            if (questionsPerPage <= 0) {
                return;
            }
            
            // ซ่อนทุกข้อ
            $('.question-item').hide();
            
            // คำนวณข้อที่ต้องแสดง
            var start = (page - 1) * questionsPerPage;
            var end = start + questionsPerPage;
            
            // แสดงข้อในหน้าที่เลือก
            $('.question-item').slice(start, end).show();
            
            // อัปเดตสถานะปุ่ม
            $('.page-button').removeClass('active');
            $('.page-button[data-page="' + page + '"]').addClass('active');
            
            // เลื่อนไปด้านบนของข้อสอบ
            $('html, body').animate({
                scrollTop: $('.exam-container').offset().top - 50
            }, 300);
            
            currentPage = page;
        }
        
        // ฟังก์ชันสำหรับเริ่มจับเวลา
        function startTimer(timeLimit) {
            // ล้างตัวจับเวลาเดิม (ถ้ามี)
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            var timer = $('#exam-timer');
            var timeDisplay = timer.find('.time-display');
            
            // รีเซ็ตการแสดงผลเวลา
            timeDisplay.removeClass('time-warning');
            
            var hours, minutes, seconds;
            var remainingTime = timeLimit;
            
            // ฟังก์ชันแสดงเวลา
            function updateDisplay() {
                hours = Math.floor(remainingTime / 3600);
                minutes = Math.floor((remainingTime % 3600) / 60);
                seconds = remainingTime % 60;
                
                timeDisplay.text(
                    (hours < 10 ? '0' + hours : hours) + ':' +
                    (minutes < 10 ? '0' + minutes : minutes) + ':' +
                    (seconds < 10 ? '0' + seconds : seconds)
                );
                
                // แสดงสีแดงเมื่อเวลาใกล้หมด
                if (remainingTime <= 300) { // 5 นาทีสุดท้าย
                    timeDisplay.addClass('time-warning');
                }
            }
            
            // แสดงเวลาเริ่มต้น
            updateDisplay();
            
            // เริ่มจับเวลา
            timerInterval = setInterval(function() {
                remainingTime--;
                
                if (remainingTime <= 0) {
                    clearInterval(timerInterval);
                    updateDisplay();
                    $('#exam-form').submit(); // ส่งฟอร์มอัตโนมัติเมื่อหมดเวลา
                    return;
                }
                
                updateDisplay();
            }, 1000);
        }
        
        // ตั้งค่าการแบ่งหน้าหากจำเป็น
        if ($('#exam-form').length) {
            setupPagination();
            
            // เริ่มจับเวลาถ้ามีการตั้งค่า
            if ($('#exam-timer').length) {
                var timeLimit = parseInt($('#exam-timer').data('time'));
                if (timeLimit > 0) {
                    startTimer(timeLimit);
                }
            }
        }
        
        // จัดการฟอร์มส่งคำตอบ
        $('#exam-form').on('submit', function(e) {
            e.preventDefault();
            
            var answers = {};
            var examSetId = $(this).find('input[name="exam_set_id"]').val() || 0;
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
                    exam_set_id: examSetId,
                    nonce: exam_ajax.nonce
                },
                beforeSend: function() {
                    // แสดง loading
                    $('.exam-controls').append('<span class="loading">กำลังตรวจคำตอบ...</span>');
                    
                    // ปิดการทำงานของปุ่มส่ง
                    $('.exam-submit-btn').prop('disabled', true);
                    
                    // หยุดการจับเวลา (ถ้ามี)
                    if (timerInterval) {
                        clearInterval(timerInterval);
                    }
                },
                success: function(response) {
                    if (response.success) {
                        // ซ่อนฟอร์มและแสดงผลลัพธ์
                        $('.exam-form').hide();
                        $('.exam-results').html(response.data.html).fadeIn();
                        
                        // บันทึกผลลัพธ์ลง localStorage เพื่อป้องกันการรีเฟรชหน้า
                        if (examSetId) {
                            try {
                                localStorage.setItem('exam_result_' + examSetId, JSON.stringify({
                                    timestamp: new Date().getTime(),
                                    html: response.data.html
                                }));
                            } catch (e) {
                                console.log('ไม่สามารถบันทึกผลลัพธ์ลง localStorage ได้');
                            }
                        }
                        
                        // เลื่อนไปที่ผลลัพธ์
                        $('html, body').animate({
                            scrollTop: $('.exam-results').offset().top - 50
                        }, 500);
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.data);
                        $('.exam-submit-btn').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการตรวจคำตอบ กรุณาลองใหม่อีกครั้ง');
                    $('.exam-submit-btn').prop('disabled', false);
                },
                complete: function() {
                    $('.loading').remove();
                }
            });
        });
        
        // ปุ่มทำข้อสอบใหม่
        $(document).on('click', '.retry-exam', function() {
            // ล้างผลลัพธ์ที่บันทึกไว้ใน localStorage (ถ้ามี)
            var examSetId = $('#exam-form input[name="exam_set_id"]').val();
            if (examSetId) {
                try {
                    localStorage.removeItem('exam_result_' + examSetId);
                } catch (e) {}
            }
            
            $('.exam-form').show();
            $('.exam-results').hide().empty();
            
            // รีเซ็ตคำตอบทั้งหมด
            $('input[type="radio"]').prop('checked', false);
            
            // เปิดการทำงานของปุ่มส่ง
            $('.exam-submit-btn').prop('disabled', false);
            
            // เลื่อนกลับไปด้านบน
            $('html, body').animate({
                scrollTop: $('.exam-container').offset().top - 50
            }, 500);
            
            // เริ่มจับเวลาใหม่ (ถ้ามี)
            if ($('#exam-timer').length) {
                var timeLimit = parseInt($('#exam-timer').data('time'));
                if (timeLimit > 0) {
                    startTimer(timeLimit);
                }
            }
            
            // แสดงหน้าแรก (หากมีการแบ่งหน้า)
            if (questionsPerPage > 0) {
                showPage(1);
            }
        });
        
        // ตรวจสอบผลลัพธ์ที่บันทึกไว้ใน localStorage
        var examSetId = $('#exam-form input[name="exam_set_id"]').val();
        if (examSetId) {
            try {
                var savedResult = localStorage.getItem('exam_result_' + examSetId);
                if (savedResult) {
                    savedResult = JSON.parse(savedResult);
                    
                    // ตรวจสอบว่าผลลัพธ์ยังไม่หมดอายุ (ไม่เกิน 1 วัน)
                    var now = new Date().getTime();
                    var oneDayMs = 24 * 60 * 60 * 1000;
                    
                    if (savedResult.timestamp && (now - savedResult.timestamp) < oneDayMs) {
                        // แสดงผลลัพธ์ที่บันทึกไว้
                        $('.exam-form').hide();
                        $('.exam-results').html(savedResult.html).show();
                        
                        // หยุดการจับเวลา (ถ้ามี)
                        if (timerInterval) {
                            clearInterval(timerInterval);
                        }
                        
                        // แสดงข้อความว่าเป็นผลลัพธ์ที่บันทึกไว้
                        $('.results-summary').prepend('<div class="saved-result-notice">นี่คือผลการทดสอบล่าสุดของคุณ</div>');
                    } else {
                        // ลบผลลัพธ์ที่หมดอายุ
                        localStorage.removeItem('exam_result_' + examSetId);
                    }
                }
            } catch (e) {
                console.log('ไม่สามารถโหลดผลลัพธ์จาก localStorage ได้');
            }
        }
        
        // บันทึกความก้าวหน้าอัตโนมัติ
        $(document).on('change', 'input[type="radio"]', function() {
            if (examSetId) {
                try {
                    var savedAnswers = {};
                    
                    // เก็บคำตอบทั้งหมดที่เลือกไว้
                    $('input[type="radio"]:checked').each(function() {
                        var name = $(this).attr('name');
                        var questionId = name.replace('q_', '');
                        savedAnswers[questionId] = $(this).val();
                    });
                    
                    // บันทึกคำตอบลง localStorage
                    localStorage.setItem('exam_progress_' + examSetId, JSON.stringify({
                        timestamp: new Date().getTime(),
                        answers: savedAnswers
                    }));
                } catch (e) {
                    console.log('ไม่สามารถบันทึกความก้าวหน้าลง localStorage ได้');
                }
            }
        });
        
        // โหลดความก้าวหน้าที่บันทึกไว้
        if (examSetId) {
            try {
                var savedProgress = localStorage.getItem('exam_progress_' + examSetId);
                if (savedProgress) {
                    savedProgress = JSON.parse(savedProgress);
                    
                    // ตรวจสอบว่าความก้าวหน้ายังไม่หมดอายุ (ไม่เกิน 1 วัน)
                    var now = new Date().getTime();
                    var oneDayMs = 24 * 60 * 60 * 1000;
                    
                    if (savedProgress.timestamp && (now - savedProgress.timestamp) < oneDayMs) {
                        // ถามผู้ใช้ว่าต้องการโหลดความก้าวหน้าหรือไม่
                        var confirmLoad = confirm('พบการทำข้อสอบที่ยังไม่เสร็จสิ้น ต้องการโหลดคำตอบที่บันทึกไว้หรือไม่?');
                        
                        if (confirmLoad && savedProgress.answers) {
                            // เลือกคำตอบตามที่บันทึกไว้
                            $.each(savedProgress.answers, function(questionId, answer) {
                                $('input[name="q_' + questionId + '"][value="' + answer + '"]').prop('checked', true);
                            });
                            
                            // แสดงข้อความว่าโหลดความก้าวหน้าเรียบร้อยแล้ว
                            $('.exam-container').prepend('<div class="notice progress-loaded-notice">โหลดคำตอบที่บันทึกไว้เรียบร้อยแล้ว</div>');
                            
                            // ซ่อนข้อความหลังจาก 3 วินาที
                            setTimeout(function() {
                                $('.progress-loaded-notice').fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 3000);
                        } else {
                            // ลบความก้าวหน้าหากผู้ใช้ไม่ต้องการโหลด
                            localStorage.removeItem('exam_progress_' + examSetId);
                        }
                    } else {
                        // ลบความก้าวหน้าที่หมดอายุ
                        localStorage.removeItem('exam_progress_' + examSetId);
                    }
                }
            } catch (e) {
                console.log('ไม่สามารถโหลดความก้าวหน้าจาก localStorage ได้');
            }
        }
        
        // ใส่หมายเลขข้อสอบบนปุ่มแบ่งหน้า
        $('.page-button').each(function() {
            var page = $(this).data('page');
            var start = (page - 1) * questionsPerPage + 1;
            var end = Math.min(start + questionsPerPage - 1, totalQuestions);
            
            // เพิ่ม tooltip แสดงช่วงข้อสอบ
            $(this).attr('title', 'ข้อ ' + start + ' - ' + end);
        });
        
        // แสดงข้อความเตือนเมื่อผู้ใช้พยายามออกจากหน้าขณะทำข้อสอบ
        var formChanged = false;
        
        $('input[type="radio"]').on('change', function() {
            formChanged = true;
        });
        
        $(window).on('beforeunload', function() {
            if (formChanged && $('.exam-form').is(':visible')) {
                return 'คุณกำลังทำข้อสอบอยู่ หากออกจากหน้านี้ข้อมูลอาจสูญหาย';
            }
        });
        
        // ยกเลิกการเตือนเมื่อส่งฟอร์ม
        $('#exam-form').on('submit', function() {
            formChanged = false;
        });
    });
    
})(jQuery);
