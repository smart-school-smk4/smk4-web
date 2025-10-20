#!/bin/bash

# Docker Deployment Script for SMK4 Web
echo "🚀 Starting SMK4 Web deployment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Stop existing containers
echo "🛑 Stopping existing containers..."
docker-compose -f docker-compose.new.yml down

# Remove old images (optional - uncomment if you want to rebuild everything)
# docker system prune -f

# Build and start containers
echo "🔨 Building and starting containers..."
docker-compose -f docker-compose.new.yml up --build -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
until docker exec smk4-web-mysql mysqladmin ping -h"localhost" --silent; do
    echo "Waiting for MySQL..."
    sleep 2
done

# Run Laravel migrations
echo "📦 Running Laravel migrations..."
docker exec smk4-web-app php artisan migrate --force

# Clear and cache Laravel configurations
echo "🧹 Clearing and caching Laravel configurations..."
docker exec smk4-web-app php artisan config:cache
docker exec smk4-web-app php artisan route:cache
docker exec smk4-web-app php artisan view:cache

# Generate application key if needed
docker exec smk4-web-app php artisan key:generate --force

echo "✅ Deployment completed!"
echo ""
echo "🌐 Application is available at: http://localhost:8080"
echo "🗄️  phpMyAdmin is available at: http://localhost:8081"
echo "📊 MySQL is available at: localhost:3306"
echo ""
echo "Database credentials:"
echo "  - Database: db_presensi"
echo "  - Username: laravel_user"
echo "  - Password: laravel_password"
echo "  - Root password: root_password"