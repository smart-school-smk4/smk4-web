#!/bin/bash

# Script untuk build dan prepare deployment

echo "🚀 Starting build process..."

# Build assets dengan Vite
echo "📦 Building CSS and JS assets..."
npm run build

# Cek apakah build berhasil
if [ -f "public/build/assets/app-vZZfIl8z.css" ]; then
    echo "✅ CSS build successful"
else
    echo "❌ CSS build failed"
    exit 1
fi

if [ -f "public/build/assets/app-DNXYN7wm.js" ]; then
    echo "✅ JS build successful"
else
    echo "❌ JS build failed"
    exit 1
fi

# Clear cache untuk memastikan perubahan teraplikasi
echo "🧹 Clearing cache..."
php artisan config:cache
php artisan view:cache
php artisan route:cache

echo "✨ Build process completed!"
echo ""
echo "📋 Files ready for deployment:"
echo "- public/build/assets/app-vZZfIl8z.css"
echo "- public/build/assets/app-DNXYN7wm.js" 
echo "- public/build/manifest.json"
echo ""
echo "🌐 Upload these files to your production server's public/build/ directory"
