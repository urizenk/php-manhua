<?php
/**
 * F3-韩漫合集模块
 * 分类标签 → 连载/完结分类 → 详情页
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

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

// 获取搜索关键词
$keyword = $_GET['keyword'] ?? '';
if ($keyword) {
    $where .= " AND m.title LIKE ?";
    $params[] = "%{$keyword}%";
}

// 获取韩漫列表
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE {$where}
     ORDER BY m.status ASC, m.sort_order DESC, m.created_at DESC",
    $params
);

// 按状态分组
$groupedMangas = [
    'new' => [],
    'serializing' => [],
    'completed' => []
];

foreach ($mangas as $manga) {
    if ($manga['status'] === 'serializing') {
        $groupedMangas['serializing'][] = $manga;
    } elseif ($manga['status'] === 'completed') {
        $groupedMangas['completed'][] = $manga;
    } else {
        $groupedMangas['new'][] = $manga;
    }
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
    .manga-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .manga-list-item {
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.3s ease;
    }
    .manga-list-item:last-child {
        border-bottom: none;
    }
    .manga-list-item:hover {
        background: #f8f9ff;
        padding-left: 15px;
    }
    .manga-link {
        color: #2196F3;
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.3s ease;
    }
    .manga-link:hover {
        color: #1976D2;
        text-decoration: underline;
    }
    .manga-subtitle {
        margin-left: 10px;
        color: #999;
        font-size: 0.85rem;
    }
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }
        .search-form {
            flex-direction: column;
        }
        .search-btn {
            width: 100%;
        }
        .filter-tags {
            gap: 8px;
        }
        .filter-tag {
            font-size: 0.85rem;
            padding: 6px 15px;
        }
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">韩漫合集</h1>
        
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

    <!-- 漫画列表 - 分组展示 -->
    <?php if (empty($mangas)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>暂无符合条件的韩漫</h3>
            <p class="text-muted">试试调整筛选条件</p>
        </div>
    <?php else: ?>
        <!-- 新推漫区域 -->
        <?php if (!empty($groupedMangas['new'])): ?>
            <div class="manga-section">
                <h3 class="section-title">新推漫</h3>
                <ul class="manga-list">
                    <?php foreach ($groupedMangas['new'] as $manga): ?>
                        <li class="manga-list-item">
                            <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                <?php echo htmlspecialchars($manga['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- 连载中区域 -->
        <?php if (!empty($groupedMangas['serializing'])): ?>
            <div class="manga-section">
                <h3 class="section-title">连载中</h3>
                <ul class="manga-list">
                    <?php foreach ($groupedMangas['serializing'] as $manga): ?>
                        <li class="manga-list-item">
                            <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                <?php echo htmlspecialchars($manga['title']); ?>
                            </a>
                            <?php if ($manga['tag_name'] && $manga['tag_name'] !== '未分类'): ?>
                                <span class="manga-subtitle"><?php echo htmlspecialchars($manga['tag_name']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- 完结区域 -->
        <?php if (!empty($groupedMangas['completed'])): ?>
            <div class="manga-section">
                <h3 class="section-title">完结</h3>
                <ul class="manga-list">
                    <?php foreach ($groupedMangas['completed'] as $manga): ?>
                        <li class="manga-list-item">
                            <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                <?php echo htmlspecialchars($manga['title']); ?>
                            </a>
                            <?php if ($manga['tag_name'] && $manga['tag_name'] !== '未分类'): ?>
                                <span class="manga-subtitle"><?php echo htmlspecialchars($manga['tag_name']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="text-center mt-5">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
