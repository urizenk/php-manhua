<?php
session_start();
// 如果已经是管理员登录状态，跳转到漫画管理页
if (isset($_SESSION['admin_verified']) && $_SESSION['admin_verified'] === true) {
    header('Location: admin-comics.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_password = $_POST['admin_password'];
    // 验证管理员密码
    if ($admin_password === 'Loveseth188') {
        $_SESSION['admin_verified'] = true;
        header('Location: admin-comics.php');
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .auth-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .auth-container h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .auth-container input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        
        .auth-container input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .auth-container button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .auth-container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .error {
            color: #dc3545;
            margin-top: 15px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2><i class="fas fa-lock"></i> 管理员登录</h2>
        <form method="POST">
            <input type="password" name="admin_password" placeholder="输入管理员密码" required>
            <button type="submit">登录</button>
            <?php if ($error): ?>
                <div class="error"><?php echo $error ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>