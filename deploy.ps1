# Docker Deployment Script for SMK4 Web (PowerShell)
Write-Host "🚀 Starting SMK4 Web deployment..." -ForegroundColor Green

# Check if Docker is running
try {
    docker info | Out-Null
} catch {
    Write-Host "❌ Docker is not running. Please start Docker first." -ForegroundColor Red
    exit 1
}

# Stop existing containers
Write-Host "🛑 Stopping existing containers..." -ForegroundColor Yellow
docker-compose -f docker-compose.new.yml down

# Build and start containers
Write-Host "🔨 Building and starting containers..." -ForegroundColor Blue
docker-compose -f docker-compose.new.yml up --build -d

# Wait for MySQL to be ready
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Cyan
do {
    Write-Host "Waiting for MySQL..." -ForegroundColor Gray
    Start-Sleep -Seconds 2
    $mysqlReady = docker exec smk4-web-mysql mysqladmin ping -h"localhost" --silent 2>$null
} while ($LASTEXITCODE -ne 0)

# Run Laravel migrations
Write-Host "📦 Running Laravel migrations..." -ForegroundColor Magenta
docker exec smk4-web-app php artisan migrate --force

# Clear and cache Laravel configurations
Write-Host "🧹 Clearing and caching Laravel configurations..." -ForegroundColor DarkYellow
docker exec smk4-web-app php artisan config:cache
docker exec smk4-web-app php artisan route:cache
docker exec smk4-web-app php artisan view:cache

# Generate application key if needed
docker exec smk4-web-app php artisan key:generate --force

Write-Host "✅ Deployment completed!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Application is available at: http://localhost:8080" -ForegroundColor White
Write-Host "🗄️  phpMyAdmin is available at: http://localhost:8081" -ForegroundColor White
Write-Host "📊 MySQL is available at: localhost:3306" -ForegroundColor White
Write-Host ""
Write-Host "Database credentials:" -ForegroundColor Yellow
Write-Host "  - Database: db_presensi" -ForegroundColor Gray
Write-Host "  - Username: laravel_user" -ForegroundColor Gray
Write-Host "  - Password: laravel_password" -ForegroundColor Gray
Write-Host "  - Root password: root_password" -ForegroundColor Gray