# 🚀 快速部署指南（密码：123456）

## 📋 部署步骤

### 第1步：MySQL服务器配置（47.110.75.188）

**在MySQL服务器上执行以下命令：**

```bash
# 1. 登录MySQL并创建用户
mysql -u root -p << 'EOF'
-- 创建数据库
CREATE DATABASE IF NOT EXISTS manhua_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建专用用户（推荐）
CREATE USER 'manhua_user'@'%' IDENTIFIED BY '123456';
GRANT ALL PRIVILEGES ON manhua_db.* TO 'manhua_user'@'%';

-- 或者允许root远程访问
GRANT ALL PRIVILEGES ON manhua_db.* TO 'root'@'%' IDENTIFIED BY '123456';

-- 刷新权限
FLUSH PRIVILEGES;

-- 查看用户
SELECT user, host FROM mysql.user WHERE user IN ('root', 'manhua_user');
EXIT;
EOF

# 2. 修改MySQL配置允许远程连接
sudo sed -i 's/bind-address.*/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf

# 3. 重启MySQL
sudo systemctl restart mysql

# 4. 开放防火墙（如果有）
sudo ufw allow 3306/tcp

# 5. 验证MySQL监听
sudo netstat -tlnp | grep 3306
```

---

### 第2步：应用服务器部署（8.149.138.212）

**复制以下完整命令到应用服务器执行：**

```bash
# ========================================
# 一键部署脚本
# ========================================

cd ~/php-manhua

# 1. 拉取最新代码
git pull origin master

# 2. 创建配置文件
cat > config/config.php << 'EOF'
<?php
return [
    // 数据库配置
    'database' => [
        'host'     => '47.110.75.188',
        'port'     => '3306',
        'database' => 'manhua_db',
        'username' => 'root',
        'password' => '123456',
        'charset'  => 'utf8mb4',
    ],
    
    // 应用配置
    'app' => [
        'name'     => '海の小窝',
        'url'      => 'http://localhost',
        'timezone' => 'Asia/Shanghai',
        'debug'    => false,
    ],
    
    // Session配置
    'session' => [
        'name'                    => 'MANHUA_SESSION',
        'lifetime'                => 7200,
        'cookie_httponly'         => true,
        'cookie_secure'           => false,
        'cookie_samesite'         => 'Strict',
        'use_strict_mode'         => true,
        'sid_length'              => 48,
        'sid_bits_per_character'  => 6,
    ],
    
    // 文件上传配置
    'upload' => [
        'max_size'      => 5242880,
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'save_path'     => '/public/uploads/',
    ],
];
EOF

# 3. 测试数据库连接
echo "🔌 测试数据库连接..."
php -r "
\$config = require 'config/config.php';
try {
    \$pdo = new PDO(
        \"mysql:host={\$config['database']['host']};dbname={\$config['database']['database']};charset=utf8mb4\",
        \$config['database']['username'],
        \$config['database']['password']
    );
    echo \"✅ 数据库连接成功！\n\";
    
    // 测试查询
    \$stmt = \$pdo->query(\"SELECT COUNT(*) as count FROM mangas\");
    \$result = \$stmt->fetch(PDO::FETCH_ASSOC);
    echo \"✅ 漫画数量: \" . \$result['count'] . \"\n\";
} catch (PDOException \$e) {
    echo \"❌ 数据库连接失败: \" . \$e->getMessage() . \"\n\";
    echo \"请检查MySQL服务器配置！\n\";
    exit(1);
}
"

# 4. 设置权限
echo "🔒 设置目录权限..."
sudo chmod -R 755 public/uploads
sudo chown -R www-data:www-data public/uploads
sudo chmod 644 config/config.php

# 5. 配置Nginx
echo "🌐 配置Nginx..."
sudo tee /etc/nginx/sites-available/php-manhua > /dev/null << 'NGINX_EOF'
server {
    listen 80;
    server_name _;
    root /root/php-manhua/public;
    index index.php;

    access_log /var/log/nginx/manhua_access.log;
    error_log /var/log/nginx/manhua_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /admin88 {
        try_files $uri $uri/ /admin88/index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

    location ~* ^/config/ {
        deny all;
    }
}
NGINX_EOF

# 6. 启用站点
sudo ln -sf /etc/nginx/sites-available/php-manhua /etc/nginx/sites-enabled/

# 7. 测试Nginx配置
echo "🔍 测试Nginx配置..."
sudo nginx -t

# 8. 重启服务
echo "🔄 重启服务..."
sudo systemctl restart nginx
sudo systemctl restart php8.0-fpm

# 9. 检查服务状态
echo "✅ 检查服务状态..."
echo "Nginx:"
sudo systemctl status nginx --no-pager | grep "Active:"
echo ""
echo "PHP-FPM:"
sudo systemctl status php8.0-fpm --no-pager | grep "Active:"

# 10. 显示访问地址
echo ""
echo "========================================="
echo "✅ 部署完成！"
echo "========================================="
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}')
echo ""
echo "🌐 访问地址:"
echo "   前台: http://$SERVER_IP/"
echo "   后台: http://$SERVER_IP/admin88/"
echo ""
echo "🔑 默认账号:"
echo "   管理员: admin"
echo "   密码: admin123"
echo "   访问码: 1024"
echo ""
echo "📋 数据库信息:"
echo "   地址: 47.110.75.188:3306"
echo "   数据库: manhua_db"
echo "   用户: root"
echo "   密码: 123456"
echo ""
echo "========================================="
```

