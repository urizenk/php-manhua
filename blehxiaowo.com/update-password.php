<?php
session_start();
require 'config.php'; // 引入配置文件

// 管理员权限验证
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    header('Location: admin-login.php'); // 跳转到管理员登录页
    exit;
}

$message = ''; // 用于显示操作结果

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    if (empty($new_password)) {
        $message = "新密码不能为空！";
    } else {
        try {
            // 直接插入明文密码
            $stmt = $pdo->prepare("INSERT INTO passwords (password) VALUES (?)");
            $stmt->execute([$new_password]); // 取消哈希处理

            // 清除Redis缓存
            if ($redis) {
                $redis->del(['latest_password', 'latest_password_id']);
            }

            // 记录操作日志
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt = $pdo->prepare("INSERT INTO audit_log (action, ip_address) VALUES ('密码更新', ?)");
            $stmt->execute([$ip]);

            // 清空所有用户会话
            session_destroy();
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $message = "更新失败: " . $e->getMessage();
        }
    }
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
    <title>更新密码</title>
    <link rel="stylesheet" href="update_style.css"> <!-- 引用外部CSS文件 -->
</head>
<body>
    <div class="auth-container">
        <h2>更新密码</h2>
        <form method="POST">
            <input type="password" name="new_password" placeholder="输入新密码" required>
            <button type="submit">更新</button>
            <?php if ($message): ?>
                <p style="color:red; margin-top:10px;"><?php echo $message; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>