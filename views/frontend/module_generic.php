<?php
/**
 * 通用模块列表页
 * 用于新增加的漫画类型，按列表形式展示该类型下的所有漫画
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$moduleCode = $GLOBALS['moduleCode'] ?? '';

if (!$db || !$moduleCode) {
    echo '模块配置错误';
    exit;
}

// 查找类型信息
$type = $db->queryOne(
    'SELECT * FROM manga_types WHERE type_code = ?',
    [$moduleCode]
);

if (!$type) {
    echo '模块不存在';
    exit;
}

// 获取搜索关键词
$keyword = $_GET['keyword'] ?? '';

// 构建查询条件
$where = "m.type_id = ?";
$params = [$type['id']];

if ($keyword) {
    $escapedKeyword = addcslashes($keyword, '%_');
    $where .= " AND m.title LIKE ?";
    $params[] = "%{$escapedKeyword}%";
}

// 获取该类型下所有漫画，按标签分组
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m
     LEFT JOIN tags t ON m.tag_id = t.id
     WHERE {$where}
     ORDER BY m.sort_order DESC, m.created_at DESC",
    $params
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

$pageTitle = htmlspecialchars($type['type_name']) . ' - 海の小窝';

// 获取模块图标
$icon = $type['icon'] ?? 'book';

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
    .page-header {
        margin-bottom: 20px;
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-title-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }
    .page-subtitle {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    .back-btn {
        display: inline-block;
        background: #FF6B35;
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .back-btn:hover {
        background: #e55a28;
        color: white;
        transform: translateY(-2px);
    }
    .search-box {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
    }
    .search-input {
        flex: 1;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 20px;
        font-size: 0.9rem;
        outline: none;
        background: white;
    }
    .search-input:focus {
        border-color: #FF6B35;
    }
    .search-btn {
        padding: 10px 25px;
        background: #FF6B35;
        color: white;
        border: none;
        border-radius: 20px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-btn:hover {
        background: #e55a28;
    }
    .tag-section {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .tag-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    .tag-icon {
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, #FFA726 0%, #FF9800 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        margin-right: 10px;
    }
    .tag-name {
        font-size: 1rem;
        font-weight: bold;
        color: #333;
        flex: 1;
    }
    .tag-count {
        background: #FF6B35;
        color: white;
        padding: 3px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .manga-item {
        padding: 10px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .manga-item:last-child {
        border-bottom: none;
    }
    .manga-link {
        text-decoration: none;
        color: #333;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: color 0.2s ease;
    }
    .manga-link:hover {
        color: #FF6B35;
    }
    .manga-link i {
        color: #999;
        font-size: 0.85rem;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
        background: white;
        border-radius: 12px;
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
    <!-- 页面头部 -->
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="bi bi-<?php echo htmlspecialchars($icon); ?>"></i></span>
            <?php echo htmlspecialchars($type['type_name']); ?>
        </h1>
        <p class="page-subtitle">当前模块下的漫画资源列表</p>
        
        <!-- 返回按钮 -->
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 回到目录
        </a>
        
        <!-- 搜索框 -->
        <form method="GET" class="search-box">
            <input type="text" 
                   name="keyword" 
                   class="search-input" 
                   placeholder="搜索漫画..." 
                   value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="search-btn">搜索</button>
        </form>
    </div>

    <?php if (empty($mangasByTag)): ?>
        <!-- 空状态 -->
        <div class="empty-state">
            <h3>暂无资源</h3>
            <p>该模块下暂时还没有添加漫画资源</p>
        </div>
    <?php else: ?>
        <!-- 按标签展示 -->
        <?php foreach ($mangasByTag as $tagName => $tagMangas): ?>
            <div class="tag-section">
                <div class="tag-header">
                    <span class="tag-icon"><i class="bi bi-folder"></i></span>
                    <span class="tag-name"><?php echo htmlspecialchars($tagName); ?></span>
                    <span class="tag-count"><?php echo count($tagMangas); ?> 本</span>
                </div>
                <ul class="manga-list">
                    <?php foreach ($tagMangas as $manga): ?>
                        <li class="manga-item">
                            <?php if (!empty($manga['resource_link'])): ?>
                                <a href="<?php echo htmlspecialchars($manga['resource_link']); ?>" 
                                   target="_blank" 
                                   class="manga-link">
                                    <span><?php echo htmlspecialchars($manga['title']); ?></span>
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            <?php else: ?>
                                <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                    <span><?php echo htmlspecialchars($manga['title']); ?></span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="bottom-back">
        <a href="/" class="bottom-back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
