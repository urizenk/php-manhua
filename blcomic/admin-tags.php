<?php
// require 'config.php';  // 注释掉原来的配置
session_start();
// 专门为标签管理使用blmh_site数据库
$host = 'localhost';
$dbname = 'blmh_site';  // 漫画数据库
$username = 'blmh_site';
$password = '4BtsZWFSQJNHABRc';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

require 'functions.php';

// 管理员权限验证
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// 莫兰迪色系配置
$morandiColors = [
    'red' => ['bg' => '#E8B4B8', 'text' => '#C44A5A'],
    'pink' => ['bg' => '#E8C3C9', 'text' => '#C44A6A'],
    'blue' => ['bg' => '#B4C7E8', 'text' => '#4A6AC4'],
    'yellow' => ['bg' => '#E8D8B4', 'text' => '#C4A44A'],
    'purple' => ['bg' => '#D4B4E8', 'text' => '#8A4AC4'],
    'orange' => ['bg' => '#E8C8B4', 'text' => '#C46A4A']
];

// 添加标签
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            // 随机选择颜色
            $colorKeys = array_keys($morandiColors);
            $randomColorKey = $colorKeys[array_rand($colorKeys)];
            $colorData = $morandiColors[$randomColorKey];
            
            $stmt = $pdo->prepare("INSERT INTO comic_tags (name, color, text_color) VALUES (?, ?, ?)");
            $stmt->execute([$name, $colorData['bg'], $colorData['text']]);
            
            $_SESSION['message'] = "标签添加成功！";
            $_SESSION['message_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "添加失败: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    }
    header('Location: admin-tags.php');
    exit;
}

// 删除标签
if (isset($_GET['delete'])) {
    $tagId = intval($_GET['delete']);
    try {
        // 先删除关联关系
        $stmt = $pdo->prepare("DELETE FROM comic_tag_relations WHERE tag_id = ?");
        $stmt->execute([$tagId]);
        
        // 再删除标签
        $stmt = $pdo->prepare("DELETE FROM comic_tags WHERE id = ?");
        $stmt->execute([$tagId]);
        
        $_SESSION['message'] = "标签删除成功！";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "删除失败: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    header('Location: admin-tags.php');
    exit;
}

// 获取所有标签
try {
    $tags = $pdo->query("SELECT * FROM comic_tags ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tags = [];
}

// 显示消息
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>标签管理 - 管理员后台</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #FFA500, #FF8C00);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .admin-nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .admin-nav a {
            color: #495057;
            text-decoration: none;
            margin-right: 20px;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #007bff;
            color: white;
        }
        
        .admin-content {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .tags-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .tag-card {
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .tag-card:hover {
            transform: translateY(-5px);
        }
        
        .tag-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .tag-actions {
            margin-top: 15px;
        }
        
        .add-tag-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            align-items: end;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .tags-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-tags"></i> 标签管理</h1>
            <p>管理漫画标签系统</p>
        </div>
        
        <div class="admin-nav">
            <a href="admin-comics.php"><i class="fas fa-plus"></i> 添加漫画</a>
            <a href="admin-comics-list.php"><i class="fas fa-list"></i> 漫画列表</a>
            <a href="admin-tags.php"><i class="fas fa-tags"></i> 标签管理</a>
            <a href="update-password.php"><i class="fas fa-key"></i> 访问码更新</a>
            <a href="index.php"><i class="fas fa-home"></i> 返回首页</a>
        </div>
        
        <div class="admin-content">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- 添加标签表单 -->
            <div class="add-tag-form">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> 添加新标签</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">标签名称</label>
                            <input type="text" name="name" class="form-control" placeholder="输入标签名称" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_tag" class="btn btn-primary">
                                <i class="fas fa-plus"></i> 添加标签
                            </button>
                        </div>
                    </div>
                    <small style="color: #6c757d;">标签背景颜色将自动随机选择莫兰迪色系</small>
                </form>
            </div>
            
            <!-- 标签列表 -->
            <h3 style="margin-bottom: 20px;"><i class="fas fa-list"></i> 现有标签</h3>
            <?php if (empty($tags)): ?>
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-tags" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>暂无标签，请添加第一个标签</p>
                </div>
            <?php else: ?>
                <div class="tags-grid">
                    <?php foreach ($tags as $tag): ?>
                        <div class="tag-card" style="background-color: <?php echo $tag['color']; ?>; color: <?php echo $tag['text_color']; ?>;">
                            <div class="tag-name"><?php echo htmlspecialchars($tag['name']); ?></div>
                            <div class="tag-actions">
                                <button onclick="deleteTag(<?php echo $tag['id']; ?>)" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> 删除
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteTag(tagId) {
            if (confirm('确定要删除这个标签吗？此操作不可恢复！')) {
                window.location.href = 'admin-tags.php?delete=' + tagId;
            }
        }
    </script>
</body>
</html>