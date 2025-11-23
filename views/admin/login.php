<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 海の小窝</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Microsoft YaHei', Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #1976D2;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #1976D2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        }
        .btn-login {
            width: 100%;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            background: #1976D2;
            border: none;
            color: white;
            margin-top: 20px;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>🐋 海の小窝</h2>
                <p class="text-muted">管理后台登录</p>
            </div>
            
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['login_error']; 
                    unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/admin88/login">
                <?php 
                // 生成CSRF Token（登录页面需要先初始化Session）
                if (!isset($session)) {
                    session_start();
                    if (!isset($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
                } else {
                    echo $session->csrfField();
                }
                ?>
                
                <div class="mb-3">
                    <label class="form-label">用户名</label>
                    <input type="text" class="form-control" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">密码</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-login">登录</button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">默认账号：admin / admin123</small>
            </div>
        </div>
    </div>
</body>
</html>


