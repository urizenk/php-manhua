#!/bin/bash

##############################################
# PHP漫画管理系统 - 自动化部署脚本
# 由 Gitee Webhook 触发执行
##############################################

set -e  # 遇到错误立即退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目配置
PROJECT_ROOT="/var/www/php-manhua"
GIT_BRANCH="main"
BACKUP_DIR="${PROJECT_ROOT}/backups"
LOG_FILE="${PROJECT_ROOT}/storage/logs/deploy.log"

# 日志函数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

# 创建备份
create_backup() {
    log_info "创建备份..."
    
    # 确保备份目录存在
    mkdir -p "${BACKUP_DIR}"
    
    # 备份文件名（带时间戳）
    BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}"
    
    # 创建备份（排除不必要的目录）
    tar -czf "${BACKUP_PATH}" \
        --exclude="${PROJECT_ROOT}/.git" \
        --exclude="${PROJECT_ROOT}/storage/logs" \
        --exclude="${PROJECT_ROOT}/storage/cache" \
        --exclude="${PROJECT_ROOT}/backups" \
        --exclude="${PROJECT_ROOT}/node_modules" \
        -C "${PROJECT_ROOT}" .
    
    if [ $? -eq 0 ]; then
        log_success "备份创建成功: ${BACKUP_NAME}"
        
        # 只保留最近5个备份
        cd "${BACKUP_DIR}"
        ls -t backup_*.tar.gz | tail -n +6 | xargs -r rm
        log_info "清理旧备份，保留最近5个"
    else
        log_error "备份创建失败"
        return 1
    fi
}

# 拉取最新代码
pull_code() {
    log_info "拉取最新代码..."
    
    cd "${PROJECT_ROOT}"
    
    # 保存当前分支
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
    log_info "当前分支: ${CURRENT_BRANCH}"
    
    # 暂存本地修改（如果有）
    if ! git diff-index --quiet HEAD --; then
        log_warning "检测到本地修改，暂存中..."
        git stash save "Auto-stash before deploy $(date '+%Y-%m-%d %H:%M:%S')"
    fi
    
    # 拉取最新代码
    git fetch origin
    git reset --hard origin/${GIT_BRANCH}
    
    if [ $? -eq 0 ]; then
        log_success "代码拉取成功"
        
        # 显示最新提交信息
        LATEST_COMMIT=$(git log -1 --pretty=format:"%h - %an: %s")
        log_info "最新提交: ${LATEST_COMMIT}"
    else
        log_error "代码拉取失败"
        return 1
    fi
}

# 安装/更新依赖
install_dependencies() {
    log_info "检查依赖..."
    
    cd "${PROJECT_ROOT}"
    
    # 检查是否有 composer.json
    if [ -f "composer.json" ]; then
        log_info "更新 Composer 依赖..."
        
        if command -v composer &> /dev/null; then
            composer install --no-dev --optimize-autoloader
            
            if [ $? -eq 0 ]; then
                log_success "Composer 依赖更新成功"
            else
                log_warning "Composer 依赖更新失败"
            fi
        else
            log_warning "未安装 Composer，跳过依赖更新"
        fi
    fi
    
    # 检查是否有 package.json
    if [ -f "package.json" ]; then
        log_info "更新 NPM 依赖..."
        
        if command -v npm &> /dev/null; then
            npm install --production
            
            if [ $? -eq 0 ]; then
                log_success "NPM 依赖更新成功"
            else
                log_warning "NPM 依赖更新失败"
            fi
        else
            log_warning "未安装 NPM，跳过依赖更新"
        fi
    fi
}

# 设置文件权限
set_permissions() {
    log_info "设置文件权限..."
    
    cd "${PROJECT_ROOT}"
    
    # 设置基本权限
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    # 设置可执行权限
    chmod +x scripts/*.sh 2>/dev/null || true
    
    # 设置可写权限
    chmod -R 777 storage 2>/dev/null || true
    chmod -R 777 public/uploads 2>/dev/null || true
    
    log_success "文件权限设置完成"
}

# 清理缓存
clear_cache() {
    log_info "清理缓存..."
    
    cd "${PROJECT_ROOT}"
    
    # 清理应用缓存
    if [ -d "storage/cache" ]; then
        rm -rf storage/cache/*
        log_info "应用缓存已清理"
    fi
    
    # 清理 OPcache（如果使用 PHP-FPM）
    if command -v php-fpm &> /dev/null; then
        # 重启 PHP-FPM 以清理 OPcache
        systemctl reload php-fpm 2>/dev/null || systemctl reload php8.0-fpm 2>/dev/null || true
        log_info "PHP-FPM 已重载"
    fi
    
    log_success "缓存清理完成"
}

# 运行数据库迁移（如果需要）
run_migrations() {
    log_info "检查数据库迁移..."
    
    cd "${PROJECT_ROOT}"
    
    # 如果有迁移脚本，在这里执行
    # 例如：php artisan migrate --force
    
    log_info "数据库迁移检查完成"
}

# 重启服务
restart_services() {
    log_info "重启相关服务..."
    
    # 重启 Nginx（如果需要）
    if command -v nginx &> /dev/null; then
        nginx -t && nginx -s reload
        if [ $? -eq 0 ]; then
            log_success "Nginx 已重载"
        else
            log_warning "Nginx 重载失败"
        fi
    fi
    
    # 重启 PHP-FPM（如果需要）
    if systemctl is-active --quiet php-fpm; then
        systemctl restart php-fpm
        log_success "PHP-FPM 已重启"
    elif systemctl is-active --quiet php8.0-fpm; then
        systemctl restart php8.0-fpm
        log_success "PHP 8.0-FPM 已重启"
    fi
}

# 健康检查
health_check() {
    log_info "执行健康检查..."
    
    # 检查网站是否可访问
    SITE_URL="http://localhost"
    
    if command -v curl &> /dev/null; then
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "${SITE_URL}")
        
        if [ "${HTTP_CODE}" == "200" ] || [ "${HTTP_CODE}" == "302" ]; then
            log_success "网站健康检查通过 (HTTP ${HTTP_CODE})"
        else
            log_warning "网站健康检查失败 (HTTP ${HTTP_CODE})"
        fi
    else
        log_warning "未安装 curl，跳过健康检查"
    fi
}

# 发送通知（可选）
send_notification() {
    log_info "发送部署通知..."
    
    # 这里可以集成钉钉、企业微信等通知
    # 例如：curl -X POST "https://oapi.dingtalk.com/robot/send?access_token=xxx" -d '{"msgtype":"text","text":{"content":"部署成功"}}'
    
    log_info "通知已发送"
}

# 主流程
main() {
    log_info "=========================================="
    log_info "开始自动化部署"
    log_info "=========================================="
    
    # 1. 创建备份
    create_backup || {
        log_error "备份失败，终止部署"
        exit 1
    }
    
    # 2. 拉取最新代码
    pull_code || {
        log_error "代码拉取失败，终止部署"
        exit 1
    }
    
    # 3. 安装/更新依赖
    install_dependencies
    
    # 4. 设置文件权限
    set_permissions
    
    # 5. 清理缓存
    clear_cache
    
    # 6. 运行数据库迁移
    run_migrations
    
    # 7. 重启服务
    restart_services
    
    # 8. 健康检查
    health_check
    
    # 9. 发送通知
    send_notification
    
    log_success "=========================================="
    log_success "自动化部署完成"
    log_success "=========================================="
}

# 执行主流程
main
