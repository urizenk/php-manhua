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

// 处理表单提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 处理图片上传
        $cover_image = '';
        $thumbnail = '';
        
        // 上传封面图片
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_image = uploadImage($_FILES['cover_image'], 'covers');
        }
        
        // 上传缩略图
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbnail = uploadImage($_FILES['thumbnail'], 'thumbnails');
        }
        
        // 插入漫画数据
        $stmt = $pdo->prepare("
            INSERT INTO comics (title, category_id, status, episodes, description, cover_image, thumbnail) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['title'],
            $_POST['category_id'],
            $_POST['status'],
            $_POST['episodes'] ?? '',
            $_POST['description'] ?? '',
            $cover_image,
            $thumbnail
        ]);
        
        $comic_id = $pdo->lastInsertId();
        
        // 处理资源链接
        if (!empty($_POST['resource_urls'])) {
            foreach ($_POST['resource_urls'] as $index => $url) {
                if (!empty($url) && !empty($_POST['resource_types'][$index])) {
                    $platform = getPlatformFromUrl($url);
                    $type = $_POST['resource_types'][$index];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO comic_resources (comic_id, type, platform, url, sort_order) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$comic_id, $type, $platform, $url, $index + 1]);
                }
            }
        }
        
        // 处理标签 - 根据分类自动添加相应标签
        $tagIds = [];
        
        // 自动添加分类对应标签
        if (!empty($_POST['category_tags'])) {
            $tagIds = array_merge($tagIds, $_POST['category_tags']);
        }
        
        // 添加手动选择的标签
        if (!empty($_POST['tags'])) {
            $tagIds = array_merge($tagIds, $_POST['tags']);
        }
        
        // 去重并插入标签关系
        $tagIds = array_unique($tagIds);
        foreach ($tagIds as $tagId) {
            $stmt = $pdo->prepare("INSERT INTO comic_tag_relations (comic_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$comic_id, $tagId]);
        }
        
        $message = "漫画添加成功！ID: " . $comic_id;
        
    } catch (Exception $e) {
        $message = "添加失败: " . $e->getMessage();
    }
}

