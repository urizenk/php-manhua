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
    <title>海の小窝</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* 全局样式 */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #FFF8DC;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* 容器样式 */
        .container {
            text-align: center;
            width: 85%;
            max-width: 600px;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* 标题样式 */
        h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
        }

        /* 标题下的提示语背景 */
        .title-notice {
            background-color: #FFF8DC;
            padding: 8px;
            border-radius: 10px;
            margin-bottom: 30px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        
        .title-notice p {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
            color: #444;
        }
        
        .title-notice p a {
            color: #ff7700;
            font-weight: bold;
            text-decoration: none;
        }

        /* 分类导航 */
        .category-nav {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .category-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-color: #FFA500;
        }

        .category-item i {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: #FFA500;
        }

        .category-item span {
            font-weight: bold;
            font-size: 1rem;
        }

        /* 修改后的链接样式 */
        .link-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .link-container a {
            display: inline-block;
            width: 50%;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            color: #555;
            background-color: white;
            border: 2px solid #FFA500;
            border-radius: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .link-container a:hover {
            background-color: #FFA500;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 0 8px rgba(255, 165, 0, 0.3);
        }

        /* 页面底部提示语 */
        .footer-notice {
            margin-top: 40px;
            font-size: 10px;
            color: #888;
            line-height: 1.5;
        }
    </style>
    <!-- 引入 FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>欢迎来到海の小窝🐳</h1>

        <!-- 标题下的提示语 -->
        <div class="title-notice">
            <p>无偿分享 禁止盗卖 更多精彩 </p>
            <p><a href="https://weibo.com/u/76856715" target="_blank">微博@资源小站</a></p>
        </div>

        <!-- 漫画分类导航 -->
        <div class="category-nav">
            <a href="hmhj.php" class="category-item">
                <i class="fas fa-book"></i>
                <span>韩漫合集</span>
            </a>
            <a href="rgbk.php" class="category-item">
                <i class="fas fa-calendar-alt"></i>
                <span>日更板块</span>
            </a>
            <a href="wjdm.php" class="category-item">
                <i class="fas fa-medal"></i>
                <span>完结短漫</span>
            </a>
            <a href="rmtj.php" class="category-item">
                <i class="far fa-hand-peace"></i>
                <span>日漫推荐</span>
            </a>
            <a href="rmhj.php" class="category-item">
                <i class="fas fa-gift"></i>
                <span>日漫合集</span>
            </a>
            <a href="dmhj.php" class="category-item">
                <i class="fas fa-film"></i>
                <span>动漫合集</span>
            </a>
            <a href="gbjhj.php" class="category-item">
                <i class="fas fa-headphones"></i>
                <span>广播剧合集</span>
            </a>
            <a href="https://box.n3ko.cc/_/bleh" class="category-item">
                <i class="far fa-comment"></i>
                <span>失效反馈</span>
            </a>
            <a href="https://fcns1cjawycp.feishu.cn/docx/YOy0dFSVXosbBbxtlx7cwOHmnhh?from=from_copylink" class="category-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>防走丢</span>
            </a>
            <a href="subpage.php" class="category-item">
                <i class="fas fa-spinner"></i>
                <span>待补充</span>
            </a>
        </div>

        <!-- 页面底部提示语 -->
        <div class="footer-notice">
            <p>本网站网址数据来源于互联网搜索</p>
            <p>和热心网友投稿,喜欢请支持作者</p>
            <p>Copyright ©2024本地保存请勿超过24小时 特此声明</p>
        </div>
    </div>
</body>
</html>