<?php
/**
 * F6-失效反馈模块（固定内容）
 */

// 全局配置（用于微博链接等）
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$pageTitle = '资源失效反馈';

// 从数据库读取配置
$configRows = $db->query("SELECT config_key, config_value FROM site_config WHERE config_key IN ('weibo_url', 'weibo_text', 'feedback_qq', 'feedback_email', 'feedback_notice')");
$configs = [];
foreach ($configRows as $row) {
    $configs[$row['config_key']] = $row['config_value'];
}
$weiboUrl  = $configs['weibo_url'] ?? 'https://weibo.com/';
$weiboText = $configs['weibo_text'] ?? '微博@资源小站';
$feedbackQQ = $configs['feedback_qq'] ?? '';
$feedbackEmail = $configs['feedback_email'] ?? '';
$feedbackNotice = $configs['feedback_notice'] ?? '如果您发现资源链接失效、无法访问或其他问题，请通过以下方式联系我们。';

$customCss = '
<style>
    .content-wrapper {
        max-width: 800px;
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
        margin-bottom: 20px;
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
    .contact-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .contact-item {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    .contact-item:last-child {
        border-bottom: none;
    }
    .contact-item:hover {
        background: #f8f9ff;
    }
    .contact-icon {
        width: 60px;
        height: 60px;
        background: #1976D2;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-right: 20px;
    }
    .contact-info {
        flex: 1;
    }
    .contact-name {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    .contact-detail {
        color: #666;
        font-size: 0.95rem;
    }
    .contact-link {
        color: #1976D2;
        text-decoration: none;
        font-weight: bold;
    }
    .contact-link:hover {
        text-decoration: underline;
    }
    .notice-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
    }
    .notice-title {
        font-weight: bold;
        color: #856404;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }
    .notice-text {
        color: #856404;
        margin: 0;
        line-height: 1.6;
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
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">💬 失效反馈</h1>
        <p class="page-subtitle">资源失效报告与问题反馈</p>
    </div>

    <!-- 提示信息 -->
    <div class="notice-box">
        <div class="notice-title">📢 反馈须知</div>
        <p class="notice-text"><?php echo nl2br(htmlspecialchars($feedbackNotice)); ?></p>
    </div>

    <!-- 联系方式 -->
    <div class="content-card">
        <h2 class="content-title">📞 联系方式</h2>
        <ul class="contact-list">
            <?php if ($weiboUrl): ?>
            <li class="contact-item">
                <div class="contact-icon">
                    <i class="bi bi-sina-weibo"></i>
                </div>
                <div class="contact-info">
                    <div class="contact-name">新浪微博</div>
                    <div class="contact-detail">
                        关注：
                        <a href="<?php echo htmlspecialchars($weiboUrl); ?>" target="_blank" class="contact-link">
                            <?php echo htmlspecialchars($weiboText); ?>
                        </a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($feedbackQQ): ?>
            <li class="contact-item">
                <div class="contact-icon">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="contact-info">
                    <div class="contact-name">QQ群</div>
                    <div class="contact-detail">
                        群号：<span class="contact-link"><?php echo htmlspecialchars($feedbackQQ); ?></span>
                    </div>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($feedbackEmail): ?>
            <li class="contact-item">
                <div class="contact-icon">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="contact-info">
                    <div class="contact-name">邮箱反馈</div>
                    <div class="contact-detail">
                        发送至：
                        <a href="mailto:<?php echo htmlspecialchars($feedbackEmail); ?>" class="contact-link"><?php echo htmlspecialchars($feedbackEmail); ?></a>
                    </div>
                </div>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- 常见问题 -->
    <div class="content-card">
        <h2 class="content-title">❓ 常见问题</h2>
        <div style="line-height: 2;">
            <p><strong>Q: 资源链接打不开怎么办？</strong></p>
            <p class="text-muted">A: 请检查网络连接，或尝试更换浏览器。如仍无法访问，请联系我们反馈。</p>
            
            <p class="mt-3"><strong>Q: 需要密码的资源如何获取？</strong></p>
            <p class="text-muted">A: 密码一般在资源详情页或相关说明中提供，如未找到请联系管理员。</p>
            
            <p class="mt-3"><strong>Q: 多久会处理失效资源？</strong></p>
            <p class="text-muted">A: 我们会在收到反馈后的 24 小时内检查并更新失效资源。</p>
        </div>
    </div>

    <!-- 返回按钮 -->
    <div class="text-center mt-4">
        <a href="/" class="back-btn">
            返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
