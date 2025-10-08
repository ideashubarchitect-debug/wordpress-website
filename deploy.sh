#!/bin/bash

# WordPress Deployment Script for KloudBean
# This script handles the deployment process

echo "🚀 Starting WordPress deployment to KloudBean..."

# Check if we're in the right directory
if [ ! -f "wp-config.php" ]; then
    echo "❌ Error: wp-config.php not found. Are you in the WordPress root directory?"
    exit 1
fi

# Create deployment package
echo "📦 Creating deployment package..."
tar -czf wordpress-deployment-$(date +%Y%m%d-%H%M%S).tar.gz \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='*.log' \
    --exclude='wp-content/uploads' \
    .

echo "✅ Deployment package created successfully!"
echo "📁 Package: wordpress-deployment-$(date +%Y%m%d-%H%M%S).tar.gz"

# Instructions for manual deployment
echo ""
echo "📋 Manual deployment steps:"
echo "1. Upload the package to your KloudBean hosting"
echo "2. Extract the files in your WordPress root directory"
echo "3. Update wp-config.php with your KloudBean database credentials"
echo "4. Test the website to ensure everything works"
echo ""
echo "🎉 Deployment process completed!"
