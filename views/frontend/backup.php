<?php
/**
 * F6-防走丢模块（固定内容）
 */

// 全局配置（用于微博链接等）
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$pageTitle = '备用地址';

// 从数据库读取配置
$configRows = $db->query("SELECT config_key, config_value FROM site_config WHERE config_key IN ('weibo_url', 'weibo_text', 'backup_urls', 'backup_notice')");
$configs = [];
foreach ($configRows as $row) {
    $configs[$row['config_key']] = $row['config_value'];
}
$weiboUrl  = $configs['weibo_url'] ?? 'https://weibo.com/';
$weiboText = $configs['weibo_text'] ?? '微博@资源小站';
$backupUrls = json_decode($configs['backup_urls'] ?? '[]', true) ?: [];
$backupNotice = $configs['backup_notice'] ?? '为防止主站无法访问，请收藏以下备用地址。';

$customCss = '
<style>
    .content-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .page-header {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 30px;
        text-align: center;
    }
    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #1976D2;
        margin-bottom: 15px;
    }
    .page-subtitle {
        color: #666;
        font-size: 1.1rem;
    }
    .content-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 35px;
        margin-bottom: 25px;
    }
    .content-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    .link-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .link-item {
        padding: 20px;
        border: 2px solid #f0f0f0;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .link-item:hover {
        border-color: #1976D2;
        background: #f8f9ff;
        transform: translateX(10px);
    }
    .link-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .link-icon {
        width: 40px;
        height: 40px;
        background: #1976D2;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 15px;
    }
    .link-name {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
    }
    .link-url {
        color: #1976D2;
        text-decoration: none;
        font-size: 0.95rem;
        word-break: break-all;
    }
    .link-url:hover {
        text-decoration: underline;
    }
    .notice-box {
        background: #d1ecf1;
        border-left: 4px solid #0c5460;
        padding: 20px;
        border-radius: 10px;
    }
    .notice-title {
        font-weight: bold;
        color: #0c5460;
        margin-bottom: 10px;
    }
    .notice-text {
        color: #0c5460;
        margin: 0;
    }
    .back-btn {
        background: #ffffff;
        color: #1976D2;
        border: 2px solid #1976D2;
        border-radius: 25px;
        padding: 10px 30px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    .back-btn:hover {
        background: #1976D2;
        color: #ffffff;
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1 class="page-title">📍 防走丢</h1>
        <p class="page-subtitle">备用访问地址 · 收藏以备不时之需</p>
    </div>

    <div class="notice-box">
        <div class="notice-title">💡 温馨提示</div>
        <p class="notice-text"><?php echo nl2br(htmlspecialchars($backupNotice)); ?></p>
    </div>

    <?php if (!empty($backupUrls)): ?>
    <div class="content-card">
        <h2 class="content-title">🔗 备用访问地址</h2>
        <ul class="link-list">
            <?php 
            $icons = ['1-circle', '2-circle', '3-circle', '4-circle', '5-circle', '6-circle', '7-circle', '8-circle', '9-circle'];
            $index = 0;
            foreach ($backupUrls as $item): 
                $name = $item['name'] ?? '备用地址 ' . ($index + 1);
                $url = $item['url'] ?? '';
                if (empty($url)) continue;
                $icon = $icons[$index % count($icons)];
                $index++;
            ?>
            <li class="link-item">
                <div class="link-header">
                    <div class="link-icon"><i class="bi bi-<?php echo $icon; ?>"></i></div>
                    <span class="link-name"><?php echo htmlspecialchars($name); ?></span>
                </div>
                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="link-url">
                    <?php echo htmlspecialchars($url); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="content-card">
        <h2 class="content-title">📱 社交媒体</h2>
        <ul class="link-list">
            <li class="link-item">
                <div class="link-header">
                    <div class="link-icon"><i class="bi bi-sina-weibo"></i></div>
                    <span class="link-name">官方微博</span>
                </div>
                <a href="<?php echo htmlspecialchars($weiboUrl); ?>" target="_blank" class="link-url">
                    <?php echo htmlspecialchars($weiboText); ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="text-center mt-4">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

