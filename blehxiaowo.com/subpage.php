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
            background-color: #FFF8DC; /* 奶黄色背景 */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* 页面高度占满整个视口 */
        }

        /* 容器样式 */
        .container {
            text-align: center;
            width: 85%;
            max-width: 600px; /* 容器最大宽度 */
            background-color: white; /* 容器背景为白色 */
            padding: 20px; /* 内边距 */
            border-radius: 15px; /* 圆角 */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* 阴影效果 */
        }

        /* 标题样式 */
        h1 {
            font-size: 36px;
            color: #333; /* 标题文字颜色 */
            margin-bottom: 20px; /* 标题与下方内容的间距 */
        }

        /* 标题下的提示语背景 */
        .title-notice {
            background-color: #FFF8DC; /* 提示语背景颜色为奶黄色 */
            padding: 8px; /* 内边距 */
            border-radius: 10px; /* 圆角 */
            margin-bottom: 30px; /* 提示语与下方内容的间距 */
            width: 80%; /* 宽度为容器的90% */
            margin-left: auto; /* 水平居中 */
            margin-right: auto; /* 水平居中 */
            text-align: center; /* 文字居中 */
        }
        
        .title-notice p {
            margin: 5px 0; /* 段落间距 */
            font-size: 16px; /* 文字大小 */
            font-weight: bold; /* 加粗 */
            color: #444; /* 加深文字颜色（从#555改为#333） */
        }
        
        .title-notice p a {
            color: #ff7700; /* 橙色 */
            font-weight: bold; /* 加粗 */
            text-decoration: none; /* 无下划线 */
        }

        /* 修改后的链接样式 */
        .link-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px; /* 按钮之间的间距 */
        }

        .link-container a {
            display: inline-block;
            width: 50%; /* 按钮宽度为容器的一半 */
            padding: 10px 20px; /* 内边距 */
            font-size: 18px; /* 文字大小 */
            font-weight: bold; /* 文字加粗 */
            text-decoration: none; /* 去除下划线 */
            color: #555; /* 文字颜色为灰色 */
            background-color: white; /* 按钮背景为白色 */
            border: 2px solid #FFA500; /* 橙色边框 */
            border-radius: 25px; /* 圆角 */
            transition: all 0.3s ease; /* 过渡效果 */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* 阴影效果 */
        }

        .link-container a:hover {
            background-color: #FFA500; /* 悬停时背景变为橙色 */
            color: white; /* 悬停时文字变为白色 */
            transform: scale(1.05); /* 悬停时按钮放大 */
            box-shadow: 0 0 8px rgba(255, 165, 0, 0.3); /* 悬停时阴影效果 */
        }

        /* 页面底部提示语 */
        .footer-notice {
            margin-top: 40px; /* 与上方内容的间距 */
            font-size: 10px; /* 文字大小 */
            color: #888; /* 文字颜色 */
            line-height: 1.5; /* 行高 */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>欢迎来到海の小窝🐳</h1>

        <!-- 标题下的提示语 -->
        <!--<div class="title-notice">
            <p>无偿分享 禁止盗卖 更多精彩微博@和泉与海</p>
        </div>-->
        <div class="title-notice">
            <p>无偿分享 禁止盗卖 更多精彩 </p>
            <p><a href="https://weibo.com/u/7623856715" target="_blank">微博@和泉与海</a></p>
        </div>
        <!-- 链接按钮 -->
        <div class="link-container">
            <a href="https://fcns1cjawycp.feishu.cn/docx/YOy0dFSVXosbBbxtlx7cwOHmnhh?from=from_copylink">防走丢</a>
            <a href="rgbk.php">日更板块</a>
            <a href="subpage2.php">韩漫合集</a>
            <a href="wjdm.php">完结短漫</a>
            <a href="rmtj.php">日漫推荐
            <a href="rmhj.php">日漫合集</a>
            <a href="dmhj.php">动漫合集</a>
            <a href="gbjhj.php">广播剧合集</a>
            <a href="https://box.n3ko.cc/_/bleh">失效反馈</a>
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