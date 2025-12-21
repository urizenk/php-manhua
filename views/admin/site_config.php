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
            
            // 更新首页跳转URL
            if (isset($_POST['homepage_redirect_url'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('homepage_redirect_url', ?, '首页跳转URL') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['homepage_redirect_url'], $_POST['homepage_redirect_url']]
                );
            }
            
            // 更新访问码获取地址（多个，JSON格式）
            if (isset($_POST['access_code_urls']) && is_array($_POST['access_code_urls'])) {
                $urls = [];
                $names = $_POST['access_code_names'] ?? [];
                foreach ($_POST['access_code_urls'] as $i => $url) {
                    $url = trim($url);
                    $name = trim($names[$i] ?? '');
                    if ($url) {
                        $urls[] = ['name' => $name ?: '获取地址', 'url' => $url];
                    }
                }
                $urlsJson = json_encode($urls, JSON_UNESCAPED_UNICODE);
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('access_code_urls', ?, '访问码获取地址列表') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$urlsJson, $urlsJson]
                );
            }
            
            // 更新访问码获取教程
            if (isset($_POST['access_code_tutorial'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('access_code_tutorial', ?, '访问码获取教程') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['access_code_tutorial'], $_POST['access_code_tutorial']]
                );
            }
            
            // 更新失效反馈配置
            if (isset($_POST['feedback_qq'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('feedback_qq', ?, 'QQ群号') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['feedback_qq'], $_POST['feedback_qq']]
                );
            }
            if (isset($_POST['feedback_email'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('feedback_email', ?, '反馈邮箱') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['feedback_email'], $_POST['feedback_email']]
                );
            }
            if (isset($_POST['feedback_notice'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('feedback_notice', ?, '反馈须知') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['feedback_notice'], $_POST['feedback_notice']]
                );
            }
            
            // 更新防走丢配置
            if (isset($_POST['backup_urls']) && is_array($_POST['backup_urls'])) {
                $urls = [];
                $names = $_POST['backup_names'] ?? [];
                foreach ($_POST['backup_urls'] as $i => $url) {
                    $url = trim($url);
                    $name = trim($names[$i] ?? '');
                    if ($url) {
                        $urls[] = ['name' => $name ?: '备用地址', 'url' => $url];
                    }
                }
                $urlsJson = json_encode($urls, JSON_UNESCAPED_UNICODE);
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('backup_urls', ?, '备用地址列表') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$urlsJson, $urlsJson]
                );
            }
            if (isset($_POST['backup_notice'])) {
                $db->execute(
                    "INSERT INTO site_config (config_key, config_value, description) 
                     VALUES ('backup_notice', ?, '防走丢提示') 
                     ON DUPLICATE KEY UPDATE config_value = ?",
                    [$_POST['backup_notice'], $_POST['backup_notice']]
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
$homepageRedirectUrl = $configs['homepage_redirect_url'] ?? '';
$accessCodeUrls = json_decode($configs['access_code_urls'] ?? '[]', true) ?: [];
$accessCodeTutorial = $configs['access_code_tutorial'] ?? '';
// 失效反馈配置
$feedbackQQ = $configs['feedback_qq'] ?? '';
$feedbackEmail = $configs['feedback_email'] ?? '';
$feedbackNotice = $configs['feedback_notice'] ?? '';
// 防走丢配置
$backupUrls = json_decode($configs['backup_urls'] ?? '[]', true) ?: [];
$backupNotice = $configs['backup_notice'] ?? '';

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
            background: linear-gradient(135deg, #FFF5E6 0%, #FFE4CC 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .config-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.15);
            overflow: hidden;
        }
        .config-header {
            background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
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
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i {
            color: #FF6B35;
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
            border-color: #FF6B35;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .btn-save {
            background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
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
            background: #FFF5E6;
            border: 2px dashed #FFD4B8;
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
        .tip-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
            font-size: 0.9rem;
            color: #856404;
            margin-bottom: 15px;
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
                    
                    <div class="mb-3">
                        <label class="form-label">访问码获取地址 <span class="text-muted">(可选，支持多个)</span></label>
                        <div id="access-code-urls-container">
                            <?php if (empty($accessCodeUrls)): ?>
                                <div class="url-item mb-2">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control form-control-sm" name="access_code_names[]" placeholder="按钮文字">
                                        </div>
                                        <div class="col-md-7">
                                            <input type="url" class="form-control form-control-sm" name="access_code_urls[]" placeholder="https://weibo.com/xxx">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-url-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($accessCodeUrls as $urlItem): ?>
                                    <div class="url-item mb-2">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" name="access_code_names[]" 
                                                       value="<?php echo htmlspecialchars($urlItem['name'] ?? ''); ?>" placeholder="按钮文字">
                                            </div>
                                            <div class="col-md-7">
                                                <input type="url" class="form-control form-control-sm" name="access_code_urls[]" 
                                                       value="<?php echo htmlspecialchars($urlItem['url'] ?? ''); ?>" placeholder="https://weibo.com/xxx">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex gap-1">
                                                    <?php if (!empty($urlItem['url'])): ?>
                                                        <a href="<?php echo htmlspecialchars($urlItem['url']); ?>" target="_blank" class="btn btn-outline-info btn-sm" title="测试链接">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-url-btn" title="删除">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-url-btn" class="btn btn-outline-success btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> 添加获取地址
                        </button>
                        <div class="help-text">配置多个访问码获取渠道，用户可选择不同方式获取访问码，留空则不显示</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="access_code_tutorial" class="form-label">访问码获取教程</label>
                        <textarea class="form-control" id="access_code_tutorial" name="access_code_tutorial" 
                                  rows="4" placeholder="输入获取访问码的教程说明，支持多行文本"><?php echo htmlspecialchars($accessCodeTutorial); ?></textarea>
                        <div class="help-text">显示在访问码输入弹窗中的教程说明文字，支持换行</div>
                    </div>
                </div>

                <!-- 首页跳转配置 -->
                <div class="config-section">
                    <div class="section-title">
                        <i class="bi bi-link-45deg"></i>
                        首页跳转配置
                    </div>
                    
                    <div class="tip-box">
                        <i class="bi bi-info-circle"></i> 
                        设置此URL后，用户点击首页的微博按钮将跳转到这个地址
                    </div>

                    <div class="mb-3">
                        <label for="homepage_redirect_url" class="form-label">首页跳转URL</label>
                        <input type="url" class="form-control" id="homepage_redirect_url" name="homepage_redirect_url" 
                               value="<?php echo htmlspecialchars($homepageRedirectUrl); ?>" 
                               placeholder="https://weibo.com/your-account">
                        <div class="help-text">点击首页微博按钮时跳转的URL地址，留空则使用下方微博链接</div>
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
                        <div class="help-text">输入您的微博主页完整URL地址（当首页跳转URL为空时使用）</div>
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
                        <a href="<?php echo htmlspecialchars($homepageRedirectUrl ?: $weiboUrl); ?>" target="_blank" class="weibo-preview" id="weibo-preview">
                            <?php echo htmlspecialchars($weiboText); ?>
                        </a>
                    </div>
                </div>

                <!-- 失效反馈配置 -->
                <div class="config-section">
                    <div class="section-title">
                        <i class="bi bi-chat-dots"></i>
                        失效反馈页面配置
                    </div>

                    <div class="mb-3">
                        <label for="feedback_qq" class="form-label">QQ群号</label>
                        <input type="text" class="form-control" id="feedback_qq" name="feedback_qq" 
                               value="<?php echo htmlspecialchars($feedbackQQ); ?>" 
                               placeholder="123456789">
                        <div class="help-text">失效反馈页面显示的QQ群号，留空则不显示</div>
                    </div>

                    <div class="mb-3">
                        <label for="feedback_email" class="form-label">反馈邮箱</label>
                        <input type="email" class="form-control" id="feedback_email" name="feedback_email" 
                               value="<?php echo htmlspecialchars($feedbackEmail); ?>" 
                               placeholder="feedback@example.com">
                        <div class="help-text">失效反馈页面显示的邮箱地址，留空则不显示</div>
                    </div>

                    <div class="mb-3">
                        <label for="feedback_notice" class="form-label">反馈须知</label>
                        <textarea class="form-control" id="feedback_notice" name="feedback_notice" 
                                  rows="3" placeholder="输入反馈须知文本"><?php echo htmlspecialchars($feedbackNotice); ?></textarea>
                        <div class="help-text">失效反馈页面顶部的提示文字</div>
                    </div>
                </div>

                <!-- 防走丢配置 -->
                <div class="config-section">
                    <div class="section-title">
                        <i class="bi bi-geo-alt"></i>
                        防走丢页面配置
                    </div>

                    <div class="mb-3">
                        <label class="form-label">备用地址列表 <span class="text-muted">(可添加多个)</span></label>
                        <div id="backup-urls-container">
                            <?php if (empty($backupUrls)): ?>
                                <div class="backup-item mb-2">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control form-control-sm" name="backup_names[]" placeholder="名称">
                                        </div>
                                        <div class="col-md-7">
                                            <input type="url" class="form-control form-control-sm" name="backup_urls[]" placeholder="https://备用地址">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-backup-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($backupUrls as $item): ?>
                                    <div class="backup-item mb-2">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm" name="backup_names[]" 
                                                       value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" placeholder="名称">
                                            </div>
                                            <div class="col-md-7">
                                                <input type="url" class="form-control form-control-sm" name="backup_urls[]" 
                                                       value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>" placeholder="https://备用地址">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-backup-btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-backup-btn" class="btn btn-outline-success btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> 添加备用地址
                        </button>
                        <div class="help-text">配置多个备用访问地址，留空则不显示</div>
                    </div>

                    <div class="mb-3">
                        <label for="backup_notice" class="form-label">温馨提示</label>
                        <textarea class="form-control" id="backup_notice" name="backup_notice" 
                                  rows="3" placeholder="输入温馨提示文本"><?php echo htmlspecialchars($backupNotice); ?></textarea>
                        <div class="help-text">防走丢页面顶部的提示文字</div>
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
        document.getElementById('weibo_url').addEventListener('input', updatePreviewUrl);
        document.getElementById('homepage_redirect_url').addEventListener('input', updatePreviewUrl);
        
        document.getElementById('weibo_text').addEventListener('input', function() {
            document.getElementById('weibo-preview').textContent = this.value;
        });
        
        function updatePreviewUrl() {
            var redirectUrl = document.getElementById('homepage_redirect_url').value;
            var weiboUrl = document.getElementById('weibo_url').value;
            document.getElementById('weibo-preview').href = redirectUrl || weiboUrl;
        }
        
        // 动态添加/删除访问码获取地址
        document.getElementById('add-url-btn').addEventListener('click', function() {
            var container = document.getElementById('access-code-urls-container');
            var newItem = document.createElement('div');
            newItem.className = 'url-item mb-2';
            newItem.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="access_code_names[]" placeholder="按钮文字">
                    </div>
                    <div class="col-md-7">
                        <input type="url" class="form-control form-control-sm" name="access_code_urls[]" placeholder="https://weibo.com/xxx">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-url-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
        });
        
        // 删除按钮事件委托
        document.getElementById('access-code-urls-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-url-btn')) {
                var urlItem = e.target.closest('.url-item');
                if (urlItem) {
                    var container = document.getElementById('access-code-urls-container');
                    if (container.querySelectorAll('.url-item').length <= 1) {
                        urlItem.querySelector('input[name="access_code_names[]"]').value = '';
                        urlItem.querySelector('input[name="access_code_urls[]"]').value = '';
                    } else {
                        urlItem.remove();
                    }
                }
            }
        });
        
        // 动态添加备用地址
        document.getElementById('add-backup-btn').addEventListener('click', function() {
            var container = document.getElementById('backup-urls-container');
            var newItem = document.createElement('div');
            newItem.className = 'backup-item mb-2';
            newItem.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="backup_names[]" placeholder="名称">
                    </div>
                    <div class="col-md-7">
                        <input type="url" class="form-control form-control-sm" name="backup_urls[]" placeholder="https://备用地址">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-backup-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
        });
        
        // 删除备用地址
        document.getElementById('backup-urls-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-backup-btn')) {
                var item = e.target.closest('.backup-item');
                if (item) {
                    var container = document.getElementById('backup-urls-container');
                    if (container.querySelectorAll('.backup-item').length <= 1) {
                        item.querySelector('input[name="backup_names[]"]').value = '';
                        item.querySelector('input[name="backup_urls[]"]').value = '';
                    } else {
                        item.remove();
                    }
                }
            }
        });
    </script>
</body>
</html>
