<?php
/**
 * F8-广播剧合集模块
 * 按板块标签分类显示漫画列表
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '广播剧合集 - 海の小窝';

// 获取广播剧合集的类型ID
$moduleType = $db->queryOne("SELECT * FROM manga_types WHERE type_code = ?", ['drama_collection']);
if (!$moduleType) {
    echo "广播剧合集配置错误";
    exit;
}

$moduleName = $moduleType['type_name'];

// 获取搜索关键词
$keyword = $_GET['keyword'] ?? '';

// 获取该板块下的所有标签（按排序）
$tags = $db->query(
    "SELECT * FROM tags WHERE type_id = ? ORDER BY sort_order, id",
    [$moduleType['id']]
);

// 构建查询条件
$where = "m.type_id = ?";
$params = [$moduleType['id']];

if ($keyword) {
    $escapedKeyword = addcslashes($keyword, '%_');
    $where .= " AND m.title LIKE ?";
    $params[] = "%{$escapedKeyword}%";
}

// 获取漫画列表
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE {$where}
     ORDER BY m.sort_order DESC, m.created_at DESC",
    $params
);

// 按标签分组
$groupedMangas = [];
$untaggedMangas = [];

foreach ($mangas as $manga) {
    $tagId = $manga['tag_id'] ?? 0;
    $tagName = $manga['tag_name'] ?? '';
    
    if ($tagId && $tagName) {
        if (!isset($groupedMangas[$tagId])) {
            $groupedMangas[$tagId] = [
                'tag_name' => $tagName,
                'mangas' => []
            ];
        }
        $groupedMangas[$tagId]['mangas'][] = $manga;
    } else {
        $untaggedMangas[] = $manga;
    }
}

// 按标签排序重新整理分组顺序
$sortedGroups = [];
foreach ($tags as $tag) {
    if (isset($groupedMangas[$tag['id']])) {
        $sortedGroups[$tag['id']] = $groupedMangas[$tag['id']];
    }
}
// 添加未分类的
if (!empty($untaggedMangas)) {
    $sortedGroups[0] = [
        'tag_name' => '未分类',
        'mangas' => $untaggedMangas
    ];
}
$groupedMangas = $sortedGroups;

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
        margin-bottom: 15px;
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
        background: #ffc107;
        color: #333;
        border: none;
        border-radius: 20px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-btn:hover {
        background: #e0a800;
    }
    .tag-badge {
        display: inline-block;
        background: #ffc107;
        color: #333;
        padding: 6px 18px;
        border-radius: 15px;
        font-weight: bold;
        font-size: 0.9rem;
        margin: 20px 0 10px 0;
    }
    .tag-badge:first-of-type {
        margin-top: 0;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    .manga-item {
        padding: 10px 0;
        border-bottom: 1px solid #e8e8e8;
    }
    .manga-item:last-child {
        border-bottom: none;
    }
    .manga-link {
        text-decoration: none;
        color: #2196F3;
        font-size: 0.95rem;
        transition: color 0.2s ease;
    }
    .manga-link:hover {
        color: #1565C0;
        text-decoration: underline;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
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
        <h1 class="page-title"><?php echo htmlspecialchars($moduleName); ?></h1>
        
        <!-- Tip提示框 -->
        <div class="tip-box">
            Tip：单部漫的密码就是每日访问码，一码通用！刷新后才能看到新增漫画！
        </div>
        
        <!-- 返回按钮 -->
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 回到目录
        </a>
        
        <!-- 搜索框 -->
        <form method="GET" class="search-box">
            <input type="text" 
                   name="keyword" 
                   class="search-input" 
                   placeholder="漫名不用打全称，用关键词搜索..." 
                   value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="search-btn">查看</button>
        </form>
    </div>

    <!-- 漫画列表 -->
    <?php if (empty($mangas)): ?>
        <div class="empty-state">
            <h3>暂无符合条件的漫画</h3>
            <p>试试调整搜索关键词</p>
        </div>
    <?php else: ?>
        
        <!-- 按标签分组显示 -->
        <?php foreach ($groupedMangas as $tagId => $group): ?>
            <div class="tag-badge"><?php echo htmlspecialchars($group['tag_name']); ?></div>
            <ul class="manga-list">
                <?php foreach ($group['mangas'] as $manga): ?>
                    <li class="manga-item">
                        <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                            <?php echo htmlspecialchars($manga['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
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
