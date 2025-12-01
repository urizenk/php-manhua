<?php
session_start();
require 'config.php';

// 如果已通过验证且缓存有效，直接跳转
if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
    header('Location: subpage.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_password = $_POST['password'];

    try {
        // 从缓存或数据库获取最新密码
        $latest_password = null;
        if ($redis && $redis->exists('latest_password')) {
            $latest_password = $redis->get('latest_password');
        } else {
            $stmt = $pdo->query("SELECT password FROM passwords ORDER BY id DESC LIMIT 1");
            $latest_password = $stmt->fetchColumn();
            if ($redis) {
                $redis->setex('latest_password', 18000, $latest_password); // 缓存5小时
            }
        }

        // 直接对比明文密码
        if ($input_password === $latest_password) {
            $_SESSION['verified'] = true;
            $_SESSION['password_version'] = $redis ? $redis->get('latest_password_id') : getLatestPasswordIdFromDB($pdo);
            header('Location: subpage.php');
            exit;
        } else {
            $error = "密码错啦！取今天最新的噢";
        }
    } catch (Exception $e) {
        $error = "访问人数太多啦！等会儿再试趴";
    }
}

// 从数据库获取最新密码ID的辅助函数
function getLatestPasswordIdFromDB($pdo) {
    $stmt = $pdo->query("SELECT id FROM passwords ORDER BY id DESC LIMIT 1");
    return $stmt->fetchColumn();
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
    <title>密码验证</title>
    <link rel="stylesheet" href="style.css"> <!-- 引用外部CSS文件 -->
</head>
<body>
    <div class="auth-container">
        <h2>请输入访问码</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="输入密码，不会就看下方取码教程" required>
            <button type="submit">提交</button>
            <?php if ($error): ?>
                <p style="color:red; margin-top:10px;"><?php echo $error ?></p>
            <?php endif; ?>
        </form>

        <!-- 新手吃粮教程链接 -->
        <div class="tutorial-link">
            <a href="https://uwn61ldr2m3.feishu.cn/wiki/YnWcwLOK7i4LDWkXMDJcpwXbnjb?from=from_copylink">🎉取码教程</a>
        </div>

        <!-- 获取每日访问码标签 -->
        <div class="access-code-label">
            获取每日访问码👇
        </div>

        <!-- 三个文字链接 -->
        <div class="access-code-links">
            <a href="https://drive.uc.cn/s/48203e8e3e834?public=1">UC</a>
            <a href="https://pan.quark.cn/s/84bb727bc9a5">夸克</a>
            <a href="https://pan.xunlei.com/s/VOelT05yx8pP0Xswi1PFTAvjA1?pwd=rdhc">迅雷</a>
        </div>
    </div>
</body>
</html>