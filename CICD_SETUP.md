# 🚀 Gitee Webhook CI/CD 自动化部署配置指南

## 📋 目录

1. [系统架构](#系统架构)
2. [前置要求](#前置要求)
3. [服务器端配置](#服务器端配置)
4. [Gitee 仓库配置](#gitee-仓库配置)
5. [测试部署](#测试部署)
6. [故障排查](#故障排查)
7. [安全建议](#安全建议)

---

## 🏗️ 系统架构

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   开发者     │         │    Gitee     │         │   服务器     │
│  本地推送    │ ──────> │   仓库       │ ──────> │  自动部署    │
└─────────────┘         └──────────────┘         └─────────────┘
                              │                         │
                              │  Webhook                │
                              │  POST 请求              │
                              ▼                         ▼
                        webhook.php              auto-deploy.sh
                        (接收端点)               (部署脚本)
```

**工作流程：**
1. 开发者推送代码到 Gitee 仓库
2. Gitee 触发 Webhook，发送 POST 请求到服务器
3. `webhook.php` 接收请求，验证签名
4. 执行 `auto-deploy.sh` 脚本
5. 脚本自动完成：备份、拉取代码、安装依赖、重启服务
6. 部署完成，网站自动更新

---

## ✅ 前置要求

### 服务器要求
- **操作系统**: Linux (Ubuntu 20.04+ / CentOS 7+)
- **Web 服务器**: Nginx 或 Apache
- **PHP 版本**: 8.0+
- **Git**: 已安装
- **权限**: 需要 sudo 权限（用于重启服务）

### 本地要求
- Git 已配置
- 有 Gitee 仓库的推送权限

---

## 🔧 服务器端配置

### 第一步：上传文件到服务器

将以下文件上传到服务器：

```bash
# 1. 上传 webhook.php 到 public 目录
scp webhook.php root@your-server:/www/wwwroot/php-manhua/public/

# 2. 上传部署脚本到 scripts 目录
scp scripts/auto-deploy.sh root@your-server:/www/wwwroot/php-manhua/scripts/
```

### 第二步：配置 webhook.php

SSH 登录服务器，编辑 `webhook.php`：

```bash
ssh root@your-server
cd /www/wwwroot/php-manhua/public
nano webhook.php
```

修改以下配置：

```php
// Webhook 密钥（必须与 Gitee 中配置的一致）
define('WEBHOOK_SECRET', 'your_strong_secret_token_here');

// 项目根目录（绝对路径）
define('PROJECT_ROOT', '/www/wwwroot/php-manhua');

// Git 分支
define('GIT_BRANCH', 'master');
```

**重要：** 生成一个强密钥，例如：
```bash
openssl rand -hex 32
# 输出示例：a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6
```

### 第三步：配置 auto-deploy.sh

编辑部署脚本：

```bash
cd /www/wwwroot/php-manhua/scripts
nano auto-deploy.sh
```

修改以下配置：

```bash
# 项目配置
PROJECT_ROOT="/www/wwwroot/php-manhua"  # 修改为实际路径
GIT_BRANCH="master"                      # 修改为实际分支
```

设置可执行权限：

```bash
chmod +x /www/wwwroot/php-manhua/scripts/auto-deploy.sh
```

### 第四步：创建必要的目录

```bash
cd /www/wwwroot/php-manhua

# 创建日志目录
mkdir -p storage/logs
chmod -R 777 storage/logs

# 创建备份目录
mkdir -p backups
chmod -R 755 backups

# 创建上传目录
mkdir -p public/uploads
chmod -R 777 public/uploads
```

### 第五步：配置 Git

确保服务器上的 Git 配置正确：

```bash
cd /www/wwwroot/php-manhua

# 配置 Git 用户信息
git config user.name "Server Deploy"
git config user.email "deploy@example.com"

# 设置远程仓库
git remote set-url origin https://gitee.com/dot123dot/php-manhua.git

# 测试 Git 连接
git fetch origin
```

**如果需要使用 SSH 方式（推荐）：**

```bash
# 生成 SSH 密钥
ssh-keygen -t rsa -b 4096 -C "deploy@example.com"

# 查看公钥
cat ~/.ssh/id_rsa.pub

# 将公钥添加到 Gitee 账户的 SSH 公钥设置中
# 然后修改远程仓库地址
git remote set-url origin git@gitee.com:dot123dot/php-manhua.git
```

### 第六步：配置 Nginx（可选）

如果需要通过域名访问 webhook：

```bash
nano /etc/nginx/sites-available/php-manhua
```

添加或修改配置：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /www/wwwroot/php-manhua/public;
    
    index index.php index.html;
    
    # Webhook 端点
    location /webhook.php {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # 其他配置...
}
```

重启 Nginx：

```bash
nginx -t
systemctl reload nginx
```

### 第七步：测试部署脚本

手动执行一次部署脚本，确保没有错误：

```bash
cd /www/wwwroot/php-manhua
bash scripts/auto-deploy.sh
```

查看日志：

```bash
tail -f storage/logs/deploy.log
```

---

## 🔗 Gitee 仓库配置

### 第一步：登录 Gitee

访问您的仓库：`https://gitee.com/dot123dot/php-manhua`

### 第二步：进入 Webhook 设置

1. 点击仓库页面的 **"管理"** 按钮
2. 在左侧菜单中选择 **"WebHooks"**
3. 点击 **"添加 WebHook"**

### 第三步：配置 Webhook

填写以下信息：

| 配置项 | 值 | 说明 |
|--------|-----|------|
| **URL** | `http://your-domain.com/webhook.php` | 服务器上的 webhook 端点 |
| **密码** | `your_strong_secret_token_here` | 与 webhook.php 中配置的一致 |
| **事件选择** | ✅ Push | 选择 Push 事件 |
| **激活** | ✅ 是 | 启用 Webhook |

### 第四步：保存并测试

1. 点击 **"添加"** 按钮保存配置
2. 在 WebHooks 列表中，点击刚创建的 Webhook
3. 点击 **"测试"** 按钮，选择 **"Push 事件"**
4. 查看响应结果

**成功的响应示例：**
```json
{
    "success": true,
    "message": "部署成功",
    "output": [
        "[INFO] 开始自动化部署",
        "[SUCCESS] 备份创建成功",
        "[SUCCESS] 代码拉取成功",
        "[SUCCESS] 自动化部署完成"
    ]
}
```

---

## 🧪 测试部署

### 测试流程

1. **本地修改代码**
   ```bash
   cd c:\Users\123\Desktop\jd\php-manhua
   
   # 修改一个文件（例如 README.md）
   echo "# Test CI/CD" >> README.md
   
   # 提交并推送
   git add .
   git commit -m "test: 测试 CI/CD 自动部署"
   git push origin master
   ```

2. **查看 Gitee Webhook 日志**
   - 进入 Gitee 仓库的 WebHooks 设置
   - 查看 **"最近推送"** 记录
   - 确认状态为 **"成功"**（绿色）

3. **查看服务器部署日志**
   ```bash
   ssh root@your-server
   tail -f /www/wwwroot/php-manhua/storage/logs/deploy.log
   ```

4. **验证网站更新**
   - 访问网站，确认更改已生效
   - 检查文件时间戳：
     ```bash
     ls -la /www/wwwroot/php-manhua/README.md
     ```

---

## 🔍 故障排查

### 问题 1：Webhook 返回 403 错误

**原因：** 签名验证失败

**解决方案：**
1. 检查 `webhook.php` 中的 `WEBHOOK_SECRET` 是否与 Gitee 配置一致
2. 确保没有多余的空格或换行符
3. 查看服务器日志：
   ```bash
   tail -f /www/wwwroot/php-manhua/storage/logs/deploy.log
   ```

### 问题 2：Webhook 返回 500 错误

**原因：** 部署脚本执行失败

**解决方案：**
1. 检查脚本权限：
   ```bash
   chmod +x /www/wwwroot/php-manhua/scripts/auto-deploy.sh
   ```
2. 手动执行脚本，查看错误：
   ```bash
   bash /www/wwwroot/php-manhua/scripts/auto-deploy.sh
   ```
3. 检查日志文件权限：
   ```bash
   chmod -R 777 /www/wwwroot/php-manhua/storage/logs
   ```

### 问题 3：Git 拉取失败

**原因：** Git 权限或配置问题

**解决方案：**
1. 检查 Git 远程仓库：
   ```bash
   cd /www/wwwroot/php-manhua
   git remote -v
   ```
2. 测试 Git 连接：
   ```bash
   git fetch origin
   ```
3. 如果使用 HTTPS，配置凭据：
   ```bash
   git config credential.helper store
   git pull origin master  # 输入用户名和密码
   ```
4. 如果使用 SSH，确保 SSH 密钥已添加到 Gitee

### 问题 4：权限不足

**原因：** Web 服务器用户没有执行权限

**解决方案：**
1. 查看 PHP-FPM 运行用户：
   ```bash
   ps aux | grep php-fpm
   ```
2. 修改文件所有者：
   ```bash
   chown -R www-data:www-data /www/wwwroot/php-manhua
   ```
3. 或者使用 sudo 执行（需要配置 sudoers）：
   ```bash
   visudo
   # 添加：www-data ALL=(ALL) NOPASSWD: /www/wwwroot/php-manhua/scripts/auto-deploy.sh
   ```

### 问题 5：部署成功但网站未更新

**原因：** 缓存未清理

**解决方案：**
1. 清理 OPcache：
   ```bash
   systemctl reload php8.0-fpm
   ```
2. 清理 Nginx 缓存（如果有）：
   ```bash
   nginx -s reload
   ```
3. 清理浏览器缓存（Ctrl + F5）

---

## 🔒 安全建议

### 1. 使用强密钥

```bash
# 生成 64 字符的随机密钥
openssl rand -hex 32
```

### 2. 限制 IP 访问

在 Nginx 配置中限制只允许 Gitee 的 IP 访问 webhook：

```nginx
location /webhook.php {
    # Gitee 的 IP 段（示例，请查询最新的）
    allow 212.64.62.0/24;
    allow 212.64.63.0/24;
    deny all;
    
    # 其他配置...
}
```

### 3. 使用 HTTPS

配置 SSL 证书，使用 HTTPS 传输：

```bash
# 使用 Let's Encrypt 免费证书
certbot --nginx -d your-domain.com
```

### 4. 日志监控

定期检查部署日志，发现异常及时处理：

```bash
# 查看最近的部署记录
tail -100 /www/wwwroot/php-manhua/storage/logs/deploy.log

# 查看失败的部署
grep "ERROR" /www/wwwroot/php-manhua/storage/logs/deploy.log
```

### 5. 备份策略

部署脚本会自动创建备份，但建议额外配置：

```bash
# 定期备份到远程服务器
rsync -avz /www/wwwroot/php-manhua/backups/ user@backup-server:/backups/php-manhua/
```

### 6. 回滚机制

如果部署出现问题，可以快速回滚：

```bash
# 查看备份列表
ls -lh /www/wwwroot/php-manhua/backups/

# 回滚到指定备份
cd /www/wwwroot/php-manhua
tar -xzf backups/backup_20231208_120000.tar.gz
systemctl reload php8.0-fpm
systemctl reload nginx
```

---

## 📊 监控和通知

### 集成钉钉通知

在 `auto-deploy.sh` 的 `send_notification()` 函数中添加：

```bash
send_notification() {
    log_info "发送部署通知..."
    
    DINGTALK_WEBHOOK="https://oapi.dingtalk.com/robot/send?access_token=YOUR_TOKEN"
    
    MESSAGE="{
        \"msgtype\": \"text\",
        \"text\": {
            \"content\": \"【部署通知】\n项目：PHP漫画管理系统\n状态：部署成功\n时间：$(date '+%Y-%m-%d %H:%M:%S')\n分支：${GIT_BRANCH}\"
        }
    }"
    
    curl -X POST "${DINGTALK_WEBHOOK}" \
         -H 'Content-Type: application/json' \
         -d "${MESSAGE}"
    
    log_info "通知已发送"
}
```

### 集成企业微信通知

```bash
send_notification() {
    WECOM_WEBHOOK="https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=YOUR_KEY"
    
    MESSAGE="{
        \"msgtype\": \"markdown\",
        \"markdown\": {
            \"content\": \"## 部署通知\n>项目：<font color=\\\"info\\\">PHP漫画管理系统</font>\n>状态：<font color=\\\"info\\\">部署成功</font>\n>时间：$(date '+%Y-%m-%d %H:%M:%S')\"
        }
    }"
    
    curl -X POST "${WECOM_WEBHOOK}" \
         -H 'Content-Type: application/json' \
         -d "${MESSAGE}"
}
```

---

## 🎯 高级功能

### 1. 多环境部署

支持开发、测试、生产环境：

```bash
# 在 auto-deploy.sh 中添加环境判断
ENVIRONMENT="production"  # development, staging, production

case $ENVIRONMENT in
    development)
        GIT_BRANCH="develop"
        ;;
    staging)
        GIT_BRANCH="staging"
        ;;
    production)
        GIT_BRANCH="master"
        ;;
esac
```

### 2. 数据库迁移

在 `run_migrations()` 函数中添加：

```bash
run_migrations() {
    log_info "执行数据库迁移..."
    
    cd "${PROJECT_ROOT}"
    
    # 检查是否有新的迁移文件
    if [ -d "database/migrations" ]; then
        # 执行迁移（示例）
        php artisan migrate --force
        
        if [ $? -eq 0 ]; then
            log_success "数据库迁移成功"
        else
            log_error "数据库迁移失败"
            return 1
        fi
    fi
}
```

### 3. 前端资源构建

如果项目包含前端资源需要编译：

```bash
build_assets() {
    log_info "构建前端资源..."
    
    cd "${PROJECT_ROOT}"
    
    if [ -f "package.json" ]; then
        npm run build
        
        if [ $? -eq 0 ]; then
            log_success "前端资源构建成功"
        else
            log_error "前端资源构建失败"
            return 1
        fi
    fi
}
```

---

## 📝 总结

现在您已经完成了 Gitee Webhook CI/CD 的配置！

**工作流程：**
1. 本地开发 → 提交代码 → 推送到 Gitee
2. Gitee 自动触发 Webhook
3. 服务器接收请求 → 验证签名 → 执行部署
4. 自动完成：备份、拉取、安装、重启
5. 部署完成，网站自动更新

**下一步：**
- 测试完整的部署流程
- 配置通知系统
- 设置监控和告警
- 定期检查日志

如有问题，请查看故障排查部分或联系技术支持。

---

**文档版本**: 1.0  
**最后更新**: 2025-12-08  
**维护者**: dot123dot