---

## 🎯 超级简化版（复制粘贴即可）

### MySQL服务器（47.110.75.188）

```bash
mysql -u root -p -e "
CREATE DATABASE IF NOT EXISTS manhua_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON manhua_db.* TO 'root'@'%' IDENTIFIED BY '123456';
FLUSH PRIVILEGES;
"

sudo sed -i 's/bind-address.*/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql
sudo ufw allow 3306/tcp
```

### 应用服务器（8.149.138.212）

```bash
cd ~/php-manhua && git pull origin master

cat > config/config.php << 'EOF'
<?php
return [
    'database' => ['host' => '47.110.75.188', 'port' => '3306', 'database' => 'manhua_db', 'username' => 'root', 'password' => '123456', 'charset' => 'utf8mb4'],
    'app' => ['name' => '海の小窝', 'url' => 'http://localhost', 'timezone' => 'Asia/Shanghai', 'debug' => false],
    'session' => ['name' => 'MANHUA_SESSION', 'lifetime' => 7200, 'cookie_httponly' => true, 'cookie_secure' => false, 'cookie_samesite' => 'Strict', 'use_strict_mode' => true, 'sid_length' => 48, 'sid_bits_per_character' => 6],
    'upload' => ['max_size' => 5242880, 'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'save_path' => '/public/uploads/'],
];
EOF

php -r "\$c=require 'config/config.php';\$p=new PDO(\"mysql:host={\$c['database']['host']};dbname={\$c['database']['database']}\",\$c['database']['username'],\$c['database']['password']);echo '✅ 数据库连接成功！';"

sudo chmod -R 755 public/uploads && sudo chown -R www-data:www-data public/uploads && sudo chmod 644 config/config.php

sudo tee /etc/nginx/sites-available/php-manhua > /dev/null << 'EOF'
server {
    listen 80;
    server_name _;
    root /root/php-manhua/public;
    index index.php;
    access_log /var/log/nginx/manhua_access.log;
    error_log /var/log/nginx/manhua_error.log;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location /admin88 { try_files $uri $uri/ /admin88/index.php?$query_string; }
    location ~ \.php$ { fastcgi_pass unix:/var/run/php/php8.0-fpm.sock; fastcgi_index index.php; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; }
    location ~ /\. { deny all; }
    location ~* ^/config/ { deny all; }
}
EOF

sudo ln -sf /etc/nginx/sites-available/php-manhua /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx && sudo systemctl restart php8.0-fpm

echo "✅ 部署完成！访问: http://$(curl -s ifconfig.me)/"
```

---

## 📝 部署顺序

1. **先在MySQL服务器（47.110.75.188）执行MySQL配置命令**
2. **再在应用服务器（8.149.138.212）执行部署命令**
3. **访问网站测试**

---

## 🐛 如果遇到问题

### 数据库连接失败
```bash
# 测试MySQL连接
mysql -h 47.110.75.188 -u root -p123456 manhua_db

# 检查MySQL是否监听
telnet 47.110.75.188 3306
```

### 页面无法访问
```bash
# 查看错误日志
sudo tail -f /var/log/nginx/manhua_error.log
sudo tail -f /var/log/php8.0-fpm.log
```

---

**现在可以开始部署了！先在MySQL服务器执行授权，再在应用服务器执行部署命令。**
