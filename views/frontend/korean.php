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

// 获取搜索关键词
$keyword = $_GET['keyword'] ?? '';

// 构建查询条件
$where = "m.type_id = ?";
$params = [$koreanType['id']];

if ($keyword) {
    $escapedKeyword = addcslashes($keyword, '%_');
    $where .= " AND m.title LIKE ?";
    $params[] = "%{$escapedKeyword}%";
}

// 获取韩漫列表
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m 
     LEFT JOIN tags t ON m.tag_id = t.id 
     WHERE {$where}
     ORDER BY m.created_at DESC, m.sort_order DESC",
    $params
);

// 按状态和字母分组
$groupedMangas = [
    'new' => [],           // 新推漫（无状态的）
    'serializing' => [],   // 连载中
    'completed' => [],     // 已完结
    'by_letter' => []      // 按字母分组
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
            $groupedMangas['by_letter'][$letter] = ['serializing' => [], 'completed' => []];
        }
        if ($manga['status'] === 'serializing') {
            $groupedMangas['by_letter'][$letter]['serializing'][] = $manga;
        } else {
            $groupedMangas['by_letter'][$letter]['completed'][] = $manga;
        }
    }
}

// 对字母分组进行排序
if (!empty($groupedMangas['by_letter'])) {
    ksort($groupedMangas['by_letter']);
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
    .new-manga-badge {
        display: inline-block;
        background: #ffc107;
        color: #333;
        padding: 6px 18px;
        border-radius: 15px;
        font-weight: 500;
        font-size: 0.85rem;
        margin-bottom: 15px;
    }
    .letter-badge {
        display: inline-block;
        background: linear-gradient(135deg, #FFA500, #FF8C00);
        color: white;
        padding: 6px 18px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 10px;
    }
    .section-title {
        font-weight: bold;
        color: #333;
        margin: 20px 0 10px 0;
        font-size: 1rem;
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
        <h1 class="page-title">韩漫合集</h1>
        
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
        
        <!-- 新推漫按钮 -->
        <span class="new-manga-badge">新推漫</span>
    </div>

    <!-- 漫画列表 -->
    <?php if (empty($mangas)): ?>
        <div class="empty-state">
            <h3>暂无符合条件的漫画</h3>
            <p>试试调整搜索关键词</p>
        </div>
    <?php else: ?>
        
        <!-- 新推漫区域 -->
        <?php if (!empty($groupedMangas['new'])): ?>
            <ul class="manga-list">
                <?php foreach ($groupedMangas['new'] as $manga): ?>
                    <li class="manga-item">
                        <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                            <?php echo htmlspecialchars($manga['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <!-- 按字母分组区域 -->
        <?php foreach ($groupedMangas['by_letter'] as $letter => $letterMangas): ?>
            <div class="letter-badge"><?php echo $letter; ?></div>
            
            <?php if (!empty($letterMangas['serializing'])): ?>
                <div class="section-title">连载中</div>
                <ul class="manga-list">
                    <?php foreach ($letterMangas['serializing'] as $manga): ?>
                        <li class="manga-item">
                            <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                <?php echo htmlspecialchars($manga['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($letterMangas['completed'])): ?>
                <div class="section-title">完结</div>
                <ul class="manga-list">
                    <?php foreach ($letterMangas['completed'] as $manga): ?>
                        <li class="manga-item">
                            <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                                <?php echo htmlspecialchars($manga['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <!-- 非字母开头的连载中 -->
        <?php 
        $nonLetterSerializing = array_filter($groupedMangas['serializing'], function($m) {
            $firstChar = mb_substr($m['title'], 0, 1);
            return !preg_match('/[A-Za-z]/', $firstChar);
        });
        if (!empty($nonLetterSerializing)): 
        ?>
            <div class="section-title">连载中</div>
            <ul class="manga-list">
                <?php foreach ($nonLetterSerializing as $manga): ?>
                    <li class="manga-item">
                        <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                            <?php echo htmlspecialchars($manga['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <!-- 非字母开头的已完结 -->
        <?php 
        $nonLetterCompleted = array_filter($groupedMangas['completed'], function($m) {
            $firstChar = mb_substr($m['title'], 0, 1);
            return !preg_match('/[A-Za-z]/', $firstChar);
        });
        if (!empty($nonLetterCompleted)): 
        ?>
            <div class="section-title">完结</div>
            <ul class="manga-list">
                <?php foreach ($nonLetterCompleted as $manga): ?>
                    <li class="manga-item">
                        <a href="/detail/<?php echo $manga['id']; ?>" class="manga-link">
                            <?php echo htmlspecialchars($manga['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
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
