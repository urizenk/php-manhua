<?php
/**
 * F2-日更板块模块
 * 按日期标签展示日更资源，最新在上
 */
$pageTitle = '日更板块 - 海の小窝';

// 获取日更板块的类型ID
$dailyType = $db->queryOne("SELECT * FROM manga_types WHERE type_code = ?", ['daily_update']);
if (!$dailyType) {
    echo "日更板块配置错误";
    exit;
}

// 获取所有日期标签（按创建时间倒序）
$tags = $db->query(
    "SELECT * FROM tags WHERE type_id = ? AND tag_name != '未分类' ORDER BY created_at DESC, sort_order DESC",
    [$dailyType['id']]
);

// 获取所有日更漫画，按标签分组
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE m.type_id = ? 
     ORDER BY m.created_at DESC",
    [$dailyType['id']]
);

// 按标签分组
$mangasByTag = [];
foreach ($mangas as $manga) {
    $tagName = $manga['tag_name'] ?? '未分类';
    if (!isset($mangasByTag[$tagName])) {
        $mangasByTag[$tagName] = [];
    }
    $mangasByTag[$tagName][] = $manga;
}

$customCss = '
<style>
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .page-header {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        text-align: center;
    }
    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #1976D2;
        margin-bottom: 10px;
    }
    .page-subtitle {
        color: #666;
        font-size: 1rem;
    }
    .tag-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .tag-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    .tag-icon {
        font-size: 1.5rem;
        margin-right: 10px;
    }
    .tag-name {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
    }
    .tag-count {
        margin-left: auto;
        background: #1976D2;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .manga-item {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }
    .manga-item:last-child {
        border-bottom: none;
    }
    .manga-item:hover {
        background: #f8f9ff;
        padding-left: 25px;
    }
    .manga-link {
        text-decoration: none;
        color: #333;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .manga-link:hover {
        color: #1976D2;
    }
    .manga-title {
        flex: 1;
    }
    .manga-icon {
        color: #999;
        font-size: 0.9rem;
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
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">📅 日更板块</h1>
        <p class="page-subtitle">每日更新资源 · 最新内容优先展示</p>
    </div>

    <?php if (empty($mangasByTag)): ?>
        <!-- 空状态 -->
        <div class="tag-section">
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>暂无日更资源</h3>
                <p class="text-muted">管理员还未添加任何日更内容</p>
            </div>
        </div>
    <?php else: ?>
        <!-- 按日期标签展示 -->
        <?php foreach ($tags as $tag): ?>
            <?php if (isset($mangasByTag[$tag['tag_name']])): ?>
                <div class="tag-section">
                    <div class="tag-header">
                        <span class="tag-icon">📆</span>
                        <span class="tag-name"><?php echo htmlspecialchars($tag['tag_name']); ?></span>
                        <span class="tag-count"><?php echo count($mangasByTag[$tag['tag_name']]); ?> 本</span>
                    </div>
                    <ul class="manga-list">
                        <?php foreach ($mangasByTag[$tag['tag_name']] as $manga): ?>
                            <li class="manga-item">
                                <?php if ($manga['resource_link']): ?>
                                    <a href="<?php echo htmlspecialchars($manga['resource_link']); ?>" 
                                       target="_blank" 
                                       class="manga-link">
                                        <span class="manga-title">
                                            <?php echo htmlspecialchars($manga['title']); ?>
                                        </span>
                                        <span class="manga-icon">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                        <span class="manga-title">
                                            <?php echo htmlspecialchars($manga['title']); ?>
                                        </span>
                                        <span class="manga-icon">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- 未分类的漫画 -->
        <?php if (isset($mangasByTag['未分类']) && !empty($mangasByTag['未分类'])): ?>
            <div class="tag-section">
                <div class="tag-header">
                    <span class="tag-icon">📋</span>
                    <span class="tag-name">未分类</span>
                    <span class="tag-count"><?php echo count($mangasByTag['未分类']); ?> 本</span>
                </div>
                <ul class="manga-list">
                    <?php foreach ($mangasByTag['未分类'] as $manga): ?>
                        <li class="manga-item">
                            <?php if ($manga['resource_link']): ?>
                                <a href="<?php echo htmlspecialchars($manga['resource_link']); ?>" 
                                   target="_blank" 
                                   class="manga-link">
                                    <span class="manga-title">
                                        <?php echo htmlspecialchars($manga['title']); ?>
                                    </span>
                                    <span class="manga-icon">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </span>
                                </a>
                            <?php else: ?>
                                <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                    <span class="manga-title">
                                        <?php echo htmlspecialchars($manga['title']); ?>
                                    </span>
                                    <span class="manga-icon">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="text-center mt-4">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
