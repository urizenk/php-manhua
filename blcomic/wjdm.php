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

// 获取完结短漫
try {
    $stmt = $pdo->prepare("
        SELECT c.*, GROUP_CONCAT(t.name) as tag_names
        FROM comics c
        LEFT JOIN comic_tag_relations r ON c.id = r.comic_id
        LEFT JOIN comic_tags t ON r.tag_id = t.id
        WHERE c.category_id = 3 AND c.status = '完结'
        GROUP BY c.id
        ORDER BY c.title
    ");
    $stmt->execute();
    $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comics = [];
}

// 按首字母分组
$groupedByLetter = [];
foreach (range('A', 'Z') as $letter) {
    $groupedByLetter[$letter] = [];
}

foreach ($comics as $comic) {
    $firstChar = strtoupper(mb_substr($comic['title'], 0, 1));
    if (ctype_alpha($firstChar) && isset($groupedByLetter[$firstChar])) {
        $groupedByLetter[$firstChar][] = $comic;
    } else {
        if (!isset($groupedByLetter['其他'])) {
            $groupedByLetter['其他'] = [];
        }
        $groupedByLetter['其他'][] = $comic;
    }
}

// 处理搜索
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchKeyword) {
    $filteredComics = [];
    foreach ($groupedByLetter as $letter => $comicsList) {
        foreach ($comicsList as $comic) {
            if (stripos($comic['title'], $searchKeyword) !== false) {
                if (!isset($filteredComics[$letter])) {
                    $filteredComics[$letter] = [];
                }
                $filteredComics[$letter][] = $comic;
            }
        }
    }
    $groupedByLetter = $filteredComics;
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
    <title>完结短漫 - 海の小窝</title>
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
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .comic-list {
            margin-left: 20px;
        }
        
        .comic-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
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
        
        .episodes {
            color: #666;
            font-size: 14px;
            margin-left: 10px;
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
        <h1>完结短漫</h1>
        <div class="tip">
            Tip ：密码就是每日访问码，一码通用哦！
        </div>
        
        <a href="subpage.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i>
            回到目录
        </a>

        <!-- 搜索功能 -->
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
        foreach ($groupedByLetter as $letter => $comicsList):
            if (!empty($comicsList)):
                $hasResults = true;
        ?>
            <div class="section">
                <span class="a-title"><?php echo htmlspecialchars($letter); ?></span>
                <div class="comic-list">
                    <?php foreach ($comicsList as $comic): ?>
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
                            <?php if (!empty($comic['episodes'])): ?>
                                <span class="episodes">
                                    (<?php echo htmlspecialchars($comic['episodes']); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php 
            endif;
        endforeach;
        
        if (!$hasResults): 
        ?>
            <div class="no-results">
                <i class="fas fa-book" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <div><?php echo $searchKeyword ? '没有找到相关漫画' : '暂无完结短漫'; ?></div>
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