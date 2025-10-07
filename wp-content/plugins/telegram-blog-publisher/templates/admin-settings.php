<?php
$webhook_secret = get_option('tbp_webhook_secret', '');
$api_keys = get_option('tbp_api_keys', []);
$license_key = get_option('tbp_license_key', '');
$license_status = get_option('tbp_license_status', 'invalid');
?>

<div class="wrap">
    <h1>🚀 Telegram Blog Publisher Settings</h1>
    
    <?php if ($license_status !== 'valid'): ?>
    <div class="notice notice-warning">
        <p><strong>⚠️ License Status:</strong> Invalid License</p>
        <button type="button" class="button button-primary" id="reactivate-license">Reactivate License</button>
    </div>
    <?php endif; ?>
    
    <div class="tbp-settings-container">
        <form id="tbp-settings-form">
            <?php wp_nonce_field('tbp_nonce', 'nonce'); ?>
            
            <!-- Webhook Configuration -->
            <div class="tbp-settings-section">
                <h2>🔗 Webhook Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <input type="text" readonly value="<?php echo esc_attr(get_rest_url() . 'telegram-blog-publisher/v1/webhook'); ?>" class="regular-text" id="webhook-url">
                            <button type="button" class="button" id="copy-webhook-url">📋 Copy</button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook Secret</th>
                        <td>
                            <div class="tbp-input-group">
                                <input type="password" name="webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="regular-text tbp-api-input" placeholder="Enter webhook secret..." id="webhook-secret">
                                <button type="button" class="tbp-toggle-btn" onclick="toggleApiKey('webhook-secret')">👁️</button>
                                <button type="button" class="tbp-test-btn" onclick="testWebhook()">🧪 Test</button>
                            </div>
                            <p class="description">Generate a secure secret for webhook authentication</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Multiple API Keys Section -->
            <div class="tbp-settings-section">
                <h2>🤖 AI Service API Keys</h2>
                <p class="description">Add API keys for different AI services. The plugin will try them in order if one fails.</p>
                
                <div class="tbp-api-grid">
                    <!-- Grok (X.AI) -->
                    <div class="tbp-api-card">
                        <h3>🦄 Grok (X.AI) - <span class="tbp-recommended">RECOMMENDED</span></h3>
                        <p class="tbp-api-description">Fast, generous free tier, great for quick responses</p>
                        <div class="tbp-input-group">
                            <input type="password" name="api_key_grok" value="<?php echo esc_attr($api_keys['grok'] ?? ''); ?>" class="tbp-api-input" placeholder="Enter your Grok API key..." id="api-key-grok">
                            <button type="button" class="tbp-toggle-btn" onclick="toggleApiKey('api-key-grok')">👁️</button>
                            <button type="button" class="tbp-test-btn" onclick="testApiKey('grok', 'api-key-grok')">🧪 Test</button>
                        </div>
                        <div class="tbp-api-help">
                            <p><strong>Get Grok API Key:</strong></p>
                            <ol>
                                <li>Visit <a href="https://console.x.ai/" target="_blank">X.AI Console</a></li>
                                <li>Sign up/Login with your X account</li>
                                <li>Go to API Keys section</li>
                                <li>Create a new API key</li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- DeepSeek -->
                    <div class="tbp-api-card">
                        <h3>🔍 DeepSeek Chat</h3>
                        <p class="tbp-api-description">Cost-effective, good quality, fast responses</p>
                        <div class="tbp-input-group">
                            <input type="password" name="api_key_deepseek" value="<?php echo esc_attr($api_keys['deepseek'] ?? ''); ?>" class="tbp-api-input" placeholder="Enter your DeepSeek API key..." id="api-key-deepseek">
                            <button type="button" class="tbp-toggle-btn" onclick="toggleApiKey('api-key-deepseek')">👁️</button>
                            <button type="button" class="tbp-test-btn" onclick="testApiKey('deepseek', 'api-key-deepseek')">🧪 Test</button>
                        </div>
                        <div class="tbp-api-help">
                            <p><strong>Get DeepSeek API Key:</strong></p>
                            <ol>
                                <li>Visit <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek Platform</a></li>
                                <li>Sign up/Login to your account</li>
                                <li>Go to API Keys section</li>
                                <li>Create a new API key</li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- OpenAI -->
                    <div class="tbp-api-card">
                        <h3>🧠 OpenAI GPT-4</h3>
                        <p class="tbp-api-description">High quality, reliable, premium service</p>
                        <div class="tbp-input-group">
                            <input type="password" name="api_key_openai" value="<?php echo esc_attr($api_keys['openai'] ?? ''); ?>" class="tbp-api-input" placeholder="Enter your OpenAI API key..." id="api-key-openai">
                            <button type="button" class="tbp-toggle-btn" onclick="toggleApiKey('api-key-openai')">👁️</button>
                            <button type="button" class="tbp-test-btn" onclick="testApiKey('openai', 'api-key-openai')">🧪 Test</button>
                        </div>
                        <div class="tbp-api-help">
                            <p><strong>Get OpenAI API Key:</strong></p>
                            <ol>
                                <li>Visit <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></li>
                                <li>Sign up/Login to your account</li>
                                <li>Go to API Keys section</li>
                                <li>Create a new API key</li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Claude -->
                    <div class="tbp-api-card">
                        <h3>🎭 Claude 3.5 Sonnet</h3>
                        <p class="tbp-api-description">Excellent quality, good for complex topics</p>
                        <div class="tbp-input-group">
                            <input type="password" name="api_key_claude" value="<?php echo esc_attr($api_keys['claude'] ?? ''); ?>" class="tbp-api-input" placeholder="Enter your Claude API key..." id="api-key-claude">
                            <button type="button" class="tbp-toggle-btn" onclick="toggleApiKey('api-key-claude')">👁️</button>
                            <button type="button" class="tbp-test-btn" onclick="testApiKey('claude', 'api-key-claude')">🧪 Test</button>
                        </div>
                        <div class="tbp-api-help">
                            <p><strong>Get Claude API Key:</strong></p>
                            <ol>
                                <li>Visit <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a></li>
                                <li>Sign up/Login to your account</li>
                                <li>Go to API Keys section</li>
                                <li>Create a new API key</li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Gemini -->
                    <div class="tbp-api-card">
                        <h3>💎 Gemini Pro</h3>
                        <p class="tbp-api-description">Google's AI, good for general content</p>
                        <div class="tbp-input-group">
                            <input type="password" name="api_key_gemini" value="<?php echo esc_attr($api_keys['gemini'] ?? ''); ?>" class="tbp-api-input" placeholder="Enter your Gemini API key..." id="api-key-gemini">
                            <button type="button" class="tbp-toggle-btn" onclick="toggleApiKey('api-key-gemini')">👁️</button>
                            <button type="button" class="tbp-test-btn" onclick="testApiKey('gemini', 'api-key-gemini')">🧪 Test</button>
                        </div>
                        <div class="tbp-api-help">
                            <p><strong>Get Gemini API Key:</strong></p>
                            <ol>
                                <li>Visit <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                                <li>Sign up/Login with your Google account</li>
                                <li>Create a new API key</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- API Fallback Information -->
            <div class="tbp-settings-section">
                <h2>🔄 API Fallback System</h2>
                <div class="tbp-info-box">
                    <h3>How it works:</h3>
                    <ol>
                        <li><strong>Grok (X.AI)</strong> - Tried first (fastest, generous free tier)</li>
                        <li><strong>DeepSeek</strong> - Tried second (cost-effective, reliable)</li>
                        <li><strong>OpenAI</strong> - Tried third (high quality, premium)</li>
                        <li><strong>Claude</strong> - Tried fourth (excellent for complex topics)</li>
                        <li><strong>Gemini</strong> - Tried last (Google's AI, general purpose)</li>
                    </ol>
                    <p><strong>💡 Tip:</strong> Add multiple API keys to ensure your blog posts are always generated, even if one service is down!</p>
                </div>
            </div>
            
            <p class="submit">
                <button type="submit" class="button button-primary button-large">💾 Save Settings</button>
            </p>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Copy webhook URL
    $('#copy-webhook-url').click(function() {
        $('#webhook-url').select();
        document.execCommand('copy');
        $(this).text('✅ Copied!');
        setTimeout(() => $(this).text('📋 Copy'), 2000);
    });
    
    // Save settings
    $('#tbp-settings-form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tbp_save_settings',
                nonce: tbp_ajax.nonce,
                webhook_secret: $('input[name="webhook_secret"]').val(),
                api_key_grok: $('input[name="api_key_grok"]').val(),
                api_key_deepseek: $('input[name="api_key_deepseek"]').val(),
                api_key_openai: $('input[name="api_key_openai"]').val(),
                api_key_claude: $('input[name="api_key_claude"]').val(),
                api_key_gemini: $('input[name="api_key_gemini"]').val()
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Settings saved successfully!', 'success');
                } else {
                    showNotice('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Error saving settings', 'error');
            }
        });
    });
    
    // Reactivate license
    $('#reactivate-license').click(function() {
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tbp_reactivate_license',
                nonce: tbp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showNotice('Error: ' + response.data, 'error');
                }
            }
        });
    });
});

