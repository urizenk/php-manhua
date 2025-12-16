<?php
/**
 * F10-详情页模块
 * 展示漫画详细信息
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

// 获取漫画ID
$mangaId = $GLOBALS['id'] ?? 0;

// 查询漫画详情
$manga = $db->queryOne(
    "SELECT m.*, t.type_name, t.type_code, tg.tag_name 
     FROM mangas m 
     LEFT JOIN manga_types t ON m.type_id = t.id 
     LEFT JOIN tags tg ON m.tag_id = tg.id 
     WHERE m.id = ?",
    [$mangaId]
);

if (!$manga) {
    header('Location: /');
    exit;
}

$pageTitle = htmlspecialchars($manga['title']) . ' - 海の小窝';

// 查询相关章节（如果有）
$chapters = $db->query(
    "SELECT * FROM manga_chapters WHERE manga_id = ? ORDER BY sort_order ASC, id ASC",
    [$mangaId]
);

// 解析漫画标签
$mangaTags = [];
if (!empty($manga['manga_tags'])) {
    $mangaTags = array_map('trim', explode('，', $manga['manga_tags']));
    // 也支持英文逗号
    if (count($mangaTags) === 1) {
        $mangaTags = array_map('trim', explode(',', $manga['manga_tags']));
    }
}

// 判断简介是否过长（超过200字）
$description = $manga['description'] ?? '';
$isLongDescription = mb_strlen($description) > 200;
$shortDescription = $isLongDescription ? mb_substr($description, 0, 200) . '...' : $description;

$customCss = '
<style>
    body {
        background: #FFF8DC;
        min-height: 100vh;
    }
    .content-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px 15px;
    }
    .detail-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .cover-section {
        position: relative;
    }
    .cover-image {
        width: 100%;
        max-height: 350px;
        object-fit: cover;
        display: block;
    }
    .no-cover {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 4rem;
    }
    .detail-body {
        padding: 25px 20px;
    }
    .manga-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    .manga-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        background: #f5f5f5;
        border-radius: 15px;
        font-size: 0.85rem;
        color: #666;
    }
    .meta-item i {
        color: #999;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-serializing {
        background: #e3f2fd;
        color: #1565c0;
    }
    .status-completed {
        background: #e8f5e9;
        color: #2e7d32;
    }
    .manga-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    .manga-tag {
        display: inline-block;
        padding: 5px 14px;
        background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
        color: #1565C0;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .section-title {
        font-size: 1rem;
        font-weight: bold;
        color: #333;
        margin: 20px 0 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title i {
        color: #FF6B35;
    }
    .description-text {
        color: #555;
        line-height: 1.8;
        font-size: 0.95rem;
        margin-bottom: 15px;
    }
    .description-full {
        display: none;
    }
    .description-toggle {
        color: #1976D2;
        cursor: pointer;
        font-size: 0.9rem;
    }
    .description-toggle:hover {
        text-decoration: underline;
    }
    .resource-section {
        background: #FFF8E1;
        border-radius: 12px;
        padding: 15px;
        margin: 20px 0;
    }
    .resource-link {
        display: block;
        padding: 12px 15px;
        background: white;
        border-radius: 10px;
        margin-bottom: 10px;
        text-decoration: none;
        color: #1976D2;
        font-size: 0.95rem;
        word-break: break-all;
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
    }
    .resource-link:hover {
        background: #E3F2FD;
        border-color: #1976D2;
    }
    .extract-code {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: #FFF3E0;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #E65100;
        margin-top: 10px;
    }
    .extract-code-value {
        font-weight: bold;
        font-family: monospace;
        font-size: 1rem;
        background: white;
        padding: 2px 10px;
        border-radius: 5px;
    }
    .chapter-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .chapter-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
    }
    .chapter-item:last-child {
        border-bottom: none;
    }
    .chapter-item:hover {
        background: #f8f9ff;
    }
    .chapter-link {
        text-decoration: none;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.95rem;
    }
    .chapter-link:hover {
        color: #1976D2;
    }
    .chapter-link i {
        color: #999;
    }
    .bottom-back {
        text-align: center;
        margin-top: 30px;
        padding-bottom: 20px;
    }
    .bottom-back-btn {
        display: inline-block;
        background: white;
        color: #FF6B35;
        border: 2px solid #FF6B35;
        padding: 10px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .bottom-back-btn:hover {
        background: #FF6B35;
        color: white;
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 详情卡片 -->
    <div class="detail-card">
        <!-- 封面图片 -->
        <div class="cover-section">
            <?php if ($manga['cover_image']): ?>
                <img src="<?php echo htmlspecialchars($manga['cover_image']); ?>" 
                     alt="<?php echo htmlspecialchars($manga['title']); ?>"
                     class="cover-image"
                     style="object-position: <?php echo htmlspecialchars($manga['cover_position'] ?? 'center'); ?>;">
            <?php else: ?>
                <div class="no-cover">
                    <i class="bi bi-book"></i>
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-body">
            <!-- 标题 -->
            <h1 class="manga-title"><?php echo htmlspecialchars($manga['title']); ?></h1>

            <!-- 元信息 -->
            <div class="manga-meta">
                <span class="meta-item">
                    <i class="bi bi-folder"></i>
                    <?php echo htmlspecialchars($manga['type_name']); ?>
                </span>
                
                <?php if ($manga['status']): ?>
                    <span class="status-badge status-<?php echo $manga['status']; ?>">
                        <?php echo $manga['status'] === 'serializing' ? '连载中' : '已完结'; ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- 漫画标签 -->
            <?php if (!empty($mangaTags)): ?>
                <div class="manga-tags">
                    <?php foreach ($mangaTags as $tag): ?>
                        <?php if (trim($tag)): ?>
                            <span class="manga-tag"><?php echo htmlspecialchars($tag); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 简介 -->
            <?php if ($description): ?>
                <div class="section-title"><i class="bi bi-file-text"></i> 简介</div>
                <div class="description-text" id="descriptionShort">
                    <?php echo nl2br(htmlspecialchars($shortDescription)); ?>
                    <?php if ($isLongDescription): ?>
                        <span class="description-toggle" onclick="toggleDescription()">展开全部</span>
                    <?php endif; ?>
                </div>
                <?php if ($isLongDescription): ?>
                    <div class="description-text description-full" id="descriptionFull">
                        <?php echo nl2br(htmlspecialchars($description)); ?>
                        <span class="description-toggle" onclick="toggleDescription()">收起</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- 资源链接 -->
            <?php if ($manga['resource_link'] || $manga['extract_code']): ?>
                <div class="section-title"><i class="bi bi-link-45deg"></i> 资源链接</div>
                <div class="resource-section">
                    <?php if ($manga['resource_link']): ?>
                        <?php 
                        // 分割多行链接
                        $links = preg_split('/[\r\n]+/', trim($manga['resource_link']));
                        foreach ($links as $link): 
                            $link = trim($link);
                            if (empty($link)) continue;
                            // 检查是否是有效的URL
                            $isUrl = preg_match('/^https?:\/\//i', $link);
                        ?>
                            <?php if ($isUrl): ?>
                                <a href="<?php echo htmlspecialchars($link); ?>" 
                                   target="_blank" 
                                   class="resource-link">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    <?php echo htmlspecialchars($link); ?>
                                </a>
                            <?php else: ?>
                                <div class="resource-link" style="color: #666; cursor: default;">
                                    <i class="bi bi-info-circle"></i>
                                    <?php echo htmlspecialchars($link); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($manga['extract_code']): ?>
                        <div class="extract-code">
                            <i class="bi bi-key"></i>
                            提取码：
                            <span class="extract-code-value"><?php echo htmlspecialchars($manga['extract_code']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 章节列表 -->
    <?php if (!empty($chapters)): ?>
    <div class="detail-card" style="margin-top: 20px;">
        <div class="detail-body">
            <div class="section-title"><i class="bi bi-list-ul"></i> 章节列表</div>
            <ul class="chapter-list">
                <?php foreach ($chapters as $chapter): ?>
                    <li class="chapter-item">
                        <a href="<?php echo htmlspecialchars($chapter['chapter_link']); ?>" 
                           target="_blank" 
                           class="chapter-link">
                            <span><?php echo htmlspecialchars($chapter['chapter_title']); ?></span>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="bottom-back">
        <a href="javascript:history.back()" class="bottom-back-btn">
            <i class="bi bi-arrow-left"></i> 返回上一页
        </a>
    </div>
</div>

<script>
function toggleDescription() {
    var shortEl = document.getElementById('descriptionShort');
    var fullEl = document.getElementById('descriptionFull');
    if (shortEl && fullEl) {
        if (fullEl.style.display === 'none' || fullEl.style.display === '') {
            shortEl.style.display = 'none';
            fullEl.style.display = 'block';
        } else {
            shortEl.style.display = 'block';
            fullEl.style.display = 'none';
        }
    }
}
</script>

<?php
// 增加浏览次数
$db->execute("UPDATE mangas SET views = views + 1 WHERE id = ?", [$mangaId]);

include APP_PATH . '/views/layouts/footer.php';
?>
