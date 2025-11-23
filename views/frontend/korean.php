<?php
/**
 * F3-韩漫合集模块
 * 分类标签 → 连载/完结分类 → 详情页
 */
$pageTitle = '韩漫合集 - 海の小窝';

// 获取韩漫合集的类型ID
$koreanType = $db->queryOne("SELECT * FROM manga_types WHERE type_code = ?", ['korean_collection']);
if (!$koreanType) {
    echo "韩漫合集配置错误";
    exit;
}

// 获取当前选中的标签和状态
$selectedTag = $_GET['tag'] ?? 'all';
$selectedStatus = $_GET['status'] ?? 'all';

// 获取所有分类标签
$tags = $db->query(
    "SELECT * FROM tags WHERE type_id = ? AND tag_name != '未分类' ORDER BY sort_order ASC, id ASC",
    [$koreanType['id']]
);

// 构建查询条件
$where = "m.type_id = ?";
$params = [$koreanType['id']];

if ($selectedTag !== 'all') {
    $where .= " AND t.tag_name = ?";
    $params[] = $selectedTag;
}

if ($selectedStatus !== 'all') {
    $where .= " AND m.status = ?";
    $params[] = $selectedStatus;
}

// 获取韩漫列表
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE {$where}
     ORDER BY m.sort_order DESC, m.created_at DESC",
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
        color: #2196F3;
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
    .filter-group {
        margin-bottom: 20px;
    }
    .filter-group:last-child {
        margin-bottom: 0;
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
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 25px;
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
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    .manga-cover {
        width: 100%;
        height: 280px;
        object-fit: cover;
        background: #e0e0e0;
        position: relative;
    }
    .manga-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .manga-status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        background: rgba(255, 255, 255, 0.95);
    }
    .status-serializing {
        color: #3498db;
    }
    .status-completed {
        color: #2ecc71;
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
    }
    .manga-tag {
        font-size: 0.85rem;
        color: #999;
    }
    .manga-tag i {
        margin-right: 5px;
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
    .no-cover {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.8);
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">📚 韩漫合集</h1>
        <p class="page-subtitle">精选韩漫作品 · 分类筛选查看</p>
    </div>

    <!-- 筛选区域 -->
    <div class="filter-section">
        <!-- 分类标签筛选 -->
        <div class="filter-group">
            <label class="filter-label">📑 分类标签</label>
            <div class="filter-tags">
                <a href="?status=<?php echo $selectedStatus; ?>" 
                   class="filter-tag <?php echo $selectedTag === 'all' ? 'active' : ''; ?>">
                    全部
                </a>
                <?php foreach ($tags as $tag): ?>
                    <a href="?tag=<?php echo urlencode($tag['tag_name']); ?>&status=<?php echo $selectedStatus; ?>" 
                       class="filter-tag <?php echo $selectedTag === $tag['tag_name'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($tag['tag_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 状态筛选 -->
        <div class="filter-group">
            <label class="filter-label">📊 连载状态</label>
            <div class="filter-tags">
                <a href="?tag=<?php echo $selectedTag; ?>" 
                   class="filter-tag <?php echo $selectedStatus === 'all' ? 'active' : ''; ?>">
                    全部
                </a>
                <a href="?tag=<?php echo $selectedTag; ?>&status=serializing" 
                   class="filter-tag <?php echo $selectedStatus === 'serializing' ? 'active' : ''; ?>">
                    连载中
                </a>
                <a href="?tag=<?php echo $selectedTag; ?>&status=completed" 
                   class="filter-tag <?php echo $selectedStatus === 'completed' ? 'active' : ''; ?>">
                    已完结
                </a>
            </div>
        </div>
    </div>

    <!-- 漫画网格 -->
    <?php if (empty($mangas)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>暂无符合条件的韩漫</h3>
            <p class="text-muted">试试调整筛选条件</p>
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
                            <div class="no-cover">📖</div>
                        <?php endif; ?>
                        
                        <?php if ($manga['status']): ?>
                            <span class="manga-status-badge status-<?php echo $manga['status']; ?>">
                                <?php echo $manga['status'] === 'serializing' ? '连载中' : '已完结'; ?>
                            </span>
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
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="text-center mt-5">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
