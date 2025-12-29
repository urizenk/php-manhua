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
        min-height: 100dvh;
    }
    .content-wrapper {
        max-width: 600px;
        margin: 0 auto;
        padding: 15px;
        min-height: calc(100vh - 30px);
        min-height: calc(100dvh - 30px);
        display: flex;
        flex-direction: column;
    }
    .detail-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(255,107,53,0.12);
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    /* 封面区域 - 更大的正方形展示 */
    .cover-section {
        position: relative;
        background: linear-gradient(135deg, #FFE4CC 0%, #FFD4B8 100%);
        flex-shrink: 0;
        width: 100%;
        aspect-ratio: 1 / 1;
        max-height: 450px;
        overflow: hidden;
    }
    .cover-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .no-cover {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 4rem;
    }
    /* 主体内容 */
    .detail-body {
        padding: 18px 16px 22px;
        flex: 1;
        min-height: 300px;
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
    /* 资源信息卡片 */
    .resource-card {
        background: #FFFBF5;
        border-radius: 12px;
        border: 1px solid #FFE8D0;
        overflow: hidden;
        margin: 12px 0;
    }
    .resource-links {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .resource-link-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        background: #ffffff;
        border: 1px solid #FFE8D0;
        border-radius: 10px;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .resource-link-btn:hover {
        background: #FFF5EB;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.06);
    }
    .resource-link-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #2D2D2D;
        flex-shrink: 0;
        max-width: 45%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .resource-link-url {
        font-size: 0.78rem;
        color: #0066CC;
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: right;
    }
    .resource-notes {
        border-top: 1px solid #FFE8D0;
        padding: 12px 14px;
        color: #666;
        font-size: 0.86rem;
        line-height: 1.6;
        background: #FFFDF9;
    }
    .resource-notes .note-row {
        margin: 6px 0;
        word-break: break-all;
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
        margin-top: auto;
        padding: 25px 0;
        flex-shrink: 0;
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
        .cover-section {
            max-height: 380px;
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
        // 直接使用数据库中的封面路径（与后台保持一致）
        $coverPath = $manga['cover_image'] ?? '';
        $hasCover = !empty($coverPath);
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

            <!-- 资源链接（支持多链接模块） -->
            <?php if (!empty($manga['resource_link']) || !empty($manga['extract_code'])): ?>
                <?php
                $resourceBlockTitle = '资源链接';
                $resourceLinks = [];
                $resourceNotes = [];

                $raw = trim((string)($manga['resource_link'] ?? ''));
                if ($raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['items']) && is_array($decoded['items'])) {
                        $resourceBlockTitle = trim((string)($decoded['title'] ?? $resourceBlockTitle)) ?: $resourceBlockTitle;
                        foreach ($decoded['items'] as $item) {
                            if (!is_array($item)) {
                                continue;
                            }
                            $url = trim((string)($item['url'] ?? ''));
                            if ($url === '') {
                                continue;
                            }
                            $label = trim((string)($item['label'] ?? ''));
                            $resourceLinks[] = ['label' => $label, 'url' => $url];
                        }
                    } else {
                        $lines = preg_split('/[\r\n]+/', $raw);
                        foreach ($lines as $line) {
                            $line = trim((string)$line);
                            if ($line === '') {
                                continue;
                            }
                            if (preg_match('/^https?:\/\//i', $line)) {
                                $resourceLinks[] = ['label' => '', 'url' => $line];
                            } else {
                                $resourceNotes[] = $line;
                            }
                        }
                    }
                }
                ?>

                <div class="section-title"><?php echo htmlspecialchars($resourceBlockTitle); ?></div>
                <div class="resource-card">
                    <?php if (!empty($resourceLinks)): ?>
                        <div class="resource-links">
                            <?php foreach ($resourceLinks as $i => $item): ?>
                                <?php
                                $url = $item['url'];
                                $label = trim((string)($item['label'] ?? ''));
                                if ($label === '') {
                                    $host = parse_url($url, PHP_URL_HOST);
                                    $label = $host ? $host : ('链接' . ($i + 1));
                                }
                                ?>
                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="resource-link-btn" title="<?php echo htmlspecialchars($url); ?>">
                                    <span class="resource-link-title"><?php echo htmlspecialchars($label); ?></span>
                                    <span class="resource-link-url"><?php echo htmlspecialchars($url); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($resourceNotes) || !empty($manga['extract_code'])): ?>
                        <div class="resource-notes">
                            <?php foreach ($resourceNotes as $note): ?>
                                <div class="note-row"><?php echo htmlspecialchars($note); ?></div>
                            <?php endforeach; ?>
                            <?php if (!empty($manga['extract_code'])): ?>
                                <div class="note-row"><strong>提取码：</strong><?php echo htmlspecialchars($manga['extract_code']); ?></div>
                            <?php endif; ?>
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
            返回上一页
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
