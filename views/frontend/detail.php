<?php
/**
 * F10-详情页模块
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

/**
 * F8-详情页模块
 * 展示漫画详细信息
 */

// 获取漫画ID
$mangaId = $id ?? 0;

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

// 判断是否需要奶黄色背景（单集汇总类型）
$isSpecialBg = in_array($manga['type_code'] ?? '', ['daily_update', 'short_complete']);

$customCss = '
<style>
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .detail-card {
        background: ' . ($isSpecialBg ? '#FFF8DC' : 'white') . ';
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .detail-header {
        padding: 40px;
        text-align: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }
    .manga-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
    }
    .manga-meta {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    .meta-item {
        display: flex;
        align-items: center;
        color: #666;
        font-size: 1rem;
    }
    .meta-item i {
        margin-right: 8px;
        color: #1976D2;
        font-size: 1.2rem;
    }
    .detail-body {
        padding: 40px;
    }
    .cover-section {
        display: flex;
        gap: 40px;
        margin-bottom: 40px;
    }
    .cover-image-wrapper {
        flex-shrink: 0;
    }
    .cover-image {
        width: 300px;
        height: 400px;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .cover-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .no-cover {
        background: #1976D2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 5rem;
    }
    .manga-info {
        flex: 1;
    }
    .info-section {
        margin-bottom: 25px;
    }
    .info-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    .info-content {
        color: #666;
        line-height: 1.8;
    }
    .status-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: bold;
    }
    .status-serializing {
        background: #d1ecf1;
        color: #0c5460;
    }
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    .chapter-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }
    .section-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    .chapter-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
    .chapter-item {
        padding: 15px;
        border: 2px solid #f0f0f0;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .chapter-item:hover {
        border-color: #1976D2;
        background: #f8f9ff;
        transform: translateX(5px);
    }
    .chapter-link {
        text-decoration: none;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chapter-link:hover {
        color: #1976D2;
    }
    .resource-btn {
        display: inline-block;
        background: #1976D2;
        color: white;
        padding: 15px 40px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    .resource-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(25, 118, 210, 0.4);
        color: white;
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
    @media (max-width: 768px) {
        .cover-section {
            flex-direction: column;
        }
        .cover-image {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 详情卡片 -->
    <div class="detail-card">
        <div class="detail-header">
            <h1 class="manga-title"><?php echo htmlspecialchars($manga['title']); ?></h1>
            <div class="manga-meta">
                <div class="meta-item">
                    <i class="bi bi-folder"></i>
                    <span><?php echo htmlspecialchars($manga['type_name']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-tag"></i>
                    <span><?php echo htmlspecialchars($manga['tag_name'] ?? '未分类'); ?></span>
                </div>
                <?php if ($manga['status']): ?>
                <div class="meta-item">
                    <i class="bi bi-info-circle"></i>
                    <span class="status-badge status-<?php echo $manga['status']; ?>">
                        <?php echo $manga['status'] === 'serializing' ? '连载中' : '已完结'; ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="meta-item">
                    <i class="bi bi-eye"></i>
                    <span><?php echo number_format($manga['views']); ?> 次浏览</span>
                </div>
            </div>
        </div>

        <div class="detail-body">
            <div class="cover-section">
                <?php if ($manga['cover_image']): ?>
                <div class="cover-image-wrapper">
                    <div class="cover-image">
                        <img src="<?php echo htmlspecialchars($manga['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($manga['title']); ?>"
                             style="object-position: <?php echo htmlspecialchars($manga['cover_position'] ?? 'center'); ?>;">
                    </div>
                </div>
                <?php endif; ?>

                <div class="manga-info">
                    <?php if ($manga['description']): ?>
                    <div class="info-section">
                        <div class="info-title">📖 简介</div>
                        <div class="info-content">
                            <?php echo nl2br(htmlspecialchars($manga['description'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($manga['resource_link']): ?>
                    <div class="info-section">
                        <div class="info-title">🔗 资源链接</div>
                        <div class="info-content">
                            <a href="<?php echo htmlspecialchars($manga['resource_link']); ?>" 
                               target="_blank" 
                               class="resource-btn">
                                <i class="bi bi-box-arrow-up-right"></i> 访问资源
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 章节列表 -->
    <?php if (!empty($chapters)): ?>
    <div class="chapter-section">
        <h2 class="section-title">📚 章节列表</h2>
        <div class="chapter-list">
            <?php foreach ($chapters as $chapter): ?>
                <div class="chapter-item">
                    <a href="<?php echo htmlspecialchars($chapter['chapter_link']); ?>" 
                       target="_blank" 
                       class="chapter-link">
                        <span><?php echo htmlspecialchars($chapter['chapter_title']); ?></span>
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="text-center mt-4">
        <a href="javascript:history.back()" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回上一页
        </a>
    </div>
</div>

<?php
// 增加浏览次数
$db->execute("UPDATE mangas SET views = views + 1 WHERE id = ?", [$mangaId]);

include APP_PATH . '/views/layouts/footer.php';
?>
