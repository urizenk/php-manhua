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

// 获取日漫推荐
try {
    $stmt = $pdo->prepare("
        SELECT c.*, GROUP_CONCAT(t.name) as tag_names
        FROM comics c
        LEFT JOIN comic_tag_relations r ON c.id = r.comic_id
        LEFT JOIN comic_tags t ON r.tag_id = t.id
        WHERE c.category_id = 4
        GROUP BY c.id
        ORDER BY c.id DESC
    ");
    $stmt->execute();
    $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comics = [];
}

// 分页设置
$perPage = 18;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalItems = count($comics);
$totalPages = ceil($totalItems / $perPage);
$currentItems = array_slice($comics, ($page-1)*$perPage, $perPage);

// 处理搜索
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
if($keyword) {
    $filteredItems = [];
    foreach($currentItems as $item) {
        if(stripos($item['title'], $keyword) !== false || 
           stripos($item['episodes'], $keyword) !== false) {
            $filteredItems[] = $item;
        }
    }
    $currentItems = $filteredItems;
    $totalItems = count($filteredItems);
    $totalPages = ceil($totalItems / $perPage);
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
    <title>日漫推荐</title>
    <!-- 引入 FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- 样式部分已省略 -->
        <style>
        /* 基础重置 */
        * {
            margin: 0; /* 移除所有元素的外边距 */
            padding: 0; /* 移除所有元素的内边距 */
            box-sizing: border-box; /* 设置盒模型为 border-box，使 padding 和 border 包含在元素宽度内 */
        }
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif; /* 设置全局字体为 Arial 或 sans-serif 备用 */
            background-color: #FFFFF0; /* 设置页面背景颜色为浅黄色 */
            margin: 0; /* 移除 body 的外边距 */
            padding: 5px; /* 设置 body 的内边距为 5px，使内容与页面边缘有一定距离 */
            color: #333; /* 设置全局文字颜色为深灰色 */
        }

        /* 容器样式 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px; /* 两端留白 */
        }
        
        /* Tip 标签样式 */
        .tip {
            background-color: #FFE4B5; /* 设置背景颜色为浅橙色 */
            padding: 8px 12px; /* 设置内边距为上下 8px，左右 12px */
            border-radius: 25px; /* 设置圆角为 25px，使标签呈现圆角矩形 */
            margin-bottom: 20px; /* 设置下方外边距为 20px，与下方内容保持距离 */
            font-size: 14px; /* 设置字体大小为 14px */
            color: #333; /* 设置文字颜色为深灰色 */
            display: block; /* 设置为块级元素，独占一行 */
            width: fit-content; /* 宽度根据内容自适应，避免撑满容器 */
        }
        
        /* 回到目录按钮样式 */
        .back-to-index {
            display: block; /* 设置为块级元素，独占一行 */
            background-color: #EC5800; /* 设置背景颜色为橙色 */
            color: white; /* 设置文字颜色为白色 */
            padding: 10px 15px; /* 设置内边距为上下 10px，左右 15px */
            border-radius: 5px; /* 设置圆角为 5px，使按钮呈现圆角矩形 */
            text-decoration: none; /* 移除链接的下划线 */
            font-size: 16px; /* 设置字体大小为 16px */
            font-weight: bold; /* 设置字体加粗 */
            transition: background-color 0.3s ease; /* 设置背景颜色变化的过渡效果 */
            margin-bottom: 20px; /* 设置下方外边距为 20px，与下方内容保持距离 */
            width: fit-content; /* 宽度根据内容自适应，避免撑满容器 */
        }
        
        .back-to-index:hover {
            background-color: #e69500; /* 设置悬停时背景颜色为深橙色 */
        }
        /* 网格布局系统 */
        .excerpts {
            display: grid; /* 使用网格布局 */
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* 设置列宽为最小 200px，最大 1fr（等分剩余空间） */
            gap: 15px; /* 设置网格项之间的间距为 15px */
            padding: 10px 0; /* 设置上下内边距为 10px，左右内边距为 0 */
        }
      

                /* 搜索框和按钮容器样式 */
        .search-container {
            display: flex; /* 使用 Flex 布局，使子元素水平排列 */
            align-items: center; /* 垂直居中对齐子元素 */
            gap: 10px; /* 设置子元素之间的间距为 10px */
            margin-bottom: 20px; /* 设置容器下方的外边距为 20px */
        }
        
        /* 搜索框样式 */
        #searchInput {
            flex: 1; /* 搜索框占据容器剩余的全部空间 */
            padding: 10px 15px; /* 设置内边距为上下 10px，左右 15px */
            border: 2px solid #FFA500; /* 设置边框为 2px 宽的橙色实线 */
            border-radius: 25px; /* 设置圆角为 25px，使搜索框呈现圆角矩形 */
            font-size: 16px; /* 设置字体大小为 16px */
            outline: none; /* 移除输入框聚焦时的默认轮廓线 */
            transition: all 0.3s ease; /* 设置所有属性的过渡效果，持续 0.3 秒 */
        }
        
        /* 搜索框聚焦状态样式 */
        #searchInput:focus {
            box-shadow: 0 0 8px rgba(255,165,0,0.3); /* 聚焦时添加橙色阴影效果 */
        }
        
        /* 查看按钮样式 */
        #searchBtn {
            padding: 10px 20px; /* 设置内边距为上下 10px，左右 20px */
            background-color: #FFA500; /* 设置背景颜色为橙色 */
            color: white; /* 设置文字颜色为白色 */
            border: none; /* 移除边框 */
            border-radius: 25px; /* 设置圆角为 25px，使按钮呈现圆角矩形 */
            font-size: 16px; /* 设置字体大小为 16px */
            cursor: pointer; /* 设置鼠标悬停时显示手型指针 */
            transition: background-color 0.3s ease; /* 设置背景颜色的过渡效果，持续 0.3 秒 */
        }
        
        /* 查看按钮悬停状态样式 */
        #searchBtn:hover {
            background-color: #e69500; /* 悬停时背景颜色变为深橙色 */
        }

        /* 移动端3列布局 */
        @media (max-width: 768px) {
            .excerpts {
                grid-template-columns: repeat(3, 1fr); /* 在移动端强制显示为 3 列 */
                gap: 10px; /* 设置网格项之间的间距为 10px */
            }
        }
        /* 图片容器 */
        .item {
            background: transparent; /* 设置背景为透明 */
            position: relative; /* 设置相对定位，用于内部元素的定位 */
            overflow: hidden; /* 隐藏超出容器的内容 */
        }

        /* 缩略图样式 */
        .thumbnail {
            display: block; /* 设置为块级元素 */
            position: relative; /* 设置相对定位 */
            padding-top: 140%; /* 设置顶部内边距为 140%，保持图片的宽高比 */
            border-radius: 12px; /* 设置圆角为 12px，使图片呈现圆角 */
            overflow: hidden; /* 隐藏超出容器的内容 */
        }
        
        .thumb {
            position: absolute; /* 设置绝对定位，相对于父容器定位 */
            top: 0; /* 距离顶部 0 */
            left: 0; /* 距离左侧 0 */
            width: 100%; /* 宽度占满父容器 */
            height: 100%; /* 高度占满父容器 */
            object-fit: cover; /* 保持图片比例，覆盖整个容器 */
            transition: transform 0.3s ease; /* 设置图片缩放过渡效果 */
            border-radius: 8px; /* 设置图片圆角为 8px */
        }
        h1 {
            color: #1B1212d0; /* 深灰色字体 */
            font-size: 32px;
            font-weight: bold;
            padding: 10px 0; /* 上下内边距 */
            display: block; /* 独占一行 */
            margin-bottom: 20px; /* 标题与内容之间的间距 */
        }

        /* 漫名样式 */
        h3 {
            margin: 12px 0;
            text-align: center;
        }

        h3 a {
            color: #28282B !important; /* 浅灰色 */
            font-weight: 600; /* 加粗 */
            text-decoration: none;
            font-size: 14px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* 限制两行 */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* 话数样式 */
        footer {
            text-align: center;
            padding: 2px 0; /* 减少间距 */
            margin-top: -10px; /* 上移 */
            color: #999;
            font-size: 13px;
            
        }

        /* 会员标签 */
        .label {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 72, 87, 0.9); /* 半透明效果 */
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 15px;
            backdrop-filter: blur(2px); /* 毛玻璃效果 */
        }

        /* 悬停效果 */
        .thumbnail:hover .thumb {
            transform: scale(1.08);
        }
        /* 新增分页样式 */
        /* 分页容器样式 */
        .pagination {
            /* 使用弹性布局(Flexbox) */
            display: flex;
            /* 水平居中对齐子元素 */
            justify-content: center;
            /* 上下边距50px，左右边距0 */
            margin: 60px 0;
            /* 允许子元素换行 */
            flex-wrap: wrap;
            /* 子元素之间的间隙为8px */
            gap: 10px;
        }
        
        /* 分页链接和页码数字的共同样式 */
        .pagination a, 
        .pagination span {
            /* 文字颜色为深灰色(#333) */
            color: #333;
            /* 内边距：上下8px，左右12px */
            padding: 10px 16px;
            /* 去除下划线等文本装饰 */
            text-decoration: none;
            /* 1px宽的浅灰色边框 */
            border: 1px solid #ddd;
            /* 4px圆角边框 */
            border-radius: 4px;
            /* 所有属性变化时添加0.3秒过渡效果 */
            transition: all 0.3s;
            /* 字体大小18px */
            font-size: 22px;
            /* 最小宽度32px */
            min-width: 40px;
            /* 文字居中对齐 */
            text-align: center;
        }
        
        /* 分页链接的悬停状态 */
        .pagination a:hover {
            /* 背景色变为橙色 */
            background-color: #FFA500;
            /* 文字颜色变为白色 */
            color: white;
            /* 边框颜色也变为橙色 */
            border-color: #FFA500;
        }
        
        /* 当前页码的样式 */
        .pagination .current {
            /* 背景色为橙色 */
            background-color: #FFA500;
            /* 文字颜色为白色 */
            color: white;
            /* 边框颜色为橙色 */
            border-color: #FFA500;
            /* 文字加粗 */
            font-weight: bold;
        }
        
        /* 禁用状态的分页元素(如不可点击的"上一页") */
        .pagination .disabled {
            /* 文字颜色为浅灰色 */
            color: #ddd;
            /* 禁用鼠标事件 */
            pointer-events: none;
            /* 边框颜色为浅灰色 */
            border-color: #ddd;
        }
        
        /* 移动端适配(屏幕宽度小于480px时生效) */
        @media (max-width: 480px) {
            /* 分页容器在移动端的调整 */
            .pagination {
                /* 缩小元素间隙为3px */
                gap: 3px;
            }
            
            /* 分页链接和页码数字在移动端的调整 */
            .pagination a, 
            .pagination span {
                /* 减小内边距 */
                padding: 6px 12px;
                /* 减小字体大小 */
                font-size: 16px;
                /* 减小最小宽度 */
                min-width: 36px;
            }
        }
    </style>