// 获取分类和标签数据
try {
    $categories = $pdo->query("SELECT * FROM comic_categories")->fetchAll(PDO::FETCH_ASSOC);
    $tags = $pdo->query("SELECT * FROM comic_tags")->fetchAll(PDO::FETCH_ASSOC);
    
    // 按类型分组标签
    $groupedTags = [
        'letters' => [],      // 字母标签
        'status' => [],       // 状态标签  
        'dates' => [],        // 日期标签
        'other' => []         // 其他标签
    ];
    
    foreach ($tags as $tag) {
        if (in_array($tag['name'], ['连载', '完结', '暂停'])) {
            $groupedTags['status'][] = $tag;
        } elseif (preg_match('/^[A-Z]$/', $tag['name']) || $tag['name'] === '新增漫') {
            $groupedTags['letters'][] = $tag;
        } elseif (preg_match('/^\d{4}$/', $tag['name'])) {
            $groupedTags['dates'][] = $tag;
        } else {
            $groupedTags['other'][] = $tag;
        }
    }
    
} catch (PDOException $e) {
    $categories = [];
    $tags = [];
    $groupedTags = ['letters' => [], 'status' => [], 'dates' => [], 'other' => []];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加漫画 - 管理员后台</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 基础样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .admin-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .admin-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .admin-nav {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .admin-nav a {
            text-decoration: none;
            color: #495057;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-nav a:hover {
            background-color: #e9ecef;
            color: #007bff;
        }
        
        .admin-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        /* 表单样式 */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
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
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        /* 按钮样式 */
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        
        /* 消息样式 */
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        /* 标签样式 */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .tag-checkbox {
            display: none;
        }
        
        .tag-label {
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border: 2px solid transparent;
        }
        
        .tag-checkbox:checked + .tag-label {
            border-color: #007bff;
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* 图片上传样式 */
        .image-upload {
            position: relative;
        }
        
        .image-upload input[type="file"] {
            display: none;
        }
        
        .image-upload label {
            display: block;
            padding: 30px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .image-upload label:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .image-upload small {
            display: block;
            margin-top: 10px;
            color: #6c757d;
        }
        
        .image-preview {
            margin-top: 15px;
            display: none;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* 资源链接样式 */
        .resource-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .resource-item select {
            flex: 0 0 120px;
        }
        
        .resource-item input {
            flex: 1;
        }
        
        /* 分类标签区域 */
        .category-tags-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .tag-group {
            margin-bottom: 20px;
        }
        
        .tag-group-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        
        .category-dependent {
            display: none;
        }
        
        .category-dependent.show {
            display: block;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .resource-item {
                flex-direction: column;
                align-items: stretch;
            }
            
            .resource-item select {
                flex: 1;
            }
            
            .admin-nav {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-book"></i> 漫画管理系统</h1>
            <p>添加新的漫画作品</p>
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
                <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="comicForm">
                <!-- 基本信息 -->
                <div class="form-group">
                    <label class="form-label">名称*</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <!-- 状态选择 -->
                <div class="form-group">
                    <label class="form-label">状态 *</label>
                    <select name="status" class="form-control" required>
                        <option value="连载">连载</option>
                        <option value="完结">完结</option>
                        <option value="暂停">暂停</option>
                    </select>
                </div>
                
                <!-- 分类选择 -->
                <div class="form-group">
                    <label class="form-label">分类 *</label>
                    <select name="category_id" class="form-control" id="categorySelect" required onchange="updateCategoryTags()">
                        <option value="">请选择分类</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 分类相关标签 -->
                <div class="category-tags-section">
                    <div class="tag-group">
                        <div class="tag-group-title">状态标签</div>
                        <div class="tags-container">
                            <?php foreach ($groupedTags['status'] as $tag): ?>
                                <input type="checkbox" name="category_tags[]" value="<?php echo $tag['id']; ?>" 
                                       id="status_tag_<?php echo $tag['id']; ?>" class="tag-checkbox">
                                <label for="status_tag_<?php echo $tag['id']; ?>" class="tag-label" 
                                       style="background-color: <?php echo $tag['color']; ?>; color: <?php echo $tag['text_color']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 韩漫合集标签 -->
                    <div id="koreanTags" class="category-dependent">
                        <div class="tag-group">
                            <div class="tag-group-title">字母分类</div>
                            <div class="tags-container">
                                <?php foreach ($groupedTags['letters'] as $tag): ?>
                                    <input type="checkbox" name="category_tags[]" value="<?php echo $tag['id']; ?>" 
                                           id="letter_tag_<?php echo $tag['id']; ?>" class="tag-checkbox">
                                    <label for="letter_tag_<?php echo $tag['id']; ?>" class="tag-label" 
                                           style="background-color: <?php echo $tag['color']; ?>; color: <?php echo $tag['text_color']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 日更板块标签 -->
                    <div id="dailyTags" class="category-dependent">
                        <div class="tag-group">
                            <div class="tag-group-title">日期标签</div>
                            <div class="tags-container">
                                <?php foreach ($groupedTags['dates'] as $tag): ?>
                                    <input type="checkbox" name="category_tags[]" value="<?php echo $tag['id']; ?>" 
                                           id="date_tag_<?php echo $tag['id']; ?>" class="tag-checkbox">
                                    <label for="date_tag_<?php echo $tag['id']; ?>" class="tag-label" 
                                           style="background-color: <?php echo $tag['color']; ?>; color: <?php echo $tag['text_color']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 完结短漫标签 -->
                    <div id="completedTags" class="category-dependent">
                        <div class="tag-group">
                            <div class="tag-group-title">字母分类</div>
                            <div class="tags-container">
                                <?php foreach ($groupedTags['letters'] as $tag): ?>
                                    <input type="checkbox" name="category_tags[]" value="<?php echo $tag['id']; ?>" 
                                           id="completed_letter_tag_<?php echo $tag['id']; ?>" class="tag-checkbox">
                                    <label for="completed_letter_tag_<?php echo $tag['id']; ?>" class="tag-label" 
                                           style="background-color: <?php echo $tag['color']; ?>; color: <?php echo $tag['text_color']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 其他标签 -->
                <div class="form-group">
                    <label class="form-label">其他标签</label>
                    <div class="tags-container">
                        <?php foreach ($groupedTags['other'] as $tag): ?>
                            <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" 
                                   id="tag_<?php echo $tag['id']; ?>" class="tag-checkbox">
                            <label for="tag_<?php echo $tag['id']; ?>" class="tag-label" 
                                   style="background-color: <?php echo $tag['color']; ?>; color: <?php echo $tag['text_color']; ?>">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 集数信息 -->
                <div class="form-group">
                    <label class="form-label">集数信息</label>
                    <input type="text" name="episodes" class="form-control" placeholder="例如：1-10完结">
                </div>
                
                <!-- 图片上传 -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">封面图片</label>
                        <div class="image-upload">
                            <input type="file" name="cover_image" id="cover_image" accept="image/*">
                            <label for="cover_image">
                                <i class="fas fa-image"></i>
                                <div>点击上传封面图片</div>
                                <small>建议尺寸：300x400px</small>
                            </label>
                            <div class="image-preview" id="cover_preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">缩略图 *</label>
                        <div class="image-upload">
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required>
                            <label for="thumbnail">
                                <i class="fas fa-image"></i>
                                <div>点击上传缩略图</div>
                                <small>建议尺寸：80x100px</small>
                            </label>
                            <div class="image-preview" id="thumbnail_preview"></div>
                        </div>
                    </div>
                </div>
                
                <!-- 简介 -->
                <div class="form-group">
                    <label class="form-label">漫画简介</label>
                    <textarea name="description" class="form-control" placeholder="请输入漫画的详细介绍..."></textarea>
                </div>
                
                <!-- 资源链接 -->
                <div class="form-group">
                    <label class="form-label">资源链接</label>
                    <div id="resources-container">
                        <div class="resource-item">
                            <select name="resource_types[]" class="form-control">
                                <option value="资源链接">资源链接</option>
                                <option value="提取码">提取码</option>
                            </select>
                            <input type="url" name="resource_urls[]" class="form-control" placeholder="资源链接">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeResource(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addResource()" style="margin-top: 10px;">
                        <i class="fas fa-plus"></i> 添加资源链接
                    </button>
                </div>
                
                <!-- 提交按钮 -->
                <div class="form-group" style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 添加漫画
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> 重置表单
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 分类标签显示控制
        function updateCategoryTags() {
            const categorySelect = document.getElementById('categorySelect');
            const categoryId = categorySelect.value;
            
            // 隐藏所有分类相关标签区域
            document.querySelectorAll('.category-dependent').forEach(el => {
                el.classList.remove('show');
            });
            
            // 根据分类显示相应标签
            switch(categoryId) {
                case '1': // 韩漫合集
                    document.getElementById('koreanTags').classList.add('show');
                    break;
                case '2': // 日更板块
                    document.getElementById('dailyTags').classList.add('show');
                    break;
                case '3': // 完结短漫
                    document.getElementById('completedTags').classList.add('show');
                    break;
            }
        }
        
        // 图片预览功能
        function setupImagePreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="预览">`;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // 初始化图片预览
        document.addEventListener('DOMContentLoaded', function() {
            setupImagePreview('cover_image', 'cover_preview');
            setupImagePreview('thumbnail', 'thumbnail_preview');
        });
        
        // 资源链接管理
        function addResource() {
            const container = document.getElementById('resources-container');
            const newResource = document.createElement('div');
            newResource.className = 'resource-item';
            newResource.innerHTML = `
                <select name="resource_types[]" class="form-control">
                    <option value="资源链接">资源链接</option>
                    <option value="提取码">提取码</option>
                </select>
                <input type="url" name="resource_urls[]" class="form-control" placeholder="资源链接">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeResource(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newResource);
        }
        
        function removeResource(button) {
            const container = document.getElementById('resources-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            }
        }
    </script>
</body>
</html>