<?php
session_start();
require 'config.php';
require 'functions.php';

// 管理员权限验证
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// 获取漫画ID
$comic_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($comic_id <= 0) {
    die('无效的漫画ID');
}

// 获取漫画详情
try {
    $stmt = $pdo->prepare("SELECT * FROM comics WHERE id = ?");
    $stmt->execute([$comic_id]);
    $comic = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$comic) {
        die('漫画不存在');
    }
    
    // 获取标签
    $stmt = $pdo->prepare("
        SELECT t.id, t.name
        FROM comic_tags t
        JOIN comic_tag_relations r ON t.id = r.tag_id
        WHERE r.comic_id = ?
    ");
    $stmt->execute([$comic_id]);
    $current_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $current_tag_ids = array_column($current_tags, 'id');
    
    // 获取资源链接
    $stmt = $pdo->prepare("SELECT * FROM comic_resources WHERE comic_id = ? ORDER BY sort_order");
    $stmt->execute([$comic_id]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('数据加载失败: ' . $e->getMessage());
}

// 获取分类和标签数据
try {
    $categories = $pdo->query("SELECT * FROM comic_categories")->fetchAll(PDO::FETCH_ASSOC);
    $tags = $pdo->query("SELECT * FROM comic_tags")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $tags = [];
}

$message = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // 更新漫画基本信息
        $stmt = $pdo->prepare("
            UPDATE comics SET 
                title = ?, status = ?, episodes = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['title'],
            $_POST['status'],
            $_POST['episodes'] ?? '',
            $_POST['description'] ?? '',
            $comic_id
        ]);
        
        // 处理图片上传
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_image = uploadImage($_FILES['cover_image'], 'covers');
            $pdo->prepare("UPDATE comics SET cover_image = ? WHERE id = ?")->execute([$cover_image, $comic_id]);
        }
        
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbnail = uploadImage($_FILES['thumbnail'], 'thumbnails');
            $pdo->prepare("UPDATE comics SET thumbnail = ? WHERE id = ?")->execute([$thumbnail, $comic_id]);
        }
        
        // 更新标签 - 关键修改：正确处理字母标签和状态标签
        $pdo->prepare("DELETE FROM comic_tag_relations WHERE comic_id = ?")->execute([$comic_id]);
        
        if (!empty($_POST['tags'])) {
            // 首先添加用户选择的标签
            foreach ($_POST['tags'] as $tagId) {
                $pdo->prepare("INSERT INTO comic_tag_relations (comic_id, tag_id) VALUES (?, ?)")->execute([$comic_id, $tagId]);
            }
            
            // 自动添加状态标签
            $status_tag_name = ($_POST['status'] === '完结') ? '完结' : '连载';
            $stmt = $pdo->prepare("SELECT id FROM comic_tags WHERE name = ?");
            $stmt->execute([$status_tag_name]);
            $status_tag = $stmt->fetch();
            
            if ($status_tag && !in_array($status_tag['id'], $_POST['tags'])) {
                $pdo->prepare("INSERT INTO comic_tag_relations (comic_id, tag_id) VALUES (?, ?)")->execute([$comic_id, $status_tag['id']]);
            }
            
            // 自动添加字母标签（基于标题首字母）
            $first_char = strtoupper(mb_substr($_POST['title'], 0, 1));
            $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            
            if (in_array($first_char, $letters)) {
                $stmt = $pdo->prepare("SELECT id FROM comic_tags WHERE name = ?");
                $stmt->execute([$first_char]);
                $letter_tag = $stmt->fetch();
                
                if ($letter_tag && !in_array($letter_tag['id'], $_POST['tags'])) {
                    $pdo->prepare("INSERT INTO comic_tag_relations (comic_id, tag_id) VALUES (?, ?)")->execute([$comic_id, $letter_tag['id']]);
                }
            }
        }
        
        // 更新资源链接
        $pdo->prepare("DELETE FROM comic_resources WHERE comic_id = ?")->execute([$comic_id]);
        if (!empty($_POST['resource_types'])) {
            foreach ($_POST['resource_types'] as $index => $type) {
                if (!empty($_POST['resource_urls'][$index])) {
                    $pdo->prepare("
                        INSERT INTO comic_resources (comic_id, type, url, platform, sort_order) 
                        VALUES (?, ?, ?, ?, ?)
                    ")->execute([
                        $comic_id,
                        $type,
                        $_POST['resource_urls'][$index],
                        $_POST['resource_platforms'][$index] ?? '',
                        $index
                    ]);
                }
            }
        }
        
        $pdo->commit();
        $message = "漫画更新成功！页面位置已根据标签和状态自动调整。";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "更新失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑漫画 - 管理员后台</title>
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
            max-width: 1000px;
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
            font-size: 2.2rem;
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
        }
        
        .message {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 600;
            border-left: 5px solid;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .form-group {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }
        
        .form-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 1.6;
        }
        
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .checkbox-item:hover {
            border-color: #3498db;
            transform: translateY(-2px);
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-item label {
            cursor: pointer;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 20px;
            color: white;
            font-size: 0.9rem;
        }
        
        .resource-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .resource-row .form-control {
            flex: 1;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(149, 165, 166, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 8px 12px;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        .form-actions {
            text-align: center;
            margin-top: 40px;
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .resource-actions {
            margin-top: 15px;
            text-align: center;
        }
        
        .current-image {
            margin-top: 15px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 200px;
            border-radius: 8px;
            border: 3px solid #e0e0e0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .current-image p {
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .tag-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 4px solid #3498db;
        }
        
        .tag-info h4 {
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .tag-info ul {
            margin-left: 20px;
            color: #555;
        }
        
        .tag-info li {
            margin-bottom: 5px;
        }
        
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
            
            .resource-row {
                flex-direction: column;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> 编辑漫画</h1>
            <p>修改漫画信息 - <?php echo htmlspecialchars($comic['title']); ?></p>
        </div>
        
        <div class="admin-nav">
            <a href="admin-comics.php"><i class="fas fa-plus-circle"></i> 添加漫画</a>
            <a href="admin-comics-list.php"><i class="fas fa-list-ul"></i> 漫画列表</a>
            <a href="admin-tags.php"><i class="fas fa-tags"></i> 标签管理</a>
            <a href="update-password.php"><i class="fas fa-key"></i> 访问码更新</a>
            <a href="hmhj.php"><i class="fas fa-home"></i> 返回韩漫合集</a>
        </div>
        
        <div class="admin-content">
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- 基本信息 -->
                <div class="form-group">
                    <label class="form-label">漫画名称 *</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($comic['title']); ?>" 
                           placeholder="请输入漫画名称" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">状态 *</label>
                    <select name="status" class="form-control" required>
                        <option value="连载中" <?php echo $comic['status'] === '连载中' ? 'selected' : ''; ?>>连载中</option>
                        <option value="完结" <?php echo $comic['status'] === '完结' ? 'selected' : ''; ?>>完结</option>
                        <option value="暂停" <?php echo $comic['status'] === '暂停' ? 'selected' : ''; ?>>暂停</option>
                    </select>
                    <div class="tag-info">
                        <h4><i class="fas fa-info-circle"></i> 状态说明</h4>
                        <ul>
                            <li><strong>连载中</strong>：漫画将在"连载"区域显示</li>
                            <li><strong>完结</strong>：漫画将在"完结"区域显示</li>
                            <li>状态变更会自动更新对应的状态标签</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">集数信息</label>
                    <input type="text" name="episodes" class="form-control" 
                           value="<?php echo htmlspecialchars($comic['episodes']); ?>" 
                           placeholder="例如: 1-100话 或 全10卷">
                </div>
                
                <!-- 标签选择 -->
                <div class="form-group">
                    <label class="form-label">分类标签</label>
                    <div class="tag-info">
                        <h4><i class="fas fa-tags"></i> 标签分组说明</h4>
                        <ul>
                            <li><strong>字母标签</strong>（A-Z）：决定漫画在哪个字母区域显示</li>
                            <li><strong>状态标签</strong>（连载/完结）：决定在连载区还是完结区显示</li>
                            <li><strong>内容标签</strong>：描述漫画题材和类型</li>
                            <li>系统会根据漫画标题首字母自动添加字母标签</li>
                        </ul>
                    </div>
                    <div class="checkbox-group">
                        <?php foreach ($tags as $tag): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" 
                                   id="tag_<?php echo $tag['id']; ?>"
                                   <?php echo in_array($tag['id'], $current_tag_ids) ? 'checked' : ''; ?>>
                            <label for="tag_<?php echo $tag['id']; ?>" 
                                   style="background-color: <?php echo $tag['color']; ?>;">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 简介 -->
                <div class="form-group">
                    <label class="form-label">漫画简介</label>
                    <textarea name="description" class="form-control" 
                              placeholder="请输入漫画的详细简介..."><?php echo htmlspecialchars($comic['description']); ?></textarea>
                </div>
                
                <!-- 图片上传 -->
                <div class="form-group">
                    <label class="form-label">封面图片</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    <?php if (!empty($comic['cover_image'])): ?>
                    <div class="current-image">
                        <p>当前封面图片：</p>
                        <img src="<?php echo htmlspecialchars($comic['cover_image']); ?>" 
                             alt="当前封面" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none; color:#e74c3c;">
                            <i class="fas fa-exclamation-triangle"></i> 封面图片加载失败
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">缩略图</label>
                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    <?php if (!empty($comic['thumbnail'])): ?>
                    <div class="current-image">
                        <p>当前缩略图：</p>
                        <img src="<?php echo htmlspecialchars($comic['thumbnail']); ?>" 
                             alt="当前缩略图"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none; color:#e74c3c;">
                            <i class="fas fa-exclamation-triangle"></i> 缩略图加载失败
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- 资源链接 -->
                <div class="form-group">
                    <label class="form-label">资源链接</label>
                    <div id="resources-container">
                        <?php if (!empty($resources)): ?>
                            <?php foreach ($resources as $index => $resource): ?>
                            <div class="resource-row">
                                <input type="text" name="resource_types[]" class="form-control" 
                                       placeholder="资源类型（如：百度网盘）" 
                                       value="<?php echo htmlspecialchars($resource['type']); ?>">
                                <input type="url" name="resource_urls[]" class="form-control" 
                                       placeholder="资源链接URL" 
                                       value="<?php echo htmlspecialchars($resource['url']); ?>" required>
                                <input type="text" name="resource_platforms[]" class="form-control" 
                                       placeholder="平台说明" 
                                       value="<?php echo htmlspecialchars($resource['platform']); ?>">
                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="resource-row">
                                <input type="text" name="resource_types[]" class="form-control" placeholder="资源类型（如：百度网盘）">
                                <input type="url" name="resource_urls[]" class="form-control" placeholder="资源链接URL" required>
                                <input type="text" name="resource_platforms[]" class="form-control" placeholder="平台说明">
                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="resource-actions">
                        <button type="button" class="btn btn-secondary" onclick="addResourceRow()">
                            <i class="fas fa-plus"></i> 添加资源链接
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 保存修改
                    </button>
                    <a href="comic-detail.php?id=<?php echo $comic_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> 预览页面
                    </a>
                    <a href="admin-comics-list.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> 返回列表
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addResourceRow() {
            const container = document.getElementById('resources-container');
            const row = document.createElement('div');
            row.className = 'resource-row';
            row.innerHTML = `
                <input type="text" name="resource_types[]" class="form-control" placeholder="资源类型（如：百度网盘）">
                <input type="url" name="resource_urls[]" class="form-control" placeholder="资源链接URL" required>
                <input type="text" name="resource_platforms[]" class="form-control" placeholder="平台说明">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(row);
        }
        
        // 自动为资源行添加删除按钮（兼容旧数据）
        document.addEventListener('DOMContentLoaded', function() {
            const resourceRows = document.querySelectorAll('.resource-row');
            resourceRows.forEach(row => {
                if (!row.querySelector('.btn-danger')) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'btn btn-danger';
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                    deleteBtn.onclick = function() { row.remove(); };
                    row.appendChild(deleteBtn);
                }
            });
        });
    </script>
</body>
</html>