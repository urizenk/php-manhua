<?php
session_start();
require 'config.php';
require 'functions.php';

// 基础会话验证
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: index.php');
    exit;
}

// 密码版本验证
try {
    $latest_password_id = null;
    if ($redis && $redis->exists('latest_password_id')) {
        $latest_password_id = $redis->get('latest_password_id');
    } else {
        $latest_password_id = getLatestPasswordIdFromDB($pdo);
        if ($redis) {
            $redis->setex('latest_password_id', 18000, $latest_password_id);
        }
    }

    if ($_SESSION['password_version'] != $latest_password_id) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("密码版本验证失败: " . $e->getMessage());
}

// 获取韩漫分类的漫画（分类ID=1）
try {
    $stmt = $pdo->prepare("
        SELECT c.*, GROUP_CONCAT(t.name) as tag_names
        FROM comics c
        LEFT JOIN comic_tag_relations r ON c.id = r.comic_id
        LEFT JOIN comic_tags t ON r.tag_id = t.id
        WHERE c.category_id = 1
        GROUP BY c.id
        ORDER BY c.title
    ");
    $stmt->execute();
    $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comics = [];
}

// 按字母和状态分组漫画
$groupedComics = [];
$letters = ['新增漫', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

// 初始化分组结构
foreach ($letters as $letter) {
    $groupedComics[$letter] = ['连载' => [], '完结' => []];
}

// 处理搜索
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

foreach ($comics as $comic) {
    $tags = $comic['tag_names'] ? explode(',', $comic['tag_names']) : [];
    
    $letter = '其他';
    $status = '连载';
    
    // 查找字母标签
    foreach ($tags as $tag) {
        $tag = trim($tag);
        if (in_array($tag, $letters)) {
            $letter = $tag;
            break;
        }
    }
    
    // 查找状态标签
    foreach ($tags as $tag) {
        $tag = trim($tag);
        if (in_array($tag, ['连载', '完结'])) {
            $status = $tag;
            break;
        }
    }
    
    // 如果没有找到字母标签，使用首字母
    if ($letter === '其他') {
        $firstChar = strtoupper(mb_substr($comic['title'], 0, 1));
        if (in_array($firstChar, $letters)) {
            $letter = $firstChar;
        }
    }
    
    if (isset($groupedComics[$letter])) {
        $groupedComics[$letter][$status][] = $comic;
    } else {
        if (!isset($groupedComics['其他'])) {
            $groupedComics['其他'] = ['连载' => [], '完结' => []];
        }
        $groupedComics['其他'][$status][] = $comic;
    }
}

// 处理搜索过滤
if ($searchKeyword) {
    $filteredComics = [];
    foreach ($groupedComics as $letter => $statusGroups) {
        foreach ($statusGroups as $status => $comicsList) {
            foreach ($comicsList as $comic) {
                if (stripos($comic['title'], $searchKeyword) !== false || 
                    stripos($comic['episodes'], $searchKeyword) !== false) {
                    if (!isset($filteredComics[$letter])) {
                        $filteredComics[$letter] = ['连载' => [], '完结' => []];
                    }
                    $filteredComics[$letter][$status][] = $comic;
                }
            }
        }
    }
    $groupedComics = $filteredComics;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>韩漫合集 - 海の小窝</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFF0;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: #1B1212d0;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .tip {
            background-color: #FFE4B5;
            padding: 8px 12px;
            border-radius: 25px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
            display: inline-block;
        }
        
        .back-to-index {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #EC5800;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }
        
        .back-to-index:hover {
            background-color: #e69500;
        }
        
        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        #searchInput {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #FFA500;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }
        
        #searchInput:focus {
            box-shadow: 0 0 8px rgba(255,165,0,0.3);
        }
        
        #searchBtn {
            padding: 10px 20px;
            background-color: #FFA500;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        #searchBtn:hover {
            background-color: #e69500;
        }
        
        .section {
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .a-title {
            display: inline-block;
            background-color: #FFA500;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        h4 {
            margin: 15px 0 10px 0;
            font-size: 18px;
            color: #333;
            border-left: 4px solid #FFA500;
            padding-left: 10px;
        }
        
        .comic-list {
            margin-left: 20px;
        }
        
        .comic-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
        }
        
        .comic-item:last-child {
            border-bottom: none;
        }
        
        .comic-link {
            color: #0077ff;
            text-decoration: none;
            font-size: 16px;
            flex: 1;
        }
        
        .comic-link:hover {
            color: #D18E85;
            text-decoration: underline;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }
        
        .highlight {
            background-color: #FFA50033;
            color: #FFA500;
            font-weight: bold;
            padding: 0 2px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>韩漫合集</h1>
        <div class="tip">
            Tip ：单部漫的密码就是每日访问码，一码通用喔！刷新后才能看到新增漫漫！
        </div>
        
        <a href="subpage.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i>
            回到目录
        </a>

        <!-- 搜索表单 -->
        <div class="search-container">
            <form method="GET" style="display: flex; flex: 1; gap: 10px;">
                <input type="text" name="search" id="searchInput" 
                       placeholder="漫名不用打全称，用关键词搜索..." 
                       value="<?php echo htmlspecialchars($searchKeyword); ?>">
                <button type="submit" id="searchBtn">搜索</button>
            </form>
        </div>

        <!-- 漫画列表 -->
        <?php
        $hasResults = false;
        foreach ($groupedComics as $letter => $statusGroups):
            $hasComics = !empty($statusGroups['连载']) || !empty($statusGroups['完结']);
            if ($hasComics):
                $hasResults = true;
        ?>
            <div class="section">
                <span class="a-title"><?php echo htmlspecialchars($letter); ?></span>
                
                <?php if (!empty($statusGroups['连载'])): ?>
                    <h4>连载中</h4>
                    <div class="comic-list">
                        <?php foreach ($statusGroups['连载'] as $comic): ?>
                            <div class="comic-item">
                                <a href="comic-detail.php?id=<?php echo $comic['id']; ?>" class="comic-link" target="_blank">
                                    <?php 
                                    $title = htmlspecialchars($comic['title']);
                                    if ($searchKeyword) {
                                        $title = preg_replace("/($searchKeyword)/i", '<span class="highlight">$1</span>', $title);
                                    }
                                    echo $title;
                                    ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($statusGroups['完结'])): ?>
                    <h4>完结</h4>
                    <div class="comic-list">
                        <?php foreach ($statusGroups['完结'] as $comic): ?>
                            <div class="comic-item">
                                <a href="comic-detail.php?id=<?php echo $comic['id']; ?>" class="comic-link" target="_blank">
                                    <?php 
                                    $title = htmlspecialchars($comic['title']);
                                    if ($searchKeyword) {
                                        $title = preg_replace("/($searchKeyword)/i", '<span class="highlight">$1</span>', $title);
                                    }
                                    echo $title;
                                    ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php 
            endif;
        endforeach; 
        
        if (!$hasResults): 
        ?>
            <div class="no-results">
                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <div><?php echo $searchKeyword ? '没有找到相关漫画' : '暂无漫画数据'; ?></div>
                <?php if ($searchKeyword): ?>
                    <div style="margin-top: 10px; font-size: 14px;">试试用其他关键词搜索</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // 实时搜索高亮
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const comicLinks = document.querySelectorAll('.comic-link');
            
            function highlightText() {
                const searchTerm = searchInput.value.trim().toLowerCase();
                
                comicLinks.forEach(link => {
                    const originalText = link.textContent.replace(/<span class="highlight">(.*?)<\/span>/gi, '$1');
                    link.innerHTML = originalText;
                    
                    if (searchTerm) {
                        const regex = new RegExp(`(${searchTerm})`, 'gi');
                        link.innerHTML = originalText.replace(regex, '<span class="highlight">$1</span>');
                    }
                });
            }
            
            searchInput.addEventListener('input', highlightText);
        });
    </script>
</body>
</html>