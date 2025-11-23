<?php
/**
 * F6-防走丢模块（固定内容）
 */
$pageTitle = '防走丢 - 海の小窝';

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
        background: white;
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
        color: white;
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
        background: white;
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
        color: white;
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
        <p class="notice-text">
            为防止主站无法访问，请收藏以下备用地址。建议将地址保存到浏览器书签或记事本中。
        </p>
    </div>

    <div class="content-card">
        <h2 class="content-title">🔗 备用访问地址</h2>
        <ul class="link-list">
            <li class="link-item">
                <div class="link-header">
                    <div class="link-icon"><i class="bi bi-1-circle"></i></div>
                    <span class="link-name">备用地址1</span>
                </div>
                <a href="https://backup1.example.com" target="_blank" class="link-url">
                    https://backup1.example.com
                </a>
            </li>
            <li class="link-item">
                <div class="link-header">
                    <div class="link-icon"><i class="bi bi-2-circle"></i></div>
                    <span class="link-name">备用地址2</span>
                </div>
                <a href="https://backup2.example.com" target="_blank" class="link-url">
                    https://backup2.example.com
                </a>
            </li>
            <li class="link-item">
                <div class="link-header">
                    <div class="link-icon"><i class="bi bi-3-circle"></i></div>
                    <span class="link-name">备用地址3</span>
                </div>
                <a href="https://backup3.example.com" target="_blank" class="link-url">
                    https://backup3.example.com
                </a>
            </li>
        </ul>
    </div>

    <div class="content-card">
        <h2 class="content-title">📱 社交媒体</h2>
        <ul class="link-list">
            <li class="link-item">
                <div class="link-header">
                    <div class="link-icon"><i class="bi bi-sina-weibo"></i></div>
                    <span class="link-name">官方微博</span>
                </div>
                <a href="https://weibo.com/example" target="_blank" class="link-url">
                    @资源小站
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
