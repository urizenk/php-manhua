<?php
session_start();
require 'config.php';

// 如果已通过验证且缓存有效，直接跳转
if (isset($_SESSION['verified']) && $_SESSION['verified'] !== true) {
    header('Location: subpage.php');
    exit;
}

// 获取访问码链接
try {
    $stmt = $pdo->query("SELECT name, url FROM access_links WHERE is_active = TRUE ORDER BY sort_order");
    $access_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 如果出错，使用默认链接
    $access_links = [
        ['name' => 'UC', 'url' => 'https://drive.uc.cn/s/cb4e628325674?public=1'],
        ['name' => '夸克', 'url' => 'https://pan.quark.cn/s/c4591b20fe71'],
        ['name' => '迅雷', 'url' => 'https://pan.xunlei.com/s/VOdiW3F949QyGlgHOGAWJgLVA1?pwd=e29r']
    ];
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

        <!-- 动态访问码链接 -->
        <div class="access-code-links">
            <?php foreach ($access_links as $link): ?>
                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['name']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>