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

// 获取漫画列表
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name 
        FROM comics c 
        LEFT JOIN comic_categories cat ON c.category_id = cat.id 
        ORDER BY c.id DESC
    ");
    $stmt->execute();
    $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comics = [];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漫画列表 - 管理员后台</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .admin-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .admin-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
            position: relative;
        }
        
        .admin-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
        }
        
        .admin-nav {
            background: #34495e;
            padding: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .admin-content {
            padding: 40px;
            min-height: 500px;
        }
        
        .comics-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .comics-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .comics-table td {
            padding: 16px 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s ease;
        }
        
        .comics-table tr:hover td {
            background: #f8f9ff;
        }
        
        .comics-table tr:last-child td {
            border-bottom: none;
        }
        
        .comic-cover {
            width: 50px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .comic-cover:hover {
            transform: scale(1.1);
        }
        
        .cover-placeholder {
            width: 50px;
            height: 65px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .comic-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
            line-height: 1.4;
        }
        
        .comic-episodes {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-ongoing {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
        }
        
        .status-completed {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
            color: white;
        }
        
        .status-paused {
            background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
            color: white;
        }
        
        .category-badge {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .view-count {
            color: #7f8c8d;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .btn-sm {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .empty-state p {
            margin-bottom: 30px;
            font-size: 1rem;
        }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                align-items: stretch;
            }
            
            .admin-nav a {
                justify-content: center;
            }
            
            .admin-content {
                padding: 20px;
            }
            
            .comics-table {
                font-size: 0.9rem;
            }
            
            .comics-table th,
            .comics-table td {
                padding: 12px 8px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                padding: 6px 8px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .admin-header {
                padding: 30px 20px;
            }
            
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .comics-table {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-list"></i> 漫画列表管理</h1>
            <p>高效管理您的漫画作品库</p>
        </div>
        
        <div class="admin-nav">
            <a href="admin-comics.php"><i class="fas fa-plus-circle"></i> 添加新漫画</a>
            <a href="admin-comics-list.php" style="background: rgba(255,255,255,0.3);"><i class="fas fa-list-ul"></i> 漫画列表</a>
            <a href="admin-tags.php"><i class="fas fa-tags"></i> 标签管理</a>
            <a href="update-password.php"><i class="fas fa-key"></i> 访问码更新</a>
            <a href="hmhj.php"><i class="fas fa-home"></i> 返回首页</a>
        </div>
        
        <div class="admin-content">
            <?php if (empty($comics)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>暂无漫画数据</h3>
                    <p>开始添加您的第一本漫画作品</p>
                    <a href="admin-comics.php" class="btn btn-primary" style="padding: 12px 24px;">
                        <i class="fas fa-plus"></i> 添加第一本漫画
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="comics-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">封面</th>
                                <th style="min-width: 200px;">漫画信息</th>
                                <th style="width: 120px;">作者</th>
                                <th style="width: 100px;">状态</th>
                                <th style="width: 120px;">分类</th>
                                <th style="width: 100px;">浏览数</th>
                                <th style="width: 180px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comics as $comic): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($comic['thumbnail'])): ?>
                                        <img src="<?php echo htmlspecialchars($comic['thumbnail']); ?>" 
                                             alt="<?php echo htmlspecialchars($comic['title']); ?>" 
                                             class="comic-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="cover-placeholder" style="display: none;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="cover-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="comic-title"><?php echo htmlspecialchars($comic['title']); ?></div>
                                    <?php if (!empty($comic['episodes'])): ?>
                                        <div class="comic-episodes"><?php echo htmlspecialchars($comic['episodes']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($comic['author']) ? htmlspecialchars($comic['author']) : '<span style="color:#bdc3c7;">-</span>'; ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'status-ongoing';
                                    if ($comic['status'] === '完结') $statusClass = 'status-completed';
                                    if ($comic['status'] === '暂停') $statusClass = 'status-paused';
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($comic['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="category-badge"><?php echo htmlspecialchars($comic['category_name']); ?></span>
                                </td>
                                <td>
                                    <span class="view-count">
                                        <i class="fas fa-eye"></i> <?php echo number_format($comic['view_count']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="comic-detail.php?id=<?php echo $comic['id']; ?>" 
                                           class="btn btn-primary btn-sm" 
                                           target="_blank"
                                           title="预览">
                                            <i class="fas fa-eye"></i>
                                            <span class="btn-text">预览</span>
                                        </a>
                                        <a href="admin-edit-comic.php?id=<?php echo $comic['id']; ?>" 
                                           class="btn btn-secondary btn-sm"
                                           title="编辑">
                                            <i class="fas fa-edit"></i>
                                            <span class="btn-text">编辑</span>
                                        </a>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="deleteComic(<?php echo $comic['id']; ?>, '<?php echo htmlspecialchars(addslashes($comic['title'])); ?>')"
                                                title="删除">
                                            <i class="fas fa-trash"></i>
                                            <span class="btn-text">删除</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 30px; text-align: center; color: #7f8c8d;">
                    <p>共 <strong><?php echo count($comics); ?></strong> 部漫画作品</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteComic(id, title) {
            if (confirm('确定要删除漫画《' + title + '》吗？\n\n此操作将永久删除该漫画的所有数据，不可恢复！')) {
                // 这里可以添加AJAX删除功能
                fetch('admin-delete-comic.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('漫画删除成功！');
                        location.reload();
                    } else {
                        alert('删除失败：' + data.message);
                    }
                })
                .catch(error => {
                    alert('删除失败，请重试');
                    console.error('Error:', error);
                });
            }
        }
        
        // 图片加载错误处理
        document.addEventListener('DOMContentLoaded', function() {
            const coverImages = document.querySelectorAll('.comic-cover');
            coverImages.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const placeholder = this.nextElementSibling;
                    if (placeholder && placeholder.classList.contains('cover-placeholder')) {
                        placeholder.style.display = 'flex';
                    }
                });
            });
        });
        
        // 响应式按钮文字隐藏
        function handleResize() {
            const buttons = document.querySelectorAll('.btn-text');
            if (window.innerWidth < 768) {
                buttons.forEach(btn => btn.style.display = 'none');
            } else {
                buttons.forEach(btn => btn.style.display = 'inline');
            }
        }
        
        window.addEventListener('resize', handleResize);
        window.addEventListener('load', handleResize);
    </script>
</body>
</html>