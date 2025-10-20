# Docker Deployment Guide - SMK4 Web

Docker setup untuk aplikasi Laravel SMK4 Web dengan MySQL, Redis, dan phpMyAdmin.

## Prerequisites

- Docker Desktop installed dan running
- Git (untuk clone repository)
- Minimal 4GB RAM untuk menjalankan semua container

## Quick Start

### 1. Clone Repository (jika belum)
```bash
git clone <repository-url>
cd smk4-web
```

### 2. Setup Environment
```bash
# Copy environment file untuk Docker
cp .env.docker .env
```

### 3. Deploy with Script

#### Windows (PowerShell):
```powershell
.\deploy.ps1
```

#### Linux/Mac:
```bash
chmod +x deploy.sh
./deploy.sh
```

### 4. Manual Deployment
Jika script tidak berjalan, gunakan command manual:

```bash
# Stop existing containers
docker-compose -f docker-compose.new.yml down

# Build and start
docker-compose -f docker-compose.new.yml up --build -d

# Wait for MySQL, then run migrations
docker exec smk4-web-app php artisan migrate --force
docker exec smk4-web-app php artisan config:cache
```

## Services

| Service | URL | Description |
|---------|-----|-------------|
| Web App | http://localhost:8080 | Laravel Application |
| phpMyAdmin | http://localhost:8081 | Database Management |
| MySQL | localhost:3306 | Database Server |
| Redis | localhost:6379 | Cache & Session Store |

## Database Credentials

- **Database:** db_presensi
- **Username:** laravel_user  
- **Password:** laravel_password
- **Root Password:** root_password

## Architecture

### Multi-stage Build
Docker menggunakan multi-stage build untuk optimasi:

1. **Frontend Stage:** Build Tailwind CSS dengan Node.js
2. **Backend Stage:** Setup PHP-FPM dengan Laravel

### Container Structure
- **app:** Laravel application dengan Nginx + PHP-FPM
- **mysql:** MySQL 8.0 database
- **redis:** Redis untuk cache dan session
- **phpmyadmin:** Web interface untuk database

## Development vs Production

### Development Mode
Untuk development dengan hot reload:
```bash
# Install dependencies
npm install
composer install

# Run development servers
npm run dev          # Vite dev server (port 5173)
php artisan serve    # Laravel dev server (port 8000)
```

### Production Mode
Docker setup ini untuk production dengan:
- Built assets (CSS/JS pre-compiled)
- Optimized PHP opcache
- Nginx serving static files
- Redis for sessions/cache

## Troubleshooting

### Container tidak start
```bash
# Check logs
docker-compose -f docker-compose.new.yml logs

# Check specific service
docker-compose -f docker-compose.new.yml logs app
docker-compose -f docker-compose.new.yml logs mysql
```

### Database connection error
```bash
# Check MySQL status
docker exec smk4-web-mysql mysqladmin ping

# Reset database
docker-compose -f docker-compose.new.yml down -v
docker-compose -f docker-compose.new.yml up -d
```

### Permission issues
```bash
# Fix Laravel permissions
docker exec smk4-web-app chown -R www-data:www-data /var/www/html/storage
docker exec smk4-web-app chmod -R 755 /var/www/html/storage
```

### Clear Laravel cache
```bash
docker exec smk4-web-app php artisan cache:clear
docker exec smk4-web-app php artisan config:clear
docker exec smk4-web-app php artisan route:clear
docker exec smk4-web-app php artisan view:clear
```

## Useful Commands

```bash
# Start containers
docker-compose -f docker-compose.new.yml up -d

# Stop containers
docker-compose -f docker-compose.new.yml down

# View logs
docker-compose -f docker-compose.new.yml logs -f

# Execute commands in app container
docker exec -it smk4-web-app bash
docker exec smk4-web-app php artisan [command]

# Backup database
docker exec smk4-web-mysql mysqldump -u root -proot_password db_presensi > backup.sql

# Restore database
docker exec -i smk4-web-mysql mysql -u root -proot_password db_presensi < backup.sql
```

## File Structure

```
├── docker/
│   ├── nginx.conf          # Nginx configuration
│   └── supervisord.conf    # Process manager config
├── database/
│   └── init.sql           # Database initialization
├── Dockerfile.new         # Multi-stage Dockerfile
├── docker-compose.new.yml # Docker Compose configuration
├── .env.docker           # Environment template for Docker
├── .dockerignore         # Docker ignore file
├── deploy.sh             # Linux/Mac deployment script
└── deploy.ps1            # Windows deployment script
```

## Security Notes

- Change default passwords in production
- Use environment variables for sensitive data
- Enable firewall rules for production deployment
- Consider using Docker secrets for production passwords