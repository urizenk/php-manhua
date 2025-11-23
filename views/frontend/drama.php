<?php
/**
 * F6-广播剧合集模块（固定内容）
 */
$pageTitle = '广播剧合集 - 海の小窝';

$customCss = '
<style>
    .content-wrapper {
        max-width: 1000px;
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
    .resource-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .resource-item {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }
    .resource-item:last-child {
        border-bottom: none;
    }
    .resource-item:hover {
        background: #f8f9ff;
        padding-left: 25px;
    }
    .resource-link {
        text-decoration: none;
        color: #333;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .resource-link:hover {
        color: #1976D2;
    }
    .resource-icon {
        color: #1976D2;
        font-size: 1.2rem;
        margin-left: 10px;
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
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">🎧 广播剧合集</h1>
        <p class="page-subtitle">精彩广播剧音频资源</p>
    </div>

    <!-- 资源内容 -->
    <div class="content-card">
        <h2 class="content-title">🎙️ 广播剧列表</h2>
        <ul class="resource-list">
            <li class="resource-item">
                <a href="https://pan.quark.cn/drama1" target="_blank" class="resource-link">
                    <span>热门广播剧合集（第一辑）</span>
                    <i class="bi bi-box-arrow-up-right resource-icon"></i>
                </a>
            </li>
            <li class="resource-item">
                <a href="https://pan.quark.cn/drama2" target="_blank" class="resource-link">
                    <span>经典广播剧合集（第二辑）</span>
                    <i class="bi bi-box-arrow-up-right resource-icon"></i>
                </a>
            </li>
            <li class="resource-item">
                <a href="https://pan.quark.cn/drama3" target="_blank" class="resource-link">
                    <span>最新广播剧合集（更新中）</span>
                    <i class="bi bi-box-arrow-up-right resource-icon"></i>
                </a>
            </li>
        </ul>
    </div>

    <!-- 使用说明 -->
    <div class="content-card">
        <h2 class="content-title">📝 收听说明</h2>
        <p class="text-muted">点击上方链接即可在线收听或下载音频文件。建议使用耳机获得更好的收听体验。</p>
    </div>

    <!-- 返回按钮 -->
    <div class="text-center mt-4">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
