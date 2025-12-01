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

// 获取韩漫合集数据
try {
    // 获取连载中漫画
    $stmt = $pdo->prepare("
        SELECT c.id, c.title, c.episodes, c.thumbnail, c.view_count, c.status,
               GROUP_CONCAT(DISTINCT t.name) as tags
        FROM comics c 
        LEFT JOIN comic_tag_relations r ON c.id = r.comic_id 
        LEFT JOIN comic_tags t ON r.tag_id = t.id 
        WHERE c.category_id = 2
        GROUP BY c.id 
        ORDER BY c.title
    ");
    $stmt->execute();
    $allComics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 按状态和字母分组
    $ongoingComics = [];
    $completedComics = [];
    
    foreach ($allComics as $comic) {
        $firstChar = mb_substr($comic['title'], 0, 1, 'UTF-8');
        if (!preg_match('/^[A-Za-z]$/u', $firstChar)) {
            $firstChar = '其他';
        }
        
        if ($comic['status'] === '连载中') {
            $ongoingComics[$firstChar][] = $comic;
        } else {
            $completedComics[$firstChar][] = $comic;
        }
    }
    
    // 按字母顺序排序
    ksort($ongoingComics);
    ksort($completedComics);
    
} catch (PDOException $e) {
    error_log("获取韩漫数据失败: " . $e->getMessage());
    $ongoingComics = [];
    $completedComics = [];
}

// 引入HTML模板
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
    <title>韩漫合集</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFF0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 20px;
            padding-left: 20px;
        }

        .tip {
            background-color: #FFE4B5;
            padding: 8px 12px;
            border-radius: 25px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
            display: inline-block;
            max-width: 100%;
        }

        .container {
            text-align: left;
            max-width: 800px;
            width: 100%;
        }

        .title {
            color: #1B1212d0;
            font-size: 32px;
            font-weight: bold;
            padding: 10px 0;
            display: block;
            margin-bottom: 20px;
        }

        .a-title {
            display: inline-block;
            background-color: #FFA500;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        /* 漫画链接样式 */
        .comic-link {
            display: flex;
            align-items: center;
            background-color: transparent;
            color: #0077ff;
            text-decoration: none;
            padding: 12px 15px;
            margin: 5px 0;
            font-size: 16px;
            line-height: 1.0;
            width: 100%;
            border: 1px solid transparent;
            box-sizing: border-box;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .comic-link:hover {
            color: #D18E85;
            background-color: #f8f9fa;
            border-color: #e9ecef;
            transform: translateX(5px);
        }

        .comic-thumbnail {
            width: 40px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 12px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .comic-info {
            flex: 1;
        }

        .comic-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .comic-meta {
            font-size: 12px;
            color: #666;
            display: flex;
            gap: 15px;
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-ongoing {
            background-color: #e4f4ff;
            color: #3498db;
        }

        .status-completed {
            background-color: #ffe4e4;
            color: #e74c3c;
        }

        .episode-info {
            color: #e74c3c;
        }

        .view-count {
            color: #95a5a6;
        }

        .tags {
            color: #3498db;
        }

        /* 回到首页按钮样式 */
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

        /* 小标题样式 */
        h4 {
            margin: 15px 0 10px 0;
            font-size: 18px;
            color: #333;
            padding-left: 10px;
            border-left: 4px solid #FFA500;
        }

        /* 搜索框样式 */
        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
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

        #viewButton {
            padding: 10px 20px;
            background-color: #FFA500;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #viewButton:hover {
            background-color: #e69500;
        }

        .search-result {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
            padding-left: 10px;
        }

        .highlight {
            background-color: #FFA50033;
            color: #FFA500;
            font-weight: bold;
            padding: 0 2px;
            border-radius: 3px;
        }

        .no-results {
            text-align: center;
            color: #999;
            padding: 40px;
            font-style: italic;
        }

        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #FFA500, transparent);
            margin: 30px 0;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="title">韩漫合集</span></h1>
        
        <div class="tip">
            Tip ：单部漫的密码就是每日访问码，一码通用喔！刷新后才能看到新增漫漫！
        </div>
        
        <a href="index.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i>
            回到目录
        </a>
        
        <!-- 搜索框 -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="漫名不用打全称，用关键词搜索...">
            <button id="viewButton">查看</button>
            <div class="search-result" id="searchResult"></div>
        </div>

        <!-- 新增漫画 -->
        <div class="new-comics-section">
            <span class="a-title">新增漫</span>
            <!-- 这里可以放置新增漫画，暂时留空 -->
        </div>

        <!-- 按字母分类的漫画列表 -->
        <?php 
        $allLetters = array_unique(array_merge(array_keys($ongoingComics), array_keys($completedComics)));
        sort($allLetters);
        ?>

        <?php if (empty($allLetters)): ?>
            <div class="no-results">
                <i class="fas fa-book-open" style="font-size: 48px; margin-bottom: 16px; color: #ddd;"></i>
                <div>暂无漫画数据</div>
            </div>
        <?php else: ?>
            <?php foreach ($allLetters as $letter): ?>
            <div class="comic-group" data-letter="<?php echo $letter; ?>">
                <span class="a-title"><?php echo htmlspecialchars($letter); ?></span>
                
                <!-- 连载中漫画 -->
                <?php if (isset($ongoingComics[$letter])): ?>
                <h4>连载中</h4>
                <div class="ongoing-comics">
                    <?php foreach ($ongoingComics[$letter] as $comic): ?>
                    <a href="comic-detail.php?id=<?php echo $comic['id']; ?>" 
                       class="comic-link" 
                       data-title="<?php echo htmlspecialchars($comic['title']); ?>">
                        <?php if (!empty($comic['thumbnail'])): ?>
                            <img src="<?php echo htmlspecialchars($comic['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($comic['title']); ?>" 
                                 class="comic-thumbnail">
                        <?php else: ?>
                            <div class="comic-thumbnail"></div>
                        <?php endif; ?>
                        <div class="comic-info">
                            <div class="comic-title"><?php echo htmlspecialchars($comic['title']); ?></div>
                            <div class="comic-meta">
                                <span class="status-badge status-ongoing">连载中</span>
                                <?php if (!empty($comic['episodes'])): ?>
                                    <span class="episode-info"><?php echo htmlspecialchars($comic['episodes']); ?></span>
                                <?php endif; ?>
                                <span class="view-count">
                                    <i class="fas fa-eye"></i> <?php echo number_format($comic['view_count']); ?>
                                </span>
                                <?php if (!empty($comic['tags'])): ?>
                                    <span class="tags"><?php echo htmlspecialchars($comic['tags']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- 完结漫画 -->
                <?php if (isset($completedComics[$letter])): ?>
                <h4>完结</h4>
                <div class="completed-comics">
                    <?php foreach ($completedComics[$letter] as $comic): ?>
                    <a href="comic-detail.php?id=<?php echo $comic['id']; ?>" 
                       class="comic-link" 
                       data-title="<?php echo htmlspecialchars($comic['title']); ?>">
                        <?php if (!empty($comic['thumbnail'])): ?>
                            <img src="<?php echo htmlspecialchars($comic['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($comic['title']); ?>" 
                                 class="comic-thumbnail">
                        <?php else: ?>
                            <div class="comic-thumbnail"></div>
                        <?php endif; ?>
                        <div class="comic-info">
                            <div class="comic-title"><?php echo htmlspecialchars($comic['title']); ?></div>
                            <div class="comic-meta">
                                <span class="status-badge status-completed">完结</span>
                                <?php if (!empty($comic['episodes'])): ?>
                                    <span class="episode-info"><?php echo htmlspecialchars($comic['episodes']); ?></span>
                                <?php endif; ?>
                                <span class="view-count">
                                    <i class="fas fa-eye"></i> <?php echo number_format($comic['view_count']); ?>
                                </span>
                                <?php if (!empty($comic['tags'])): ?>
                                    <span class="tags"><?php echo htmlspecialchars($comic['tags']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <hr class="section-divider">
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 内联 JavaScript -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const viewButton = document.getElementById('viewButton');
        const allLinks = document.querySelectorAll('.comic-link');
        const searchResult = document.getElementById('searchResult');

        // 实时搜索
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            let matchCount = 0;
            let firstMatch = null;

            allLinks.forEach(link => {
                const title = link.getAttribute('data-title').toLowerCase();
                const index = title.indexOf(searchTerm);
                
                // 清除之前的高亮
                const titleElement = link.querySelector('.comic-title');
                titleElement.innerHTML = titleElement.textContent;

                if (searchTerm && index > -1) {
                    // 高亮匹配文字
                    const originalText = titleElement.textContent;
                    const highlighted = originalText.substring(0, index) + 
                        `<span class="highlight">${originalText.substr(index, searchTerm.length)}</span>` +
                        originalText.substring(index + searchTerm.length);
                    titleElement.innerHTML = highlighted;
                    
                    link.style.display = 'flex';
                    link.parentElement.style.display = 'block';
                    link.closest('.comic-group').style.display = 'block';
                    if (!firstMatch) firstMatch = link;
                    matchCount++;
                } else {
                    link.style.display = searchTerm ? 'none' : 'flex';
                    // 隐藏空的字母组
                    const group = link.closest('.comic-group');
                    const visibleLinks = group.querySelectorAll('.comic-link[style="display: flex;"]');
                    if (visibleLinks.length === 0) {
                        group.style.display = 'none';
                    } else {
                        group.style.display = 'block';
                    }
                }
            });

            // 显示搜索结果
            if (searchTerm) {
                searchResult.textContent = matchCount ? `找到 ${matchCount} 个结果` : '没有找到匹配内容';
            } else {
                searchResult.textContent = '';
                // 重置所有显示
                document.querySelectorAll('.comic-group').forEach(group => {
                    group.style.display = 'block';
                });
            }
        });

        // 查看按钮点击事件
        viewButton.addEventListener('click', () => {
            const searchTerm = searchInput.value.trim().toLowerCase();
            if (!searchTerm) {
                searchResult.textContent = '请输入搜索内容';
                return;
            }

            const firstVisibleLink = document.querySelector('.comic-link[style="display: flex;"]');
            if (firstVisibleLink) {
                firstVisibleLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                searchResult.textContent = '没有找到匹配内容';
            }
        });

        // 回车键触发查看
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                viewButton.click();
            }
        });
    </script>
</body>
</html>