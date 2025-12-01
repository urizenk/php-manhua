<?php
session_start();
// 数据库配置
$host = 'localhost';
$dbname = 'blmh_site';
$username = 'blmh_site';
$password = '4BtsZWFSQJNHABRc';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 管理员权限验证
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$message = '';

// 处理访问码更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_password'])) {
        // 更新访问码
        $new_password = $_POST['new_password'];
        if (empty($new_password)) {
            $message = "新密码不能为空！";
        } else {
            try {
                // 直接插入明文密码
                $stmt = $pdo->prepare("INSERT INTO passwords (password) VALUES (?)");
                $stmt->execute([$new_password]);

                // 记录操作日志
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt = $pdo->prepare("INSERT INTO audit_log (action, ip_address) VALUES ('密码更新', ?)");
                $stmt->execute([$ip]);

                $message = "访问码更新成功！所有用户需要重新登录。";
                
            } catch (Exception $e) {
                $message = "更新失败: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update_links'])) {
        // 更新访问码链接
        try {
            if (!empty($_POST['link_names']) && !empty($_POST['link_urls'])) {
                // 先禁用所有链接
                $stmt = $pdo->prepare("UPDATE access_links SET is_active = FALSE");
                $stmt->execute();
                
                // 更新或插入新链接
                foreach ($_POST['link_names'] as $index => $name) {
                    $url = $_POST['link_urls'][$index];
                    if (!empty($name) && !empty($url)) {
                        $sort_order = $index + 1;
                        
                        // 检查是否已存在
                        $stmt = $pdo->prepare("SELECT id FROM access_links WHERE name = ?");
                        $stmt->execute([$name]);
                        $existing = $stmt->fetch();
                        
                        if ($existing) {
                            // 更新现有链接
                            $stmt = $pdo->prepare("UPDATE access_links SET url = ?, sort_order = ?, is_active = TRUE WHERE id = ?");
                            $stmt->execute([$url, $sort_order, $existing['id']]);
                        } else {
                            // 插入新链接
                            $stmt = $pdo->prepare("INSERT INTO access_links (name, url, sort_order, is_active) VALUES (?, ?, ?, TRUE)");
                            $stmt->execute([$name, $url, $sort_order]);
                        }
                    }
                }
                
                $message = "访问码链接更新成功！";
            }
        } catch (Exception $e) {
            $message = "链接更新失败: " . $e->getMessage();
        }
    } elseif (isset($_POST['add_link'])) {
        // 添加新链接
        try {
            $name = $_POST['new_link_name'] ?? '';
            $url = $_POST['new_link_url'] ?? '';
            
            if (!empty($name) && !empty($url)) {
                // 获取最大排序值
                $stmt = $pdo->query("SELECT MAX(sort_order) as max_order FROM access_links");
                $max_order = $stmt->fetch()['max_order'] ?? 0;
                $sort_order = $max_order + 1;
                
                $stmt = $pdo->prepare("INSERT INTO access_links (name, url, sort_order, is_active) VALUES (?, ?, ?, TRUE)");
                $stmt->execute([$name, $url, $sort_order]);
                
                $message = "链接添加成功！";
            } else {
                $message = "链接名称和URL不能为空！";
            }
        } catch (Exception $e) {
            $message = "链接添加失败: " . $e->getMessage();
        }
    }
}

// 获取当前访问码链接
try {
    $stmt = $pdo->query("SELECT * FROM access_links WHERE is_active = TRUE ORDER BY sort_order");
    $access_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $access_links = [];
}

