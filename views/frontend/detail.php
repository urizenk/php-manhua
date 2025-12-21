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
        background: linear-gradient(180deg, #FFF8DC 0%, #FFFEF5 100%) !important;
        min-height: 100vh;
    }
    .content-wrapper {
        max-width: 600px;
        margin: 0 auto;
        padding: 15px;
    }
    .detail-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(255,107,53,0.12);
    }
    /* 封面区域 */
    .cover-section {
        position: relative;
        background: linear-gradient(135deg, #FFE4CC 0%, #FFD4B8 100%);
    }
    .cover-image {
        width: 100%;
        height: 280px;
        object-fit: cover;
        display: block;
    }
    .no-cover {
        height: 180px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3.5rem;
    }
    /* 主体内容 */
    .detail-body {
        padding: 18px 16px 22px;
    }
    .manga-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2D2D2D;
        margin-bottom: 12px;
        line-height: 1.45;
    }
    /* 元信息标签 */
    .manga-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        background: #FFF5EB;
        border-radius: 16px;
        font-size: 0.78rem;
        color: #FF8C42;
        border: 1px solid #FFE4CC;
    }
    .meta-item i {
        font-size: 0.8rem;
    }
    /* 漫画标签 - 莫兰迪配色 */
    .manga-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 0;
    }
    .manga-tag {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 0.78rem;
        font-weight: 500;
    }
    .manga-tag:nth-child(6n+1) { background: #F2E1E1; color: #9B6B6B; }
    .manga-tag:nth-child(6n+2) { background: #FDF3E7; color: #A68B5B; }
    .manga-tag:nth-child(6n+3) { background: #E1ECF2; color: #5B7B9B; }
    .manga-tag:nth-child(6n+4) { background: #E5F2E1; color: #5B9B6B; }
    .manga-tag:nth-child(6n+5) { background: #EDE1F2; color: #7B5B9B; }
    .manga-tag:nth-child(6n+6) { background: #F2EDE1; color: #8B7B5B; }
    /* 分区标题 */
    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #555;
        margin: 18px 0 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .section-title i {
        color: #FF7744;
        font-size: 0.95rem;
    }
    /* 简介 */
    .description-text {
        color: #666;
        line-height: 1.75;
        font-size: 0.88rem;
        background: #FAFAFA;
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 0;
    }
    .description-full {
        display: none;
    }
    .description-toggle {
        color: #FF7744;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 500;
        margin-left: 5px;
    }
    .description-toggle:hover {
        text-decoration: underline;
    }
    /* 资源链接区域 */
    .resource-section {
        background: #FFFBF5;
        border-radius: 12px;
        padding: 12px;
        margin: 12px 0;
        border: 1px solid #FFE8D0;
    }
    .resource-item {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        background: white;
        border-radius: 10px;
        margin-bottom: 8px;
        text-decoration: none;
        color: #0066CC;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: 1px solid #E8E8E8;
    }
    .resource-item:last-of-type {
        margin-bottom: 0;
    }
    .resource-item:hover {
        background: #F0F7FF;
        border-color: #0066CC;
    }
    .resource-item i {
        flex-shrink: 0;
        margin-right: 10px;
        font-size: 1rem;
    }
    .resource-item .link-text {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .resource-item .link-full {
        display: none;
    }
    .resource-item.expanded .link-text {
        white-space: normal;
        word-break: break-all;
    }
    .link-toggle {
        flex-shrink: 0;
        margin-left: 8px;
        padding: 4px 8px;
        background: #F5F5F5;
        border: none;
        border-radius: 4px;
        font-size: 0.75rem;
        color: #666;
        cursor: pointer;
    }
    .link-toggle:hover {
        background: #E8E8E8;
    }
    /* 提取码 */
    .extract-code-box {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        background: white;
        border-radius: 10px;
        border: 1px dashed #FFB366;
        margin-top: 8px;
    }
    .extract-label {
        color: #E65100;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }
    .extract-label i {
        margin-right: 6px;
    }
    .extract-value {
        font-family: "SF Mono", "Monaco", "Consolas", monospace;
        font-size: 0.95rem;
        font-weight: 700;
        color: #D84315;
        background: #FFF3E0;
        padding: 4px 10px;
        border-radius: 6px;
        margin-left: 10px;
        letter-spacing: 0.5px;
    }
    /* 章节列表 */
    .chapter-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .chapter-item {
        padding: 12px 0;
        border-bottom: 1px solid #F5F5F5;
    }
    .chapter-item:last-child {
        border-bottom: none;
    }
    .chapter-link {
        text-decoration: none;
        color: #444;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.9rem;
        padding: 8px 12px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .chapter-link:hover {
        background: #FFF5EB;
        color: #FF7744;
    }
    .chapter-link i {
        color: #CCC;
        transition: color 0.2s ease;
    }
    .chapter-link:hover i {
        color: #FF7744;
    }
    /* 返回按钮 */
    .bottom-back {
        text-align: center;
        margin-top: 20px;
        padding-bottom: 25px;
    }
    .bottom-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        color: #FF7744;
        border: 2px solid #FF7744;
        padding: 10px 28px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: 0 3px 12px rgba(255,119,68,0.12);
    }
    .bottom-back-btn:hover {
        background: linear-gradient(135deg, #FF9966 0%, #FF7744 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 18px rgba(255,119,68,0.25);
    }
    /* 移动端适配 */
    @media (max-width: 480px) {
        .content-wrapper {
            padding: 10px;
        }
        .cover-image {
            height: 220px;
        }
        .detail-body {
            padding: 15px 15px 20px;
        }
        .manga-title {
            font-size: 1.2rem;
        }
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 详情卡片 -->
    <div class="detail-card">
        <!-- 封面图片 -->
        <?php 
        $coverPath = $manga['cover_image'] ?? '';
        $hasCover = !empty($coverPath);
        // 处理封面路径，确保路径正确
        if ($hasCover) {
            // 移除开头的 /public 前缀（如果存在），因为 public 是 web 根目录
            $coverPath = preg_replace('#^/?public/#', '/', $coverPath);
            // 确保路径以 / 开头
            if (strpos($coverPath, '/') !== 0 && strpos($coverPath, 'http') !== 0) {
                $coverPath = '/' . $coverPath;
            }
        }
        ?>
        <div class="cover-section">
            <?php if ($hasCover): ?>
                <img src="<?php echo htmlspecialchars($coverPath); ?>" 
                     alt="<?php echo htmlspecialchars($manga['title']); ?>"
                     class="cover-image"
                     onerror="this.parentElement.innerHTML='<div class=\'no-cover\'><i class=\'bi bi-image\'></i></div>'"
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
                <?php if (!empty($manga['type_name'])): ?>
                    <span class="meta-item">
                        <i class="bi bi-folder2"></i>
                        <?php echo htmlspecialchars($manga['type_name']); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($manga['tag_name'])): ?>
                    <span class="meta-item">
                        <i class="bi bi-tag"></i>
                        <?php echo htmlspecialchars($manga['tag_name']); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($manga['status'])): ?>
                    <span class="meta-item" style="<?php echo $manga['status'] === 'completed' ? 'background:#E8F5E9;color:#2E7D32;border-color:#C8E6C9;' : 'background:#E3F2FD;color:#1565C0;border-color:#BBDEFB;'; ?>">
                        <i class="bi bi-<?php echo $manga['status'] === 'completed' ? 'check-circle' : 'play-circle'; ?>"></i>
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
                        <span class="description-toggle" onclick="toggleDescription()">展开全部 ▼</span>
                    <?php endif; ?>
                </div>
                <?php if ($isLongDescription): ?>
                    <div class="description-text description-full" id="descriptionFull">
                        <?php echo nl2br(htmlspecialchars($description)); ?>
                        <span class="description-toggle" onclick="toggleDescription()">收起 ▲</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- 资源链接 -->
            <?php if (!empty($manga['resource_link']) || !empty($manga['extract_code'])): ?>
                <div class="section-title"><i class="bi bi-link-45deg"></i> 资源链接</div>
                <div class="resource-section">
                    <?php if (!empty($manga['resource_link'])): ?>
                        <?php 
                        // 分割多行链接
                        $links = preg_split('/[\r\n]+/', trim($manga['resource_link']));
                        $linkIndex = 0;
                        foreach ($links as $link): 
                            $link = trim($link);
                            if (empty($link)) continue;
                            $linkIndex++;
                            // 检查是否是有效的URL
                            $isUrl = preg_match('/^https?:\/\//i', $link);
                            // 显示简短版本
                            $displayLink = $link;
                            if (mb_strlen($link) > 45) {
                                $displayLink = mb_substr($link, 0, 42) . '...';
                            }
                        ?>
                            <?php if ($isUrl): ?>
                                <a href="<?php echo htmlspecialchars($link); ?>" 
                                   target="_blank" 
                                   class="resource-item"
                                   title="<?php echo htmlspecialchars($link); ?>">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    <span class="link-text"><?php echo htmlspecialchars($displayLink); ?></span>
                                </a>
                            <?php else: ?>
                                <div class="resource-item" style="color: #666; cursor: default; border-style: dashed;">
                                    <i class="bi bi-info-circle"></i>
                                    <span class="link-text"><?php echo htmlspecialchars($link); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($manga['extract_code'])): ?>
                        <div class="extract-code-box">
                            <span class="extract-label">
                                <i class="bi bi-key-fill"></i>提取码：
                            </span>
                            <span class="extract-value"><?php echo htmlspecialchars($manga['extract_code']); ?></span>
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
