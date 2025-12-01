<?php
session_start();
require 'config.php';
require 'functions.php'; // 引入辅助函数文件

// 基础会话验证
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: index.php');
    exit;
}

// 密码版本验证
try {
    // 从缓存或数据库获取最新密码ID
    $latest_password_id = null;
    if ($redis && $redis->exists('latest_password_id')) {
        $latest_password_id = $redis->get('latest_password_id');
    } else {
        $latest_password_id = getLatestPasswordIdFromDB($pdo);
        if ($redis) {
            $redis->setex('latest_password_id', 18000, $latest_password_id); // 缓存5分钟
        }
    }

    // 对比会话中的密码版本
    if ($_SESSION['password_version'] != $latest_password_id) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    // 降级处理：允许访问但记录日志
    error_log("密码版本验证失败: " . $e->getMessage());
}

// 分页设置
$perPage = 30; // 每页显示数量
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 2; // 当前页码

// 示例数据（需替换为真实数据）
 
$articles = [
    [
        'url' => ' https://pan.baidu.com/s/166sQ-h7gHCIdJbE2q77dgA?pwd=bleh', // 根据关键词“甜美的”更新
        'image' => '/images/警察.webp',
        'title' => '发出甜美的娇喘吧，警察先生~', 
        'episodes' => '全7話',
        'member' => false
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOPt7s_PMRCHy2np9w3ZsvscA1?pwd=jziq#', // 根据关键词“攻或受”更新
        'image' => '/images/攻或受.webp',
        'title' => '攻或受你想當哪個？',
        'episodes' => '全3話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1Oc0Wg_2WzdRoNqDQQ33Bjg?pwd=bleh',
        'image' => '/images/白昼33.webp',
        'title' => '白昼限定甜蜜赏味期',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOPpv7aVKjtMjvAjm26w8_6rA1?pwd=cjd8#',
        'image' => 'images/脱掉.webp',
        'title' => '这么喜欢就亲自帮我脱掉',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1Cx4K5DdXybRoPKpVnjnskQ?pwd=bleh',
        'image' => 'images/深见2.jpg',
        'title' => '格外性感的深见君',
        'episodes' => '1-13话（连载中）',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1rwieI1ss7jfX7cfm2_Tr6A?pwd=bleh', // 根据关键词“琥珀”更新
        'image' => 'images/爱情32.webp',
        'title' => '还算不上是爱情', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOOxJzW_w9uE1nBZ2V95E9CXA1?pwd=zuaf#', // 根据关键词“琥珀”更新
        'image' => 'images/爱我31.webp',
        'title' => '别说什么你爱我', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1syk0AZXFKg9MeE4riHy_MQ?pwd=bleh', // 根据关键词“琥珀”更新
        'image' => 'images/星期五30.webp',
        'title' => '星期五的年下男友', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1FHO2FJlzyf56Yf4SGyUQHg?pwd=bleh', // 根据关键词“琥珀”更新
        'image' => 'images/琥珀2.webp',
        'title' => '他的小琥珀His little amber', 
        'episodes' => '全11話',
        'member' => false
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/1JQPbGdZguJeDWVwsKlu-Tg?pwd=bleh',
        'image' => 'images/衣下30.webp',
        'title' => '衣下秘密不可告人',
        'episodes' => '全3话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => ' https://pan.baidu.com/s/1nw9Jp69aaOKv9cTuIEyl9A?pwd=bleh',
        'image' => 'images/近夜29.webp',
        'title' => '近夜黄昏的Sugar cat',
        'episodes' => '全5话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1BxbyYi937SW5fXLvNvXHPA?pwd=bleh',
        'image' => 'images/治愈28.webp',
        'title' => '治愈悖论',
        'episodes' => '更至2卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1ntd2jrTJLKC91VL2XS2vzw?pwd=bleh',
        'image' => 'images/伊古纳多27.webp',
        'title' => '伊谷纳多的新娘',
        'episodes' => '第一卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1qLc-IBxDqRgm6TYt1iNcSQ?pwd=bleh',
        'image' => 'images/count26.webp',
        'title' => '10 count',
        'episodes' => '全6卷',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.baidu.com/s/1mQYY6ZVd-Dia65GCid5USw?pwd=bleh ',
        'image' => 'images/青蓝25.webp',
        'title' => '于青蓝之夜唇齿交融',
        'episodes' => '更至01（连载中）',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1FkDhX0a98DqEtQcjW9wRJA?pwd=bleh  ',
        'image' => 'images/菁英24.webp',
        'title' => '菁英医生不可告人的烦恼',
        'episodes' => '全2卷',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.baidu.com/s/1sYAH-CZTLkaJXxDvFjAJDA?pwd=bleh ',
        'image' => 'images/怀孕23.webp',
        'title' => '不要让我怀孕',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1bDKllNxqubZOmtpC0Z36nw?pwd=bleh',
        'image' => 'images/ppatta.webp',
        'title' => 'ppatta大合集',
        'episodes' => '全40篇',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1zLyKbcY7cAPJO1RFIppsWg?pwd=bleh',
        'image' => 'images/离不开.webp',
        'title' => '离不开,也逃不了',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.baidu.com/s/1-E_Y2HwqEj-nJlPslxrJSA?pwd=bleh',
        'image' => 'images/无节操全.webp',
        'title' => '无节操☆Bitch社',
        'episodes' => '全5卷（更新中）',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/13uFrbiVEopsnjyoaKe5Oqw?pwd=bleh',
        'image' => 'images/维克多.webp',
        'title' => '我美丽的维克多',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/1H-b7eu-sU29pc9LoKvU4ZQ?pwd=bleh',
        'image' => 'images/无节操2.webp',
        'title' => '无节操☆Bitch社 02',
        'episodes' => '第二卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1AmZMPFcxAibVZ4mxCKqLsg?pwd=bleh',
        'image' => 'images/无节操.webp',
        'title' => '无节操☆Bitch社 01',
        'episodes' => '第一卷',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/1Vc-v6hMCXBzfFGQQ5o6sOg?pwd=bleh',
        'image' => 'images/极道.webp',
        'title' => 'Gotcha！距离感为零的极道的乳头今天也在诱惑着我～',
        'episodes' => '全6話',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1jlNz1CZucsJm2EMbwUHJww?pwd=bleh',
        'image' => 'images/同床守则.webp',
        'title' => '同床守则',
        'episodes' => '全6話',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1vMqL3iPcqcbaWCJvCbO8mA?pwd=bleh ', // 根据关键词“山神”更新
        'image' => 'images/山神.webp',
        'title' => '山神大人与他的宠儿', 
        'episodes' => '全5話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/10L6WtDskhWbGqvWlE_Z80g?pwd=bleh ', // 根据关键词“共鸣恋情”更新
        'image' => 'images/新娘 .webp',
        'title' => 'α的新娘 共鸣恋情', 
        'episodes' => '全4卷',
        'member' => false
    ],

    [
        'url' => 'https://pan.baidu.com/s/12FuWQ2vbSWtdREwi_RCC5Q?pwd=bleh', // 根据关键词“很可爱”更新
        'image' => 'images/不可爱.webp',
        'title' => '是很可愛啦但不可愛', 
        'episodes' => '全9話',
        'member' => false
    ],

    [
        'url' => 'https://pan.baidu.com/s/1J_FdkXbywYPbc3KK1TyGtw?pwd=bleh', // 根据关键词“祭品”更新
        'image' => 'images/祭品.webp',
        'title' => '被退货的祭品',
        'episodes' => '全6話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1AN1U7ntnDKAuvtrHs9nrjg?pwd=bleh', // 根据关键词“人鱼”更新
        'image' => 'images/人鱼.webp',
        'title' => '捡到的人鱼想和我交尾',
        'episodes' => '全2話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1BqxpSgku-hy2w7MIH9B5fA?pwd=bleh', // 根据关键词“双子”更新
        'image' => 'images/双子.webp',
        'title' => '双子与老师',
        'episodes' => '全6話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1ejNQccjjMTpKF-9490VuvQ?pwd=bleh', // 根据关键词“办公室”更新
        'image' => 'images/猎豹.webp',
        'title' => '办公室的猎豹', 
        'episodes' => '全6話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1fketu_LduF9WadSq4FPqag?pwd=bleh', // 根据关键词“会长”更新
        'image' => 'images/会长.webp',
        'title' => '会长，别再装乖孩子了', 
        'episodes' => '全6話',
        'member' => false
    ],

    [
        'url' => 'https://pan.baidu.com/s/1Ykbu5ytY6mQ22P6J3ZjdAQ?pwd=bleh', // 根据关键词“狼先生”更新
        'image' => 'images/性癖.webp',
        'title' => '性癖是脾气不好的狼先生',
        'episodes' => '全7话',
        'member' => false
    ],
    
    ];
