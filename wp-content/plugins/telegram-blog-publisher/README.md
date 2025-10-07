# 📱 Telegram Blog Publisher

A powerful WordPress plugin that allows you to publish blog posts directly from Telegram via n8n webhooks. Send a topic and details from Telegram, and automatically create and publish blog posts on your WordPress site using AI.

## ✨ Features

- **🤖 AI-Powered Content Generation** - Supports OpenAI, Claude, and Gemini
- **📱 Telegram Integration** - Publish blogs directly from Telegram
- **🔗 n8n Webhook Support** - Seamless integration with n8n workflows
- **⚙️ Flexible Configuration** - Customize categories, tags, authors, and more
- **📊 Activity Logging** - Track all webhook requests and generated posts
- **🎯 Smart Content** - AI generates SEO-friendly, well-structured blog posts
- **🖼️ Image Support** - Automatic featured image handling
- **📝 Draft/Publish Control** - Choose whether to auto-publish or save as drafts

## 🚀 Installation

1. Upload the `telegram-blog-publisher` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Telegram Publisher' in the admin menu to configure

## ⚙️ Configuration

### 1. Webhook Setup
- Copy the webhook URL and secret from the dashboard
- Use these in your n8n workflow

### 2. AI Service Configuration
- Choose your preferred AI service (OpenAI, Claude, or Gemini)
- Enter your API key
- Test the configuration

### 3. Default Settings
- Set default author, category, and publishing status
- Configure auto-publish behavior

## 🔧 n8n Integration

### Step 1: Create n8n Workflow
1. Add a "Telegram Trigger" node
2. Configure your Telegram bot token
3. Add an "HTTP Request" node
4. Set the URL to your webhook endpoint
5. Add required headers

### Step 2: Configure Data Mapping
Map Telegram message data to the webhook payload:

```json
{
  "topic": "{{ $json.message.text }}",
  "details": "{{ $json.message.text }}",
  "category": "General",
  "tags": "telegram, auto-generated",
  "status": "draft"
}
```

### Step 3: Test Integration
Send a message to your Telegram bot with a topic and details!

## 📋 Webhook API

### Endpoint
```
POST /wp-json/telegram-blog-publisher/v1/webhook
```

### Headers
```
Content-Type: application/json
X-Webhook-Secret: your_secret_key
```

### Request Body
```json
{
  "topic": "Your Blog Post Title",
  "details": "Additional details about the topic",
  "category": "Optional Category",
  "tags": "tag1, tag2, tag3",
  "featured_image": "https://example.com/image.jpg",
  "author_id": 1,
  "status": "publish"
}
```

### Response
```json
{
  "success": true,
  "message": "Blog post created successfully",
  "post_id": 123,
  "post_url": "https://yoursite.com/post-title/",
  "edit_url": "https://yoursite.com/wp-admin/post.php?post=123&action=edit"
}
```

## 🎯 Example Usage

### Simple Topic
Send to Telegram: `Write about the benefits of meditation`

### Topic + Details
Send to Telegram:
```
Topic: Remote Work Tips
Details: Share practical advice for staying productive while working from home, including time management and communication strategies.
```

## 🤖 AI Services Supported

### OpenAI (GPT-3.5)
- Get API key from [OpenAI Platform](https://platform.openai.com/api-keys)
- Cost-effective and fast
- Great for general content

### Claude (Anthropic)
- Get API key from [Anthropic Console](https://console.anthropic.com/)
- Excellent for detailed, thoughtful content
- Good for technical topics

### Gemini (Google)
- Get API key from [Google AI Studio](https://makersuite.google.com/app/apikey)
- Free tier available
- Good for creative content

## 📊 Admin Features

### Dashboard
- Webhook configuration and testing
- Quick stats and recent posts
- Activity logs and monitoring
- Quick content generation test

### Settings
- AI service configuration
- Default post settings
- Webhook security settings
- API key management

### Logs
- View all webhook requests
- Track post creation activity
- Export logs for analysis
- Filter by action type

## 🔒 Security

- Webhook secret authentication
- Input sanitization and validation
- Nonce verification for admin actions
- Capability checks for all operations

## 🛠️ Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- cURL extension enabled
- Valid AI service API key

## 📝 Changelog

### Version 1.0.0
- Initial release
- AI-powered content generation
- Telegram webhook integration
- n8n workflow support
- Admin dashboard and settings
- Activity logging and monitoring

## 🤝 Support

For support, feature requests, or bug reports, please contact:
- Email: vikram@barcodemine.com
- Website: https://barcodemine.com

## 📄 License

This plugin is licensed under the GPL v2 or later.

---

**Made with ❤️ for the WordPress community**
