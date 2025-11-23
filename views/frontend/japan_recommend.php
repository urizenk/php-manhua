<?php
/**
 * F5-日漫推荐模块
 * 封面图片展示（18本/页）+ 分页功能
 */
$pageTitle = '日漫推荐 - 海の小窝';

// 获取日漫推荐的类型ID
$japanType = $db->queryOne("SELECT * FROM manga_types WHERE type_code = ?", ['japan_recommend']);
if (!$japanType) {
    echo "日漫推荐配置错误";
    exit;
}

// 获取当前页码
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 18;

// 获取当前选中的标签
$selectedTag = $_GET['tag'] ?? 'all';

// 获取所有作者标签
$tags = $db->query(
    "SELECT * FROM tags WHERE type_id = ? AND tag_name != '未分类' ORDER BY sort_order ASC, id ASC",
    [$japanType['id']]
);

// 构建查询条件
$where = "m.type_id = ?";
$params = [$japanType['id']];

if ($selectedTag !== 'all') {
    $where .= " AND t.tag_name = ?";
    $params[] = $selectedTag;
}

// 获取总数
$countSql = "SELECT COUNT(*) as total FROM mangas m LEFT JOIN tags t ON m.tag_id = t.id WHERE {$where}";
$totalResult = $db->queryOne($countSql, $params);
$total = $totalResult['total'] ?? 0;
$totalPages = ceil($total / $perPage);

// 获取当前页的漫画
$offset = ($page - 1) * $perPage;
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE {$where}
     ORDER BY m.sort_order DESC, m.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

$customCss = '
<style>
    .content-wrapper {
        max-width: 1400px;
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
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }
    .filter-label {
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .filter-tag {
        padding: 8px 20px;
        border-radius: 20px;
        background: #f0f0f0;
        color: #666;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .filter-tag:hover {
        background: #1976D2;
        color: white;
        transform: translateY(-2px);
    }
    .filter-tag.active {
        background: #1976D2;
        color: white;
        font-weight: bold;
    }
    .manga-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 30px;
        margin-top: 20px;
    }
    .manga-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .manga-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .manga-cover {
        width: 100%;
        height: 300px;
        background: #e0e0e0;
        position: relative;
        overflow: hidden;
    }
    .manga-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .manga-card:hover .manga-cover img {
        transform: scale(1.1);
    }
    .no-cover {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: rgba(255, 255, 255, 0.8);
        height: 100%;
    }
    .manga-info {
        padding: 15px;
    }
    .manga-title {
        font-size: 1.05rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 48px;
    }
    .manga-tag {
        font-size: 0.85rem;
        color: #999;
        display: flex;
        align-items: center;
    }
    .manga-tag i {
        margin-right: 5px;
    }
    .pagination-wrapper {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-top: 30px;
    }
    .pagination {
        justify-content: center;
        margin: 0;
    }
    .page-link {
        border-radius: 10px;
        margin: 0 5px;
        border: 2px solid #f0f0f0;
        color: #1976D2;
        font-weight: bold;
    }
    .page-link:hover {
        background: #1976D2;
        color: white;
        border-color: #1976D2;
    }
    .page-item.active .page-link {
        background: #1976D2;
        border-color: #1976D2;
    }
    .page-info {
        text-align: center;
        color: #666;
        margin-bottom: 15px;
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
        background: white;
        border-radius: 15px;
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">🎌 日漫推荐</h1>
        <p class="page-subtitle">精品日漫推荐 · 每页展示18本</p>
    </div>

    <!-- 标签筛选 -->
    <?php if (!empty($tags)): ?>
    <div class="filter-section">
        <label class="filter-label">🏷️ 作者/分类标签</label>
        <div class="filter-tags">
            <a href="?page=1" 
               class="filter-tag <?php echo $selectedTag === 'all' ? 'active' : ''; ?>">
                全部
            </a>
            <?php foreach ($tags as $tag): ?>
                <a href="?tag=<?php echo urlencode($tag['tag_name']); ?>&page=1" 
                   class="filter-tag <?php echo $selectedTag === $tag['tag_name'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($tag['tag_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 漫画网格 -->
    <?php if (empty($mangas)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>暂无日漫推荐</h3>
            <p class="text-muted">管理员还未添加任何日漫推荐</p>
        </div>
    <?php else: ?>
        <div class="manga-grid">
            <?php foreach ($mangas as $manga): ?>
                <a href="/detail/<?php echo $manga['id']; ?>" class="manga-card">
                    <div class="manga-cover">
                        <?php if ($manga['cover_image']): ?>
                            <img src="<?php echo htmlspecialchars($manga['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($manga['title']); ?>"
                                 style="object-position: <?php echo htmlspecialchars($manga['cover_position'] ?? 'center'); ?>;">
                        <?php else: ?>
                            <div class="no-cover">📚</div>
                        <?php endif; ?>
                    </div>
                    <div class="manga-info">
                        <div class="manga-title"><?php echo htmlspecialchars($manga['title']); ?></div>
                        <div class="manga-tag">
                            <i class="bi bi-tag"></i>
                            <?php echo htmlspecialchars($manga['tag_name'] ?? '未分类'); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- 分页 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper">
            <div class="page-info">
                第 <?php echo $page; ?> 页 / 共 <?php echo $totalPages; ?> 页 · 共 <?php echo $total; ?> 本漫画
            </div>
            <nav>
                <ul class="pagination">
                    <!-- 首页 -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?tag=<?php echo urlencode($selectedTag); ?>&page=1">
                                <i class="bi bi-chevron-double-left"></i> 首页
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 上一页 -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?tag=<?php echo urlencode($selectedTag); ?>&page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i> 上一页
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 页码 -->
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?tag=<?php echo urlencode($selectedTag); ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- 下一页 -->
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?tag=<?php echo urlencode($selectedTag); ?>&page=<?php echo $page + 1; ?>">
                                下一页 <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 尾页 -->
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?tag=<?php echo urlencode($selectedTag); ?>&page=<?php echo $totalPages; ?>">
                                尾页 <i class="bi bi-chevron-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
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
