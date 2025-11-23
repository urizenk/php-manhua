<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 海の小窝</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #FFF5E6 0%, #FFE4CC 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            padding: 20px;
        }
        .login-container { max-width: 420px; width: 100%; }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(255, 107, 53, 0.2);
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h2 {
            color: #FF6B35;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .login-header p { color: #999; font-size: 0.95rem; }
        .alert {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #FFD4B8;
            background: #FFF5E6;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #FF6B35;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        .btn-login {
            width: 100%;
            border-radius: 10px;
            padding: 14px;
            font-weight: bold;
            background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
            border: none;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #FF6B35 0%, #FF5722 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        }
        .btn-login:active { transform: translateY(0); }
        .login-footer { text-align: center; margin-top: 20px; }
        .login-footer small { color: #999; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>🐋 海の小窝</h2>
                <p>管理后台登录</p>
            </div>
            
            <?php 
            // Session已经在路由入口启动，无需再次启动
            if (isset($_SESSION['login_error'])): 
            ?>
                <div class="alert">
                    <?php 
                    echo htmlspecialchars($_SESSION['login_error'], ENT_QUOTES, 'UTF-8'); 
                    unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/admin88/login">
                <?php 
                // 生成CSRF Token
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-group">
                    <label class="form-label">用户名</label>
                    <input type="text" class="form-control" name="username" placeholder="请输入用户名" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">密码</label>
                    <input type="password" class="form-control" name="password" placeholder="请输入密码" required>
                </div>
                
                <button type="submit" class="btn-login">登 录</button>
            </form>
            
            <div class="login-footer">
                <small>默认账号：admin / admin123</small>
            </div>
        </div>
    </div>
</body>
</html>
