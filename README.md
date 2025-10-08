# WordPress Website - CI/CD Implementation

This repository contains a WordPress website with custom themes and CI/CD implementation for deployment to KloudBean.

## 🎯 Project Structure

```
├── wp-content/themes/
│   ├── saas-reminder/     # Minimal block theme for task reminders
│   └── aurora/            # Beautiful modern theme with gradients
├── .github/workflows/     # CI/CD workflows
├── deploy.sh             # Deployment script
└── README.md             # This file
```

## 🚀 Themes Included

### 1. SaaS Reminder Theme
- **Location**: `wp-content/themes/saas-reminder/`
- **Type**: WordPress Block Theme
- **Features**: 
  - Minimal design for task reminder SaaS
  - Responsive layout
  - Custom color palette
  - Accessibility features

### 2. Aurora Theme
- **Location**: `wp-content/themes/aurora/`
- **Type**: WordPress Block Theme
- **Features**:
  - Beautiful gradient designs
  - Modern animations
  - Google Fonts integration
  - Mobile-first responsive design

## 🛠️ Development Setup

### Prerequisites
- PHP 8.2+
- WordPress 6.0+
- Git

### Local Development
1. Clone the repository
2. Set up WordPress locally (XAMPP, Local by Flywheel, etc.)
3. Copy the theme files to your WordPress installation
4. Activate the desired theme in WordPress admin

### Theme Development
- Edit theme files in `wp-content/themes/[theme-name]/`
- Use `theme.json` for global styles
- Follow WordPress coding standards

## 🚀 Deployment

### Automatic Deployment (CI/CD)
- Push to `main` branch triggers deployment
- GitHub Actions handles the build process
- Automatic deployment to KloudBean

### Manual Deployment
1. Run the deployment script:
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```
2. Upload the generated package to KloudBean
3. Extract and configure

## 📁 File Structure

```
├── wp-content/themes/saas-reminder/
│   ├── style.css
│   ├── theme.json
│   ├── functions.php
│   ├── templates/
│   ├── parts/
│   └── patterns/
├── wp-content/themes/aurora/
│   ├── style.css
│   ├── theme.json
│   ├── functions.php
│   ├── templates/
│   └── parts/
├── .github/workflows/deploy.yml
├── deploy.sh
└── README.md
```

## 🎨 Theme Features

### SaaS Reminder Theme
- **Colors**: Primary #5B7CFF, Dark #0B1020, Surface #0F1535
- **Typography**: System fonts with responsive sizing
- **Layout**: Block-based with custom patterns
- **Accessibility**: WCAG compliant

### Aurora Theme
- **Colors**: Purple gradients, modern palette
- **Typography**: Inter + Poppins fonts
- **Layout**: Gradient backgrounds, smooth animations
- **Features**: Ripple effects, fade-in animations

## 🔧 Configuration

### Theme Activation
1. Go to WordPress Admin → Appearance → Themes
2. Activate the desired theme
3. Customize through the Customizer

### Customization
- Edit `theme.json` for global styles
- Modify template files in `templates/`
- Add custom patterns in `patterns/`

## 📝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 🐛 Troubleshooting

### Common Issues
- **Theme not appearing**: Check file permissions and WordPress version
- **Styles not loading**: Verify `theme.json` syntax
- **Database errors**: Ensure proper WordPress configuration

### Support
- Check WordPress documentation
- Review theme files for syntax errors
- Test in a clean WordPress installation

## 📄 License

This project is licensed under the GPL v2 or later - see the [WordPress License](https://wordpress.org/about/license/) for details.

## 🙏 Acknowledgments

- WordPress.org for the amazing platform
- KloudBean for hosting services
- GitHub for CI/CD capabilities