// 获取最新的访问码（用于显示）
try {
    $stmt = $pdo->query("SELECT password, created_at FROM passwords ORDER BY id DESC LIMIT 1");
    $latest_password = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latest_password = null;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>访问码管理 - 管理员后台</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #FFA500, #FF8C00);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .admin-nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .admin-nav a {
            color: #495057;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #007bff;
            color: white;
        }
        
        .admin-content {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            color: #0066cc;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .section h3 {
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .link-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .link-item input {
            flex: 1;
        }
        
        .current-links {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .current-links h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .link-list {
            list-style: none;
            padding: 0;
        }
        
        .link-list li {
            padding: 8px 0;
            border-bottom: 1px solid #ffeaa7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .link-list li:last-child {
            border-bottom: none;
        }
        
        .password-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .password-info h4 {
            color: #0c5460;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .admin-content {
                padding: 20px;
            }
            
            .admin-nav {
                flex-direction: column;
            }
            
            .link-item {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-key"></i> 访问码管理</h1>
            <p>管理网站访问密码和获取链接</p>
        </div>
        
        <div class="admin-nav">
            <a href="admin-comics.php"><i class="fas fa-plus"></i> 添加漫画</a>
            <a href="admin-comics-list.php"><i class="fas fa-list"></i> 漫画列表</a>
            <a href="admin-tags.php"><i class="fas fa-tags"></i> 标签管理</a>
            <a href="update-password.php" class="active"><i class="fas fa-key"></i> 访问码管理</a>
            <a href="index.php"><i class="fas fa-home"></i> 返回首页</a>
        </div>
        
        <div class="admin-content">
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3><i class="fas fa-info-circle"></i> 说明</h3>
                <p>在这里可以更新网站访问码和管理用户获取访问码的链接。</p>
                <p>更新访问码后，所有用户需要重新输入新的访问码才能进入网站。</p>
            </div>

            <!-- 当前访问码信息 -->
            <?php if ($latest_password): ?>
            <div class="password-info">
                <h4><i class="fas fa-lock"></i> 当前访问码</h4>
                <p><strong>密码：</strong><?php echo htmlspecialchars($latest_password['password']); ?></p>
                <p><strong>更新时间：</strong><?php echo htmlspecialchars($latest_password['created_at']); ?></p>
            </div>
            <?php endif; ?>

            <!-- 更新访问码 -->
            <div class="section">
                <h3><i class="fas fa-sync-alt"></i> 更新访问码</h3>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">新访问码</label>
                        <input type="text" name="new_password" class="form-control" placeholder="输入新的访问码" required>
                    </div>
                    
                    <div class="form-group" style="text-align: center;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 更新访问码
                        </button>
                    </div>
                </form>
            </div>

            <!-- 管理访问码链接 -->
            <div class="section">
                <h3><i class="fas fa-link"></i> 管理访问码链接</h3>
                
                <!-- 当前链接显示 -->
                <?php if (!empty($access_links)): ?>
                <div class="current-links">
                    <h4><i class="fas fa-list"></i> 当前生效的链接</h4>
                    <ul class="link-list">
                        <?php foreach ($access_links as $link): ?>
                        <li>
                            <span><strong><?php echo htmlspecialchars($link['name']); ?>：</strong>
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['url']); ?></a></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- 更新链接表单 -->
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">访问码链接</label>
                        <div id="links-container">
                            <?php foreach ($access_links as $index => $link): ?>
                            <div class="link-item">
                                <input type="text" name="link_names[]" class="form-control" placeholder="链接名称（如：UC网盘）" value="<?php echo htmlspecialchars($link['name']); ?>" required>
                                <input type="url" name="link_urls[]" class="form-control" placeholder="链接URL" value="<?php echo htmlspecialchars($link['url']); ?>" required>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="removeLink(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($access_links)): ?>
                            <div class="link-item">
                                <input type="text" name="link_names[]" class="form-control" placeholder="链接名称（如：UC网盘）" required>
                                <input type="url" name="link_urls[]" class="form-control" placeholder="链接URL" required>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="removeLink(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addLink()" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> 添加链接
                        </button>
                    </div>
                    
                    <div class="form-group" style="text-align: center;">
                        <button type="submit" name="update_links" class="btn btn-success">
                            <i class="fas fa-save"></i> 更新链接
                        </button>
                    </div>
                </form>

                <!-- 添加新链接 -->
                <form method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-plus-circle"></i> 添加新链接</h4>
                    <div class="form-row" style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <input type="text" name="new_link_name" class="form-control" placeholder="新链接名称">
                        </div>
                        <div style="flex: 2;">
                            <input type="url" name="new_link_url" class="form-control" placeholder="新链接URL">
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" name="add_link" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 添加链接
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 链接管理
        function addLink() {
            const container = document.getElementById('links-container');
            const newLink = document.createElement('div');
            newLink.className = 'link-item';
            newLink.innerHTML = `
                <input type="text" name="link_names[]" class="form-control" placeholder="链接名称（如：UC网盘）" required>
                <input type="url" name="link_urls[]" class="form-control" placeholder="链接URL" required>
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeLink(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newLink);
        }
        
        function removeLink(button) {
            const container = document.getElementById('links-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            }
        }
    </script>
</body>
</html>