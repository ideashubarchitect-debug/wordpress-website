<?php
/**
 * Admin Dashboard Template
 */

$webhook_url = get_rest_url() . 'telegram-blog-publisher/v1/webhook';
$webhook_secret = get_option('tbp_webhook_secret', '');
$license_status = get_option('tbp_license_status', 'invalid');
$license_key = get_option('tbp_license_key', '');
$recent_posts = get_posts(array(
    'meta_key' => '_tbp_telegram_generated',
    'meta_value' => true,
    'numberposts' => 10,
    'post_status' => 'any',
));
$logs = get_option('tbp_logs', array());
$recent_logs = array_slice(array_reverse($logs), 0, 5);
?>

<div class="wrap tbp-dashboard">
    <h1>📱 Telegram Blog Publisher</h1>
    
    <?php if ($license_status !== 'valid'): ?>
    <div class="notice notice-warning">
        <p><strong>License Status:</strong> <?php echo esc_html($license_status); ?></p>
        <button type="button" class="button" id="reactivate-license">Reactivate License</button>
    </div>
    <?php endif; ?>
    
    <div class="tbp-dashboard-grid">
        <!-- Webhook Info Card -->
        <div class="tbp-card">
            <h2>🔗 Webhook Configuration</h2>
            <div class="tbp-webhook-info">
                <p><strong>Webhook URL:</strong></p>
                <div class="tbp-url-container">
                    <input type="text" value="<?php echo esc_url($webhook_url); ?>" readonly class="tbp-url-input" id="webhook-url">
                    <button type="button" class="button" onclick="copyToClipboard('webhook-url')">Copy</button>
                </div>
                
                <p><strong>Webhook Secret:</strong></p>
                <div class="tbp-secret-container">
                    <input type="password" value="<?php echo esc_attr($webhook_secret); ?>" readonly class="tbp-secret-input" id="webhook-secret">
                    <button type="button" class="button" onclick="toggleSecret('webhook-secret')">Show</button>
                </div>
                
                <div class="tbp-test-webhook">
                    <button type="button" class="button button-primary" id="test-webhook">Test Webhook</button>
                    <div id="test-result" class="tbp-test-result"></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Card -->
        <div class="tbp-card">
            <h2>📊 Quick Stats</h2>
            <div class="tbp-stats">
                <div class="tbp-stat-item">
                    <span class="tbp-stat-number"><?php echo count($recent_posts); ?></span>
                    <span class="tbp-stat-label">Posts Generated</span>
                </div>
                <div class="tbp-stat-item">
                    <span class="tbp-stat-number"><?php echo count($logs); ?></span>
                    <span class="tbp-stat-label">Total Requests</span>
                </div>
                <div class="tbp-stat-item">
                    <span class="tbp-stat-number"><?php echo count(array_filter($recent_posts, function($post) { return $post->post_status === 'publish'; })); ?></span>
                    <span class="tbp-stat-label">Published</span>
                </div>
            </div>
        </div>
        
        <!-- Recent Posts Card -->
        <div class="tbp-card tbp-card-wide">
            <h2>📝 Recent Generated Posts</h2>
            <?php if (empty($recent_posts)): ?>
                <p>No posts generated yet. Send a message to your Telegram bot to create your first post!</p>
            <?php else: ?>
                <div class="tbp-posts-list">
                    <?php foreach ($recent_posts as $post): ?>
                        <div class="tbp-post-item">
                            <div class="tbp-post-info">
                                <h4><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a></h4>
                                <p class="tbp-post-meta">
                                    <span class="tbp-post-status status-<?php echo esc_attr($post->post_status); ?>"><?php echo esc_html(ucfirst($post->post_status)); ?></span>
                                    <span class="tbp-post-date"><?php echo get_the_date('M j, Y g:i A', $post); ?></span>
                                </p>
                            </div>
                            <div class="tbp-post-actions">
                                <a href="<?php echo get_permalink($post->ID); ?>" class="button button-small" target="_blank">View</a>
                                <a href="<?php echo get_edit_post_link($post->ID); ?>" class="button button-small">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Activity Card -->
        <div class="tbp-card tbp-card-wide">
            <h2>📋 Recent Activity</h2>
            <?php if (empty($recent_logs)): ?>
                <p>No activity yet.</p>
            <?php else: ?>
                <div class="tbp-logs-list">
                    <?php foreach ($recent_logs as $log): ?>
                        <div class="tbp-log-item">
                            <div class="tbp-log-icon">
                                <?php if ($log['action'] === 'webhook_received'): ?>
                                    📥
                                <?php elseif ($log['action'] === 'post_created'): ?>
                                    ✅
                                <?php else: ?>
                                    📝
                                <?php endif; ?>
                            </div>
                            <div class="tbp-log-content">
                                <div class="tbp-log-action"><?php echo esc_html(ucfirst(str_replace('_', ' ', $log['action']))); ?></div>
                                <div class="tbp-log-time"><?php echo esc_html($log['timestamp']); ?></div>
                                <?php if (isset($log['post_id'])): ?>
                                    <div class="tbp-log-details">
                                        <a href="<?php echo get_edit_post_link($log['post_id']); ?>">Post #<?php echo $log['post_id']; ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Test Card -->
        <div class="tbp-card">
            <h2>🧪 Quick Test</h2>
            <form id="quick-test-form">
                <p>
                    <label for="test-topic">Topic:</label>
                    <input type="text" id="test-topic" name="topic" placeholder="e.g., Benefits of Remote Work" class="regular-text">
                </p>
                <p>
                    <label for="test-details">Details:</label>
                    <textarea id="test-details" name="details" placeholder="e.g., Discuss the advantages of working from home, productivity tips, and work-life balance." rows="3" class="large-text"></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary">Generate Content</button>
                </p>
            </form>
            <div id="generated-content" class="tbp-generated-content" style="display: none;">
                <h4>Generated Content:</h4>
                <div id="content-preview"></div>
            </div>
        </div>
    </div>
    
    <!-- n8n Integration Guide -->
    <div class="tbp-card tbp-card-full">
        <h2>🔧 n8n Integration Guide</h2>
        <div class="tbp-integration-guide">
            <h3>Step 1: Create n8n Workflow</h3>
            <ol>
                <li>Open n8n and create a new workflow</li>
                <li>Add a "Telegram Trigger" node</li>
                <li>Configure your Telegram bot token</li>
                <li>Add a "HTTP Request" node</li>
                <li>Set the URL to: <code><?php echo esc_url($webhook_url); ?></code></li>
                <li>Set method to POST</li>
                <li>Add headers: <code>X-Webhook-Secret: <?php echo esc_attr($webhook_secret); ?></code></li>
                <li>Set Content-Type to application/json</li>
            </ol>
            
            <h3>Step 2: Configure Data Mapping</h3>
            <p>Map Telegram message data to the webhook payload:</p>
            <pre><code>{
  "topic": "{{ $json.message.text }}",
  "details": "{{ $json.message.text }}",
  "category": "General",
  "tags": "telegram, auto-generated",
  "status": "draft"
}</code></pre>
            
            <h3>Step 3: Test the Integration</h3>
            <p>Send a message to your Telegram bot with a topic and details. The bot will automatically create a blog post on your WordPress site!</p>
            
            <h3>Example Telegram Messages</h3>
            <div class="tbp-examples">
                <div class="tbp-example">
                    <strong>Simple Topic:</strong><br>
                    <code>Write about the benefits of meditation</code>
                </div>
                <div class="tbp-example">
                    <strong>Topic + Details:</strong><br>
                    <code>Topic: Remote Work Tips<br>Details: Share practical advice for staying productive while working from home, including time management and communication strategies.</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = element.nextElementSibling;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    setTimeout(() => {
        button.textContent = originalText;
    }, 2000);
}

