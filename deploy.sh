#!/bin/bash

##############################################
# PHP漫画管理系统 - Ubuntu 20.04 部署脚本
# 用法：sudo bash deploy.sh
##############################################

set -e  # 遇到错误立即退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查是否为 root 用户
check_root() {
    if [ "$EUID" -ne 0 ]; then 
        log_error "请使用 sudo 运行此脚本"
        exit 1
    fi
}

# 更新系统
update_system() {
    log_info "更新系统软件包..."
    apt-get update -y
    log_success "系统更新完成"
}

# 安装 PHP 8.0 及扩展
install_php() {
    log_info "检查 PHP 安装..."
    
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
        log_info "已安装 PHP $PHP_VERSION"
        
        if [ "$PHP_VERSION" != "8.0" ] && [ "$PHP_VERSION" != "8.1" ] && [ "$PHP_VERSION" != "8.2" ]; then
            log_warning "PHP 版本不匹配，建议使用 PHP 8.0+"
        fi
    else
        log_info "安装 PHP 8.0 及扩展..."
        
        # 添加 PHP 仓库
        apt-get install -y software-properties-common
        add-apt-repository -y ppa:ondrej/php
        apt-get update -y
        
        # 安装 PHP 8.0 及必要扩展
        apt-get install -y \
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
        
        log_success "PHP 8.0 安装完成"
    fi
    
    # 显示 PHP 版本
    php -v
}

# 安装 Composer
install_composer() {
    log_info "检查 Composer 安装..."
    
    if command -v composer &> /dev/null; then
        log_info "Composer 已安装: $(composer --version)"
    else
        log_info "安装 Composer..."
        
        # 下载 Composer 安装脚本
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        
        # 安装 Composer
        php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        
        # 清理
        php -r "unlink('composer-setup.php');"
        
        log_success "Composer 安装完成"
    fi
    
    # 配置国内镜像
    log_info "配置 Composer 国内镜像..."
    composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
    log_success "Composer 镜像配置完成"
}

# 安装 Nginx
install_nginx() {
    log_info "检查 Nginx 安装..."
    
    if command -v nginx &> /dev/null; then
        log_info "Nginx 已安装: $(nginx -v 2>&1)"
    else
        log_info "安装 Nginx..."
        apt-get install -y nginx
        systemctl enable nginx
        systemctl start nginx
        log_success "Nginx 安装完成"
    fi
}

# 安装 MySQL 客户端
install_mysql_client() {
    log_info "检查 MySQL 客户端..."
    
    if command -v mysql &> /dev/null; then
        log_info "MySQL 客户端已安装"
    else
        log_info "安装 MySQL 客户端..."
        apt-get install -y mysql-client
        log_success "MySQL 客户端安装完成"
    fi
}

# 配置项目
setup_project() {
    log_info "配置项目..."
    
    # 获取当前目录
    PROJECT_DIR=$(pwd)
    log_info "项目目录: $PROJECT_DIR"
    
    # 复制配置文件
    if [ ! -f "$PROJECT_DIR/config/config.php" ]; then
        log_info "创建配置文件..."
        cp "$PROJECT_DIR/config/config.example.php" "$PROJECT_DIR/config/config.php"
        log_success "配置文件已创建，请编辑 config/config.php 设置数据库密码"
    else
        log_info "配置文件已存在"
    fi
    
    # 设置目录权限
    log_info "设置目录权限..."
    mkdir -p "$PROJECT_DIR/public/uploads"
    mkdir -p "$PROJECT_DIR/storage/logs"
    chmod -R 755 "$PROJECT_DIR/public/uploads"
    chmod -R 755 "$PROJECT_DIR/storage"
    
    # 设置所有者（如果使用 www-data 用户）
    if id "www-data" &>/dev/null; then
        chown -R www-data:www-data "$PROJECT_DIR/public/uploads"
        chown -R www-data:www-data "$PROJECT_DIR/storage"
        log_success "目录权限设置完成（www-data）"
    else
        log_warning "未找到 www-data 用户，跳过所有者设置"
    fi
}

# 安装项目依赖
install_dependencies() {
    log_info "安装项目依赖..."
    
    PROJECT_DIR=$(pwd)
    cd "$PROJECT_DIR"
    
    # 安装 Composer 依赖
    composer install --no-dev --optimize-autoloader
    
    log_success "项目依赖安装完成"
}

# 配置 Nginx
configure_nginx() {
    log_info "配置 Nginx..."
    
    PROJECT_DIR=$(pwd)
    NGINX_CONF="/etc/nginx/sites-available/php-manhua"
    
    # 创建 Nginx 配置文件
    cat > "$NGINX_CONF" << EOF
server {
    listen 80;
    server_name localhost;
    
    root $PROJECT_DIR/public;
    index index.php index.html;
    
    # 日志
    access_log /var/log/nginx/php-manhua-access.log;
    error_log /var/log/nginx/php-manhua-error.log;
    
    # 伪静态规则
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # 禁止访问敏感文件
    location ~ /\.(git|env|htaccess) {
        deny all;
    }
    
    location ~ /config/ {
        deny all;
    }
    
    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
EOF
    
    # 启用站点
    ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/
    
    # 测试 Nginx 配置
    nginx -t
    
    # 重启 Nginx
    systemctl restart nginx
    
    log_success "Nginx 配置完成"
}

# ========================================
# 主函数
# ========================================
main() {
    echo "========================================="
    echo "  PHP漫画管理系统 - Ubuntu 20.04 部署"
    echo "========================================="
    echo ""
    
    check_root
    update_system
    install_php
    install_composer
    install_nginx
    install_mysql_client
    setup_project
    install_dependencies
    configure_nginx
    show_completion
}

# 执行主函数
main