function toggleApiKey(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👁️';
    }
}

function testApiKey(service, inputId) {
    const apiKey = document.getElementById(inputId).value;
    const button = document.getElementById(inputId).nextElementSibling.nextElementSibling;
    
    if (!apiKey) {
        showNotice('Please enter an API key first', 'error');
        return;
    }
    
    button.textContent = '⏳ Testing...';
    button.disabled = true;
    
    jQuery.ajax({
        url: tbp_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'tbp_test_api_key',
            nonce: tbp_ajax.nonce,
            service: service,
            api_key: apiKey
        },
        success: function(response) {
            if (response.success) {
                showNotice('✅ ' + service + ' API key is working!', 'success');
                button.textContent = '✅ Working';
                button.style.background = '#46b450';
            } else {
                showNotice('❌ ' + service + ' API error: ' + response.data, 'error');
                button.textContent = '❌ Failed';
                button.style.background = '#dc3232';
            }
        },
        error: function() {
            showNotice('❌ Error testing ' + service + ' API key', 'error');
            button.textContent = '❌ Error';
            button.style.background = '#dc3232';
        },
        complete: function() {
            button.disabled = false;
            setTimeout(() => {
                button.textContent = '🧪 Test';
                button.style.background = '';
            }, 3000);
        }
    });
}

function testWebhook() {
    const button = document.querySelector('[onclick="testWebhook()"]');
    button.textContent = '⏳ Testing...';
    button.disabled = true;
    
    jQuery.ajax({
        url: tbp_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'tbp_test_webhook',
            nonce: tbp_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                showNotice('✅ Webhook test successful!', 'success');
                button.textContent = '✅ Working';
                button.style.background = '#46b450';
            } else {
                showNotice('❌ Webhook test failed: ' + response.data, 'error');
                button.textContent = '❌ Failed';
                button.style.background = '#dc3232';
            }
        },
        error: function() {
            showNotice('❌ Error testing webhook', 'error');
            button.textContent = '❌ Error';
            button.style.background = '#dc3232';
        },
        complete: function() {
            button.disabled = false;
            setTimeout(() => {
                button.textContent = '🧪 Test';
                button.style.background = '';
            }, 3000);
        }
    });
}

function showNotice(message, type) {
    const notice = jQuery('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
    jQuery('.wrap h1').after(notice);
    setTimeout(() => notice.fadeOut(), 5000);
}
</script>