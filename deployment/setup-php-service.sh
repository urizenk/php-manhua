#!/bin/bash
# PHP-CGI Systemd服务安装脚本

echo "=========================================="
echo "PHP-CGI Systemd 服务安装"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. 停止现有的PHP-CGI进程
echo "1️⃣ 停止现有PHP-CGI进程..."
killall -9 php-cgi 2>/dev/null
echo -e "${GREEN}✓ 已停止现有进程${NC}"

# 2. 复制service文件到systemd目录
echo ""
echo "2️⃣ 安装systemd服务文件..."
sudo cp /var/www/php-manhua/deployment/php-cgi.service /etc/systemd/system/
sudo chmod 644 /etc/systemd/system/php-cgi.service
echo -e "${GREEN}✓ 服务文件已安装${NC}"

# 3. 重新加载systemd
echo ""
echo "3️⃣ 重新加载systemd配置..."
sudo systemctl daemon-reload
echo -e "${GREEN}✓ systemd配置已重新加载${NC}"

# 4. 启用服务（开机自启动）
echo ""
echo "4️⃣ 启用PHP-CGI服务..."
sudo systemctl enable php-cgi.service
echo -e "${GREEN}✓ 服务已设置为开机自启动${NC}"

# 5. 启动服务
echo ""
echo "5️⃣ 启动PHP-CGI服务..."
sudo systemctl start php-cgi.service
sleep 2
echo -e "${GREEN}✓ 服务已启动${NC}"

# 6. 检查服务状态
echo ""
echo "6️⃣ 检查服务状态..."
if systemctl is-active --quiet php-cgi.service; then
    echo -e "${GREEN}✓ PHP-CGI服务运行正常${NC}"
else
    echo -e "${RED}✗ PHP-CGI服务启动失败${NC}"
    echo "查看错误详情："
    sudo systemctl status php-cgi.service
    exit 1
fi

# 7. 检查端口监听
echo ""
echo "7️⃣ 检查端口监听..."
if netstat -tlnp 2>/dev/null | grep -q ":9000"; then
    echo -e "${GREEN}✓ 端口9000监听正常${NC}"
else
    echo -e "${RED}✗ 端口9000未监听${NC}"
    exit 1
fi

# 8. 重启Nginx
echo ""
echo "8️⃣ 重启Nginx..."
sudo systemctl restart nginx
echo -e "${GREEN}✓ Nginx已重启${NC}"

# 9. 显示服务信息
echo ""
echo "=========================================="
echo "安装完成！"
echo "=========================================="
echo ""
echo "服务管理命令："
echo "  启动服务: sudo systemctl start php-cgi"
echo "  停止服务: sudo systemctl stop php-cgi"
echo "  重启服务: sudo systemctl restart php-cgi"
echo "  查看状态: sudo systemctl status php-cgi"
echo "  查看日志: sudo journalctl -u php-cgi -f"
echo ""
echo "访问地址："
echo "  前台: http://8.149.138.212:9090/"
echo "  后台: http://8.149.138.212:9090/admin88/"
echo ""
echo -e "${GREEN}✅ PHP-CGI现在会在后台持续运行，SSH断开也不会停止！${NC}"
