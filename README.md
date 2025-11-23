# PHP漫画资源管理系统

一个基于PHP 8.0 + MySQL 5.7的漫画资源管理与分享平台。

## 功能特性

### 后台管理系统
- ✅ 添加漫画（支持9种类型）
- ✅ 漫画列表管理（增删改查、批量操作）
- ✅ 标签管理（CRUD、排序）
- ✅ 访问码更新
- ✅ 左侧竖向菜单布局

### 前台展示系统
- ✅ 9宫格卡片主界面
- ✅ 访问码验证（点击模块时拦截）
- ✅ 9大漫画模块（日更、韩漫、完结短漫等）
- ✅ 详情页展示
- ✅ 关键词搜索

## 技术栈

- **后端**: PHP 8.0 + 原生MVC架构
- **数据库**: MySQL 5.7.44
- **前端**: Bootstrap 5 + jQuery
- **Web服务器**: Nginx
- **部署环境**: 宝塔面板 + CentOS 7

## 项目结构

```
php-manhua/
├── app/
│   ├── Core/              # 核心模块
│   │   ├── Database.php   # 数据库管理
│   │   ├── Router.php     # 路由模块
│   │   ├── Session.php    # 会话管理
│   │   └── Upload.php     # 文件上传
│   └── Controllers/       # 控制器（未使用）
├── config/
│   └── config.php         # 配置文件
├── database/
│   └── schema.sql         # 数据库表结构
├── public/
│   ├── admin88/           # 后台入口
│   │   └── index.php
│   ├── index.php          # 前台入口
│   ├── assets/            # 静态资源
│   └── uploads/           # 上传文件
├── storage/
│   ├── cache/             # 缓存目录
│   └── logs/              # 日志目录
├── views/
│   ├── admin/             # 后台视图
│   ├── frontend/          # 前台视图
│   └── errors/            # 错误页面
├── .htaccess              # Apache配置
├── nginx.conf             # Nginx配置
└── README.md              # 说明文档
```

## 安装部署（宝塔面板）

### 1. 环境要求

- PHP >= 8.0
- MySQL >= 5.7
- Nginx
- PHP扩展: pdo_mysql, gd, mbstring, json, session

### 2. 创建网站

在宝塔面板中：
1. 点击「网站」→「添加站点」
2. 域名填写：`yourdomain.com` 或 `manhua.yourdomain.com`
3. 根目录选择：`/www/wwwroot/php-manhua`
4. PHP版本选择：`PHP-80`
5. 创建数据库：`manhua_db`

### 3. 上传代码

将所有文件上传到：`/www/wwwroot/php-manhua/`

### 4. 导入数据库

1. 在宝塔面板进入「数据库」
2. 找到 `manhua_db`，点击「管理」→「导入」
3. 选择 `database/schema.sql` 导入

### 5. 修改配置文件

编辑 `config/config.php`：

```php
'database' => [
    'host'     => 'localhost',
    'dbname'   => 'manhua_db',
    'username' => 'root',              // 修改为实际用户名
    'password' => 'your_password',     // 修改为实际密码
],

'app' => [
    'base_url' => 'https://yourdomain.com',  // 修改为实际域名
    'debug'    => false,                     // 生产环境设为false
],
```

### 6. 配置伪静态

在宝塔面板：
1. 进入「网站设置」→「伪静态」
2. 复制 `nginx.conf` 的内容粘贴
3. 保存

### 7. 设置目录权限

```bash
chmod -R 755 /www/wwwroot/php-manhua
chmod -R 777 /www/wwwroot/php-manhua/public/uploads
chmod -R 777 /www/wwwroot/php-manhua/storage
```

### 8. 配置SSL证书（可选）

在宝塔面板：
1. 进入「网站设置」→「SSL」
2. 选择「Let's Encrypt」免费证书
3. 申请并部署

### 9. 访问系统

- **前台地址**: `https://yourdomain.com`
- **后台地址**: `https://yourdomain.com/admin88`
- **默认账号**: admin / admin123
- **默认访问码**: 1024

## 默认账号密码

### 管理员账号
- 用户名: `admin`
- 密码: `admin123`

### 前台访问码
- 默认访问码: `1024`
- 可在后台「访问码更新」中修改

## 常见问题

### 1. 访问码更新失败

**原因**: 数据库连接配置错误

**解决方案**:
1. 检查 `config/config.php` 中的数据库配置
2. 确认数据库名称、用户名、密码正确
3. 检查 `site_config` 表是否存在

### 2. 图片上传失败

**原因**: 目录权限不足

**解决方案**:
```bash
chmod -R 777 /www/wwwroot/php-manhua/public/uploads
```

### 3. 404错误

**原因**: 伪静态规则未生效

**解决方案**:
1. 确认已在宝塔面板配置伪静态
2. 重启Nginx服务

### 4. 数据库连接失败

**原因**: 数据库配置错误或数据库未创建

**解决方案**:
1. 检查数据库是否已创建
2. 确认数据库用户名密码正确
3. 检查MySQL服务是否启动

## 安全建议

1. **修改后台路径**: 将 `public/admin88` 改为自定义路径
2. **修改管理员密码**: 首次登录后立即修改密码
3. **关闭调试模式**: 生产环境将 `app.debug` 设为 `false`
4. **定期备份数据库**: 在宝塔面板设置每日自动备份
5. **启用SSL**: 使用HTTPS协议访问

## 数据库备份

在宝塔面板中：
1. 进入「计划任务」
2. 添加任务类型：「备份数据库」
3. 执行周期：每天凌晨3点
4. 备份数据库：`manhua_db`
5. 保留天数：7天

## 更新日志

### v1.0.0 (2025-11-16)
- ✅ 项目初始化
- ✅ 核心基础模块完成
- ✅ 后台管理系统完成
- ✅ 前台展示系统完成
- ✅ 宝塔部署配置完成

## 技术支持

如有问题，请参考：
- 项目文档: `PROJECT_INIT.md`
- 进度跟踪: `PROGRESS.md`

## License

MIT License


