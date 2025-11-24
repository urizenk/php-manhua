#!/bin/bash
# 服务器环境检查和修复脚本

echo "=========================================="
echo "PHP漫画管理系统 - 服务器环境检查和修复"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. 检查config.php是否存在
echo "1️⃣ 检查配置文件..."
if [ ! -f "/var/www/php-manhua/config/config.php" ]; then
    echo -e "${RED}✗ config.php不存在，正在从config.example.php创建...${NC}"
    cp /var/www/php-manhua/config/config.example.php /var/www/php-manhua/config/config.php
    echo -e "${GREEN}✓ 已创建config.php${NC}"
else
    echo -e "${GREEN}✓ config.php存在${NC}"
fi

# 2. 检查数据库连接
echo ""
echo "2️⃣ 检查数据库连接..."
DB_HOST="47.110.75.188"
DB_USER="root"
DB_PASS="123456"
DB_NAME="manhua_db"

if mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -e "USE $DB_NAME; SELECT 1;" &> /dev/null; then
    echo -e "${GREEN}✓ 数据库连接成功${NC}"
else
    echo -e "${RED}✗ 数据库连接失败${NC}"
    echo "请检查config.php中的数据库配置"
fi

# 3. 检查并创建uploads目录
echo ""
echo "3️⃣ 检查上传目录权限..."
UPLOAD_DIR="/var/www/php-manhua/public/uploads"

if [ ! -d "$UPLOAD_DIR" ]; then
    echo -e "${YELLOW}! uploads目录不存在，正在创建...${NC}"
    mkdir -p "$UPLOAD_DIR"
fi

# 创建子目录
mkdir -p "$UPLOAD_DIR/covers"
mkdir -p "$UPLOAD_DIR/chapters"
mkdir -p "$UPLOAD_DIR/temp"

# 设置权限
chmod -R 755 "$UPLOAD_DIR"
chown -R www-data:www-data "$UPLOAD_DIR"

echo -e "${GREEN}✓ uploads目录权限已设置${NC}"

# 4. 检查storage目录
echo ""
echo "4️⃣ 检查storage目录..."
STORAGE_DIR="/var/www/php-manhua/storage"

if [ ! -d "$STORAGE_DIR" ]; then
    mkdir -p "$STORAGE_DIR/logs"
    mkdir -p "$STORAGE_DIR/cache"
    mkdir -p "$STORAGE_DIR/sessions"
fi

chmod -R 755 "$STORAGE_DIR"
chown -R www-data:www-data "$STORAGE_DIR"

echo -e "${GREEN}✓ storage目录已创建${NC}"

# 5. 检查PHP-CGI是否运行
echo ""
echo "5️⃣ 检查PHP-CGI服务..."
if pgrep -f "php-cgi" > /dev/null; then
    echo -e "${GREEN}✓ PHP-CGI正在运行${NC}"
else
    echo -e "${YELLOW}! PHP-CGI未运行，正在启动...${NC}"
    killall -9 php-cgi 2>/dev/null
    /usr/local/php80/bin/php-cgi -b 127.0.0.1:9000 &
    sleep 2
    if pgrep -f "php-cgi" > /dev/null; then
        echo -e "${GREEN}✓ PHP-CGI已启动${NC}"
    else
        echo -e "${RED}✗ PHP-CGI启动失败${NC}"
    fi
fi

# 6. 检查Nginx服务
echo ""
echo "6️⃣ 检查Nginx服务..."
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx正在运行${NC}"
else
    echo -e "${YELLOW}! Nginx未运行，正在启动...${NC}"
    systemctl start nginx
    if systemctl is-active --quiet nginx; then
        echo -e "${GREEN}✓ Nginx已启动${NC}"
    else
        echo -e "${RED}✗ Nginx启动失败${NC}"
    fi
fi

# 7. 测试网站访问
echo ""
echo "7️⃣ 测试网站访问..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:9090/ | grep -q "200\|302"; then
    echo -e "${GREEN}✓ 网站可以正常访问${NC}"
else
    echo -e "${RED}✗ 网站访问异常${NC}"
    echo "请查看错误日志: sudo tail -50 /var/log/nginx/manhua_error.log"
fi

# 8. 检查文件权限
echo ""
echo "8️⃣ 检查关键文件权限..."
cd /var/www/php-manhua

# 确保所有PHP文件可读
find . -name "*.php" -exec chmod 644 {} \;

# 确保目录可执行
find . -type d -exec chmod 755 {} \;

echo -e "${GREEN}✓ 文件权限已修复${NC}"

# 9. 清理错误日志
echo ""
echo "9️⃣ 清理错误日志..."
if [ -f "/var/log/nginx/manhua_error.log" ]; then
    truncate -s 0 /var/log/nginx/manhua_error.log
    echo -e "${GREEN}✓ 错误日志已清空${NC}"
fi

# 10. 显示系统信息
echo ""
echo "=========================================="
echo "系统信息"
echo "=========================================="
echo "PHP版本: $(/usr/local/php80/bin/php -v | head -n 1)"
echo "Nginx版本: $(nginx -v 2>&1)"
echo "MySQL连接: mysql -h $DB_HOST -u $DB_USER"
echo ""
echo "访问地址:"
echo "  前台: http://8.149.138.212:9090/"
echo "  后台: http://8.149.138.212:9090/admin88/login.php"
echo "  默认账号: admin / admin123"
echo "  默认访问码: 1024"
echo ""
echo -e "${GREEN}=========================================="
echo "环境检查完成！"
echo "==========================================${NC}"
