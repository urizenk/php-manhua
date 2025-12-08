#!/bin/bash

##############################################
# CI/CD 快速配置脚本
# 用于在服务器上快速设置 Webhook 自动部署
##############################################

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  PHP漫画管理系统 - CI/CD 配置向导${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# 获取项目根目录
read -p "请输入项目根目录 [默认: /www/wwwroot/php-manhua]: " PROJECT_ROOT
PROJECT_ROOT=${PROJECT_ROOT:-/www/wwwroot/php-manhua}

# 获取 Git 分支
read -p "请输入 Git 分支 [默认: master]: " GIT_BRANCH
GIT_BRANCH=${GIT_BRANCH:-master}

# 生成 Webhook 密钥
echo -e "\n${YELLOW}正在生成 Webhook 密钥...${NC}"
WEBHOOK_SECRET=$(openssl rand -hex 32)
echo -e "${GREEN}生成的密钥: ${WEBHOOK_SECRET}${NC}"
echo -e "${YELLOW}请保存此密钥，稍后需要在 Gitee 中配置${NC}"

# 创建必要的目录
echo -e "\n${BLUE}创建必要的目录...${NC}"
mkdir -p "${PROJECT_ROOT}/storage/logs"
mkdir -p "${PROJECT_ROOT}/backups"
mkdir -p "${PROJECT_ROOT}/public/uploads"
mkdir -p "${PROJECT_ROOT}/scripts"

# 设置权限
echo -e "${BLUE}设置目录权限...${NC}"
chmod -R 777 "${PROJECT_ROOT}/storage/logs"
chmod -R 755 "${PROJECT_ROOT}/backups"
chmod -R 777 "${PROJECT_ROOT}/public/uploads"

# 配置 webhook.php
echo -e "\n${BLUE}配置 webhook.php...${NC}"
if [ -f "${PROJECT_ROOT}/public/webhook.php" ]; then
    # 更新配置
    sed -i "s|define('WEBHOOK_SECRET', '.*')|define('WEBHOOK_SECRET', '${WEBHOOK_SECRET}')|" "${PROJECT_ROOT}/public/webhook.php"
    sed -i "s|define('PROJECT_ROOT', '.*')|define('PROJECT_ROOT', '${PROJECT_ROOT}')|" "${PROJECT_ROOT}/public/webhook.php"
    sed -i "s|define('GIT_BRANCH', '.*')|define('GIT_BRANCH', '${GIT_BRANCH}')|" "${PROJECT_ROOT}/public/webhook.php"
    echo -e "${GREEN}webhook.php 配置完成${NC}"
else
    echo -e "${RED}错误: webhook.php 不存在${NC}"
    exit 1
fi

# 配置 auto-deploy.sh
echo -e "\n${BLUE}配置 auto-deploy.sh...${NC}"
if [ -f "${PROJECT_ROOT}/scripts/auto-deploy.sh" ]; then
    # 更新配置
    sed -i "s|PROJECT_ROOT=\".*\"|PROJECT_ROOT=\"${PROJECT_ROOT}\"|" "${PROJECT_ROOT}/scripts/auto-deploy.sh"
    sed -i "s|GIT_BRANCH=\".*\"|GIT_BRANCH=\"${GIT_BRANCH}\"|" "${PROJECT_ROOT}/scripts/auto-deploy.sh"
    
    # 设置可执行权限
    chmod +x "${PROJECT_ROOT}/scripts/auto-deploy.sh"
    echo -e "${GREEN}auto-deploy.sh 配置完成${NC}"
else
    echo -e "${RED}错误: auto-deploy.sh 不存在${NC}"
    exit 1
fi

# 配置 Git
echo -e "\n${BLUE}配置 Git...${NC}"
cd "${PROJECT_ROOT}"

if [ -d ".git" ]; then
    git config user.name "Server Deploy"
    git config user.email "deploy@example.com"
    
    # 检查远程仓库
    REMOTE_URL=$(git remote get-url origin 2>/dev/null || echo "")
    if [ -z "$REMOTE_URL" ]; then
        read -p "请输入 Git 远程仓库地址: " REMOTE_URL
        git remote add origin "$REMOTE_URL"
    fi
    
    echo -e "${GREEN}Git 配置完成${NC}"
    echo -e "远程仓库: ${REMOTE_URL}"
else
    echo -e "${RED}错误: 不是 Git 仓库${NC}"
    exit 1
fi

# 测试部署脚本
echo -e "\n${YELLOW}是否测试部署脚本？(y/n)${NC}"
read -p "> " TEST_DEPLOY

if [ "$TEST_DEPLOY" = "y" ] || [ "$TEST_DEPLOY" = "Y" ]; then
    echo -e "\n${BLUE}执行测试部署...${NC}"
    bash "${PROJECT_ROOT}/scripts/auto-deploy.sh"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}测试部署成功！${NC}"
    else
        echo -e "${RED}测试部署失败，请检查日志${NC}"
    fi
fi

# 显示配置摘要
echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  配置完成！${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}配置摘要：${NC}"
echo -e "  项目根目录: ${PROJECT_ROOT}"
echo -e "  Git 分支: ${GIT_BRANCH}"
echo -e "  Webhook 密钥: ${WEBHOOK_SECRET}"
echo ""
echo -e "${YELLOW}下一步操作：${NC}"
echo -e "1. 在 Gitee 仓库中配置 Webhook"
echo -e "   URL: http://your-domain.com/webhook.php"
echo -e "   密码: ${WEBHOOK_SECRET}"
echo -e "   事件: Push"
echo ""
echo -e "2. 测试 Webhook"
echo -e "   在 Gitee 中点击 '测试' 按钮"
echo ""
echo -e "3. 查看部署日志"
echo -e "   tail -f ${PROJECT_ROOT}/storage/logs/deploy.log"
echo ""
echo -e "${GREEN}详细文档请查看: CICD_SETUP.md${NC}"
echo ""
