<?php
/**
 * F4-完结短漫模块
 * 字母标签（A/B/C...）→ 漫画列表 → 详情页
 */
$pageTitle = '完结短漫 - 海の小窝';

// 获取完结短漫的类型ID
$shortType = $db->queryOne("SELECT * FROM manga_types WHERE type_code = ?", ['short_complete']);
if (!$shortType) {
    echo "完结短漫配置错误";
    exit;
}

// 获取当前选中的字母
$selectedLetter = $_GET['letter'] ?? 'all';

// 获取所有字母标签（按字母顺序）
$tags = $db->query(
    "SELECT * FROM tags WHERE type_id = ? AND tag_type = 'letter' ORDER BY tag_name ASC",
    [$shortType['id']]
);

// 构建查询条件
$where = "m.type_id = ?";
$params = [$shortType['id']];

if ($selectedLetter !== 'all') {
    $where .= " AND t.tag_name = ?";
    $params[] = $selectedLetter;
}

// 获取完结短漫列表
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE {$where}
     ORDER BY t.tag_name ASC, m.title ASC",
    $params
);

// 按字母分组
$mangasByLetter = [];
foreach ($mangas as $manga) {
    $letter = $manga['tag_name'] ?? '其他';
    if (!isset($mangasByLetter[$letter])) {
        $mangasByLetter[$letter] = [];
    }
    $mangasByLetter[$letter][] = $manga;
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
    .letter-filter {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }
    .filter-label {
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        display: block;
        text-align: center;
    }
    .letter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
        gap: 10px;
    }
    .letter-btn {
        padding: 12px 10px;
        border-radius: 10px;
        background: #f0f0f0;
        color: #666;
        text-decoration: none;
        text-align: center;
        font-weight: bold;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        display: block;
    }
    .letter-btn:hover {
        background: #1976D2;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(25, 118, 210, 0.3);
    }
    .letter-btn.active {
        background: #1976D2;
        color: white;
        box-shadow: 0 5px 15px rgba(25, 118, 210, 0.3);
    }
    .letter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .letter-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    .letter-icon {
        width: 50px;
        height: 50px;
        background: #1976D2;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin-right: 15px;
    }
    .letter-name {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
    }
    .letter-count {
        margin-left: auto;
        background: #1976D2;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    .manga-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
    .manga-item {
        padding: 15px;
        border: 2px solid #f0f0f0;
        border-radius: 10px;
        transition: all 0.3s ease;
        background: #fafafa;
    }
    .manga-item:hover {
        border-color: #1976D2;
        background: white;
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .manga-link {
        text-decoration: none;
        color: #333;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .manga-link:hover {
        color: #1976D2;
    }
    .manga-title {
        flex: 1;
        font-weight: 500;
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
        <h1 class="page-title">🏅 完结短漫</h1>
        <p class="page-subtitle">短篇完结作品 · 按字母分类查看</p>
    </div>

    <!-- 字母筛选 -->
    <div class="letter-filter">
        <label class="filter-label">🔤 选择字母</label>
        <div class="letter-grid">
            <a href="?" class="letter-btn <?php echo $selectedLetter === 'all' ? 'active' : ''; ?>">
                全部
            </a>
            <?php foreach ($tags as $tag): ?>
                <a href="?letter=<?php echo urlencode($tag['tag_name']); ?>" 
                   class="letter-btn <?php echo $selectedLetter === $tag['tag_name'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($tag['tag_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($mangasByLetter)): ?>
        <!-- 空状态 -->
        <div class="letter-section">
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>暂无完结短漫</h3>
                <p class="text-muted">试试选择其他字母分类</p>
            </div>
        </div>
    <?php else: ?>
        <!-- 按字母展示 -->
        <?php 
        // 获取排序后的字母
        $letters = array_keys($mangasByLetter);
        sort($letters);
        
        foreach ($letters as $letter): 
            if (!isset($mangasByLetter[$letter]) || empty($mangasByLetter[$letter])) continue;
        ?>
            <div class="letter-section">
                <div class="letter-header">
                    <div class="letter-icon"><?php echo htmlspecialchars($letter); ?></div>
                    <span class="letter-name"><?php echo htmlspecialchars($letter); ?> 字母分类</span>
                    <span class="letter-count"><?php echo count($mangasByLetter[$letter]); ?> 本</span>
                </div>
                <div class="manga-list">
                    <?php foreach ($mangasByLetter[$letter] as $manga): ?>
                        <div class="manga-item">
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 返回按钮 -->
    <div class="text-center mt-4">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
