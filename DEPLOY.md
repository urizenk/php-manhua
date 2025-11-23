# Ubuntu 20.04 部署指南

## 🚀 快速部署

### 方法1：一键部署脚本（推荐）

```bash
# 1. 克隆项目
git clone https://gitee.com/dot123dot/php-manhua.git
cd php-manhua

# 2. 运行部署脚本
sudo bash deploy.sh

# 3. 编辑配置文件
nano config/config.php
# 修改数据库密码为您的实际密码

# 4. 导入数据库
mysql -h 47.110.75.188 -u root -p -e "CREATE DATABASE IF NOT EXISTS manhua_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h 47.110.75.188 -u root -p manhua_db < database/schema.sql
mysql -h 47.110.75.188 -u root -p manhua_db < database/test_data.sql

# 5. 运行测试
chmod +x run-tests.sh
./run-tests.sh all

# 6. 访问网站
# 前台: http://your-server-ip/
# 后台: http://your-server-ip/admin88/login
```

---

## 📋 手动部署步骤

### 1. 安装 PHP 8.0

```bash
# 添加 PHP 仓库
sudo apt-get update
sudo apt-get install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update

# 安装 PHP 8.0 及扩展
sudo apt-get install -y \
    php8.0 \
    php8.0-cli \
    php8.0-fpm \
    php8.0-mysql \
    php8.0-pdo \
    php8.0-mbstring \
    php8.0-xml \
    php8.0-curl \
    php8.0-zip \
    php8.0-gd \
    php8.0-bcmath \
    php8.0-intl

# 验证安装
php -v
```

### 2. 安装 Composer

```bash
# 下载并安装 Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# 配置国内镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# 验证安装
composer --version
```

### 3. 安装 Nginx

```bash
# 安装 Nginx
sudo apt-get install -y nginx

# 启动 Nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# 检查状态
sudo systemctl status nginx
```

### 4. 安装 MySQL 客户端

```bash
sudo apt-get install -y mysql-client

# 测试连接
mysql -h 47.110.75.188 -u root -p -e "SELECT VERSION();"
```

### 5. 配置项目

```bash
# 克隆项目
git clone https://gitee.com/dot123dot/php-manhua.git
cd php-manhua

# 复制配置文件
cp config/config.example.php config/config.php

# 编辑配置文件
nano config/config.php
```

修改数据库配置：
```php
'database' => [
    'host'     => '47.110.75.188',
    'port'     => '3306',
    'dbname'   => 'manhua_db',
    'username' => 'root',
    'password' => 'your_actual_password',  // 修改为实际密码
],
```

### 6. 设置目录权限

```bash
# 创建必要目录
mkdir -p public/uploads
mkdir -p storage/logs

# 设置权限
sudo chmod -R 755 public/uploads
sudo chmod -R 755 storage

# 设置所有者（如果使用 www-data）
sudo chown -R www-data:www-data public/uploads
sudo chown -R www-data:www-data storage
```

### 7. 安装项目依赖

```bash
composer install
```

### 8. 配置 Nginx

创建配置文件：
```bash
sudo nano /etc/nginx/sites-available/php-manhua
```

添加以下内容：
```nginx
server {
    listen 80;
    server_name your-domain.com;  # 修改为您的域名或IP
    
    root /path/to/php-manhua/public;  # 修改为实际路径
    index index.php index.html;
    
    access_log /var/log/nginx/php-manhua-access.log;
    error_log /var/log/nginx/php-manhua-error.log;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(git|env|htaccess) {
        deny all;
    }
    
    location ~ /config/ {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

启用站点：
```bash
sudo ln -s /etc/nginx/sites-available/php-manhua /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 9. 导入数据库

```bash
# 创建数据库
mysql -h 47.110.75.188 -u root -p -e "CREATE DATABASE IF NOT EXISTS manhua_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 导入表结构
mysql -h 47.110.75.188 -u root -p manhua_db < database/schema.sql

# 导入测试数据
mysql -h 47.110.75.188 -u root -p manhua_db < database/test_data.sql

# 验证导入
mysql -h 47.110.75.188 -u root -p manhua_db -e "SHOW TABLES;"
```

### 10. 运行测试

```bash
# 添加执行权限
chmod +x run-tests.sh

# 运行所有测试
./run-tests.sh all

# 或运行特定测试
./run-tests.sh unit      # 单元测试
./run-tests.sh api       # API测试
./run-tests.sh coverage  # 代码覆盖率
```

---

## 🔧 故障排查

### 问题1：PHP-FPM 未运行
```bash
sudo systemctl start php8.0-fpm
sudo systemctl enable php8.0-fpm
```

### 问题2：权限问题
```bash
sudo chown -R www-data:www-data /path/to/php-manhua
sudo chmod -R 755 /path/to/php-manhua
```

### 问题3：数据库连接失败
```bash
# 检查防火墙
sudo ufw status
sudo ufw allow 3306/tcp

# 测试连接
mysql -h 47.110.75.188 -u root -p -e "SELECT 1;"
```

### 问题4：Nginx 502 错误
```bash
# 检查 PHP-FPM
sudo systemctl status php8.0-fpm

# 查看错误日志
sudo tail -f /var/log/nginx/php-manhua-error.log
```

---

## 📊 性能优化

### 1. 启用 OPcache
编辑 `/etc/php/8.0/fpm/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 2. 配置 PHP-FPM
编辑 `/etc/php/8.0/fpm/pool.d/www.conf`:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

### 3. 重启服务
```bash
sudo systemctl restart php8.0-fpm
sudo systemctl restart nginx
```

---

## 🔒 安全建议

1. **修改默认密码**
   - 管理员密码：admin/admin123
   - 访问码：1024

2. **配置防火墙**
   ```bash
   sudo ufw enable
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw allow 22/tcp
   ```

3. **配置 HTTPS**（推荐使用 Let's Encrypt）
   ```bash
   sudo apt-get install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d your-domain.com
   ```

4. **定期备份数据库**
   ```bash
   mysqldump -h 47.110.75.188 -u root -p manhua_db > backup_$(date +%Y%m%d).sql
   ```

---

## 📝 默认账号信息

- **管理员账号**: admin
- **管理员密码**: admin123
- **访问码**: 1024
- **数据库**: manhua_db

---

## 🆘 获取帮助

如遇到问题，请查看日志：
```bash
# Nginx 日志
sudo tail -f /var/log/nginx/php-manhua-error.log

# PHP-FPM 日志
sudo tail -f /var/log/php8.0-fpm.log

# 项目日志
tail -f storage/logs/app.log
```
