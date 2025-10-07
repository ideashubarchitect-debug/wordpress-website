/**
 * Telegram Blog Publisher Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin functionality
    initAdmin();
    
    function initAdmin() {
        // Initialize tooltips
        initTooltips();
        
        // Initialize form validation
        initFormValidation();
        
        // Initialize AJAX handlers
        initAjaxHandlers();
    }
    
    function initTooltips() {
        // Add tooltips to help text
        $('.tbp-help').each(function() {
            const helpText = $(this).data('help');
            if (helpText) {
                $(this).attr('title', helpText);
            }
        });
    }
    
    function initFormValidation() {
        // Validate settings form
        $('#tbp-settings-form').on('submit', function(e) {
            const form = $(this);
            let isValid = true;
            
            // Clear previous errors
            form.find('.tbp-field-error').remove();
            
            // Validate required fields
            const requiredFields = form.find('[required]');
            requiredFields.each(function() {
                const field = $(this);
                const value = field.val().trim();
                
                if (!value) {
                    showFieldError(field, 'This field is required');
                    isValid = false;
                }
            });
            
            // Validate API key format
            const apiKey = $('#ai_api_key').val().trim();
            const aiService = $('#ai_service').val();
            
            if (apiKey && !isValidApiKey(apiKey, aiService)) {
                showFieldError($('#ai_api_key'), 'Invalid API key format for selected service');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fix the errors below', 'error');
            }
        });
    }
    
    function isValidApiKey(apiKey, service) {
        switch (service) {
            case 'openai':
                return apiKey.startsWith('sk-') && apiKey.length > 20;
            case 'claude':
                return apiKey.startsWith('sk-ant-') && apiKey.length > 20;
            case 'gemini':
                return apiKey.length > 20; // Gemini keys don't have a specific prefix
            default:
                return true;
        }
    }
    
    function showFieldError(field, message) {
        field.addClass('tbp-field-error');
        field.after('<div class="tbp-field-error-message">' + message + '</div>');
    }
    
    function initAjaxHandlers() {
        // Test webhook
        $(document).on('click', '#test-webhook', function() {
            testWebhook();
        });
        
        // Generate content
        $(document).on('submit', '#quick-test-form', function(e) {
            e.preventDefault();
            generateContent();
        });
        
        // Save settings
        $(document).on('submit', '#tbp-settings-form', function(e) {
            e.preventDefault();
            saveSettings();
        });
        
        // Clear logs
        $(document).on('click', '#clear-logs', function() {
            clearLogs();
        });
        
        // Export logs
        $(document).on('click', '#export-logs', function() {
            exportLogs();
        });
        
        // View log details
        $(document).on('click', '.tbp-view-details', function() {
            const logIndex = $(this).data('log-index');
            viewLogDetails(logIndex);
        });
        
        // Close modal
        $(document).on('click', '.tbp-modal-close, #tbp-log-modal', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
    
    function testWebhook() {
        const button = $('#test-webhook');
        const resultDiv = $('#test-result');
        
        // Disable button and show loading
        button.prop('disabled', true).text('Testing...');
        resultDiv.html('<div class="tbp-loading">Testing webhook...</div>');
        
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tbp_test_webhook',
                nonce: tbp_ajax.nonce
            },
            timeout: 30000,
            success: function(response) {
                if (response.success) {
                    const successHtml = '<div class="tbp-success">' +
                        '✅ Webhook test successful! ' +
                        '<a href="' + response.data.edit_url + '" target="_blank">Edit Post</a>' +
                        '</div>';
                    resultDiv.html(successHtml);
                } else {
                    const errorHtml = '<div class="tbp-error">❌ Webhook test failed: ' + 
                        (response.data || 'Unknown error') + '</div>';
                    resultDiv.html(errorHtml);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Network error';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out';
                } else if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMessage = xhr.responseJSON.data;
                }
                
                const errorHtml = '<div class="tbp-error">❌ Webhook test failed: ' + errorMessage + '</div>';
                resultDiv.html(errorHtml);
            },
            complete: function() {
                button.prop('disabled', false).text('Test Webhook');
            }
        });
    }
    
    function generateContent() {
        const form = $('#quick-test-form');
        const topic = $('#test-topic').val().trim();
        const details = $('#test-details').val().trim();
        const button = form.find('button[type="submit"]');
        const contentDiv = $('#generated-content');
        const previewDiv = $('#content-preview');
        
        // Validate input
        if (!topic || !details) {
            showNotification('Please fill in both topic and details', 'error');
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text('Generating...');
        contentDiv.show();
        previewDiv.html('<div class="tbp-loading">Generating content...</div>');
        
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tbp_generate_content',
                nonce: tbp_ajax.nonce,
                topic: topic,
                details: details
            },
            timeout: 60000,
            success: function(response) {
                if (response.success) {
                    const contentHtml = '<div class="tbp-content-preview">' + 
                        response.data.content + '</div>';
                    previewDiv.html(contentHtml);
                    showNotification('Content generated successfully!', 'success');
                } else {
                    const errorHtml = '<div class="tbp-error">❌ Content generation failed: ' + 
                        (response.data || 'Unknown error') + '</div>';
                    previewDiv.html(errorHtml);
                    showNotification('Content generation failed', 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Network error';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out - AI service may be slow';
                } else if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMessage = xhr.responseJSON.data;
                }
                
                const errorHtml = '<div class="tbp-error">❌ Content generation failed: ' + errorMessage + '</div>';
                previewDiv.html(errorHtml);
                showNotification('Content generation failed', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Generate Content');
            }
        });
    }
    
    function saveSettings() {
        const form = $('#tbp-settings-form');
        const formData = form.serialize();
        const button = form.find('button[type="submit"]');
        const resultDiv = $('#save-result');
        
        // Show loading state
        button.prop('disabled', true).text('Saving...');
        resultDiv.html('<div class="tbp-loading">Saving settings...</div>');
        
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=tbp_save_settings&nonce=' + tbp_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="tbp-success">✅ Settings saved successfully!</div>');
                    showNotification('Settings saved successfully!', 'success');
                } else {
                    resultDiv.html('<div class="tbp-error">❌ Failed to save settings: ' + 
                        (response.data || 'Unknown error') + '</div>');
                    showNotification('Failed to save settings', 'error');
                }
            },
            error: function(xhr, status, error) {
                const errorHtml = '<div class="tbp-error">❌ Failed to save settings: Network error</div>';
                resultDiv.html(errorHtml);
                showNotification('Failed to save settings', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Save Settings');
            }
        });
    }
    
    function clearLogs() {
        if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
            return;
        }
        
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tbp_clear_logs',
                nonce: tbp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Logs cleared successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('Failed to clear logs: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Failed to clear logs: Network error', 'error');
            }
        });
    }
    
    function exportLogs() {
        // Get logs data from the page
        const logsData = window.tbpLogs || [];
        const dataStr = JSON.stringify(logsData, null, 2);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        // Create download link
        const a = document.createElement('a');
        a.href = url;
        a.download = 'telegram-blog-publisher-logs-' + new Date().toISOString().split('T')[0] + '.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        showNotification('Logs exported successfully!', 'success');
    }
    
    function viewLogDetails(logIndex) {
        const logs = window.tbpLogs || [];
        const log = logs[logIndex];
        
        if (!log) {
            showNotification('Log not found', 'error');
            return;
        }
        
        let content = '<div class="tbp-log-detail">';
        content += '<h4>Log Information</h4>';
        content += '<p><strong>Timestamp:</strong> ' + log.timestamp + '</p>';
        content += '<p><strong>Action:</strong> ' + log.action + '</p>';
        
        if (log.post_id) {
            content += '<p><strong>Post ID:</strong> ' + log.post_id + '</p>';
        }
        
        content += '<h4>Data</h4>';
        content += '<pre>' + JSON.stringify(log.data, null, 2) + '</pre>';
        content += '</div>';
        
        $('#log-details-content').html(content);
        $('#tbp-log-modal').show();
    }
    
    function closeModal() {
        $('#tbp-log-modal').hide();
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        $('.tbp-notification').remove();
        
        // Create notification
        const notification = $('<div class="tbp-notification tbp-notification-' + type + '">' + message + '</div>');
        
        // Add to page
        $('body').append(notification);
        
        // Show notification
        setTimeout(function() {
            notification.addClass('tbp-notification-show');
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(function() {
            notification.removeClass('tbp-notification-show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Utility functions
    window.copyToClipboard = function(elementId) {
        const element = document.getElementById(elementId);
        element.select();
        element.setSelectionRange(0, 99999);
        document.execCommand('copy');
        
        // Show feedback
        const button = element.nextElementSibling;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        setTimeout(function() {
            button.textContent = originalText;
        }, 2000);
        
        showNotification('Copied to clipboard!', 'success');
    };
    
    window.toggleSecret = function(elementId) {
        const element = document.getElementById(elementId);
        const button = element.nextElementSibling;
        
        if (element.type === 'password') {
            element.type = 'text';
            button.textContent = 'Hide';
        } else {
            element.type = 'password';
            button.textContent = 'Show';
        }
    };
    
    // Update API key help based on selected service
    function updateApiKeyHelp() {
        const service = $('#ai_service').val();
        const info = $('#ai-service-info');
        const help = $('#api-key-help');
        
        $('.tbp-api-service').hide();
        $('#' + service + '-help').show();
        
        switch(service) {
            case 'openai':
                info.text('Enter your OpenAI API key');
                help.attr('href', 'https://platform.openai.com/api-keys');
                break;
            case 'claude':
                info.text('Enter your Claude API key');
                help.attr('href', 'https://console.anthropic.com/');
                break;
            case 'gemini':
                info.text('Enter your Gemini API key');
                help.attr('href', 'https://makersuite.google.com/app/apikey');
                break;
        }
    }
    
    // Initialize API key help
    $('#ai_service').on('change', updateApiKeyHelp);
    updateApiKeyHelp();
});

// Add notification styles
jQuery(document).ready(function($) {
    if (!$('#tbp-notification-styles').length) {
        $('head').append(`
            <style id="tbp-notification-styles">
                .tbp-notification {
                    position: fixed;
                    top: 32px;
                    right: 20px;
                    padding: 12px 20px;
                    border-radius: 4px;
                    color: #fff;
                    font-weight: 600;
                    z-index: 100001;
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                    max-width: 300px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                }
                
                .tbp-notification-show {
                    transform: translateX(0);
                }
                
                .tbp-notification-success {
                    background: #46b450;
                }
                
                .tbp-notification-error {
                    background: #dc3232;
                }
                
                .tbp-field-error {
                    border-color: #dc3232 !important;
                }
                
                .tbp-field-error-message {
                    color: #dc3232;
                    font-size: 0.9em;
                    margin-top: 5px;
                }
            </style>
        `);
    }
});
