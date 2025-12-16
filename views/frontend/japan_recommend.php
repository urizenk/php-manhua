<?php
/**
 * F5-日漫推荐模块
 * 封面图片展示（18本/页）+ 分页功能
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

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

// 获取当前选中的标签和状态
$selectedTag = $_GET['tag'] ?? 'all';
$selectedStatus = $_GET['status'] ?? 'all';

// 获取搜索关键词
$keyword = $_GET['keyword'] ?? '';

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

if ($selectedStatus !== 'all') {
    $where .= " AND m.status = ?";
    $params[] = $selectedStatus;
}

if ($keyword) {
    $where .= " AND m.title LIKE ?";
    $params[] = "%{$keyword}%";
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

// 按状态和字母分组
$groupedMangas = [
    'new' => [],
    'serializing' => [],
    'completed' => [],
    'by_letter' => []
];

foreach ($mangas as $manga) {
    // 按状态分组
    if ($manga['status'] === 'serializing') {
        $groupedMangas['serializing'][] = $manga;
    } elseif ($manga['status'] === 'completed') {
        $groupedMangas['completed'][] = $manga;
    } else {
        $groupedMangas['new'][] = $manga;
    }
    
    // 按字母分组
    $firstChar = mb_substr($manga['title'], 0, 1);
    if (preg_match('/[A-Za-z]/', $firstChar)) {
        $letter = strtoupper($firstChar);
        if (!isset($groupedMangas['by_letter'][$letter])) {
            $groupedMangas['by_letter'][$letter] = [];
        }
        $groupedMangas['by_letter'][$letter][] = $manga;
    }
}

// 对字母分组进行排序
if (!empty($groupedMangas['by_letter'])) {
    ksort($groupedMangas['by_letter']);
}

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
    }
    .page-title {
        font-size: 1.8rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
    }
    .tip-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        margin: 15px 0;
        border-radius: 5px;
        font-size: 0.9rem;
        color: #856404;
        text-align: left;
    }
    .tip-box i {
        margin-right: 8px;
    }
    .back-btn-top {
        display: inline-block;
        background: #ff5722;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        margin: 15px 0;
        transition: all 0.3s ease;
    }
    .back-btn-top:hover {
        background: #e64a19;
        color: white;
        transform: translateY(-2px);
    }
    .search-box {
        margin: 20px 0;
    }
    .search-form {
        display: flex;
        gap: 10px;
        max-width: 600px;
        margin: 0 auto;
    }
    .search-input {
        flex: 1;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 25px;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.3s ease;
    }
    .search-input:focus {
        border-color: #2196F3;
    }
    .search-btn {
        padding: 10px 30px;
        background: #ffc107;
        color: #333;
        border: none;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-btn:hover {
        background: #ffb300;
        transform: translateY(-2px);
    }
    .new-manga-btn {
        display: inline-block;
        background: #ffc107;
        color: #333;
        padding: 10px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    .new-manga-btn:hover {
        background: #ffb300;
        color: #333;
        transform: translateY(-2px);
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
        <h1 class="page-title">日漫推荐</h1>
        
        <!-- Tip提示框 -->
        <div class="tip-box">
            <i class="bi bi-info-circle"></i>
            Tip：单部漫的密码就是每日访问码，一码通用！刷新后才能看到新漫画！
        </div>
        
        <!-- 返回按钮 -->
        <a href="/" class="back-btn-top">
            <i class="bi bi-arrow-left"></i> 回到目录
        </a>
        
        <!-- 搜索框 -->
        <div class="search-box">
            <form method="GET" class="search-form">
                <input type="hidden" name="tag" value="<?php echo htmlspecialchars($selectedTag); ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($selectedStatus); ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="text" 
                       name="keyword" 
                       class="search-input" 
                       placeholder="搜索不用打全称，用关键词搜索..." 
                       value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" class="search-btn">查看</button>
            </form>
        </div>
        
        <!-- 新推漫按钮 -->
        <a href="?tag=<?php echo $selectedTag; ?>&status=<?php echo $selectedStatus; ?>" class="new-manga-btn">
            新推漫
        </a>
    </div>

    <!-- 筛选区域 -->
    <?php if (!empty($tags)): ?>
    <div class="filter-section">
        <!-- 作者筛选 -->
        <div class="filter-group">
            <label class="filter-label">📑 作者筛选</label>
            <div class="filter-tags">
                <a href="?status=<?php echo $selectedStatus; ?>&page=<?php echo $page; ?>" 
                   class="filter-tag <?php echo $selectedTag === 'all' ? 'active' : ''; ?>">
                    全部
                </a>
                <?php foreach ($tags as $tag): ?>
                    <a href="?tag=<?php echo urlencode($tag['tag_name']); ?>&status=<?php echo $selectedStatus; ?>&page=<?php echo $page; ?>" 
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
                <a href="?tag=<?php echo $selectedTag; ?>&page=<?php echo $page; ?>" 
                   class="filter-tag <?php echo $selectedStatus === 'all' ? 'active' : ''; ?>">
                    全部
                </a>
                <a href="?tag=<?php echo $selectedTag; ?>&status=serializing&page=<?php echo $page; ?>" 
                   class="filter-tag <?php echo $selectedStatus === 'serializing' ? 'active' : ''; ?>">
                    连载中
                </a>
                <a href="?tag=<?php echo $selectedTag; ?>&status=completed&page=<?php echo $page; ?>" 
                   class="filter-tag <?php echo $selectedStatus === 'completed' ? 'active' : ''; ?>">
                    已完结
                </a>
            </div>
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
