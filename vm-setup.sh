#!/bin/bash

# 🚀 Quick Setup Script untuk VM - SMK4 Web
# Run this script on fresh Ubuntu VM

set -e  # Exit on any error

echo "🐧 SMK4 Web VM Setup Script"
echo "=========================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root"
   exit 1
fi

# Update system
print_status "Updating system packages..."
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git vim nano htop

# Install Docker
print_status "Installing Docker..."
if ! command -v docker &> /dev/null; then
    # Remove old versions
    sudo apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true
    
    # Install dependencies
    sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release
    
    # Add Docker GPG key
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    
    # Add repository
    echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Install Docker
    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io
    
    # Start and enable Docker
    sudo systemctl start docker
    sudo systemctl enable docker
    
    # Add user to docker group
    sudo usermod -aG docker $USER
    
    print_success "Docker installed successfully"
else
    print_warning "Docker already installed"
fi

# Install Docker Compose
print_status "Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    print_success "Docker Compose installed successfully"
else
    print_warning "Docker Compose already installed"
fi

# Configure firewall
print_status "Configuring firewall..."
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 8080
sudo ufw allow 8081
echo "y" | sudo ufw enable

# Clone repository (if not exists)
print_status "Setting up project..."
PROJECT_DIR="$HOME/smk4-web"

if [ ! -d "$PROJECT_DIR" ]; then
    print_status "Cloning repository..."
    git clone https://github.com/smart-school-smk4/smk4-web.git "$PROJECT_DIR"
else
    print_warning "Project directory already exists"
fi

cd "$PROJECT_DIR"

# Setup environment
if [ ! -f ".env" ]; then
    print_status "Setting up environment..."
    cp .env.docker .env
    print_success "Environment file created"
else
    print_warning ".env file already exists"
fi

# Make scripts executable
chmod +x deploy.sh 2>/dev/null || true

print_success "Setup completed!"
echo ""
echo "============================================"
print_status "Next steps:"
echo "1. Logout and login again (for Docker group permissions)"
echo "2. cd $PROJECT_DIR"
echo "3. ./deploy.sh"
echo ""
print_status "Or manually run:"
echo "docker-compose -f docker-compose.new.yml up --build -d"
echo ""
print_status "Access your application at:"
echo "- Web App: http://$(hostname -I | awk '{print $1}'):8080"
echo "- phpMyAdmin: http://$(hostname -I | awk '{print $1}'):8081"
echo "============================================"

# Create backup script
print_status "Creating backup script..."
cat > "$HOME/backup_smk4.sh" << 'EOF'
#!/bin/bash
BACKUP_DIR="$HOME/backups"
mkdir -p "$BACKUP_DIR"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

cd "$HOME/smk4-web"

# Backup database
docker exec smk4-web-mysql mysqldump -u root -proot_password db_presensi > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# Backup uploaded files (if any)
tar -czf "$BACKUP_DIR/files_backup_$TIMESTAMP.tar.gz" storage/app/public/ 2>/dev/null || true

echo "Backup completed: $TIMESTAMP"
echo "Database: $BACKUP_DIR/db_backup_$TIMESTAMP.sql"
echo "Files: $BACKUP_DIR/files_backup_$TIMESTAMP.tar.gz"
EOF

chmod +x "$HOME/backup_smk4.sh"
print_success "Backup script created at $HOME/backup_smk4.sh"

print_success "VM setup script completed!"
print_warning "IMPORTANT: Logout and login again for Docker permissions to take effect!"