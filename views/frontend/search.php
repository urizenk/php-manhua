<?php
/**
 * F11-搜索页模块
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '搜索结果 - 海の小窝';

// 获取搜索关键词
$keyword = $_GET['q'] ?? '';
$keyword = trim($keyword);

// 初始化结果
$results = [];
$total = 0;

// 如果有搜索关键词，执行搜索
if ($keyword) {
    // 转义特殊字符防止LIKE查询错误
    $escapedKeyword = addcslashes($keyword, '%_');
    $searchPattern = '%' . $escapedKeyword . '%';
    
    // 搜索漫画标题和描述
    $results = $db->query(
        "SELECT m.*, t.type_name, tg.tag_name 
         FROM mangas m 
         LEFT JOIN manga_types t ON m.type_id = t.id 
         LEFT JOIN tags tg ON m.tag_id = tg.id 
         WHERE m.title LIKE ? OR m.description LIKE ?
         ORDER BY m.created_at DESC
         LIMIT 50",
        [$searchPattern, $searchPattern]
    );
    
    $total = count($results);
}

$customCss = '
<style>
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .search-header {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 30px;
    }
    .search-title {
        font-size: 2rem;
        font-weight: bold;
        color: #1976D2;
        text-align: center;
        margin-bottom: 25px;
    }
    .search-box {
        max-width: 600px;
        margin: 0 auto;
        position: relative;
    }
    .search-input {
        width: 100%;
        padding: 15px 60px 15px 25px;
        border: 2px solid #ddd;
        border-radius: 30px;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    .search-input:focus {
        border-color: #1976D2;
        box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        outline: none;
    }
    .search-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: #1976D2;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 10px 25px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-btn:hover {
        transform: translateY(-50%) scale(1.05);
        box-shadow: 0 5px 15px rgba(25, 118, 210, 0.4);
    }
    .search-info {
        background: white;
        border-radius: 15px;
        padding: 20px 30px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .search-keyword {
        font-size: 1.1rem;
        color: #333;
    }
    .search-keyword strong {
        color: #1976D2;
    }
    .search-count {
        color: #999;
        font-size: 0.95rem;
    }
    .results-grid {
        display: grid;
        gap: 20px;
    }
    .result-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: flex;
        gap: 20px;
    }
    .result-card:hover {
        transform: translateX(10px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .result-cover {
        width: 120px;
        height: 160px;
        flex-shrink: 0;
        border-radius: 10px;
        overflow: hidden;
        background: #e0e0e0;
    }
    .result-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .no-cover {
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    .result-info {
        flex: 1;
    }
    .result-title {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    .result-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .meta-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        background: #f0f0f0;
        border-radius: 15px;
        font-size: 0.85rem;
        color: #666;
    }
    .meta-badge i {
        margin-right: 5px;
    }
    .result-desc {
        color: #666;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
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
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <!-- 搜索框 -->
    <div class="search-header">
        <h1 class="search-title">🔍 搜索漫画资源</h1>
        <form action="/search" method="GET" class="search-box">
            <input type="text" 
                   name="q" 
                   class="search-input" 
                   placeholder="输入漫画名称或关键词..."
                   value="<?php echo htmlspecialchars($keyword); ?>"
                   required>
            <button type="submit" class="search-btn">
                <i class="bi bi-search"></i> 搜索
            </button>
        </form>
    </div>

    <?php if ($keyword): ?>
        <!-- 搜索信息 -->
        <div class="search-info">
            <div class="search-keyword">
                搜索关键词：<strong><?php echo htmlspecialchars($keyword); ?></strong>
            </div>
            <div class="search-count">
                找到 <?php echo $total; ?> 个结果
            </div>
        </div>

        <!-- 搜索结果 -->
        <?php if (empty($results)): ?>
            <div class="empty-state">
                <div class="empty-icon">😔</div>
                <h3>未找到相关结果</h3>
                <p class="text-muted">试试其他关键词或浏览分类模块</p>
            </div>
        <?php else: ?>
            <div class="results-grid">
                <?php foreach ($results as $manga): ?>
                    <a href="/detail/<?php echo $manga['id']; ?>" class="result-card">
                        <div class="result-cover">
                            <?php if ($manga['cover_image']): ?>
                                <img src="<?php echo htmlspecialchars($manga['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($manga['title']); ?>">
                            <?php else: ?>
                                <div class="no-cover">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="result-info">
                            <div class="result-title"><?php echo htmlspecialchars($manga['title']); ?></div>
                            <div class="result-meta">
                                <span class="meta-badge">
                                    <i class="bi bi-folder"></i>
                                    <?php echo htmlspecialchars($manga['type_name']); ?>
                                </span>
                                <span class="meta-badge">
                                    <i class="bi bi-tag"></i>
                                    <?php echo htmlspecialchars($manga['tag_name'] ?? '未分类'); ?>
                                </span>
                                <?php if ($manga['status']): ?>
                                    <span class="meta-badge">
                                        <i class="bi bi-info-circle"></i>
                                        <?php echo $manga['status'] === 'serializing' ? '连载中' : '已完结'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($manga['description']): ?>
                                <div class="result-desc">
                                    <?php echo htmlspecialchars($manga['description']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- 未输入关键词 -->
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>在上方输入关键词开始搜索</h3>
            <p class="text-muted">支持搜索漫画名称和描述内容</p>
        </div>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="text-center mt-5">
        <a href="/" class="back-btn">
            返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
