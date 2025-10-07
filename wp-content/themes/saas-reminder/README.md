# SaaS Reminder Theme

A minimal, fast block theme designed for a daily task reminder SaaS application. Built with modern WordPress block editor features and accessibility in mind.

## Features

- **Minimal Design**: Clean, modern interface focused on content
- **Fast Performance**: No external CSS frameworks, optimized for speed
- **Accessibility**: WCAG compliant with skip links, focus states, and semantic HTML
- **Responsive**: Mobile-first design that works on all devices
- **Block Patterns**: Pre-built sections for hero, features, pricing, and CTA
- **Custom Colors**: Primary #5B7CFF, Dark #0B1020, Surface #0F1535, Text #E8ECFF

## Pages Included

- **Home**: Hero section with features and CTA
- **Features**: Detailed feature showcase
- **Pricing**: Three-tier pricing (Starter, Pro, Business)
- **Documentation**: Help and API reference
- **Contact**: Accessible contact form

## Installation

1. Upload the `saas-reminder` folder to `/wp-content/themes/`
2. Activate the theme in WordPress Admin → Appearance → Themes
3. The theme will automatically:
   - Create all necessary pages
   - Set up the primary navigation menu
   - Set the Home page as the static front page

## Customization

### Colors
The theme uses CSS custom properties defined in `theme.json`:
- Primary: #5B7CFF
- Dark: #0B1020
- Surface: #0F1535
- Text: #E8ECFF

### Typography
- Base font: 16px system font stack
- Responsive headings using `clamp()`
- Font sizes defined in `theme.json`

### Block Patterns
Available patterns:
- `saas-reminder/hero` - Hero section with headline and CTA
- `saas-reminder/features` - Three-column features section
- `saas-reminder/pricing` - Pricing table with three tiers
- `saas-reminder/cta` - Call-to-action section

## File Structure

```
saas-reminder/
├── style.css              # Theme styles and header
├── theme.json             # Theme configuration
├── functions.php          # Theme functions and setup
├── parts/
│   ├── header.html        # Site header
│   └── footer.html        # Site footer
├── templates/
│   ├── front-page.html    # Homepage template
│   ├── page.html          # Page template
│   └── single.html        # Single post template
├── patterns/
│   ├── hero.php           # Hero pattern
│   ├── features.php       # Features pattern
│   ├── pricing.php        # Pricing pattern
│   └── cta.php            # CTA pattern
├── screenshot.png         # Theme screenshot
└── README.md             # This file
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Modern browser with CSS Grid and Flexbox support

## License

GPL v2 or later

## Support

For theme support and customization, please contact the theme developer.
