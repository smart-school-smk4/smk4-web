# 🐧 Tutorial Deploy SMK4 Web di VM - Step by Step

## 📋 Prerequisites VM

### 1. Spesifikasi Minimum VM
- **OS:** Ubuntu 20.04/22.04 LTS atau CentOS 8+
- **RAM:** Minimal 4GB (recommended 8GB)
- **Storage:** Minimal 20GB free space
- **CPU:** 2 cores minimum
- **Network:** Internet connection untuk download dependencies

### 2. User Privileges
- User dengan sudo privileges
- Akses SSH ke VM (jika remote)

---

## 🛠️ Step 1: Persiapan VM

### 1.1 Update System
```bash
# Update package repository
sudo apt update && sudo apt upgrade -y

# Install basic tools
sudo apt install -y curl wget git vim nano htop
```

### 1.2 Install Docker
```bash
# Remove old Docker versions (if any)
sudo apt remove docker docker-engine docker.io containerd runc

# Install Docker dependencies
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release

# Add Docker GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Add user to docker group (logout/login required after this)
sudo usermod -aG docker $USER

# Verify Docker installation
docker --version
```

### 1.3 Install Docker Compose
```bash
# Download Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

# Make it executable
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker-compose --version
```

### 1.4 Configure Firewall (jika diperlukan)
```bash
# Allow SSH (important!)
sudo ufw allow ssh

# Allow HTTP and HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Allow application ports
sudo ufw allow 8080  # Laravel app
sudo ufw allow 8081  # phpMyAdmin

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## 📁 Step 2: Deploy Aplikasi

### 2.1 Clone Repository
```bash
# Navigate to desired directory
cd /home/$USER

# Clone your repository
git clone https://github.com/smart-school-smk4/smk4-web.git

# Enter project directory
cd smk4-web

# Verify files
ls -la
```

### 2.2 Setup Environment
```bash
# Copy Docker environment file
cp .env.docker .env

# Edit environment if needed
nano .env

# Set proper permissions
chmod 644 .env
```

### 2.3 Build and Deploy
```bash
# Make deploy script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

**Atau manual deployment:**
```bash
# Stop any existing containers
docker-compose -f docker-compose.new.yml down

# Build and start containers
docker-compose -f docker-compose.new.yml up --build -d

# Wait a moment for MySQL to start, then run migrations
sleep 30
docker exec smk4-web-app php artisan migrate --force

# Cache configurations
docker exec smk4-web-app php artisan config:cache
docker exec smk4-web-app php artisan route:cache
docker exec smk4-web-app php artisan view:cache
```

---

## 🔍 Step 3: Verifikasi Deployment

### 3.1 Check Container Status
```bash
# Check all containers
docker-compose -f docker-compose.new.yml ps

# Check logs
docker-compose -f docker-compose.new.yml logs

# Check specific service logs
docker-compose -f docker-compose.new.yml logs app
docker-compose -f docker-compose.new.yml logs mysql
```

### 3.2 Test Application
```bash
# Test database connection
docker exec smk4-web-mysql mysqladmin ping -h localhost -u laravel_user -plaravel_password

# Test Laravel
curl -I http://localhost:8080

# Check if all services are responding
curl -I http://localhost:8081  # phpMyAdmin
```

### 3.3 Akses dari Browser
- **Aplikasi:** http://[VM_IP]:8080
- **phpMyAdmin:** http://[VM_IP]:8081

---

## 🌐 Step 4: Konfigurasi Production (Optional)

### 4.1 Setup Reverse Proxy dengan Nginx
```bash
# Install Nginx for reverse proxy
sudo apt install -y nginx

# Create site configuration
sudo nano /etc/nginx/sites-available/smk4-web
```

**Content file nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;  # Ganti dengan domain Anda
    
    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/smk4-web /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 4.2 Setup SSL dengan Let's Encrypt (Optional)
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

---

## 📊 Step 5: Monitoring dan Maintenance

### 5.1 Monitoring Commands
```bash
# Check system resources
htop

# Check Docker stats
docker stats

# Check disk space
df -h

# Check container logs
docker-compose -f docker-compose.new.yml logs -f --tail=100
```

### 5.2 Backup Database
```bash
# Create backup directory
mkdir -p ~/backups

# Backup database
docker exec smk4-web-mysql mysqldump -u root -proot_password db_presensi > ~/backups/db_backup_$(date +%Y%m%d_%H%M%S).sql

# Create automated backup script
cat > ~/backup_db.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/home/$USER/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker exec smk4-web-mysql mysqldump -u root -proot_password db_presensi > $BACKUP_DIR/db_backup_$TIMESTAMP.sql
echo "Backup created: db_backup_$TIMESTAMP.sql"
EOF

chmod +x ~/backup_db.sh
```

### 5.3 Setup Cron untuk Auto Backup
```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * /home/$USER/backup_db.sh
```

---

## 🆘 Troubleshooting Common Issues

### Issue 1: Container tidak start
```bash
# Check logs
docker-compose -f docker-compose.new.yml logs

# Check disk space
df -h

# Restart Docker service
sudo systemctl restart docker
```

### Issue 2: Database connection error
```bash
# Check MySQL container
docker exec smk4-web-mysql mysqladmin ping

# Reset database
docker-compose -f docker-compose.new.yml down -v
docker-compose -f docker-compose.new.yml up -d
```

### Issue 3: Permission issues
```bash
# Fix Laravel permissions
docker exec smk4-web-app chown -R www-data:www-data /var/www/html/storage
docker exec smk4-web-app chmod -R 755 /var/www/html/storage
```

### Issue 4: Memory issues
```bash
# Check memory usage
free -h

# Add swap if needed
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Make permanent
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## 🔧 Useful Commands Reference

### Docker Management
```bash
# Start all services
docker-compose -f docker-compose.new.yml up -d

# Stop all services
docker-compose -f docker-compose.new.yml down

# Restart specific service
docker-compose -f docker-compose.new.yml restart app

# View logs (live)
docker-compose -f docker-compose.new.yml logs -f

# Execute commands in container
docker exec -it smk4-web-app bash
docker exec smk4-web-app php artisan [command]
```

### Laravel Commands in Container
```bash
# Clear cache
docker exec smk4-web-app php artisan cache:clear
docker exec smk4-web-app php artisan config:clear
docker exec smk4-web-app php artisan route:clear
docker exec smk4-web-app php artisan view:clear

# Run migrations
docker exec smk4-web-app php artisan migrate

# Create user
docker exec smk4-web-app php artisan tinker
```

### System Monitoring
```bash
# System resources
htop
iotop
nethogs

# Docker resources
docker system df
docker system prune

# Container stats
docker stats --no-stream
```

---

## ✅ Final Checklist

- [ ] VM specs meet requirements
- [ ] Docker and Docker Compose installed
- [ ] Repository cloned successfully
- [ ] Environment configured (.env file)
- [ ] Containers built and running
- [ ] Database migrations completed
- [ ] Application accessible via browser
- [ ] Backup strategy implemented
- [ ] Monitoring setup configured
- [ ] Firewall properly configured
- [ ] SSL certificate installed (if needed)

---

## 📞 Support

Jika mengalami masalah:
1. Check logs dengan `docker-compose logs`
2. Verify semua containers running dengan `docker ps`
3. Check system resources dengan `htop`
4. Review error messages di browser developer tools

**Database Credentials untuk troubleshooting:**
- Database: db_presensi
- Username: laravel_user
- Password: laravel_password
- Root Password: root_password