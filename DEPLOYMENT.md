# Panduan Deployment Sistem Penggajian STEA

## 🚀 Deployment ke Production

### Persyaratan Server
- **OS**: Ubuntu 20.04 LTS atau CentOS 8
- **Web Server**: Nginx atau Apache
- **PHP**: 8.1 atau lebih tinggi
- **Database**: MySQL 8.0 atau MariaDB 10.5
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 10GB SSD

### Instalasi Dependencies

#### Ubuntu/Debian
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-zip php8.1-mbstring php8.1-gd php8.1-intl

# Install MySQL
sudo apt install mysql-server

# Install Nginx
sudo apt install nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

#### CentOS/RHEL
```bash
# Update system
sudo yum update -y

# Install PHP 8.1
sudo yum install epel-release
sudo yum install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
sudo yum module enable php:remi-8.1
sudo yum install php php-fpm php-mysql php-xml php-curl php-zip php-mbstring php-gd php-intl

# Install MySQL
sudo yum install mysql-server

# Install Nginx
sudo yum install nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo yum install nodejs
```

### Database Setup

```sql
-- Create database
CREATE DATABASE payroll_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'payroll_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON payroll_system.* TO 'payroll_user'@'localhost';
FLUSH PRIVILEGES;
```

### Application Deployment

```bash
# Clone repository
git clone https://github.com/Maretume/stea.git /var/www/payroll
cd /var/www/payroll

# Set ownership
sudo chown -R www-data:www-data /var/www/payroll

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env
nano .env
```

### Environment Configuration (.env)

```env
APP_NAME="Sistem Penggajian STEA"
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://payroll.yourcompany.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payroll_system
DB_USERNAME=payroll_user
DB_PASSWORD=secure_password_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourcompany.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourcompany.com
MAIL_PASSWORD=mail_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourcompany.com"
MAIL_FROM_NAME="${APP_NAME}"

# Company Settings
COMPANY_NAME="PT. Your Company Name"
COMPANY_ADDRESS="Your Company Address"
COMPANY_PHONE="Your Company Phone"
COMPANY_EMAIL="info@yourcompany.com"
```

### Database Migration

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name payroll.yourcompany.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name payroll.yourcompany.com;
    root /var/www/payroll/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName payroll.yourcompany.com
    Redirect permanent / https://payroll.yourcompany.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName payroll.yourcompany.com
    DocumentRoot /var/www/payroll/public

    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key

    <Directory /var/www/payroll/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"

    # Compression
    LoadModule deflate_module modules/mod_deflate.so
    <Location />
        SetOutputFilter DEFLATE
        SetEnvIfNoCase Request_URI \
            \.(?:gif|jpe?g|png)$ no-gzip dont-vary
        SetEnvIfNoCase Request_URI \
            \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
    </Location>
</VirtualHost>
```

### Redis Setup

```bash
# Install Redis
sudo apt install redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf

# Set password
requirepass your_redis_password_here

# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

### Cron Jobs Setup

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/payroll && php artisan schedule:run >> /dev/null 2>&1

# Add backup job (daily at 2 AM)
0 2 * * * cd /var/www/payroll && php artisan backup:run >> /var/log/backup.log 2>&1
```

### File Permissions

```bash
# Set correct permissions
sudo chown -R www-data:www-data /var/www/payroll
sudo chmod -R 755 /var/www/payroll
sudo chmod -R 775 /var/www/payroll/storage
sudo chmod -R 775 /var/www/payroll/bootstrap/cache
sudo chmod -R 775 /var/www/payroll/public/uploads
```

### SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d payroll.yourcompany.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 🔒 Security Hardening

### Firewall Configuration

```bash
# UFW Firewall
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306 # MySQL (only if external access needed)
```

### Fail2Ban Setup

```bash
# Install Fail2Ban
sudo apt install fail2ban

# Configure
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true

[sshd]
enabled = true
```

### Database Security

```sql
-- Remove test database
DROP DATABASE IF EXISTS test;

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove remote root login
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Reload privileges
FLUSH PRIVILEGES;
```

## 📊 Monitoring & Logging

### Log Rotation

```bash
# Configure logrotate
sudo nano /etc/logrotate.d/laravel
```

```
/var/www/payroll/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### System Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Monitor processes
htop

# Monitor disk I/O
iotop

# Monitor network
nethogs
```

## 🔄 Backup Strategy

### Database Backup

```bash
#!/bin/bash
# backup-db.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/payroll"
DB_NAME="payroll_system"
DB_USER="payroll_user"
DB_PASS="secure_password_here"

mkdir -p $BACKUP_DIR

mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete
```

### File Backup

```bash
#!/bin/bash
# backup-files.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/payroll"
APP_DIR="/var/www/payroll"

mkdir -p $BACKUP_DIR

tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    $APP_DIR

# Keep only last 7 days
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +7 -delete
```

## 🚀 Performance Optimization

### PHP-FPM Tuning

```ini
; /etc/php/8.1/fpm/pool.d/www.conf

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; PHP settings
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M
```

### MySQL Tuning

```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf

[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 128M
max_connections = 200
```

### Redis Configuration

```
# /etc/redis/redis.conf

maxmemory 512mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## 📈 Scaling Considerations

### Load Balancer Setup

```nginx
upstream payroll_backend {
    server 192.168.1.10:80;
    server 192.168.1.11:80;
    server 192.168.1.12:80;
}

server {
    listen 80;
    server_name payroll.yourcompany.com;
    
    location / {
        proxy_pass http://payroll_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

### Database Replication

```sql
-- Master configuration
[mysqld]
server-id = 1
log-bin = mysql-bin
binlog-do-db = payroll_system

-- Slave configuration
[mysqld]
server-id = 2
relay-log = mysql-relay-bin
log-slave-updates = 1
read-only = 1
```

## 🔧 Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/payroll
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Database Connection**
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

3. **Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Queue Issues**
   ```bash
   php artisan queue:restart
   php artisan queue:work --daemon
   ```

### Log Locations

- **Application Logs**: `/var/www/payroll/storage/logs/`
- **Nginx Logs**: `/var/log/nginx/`
- **PHP-FPM Logs**: `/var/log/php8.1-fpm.log`
- **MySQL Logs**: `/var/log/mysql/`

---

**Deployment Guide** - Panduan lengkap untuk deploy Sistem Penggajian STEA ke production server.