function toggleSecret(elementId) {
    const element = document.getElementById(elementId);
    const button = element.nextElementSibling;
    
    if (element.type === 'password') {
        element.type = 'text';
        button.textContent = 'Hide';
    } else {
        element.type = 'password';
        button.textContent = 'Show';
    }
}

jQuery(document).ready(function($) {
    // Reactivate license
    $('#reactivate-license').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).text('Reactivating...');
        
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
                    alert('Failed to reactivate license: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to reactivate license: Network error');
            },
            complete: function() {
                button.prop('disabled', false).text('Reactivate License');
            }
        });
    });
    
    // Test webhook
    $('#test-webhook').on('click', function() {
        const button = $(this);
        const resultDiv = $('#test-result');
        
        button.prop('disabled', true).text('Testing...');
        resultDiv.html('<div class="tbp-loading">Testing webhook...</div>');
        
        $.ajax({
            url: tbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tbp_test_webhook',
                nonce: tbp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="tbp-success">✅ Webhook test successful! Post created: <a href="' + response.data.edit_url + '">Edit Post</a></div>');
                } else {
                    resultDiv.html('<div class="tbp-error">❌ Webhook test failed: ' + response.data + '</div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="tbp-error">❌ Webhook test failed: Network error</div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Test Webhook');
            }
        });
    });
    
    // Quick test form
    $('#quick-test-form').on('submit', function(e) {
        e.preventDefault();
        
        const topic = $('#test-topic').val();
        const details = $('#test-details').val();
        
        if (!topic || !details) {
            alert('Please fill in both topic and details');
            return;
        }
        
        const button = $(this).find('button[type="submit"]');
        const contentDiv = $('#generated-content');
        const previewDiv = $('#content-preview');
        
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
            success: function(response) {
                if (response.success) {
                    previewDiv.html('<div class="tbp-content-preview">' + response.data.content + '</div>');
                } else {
                    previewDiv.html('<div class="tbp-error">❌ Content generation failed: ' + response.data + '</div>');
                }
            },
            error: function() {
                previewDiv.html('<div class="tbp-error">❌ Content generation failed: Network error</div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Generate Content');
            }
        });
    });
});
</script>
