<?php
/**
 * F2-日更板块模块
 * 按日期标签展示日更资源，最新在上
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '日更板块 - 海の小窝';

// 获取日更板块的类型ID
$dailyType = $db->queryOne("SELECT * FROM manga_types WHERE type_code = ?", ['daily_update']);
if (!$dailyType) {
    echo "日更板块配置错误";
    exit;
}

// 获取搜索关键词
$keyword = $_GET['keyword'] ?? '';

// 构建查询条件
$where = "m.type_id = ?";
$params = [$dailyType['id']];

if ($keyword) {
    $escapedKeyword = addcslashes($keyword, '%_');
    $where .= " AND m.title LIKE ?";
    $params[] = "%{$escapedKeyword}%";
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
     WHERE {$where}
     ORDER BY m.created_at DESC",
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
        color: #1976D2;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-title-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #42A5F5 0%, #1976D2 100%);
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
    .tip-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 10px 12px;
        margin-bottom: 15px;
        border-radius: 0 8px 8px 0;
        font-size: 0.85rem;
        color: #856404;
    }
    .back-btn {
        display: inline-block;
        background: #1976D2;
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
        background: #1565C0;
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
        border-color: #1976D2;
    }
    .search-btn {
        padding: 10px 25px;
        background: #1976D2;
        color: white;
        border: none;
        border-radius: 20px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-btn:hover {
        background: #1565C0;
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
        background: #1976D2;
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
        color: #1976D2;
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
        color: #1976D2;
        border: 2px solid #1976D2;
        padding: 10px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .bottom-back-btn:hover {
        background: #1976D2;
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
            <span class="page-title-icon"><i class="bi bi-calendar-date"></i></span>
            日更板块
        </h1>
        <p class="page-subtitle">每日更新资源 · 最新内容优先展示</p>
        
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
            <h3>暂无日更资源</h3>
            <p>管理员还未添加任何日更内容</p>
        </div>
    <?php else: ?>
        <!-- 按日期标签展示 -->
        <?php foreach ($tags as $tag): ?>
            <?php if (isset($mangasByTag[$tag['tag_name']])): ?>
                <div class="tag-section">
                    <div class="tag-header">
                        <span class="tag-icon"><i class="bi bi-calendar-event"></i></span>
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
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- 未分类的漫画 -->
        <?php if (isset($mangasByTag['未分类']) && !empty($mangasByTag['未分类'])): ?>
            <div class="tag-section">
                <div class="tag-header">
                    <span class="tag-icon"><i class="bi bi-folder"></i></span>
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
        <?php endif; ?>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="bottom-back">
        <a href="/" class="bottom-back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
