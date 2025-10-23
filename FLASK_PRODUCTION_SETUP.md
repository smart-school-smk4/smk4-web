# 🚀 Flask API Production Setup Guide

## 📋 Diagnosa Masalah "Gagal terhubung ke device"

### ✅ Checklist Setup Production

#### 1️⃣ **Flask API Harus Running di Server**

**Cek apakah Flask aktif:**
```bash
# SSH ke server production
ssh user@dkvsmkn4jember.my.id

# Cek proses Flask
ps aux | grep python
ps aux | grep flask
ps aux | grep app.py

# Atau cek port 5000
sudo netstat -tlnp | grep 5000
# atau
sudo lsof -i :5000
```

**Jika belum running, jalankan Flask:**
```bash
cd /path/to/arcface-facerecog
python3 app.py

# Atau gunakan screen/tmux agar tetap running:
screen -S flask
python3 app.py
# Ctrl+A, D untuk detach

# Atau gunakan systemd service (recommended)
```

---

#### 2️⃣ **Setup Systemd Service (Production Best Practice)**

Buat service file untuk auto-start Flask:

```bash
sudo nano /etc/systemd/system/flask-attendance.service
```

Isi dengan:
```ini
[Unit]
Description=Flask Attendance Face Recognition API
After=network.target

[Service]
Type=simple
User=www-data
# atau user Anda, misalnya: User=ubuntu

WorkingDirectory=/home/ubuntu/arcface-facerecog
# Sesuaikan path dengan lokasi Flask Anda

Environment="PATH=/home/ubuntu/arcface-facerecog/venv/bin"
# Jika pakai virtual environment

ExecStart=/home/ubuntu/arcface-facerecog/venv/bin/python3 /home/ubuntu/arcface-facerecog/app.py
# Atau tanpa venv:
# ExecStart=/usr/bin/python3 /home/ubuntu/arcface-facerecog/app.py

Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable dan jalankan:
```bash
sudo systemctl daemon-reload
sudo systemctl enable flask-attendance
sudo systemctl start flask-attendance

# Cek status
sudo systemctl status flask-attendance

# Lihat log jika error
sudo journalctl -u flask-attendance -f
```

---

#### 3️⃣ **Firewall & Port Configuration**

**Buka port 5000 (jika perlu akses dari luar):**
```bash
# UFW (Ubuntu)
sudo ufw allow 5000/tcp
sudo ufw status

# Firewalld (CentOS/RHEL)
sudo firewall-cmd --permanent --add-port=5000/tcp
sudo firewall-cmd --reload

# iptables
sudo iptables -A INPUT -p tcp --dport 5000 -j ACCEPT
```

**⚠️ PENTING untuk Security:**
Jika Flask dan Laravel di server yang sama, **JANGAN buka port 5000 ke public**. 
Gunakan `127.0.0.1` atau `localhost` saja:

```python
# Di app.py Flask
if __name__ == '__main__':
    # Production - hanya listen di localhost
    app.run(host='127.0.0.1', port=5000, debug=False)
    
    # Atau jika perlu akses dari LAN
    # app.run(host='0.0.0.0', port=5000, debug=False)
```

---

#### 4️⃣ **Update IP Address Device di Database**

**Cek IP device di database:**
```bash
# SSH ke server
mysql -u root -p

USE smk4_db;  # Sesuaikan nama database
SELECT id, nama_device, ip_address FROM devices;
```

**Update IP jika salah:**
```sql
-- Jika Flask di server yang sama dengan Laravel
UPDATE devices SET ip_address = '127.0.0.1' WHERE id = 1;

-- Atau jika Flask di device terpisah (IP LAN)
UPDATE devices SET ip_address = '192.168.1.100' WHERE id = 1;

-- Commit
COMMIT;
```

**Atau update via Laravel Tinker:**
```bash
php artisan tinker

>>> $device = \App\Models\Devices::find(1);
>>> $device->ip_address = '127.0.0.1';
>>> $device->save();
>>> exit
```

---

#### 5️⃣ **Test Koneksi dari Server Laravel ke Flask**

**Test manual dari server:**
```bash
# SSH ke server production
ssh user@dkvsmkn4jember.my.id

# Test curl ke Flask API
curl http://127.0.0.1:5000/

# Expected response: HTML dashboard Flask

# Test set_mode endpoint
curl http://127.0.0.1:5000/set_mode/masuk
# Expected: "Mode absensi diubah ke: masuk"

curl http://127.0.0.1:5000/set_mode/keluar
# Expected: "Mode absensi diubah ke: keluar"
```

**Jika curl gagal:**
```bash
# Cek apakah Flask running
ps aux | grep python

# Cek port listening
sudo netstat -tlnp | grep 5000

# Cek log Flask
tail -f /path/to/flask/logs/app.log
# atau
sudo journalctl -u flask-attendance -f
```

---

#### 6️⃣ **Network Topology Scenarios**

**Scenario A: Flask & Laravel di Server yang Sama** ✅ Recommended
```
┌─────────────────────────────────────┐
│  Server Production (VPS/Cloud)     │
│  IP: dkvsmkn4jember.my.id          │
│                                     │
│  ┌─────────────┐  HTTP Request     │
│  │   Laravel   │ ────────────────► │
│  │ (Port 80)   │  127.0.0.1:5000   │
│  └─────────────┘                    │
│         ▲                           │
│         │         ┌──────────────┐  │
│         └─────────│  Flask API   │  │
│                   │  (Port 5000) │  │
│                   └──────────────┘  │
└─────────────────────────────────────┘

