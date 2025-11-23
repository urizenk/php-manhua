#!/bin/bash

# ========================================
# PHP漫画管理系统 - 完整部署脚本
# 适用于 Ubuntu 20.04 + PHP 8.0 + Nginx + MySQL
# ========================================

set -e  # 遇到错误立即退出

echo "========================================="
echo "🚀 PHP漫画管理系统 - 完整部署"
echo "========================================="

# 项目目录
PROJECT_DIR="$HOME/php-manhua"
NGINX_SITE="php-manhua"

# ========================================
# 第1步：拉取最新代码
# ========================================
echo ""
echo "📥 [1/8] 拉取最新代码..."
cd $PROJECT_DIR || exit
git pull origin master

# ========================================
# 第2步：配置数据库连接
# ========================================
echo ""
echo "⚙️  [2/8] 检查配置文件..."
if [ ! -f "config/config.php" ]; then
    echo "⚠️  config.php 不存在，从示例复制..."
    cp config/config.example.php config/config.php
    echo ""
    echo "❗❗❗ 重要：请立即编辑 config/config.php 配置数据库连接 ❗❗❗"
    echo ""
    echo "执行以下命令编辑配置："
    echo "  nano $PROJECT_DIR/config/config.php"
    echo ""
    echo "需要修改的内容："
    echo "  'host'     => '47.110.75.188',  // 数据库地址"
    echo "  'username' => '你的用户名',"
    echo "  'password' => '你的密码',"
    echo "  'database' => 'manhua_db',"
    echo ""
    read -p "配置完成后按回车继续..."
else
    echo "✅ config.php 已存在"
fi

# ========================================
# 第3步：测试数据库连接
# ========================================
echo ""
echo "🔌 [3/8] 测试数据库连接..."
php -r "
\$config = require '$PROJECT_DIR/config/config.php';
try {
    \$pdo = new PDO(
        \"mysql:host={\$config['database']['host']};dbname={\$config['database']['database']};charset=utf8mb4\",
        \$config['database']['username'],
        \$config['database']['password']
    );
    echo \"✅ 数据库连接成功！\n\";
    \$stmt = \$pdo->query(\"SELECT COUNT(*) as count FROM mangas\");
    \$result = \$stmt->fetch(PDO::FETCH_ASSOC);
    echo \"✅ 漫画数量: \" . \$result['count'] . \"\n\";
} catch (PDOException \$e) {
    echo \"❌ 数据库连接失败: \" . \$e->getMessage() . \"\n\";
    exit(1);
}
"

# ========================================
# 第4步：设置目录权限
# ========================================
echo ""
echo "🔒 [4/8] 设置目录权限..."
sudo chmod -R 755 public/uploads
sudo chown -R www-data:www-data public/uploads
sudo chmod 644 config/config.php
echo "✅ 权限设置完成"

# ========================================
# 第5步：配置Nginx（如果需要）
# ========================================
echo ""
echo "🌐 [5/8] 检查Nginx配置..."
if [ ! -f "/etc/nginx/sites-available/$NGINX_SITE" ]; then
    echo "⚠️  Nginx配置不存在，正在创建..."
    
    sudo tee /etc/nginx/sites-available/$NGINX_SITE > /dev/null <<'EOF'
server {
    listen 80;
    server_name _;
    root /root/php-manhua/public;
    index index.php;

    # 日志
    access_log /var/log/nginx/manhua_access.log;
    error_log /var/log/nginx/manhua_error.log;

    # 主路由
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 后台路由
    location /admin88 {
        try_files $uri $uri/ /admin88/index.php?$query_string;
    }

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }

    # 禁止访问配置文件
    location ~* ^/config/ {
        deny all;
    }
}
EOF

    # 启用站点
    sudo ln -sf /etc/nginx/sites-available/$NGINX_SITE /etc/nginx/sites-enabled/
    echo "✅ Nginx配置已创建"
else
    echo "✅ Nginx配置已存在"
fi

# 测试Nginx配置
echo "🔍 测试Nginx配置..."
sudo nginx -t

# ========================================
# 第6步：确保PHP-FPM已安装
# ========================================
echo ""
echo "🐘 [6/8] 检查PHP-FPM..."
if ! systemctl is-active --quiet php8.0-fpm; then
    echo "⚠️  PHP-FPM未运行，正在启动..."
    sudo systemctl start php8.0-fpm
    sudo systemctl enable php8.0-fpm
fi
echo "✅ PHP-FPM运行正常"

# ========================================
# 第7步：重启服务
# ========================================
echo ""
echo "🔄 [7/8] 重启服务..."
sudo systemctl restart nginx
sudo systemctl restart php8.0-fpm
echo "✅ 服务重启完成"

# ========================================
# 第8步：检查服务状态
# ========================================
echo ""
echo "✅ [8/8] 检查服务状态..."
echo "Nginx状态:"
sudo systemctl status nginx --no-pager | grep "Active:" || echo "❌ Nginx未运行"
echo ""
echo "PHP-FPM状态:"
sudo systemctl status php8.0-fpm --no-pager | grep "Active:" || echo "❌ PHP-FPM未运行"

# ========================================
# 完成
# ========================================
echo ""
echo "========================================="
echo "✅ 部署完成！"
echo "========================================="
echo ""
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || echo "your-server-ip")
echo "🌐 访问地址:"
echo "   前台: http://$SERVER_IP/"
echo "   后台: http://$SERVER_IP/admin88/"
echo ""
echo "🔑 默认账号:"
echo "   管理员账号: admin"
echo "   管理员密码: admin123"
echo "   访问码: 1024"
echo ""
echo "📋 日志位置:"
echo "   Nginx访问日志: /var/log/nginx/manhua_access.log"
echo "   Nginx错误日志: /var/log/nginx/manhua_error.log"
echo "   PHP-FPM日志: /var/log/php8.0-fpm.log"
echo ""
echo "🔧 常用命令:"
echo "   查看Nginx日志: sudo tail -f /var/log/nginx/manhua_error.log"
echo "   查看PHP日志: sudo tail -f /var/log/php8.0-fpm.log"
echo "   重启Nginx: sudo systemctl restart nginx"
echo "   重启PHP-FPM: sudo systemctl restart php8.0-fpm"
echo ""
echo "========================================="
