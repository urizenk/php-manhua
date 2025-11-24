# PHP-CGI 守护进程部署说明

## 问题描述

手动启动的PHP-CGI进程在SSH会话断开后会终止，导致网站无法访问（502 Bad Gateway）。

## 解决方案

将PHP-CGI配置为systemd服务，让它作为系统守护进程运行，实现：
- ✅ SSH断开后持续运行
- ✅ 开机自动启动
- ✅ 异常退出自动重启
- ✅ 统一的服务管理

---

## 快速部署

### 1. 推送代码到服务器

**在Windows本地执行：**
```bash
cd c:\Users\123\Desktop\jd\php-manhua
git add deployment/
git commit -m "feat: 添加PHP-CGI systemd服务配置"
git push origin main:master
```

### 2. 在服务器安装服务

**在服务器执行：**
```bash
# 拉取最新代码
cd /var/www/php-manhua
git pull origin master

# 赋予执行权限
chmod +x deployment/setup-php-service.sh

# 运行安装脚本
sudo deployment/setup-php-service.sh
```

### 3. 验证服务状态

```bash
# 查看服务状态
sudo systemctl status php-cgi

# 查看进程
ps aux | grep php-cgi

# 查看端口
netstat -tlnp | grep 9000

# 测试网站访问
curl -I http://localhost:9090/
```

---

## 服务管理命令

```bash
# 启动服务
sudo systemctl start php-cgi

# 停止服务
sudo systemctl stop php-cgi

# 重启服务
sudo systemctl restart php-cgi

# 查看状态
sudo systemctl status php-cgi

# 查看日志
sudo journalctl -u php-cgi -f

# 禁用开机自启
sudo systemctl disable php-cgi

# 启用开机自启
sudo systemctl enable php-cgi
```

---

## 故障排查

### 服务无法启动

1. 检查PHP-CGI路径是否正确：
```bash
which php-cgi
/usr/local/php80/bin/php-cgi --version
```

2. 检查端口占用：
```bash
netstat -tlnp | grep 9000
```

3. 查看服务日志：
```bash
sudo journalctl -u php-cgi -n 50
```

### 502 Bad Gateway

1. 确认服务运行：
```bash
sudo systemctl status php-cgi
```

2. 检查Nginx配置：
```bash
sudo nginx -t
sudo systemctl restart nginx
```

3. 查看Nginx错误日志：
```bash
sudo tail -50 /var/log/nginx/manhua_error.log
```

---

## 文件说明

- `php-cgi.service` - systemd服务配置文件
- `setup-php-service.sh` - 一键安装脚本
- `README.md` - 本文档

---

## 技术细节

### 服务配置特点

- **类型**: simple - 前台运行
- **用户**: www-data - 与Nginx一致
- **自动重启**: 异常退出3秒后重启
- **资源限制**: 最大文件描述符65535

### 安全设置

- `PrivateTmp=true` - 使用独立临时目录
- `NoNewPrivileges=true` - 禁止获取新权限

---

## 验证清单

安装完成后，请验证以下项目：

- [ ] `sudo systemctl status php-cgi` 显示 active (running)
- [ ] `netstat -tlnp | grep 9000` 显示端口监听
- [ ] 浏览器访问 `http://8.149.138.212:9090/` 正常
- [ ] 浏览器访问 `http://8.149.138.212:9090/admin88/` 正常
- [ ] 退出SSH后网站依然可访问
- [ ] 服务器重启后网站自动恢复

---

## 注意事项

1. **首次安装后需要重启服务器验证开机自启**
2. **不要再使用 `php-cgi -b 127.0.0.1:9000 &` 手动启动**
3. **修改service文件后需要执行 `sudo systemctl daemon-reload`**
4. **查看实时日志使用 `sudo journalctl -u php-cgi -f`**

---

## 联系支持

如有问题，请查看：
- 系统日志: `sudo journalctl -u php-cgi -n 100`
- Nginx日志: `sudo tail -100 /var/log/nginx/manhua_error.log`
- PHP-CGI进程: `ps aux | grep php-cgi`