Database IP: 127.0.0.1 atau localhost
```

**Scenario B: Flask di Device Terpisah (Camera/Raspberry Pi)**
```
┌──────────────────┐         ┌──────────────────┐
│  Laravel Server  │         │  Flask Device    │
│  (VPS/Cloud)     │         │  (Raspberry Pi)  │
│                  │  HTTP   │                  │
│  IP: dkvsmkn4j.. │ ──────► │  IP: 192.168.x.x │
│  Port: 80/443    │ Request │  Port: 5000      │
└──────────────────┘         └──────────────────┘
        ▲                             ▲
        │                             │
        └─────────── LAN ─────────────┘

Database IP: 192.168.x.x (IP lokal device)
Syarat: Harus 1 network atau VPN
```

**Scenario C: Docker Compose (Keduanya di Container)**
```
┌─────────────────────────────────────┐
│  Docker Network: smk4-network       │
│                                     │
│  ┌──────────────┐  ┌─────────────┐ │
│  │  laravel-app │──│  flask-api  │ │
│  │  Port: 8080  │  │  Port: 5000 │ │
│  └──────────────┘  └─────────────┘ │
└─────────────────────────────────────┘

Database IP: flask-api (service name)
```

---

#### 7️⃣ **Debugging di Production**

**Enable Laravel log:**
```bash
# SSH ke server
cd /path/to/laravel

# Tail log real-time
tail -f storage/logs/laravel.log

# Atau
php artisan log:clear
# Klik tombol di UI
tail -f storage/logs/laravel.log
```

**Cek log yang ditambahkan di controller:**
- `Attempting to connect to Flask API: http://...`
- `Successfully changed mode to ...`
- `Connection failed to Flask API: ...`

---

#### 8️⃣ **Quick Fix Commands**

**Restart semua service:**
```bash
# Restart Flask
sudo systemctl restart flask-attendance

# Restart PHP-FPM (jika pakai Nginx)
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Clear Laravel cache
cd /path/to/laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🔍 Troubleshooting by Error Message

### Error: "Gagal terhubung ke device. Pastikan Flask API aktif"

**Kemungkinan penyebab:**
1. Flask tidak running
2. Port 5000 tidak listening
3. IP address salah di database
4. Firewall memblokir

**Solusi:**
```bash
# 1. Cek Flask running
ps aux | grep python
sudo systemctl status flask-attendance

# 2. Cek port
sudo netstat -tlnp | grep 5000

# 3. Cek IP di database
mysql> SELECT ip_address FROM devices;

# 4. Test curl manual
curl http://127.0.0.1:5000/set_mode/masuk
```

---

### Error: "Connection timeout"

**Penyebab:** Network issue atau Flask lambat respond

**Solusi:**
```bash
# Increase timeout (sudah dilakukan di controller: 10 detik)

# Cek network latency
ping 127.0.0.1
ping 192.168.x.x  # jika Flask di device lain

# Cek load server
top
htop

# Restart Flask
sudo systemctl restart flask-attendance
```

---

### Error: "Mixed Content" di browser console

**Penyebab:** Browser memblokir HTTP dari halaman HTTPS

**Solusi:** ✅ Sudah diperbaiki!
Request sekarang dari **server-side Laravel**, bukan dari browser.
Jadi tidak ada Mixed Content issue.

---

## ✅ Final Checklist

Sebelum test lagi, pastikan:

- [ ] Flask API running di server production
- [ ] Port 5000 listening (`netstat -tlnp | grep 5000`)
- [ ] IP address device di database benar (127.0.0.1 atau IP LAN)
- [ ] Curl test berhasil: `curl http://127.0.0.1:5000/set_mode/masuk`
- [ ] Laravel log terbaca: `tail -f storage/logs/laravel.log`
- [ ] Controller sudah di-update (commit & push terbaru)
- [ ] Cache cleared: `php artisan config:clear`

---

## 🚀 Production Deployment Steps

**Complete deployment:**
```bash
# 1. Di local, commit & push
git add .
git commit -m "Fix: Improve Flask API connection for production"
git push origin main

# 2. SSH ke server
ssh user@dkvsmkn4jember.my.id

# 3. Pull latest code
cd /var/www/smk4-web  # Sesuaikan path
git pull origin main

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 5. Restart services
sudo systemctl restart flask-attendance
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

# 6. Test
curl http://127.0.0.1:5000/set_mode/masuk

# 7. Monitor log
tail -f storage/logs/laravel.log
```

---

## 📞 Need Help?

Jika masih error, jalankan diagnostic ini dan share outputnya:

```bash
#!/bin/bash
echo "=== Flask Diagnostic ==="
echo "1. Flask Process:"
ps aux | grep python

echo -e "\n2. Port 5000:"
sudo netstat -tlnp | grep 5000

echo -e "\n3. Curl Test:"
curl -v http://127.0.0.1:5000/

echo -e "\n4. Device IP from DB:"
mysql -u root -p -e "SELECT id, nama_device, ip_address FROM smk4_db.devices;"

echo -e "\n5. Laravel Log (last 20 lines):"
tail -20 /var/www/smk4-web/storage/logs/laravel.log

echo -e "\n6. Flask Service Status:"
sudo systemctl status flask-attendance
```

Save output dan bagikan untuk debugging lebih lanjut.
