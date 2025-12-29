<?php
/**
 * 通用模块列表页
 * 按板块标签分类显示漫画列表
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
$moduleType = $db->queryOne(
    'SELECT * FROM manga_types WHERE type_code = ?',
    [$moduleCode]
);

if (!$moduleType) {
    echo '模块不存在';
    exit;
}

$moduleName = $moduleType['type_name'];
$pageTitle = htmlspecialchars($moduleName) . ' - 海の小窝';

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
        font-family: Arial, sans-serif;
        background-color: #FFFFF0;
        margin: 0;
        padding: 20px;
        color: #333;
        min-height: 100vh;
    }
    .content-wrapper {
        max-width: 800px;
        margin: 0 auto;
    }
    .page-title {
        color: #1B1212;
        font-size: 2rem;
        font-weight: bold;
        padding: 10px 0;
        margin-bottom: 20px;
    }
    .tip-box {
        background-color: #FFE4B5;
        padding: 10px 15px;
        border-radius: 25px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #333;
        display: inline-block;
    }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #EC5800;
        color: white;
        padding: 10px 18px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 1rem;
        font-weight: bold;
        transition: background-color 0.3s ease;
        margin-bottom: 20px;
    }
    .back-btn:hover {
        background-color: #d14e00;
        color: white;
    }
    .search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    .search-input {
        flex: 1;
        min-width: 200px;
        padding: 10px 15px;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        outline: none;
    }
    .search-input:focus {
        border-color: #FFA500;
    }
    .search-btn {
        padding: 10px 25px;
        background-color: #FFA500;
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .search-btn:hover {
        background-color: #e69500;
    }
    .tag-badge {
        display: inline-block;
        font-size: 1rem;
        font-weight: bold;
        padding: 8px 15px;
        margin: 15px 0 10px 0;
        border-radius: 5px;
        background-color: #FFA500;
        color: white;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    .manga-item {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .manga-item:last-child {
        border-bottom: none;
    }
    .manga-link {
        color: #0077ff;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
    }
    .manga-link:hover {
        text-decoration: underline;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .bottom-back {
        text-align: center;
        margin-top: 40px;
        padding-bottom: 30px;
    }
    .bottom-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #EC5800;
        color: white;
        padding: 12px 25px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }
    .bottom-back-btn:hover {
        background-color: #d14e00;
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
        <?php 
        $tipText = $moduleType['tip_text'] ?? '';
        if (!$tipText) {
            $tipText = 'Tip：单部漫的密码就是每日访问码，一码通用！刷新后才能看到新增漫画！';
        }
        ?>
        <div class="tip-box">
            <?php echo htmlspecialchars($tipText); ?>
        </div>
        
        <!-- 返回按钮 -->
        <a href="/" class="back-btn">
            回到目录
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
        <?php 
foreach ($groupedMangas as $tagId => $group): ?>
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
            返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