$totalItems = count($articles);
$totalPages = ceil($totalItems / $perPage);
$currentItems = array_slice($articles, ($page-1)*$perPage, $perPage);
?>

<!DOCTYPE HTML>
<html>
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
            font-size: 12px;
            backdrop-filter: blur(2px); /* 毛玻璃效果 */
        }

        /* 悬停效果 */
        .thumbnail:hover .thumb {
            transform: scale(1.08);
        }
    </style>
</head>
<body>
    <h1>日漫推荐</h1>
        <!-- Tip 标签 -->
    <div class="tip">
        Tip ：刷新后看最新漫漫！提取码都是:bleh
    </div>

    <!-- 回到首页按钮 -->
    <a href="/index.php" class="back-to-index">
        <i class="fas fa-arrow-left"></i> <!-- FontAwesome 向左箭头图标 -->
        回到目录
    </a>
    <!-- 漫画搜索框 -->
    <div class="search-container">
    <input type="text" id="searchInput" placeholder="输入漫画名称...">
    <button id="searchBtn">查看</button>
    </div>
    
    <section class="container excerpts-wrap">
        <div class="excerpts">
            <?php foreach ($articles as $article): ?>
            <article class="item">
                <span class="thumbnail">
                    <a href="<?= $article['url'] ?>">
                        <img src="<?= $article['image'] ?>" class="thumb" alt="<?= $article['title'] ?>">
                        <?php if ($article['member']): ?>
                        <span class="label">N</span>
                        <?php endif; ?>
                    </a>
                </span>
                <h3>
                    <a href="<?= $article['url'] ?>"><?= $article['title'] ?></a>
                </h3>
                <footer><?= $article['episodes'] ?></footer>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="container excerpts-wrap">
        <div class="excerpts">
                <!-- 原有文章循环 -->
        </div>
            <!-- 添加无结果提示 -->
        <div id="noResults" style="display: none; text-align: center; padding: 20px; color: #D3D3D3;">
            没有搜到相关漫画哦！试试用漫画关键词搜索呀！
        </div>
    </section> 
    <!-- 分页导航 -->
    <div style="text-align: center; margin: 30px 0;">
        <div style="display: inline-block;">
            <?php 
            // 手动设置总页数（根据你实际要生成的页面数修改）
            $totalPages = 5; // 示例设置为5个分页
            
            // 当前页码（首页固定为1）
            $currentPage = 2;
            
            // 显示页码范围
            $start = max(1, $currentPage - 2);
            $end = min($totalPages, $currentPage + 2);
            ?>
    
            <?php if ($totalPages > 1): ?>
                <!-- 首页始终显示 -->
                <?php if ($currentPage > 1): ?>
                    <a href="index.php" style="display: inline-block; padding: 8px 12px; margin: 0 2px; text-decoration: none; color: #333; border: 1px solid #ddd; border-radius: 4px;">&laquo; 首页</a>
                <?php else: ?>
                    <span style="display: inline-block; padding: 8px 12px; margin: 0 2px; background-color: #FFA500; color: white; border: 1px solid #FFA500; border-radius: 4px;">1</span>
                <?php endif; ?>
    
                <!-- 中间页码 -->
                <?php if ($start > 2): ?>
                    <span style="padding: 8px 12px; margin: 0 2px;">...</span>
                <?php endif; ?>
    
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == 1) continue; // 跳过首页（已单独处理）?>
                    <?php if ($i == $currentPage): ?>
                        <span style="display: inline-block; padding: 8px 12px; margin: 0 2px; background-color: #FFA500; color: white; border: 1px solid #FFA500; border-radius: 4px;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="pages/page<?= $i ?>.php" style="display: inline-block; padding: 8px 12px; margin: 0 2px; text-decoration: none; color: #333; border: 1px solid #ddd; border-radius: 4px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
    
                <?php if ($end < $totalPages): ?>
                    <span style="padding: 8px 12px; margin: 0 2px;">...</span>
                <?php endif; ?>
    
                <!-- 末页按钮 -->
                <a href="pages/page<?= $totalPages ?>.php" style="display: inline-block; padding: 8px 12px; margin: 0 2px; text-decoration: none; color: #333; border: 1px solid #ddd; border-radius: 4px;">末页 &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
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