</head>
<body>
    <h1>日漫推荐</h1>
    <div class="tip">
        Tip ：①蓝奏云链接打不开的把链接lanzoum改成lanzov ②度盘提取码都是:bleh
    </div>
    <!-- 修改返回链接 -->
    <a href="subpage.php" class="back-to-index">
        <i class="fas fa-arrow-left"></i>
        回到目录
    </a>

    <!-- 搜索表单 - 客户端优化 -->
    <div class="search-container">
        <form method="get" style="display: contents;" id="searchForm">
            <input type="text" 
                   id="searchInput" 
                   name="keyword"
                   placeholder="漫名不用打全称，用关键词搜索..."
                   value="<?= htmlspecialchars($keyword) ?>"
                   autocomplete="off">
            <button type="submit" id="searchBtn">搜索</button>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <section class="container excerpts-wrap">
        <?php if(!empty($currentItems)): ?>
        <div class="excerpts">
            <?php foreach ($currentItems as $article): ?>
            <div class="item">
                <a href="<?= htmlspecialchars($article['url']) ?>" class="thumbnail" target="_blank">
                    <img class="thumb" src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                    <?php if($article['member']): ?>
                    <span class="label">会员</span>
                    <?php endif; ?>
                </a>
                <h3>
                    <a href="<?= htmlspecialchars($article['url']) ?>" target="_blank">
                        <?= htmlspecialchars($article['title']) ?>
                    </a>
                </h3>
                <footer><?= htmlspecialchars($article['episodes']) ?></footer>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页导航 -->
        <div class="pagination">
            <?php 
            $queryString = $keyword ? '&keyword='.urlencode($keyword) : '';
            
            if ($page > 1): ?>
                <a href="?page=1<?= $queryString ?>">首页</a>
                <a href="?page=<?= $page-1 ?><?= $queryString ?>">上一页</a>
            <?php else: ?>
                <span class="disabled">首页</span>
                <span class="disabled">上一页</span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if($start > 1) echo '<span>...</span>';
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if($end < $totalPages) echo '<span>...</span>'; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?><?= $queryString ?>">下一页</a>
                <a href="?page=<?= $totalPages ?><?= $queryString ?>">末页</a>
            <?php else: ?>
                <span class="disabled">下一页</span>
                <span class="disabled">末页</span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div id="noResults" style="text-align: center; padding: 20px; color: #333;">
            没有搜到相关漫画哦！试试用漫画关键词搜索呀！例如：格外性感的深见 搜索：深见
        </div>
        <?php endif; ?>
    </section>

    <!-- 脚本部分 -->
    <script>
        // 获取元素
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchBtn');
        const items = document.querySelectorAll('.item');
        const noResults = document.getElementById('noResults');
    
        // 保存原始标题HTML（用于清除高亮）
        items.forEach(item => {
            const titleElement = item.querySelector('h3 a');
            titleElement.dataset.original = titleElement.innerHTML; // 存储原始HTML
        });
    
        function highlightMatches(text, searchTerm) {
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }
    
        function performSearch() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            let hasMatch = false;
            let visibleCount = 0;
    
            items.forEach(item => {
                const titleElement = item.querySelector('h3 a');
                const originalTitle = titleElement.textContent; // 保留原始大小写
                const footer = item.querySelector('footer').textContent.toLowerCase();
                
                // 恢复原始内容（避免高亮残留）
                titleElement.innerHTML = titleElement.dataset.original;
    
                // 匹配逻辑（标题保留大小写匹配）
                const titleMatch = originalTitle.toLowerCase().includes(searchTerm);
                const footerMatch = footer.includes(searchTerm);
                const isVisible = titleMatch || footerMatch;
    
                item.style.display = isVisible ? 'block' : 'none';
                if (isVisible) {
                    visibleCount++;
                    // 高亮处理（保留原始大小写）
                    if (searchTerm) {
                        titleElement.innerHTML = highlightMatches(originalTitle, searchTerm);
                    }
                }
            });
    
            // 处理无结果提示
            noResults.style.display = (searchTerm && visibleCount === 0) ? 'block' : 'none';
    
            // 清空时完全恢复
            if (!searchTerm) {
                items.forEach(item => {
                    item.style.display = 'block';
                    const titleElement = item.querySelector('h3 a');
                    titleElement.innerHTML = titleElement.dataset.original; // 还原原始HTML
                });
                noResults.style.display = 'none';
            }
        }
    
        // 绑定事件（增加input监听实现实时搜索）
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('input', performSearch); // 输入时实时触发
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch();
        });
    </script>
</body>
</html>