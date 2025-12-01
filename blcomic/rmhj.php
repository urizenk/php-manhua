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
    <title>日漫合集</title>
            <!-- 引入 FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFF0;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            font-size: 32px;
            font-weight: bold;
            color: #1B1212;
            margin-bottom: 20px;
            display: block; /* 独占一行 */
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
        /* 字母标签样式 */
        .letter-title {
            display: inline-block;
            background-color: #FFA500; /* 橙色背景 */
            color: white; /* 白色字体 */
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            margin: 10px 0; /* 上下外边距相同 */
            border-radius: 5px; /* 圆角矩形 */
        }

        p {
            font-size: 16px;
            margin: 5px 0;
        }

        a {
            color: #0077ff;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            display: block; /* 每个链接单独一行 */
            margin: 5px 0;
        }

        a:hover {
            text-decoration: underline;
        }

        .section {
            margin-bottom: 30px;
        }

        .section .link {
            display: flex;
            align-items: center;
            gap: 5px; /* 图标和文字之间的间距 */
        }

        .section .link::before {
            content: "🔗"; /* 默认链接图标 */
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>日漫合集</h1>

        <!-- Tip 标签 -->
        <div class="tip">
            Tip ：刷新后看最新！
        </div>
        
                <!-- 回到首页按钮 -->
        <a href="subpage.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i> <!-- FontAwesome 向左箭头图标 -->
            回到目录
        </a>
    <!-- 日漫合集链接 -->
    <div class="section">
        <div class="letter-title">AAA</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2gla2j">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">BBB</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2gla4b">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">CCC</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2gla7e">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">DDD</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2gla8f">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">EEE</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2gla9g">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">FFF</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glaah">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">GGG</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glabi">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">HHH</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glacj">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">JJJ</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glada">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">LLL</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glamj">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">MMM</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glaob">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">NNN</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glaqd">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">OOO</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glare">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">PPP</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glasf">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">QQQ</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glatg">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">RRR</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glazc">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">SSS</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glb0d">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">TTT</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glb2f">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">WWW</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glb3g">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">XXX</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glb5i">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">YYY</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glb7a">点我点我</a></div>
    </div>

    <div class="section">
        <div class="letter-title">ZZZ</div>
        <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g2glb8b">点我点我</a></div>
    </div>
</body>
</html>