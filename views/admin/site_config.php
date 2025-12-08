<?php
/**
 * 网站配置管理模块
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '网站配置';

// 处理表单提交
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    // CSRF Token验证
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'CSRF验证失败，请刷新页面重试';
        $messageType = 'danger';
    } else {
        try {
            $db->beginTransaction();
            
            // 更新访问码
            if (isset($_POST['access_code'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('access_code', ?, '当日访问码') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['access_code'], $_POST['access_code']]
                );
            }
            
            // 更新网站名称
            if (isset($_POST['site_name'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('site_name', ?, '网站名称') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['site_name'], $_POST['site_name']]
                );
            }
            
            // 更新网站描述
            if (isset($_POST['site_desc'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('site_desc', ?, '网站描述') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['site_desc'], $_POST['site_desc']]
                );
            }
            
            // 更新微博链接
            if (isset($_POST['weibo_url'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('weibo_url', ?, '微博主页链接') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['weibo_url'], $_POST['weibo_url']]
                );
            }
            
            // 更新微博显示文本
            if (isset($_POST['weibo_text'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('weibo_text', ?, '微博显示文本') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['weibo_text'], $_POST['weibo_text']]
                );
            }
            
            $db->commit();
            $message = '配置保存成功！';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $db->rollBack();
            $message = '保存失败：' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// 获取当前配置
$configs = [];
$configRows = $db->query("SELECT config_key, config_value FROM site_config");
foreach ($configRows as $row) {
    $configs[$row['config_key']] = $row['config_value'];
}

// 设置默认值
$accessCode = $configs['access_code'] ?? '1024';
$siteName = $configs['site_name'] ?? '海の小窝';
$siteDesc = $configs['site_desc'] ?? '无偿分享 禁止盗卖 更多精彩';
$weiboUrl = $configs['weibo_url'] ?? 'https://weibo.com/';
$weiboText = $configs['weibo_text'] ?? '微博@资源小站';

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .config-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .config-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .config-header h1 {
            font-size: 2rem;
            margin: 0;
            font-weight: 700;
        }
        .config-body {
            padding: 40px;
        }
        .config-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        .config-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i {
            color: #3498db;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-back {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .preview-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        .preview-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .weibo-preview {
            display: inline-block;
            background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .weibo-preview:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
    </style>
</head>
<body>
    <div class="config-container">
        <div class="config-header">
            <h1><i class="bi bi-gear-fill"></i> 网站配置管理</h1>
        </div>

        <div class="config-body">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo $session->csrfField(); ?>

                <!-- 基本配置 -->
                <div class="config-section">
                    <div class="section-title">
                        <i class="bi bi-house-door-fill"></i>
                        基本配置
                    </div>

                    <div class="mb-3">
                        <label for="site_name" class="form-label">网站名称</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="<?php echo htmlspecialchars($siteName); ?>" required>
                        <div class="help-text">显示在网站标题和页面顶部</div>
                    </div>

                    <div class="mb-3">
                        <label for="site_desc" class="form-label">网站描述</label>
                        <input type="text" class="form-control" id="site_desc" name="site_desc" 
                               value="<?php echo htmlspecialchars($siteDesc); ?>" required>
                        <div class="help-text">显示在首页欢迎卡片中</div>
                    </div>

                    <div class="mb-3">
                        <label for="access_code" class="form-label">访问码</label>
                        <input type="text" class="form-control" id="access_code" name="access_code" 
                               value="<?php echo htmlspecialchars($accessCode); ?>" required>
                        <div class="help-text">用户需要输入此访问码才能访问网站内容</div>
                    </div>
                </div>

                <!-- 社交媒体配置 -->
                <div class="config-section">
                    <div class="section-title">
                        <i class="bi bi-share-fill"></i>
                        社交媒体配置
                    </div>

                    <div class="mb-3">
                        <label for="weibo_url" class="form-label">微博主页链接</label>
                        <input type="url" class="form-control" id="weibo_url" name="weibo_url" 
                               value="<?php echo htmlspecialchars($weiboUrl); ?>" 
                               placeholder="https://weibo.com/your-account" required>
                        <div class="help-text">输入您的微博主页完整URL地址</div>
                    </div>

                    <div class="mb-3">
                        <label for="weibo_text" class="form-label">微博按钮显示文本</label>
                        <input type="text" class="form-control" id="weibo_text" name="weibo_text" 
                               value="<?php echo htmlspecialchars($weiboText); ?>" 
                               placeholder="微博@资源小站" required>
                        <div class="help-text">显示在微博按钮上的文字</div>
                    </div>

                    <!-- 预览 -->
                    <div class="preview-box">
                        <div class="preview-label">按钮预览效果：</div>
                        <a href="<?php echo htmlspecialchars($weiboUrl); ?>" target="_blank" class="weibo-preview" id="weibo-preview">
                            <?php echo htmlspecialchars($weiboText); ?>
                        </a>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="d-flex gap-3 justify-content-center">
                    <button type="submit" name="save_config" class="btn btn-primary btn-save">
                        <i class="bi bi-check-circle-fill"></i> 保存配置
                    </button>
                    <a href="/admin88/" class="btn btn-secondary btn-back">
                        <i class="bi bi-arrow-left"></i> 返回首页
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 实时预览
        document.getElementById('weibo_url').addEventListener('input', function() {
            document.getElementById('weibo-preview').href = this.value;
        });
        
        document.getElementById('weibo_text').addEventListener('input', function() {
            document.getElementById('weibo-preview').textContent = this.value;
        });
    </script>
</body>
</html>
