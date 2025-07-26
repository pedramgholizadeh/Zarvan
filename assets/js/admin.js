/* Admin JavaScript for Zarvan AI plugin */
jQuery(document).ready(function($) {
    console.log('Zarvan AI admin script loaded. jQuery version:', $.fn.jquery);
    console.log('SweetAlert loaded:', typeof Swal !== 'undefined');

    // Function to load saved form data
    function loadFormData(callback) {
        $.ajax({
            url: zarvanAi.ajaxurl,
            type: 'POST',
            data: {
                action: 'zarvan_ai_get_form_data',
                nonce: zarvanAi.nonce
            },
            success: function(response) {
                console.log('Loaded form data:', response);
                if (response.success) {
                    callback(response.data || {});
                } else {
                    callback({});
                }
            },
            error: function(xhr, status, error) {
                console.log('Error loading form data:', status, error, xhr.responseText);
                callback({});
            }
        });
    }

    // Function to save form data
    function saveFormData(formData) {
        $.ajax({
            url: zarvanAi.ajaxurl,
            type: 'POST',
            data: {
                action: 'zarvan_ai_save_form_data',
                nonce: zarvanAi.nonce,
                form_data: formData
            },
            success: function(response) {
                console.log('Saved form data response:', response);
            },
            error: function(xhr, status, error) {
                console.log('Error saving form data:', status, error, xhr.responseText);
            }
        });
    }

    // Open content generation modal
    $(document).on('click', '#zarvan-ai-generate', function(e) {
        console.log('Generate button clicked', e.target);
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            alert('خطا: SweetAlert2 بارگذاری نشده است');
            return;
        }

        // Load saved form data before opening modal
        loadFormData(function(savedData) {
            Swal.fire({
                title: 'تولید محتوا',
                html: `
                    <form id="zarvan-ai-form">
                        <div class="form-group">
                            <label for="audience">مخاطب هدف</label>
                            <input type="text" id="audience" class="swal2-input" value="${savedData.audience || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="content_type">نوع محتوا</label>
                            <input type="text" id="content_type" class="swal2-input" value="${savedData.content_type || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tone">لحن محتوا</label>
                            <input type="text" id="tone" class="swal2-input" value="${savedData.tone || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="word_count">تعداد کلمات</label>
                            <input type="number" id="word_count" class="swal2-input" value="${savedData.word_count || ''}" required>
                        </div>
                        <input type="hidden" id="title" value="${$('#title').val() || ''}">
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'تولید',
                cancelButtonText: 'لغو',
                preConfirm: () => {
                    const form = document.getElementById('zarvan-ai-form');
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return false;
                    }
                    return {
                        audience: document.getElementById('audience').value,
                        content_type: document.getElementById('content_type').value,
                        tone: document.getElementById('tone').value,
                        word_count: document.getElementById('word_count').value,
                        title: document.getElementById('title').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state with timer
                    let seconds = 0;
                    const timerInterval = setInterval(() => {
                        seconds++;
                        if (document.querySelector('.swal2-timer')) {
                            document.querySelector('.swal2-timer').textContent = `زمان سپری شده: ${seconds} ثانیه`;
                        }
                    }, 1000);

                    Swal.fire({
                        title: 'در حال تولید محتوا...',
                        html: '<p>لطفاً منتظر بمانید. این فرآیند ممکن است تا ۲ دقیقه طول بکشد.</p><p class="swal2-timer">زمان سپری شده: 0 ثانیه</p>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        showCancelButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    console.log('AJAX data:', result.value);
                    $.ajax({
                        url: zarvanAi.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'zarvan_ai_generate',
                            nonce: zarvanAi.nonce,
                            ...result.value
                        },
                        success: function(response) {
                            clearInterval(timerInterval);
                            console.log('AJAX response:', response);
                            if (response.success) {
                                // Log prompt, URL, and API key
                                $('#title').val(response.data.title);
                                tinymce.activeEditor.setContent(response.data.content);
                                console.log(response.data.content);
                                // if (wp.blocks) {
                                //     // Gutenberg Editor
                                //     wp.data.dispatch('core/block-editor').insertBlocks(
                                //         wp.blocks.parse(response.data.content)
                                //     );
                                //     tinymce.activeEditor.setContent(response.data.content);
                                // } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                                //     // Classic Editor with TinyMCE
                                //     tinymce.activeEditor.setContent(response.data.content);
                                //     $('.wp-editor-area').val(response.data.content);
                                // } else {
                                //     // Fallback: just insert into the textarea
                                //     $('.wp-editor-area').val(response.data.content);
                                // }
                                // Save form data on successful generation
                                
                                saveFormData({
                                    audience: result.value.audience,
                                    content_type: result.value.content_type,
                                    tone: result.value.tone,
                                    word_count: result.value.word_count
                                });
                                Swal.fire('موفقیت', 'محتوا با موفقیت تولید شد', 'success');
                            } else {
                                console.log('Web service error:', response.data.message);
                                Swal.fire('خطا', response.data.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            clearInterval(timerInterval);
                            console.log('Web service error:', xhr.responseText || 'خطا در ارتباط با سرور');
                            Swal.fire('خطا', xhr.responseText || 'خطا در ارتباط با سرور', 'error');
                        }
                    });
                }
            });
        });
    });
});