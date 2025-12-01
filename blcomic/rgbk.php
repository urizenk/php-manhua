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

// 获取日更板块的漫画
try {
    $stmt = $pdo->prepare("
        SELECT c.*, GROUP_CONCAT(t.name) as tag_names
        FROM comics c
        LEFT JOIN comic_tag_relations r ON c.id = r.comic_id
        LEFT JOIN comic_tags t ON r.tag_id = t.id
        WHERE c.category_id = 2
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comics = [];
}

// 按日期分组
$groupedByDate = [];
foreach ($comics as $comic) {
    $date = date('md', strtotime($comic['created_at']));
    if (!isset($groupedByDate[$date])) {
        $groupedByDate[$date] = [];
    }
    $groupedByDate[$date][] = $comic;
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
    <title>日更板块 - 海の小窝</title>
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
        
        .section {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .date-title {
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
    </style>
</head>
<body>
    <div class="container">
        <h1>日更板块</h1>
        <div class="tip">
            Tip ：日更保留一个月，往期资源见微博铁粉群
        </div>
        
        <a href="subpage.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i>
            回到目录
        </a>

        <!-- 日更内容 -->
        <?php if (empty($groupedByDate)): ?>
            <div class="no-results">
                <i class="fas fa-calendar-day" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <div>暂无日更内容</div>
            </div>
        <?php else: ?>
            <?php foreach ($groupedByDate as $date => $comicsList): ?>
                <div class="section">
                    <span class="date-title"><?php echo substr($date, 0, 2) . substr($date, 2); ?></span>
                    <div class="comic-list">
                        <?php foreach ($comicsList as $comic): ?>
                            <div class="comic-item">
                                <a href="comic-detail.php?id=<?php echo $comic['id']; ?>" class="comic-link" target="_blank">
                                    <?php echo htmlspecialchars($comic['title']); ?>
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>