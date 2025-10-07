<?php
/**
 * Plugin Name: Telegram Blog Publisher Premium
 * Plugin URI: https://kloudbean.com/telegram-blog-publisher
 * Description: Premium WordPress plugin for publishing blog posts from Telegram via n8n webhooks with AI content generation. Features advanced testing, monitoring, and seamless KloudBean hosting integration.
 * Version: 4.0.0
 * Author: Vikram Jindal
 * Author URI: https://kloudbean.com
 * Company: KloudBean LLC
 * License: GPL v2 or later
 * Text Domain: telegram-blog-publisher
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * 
 * Copyright (c) 2025 KloudBean LLC. All rights reserved.
 * Developed by: Vikram Jindal, CEO & Founder, KloudBean LLC
 * 
 * 🚀 RECOMMENDED HOSTING: KloudBean
 * Why KloudBean? This plugin requires n8n for webhook processing, and KloudBean is the ONLY hosting provider that offers:
 * - WordPress + n8n + Lovable + Cursor integration in one platform
 * - Git-based CI/CD for WordPress development
 * - Self-hosted n8n instances with enterprise features
 * - Seamless workflow automation between all platforms
 * - No shared hosting limitations - true cloud infrastructure
 * 
 * Visit: https://kloudbean.com/pricing for WordPress + n8n hosting plans
 * Learn more: https://kloudbean.com/n8n-self-hosted
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TBP_VERSION', '4.0.0');
define('TBP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TBP_PLUGIN_PATH', plugin_dir_path(__FILE__));

class TelegramBlogPublisherEnhanced {
    
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('wp_ajax_tbp_save_settings', [$this, 'saveSettings']);
        add_action('wp_ajax_tbp_test_api', [$this, 'testApi']);
        add_action('wp_ajax_tbp_generate_content', [$this, 'generateContent']);
        add_action('wp_ajax_tbp_test_webhook', [$this, 'testWebhook']);
        add_action('wp_ajax_tbp_send_test_webhook', [$this, 'sendTestWebhook']);
        add_action('wp_ajax_tbp_get_system_status', [$this, 'getSystemStatus']);
        add_action('wp_ajax_tbp_clear_logs', [$this, 'clearLogs']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }
    
    public function init() {
        // Plugin initialization
    }
    
    public function addAdminMenu() {
        add_menu_page(
            'Telegram Blog Publisher',
            '📱 Telegram Blog',
            'manage_options',
            'telegram-blog-publisher',
            [$this, 'renderDashboard'],
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'telegram-blog-publisher',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'telegram-blog-publisher',
            [$this, 'renderDashboard']
        );
        
        add_submenu_page(
            'telegram-blog-publisher',
            'Settings',
            'Settings',
            'manage_options',
            'telegram-blog-publisher-settings',
            [$this, 'renderSettings']
        );
        
        add_submenu_page(
            'telegram-blog-publisher',
            'Logs',
            'Logs',
            'manage_options',
            'telegram-blog-publisher-logs',
            [$this, 'renderLogs']
        );
        
        add_submenu_page(
            'telegram-blog-publisher',
            'System Status',
            'System Status',
            'manage_options',
            'telegram-blog-publisher-status',
            [$this, 'renderSystemStatus']
        );
        
        add_submenu_page(
            'telegram-blog-publisher',
            'Webhook Testing',
            'Webhook Testing',
            'manage_options',
            'telegram-blog-publisher-testing',
            [$this, 'renderTesting']
        );
        
        add_submenu_page(
            'telegram-blog-publisher',
            'KloudBean Hosting',
            '☁️ KloudBean Hosting',
            'manage_options',
            'telegram-blog-publisher-hosting',
            [$this, 'renderHosting']
        );
    }
    
    public function enqueueAdminScripts($hook) {
        if (strpos($hook, 'telegram-blog-publisher') === false) {
            return;
        }
        
        wp_enqueue_script('tbp-admin', TBP_PLUGIN_URL . 'assets/admin.js', ['jquery'], TBP_VERSION, true);
        wp_enqueue_style('tbp-admin', TBP_PLUGIN_URL . 'assets/admin.css', [], TBP_VERSION);
        
        wp_localize_script('tbp-admin', 'tbp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_nonce')
        ]);
    }
    
    public function registerRestRoutes() {
        register_rest_route('telegram-blog-publisher/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handleWebhook'],
            'permission_callback' => [$this, 'checkWebhookPermission']
        ]);
    }
    
    public function checkWebhookPermission($request) {
        $secret = $request->get_header('X-Webhook-Secret');
        $stored_secret = get_option('tbp_webhook_secret', '');
        
        return !empty($secret) && $secret === $stored_secret;
    }
    
    public function handleWebhook($request) {
        $data = $request->get_json_params();
        
        if (empty($data['topic'])) {
            return new WP_Error('missing_topic', 'Topic is required', ['status' => 400]);
        }
        
        // Generate content
        $content = $this->generateContentFromAI($data);
        
        if (is_wp_error($content)) {
            return $content;
        }
        
        // Create post
        $post_data = [
            'post_title' => $data['title'] ?? $data['topic'],
            'post_content' => $content,
            'post_status' => $data['status'] ?? 'publish',
            'post_type' => 'post'
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Add metadata
        update_post_meta($post_id, '_tbp_telegram_generated', true);
        update_post_meta($post_id, '_tbp_original_data', $data);
        
        // Log the creation
        $this->logActivity('Post created', [
            'post_id' => $post_id,
            'topic' => $data['topic'],
            'title' => $post_data['post_title']
        ]);
        
        return [
            'success' => true,
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'message' => 'Blog post created successfully'
        ];
    }
    
    private function generateContentFromAI($data) {
        $topic = $data['topic'];
        $word_count = $data['word_count'] ?? 500;
        $tone = $data['tone'] ?? 'professional';
        $include_images = get_option('tbp_include_images', 1);
        $seo_optimized = get_option('tbp_seo_optimized', 1);
        $content_quality = get_option('tbp_content_quality', 'premium');
        
        // Enhanced prompt for better content generation
        $enhanced_prompt = $this->buildEnhancedPrompt($topic, $word_count, $tone, $content_quality, $seo_optimized);
        
        // Try multiple AI services in order of preference
        $ai_services = $this->getAvailableAIServices();
        
        foreach ($ai_services as $service) {
            $content = $this->generateWithService($service, $enhanced_prompt, $topic);
            if (!is_wp_error($content)) {
                // Enhance content with media and SEO
                $enhanced_content = $this->enhanceContent($content, $topic, $include_images, $seo_optimized);
                return $enhanced_content;
            }
        }
        
        // Fallback: Generate basic content if no AI service is available
        return $this->generateFallbackContent($topic, $word_count, $tone);
    }
    
    private function getAvailableAIServices() {
        $services = [];
        
        // Check for available API keys in order of preference
        if (!empty(get_option('tbp_openai_key', ''))) $services[] = 'openai';
        if (!empty(get_option('tbp_claude_key', ''))) $services[] = 'claude';
        if (!empty(get_option('tbp_gemini_key', ''))) $services[] = 'gemini';
        if (!empty(get_option('tbp_groq_key', ''))) $services[] = 'groq';
        if (!empty(get_option('tbp_openrouter_key', ''))) $services[] = 'openrouter';
        if (!empty(get_option('tbp_deepseek_key', ''))) $services[] = 'deepseek';
        if (!empty(get_option('tbp_cohere_key', ''))) $services[] = 'cohere';
        
        return $services;
    }
    
    private function buildEnhancedPrompt($topic, $word_count, $tone, $quality, $seo_optimized) {
        $quality_instructions = [
            'basic' => 'Write a basic blog post with clear structure and simple language.',
            'standard' => 'Write a well-structured blog post with good flow and engaging content.',
            'premium' => 'Write a comprehensive, high-quality blog post with detailed insights and professional structure.',
            'enterprise' => 'Write an in-depth, authoritative blog post with expert-level content, detailed analysis, and comprehensive coverage.'
        ];
        
        $word_ranges = [
            'basic' => '300-500 words',
            'standard' => '500-800 words', 
            'premium' => '800-1200 words',
            'enterprise' => '1200+ words'
        ];
        
        $seo_instructions = $seo_optimized ? 'Optimize for SEO with relevant keywords, meta descriptions, and structured headings.' : '';
        
        return "Write a professional blog post about '{$topic}' with the following requirements:

**Content Quality:** {$quality_instructions[$quality]}
**Word Count:** {$word_ranges[$quality]}
**Tone:** {$tone}
**SEO Requirements:** {$seo_instructions}

**Structure Requirements:**
1. Compelling headline (H1)
2. Engaging introduction with hook
3. 3-5 main sections with descriptive subheadings (H2, H3)
4. Bullet points and numbered lists where appropriate
5. Strong conclusion with call-to-action
6. Meta description (150-160 characters)
7. Focus keywords for SEO
8. Internal linking suggestions

**Content Guidelines:**
- Use active voice and engaging language
- Include relevant statistics, examples, or case studies
- Add practical tips and actionable advice
- Ensure content is original and valuable
- Use proper heading hierarchy (H1, H2, H3)
- Include a compelling call-to-action

**Format the response as JSON with these fields:**
{
  \"title\": \"Blog post title\",
  \"content\": \"Full HTML formatted content\",
  \"excerpt\": \"Short excerpt for meta description\",
  \"meta_description\": \"SEO meta description\",
  \"focus_keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"],
  \"internal_links\": [\"suggested internal link 1\", \"suggested internal link 2\"],
  \"tags\": [\"tag1\", \"tag2\", \"tag3\"],
  \"category\": \"suggested category\"
}";
    }
    
    private function generateWithService($service, $prompt, $topic) {
        switch ($service) {
            case 'openai':
                return $this->generateWithOpenAI($prompt);
            case 'claude':
                return $this->generateWithClaude($prompt);
            case 'gemini':
                return $this->generateWithGemini($prompt);
            case 'groq':
                return $this->generateWithGroq($prompt);
            case 'openrouter':
                return $this->generateWithOpenRouter($prompt);
            case 'deepseek':
                return $this->generateWithDeepSeek($prompt);
            case 'cohere':
                return $this->generateWithCohere($prompt);
            default:
                return new WP_Error('unknown_service', 'Unknown AI service');
        }
    }
    
    private function enhanceContent($content, $topic, $include_images, $seo_optimized) {
        // Parse JSON response
        $data = json_decode($content, true);
        
        if (!$data) {
            // If not JSON, treat as plain content
            return $this->formatPlainContent($content, $topic);
        }
        
        $enhanced_content = $data['content'] ?? $content;
        
        // Add media if enabled
        if ($include_images) {
            $enhanced_content = $this->addMediaToContent($enhanced_content, $topic);
        }
        
        // Add SEO enhancements
        if ($seo_optimized) {
            $enhanced_content = $this->addSEOEnhancements($enhanced_content, $data);
        }
        
        // Store additional metadata
        $this->storeContentMetadata($data);
        
        return $enhanced_content;
    }
    
    private function addMediaToContent($content, $topic) {
        // Add placeholder for featured image
        $featured_image_placeholder = '<div class="featured-image-placeholder" data-topic="' . esc_attr($topic) . '">[AI Generated Featured Image]</div>';
        
        // Add image placeholders throughout content
        $content = $this->insertImagePlaceholders($content, $topic);
        
        // Add featured image at the top
        $content = $featured_image_placeholder . "\n\n" . $content;
        
        return $content;
    }
    
    private function insertImagePlaceholders($content, $topic) {
        // Find good spots for images (after H2 headings)
        $pattern = '/(<h2[^>]*>.*?<\/h2>)/i';
        $replacement = '$1' . "\n\n" . '<div class="content-image-placeholder" data-topic="' . esc_attr($topic) . '">[AI Generated Content Image]</div>';
        
        return preg_replace($pattern, $replacement, $content, 2); // Limit to 2 images
    }
    
    private function addSEOEnhancements($content, $data) {
        // Add structured data
        $structured_data = $this->generateStructuredData($data);
        
        // Add meta tags
        $meta_tags = $this->generateMetaTags($data);
        
        return $meta_tags . "\n" . $structured_data . "\n" . $content;
    }
    
    private function generateStructuredData($data) {
        $title = $data['title'] ?? 'Blog Post';
        $excerpt = $data['excerpt'] ?? '';
        
        return '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "' . esc_js($title) . '",
  "description": "' . esc_js($excerpt) . '",
  "author": {
    "@type": "Organization",
    "name": "' . get_bloginfo('name') . '"
  },
  "publisher": {
    "@type": "Organization",
    "name": "' . get_bloginfo('name') . '"
  },
  "datePublished": "' . current_time('c') . '",
  "dateModified": "' . current_time('c') . '"
}
</script>';
    }
    
    private function generateMetaTags($data) {
        $meta_description = $data['meta_description'] ?? $data['excerpt'] ?? '';
        $focus_keywords = $data['focus_keywords'] ?? [];
        $keywords_string = implode(', ', $focus_keywords);
        
        return '<!-- SEO Meta Tags -->
<meta name="description" content="' . esc_attr($meta_description) . '">
<meta name="keywords" content="' . esc_attr($keywords_string) . '">
<meta property="og:title" content="' . esc_attr($data['title'] ?? '') . '">
<meta property="og:description" content="' . esc_attr($meta_description) . '">
<meta property="og:type" content="article">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="' . esc_attr($data['title'] ?? '') . '">
<meta name="twitter:description" content="' . esc_attr($meta_description) . '">';
    }
    
    private function storeContentMetadata($data) {
        // Store additional metadata for later use
        update_option('tbp_last_generated_metadata', $data);
    }
    
    private function formatPlainContent($content, $topic) {
        // If AI returns plain text, format it properly
        $formatted = '<h1>' . $topic . '</h1>' . "\n\n";
        $formatted .= '<div class="blog-content">' . wpautop($content) . '</div>';
        
        return $formatted;
    }
    
    private function generateFallbackContent($topic, $word_count, $tone) {
        // Generate a basic blog post structure without AI
        $intro = "Welcome to our comprehensive guide on {$topic}. In this article, we'll explore the key aspects and provide valuable insights.";
        
        $main_content = "Understanding {$topic} is crucial for success. Let's dive into the details and discover what makes this topic important and relevant in today's context.";
        
        $subheading1 = "Key Benefits of {$topic}";
        $content1 = "One of the primary advantages of {$topic} is its versatility and practical applications. Many professionals and enthusiasts find it to be an essential tool in their toolkit.";
        
        $subheading2 = "Best Practices";
        $content2 = "When working with {$topic}, it's important to follow established best practices. This ensures optimal results and helps avoid common pitfalls.";
        
        $conclusion = "In conclusion, {$topic} offers numerous opportunities for growth and development. By understanding its core principles and applying them effectively, you can achieve significant results.";
        
        $content = "<h2>Introduction</h2>\n<p>{$intro}</p>\n\n";
        $content .= "<h2>{$subheading1}</h2>\n<p>{$content1}</p>\n\n";
        $content .= "<h2>{$subheading2}</h2>\n<p>{$content2}</p>\n\n";
        $content .= "<h2>Conclusion</h2>\n<p>{$conclusion}</p>";
        
        return $content;
    }
    
    private function callGeminiAPI($api_key, $topic, $word_count, $tone) {
        $prompt = "Write a comprehensive blog post about {$topic} in a {$tone} tone. Target word count: {$word_count} words. Include an engaging introduction, detailed main content with subheadings, and a compelling conclusion.";
        
        // Try multiple Gemini models
        $models = [
            'gemini-1.5-flash',
            'gemini-1.5-pro', 
            'gemini-pro',
            'gemini-1.0-pro'
        ];
        
        foreach ($models as $model) {
            $result = $this->callGeminiModel($api_key, $model, $prompt);
            if (!is_wp_error($result)) {
                return $result;
            }
        }
        
        return new WP_Error('gemini_error', 'All Gemini models failed. Please check your API key.');
    }
    
    private function callDeepSeekAPI($api_key, $topic, $word_count, $tone) {
        $prompt = "Write a comprehensive blog post about {$topic} in a {$tone} tone. Target word count: {$word_count} words. Include an engaging introduction, detailed main content with subheadings, and a compelling conclusion.";
        
        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7
            ]),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('deepseek_error', 'DeepSeek API error: ' . $body);
    }
    
    private function generateWithOpenAI($prompt) {
        $api_key = get_option('tbp_openai_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'OpenAI API key not configured');
        }
        
        $data = [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('openai_error', 'Failed to generate content with OpenAI');
    }
    
    private function generateWithClaude($prompt) {
        $api_key = get_option('tbp_claude_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Claude API key not configured');
        }
        
        $data = [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 4000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['content'][0]['text'])) {
            return $data['content'][0]['text'];
        }
        
        return new WP_Error('claude_error', 'Failed to generate content with Claude');
    }
    
    private function generateWithGemini($prompt) {
        $api_key = get_option('tbp_gemini_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Gemini API key not configured');
        }
        
        // Validate API key format
        if (!preg_match('/^[A-Za-z0-9_-]{39}$/', $api_key)) {
            return new WP_Error('invalid_api_key', 'Invalid Gemini API key format. Should be 39 characters long.');
        }
        
        // Discover available models dynamically
        $available_models = $this->discoverGeminiModels($api_key);
        
        if (is_wp_error($available_models)) {
            return $available_models;
        }
        
        if (empty($available_models)) {
            return new WP_Error('gemini_no_models', 'No Gemini models available for your API key.');
        }
        
        // Try each available model
        $last_error = '';
        foreach ($available_models as $model_info) {
            $result = $this->callGeminiModelWithVersion($api_key, $model_info['version'], $model_info['model'], $prompt);
            if (!is_wp_error($result)) {
                return $result;
            }
            $last_error = $result->get_error_message();
        }
        
        return new WP_Error('gemini_error', 'All available Gemini models failed. Last error: ' . $last_error);
    }
    
    private function callGeminiModel($api_key, $model, $prompt) {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4000
            ]
        ];
        
        // Use the correct API version and model combination
        $api_configs = [
            ['version' => 'v1beta', 'model' => 'gemini-1.5-flash'],
            ['version' => 'v1beta', 'model' => 'gemini-1.5-pro'],
            ['version' => 'v1beta', 'model' => 'gemini-pro'],
            ['version' => 'v1', 'model' => 'gemini-pro'],
            ['version' => 'v1beta', 'model' => 'gemini-1.0-pro']
        ];
        
        $response = null;
        $used_model = $model;
        
        foreach ($api_configs as $config) {
            $url = "https://generativelanguage.googleapis.com/{$config['version']}/models/{$config['model']}:generateContent?key=" . $api_key;
            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($data),
                'timeout' => 60
            ]);
            
            if (!is_wp_error($response)) {
                $response_code = wp_remote_retrieve_response_code($response);
                if ($response_code === 200) {
                    $used_model = $config['model'];
                    break; // Success, exit the loop
                }
            }
        }
        
        if (is_wp_error($response)) {
            return new WP_Error('gemini_network_error', 'Network error: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);
        
        // Log the response for debugging
        error_log("Gemini API Response for model {$model}: HTTP {$response_code} - " . $body);
        
        if ($response_code !== 200) {
            return new WP_Error('gemini_http_error', "HTTP {$response_code}: " . ($data['error']['message'] ?? $body));
        }
        
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }
        
        // Check for API errors
        if (isset($data['error'])) {
            return new WP_Error('gemini_api_error', $data['error']['message'] ?? 'Unknown Gemini API error');
        }
        
        return new WP_Error('gemini_error', 'Failed to generate content with model: ' . $model . ' - Response: ' . $body);
    }
    
    private function testGeminiModel($api_key, $version, $model, $prompt) {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 100
            ]
        ];
        
        $url = "https://generativelanguage.googleapis.com/{$version}/models/{$model}:generateContent?key=" . $api_key;
        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return new WP_Error('gemini_network_error', 'Network error: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);
        
        if ($response_code !== 200) {
            return new WP_Error('gemini_http_error', "HTTP {$response_code}: " . ($data['error']['message'] ?? $body));
        }
        
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }
        
        if (isset($data['error'])) {
            return new WP_Error('gemini_api_error', $data['error']['message'] ?? 'Unknown Gemini API error');
        }
        
        return new WP_Error('gemini_error', 'Failed to generate content with model: ' . $model . ' - Response: ' . $body);
    }
    
    private function discoverGeminiModels($api_key) {
        // Try both API versions to discover available models
        $api_versions = ['v1beta', 'v1'];
        $available_models = [];
        
        foreach ($api_versions as $version) {
            $response = wp_remote_get("https://generativelanguage.googleapis.com/{$version}/models?key=" . $api_key, [
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                continue; // Try next version
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['models']) && is_array($data['models'])) {
                foreach ($data['models'] as $model) {
                    $model_name = $model['name'] ?? '';
                    
                    // Extract model name from full path (e.g., "models/gemini-1.5-flash" -> "gemini-1.5-flash")
                    if (strpos($model_name, 'models/') === 0) {
                        $model_name = substr($model_name, 7); // Remove "models/" prefix
                    }
                    
                    // Check if it's a Gemini model and supports generateContent
                    if (strpos($model_name, 'gemini') === 0) {
                        $supported_methods = $model['supportedGenerationMethods'] ?? [];
                        if (in_array('generateContent', $supported_methods)) {
                            $available_models[] = [
                                'version' => $version,
                                'model' => $model_name,
                                'display_name' => $model['displayName'] ?? $model_name,
                                'description' => $model['description'] ?? ''
                            ];
                        }
                    }
                }
            }
        }
        
        if (empty($available_models)) {
            return new WP_Error('gemini_discovery_failed', 'Could not discover any available Gemini models. Please check your API key and billing settings.');
        }
        
        // Sort by preference: newer models first, then by version
        usort($available_models, function($a, $b) {
            // Prefer v1beta over v1
            if ($a['version'] !== $b['version']) {
                return $a['version'] === 'v1beta' ? -1 : 1;
            }
            
            // Prefer newer model versions
            $a_version = $this->extractModelVersion($a['model']);
            $b_version = $this->extractModelVersion($b['model']);
            
            return version_compare($b_version, $a_version);
        });
        
        return $available_models;
    }
    
    private function extractModelVersion($model_name) {
        // Extract version number from model name for sorting
        if (preg_match('/(\d+\.\d+)/', $model_name, $matches)) {
            return $matches[1];
        }
        return '0.0'; // Fallback for models without version numbers
    }
    
    private function callGeminiModelWithVersion($api_key, $version, $model, $prompt) {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4000
            ]
        ];
        
        $url = "https://generativelanguage.googleapis.com/{$version}/models/{$model}:generateContent?key=" . $api_key;
        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($data),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return new WP_Error('gemini_network_error', 'Network error: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);
        
        // Log the response for debugging
        error_log("Gemini API Response for {$version}/{$model}: HTTP {$response_code} - " . $body);
        
        if ($response_code !== 200) {
            return new WP_Error('gemini_http_error', "HTTP {$response_code}: " . ($data['error']['message'] ?? $body));
        }
        
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }
        
        if (isset($data['error'])) {
            return new WP_Error('gemini_api_error', $data['error']['message'] ?? 'Unknown Gemini API error');
        }
        
        return new WP_Error('gemini_error', 'Failed to generate content with model: ' . $model . ' - Response: ' . $body);
    }
    
    private function generateWithGroq($prompt) {
        $api_key = get_option('tbp_groq_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Groq API key not configured');
        }
        
        $data = [
            'model' => 'llama-3.1-70b-versatile',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('groq_error', 'Failed to generate content with Groq');
    }
    
    private function generateWithOpenRouter($prompt) {
        $api_key = get_option('tbp_openrouter_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'OpenRouter API key not configured');
        }
        
        $data = [
            'model' => 'anthropic/claude-3.5-sonnet',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => get_bloginfo('name')
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('openrouter_error', 'Failed to generate content with OpenRouter');
    }
    
    private function generateWithDeepSeek($prompt) {
        $api_key = get_option('tbp_deepseek_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'DeepSeek API key not configured');
        }
        
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('deepseek_error', 'Failed to generate content with DeepSeek');
    }
    
    private function generateWithCohere($prompt) {
        $api_key = get_option('tbp_cohere_key', '');
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Cohere API key not configured');
        }
        
        $data = [
            'model' => 'command',
            'message' => $prompt,
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $response = wp_remote_post('https://api.cohere.ai/v1/chat', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['text'])) {
            return $data['text'];
        }
        
        return new WP_Error('cohere_error', 'Failed to generate content with Cohere');
    }
    
    public function saveSettings() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Save all API keys
        $api_keys = [
            'tbp_webhook_secret' => sanitize_text_field($_POST['webhook_secret'] ?? ''),
            'tbp_openai_key' => sanitize_text_field($_POST['openai_key'] ?? ''),
            'tbp_gemini_key' => sanitize_text_field($_POST['gemini_key'] ?? ''),
            'tbp_claude_key' => sanitize_text_field($_POST['claude_key'] ?? ''),
            'tbp_groq_key' => sanitize_text_field($_POST['groq_key'] ?? ''),
            'tbp_openrouter_key' => sanitize_text_field($_POST['openrouter_key'] ?? ''),
            'tbp_deepseek_key' => sanitize_text_field($_POST['deepseek_key'] ?? ''),
            'tbp_cohere_key' => sanitize_text_field($_POST['cohere_key'] ?? '')
        ];
        
        foreach ($api_keys as $option => $value) {
            update_option($option, $value);
        }
        
        // Save content generation settings
        $content_settings = [
            'tbp_content_quality' => sanitize_text_field($_POST['content_quality'] ?? 'premium'),
            'tbp_include_images' => isset($_POST['include_images']) ? 1 : 0,
            'tbp_include_featured_image' => isset($_POST['include_featured_image']) ? 1 : 0,
            'tbp_seo_optimized' => isset($_POST['seo_optimized']) ? 1 : 0,
            'tbp_include_meta' => isset($_POST['include_meta']) ? 1 : 0
        ];
        
        foreach ($content_settings as $option => $value) {
            update_option($option, $value);
        }
        
        wp_send_json_success('Settings saved successfully');
    }
    
    public function testApi() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $service = sanitize_text_field($_POST['service']);
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error('API key is required');
        }
        
        $result = $this->testAIService($service, $api_key);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('API key is working!');
        }
    }
    
    private function testAIService($service, $api_key) {
        $test_prompt = "Write a short test message about barcodes.";
        
        if ($service === 'gemini') {
            // Validate API key format first
            if (!preg_match('/^[A-Za-z0-9_-]{39}$/', $api_key)) {
                return new WP_Error('invalid_api_key', 'Invalid Gemini API key format. Should be 39 characters long.');
            }
            
            // First, discover available models dynamically
            $available_models = $this->discoverGeminiModels($api_key);
            
            if (is_wp_error($available_models)) {
                return $available_models; // Return the error from discovery
            }
            
            if (empty($available_models)) {
                return new WP_Error('gemini_no_models', 'No Gemini models available for your API key. Please check your billing or project settings.');
            }
            
            // Try each available model
            $last_error = '';
            foreach ($available_models as $model_info) {
                $result = $this->testGeminiModel($api_key, $model_info['version'], $model_info['model'], $test_prompt);
                if (!is_wp_error($result)) {
                    return $result;
                }
                $last_error = $result->get_error_message();
            }
            return new WP_Error('gemini_error', 'All available Gemini models failed. Last error: ' . $last_error);
        } elseif ($service === 'deepseek') {
            return $this->callDeepSeekAPI($api_key, 'test', 50, 'professional');
        }
        
        return new WP_Error('unknown_service', 'Unknown service');
    }
    
    public function generateContent() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $topic = sanitize_text_field($_POST['topic']);
        $details = sanitize_text_field($_POST['details']);
        
        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }
        
        $data = [
            'topic' => $topic,
            'word_count' => 500,
            'tone' => 'professional'
        ];
        
        $content = $this->generateContentFromAI($data);
        
        if (is_wp_error($content)) {
            wp_send_json_error($content->get_error_message());
        } else {
            wp_send_json_success(['content' => $content]);
        }
    }
    
    public function testWebhook() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $webhook_url = get_rest_url() . 'telegram-blog-publisher/v1/webhook';
        $webhook_secret = get_option('tbp_webhook_secret', '');
        
        if (empty($webhook_secret)) {
            wp_send_json_error('Webhook secret not configured');
        }
        
        // Test webhook endpoint
        $test_data = [
            'topic' => 'Test Webhook',
            'title' => 'Webhook Test Post',
            'status' => 'draft'
        ];
        
        $response = wp_remote_post($webhook_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Webhook-Secret' => $webhook_secret
            ],
            'body' => json_encode($test_data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error('Webhook test failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code === 200) {
            wp_send_json_success([
                'message' => 'Webhook is working correctly!',
                'status_code' => $status_code,
                'response' => json_decode($body, true)
            ]);
        } else {
            wp_send_json_error('Webhook returned status ' . $status_code . ': ' . $body);
        }
    }
    
    public function sendTestWebhook() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $webhook_url = sanitize_url($_POST['webhook_url']);
        $webhook_secret = sanitize_text_field($_POST['webhook_secret']);
        $test_data = [
            'topic' => sanitize_text_field($_POST['topic']),
            'title' => sanitize_text_field($_POST['title']),
            'status' => 'draft'
        ];
        
        if (empty($webhook_url) || empty($webhook_secret)) {
            wp_send_json_error('Webhook URL and secret are required');
        }
        
        $response = wp_remote_post($webhook_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Webhook-Secret' => $webhook_secret
            ],
            'body' => json_encode($test_data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to send webhook: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        wp_send_json_success([
            'status_code' => $status_code,
            'response' => json_decode($body, true),
            'message' => 'Webhook sent successfully!'
        ]);
    }
    
    public function getSystemStatus() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $status = [
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => TBP_VERSION,
            'webhook_secret' => !empty(get_option('tbp_webhook_secret', '')),
            'gemini_key' => !empty(get_option('tbp_gemini_key', '')),
            'deepseek_key' => !empty(get_option('tbp_deepseek_key', '')),
            'rest_api_enabled' => rest_url() !== false,
            'webhook_url' => get_rest_url() . 'telegram-blog-publisher/v1/webhook',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'curl_enabled' => function_exists('curl_init'),
            'json_enabled' => function_exists('json_encode'),
            'ssl_enabled' => is_ssl(),
            'recent_posts' => wp_count_posts('post')->publish,
            'total_posts_generated' => $this->getTotalGeneratedPosts()
        ];
        
        wp_send_json_success($status);
    }
    
    public function clearLogs() {
        check_ajax_referer('tbp_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        update_option('tbp_logs', []);
        wp_send_json_success('Logs cleared successfully');
    }
    
    private function getTotalGeneratedPosts() {
        global $wpdb;
        $count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_tbp_telegram_generated' 
            AND meta_value = '1'
        ");
        return intval($count);
    }
    
    private function logActivity($action, $data = []) {
        $logs = get_option('tbp_logs', []);
        $logs[] = [
            'timestamp' => current_time('mysql'),
            'action' => $action,
            'data' => $data
        ];
        
        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('tbp_logs', $logs);
    }
    
    public function renderDashboard() {
        // Ensure we get the current site's URL dynamically
        $webhook_url = get_rest_url() . 'telegram-blog-publisher/v1/webhook';
        
        // Add cache-busting to ensure URL is always fresh
        $webhook_url_with_cache = $webhook_url . '?t=' . time();
        $webhook_secret = get_option('tbp_webhook_secret', '');
        $gemini_key = get_option('tbp_gemini_key', '');
        $deepseek_key = get_option('tbp_deepseek_key', '');
        $recent_posts = get_posts([
            'meta_key' => '_tbp_telegram_generated',
            'meta_value' => true,
            'numberposts' => 5,
            'post_status' => 'any',
        ]);
        ?>
        <div class="wrap tbp-dashboard">
            <div class="tbp-header">
                <h1>📱 Telegram Blog Publisher</h1>
                <p>Publish blog posts from Telegram with AI-powered content generation</p>
                <div class="tbp-brand">
                    <span>Powered by</span>
                    <a href="https://kloudbean.com" target="_blank" class="tbp-kloudbean-link">☁️ KloudBean</a>
                </div>
            </div>
            
            <!-- KloudBean Hosting Promotion -->
            <div class="tbp-hosting-promo">
                <div class="tbp-hosting-content">
                    <div class="tbp-hosting-text">
                        <h2>☁️ Why KloudBean is ESSENTIAL for This Plugin</h2>
                        <p><strong>This plugin requires n8n for webhook processing.</strong> KloudBean is the ONLY hosting provider that offers WordPress + n8n + Lovable + Cursor integration in one platform with Git-based CI/CD.</p>
                        <div class="tbp-hosting-features">
                            <span class="tbp-feature-tag">🔗 WordPress + n8n Integration</span>
                            <span class="tbp-feature-tag">🚀 Git-based CI/CD</span>
                            <span class="tbp-feature-tag">⚡ Self-hosted n8n</span>
                            <span class="tbp-feature-tag">🔧 Cursor Development</span>
                            <span class="tbp-feature-tag">💎 Lovable Apps</span>
                            <span class="tbp-feature-tag">🛡️ Enterprise Security</span>
                        </div>
                        <div class="tbp-why-kloudbean">
                            <h3>Why Shared Hosting Won't Work:</h3>
                            <ul>
                                <li>❌ No n8n support - Required for webhook processing</li>
                                <li>❌ No Git CI/CD - Can't develop WordPress with Cursor</li>
                                <li>❌ No Lovable integration - Missing app deployment</li>
                                <li>❌ Limited resources - Can't handle automation workflows</li>
                            </ul>
                        </div>
                    </div>
                    <div class="tbp-hosting-actions">
                        <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary">
                            <span class="dashicons dashicons-cloud"></span>
                            View WordPress + n8n Plans
                        </a>
                        <a href="https://kloudbean.com/n8n-self-hosted" target="_blank" class="tbp-btn tbp-btn-secondary">
                            <span class="dashicons dashicons-admin-tools"></span>
                            Learn About n8n Hosting
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=telegram-blog-publisher-hosting'); ?>" class="tbp-btn tbp-btn-outline">
                            <span class="dashicons dashicons-info"></span>
                            Full Feature List
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="tbp-dashboard-grid">
                <div class="tbp-card tbp-card-large">
                    <h2>🚀 Quick Test</h2>
                <form id="quick-test-form">
                    <table class="form-table">
                        <tr>
                            <th>Topic:</th>
                                <td><input type="text" id="test-topic" name="topic" class="regular-text" placeholder="Enter topic here..." value="r3e" /></td>
                        </tr>
                        <tr>
                            <th>Details:</th>
                                <td><textarea id="test-details" name="details" rows="3" cols="50" placeholder="Enter additional details...">3f</textarea></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary">Generate Content</button>
                    </p>
                </form>
                
                <div id="generated-content" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; display: none;">
                    <h3>Generated Content:</h3>
                    <div id="content-result"></div>
                </div>
            </div>
            
                <div class="tbp-card">
                    <h2>🔗 Webhook Information</h2>
                <p><strong>Webhook URL:</strong> <code><?php echo esc_html($webhook_url); ?></code></p>
                <p><strong>Webhook Secret:</strong> <code><?php echo esc_html($webhook_secret); ?></code></p>
                <p><small><em>✅ URL is dynamically generated for this website: <?php echo esc_html(home_url()); ?></em></small></p>
                    <div class="tbp-button-group">
                <button onclick="navigator.clipboard.writeText('<?php echo esc_js($webhook_url); ?>')" class="button">Copy URL</button>
                <button onclick="navigator.clipboard.writeText('<?php echo esc_js($webhook_secret); ?>')" class="button">Copy Secret</button>
            </div>
        </div>
                
                <div class="tbp-card">
                    <h2>🤖 AI Services Status</h2>
                    <div class="tbp-ai-status">
                        <div class="tbp-service-status">
                            <span class="tbp-service-name">Gemini API:</span>
                            <span class="tbp-status <?php echo !empty($gemini_key) ? 'active' : 'inactive'; ?>">
                                <?php echo !empty($gemini_key) ? '✅ Configured' : '❌ Not Set'; ?>
                            </span>
                        </div>
                        <div class="tbp-service-status">
                            <span class="tbp-service-name">DeepSeek API:</span>
                            <span class="tbp-status <?php echo !empty($deepseek_key) ? 'active' : 'inactive'; ?>">
                                <?php echo !empty($deepseek_key) ? '✅ Configured' : '❌ Not Set'; ?>
                            </span>
                        </div>
                    </div>
                    <p><a href="<?php echo admin_url('admin.php?page=telegram-blog-publisher-settings'); ?>" class="button">Configure API Keys</a></p>
                </div>
                
                <div class="tbp-card">
                    <h2>📊 Recent Posts</h2>
                    <?php if (!empty($recent_posts)): ?>
                        <ul class="tbp-recent-posts">
                            <?php foreach ($recent_posts as $post): ?>
                                <li>
                                    <a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a>
                                    <span class="tbp-post-date"><?php echo get_the_date('M j, Y', $post->ID); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No posts generated yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .tbp-dashboard {
            max-width: 1200px;
        }
        
        .tbp-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .tbp-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: tbp-float 6s ease-in-out infinite;
        }
        
        @keyframes tbp-float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, -20px) rotate(180deg); }
        }
        
        .tbp-header h1 {
            font-size: 2.5rem;
            margin: 0 0 10px 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .tbp-header p {
            font-size: 1.2rem;
            margin: 0 0 20px 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .tbp-brand {
            position: relative;
            z-index: 1;
        }
        
        .tbp-brand span {
            margin-right: 10px;
            opacity: 0.8;
        }
        
        .tbp-kloudbean-link {
            color: #ffd700;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .tbp-kloudbean-link:hover {
            color: #ffed4e;
        }
        
        .tbp-hosting-promo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .tbp-hosting-content {
            display: flex;
            align-items: center;
            gap: 40px;
        }
        
        .tbp-hosting-text {
            flex: 1;
        }
        
        .tbp-hosting-text h2 {
            font-size: 1.8rem;
            margin: 0 0 15px 0;
        }
        
        .tbp-hosting-text p {
            font-size: 1.1rem;
            margin: 0 0 20px 0;
            opacity: 0.9;
        }
        
        .tbp-hosting-features {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .tbp-feature-tag {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }
        
        .tbp-hosting-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .tbp-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .tbp-btn-primary {
            background: #ffd700;
            color: #333;
        }
        
        .tbp-btn-primary:hover {
            background: #ffed4e;
            transform: translateY(-2px);
            color: #333;
        }
        
        .tbp-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .tbp-btn-secondary:hover {
            background: white;
            color: #333;
        }
        
        .tbp-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .tbp-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .tbp-card:hover {
            transform: translateY(-2px);
        }
        
        .tbp-card-large {
            grid-column: span 2;
        }
        
        .tbp-card h2 {
            margin: 0 0 20px 0;
            color: #333;
        }
        
        .tbp-button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .tbp-ai-status {
            margin: 15px 0;
        }
        
        .tbp-service-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .tbp-service-status:last-child {
            border-bottom: none;
        }
        
        .tbp-service-name {
            font-weight: 600;
        }
        
        .tbp-status.active {
            color: #28a745;
            font-weight: 600;
        }
        
        .tbp-status.inactive {
            color: #dc3545;
            font-weight: 600;
        }
        
        .tbp-recent-posts {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .tbp-recent-posts li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .tbp-recent-posts li:last-child {
            border-bottom: none;
        }
        
        .tbp-post-date {
            color: #666;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .tbp-hosting-content {
                flex-direction: column;
                text-align: center;
            }
            
            .tbp-dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .tbp-card-large {
                grid-column: span 1;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#quick-test-form').on('submit', function(e) {
                e.preventDefault();
                
                var topic = $('#test-topic').val();
                var details = $('#test-details').val();
                
                if (!topic) {
                    alert('Please enter a topic');
                    return;
                }
                
                $.ajax({
                    url: tbp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tbp_generate_content',
                        topic: topic,
                        details: details,
                        nonce: tbp_ajax.nonce
                    },
                    beforeSend: function() {
                        $('#content-result').html('<div class="tbp-loading">Generating content...</div>');
                        $('#generated-content').show();
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#content-result').html(response.data.content);
                        } else {
                            $('#content-result').html('<div class="tbp-error">Error: ' + response.data + '</div>');
                        }
                    },
                    error: function() {
                        $('#content-result').html('<div class="tbp-error">An error occurred. Please try again.</div>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function renderSettings() {
        $webhook_secret = get_option('tbp_webhook_secret', '');
        $gemini_key = get_option('tbp_gemini_key', '');
        $deepseek_key = get_option('tbp_deepseek_key', '');
        $openai_key = get_option('tbp_openai_key', '');
        $claude_key = get_option('tbp_claude_key', '');
        $openrouter_key = get_option('tbp_openrouter_key', '');
        $cohere_key = get_option('tbp_cohere_key', '');
        $groq_key = get_option('tbp_groq_key', '');
        ?>
        <div class="wrap tbp-settings-wrap">
            <h1>⚙️ Advanced Settings</h1>
            
            <form id="tbp-settings-form">
                <div class="tbp-api-section">
                    <h3>🔑 API Configuration</h3>
                    <p class="tbp-section-description">Configure your preferred AI service for content generation. You can use multiple APIs for better content quality.</p>
                    
                    <div class="tbp-api-grid">
                        <!-- Webhook Secret -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>🔐 Webhook Secret</h4>
                                <span class="tbp-required">Required</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="webhook-secret" name="webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="tbp-api-input" placeholder="Enter webhook secret" />
                                <button type="button" class="tbp-eye-btn" data-target="webhook-secret">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                            </div>
                            <p class="tbp-field-description">Secret key for webhook authentication</p>
                        </div>

                        <!-- OpenAI -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>🤖 OpenAI</h4>
                                <span class="tbp-recommended">Recommended</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="openai-key" name="openai_key" value="<?php echo esc_attr($openai_key); ?>" class="tbp-api-input" placeholder="sk-..." />
                                <button type="button" class="tbp-eye-btn" data-target="openai-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="openai">Test</button>
                            </div>
                            <p class="tbp-field-description">GPT-4, GPT-3.5, and other OpenAI models</p>
                        </div>

                        <!-- Google Gemini -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>💎 Google Gemini</h4>
                                <span class="tbp-free">Free Tier</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="gemini-key" name="gemini_key" value="<?php echo esc_attr($gemini_key); ?>" class="tbp-api-input" placeholder="AI..." />
                                <button type="button" class="tbp-eye-btn" data-target="gemini-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="gemini">Test</button>
                            </div>
                            <p class="tbp-field-description">Gemini Pro and Gemini Ultra models</p>
                        </div>

                        <!-- Anthropic Claude -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>🧠 Anthropic Claude</h4>
                                <span class="tbp-premium">Premium</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="claude-key" name="claude_key" value="<?php echo esc_attr($claude_key); ?>" class="tbp-api-input" placeholder="sk-ant-..." />
                                <button type="button" class="tbp-eye-btn" data-target="claude-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="claude">Test</button>
                            </div>
                            <p class="tbp-field-description">Claude 3.5 Sonnet, Claude 3 Opus, and Claude 3 Haiku</p>
                        </div>

                        <!-- DeepSeek -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>🔍 DeepSeek</h4>
                                <span class="tbp-affordable">Affordable</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="deepseek-key" name="deepseek_key" value="<?php echo esc_attr($deepseek_key); ?>" class="tbp-api-input" placeholder="sk-..." />
                                <button type="button" class="tbp-eye-btn" data-target="deepseek-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="deepseek">Test</button>
                            </div>
                            <p class="tbp-field-description">DeepSeek V2.5 and DeepSeek Coder models</p>
                        </div>

                        <!-- OpenRouter -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>🌐 OpenRouter</h4>
                                <span class="tbp-universal">Universal</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="openrouter-key" name="openrouter_key" value="<?php echo esc_attr($openrouter_key); ?>" class="tbp-api-input" placeholder="sk-or-..." />
                                <button type="button" class="tbp-eye-btn" data-target="openrouter-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="openrouter">Test</button>
                            </div>
                            <p class="tbp-field-description">Access to 100+ models from various providers</p>
                        </div>

                        <!-- Cohere -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>⚡ Cohere</h4>
                                <span class="tbp-enterprise">Enterprise</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="cohere-key" name="cohere_key" value="<?php echo esc_attr($cohere_key); ?>" class="tbp-api-input" placeholder="co-..." />
                                <button type="button" class="tbp-eye-btn" data-target="cohere-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="cohere">Test</button>
                            </div>
                            <p class="tbp-field-description">Command, Command Light, and Command Nightly</p>
                        </div>

                        <!-- Groq -->
                        <div class="tbp-api-card">
                            <div class="tbp-api-header">
                                <h4>⚡ Groq</h4>
                                <span class="tbp-fast">Ultra Fast</span>
                            </div>
                            <div class="tbp-input-group">
                                <input type="password" id="groq-key" name="groq_key" value="<?php echo esc_attr($groq_key); ?>" class="tbp-api-input" placeholder="gsk_..." />
                                <button type="button" class="tbp-eye-btn" data-target="groq-key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <button type="button" class="tbp-test-btn" data-api="groq">Test</button>
                            </div>
                            <p class="tbp-field-description">Llama 3.1, Mixtral, and other fast models</p>
                        </div>
                    </div>
                </div>

                <div class="tbp-content-section">
                    <h3>📝 Content Generation Settings</h3>
                    <div class="tbp-content-grid">
                        <div class="tbp-content-card">
                            <h4>🎯 Content Quality</h4>
                            <select name="content_quality" id="content-quality">
                                <option value="basic" <?php selected(get_option('tbp_content_quality', 'premium'), 'basic'); ?>>Basic (300-500 words)</option>
                                <option value="standard" <?php selected(get_option('tbp_content_quality', 'premium'), 'standard'); ?>>Standard (500-800 words)</option>
                                <option value="premium" <?php selected(get_option('tbp_content_quality', 'premium'), 'premium'); ?>>Premium (800-1200 words)</option>
                                <option value="enterprise" <?php selected(get_option('tbp_content_quality', 'premium'), 'enterprise'); ?>>Enterprise (1200+ words)</option>
                            </select>
                        </div>

                        <div class="tbp-content-card">
                            <h4>🖼️ Media Integration</h4>
                            <label class="tbp-checkbox-label">
                                <input type="checkbox" name="include_images" value="1" <?php checked(get_option('tbp_include_images', 1), 1); ?> />
                                <span class="tbp-checkbox-text">Auto-generate relevant images</span>
                            </label>
                            <label class="tbp-checkbox-label">
                                <input type="checkbox" name="include_featured_image" value="1" <?php checked(get_option('tbp_include_featured_image', 1), 1); ?> />
                                <span class="tbp-checkbox-text">Generate featured image</span>
                            </label>
                        </div>

                        <div class="tbp-content-card">
                            <h4>📊 SEO Optimization</h4>
                            <label class="tbp-checkbox-label">
                                <input type="checkbox" name="seo_optimized" value="1" <?php checked(get_option('tbp_seo_optimized', 1), 1); ?> />
                                <span class="tbp-checkbox-text">SEO-optimized content</span>
                            </label>
                            <label class="tbp-checkbox-label">
                                <input type="checkbox" name="include_meta" value="1" <?php checked(get_option('tbp_include_meta', 1), 1); ?> />
                                <span class="tbp-checkbox-text">Auto-generate meta descriptions</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="tbp-save-section">
                    <button type="submit" class="tbp-save-btn">
                        <span class="dashicons dashicons-saved"></span>
                        Save All Settings
                    </button>
                </div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Eye button functionality
            $('.tbp-eye-btn').on('click', function() {
                var target = $(this).data('target');
                var input = $('#' + target);
                var icon = $(this).find('.dashicons');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                }
            });
            
            // API testing functionality
            $('.tbp-test-btn').on('click', function() {
                var api = $(this).data('api');
                var input = $('input[name="' + api + '_key"]');
                var apiKey = input.val();
                var button = $(this);
                
                if (!apiKey) {
                    alert('Please enter an API key first');
                    return;
                }
                
                button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: tbp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tbp_test_api',
                        service: api,
                        api_key: apiKey,
                        nonce: tbp_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            button.text('✓ Working').css('background', '#40B75F');
                            setTimeout(function() {
                                button.text('Test').css('background', '#40B75F');
                            }, 2000);
                        } else {
                            button.text('✗ Error').css('background', '#ff6b6b');
                            alert('Error: ' + response.data);
                            setTimeout(function() {
                                button.text('Test').css('background', '#40B75F');
                            }, 2000);
                        }
                    },
                    error: function() {
                        button.text('✗ Error').css('background', '#ff6b6b');
                        alert('Network error occurred');
                        setTimeout(function() {
                            button.text('Test').css('background', '#40B75F');
                        }, 2000);
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });
            
            // Settings form submission
            $('#tbp-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var submitBtn = $('.tbp-save-btn');
                
                submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Saving...');
                
                $.ajax({
                    url: tbp_ajax.ajax_url,
                    type: 'POST',
                    data: formData + '&action=tbp_save_settings&nonce=' + tbp_ajax.nonce,
                    success: function(response) {
                        if (response.success) {
                            submitBtn.html('<span class="dashicons dashicons-saved"></span> Saved!');
                            setTimeout(function() {
                                submitBtn.html('<span class="dashicons dashicons-saved"></span> Save All Settings');
                            }, 2000);
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Network error occurred');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                    }
                });
            });
        });
        
        function testAPI(service) {
            var apiKey = document.querySelector('input[name="' + service + '_key"]').value;
            if (!apiKey) {
                alert('Please enter an API key first');
                return;
            }
            
            jQuery.post(tbp_ajax.ajax_url, {
                action: 'tbp_test_api',
                service: service,
                api_key: apiKey,
                nonce: tbp_ajax.nonce
            }, function(response) {
                if (response.success) {
                    alert('API key is working!');
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }
        
        jQuery(document).ready(function($) {
            $('#tbp-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: tbp_ajax.ajax_url,
                    type: 'POST',
                    data: $(this).serialize() + '&action=tbp_save_settings&nonce=' + tbp_ajax.nonce,
                    success: function(response) {
                        if (response.success) {
                            alert('Settings saved successfully!');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
            
            // Premium Testing Features
            // Test local webhook
            $('#test-local-webhook').on('click', function() {
                const $button = $(this);
                const $result = $('#local-webhook-result');
                
                $button.prop('disabled', true).text('Testing...');
                $result.hide();
                
                $.ajax({
                url: tbp_ajax.ajax_url,
                type: 'POST',
                data: {
                        action: 'tbp_test_webhook',
                        nonce: tbp_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                            $result.removeClass('error').addClass('success').html('<strong>Success!</strong> ' + response.data.message).show();
                    } else {
                            $result.removeClass('success').addClass('error').html('<strong>Error:</strong> ' + response.data).show();
                    }
                },
                error: function() {
                        $result.removeClass('success').addClass('error').html('<strong>Error:</strong> Failed to test webhook.').show();
                },
                complete: function() {
                        $button.prop('disabled', false).text('Test Local Webhook');
                }
            });
            });
        
            // Send external webhook
            $('#external-webhook-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $button = $form.find('button[type="submit"]');
                const $result = $('#external-webhook-result');
                
                $button.prop('disabled', true).text('Sending...');
                $result.hide();
                
                $.ajax({
                    url: tbp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tbp_send_test_webhook',
                        webhook_url: $form.find('#webhook-url').val(),
                        webhook_secret: $form.find('#webhook-secret').val(),
                        topic: $form.find('#test-topic').val(),
                        title: $form.find('#test-title').val(),
                        nonce: tbp_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.removeClass('error').addClass('success').html('<strong>Success!</strong> ' + response.data.message).show();
                        } else {
                            $result.removeClass('success').addClass('error').html('<strong>Error:</strong> ' + response.data).show();
                        }
                    },
                    error: function() {
                        $result.removeClass('success').addClass('error').html('<strong>Error:</strong> Failed to send webhook.').show();
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Send Test Webhook');
                    }
                });
            });
            
            // Copy webhook URL
            $('#copy-webhook-url').on('click', function() {
                const webhookUrl = '<?php echo get_rest_url() . 'telegram-blog-publisher/v1/webhook'; ?>';
                navigator.clipboard.writeText(webhookUrl).then(function() {
                    $(this).text('Copied!');
                    setTimeout(() => {
                        $(this).html('<span class="dashicons dashicons-clipboard"></span> Copy');
                    }, 2000);
                }.bind(this));
            });
            
            // Refresh status
            $('#refresh-status').on('click', function() {
                location.reload();
            });
            
            // Test webhook from status page
            $('#test-webhook').on('click', function() {
                const $button = $(this);
                $button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: tbp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tbp_test_webhook',
                        nonce: tbp_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Webhook test successful!');
                        } else {
                            alert('Webhook test failed: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Webhook test failed: Network error');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Test Webhook');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function renderLogs() {
        $logs = get_option('tbp_logs', []);
        $recent_logs = array_reverse($logs);
        ?>
        <div class="wrap">
            <h1>Activity Logs</h1>
            <div class="tbp-logs">
                <?php if (!empty($recent_logs)): ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <div class="tbp-log-entry">
                            <div class="tbp-log-time"><?php echo esc_html($log['timestamp']); ?></div>
                            <div class="tbp-log-action"><?php echo esc_html($log['action']); ?></div>
                            <?php if (!empty($log['data'])): ?>
                                <div class="tbp-log-data"><?php echo esc_html(json_encode($log['data'], JSON_PRETTY_PRINT)); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No logs available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .tbp-logs {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .tbp-log-entry {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .tbp-log-time {
            font-weight: bold;
            color: #666;
        }
        
        .tbp-log-action {
            margin: 5px 0;
            color: #333;
        }
        
        .tbp-log-data {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
        </style>
        <?php
    }
    
    public function renderHosting() {
        ?>
        <div class="wrap tbp-hosting-page">
            <!-- Hero Section -->
            <div class="tbp-hosting-hero">
                <div class="tbp-hero-content">
                    <div class="tbp-kloudbean-logo">
                        <img src="https://www.kloudbean.com/wp-content/uploads/2024/08/logo.svg" alt="KloudBean Logo" width="80" height="80">
                    </div>
                    <h1>☁️ KloudBean Hosting</h1>
                    <p class="tbp-hero-subtitle">The ONLY hosting platform that supports WordPress + n8n + Lovable + Cursor development with Git-based CI/CD</p>
                    <div class="tbp-hero-cta">
                        <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary tbp-btn-large">
                            <span class="dashicons dashicons-cloud"></span>
                            View Pricing Plans
                        </a>
                        <a href="https://kloudbean.com/n8n-self-hosted" target="_blank" class="tbp-btn tbp-btn-secondary tbp-btn-large">
                            <span class="dashicons dashicons-admin-tools"></span>
                            Learn About n8n
                        </a>
                    </div>
                </div>
            </div>

            <!-- Why KloudBean is Essential -->
            <div class="tbp-essential-section">
                <h2>Why KloudBean is ESSENTIAL for This Plugin</h2>
                <div class="tbp-essential-content">
                    <div class="tbp-essential-text">
                        <p><strong>This Telegram Blog Publisher plugin requires n8n for webhook processing.</strong> KloudBean is the ONLY hosting provider that offers complete integration of WordPress, n8n, Lovable, and Cursor development in one unified platform.</p>
                        
                        <div class="tbp-integration-highlights">
                            <div class="tbp-integration-item">
                                <span class="tbp-integration-icon">🔗</span>
                                <div class="tbp-integration-text">
                                    <h4>WordPress + n8n Integration</h4>
                                    <p>Seamless webhook processing and workflow automation</p>
                                </div>
                            </div>
                            <div class="tbp-integration-item">
                                <span class="tbp-integration-icon">🚀</span>
                                <div class="tbp-integration-text">
                                    <h4>Git-based CI/CD</h4>
                                    <p>Develop WordPress with Cursor and deploy automatically via Git</p>
                                </div>
                            </div>
                            <div class="tbp-integration-item">
                                <span class="tbp-integration-icon">💎</span>
                                <div class="tbp-integration-text">
                                    <h4>Lovable Apps</h4>
                                    <p>Deploy and manage Lovable applications alongside WordPress</p>
                                </div>
                            </div>
                            <div class="tbp-integration-item">
                                <span class="tbp-integration-icon">🔧</span>
                                <div class="tbp-integration-text">
                                    <h4>Cursor Development</h4>
                                    <p>AI-powered development environment with cloud integration</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Before vs After Comparison -->
            <div class="tbp-comparison-section">
                <h2>Before vs After KloudBean</h2>
                <div class="tbp-comparison-container">
                    <div class="tbp-comparison-box tbp-before-box">
                        <h3>❌ Shared Hosting Limitations</h3>
                        <div class="tbp-problem-list">
                            <div class="tbp-problem-item">
                                <span class="tbp-problem-icon">❌</span>
                                <span>No n8n support - Plugin won't work</span>
                            </div>
                            <div class="tbp-problem-item">
                                <span class="tbp-problem-icon">❌</span>
                                <span>No Git CI/CD - Can't develop with Cursor</span>
                            </div>
                            <div class="tbp-problem-item">
                                <span class="tbp-problem-icon">❌</span>
                                <span>No Lovable integration</span>
                            </div>
                            <div class="tbp-problem-item">
                                <span class="tbp-problem-icon">❌</span>
                                <span>Limited resources for automation</span>
                            </div>
                            <div class="tbp-problem-item">
                                <span class="tbp-problem-icon">❌</span>
                                <span>Multiple vendors and billing</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tbp-arrow-container">
                        <div class="tbp-arrow">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </div>
                    </div>
                    
                    <div class="tbp-comparison-box tbp-after-box">
                        <h3>✅ KloudBean Solution</h3>
                        <div class="tbp-solution-center">
                            <div class="tbp-kloudbean-logo-small">
                                <img src="https://www.kloudbean.com/wp-content/uploads/2024/08/logo.svg" alt="KloudBean" width="50" height="50">
                            </div>
                            <h4>One Platform</h4>
                            <div class="tbp-solution-list">
                                <div class="tbp-solution-item">
                                    <span class="tbp-solution-icon">✅</span>
                                    <span>Complete n8n integration</span>
                                </div>
                                <div class="tbp-solution-item">
                                    <span class="tbp-solution-icon">✅</span>
                                    <span>Git-based CI/CD for Cursor</span>
                                </div>
                                <div class="tbp-solution-item">
                                    <span class="tbp-solution-icon">✅</span>
                                    <span>Lovable app deployment</span>
                                </div>
                                <div class="tbp-solution-item">
                                    <span class="tbp-solution-icon">✅</span>
                                    <span>Unified dashboard & billing</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KloudBean Magic Steps -->
            <div class="tbp-magic-section">
                <h2>🚀 Deploy in Seconds, Not Days</h2>
                <div class="tbp-steps">
                    <div class="tbp-step">
                        <div class="tbp-step-content">
                            <i class="fas fa-cubes"></i>
                            <h3>1. Select Application</h3>
                            <p>Choose from WordPress, n8n, Lovable, or Cursor projects</p>
                        </div>
                        <div class="tbp-step-line"></div>
                    </div>
                    <div class="tbp-step">
                        <div class="tbp-step-content">
                            <i class="fas fa-server"></i>
                            <h3>2. Select Your Server Size</h3>
                            <p>Pick the right resources for your needs (easily scalable anytime)</p>
                        </div>
                        <div class="tbp-step-line"></div>
                    </div>
                    <div class="tbp-step">
                        <div class="tbp-step-content">
                            <i class="fas fa-rocket"></i>
                            <h3>3. One-Click Deploy</h3>
                            <p>Watch as your entire digital workspace comes to life in minutes</p>
                        </div>
                        <div class="tbp-step-line"></div>
                    </div>
                    <div class="tbp-step">
                        <div class="tbp-step-content">
                            <i class="fas fa-lock"></i>
                            <h3>4. Focus on Your Work</h3>
                            <p>While we handle maintenance, backups, updates, and security</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="tbp-features-grid">
                <div class="tbp-feature-card">
                    <h5>🚀 Deploy in Seconds, Not Days</h5>
                    <p>Turn multi-hour setups into single-click deployments. Launch N8N workflows, GitLab repositories, Penpot designs, or private AI models in less time than it takes to make coffee.</p>
                </div>
                <div class="tbp-feature-card">
                    <h5>💰 Save Up to 90% on Software Costs</h5>
                    <p>Why pay for separate SaaS subscriptions when you can run everything on one affordable server? A typical business saves $2,400+ annually by consolidating tools on Kloudbean.</p>
                </div>
                <div class="tbp-feature-card">
                    <h5>🔒 Your Data, Your Control</h5>
                    <p>Deploy client projects seamlessly with ready-to-deploy environments for Node.js, React, Laravel, Python, WordPress, and more—eliminating setup hassles.</p>
                </div>
                <div class="tbp-feature-card">
                    <h5>🔒 Enterprise-Grade Security</h5>
                    <p>Deliver peace of mind to your clients with 24/7 advanced security—DDoS prevention, IP whitelisting, bot protection, and BitNinja shielding to protect your applications and client data effortlessly.</p>
                </div>
                <div class="tbp-feature-card">
                    <h5>⚡ Ultra-Fast Performance</h5>
                    <p>Enjoy optimized servers with cutting-edge caching, PHP-FPM, and HTTP/2 for ultra-fast load times and low latency—no delays, no complaints.</p>
                </div>
                <div class="tbp-feature-card">
                    <h5>🛠️ Pre-Built Environments</h5>
                    <p>Deploy client projects seamlessly with ready-to-deploy environments for Node.js, React, Laravel, Python, WordPress, and more—eliminating setup hassles.</p>
                </div>
            </div>

            <!-- Pricing Plans -->
            <div class="tbp-pricing-section">
                <h2>Pricing Plans</h2>
                <div class="tbp-pricing-grid">
                    <div class="tbp-pricing-card">
                        <h3>g6-nanode-1</h3>
                        <div class="tbp-price">$8<span>/month</span></div>
                        <ul>
                            <li>1 GB RAM</li>
                            <li>1 Core Processor</li>
                            <li>25 GB SSD Storage</li>
                            <li>1000 GB Bandwidth</li>
                            <li>WordPress + n8n Ready</li>
                            <li>Basic Support</li>
                        </ul>
                        <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary">Get Started →</a>
                    </div>
                    
                    <div class="tbp-pricing-card tbp-featured">
                        <div class="tbp-featured-badge">Most Popular</div>
                        <h3>g6-standard-1</h3>
                        <div class="tbp-price">$24<span>/month</span></div>
                        <ul>
                            <li>2 GB RAM</li>
                            <li>1 Core Processor</li>
                            <li>50 GB SSD Storage</li>
                            <li>2000 GB Bandwidth</li>
                            <li>WordPress + n8n + Cursor</li>
                            <li>Priority Support</li>
                        </ul>
                        <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary">Get Started →</a>
                    </div>
                    
                    <div class="tbp-pricing-card">
                        <h3>g6-standard-2</h3>
                        <div class="tbp-price">$48<span>/month</span></div>
                        <ul>
                            <li>4 GB RAM</li>
                            <li>2 Core Processor</li>
                            <li>80 GB SSD Storage</li>
                            <li>4000 GB Bandwidth</li>
                            <li>WordPress + n8n + Lovable</li>
                            <li>Advanced Features</li>
                        </ul>
                        <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary">Get Started →</a>
                    </div>
                    
                    <div class="tbp-pricing-card">
                        <h3>g6-standard-4</h3>
                        <div class="tbp-price">$96<span>/month</span></div>
                        <ul>
                            <li>8 GB RAM</li>
                            <li>4 Core Processor</li>
                            <li>160 GB SSD Storage</li>
                            <li>5000 GB Bandwidth</li>
                            <li>Full Stack Integration</li>
                            <li>24/7 Priority Support</li>
                        </ul>
                        <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary">Get Started →</a>
                    </div>
                </div>
            </div>

            <!-- Final CTA -->
            <div class="tbp-hosting-cta">
                <h2>Ready to Get Started?</h2>
                <p>Join thousands of developers who trust KloudBean for their WordPress + n8n + automation needs.</p>
                <div class="tbp-cta-actions">
                    <a href="https://kloudbean.com/pricing" target="_blank" class="tbp-btn tbp-btn-primary tbp-btn-large">
                        <span class="dashicons dashicons-cloud"></span>
                        View All Plans
                    </a>
                    <a href="https://kloudbean.com/n8n-self-hosted" target="_blank" class="tbp-btn tbp-btn-secondary tbp-btn-large">
                        <span class="dashicons dashicons-admin-tools"></span>
                        Learn About n8n
                    </a>
                </div>
            </div>
        </div>
        
        <style>
        .tbp-hosting-page {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Hero Section */
        .tbp-hosting-hero {
            background: linear-gradient(135deg, #000F27 0%, #04142E 50%, #4F1AF3 100%);
            color: white;
            padding: 80px 40px;
            border-radius: 16px;
            margin-bottom: 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .tbp-hosting-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: tbp-float 6s ease-in-out infinite;
        }
        
        .tbp-hero-content {
            position: relative;
            z-index: 1;
        }
        
        .tbp-kloudbean-logo {
            margin-bottom: 30px;
        }
        
        .tbp-kloudbean-logo img {
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0, 15, 39, 0.3);
            border: 3px solid rgba(64, 183, 95, 0.6);
        }
        
        .tbp-hosting-hero h1 {
            font-size: 3.5rem;
            margin: 0 0 20px 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .tbp-hero-subtitle {
            font-size: 1.4rem;
            margin: 0 0 40px 0;
            opacity: 0.9;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .tbp-hero-cta {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Essential Section */
        .tbp-essential-section {
            background: #f8f9fa;
            padding: 60px 40px;
            border-radius: 16px;
            margin-bottom: 60px;
        }
        
        .tbp-essential-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #333;
        }
        
        .tbp-integration-highlights {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .tbp-integration-item {
            display: flex;
            align-items: flex-start;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .tbp-integration-item:hover {
            transform: translateY(-5px);
        }
        
        .tbp-integration-icon {
            font-size: 2rem;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .tbp-integration-text h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.3rem;
        }
        
        .tbp-integration-text p {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }
        
        /* Comparison Section */
        .tbp-comparison-section {
            margin-bottom: 60px;
        }
        
        .tbp-comparison-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #333;
        }
        
        .tbp-comparison-container {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-top: 40px;
        }
        
        .tbp-comparison-box {
            flex: 1;
            padding: 40px;
            border-radius: 16px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        .tbp-before-box {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }
        
        .tbp-after-box {
            background: linear-gradient(135deg, #40B75F, #28a745);
            color: white;
        }
        
        .tbp-comparison-box h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .tbp-problem-list, .tbp-solution-list {
            flex: 1;
        }
        
        .tbp-problem-item, .tbp-solution-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .tbp-problem-icon, .tbp-solution-icon {
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .tbp-solution-center {
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .tbp-kloudbean-logo-small {
            margin: 0 auto 20px;
        }
        
        .tbp-kloudbean-logo-small img {
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .tbp-solution-center h4 {
            font-size: 2rem;
            margin-bottom: 30px;
        }
        
        .tbp-arrow-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .tbp-arrow {
            width: 60px;
            height: 60px;
            background: #4F1AF3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(79, 26, 243, 0.3);
            animation: tbp-pulse 2s infinite;
        }
        
        /* Magic Steps Section */
        .tbp-magic-section {
            background: linear-gradient(135deg, #000F27, #04142E);
            color: white;
            padding: 60px 40px;
            border-radius: 16px;
            margin-bottom: 60px;
        }
        
        .tbp-magic-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 50px;
        }
        
        .tbp-steps {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .tbp-step {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .tbp-step:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .tbp-step i {
            font-size: 3rem;
            color: #40B75F;
            margin-bottom: 20px;
        }
        
        .tbp-step h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: white;
        }
        
        .tbp-step p {
            color: #ccc;
            line-height: 1.6;
        }
        
        .tbp-step-line {
            position: absolute;
            top: 50%;
            right: -20px;
            width: 40px;
            height: 2px;
            background: white;
            transform: translateY(-50%);
        }
        
        .tbp-step-line::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 0;
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            transform: translateY(-50%);
        }
        
        .tbp-step:last-child .tbp-step-line {
            display: none;
        }
        
        /* Features Grid */
        .tbp-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .tbp-feature-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #40B75F;
        }
        
        .tbp-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .tbp-feature-card h5 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .tbp-feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* Pricing Section */
        .tbp-pricing-section {
            margin-bottom: 60px;
        }
        
        .tbp-pricing-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: #333;
        }
        
        .tbp-pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .tbp-pricing-card {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .tbp-pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .tbp-pricing-card.tbp-featured {
            border: 3px solid #40B75F;
            transform: scale(1.05);
        }
        
        .tbp-featured-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #40B75F;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .tbp-pricing-card h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        .tbp-price {
            font-size: 3rem;
            font-weight: 700;
            color: #40B75F;
            margin-bottom: 30px;
        }
        
        .tbp-price span {
            font-size: 1rem;
            color: #666;
        }
        
        .tbp-pricing-card ul {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }
        
        .tbp-pricing-card li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }
        
        .tbp-pricing-card li:last-child {
            border-bottom: none;
        }
        
        /* CTA Section */
        .tbp-hosting-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            border-radius: 16px;
            text-align: center;
        }
        
        .tbp-hosting-cta h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .tbp-hosting-cta p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .tbp-cta-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Animations */
        @keyframes tbp-float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, -20px) rotate(180deg); }
        }
        
        @keyframes tbp-pulse {
            0% { transform: scale(1); box-shadow: 0 5px 15px rgba(79, 26, 243, 0.3); }
            50% { transform: scale(1.1); box-shadow: 0 5px 25px rgba(79, 26, 243, 0.5); }
            100% { transform: scale(1); box-shadow: 0 5px 15px rgba(79, 26, 243, 0.3); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .tbp-hosting-hero {
                padding: 40px 20px;
            }
            
            .tbp-hosting-hero h1 {
                font-size: 2.5rem;
            }
            
            .tbp-hero-subtitle {
                font-size: 1.1rem;
            }
            
            .tbp-hero-cta {
                flex-direction: column;
                align-items: center;
            }
            
            .tbp-comparison-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .tbp-arrow-container {
                transform: rotate(90deg);
            }
            
            .tbp-steps {
                flex-direction: column;
                align-items: center;
            }
            
            .tbp-step {
                max-width: 100%;
            }
            
            .tbp-step-line {
                display: none;
            }
            
            .tbp-features-grid {
                grid-template-columns: 1fr;
            }
            
            .tbp-pricing-grid {
                grid-template-columns: 1fr;
            }
            
            .tbp-pricing-card.tbp-featured {
                transform: none;
            }
        }
        
        /* Premium System Status Styles */
        .tbp-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .tbp-status-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .tbp-status-card h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.3rem;
        }
        
        .tbp-status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .tbp-status-item:last-child {
            border-bottom: none;
        }
        
        .tbp-status-label {
            font-weight: 600;
            color: #555;
        }
        
        .tbp-status-value {
            font-weight: 500;
        }
        
        .tbp-status-ok {
            color: #28a745;
        }
        
        .tbp-status-warning {
            color: #ffc107;
        }
        
        .tbp-status-error {
            color: #dc3545;
        }
        
        .tbp-status-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        /* Premium Testing Styles */
        .tbp-testing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .tbp-testing-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .tbp-testing-card h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.4rem;
        }
        
        .tbp-testing-card p {
            margin: 0 0 20px 0;
            color: #666;
        }
        
        .tbp-form-group {
            margin-bottom: 20px;
        }
        
        .tbp-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .tbp-form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .tbp-form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .tbp-test-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .tbp-test-result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .tbp-test-result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .tbp-testing-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin-top: 30px;
        }
        
        .tbp-testing-info h3 {
            margin: 0 0 20px 0;
            color: #333;
        }
        
        .tbp-info-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .tbp-info-card h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .tbp-info-card code {
            background: #f1f3f4;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            display: block;
            margin: 10px 0;
            word-break: break-all;
        }
        
        .tbp-info-card pre {
            background: #f1f3f4;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
        }
        
        .tbp-btn-small {
            padding: 8px 16px;
            font-size: 12px;
        }
        
        /* Enhanced KloudBean Hosting Styles */
        .tbp-why-kloudbean {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .tbp-why-kloudbean h3 {
            margin: 0 0 15px 0;
            color: #856404;
        }
        
        .tbp-why-kloudbean ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .tbp-why-kloudbean li {
            margin-bottom: 8px;
            color: #856404;
        }
        
        .tbp-btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .tbp-btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        /* Enhanced Settings Page Styles */
        .tbp-settings-wrap {
            background: linear-gradient(135deg, #000F27 0%, #04142E 100%);
            color: white;
            min-height: 100vh;
            padding: 40px;
            border-radius: 16px;
        }
        
        .tbp-settings-wrap h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .tbp-api-section, .tbp-content-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .tbp-api-section h3, .tbp-content-section h3 {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tbp-section-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .tbp-api-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .tbp-api-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .tbp-api-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .tbp-api-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .tbp-api-header h4 {
            color: white;
            font-size: 1.3rem;
            margin: 0;
        }
        
        .tbp-required, .tbp-recommended, .tbp-free, .tbp-premium, .tbp-affordable, .tbp-universal, .tbp-enterprise, .tbp-fast {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .tbp-required { background: #ff6b6b; color: white; }
        .tbp-recommended { background: #40B75F; color: white; }
        .tbp-free { background: #4F1AF3; color: white; }
        .tbp-premium { background: #ffc107; color: #000; }
        .tbp-affordable { background: #17a2b8; color: white; }
        .tbp-universal { background: #6f42c1; color: white; }
        .tbp-enterprise { background: #fd7e14; color: white; }
        .tbp-fast { background: #e83e8c; color: white; }
        
        .tbp-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .tbp-api-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .tbp-api-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .tbp-api-input:focus {
            outline: none;
            border-color: #40B75F;
            background: rgba(255, 255, 255, 0.15);
        }
        
        .tbp-eye-btn, .tbp-test-btn {
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .tbp-eye-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            min-width: 45px;
        }
        
        .tbp-eye-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .tbp-test-btn {
            background: #40B75F;
            color: white;
            font-weight: 600;
            min-width: 60px;
        }
        
        .tbp-test-btn:hover {
            background: #28a745;
            transform: translateY(-2px);
        }
        
        .tbp-field-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin: 0;
        }
        
        .tbp-content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .tbp-content-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .tbp-content-card h4 {
            color: white;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .tbp-content-card select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
        }
        
        .tbp-content-card select option {
            background: #000F27;
            color: white;
        }
        
        .tbp-checkbox-label {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            cursor: pointer;
        }
        
        .tbp-checkbox-label input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }
        
        .tbp-checkbox-text {
            color: white;
            font-size: 14px;
        }
        
        .tbp-save-section {
            text-align: center;
            margin-top: 40px;
        }
        
        .tbp-save-btn {
            background: linear-gradient(135deg, #40B75F, #28a745);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .tbp-save-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(64, 183, 95, 0.3);
        }
        
        /* Fixed Pricing Styles - Better Contrast */
        .tbp-pricing-card h3 {
            color: #333 !important;
            font-weight: 700;
        }
        
        .tbp-pricing-card p, .tbp-pricing-card li {
            color: #666 !important;
        }
        
        .tbp-pricing-card {
            background: white !important;
            border: 2px solid #e0e0e0 !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
        }
        
        .tbp-pricing-card.tbp-featured {
            border: 3px solid #40B75F !important;
            background: linear-gradient(135deg, #f8fff9, #ffffff) !important;
            transform: scale(1.05);
        }
        
        .tbp-price {
            color: #40B75F !important;
            font-weight: 700;
        }
        
        .tbp-hosting-cta h2, .tbp-hosting-cta p {
            color: white !important;
        }
        
        .tbp-hosting-hero h1, .tbp-hosting-hero p {
            color: white !important;
        }
        
        /* Fixed Essential Section - Better Contrast */
        .tbp-essential-section {
            background: linear-gradient(135deg, #f8f9fa, #ffffff) !important;
            border: 1px solid #e0e0e0 !important;
        }
        
        .tbp-essential-section h2 {
            color: #333 !important;
            font-weight: 700;
        }
        
        .tbp-integration-text h4 {
            color: #333 !important;
            font-weight: 600;
        }
        
        .tbp-integration-text p {
            color: #666 !important;
        }
        
        .tbp-integration-item {
            background: white !important;
            border: 1px solid #e0e0e0 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
        }
        
        /* Fixed Comparison Section */
        .tbp-comparison-section h2 {
            color: #333 !important;
            font-weight: 700;
        }
        
        .tbp-before-box h3, .tbp-after-box h3 {
            color: white !important;
        }
        
        .tbp-before-box .tbp-problem-text, .tbp-after-box .tbp-solution-text {
            color: white !important;
        }
        
        /* Fixed Magic Section */
        .tbp-magic-section h2 {
            color: white !important;
        }
        
        .tbp-step h3, .tbp-step p {
            color: white !important;
        }
        
        /* Fixed Feature Cards */
        .tbp-feature-card h5 {
            color: #333 !important;
            font-weight: 600;
        }
        
        .tbp-feature-card p {
            color: #666 !important;
        }
        
        .tbp-feature-card {
            background: white !important;
            border: 1px solid #e0e0e0 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
        }
        
        /* Enhanced Testing Page Styles */
        .tbp-testing-wrap {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 40px;
        }
        
        .tbp-testing-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .tbp-testing-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .tbp-testing-subtitle {
            color: #666;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .tbp-testing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .tbp-test-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .tbp-test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .tbp-primary-card {
            border-left: 4px solid #40B75F;
        }
        
        .tbp-secondary-card {
            border-left: 4px solid #4F1AF3;
        }
        
        .tbp-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .tbp-card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #40B75F, #28a745);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .tbp-secondary-card .tbp-card-icon {
            background: linear-gradient(135deg, #4F1AF3, #6f42c1);
        }
        
        .tbp-card-icon .dashicons {
            color: white;
            font-size: 24px;
        }
        
        .tbp-test-card h3 {
            color: #333;
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
        }
        
        .tbp-test-card p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .tbp-test-form {
            margin-bottom: 20px;
        }
        
        .tbp-form-row {
            margin-bottom: 20px;
        }
        
        .tbp-form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .tbp-form-group {
            margin-bottom: 15px;
        }
        
        .tbp-form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .tbp-form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .tbp-form-group input:focus {
            outline: none;
            border-color: #40B75F;
            box-shadow: 0 0 0 3px rgba(64, 183, 95, 0.1);
        }
        
        .tbp-form-group small {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            line-height: 1.4;
        }
        
        /* Fix form layout issues */
        .tbp-form-group {
            margin-bottom: 20px;
        }
        
        .tbp-form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .tbp-form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .tbp-form-group input:focus {
            outline: none;
            border-color: #40B75F;
            box-shadow: 0 0 0 3px rgba(64, 183, 95, 0.1);
        }
        
        /* Fix button styling */
        .tbp-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .tbp-btn-primary {
            background: linear-gradient(135deg, #40B75F, #28a745);
            color: white;
        }
        
        .tbp-btn-primary:hover {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(64, 183, 95, 0.3);
        }
        
        .tbp-btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #e0e0e0;
        }
        
        .tbp-btn-secondary:hover {
            background: #40B75F;
            color: white;
            border-color: #40B75F;
        }
        
        /* Fix icon alignment */
        .tbp-btn .dashicons {
            font-size: 16px;
            line-height: 1;
        }
        
        /* Fix copy button styling */
        .tbp-copy-btn {
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
        }
        
        .tbp-copy-btn:hover {
            background: #40B75F;
            color: white;
            border-color: #40B75F;
        }
        
        .tbp-copy-btn .dashicons {
            font-size: 14px;
        }
        
        .tbp-card-actions {
            margin-top: 25px;
        }
        
        .tbp-btn-large {
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .tbp-test-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .tbp-test-result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .tbp-test-result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        /* Webhook Configuration Styles */
        .tbp-webhook-config {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        
        .tbp-config-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .tbp-config-header h3 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .tbp-config-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .tbp-config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .tbp-config-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .tbp-config-card:hover {
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .tbp-config-card-wide {
            grid-column: 1 / -1;
        }
        
        .tbp-config-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #40B75F, #28a745);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .tbp-config-icon .dashicons {
            color: white;
            font-size: 20px;
        }
        
        .tbp-config-card h4 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .tbp-url-display {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .tbp-url-display input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            font-family: monospace;
            font-size: 14px;
            color: #333;
        }
        
        .tbp-url-display input:focus {
            outline: none;
            border-color: #40B75F;
            box-shadow: 0 0 0 3px rgba(64, 183, 95, 0.1);
        }
        
        .tbp-headers {
            margin-bottom: 10px;
        }
        
        .tbp-header-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .tbp-header-item:hover {
            border-color: #40B75F;
            box-shadow: 0 2px 8px rgba(64, 183, 95, 0.1);
        }
        
        .tbp-header-item code {
            color: #333;
            font-family: monospace;
            font-size: 13px;
            font-weight: 500;
        }
        
        .tbp-copy-btn {
            padding: 5px 10px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tbp-copy-btn:hover {
            background: #40B75F;
            color: white;
        }
        
        .tbp-payload-container {
            position: relative;
        }
        
        .tbp-payload {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
            overflow-x: auto;
        }
        
        .tbp-config-card small {
            color: #666;
            font-size: 12px;
        }
        
        /* Enhanced Responsive Design */
        @media (max-width: 768px) {
            .tbp-settings-wrap, .tbp-testing-wrap {
                padding: 20px;
            }
            
            .tbp-api-grid, .tbp-content-grid, .tbp-testing-grid {
                grid-template-columns: 1fr;
            }
            
            .tbp-input-group {
                flex-direction: column;
            }
            
            .tbp-eye-btn, .tbp-test-btn {
                width: 100%;
            }
            
            .tbp-form-row-2 {
                grid-template-columns: 1fr;
            }
            
            .tbp-config-grid {
                grid-template-columns: 1fr;
            }
            
            .tbp-url-display {
                flex-direction: column;
                gap: 10px;
            }
            
            .tbp-header-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .tbp-header-item code {
                word-break: break-all;
            }
            
            .tbp-testing-header h1 {
                font-size: 2rem;
            }
            
            .tbp-testing-subtitle {
                font-size: 1rem;
            }
        }
        
        /* Fix for very small screens */
        @media (max-width: 480px) {
            .tbp-testing-wrap {
                padding: 15px;
            }
            
            .tbp-test-card {
                padding: 20px;
            }
            
            .tbp-webhook-config {
                padding: 25px;
            }
            
            .tbp-form-group input {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .tbp-btn {
                padding: 10px 20px;
                font-size: 13px;
            }
        }
        </style>
        <?php
    }
    
    public function renderSystemStatus() {
        ?>
        <div class="wrap tbp-admin-wrap">
            <h1>📊 System Status</h1>
            
            <div class="tbp-status-grid">
                <div class="tbp-status-card">
                    <h3>Plugin Information</h3>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">Version:</span>
                        <span class="tbp-status-value"><?php echo TBP_VERSION; ?></span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">WordPress:</span>
                        <span class="tbp-status-value"><?php echo get_bloginfo('version'); ?></span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">PHP:</span>
                        <span class="tbp-status-value"><?php echo PHP_VERSION; ?></span>
                    </div>
                </div>
                
                <div class="tbp-status-card">
                    <h3>Configuration Status</h3>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">Webhook Secret:</span>
                        <span class="tbp-status-value <?php echo !empty(get_option('tbp_webhook_secret', '')) ? 'tbp-status-ok' : 'tbp-status-error'; ?>">
                            <?php echo !empty(get_option('tbp_webhook_secret', '')) ? '✓ Configured' : '✗ Not Set'; ?>
                        </span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">Gemini API:</span>
                        <span class="tbp-status-value <?php echo !empty(get_option('tbp_gemini_key', '')) ? 'tbp-status-ok' : 'tbp-status-warning'; ?>">
                            <?php echo !empty(get_option('tbp_gemini_key', '')) ? '✓ Configured' : '⚠ Optional'; ?>
                        </span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">DeepSeek API:</span>
                        <span class="tbp-status-value <?php echo !empty(get_option('tbp_deepseek_key', '')) ? 'tbp-status-ok' : 'tbp-status-warning'; ?>">
                            <?php echo !empty(get_option('tbp_deepseek_key', '')) ? '✓ Configured' : '⚠ Optional'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="tbp-status-card">
                    <h3>System Requirements</h3>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">REST API:</span>
                        <span class="tbp-status-value tbp-status-ok">✓ Enabled</span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">cURL:</span>
                        <span class="tbp-status-value <?php echo function_exists('curl_init') ? 'tbp-status-ok' : 'tbp-status-error'; ?>">
                            <?php echo function_exists('curl_init') ? '✓ Available' : '✗ Missing'; ?>
                        </span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">JSON:</span>
                        <span class="tbp-status-value <?php echo function_exists('json_encode') ? 'tbp-status-ok' : 'tbp-status-error'; ?>">
                            <?php echo function_exists('json_encode') ? '✓ Available' : '✗ Missing'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="tbp-status-card">
                    <h3>Statistics</h3>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">Total Posts:</span>
                        <span class="tbp-status-value"><?php echo wp_count_posts('post')->publish; ?></span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">Generated by Plugin:</span>
                        <span class="tbp-status-value"><?php echo $this->getTotalGeneratedPosts(); ?></span>
                    </div>
                    <div class="tbp-status-item">
                        <span class="tbp-status-label">Memory Limit:</span>
                        <span class="tbp-status-value"><?php echo ini_get('memory_limit'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="tbp-status-actions">
                <button id="refresh-status" class="tbp-btn tbp-btn-primary">
                    <span class="dashicons dashicons-update"></span>
                    Refresh Status
                </button>
                <button id="test-webhook" class="tbp-btn tbp-btn-secondary">
                    <span class="dashicons dashicons-admin-tools"></span>
                    Test Webhook
                </button>
            </div>
        </div>
        <?php
    }
    
    public function renderTesting() {
        // Generate dynamic webhook URL for current website
        $webhook_url = get_rest_url() . 'telegram-blog-publisher/v1/webhook';
        $webhook_secret = get_option('tbp_webhook_secret', '');
        ?>
        <div class="wrap tbp-testing-wrap">
            <div class="tbp-testing-header">
                <h1>🔧 Webhook Testing & Configuration</h1>
                <p class="tbp-testing-subtitle">Test your webhook endpoints and configure integration with n8n or other automation tools</p>
            </div>
            
            <div class="tbp-testing-grid">
                <div class="tbp-test-card tbp-primary-card">
                    <div class="tbp-card-header">
                        <div class="tbp-card-icon">
                            <span class="dashicons dashicons-admin-tools"></span>
                        </div>
                        <h3>Test Local Webhook</h3>
                    </div>
                    <p>Test the webhook endpoint on this WordPress site to ensure it's working correctly.</p>
                    <div class="tbp-card-actions">
                        <button id="test-local-webhook" class="tbp-btn tbp-btn-primary tbp-btn-large">
                            <span class="dashicons dashicons-admin-tools"></span>
                            Test Local Webhook
                        </button>
                    </div>
                    <div id="local-webhook-result" class="tbp-test-result"></div>
                </div>
                
                <div class="tbp-test-card tbp-secondary-card">
                    <div class="tbp-card-header">
                        <div class="tbp-card-icon">
                            <span class="dashicons dashicons-external"></span>
                        </div>
                        <h3>Send Test Webhook</h3>
                    </div>
                    <p>Send a test webhook to an external URL (like n8n) to verify integration.</p>
                    <form id="external-webhook-form" class="tbp-test-form">
                        <div class="tbp-form-row">
                            <div class="tbp-form-group">
                                <label for="webhook-url">Webhook URL</label>
                                <input type="url" id="webhook-url" name="webhook_url" placeholder="https://your-n8n-instance.com/webhook/telegram" required>
                                <small>Enter your n8n or external webhook URL</small>
                            </div>
                        </div>
                        <div class="tbp-form-row">
                            <div class="tbp-form-group">
                                <label for="webhook-secret">Webhook Secret</label>
                                <input type="text" id="webhook-secret" name="webhook_secret" placeholder="Your webhook secret" required>
                                <small>Enter the secret key for authentication</small>
                            </div>
                        </div>
                        <div class="tbp-form-row tbp-form-row-2">
                            <div class="tbp-form-group">
                                <label for="test-topic">Test Topic</label>
                                <input type="text" id="test-topic" name="topic" placeholder="Test Blog Post" value="Test Webhook" required>
                            </div>
                            <div class="tbp-form-group">
                                <label for="test-title">Test Title</label>
                                <input type="text" id="test-title" name="title" placeholder="Test Title" value="Webhook Test Post" required>
                            </div>
                        </div>
                        <div class="tbp-card-actions">
                            <button type="submit" class="tbp-btn tbp-btn-primary tbp-btn-large">
                                <span class="dashicons dashicons-send"></span>
                                Send Test Webhook
                            </button>
                        </div>
                    </form>
                    <div id="external-webhook-result" class="tbp-test-result"></div>
                </div>
            </div>
            
            <div class="tbp-webhook-config">
                <div class="tbp-config-header">
                    <h3>🔗 Webhook Configuration</h3>
                    <p>Use these details to configure your n8n workflows or other automation tools</p>
                </div>
                
                <div class="tbp-config-grid">
                    <div class="tbp-config-card">
                        <div class="tbp-config-icon">
                            <span class="dashicons dashicons-admin-links"></span>
                        </div>
                        <h4>Webhook URL</h4>
                        <div class="tbp-url-display">
                            <input type="text" value="<?php echo esc_attr($webhook_url); ?>" readonly />
                            <button id="copy-webhook-url" class="tbp-btn tbp-btn-secondary">
                                <span class="dashicons dashicons-admin-page"></span>
                                Copy
                            </button>
                        </div>
                        <small>Use this URL in your n8n webhook node</small>
                        <small><em>✅ Dynamically generated for: <?php echo esc_html(home_url()); ?></em></small>
                    </div>
                    
                    <div class="tbp-config-card">
                        <div class="tbp-config-icon">
                            <span class="dashicons dashicons-shield"></span>
                        </div>
                        <h4>Required Headers</h4>
                        <div class="tbp-headers">
                            <div class="tbp-header-item">
                                <code>Content-Type: application/json</code>
                                <button class="tbp-copy-btn" data-text="Content-Type: application/json">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                            </div>
                            <div class="tbp-header-item">
                                <code>X-Webhook-Secret: your_secret_here</code>
                                <button class="tbp-copy-btn" data-text="X-Webhook-Secret: your_secret_here">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                            </div>
                        </div>
                        <small>Include these headers in your webhook requests</small>
                    </div>
                    
                    <div class="tbp-config-card tbp-config-card-wide">
                        <div class="tbp-config-icon">
                            <span class="dashicons dashicons-editor-code"></span>
                        </div>
                        <h4>Sample Payload</h4>
                        <div class="tbp-payload-container">
                            <pre class="tbp-payload">{
  "topic": "Your blog topic",
  "title": "Your blog title",
  "content": "Your blog content",
  "secret": "<?php echo esc_attr($webhook_secret); ?>"
}</pre>
                            <button id="copy-payload" class="tbp-btn tbp-btn-secondary">
                                <span class="dashicons dashicons-admin-page"></span>
                                Copy Payload
                            </button>
                        </div>
                        <small>Use this JSON structure for your webhook requests</small>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
new TelegramBlogPublisherEnhanced();
?>
