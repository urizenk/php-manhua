<?php
session_start();
// 如果已经是管理员登录状态，跳转到密码更新页
if (isset($_SESSION['admin_verified'])) 
{
    header('Location: update-password.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_password = $_POST['admin_password'];
    // 验证管理员密码（假设管理员密码为 "admin123"）
    if ($admin_password === 'Loveseth188') {
        $_SESSION['admin_verified'] = true;
        header('Location: update-password.php');
        exit;
    } else {
        $error = "管理员密码错误";
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
    <title>管理员登录</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="auth-container">
        <h2>管理员登录</h2>
        <form method="POST">
            <input type="password" name="admin_password" placeholder="输入管理员密码" required>
            <button type="submit">登录</button>
            <?php if ($error): ?>
                <p style="color:red; margin-top:10px;"><?php echo $error ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>