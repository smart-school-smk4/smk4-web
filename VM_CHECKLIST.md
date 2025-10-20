# ✅ VM Setup Checklist - SMK4 Web

## Pre-Deployment Checklist

### 🖥️ VM Requirements
- [ ] VM dengan Ubuntu 20.04/22.04 LTS
- [ ] Minimal 4GB RAM (recommended 8GB)
- [ ] Minimal 20GB storage space
- [ ] 2 CPU cores minimum
- [ ] Internet connection available
- [ ] SSH access (jika remote)

### 👤 User Setup
- [ ] Non-root user dengan sudo privileges
- [ ] SSH key configured (jika remote)

---

## 🚀 Quick Deployment (Recommended)

### Option 1: Auto Setup Script
```bash
# 1. Copy script ke VM
wget https://raw.githubusercontent.com/smart-school-smk4/smk4-web/main/vm-setup.sh

# 2. Run script
chmod +x vm-setup.sh
./vm-setup.sh

# 3. Logout/Login untuk Docker permissions

# 4. Deploy aplikasi
cd ~/smk4-web
./deploy.sh
```

### Option 2: Manual Step-by-Step
Ikuti tutorial lengkap di `VM_TUTORIAL.md`

---

## 🔍 Verification Steps

### After Deployment
- [ ] All containers running: `docker ps`
- [ ] App accessible: http://VM_IP:8080
- [ ] phpMyAdmin accessible: http://VM_IP:8081
- [ ] Database connection working
- [ ] No errors in logs: `docker-compose logs`

### Quick Tests
```bash
# Test containers
docker-compose -f docker-compose.new.yml ps

# Test database
docker exec smk4-web-mysql mysqladmin ping

# Test web response
curl -I http://localhost:8080
```

---

## 🛠️ Common Commands

### Start/Stop Services
```bash
# Start all
docker-compose -f docker-compose.new.yml up -d

# Stop all
docker-compose -f docker-compose.new.yml down

# Restart app only
docker-compose -f docker-compose.new.yml restart app
```

### Backup Database
```bash
# Manual backup
~/backup_smk4.sh

# Setup automatic daily backup
echo "0 2 * * * $HOME/backup_smk4.sh" | crontab -
```

### View Logs
```bash
# All services
docker-compose -f docker-compose.new.yml logs -f

# Specific service
docker-compose -f docker-compose.new.yml logs app
```

---

## 🆘 Troubleshooting Quick Fixes

### Container won't start
```bash
# Check logs
docker-compose -f docker-compose.new.yml logs

# Check disk space
df -h

# Restart Docker
sudo systemctl restart docker
```

### Database issues
```bash
# Reset database
docker-compose -f docker-compose.new.yml down -v
docker-compose -f docker-compose.new.yml up -d
```

### Permission issues
```bash
# Fix storage permissions
docker exec smk4-web-app chown -R www-data:www-data /var/www/html/storage
```

---

## 📊 Access Information

### Default URLs
- **Main App:** http://VM_IP:8080
- **phpMyAdmin:** http://VM_IP:8081

### Database Credentials
- **Host:** mysql (internal) / VM_IP:3306 (external)
- **Database:** db_presensi
- **Username:** laravel_user
- **Password:** laravel_password
- **Root Password:** root_password

### File Locations
- **Project:** ~/smk4-web/
- **Backups:** ~/backups/
- **Logs:** `docker-compose logs`

---

## 🔒 Security Notes

### For Production
- [ ] Change default database passwords
- [ ] Setup SSL certificate
- [ ] Configure proper firewall rules
- [ ] Setup reverse proxy with Nginx
- [ ] Enable automatic security updates
- [ ] Setup monitoring and alerting

### Basic Security
```bash
# Change default passwords in .env
nano ~/smk4-web/.env

# Setup UFW firewall
sudo ufw enable

# Keep system updated
sudo apt update && sudo apt upgrade -y
```

---

## 📞 Support

### Get Help
1. Check logs: `docker-compose -f docker-compose.new.yml logs`
2. Verify containers: `docker ps -a`
3. Check system resources: `htop`
4. Review VM_TUTORIAL.md for detailed steps

### Useful Resources
- Docker documentation: https://docs.docker.com/
- Laravel documentation: https://laravel.com/docs
- Ubuntu server guide: https://ubuntu.com/server/docs