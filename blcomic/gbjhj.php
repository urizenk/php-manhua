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
            $redis->setex('latest_password_id', 300, $latest_password_id); // 缓存5分钟
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
    <title>广播剧合集</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFF0; /* 奶黄色背景 */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-start; /* 内容靠左 */
            align-items: flex-start; /* 内容从顶部开始排列 */
            min-height: 100vh; /* 确保页面高度占满整个视口 */
            padding-top: 20px; /* 顶部留出间距 */
            padding-left: 20px; /* 左侧留出间距 */
        }

        /* 容器样式 */
        .container {
            text-align: left; /* 内容靠左 */
            max-width: 600px; /* 限制容器宽度 */
            width: 100%;
        }

        /* 页面标题样式 */
        .title {
            color: #1B1212d0; /* 深灰色字体 */
            font-size: 32px;
            font-weight: bold;
            padding: 10px 0; /* 上下内边距 */
            display: block; /* 独占一行 */
            margin-bottom: 20px; /* 标题与内容之间的间距 */
        }

        /* A 标签的标题样式（橙色背景，去掉【】） */
        .a-title {
            display: inline-block;
            background-color: #FFA500; /* 橙色背景 */
            color: white; /* 白色字体 */
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            margin: 10px 0; /* 上下外边距相同 */
            border-radius: 5px; /* 圆角矩形 */
        }

        /* 超链接样式 */
        a {
            display: block; /* 让链接独占一行 */
            background-color: transparent; /* 透明背景 */
            color: #0077ff; /* 蓝色字体 */
            text-decoration: none; /* 去除下划线 */
            padding: 8px 15px; /* 设置内边距 */
            margin: 3px 0; /* 链接之间的行间距 */
            font-size: 16px;
            line-height: 1.0; /* 增加行间距 */
            width: fit-content; /* 让链接宽度自适应内容 */
            border: 1px solid transparent; /* 添加透明边框 */
            box-sizing: border-box; /* 确保内边距和边框包含在元素宽度内 */
            transition: color 0.3s ease; /* 添加过渡效果 */
        }
        /* Tip 标签样式 */
        .tip {
            background-color: #FFE4B5; /* 浅橙色背景 */
            padding: 8px 12px; /* 内边距 */
            border-radius: 25px; /* 圆角 */
            margin-bottom: 20px; /* 与下方内容的间距 */
            font-size: 14px;
            color: #333;
            display: block; /* 修改为块级元素，独占一行 */
            width: fit-content; /* 宽度根据内容自适应 */
        }

        /* 回到目录按钮样式 */
        .back-to-index {
            display: block; /* 修改为块级元素，独占一行 */
            background-color: #EC5800; /* 绿色背景 */
            color: white; /* 白色文字 */
            padding: 10px 15px;
            border-radius: 5px; /* 圆角矩形 */
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 20px; /* 与下方内容的间距 */
            width: fit-content; /* 宽度根据内容自适应 */
        }

        .back-to-index:hover {
            background-color: #e69500; /* 悬停时颜色加深 */
        }

        /* 点击效果 */
        a:active {
            background-color: #e0e0e0; /* 点击时背景色加深 */
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
            background-color: #e69500; /* 悬停时颜色加深 */
        }

    </style>
</head>
<body>
    <div class="container">
        <h1><span class="title">广播剧合集</span></h1>
                <div class="tip">
            Tip ：刷新后才能看到新增的！
        </div>
                        <!-- 回到首页按钮 -->
        <a href="subpage.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i> <!-- FontAwesome 向左箭头图标 -->
            回到目录
        </a>
        
        <!-- 动漫列表 -->
        <div>
            <span class="a-title">A-D</span>
            <a href="https://pan.quark.cn/s/63c5f95c6530">点我点我</a>
        </div>

        <div>
            <span class="a-title">E-J</span>
            <a href="https://pan.quark.cn/s/b3462887ebac">点我点我</a>
        </div>

        <div>
            <span class="a-title">K-Z</span>
            <a href="https://pan.quark.cn/s/851b77baf9c9">点我点我</a>
        </div>
    </div>

</body>
</html>