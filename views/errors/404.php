<?php
/**
 * 404错误页面
 */
$pageTitle = '页面未找到 - 海の小窝';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #1976D2;
            margin: 0;
        }
        .error-title {
            font-size: 2rem;
            color: #333;
            margin: 20px 0;
        }
        .error-desc {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .btn-home {
            background: #1976D2;
            color: white;
            padding: 12px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(25, 118, 210, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">页面未找到</h2>
        <p class="error-desc">抱歉，您访问的页面不存在或已被删除</p>
        <a href="/" class="btn-home">返回首页</a>
    </div>
</body>
</html>
