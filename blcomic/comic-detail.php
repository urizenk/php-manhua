<?php
session_start();
require 'config.php';
require 'functions.php';

// 用户验证
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: index.php');
    exit;
}

// 密码版本验证
try {
    $latest_password_id = null;
    if ($redis && $redis->exists('latest_password_id')) {
        $latest_password_id = $redis->get('latest_password_id');
    } else {
        $latest_password_id = getLatestPasswordIdFromDB($pdo);
        if ($redis) {
            $redis->setex('latest_password_id', 7200, $latest_password_id);
        }
    }
    
    if ($_SESSION['password_version'] != $latest_password_id) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("密码版本验证失败: " . $e->getMessage());
}

// 获取漫画ID
$comic_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($comic_id <= 0) {
    die('漫画不存在');
}

// 检查管理员权限
$is_admin = isset($_SESSION['admin_verified']) && $_SESSION['admin_verified'] === true;

// 获取漫画详情
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name 
        FROM comics c 
        LEFT JOIN comic_categories cat ON c.category_id = cat.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$comic_id]);
    $comic = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$comic) {
        die('漫画不存在');
    }
    
    // 获取标签
    $stmt = $pdo->prepare("
        SELECT t.name, t.color 
        FROM comic_tags t
        JOIN comic_tag_relations r ON t.id = r.tag_id
        WHERE r.comic_id = ?
    ");
    $stmt->execute([$comic_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取资源链接
    $stmt = $pdo->prepare("
        SELECT type, url, platform 
        FROM comic_resources 
        WHERE comic_id = ? 
        ORDER BY sort_order
    ");
    $stmt->execute([$comic_id]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 更新浏览次数
    $pdo->prepare("UPDATE comics SET view_count = view_count + 1 WHERE id = ?")->execute([$comic_id]);
    
} catch (PDOException $e) {
    die('数据加载失败: ' . $e->getMessage());
}

// 状态颜色映射
$statusColors = [
    '完结' => '#ffe4e4',
    '连载中' => '#e4f4ff',
    '暂停' => '#fff4e4'
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($comic['title']); ?> - 海の小窝</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Microsoft YaHei', sans-serif;
            background-color: #FFFFF0;
            color: #333;
            line-height: 1.5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            min-height: calc(100vh);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #EC5800;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #e69500;
        }
        
        .edit-btn {
            padding: 8px 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .edit-btn:hover {
            background: #2980b9;
        }
        
        .comic-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #FFA500;
            padding-bottom: 8px;
        }
        
        .comic-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            align-items: start;
            margin-bottom: 30px;
        }
        
        .cover-section {
            text-align: center;
        }
        
        .cover-image {
            width: 100%;
            max-width: 230px;
            height: 320px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 3px solid white;
        }
        
        .cover-placeholder {
            width: 100%;
            max-width: 230px;
            height: 320px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-section {
            padding: 15px 0;
            max-height: 320px;
            overflow-y: auto;
        }
        
        .info-section::-webkit-scrollbar {
            width: 4px;
        }
        
        .info-section::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }
        
        .info-section::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }
        
        .info-section::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .info-label {
            width: 50px;
            color: #828282;
            font-weight: 500;
            flex-shrink: 0;
            margin-right: 8px;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            color: #d63031;
            background-color: <?php echo $statusColors[$comic['status']] ?? '#f0f0f0'; ?>;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .tag {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 13px;
            color: white;
            cursor: default;
            transition: transform 0.2s;
            background-color: <?php echo $tag['color'] ?? '#3498db'; ?>;
        }
        
        .tag:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .description {
            line-height: 1.6;
            color: #555;
            font-size: 15px;
            white-space: pre-wrap;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #FFA500;
        }
        
        .resources-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .resources-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }
        
        .resource-item {
            padding: 12px;
            border-radius: 6px;
            background: #f8f9fa;
            transition: background 0.3s;
            border: 1px solid #e9ecef;
        }
        
        .resource-item:hover {
            background: #e9ecef;
        }
        
        .resource-type {
            font-size: 12px;
            color: #828282;
            margin-bottom: 6px;
            font-weight: 500;
        }
        
        .resource-link {
            color: #3498db;
            text-decoration: none;
            font-size: 13px;
            word-break: break-all;
            transition: color 0.3s;
            display: block;
            line-height: 1.4;
        }
        
        .resource-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .view-count {
            color: #828282;
            font-size: 12px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }

        /* 移动端适配 */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header-actions {
                margin-bottom: 15px;
            }
            
            .back-btn, .edit-btn {
                font-size: 15px;
                padding: 6px 12px;
            }
            
            .comic-title {
                font-size: 22px;
                margin-bottom: 15px;
                padding-bottom: 6px;
            }
            
            .comic-content {
                grid-template-columns: 1fr;
                gap: 20px;
                margin-bottom: 25px;
            }
            
            .cover-section {
                text-align: center;
            }
            
            .cover-image, .cover-placeholder {
                max-width: 200px;
                height: 280px;
            }
            
            .info-section {
                max-height: none;
                overflow-y: visible;
                padding: 0;
            }
            
            .info-item {
                margin-bottom: 12px;
                font-size: 15px;
            }
            
            .info-label {
                width: 45px;
                margin-right: 6px;
            }
            
            .status-badge {
                padding: 3px 10px;
                font-size: 13px;
            }
            
            .tag {
                padding: 2px 8px;
                font-size: 12px;
            }
            
            .description {
                font-size: 14px;
                padding: 10px;
                line-height: 1.5;
            }
            
            .resources-section {
                margin-top: 25px;
                padding-top: 15px;
            }
            
            .resources-title {
                font-size: 18px;
                margin-bottom: 12px;
            }
            
            .resources-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .resource-item {
                padding: 10px;
            }
            
            .resource-type {
                font-size: 13px;
            }
            
            .resource-link {
                font-size: 14px;
            }
            
            .view-count {
                font-size: 13px;
                margin-top: 20px;
                padding-top: 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .comic-title {
                font-size: 20px;
            }
            
            .cover-image, .cover-placeholder {
                max-width: 180px;
                height: 250px;
            }
            
            .info-item {
                font-size: 14px;
            }
            
            .resources-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <a href="hmhj.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> 返回韩漫合集
            </a>
            <?php if ($is_admin): ?>
            <a href="admin-edit-comic.php?id=<?php echo $comic_id; ?>" class="edit-btn">
                <i class="fas fa-edit"></i> 编辑漫画
            </a>
            <?php endif; ?>
        </div>
        
        <h1 class="comic-title"><?php echo htmlspecialchars($comic['title']); ?></h1>
        
        <div class="comic-content">
            <!-- 封面图片区域 -->
            <div class="cover-section">
                <?php if (!empty($comic['cover_image'])): ?>
                    <img src="<?php echo htmlspecialchars($comic['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($comic['title']); ?>" 
                         class="cover-image">
                <?php else: ?>
                    <div class="cover-placeholder">
                        <div>
                            <i class="fas fa-image" style="font-size: 36px; margin-bottom: 8px;"></i>
                            <div>暂无封面</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 漫画信息区域 -->
            <div class="info-section">
                <!-- 名称 -->
                <div class="info-item">
                    <span class="info-label">名称</span>
                    <span class="info-value"><?php echo htmlspecialchars($comic['title']); ?></span>
                </div>
                
                <!-- 状态 -->
                <div class="info-item">
                    <span class="info-label">状态</span>
                    <span class="info-value">
                        <span class="status-badge"><?php echo htmlspecialchars($comic['status']); ?></span>
                    </span>
                </div>
                
                <!-- 集数信息 -->
                <?php if (!empty($comic['episodes'])): ?>
                <div class="info-item">
                    <span class="info-label">集数</span>
                    <span class="info-value"><?php echo htmlspecialchars($comic['episodes']); ?></span>
                </div>
                <?php endif; ?>
                
                <!-- 标签 -->
                <?php if (!empty($tags)): ?>
                <div class="info-item">
                    <span class="info-label">标签</span>
                    <span class="info-value">
                        <div class="tags-container">
                            <?php foreach ($tags as $tag): ?>
                            <span class="tag" style="background-color: <?php echo htmlspecialchars($tag['color'] ?? '#828282'); ?>">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- 简介 -->
                <?php if (!empty($comic['description'])): ?>
                <div class="info-item">
                    <span class="info-label">简介</span>
                    <span class="info-value">
                        <div class="description"><?php echo htmlspecialchars($comic['description']); ?></div>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 资源链接区域 -->
        <?php if (!empty($resources)): ?>
        <div class="resources-section">
            <h2 class="resources-title">资源链接</h2>
            <div class="resources-grid">
                <?php foreach ($resources as $resource): ?>
                <div class="resource-item">
                    <div class="resource-type"><?php echo htmlspecialchars($resource['type']); ?></div>
                    <a href="<?php echo htmlspecialchars($resource['url']); ?>" 
                       class="resource-link" 
                       target="_blank"
                       rel="noopener noreferrer">
                        <?php echo htmlspecialchars($resource['url']); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 浏览统计 -->
        <div class="view-count">
            <i class="fas fa-eye"></i> 浏览次数: <?php echo number_format($comic['view_count']); ?>
        </div>
    </div>

    <script>
        // 图片加载失败处理
        document.addEventListener('DOMContentLoaded', function() {
            const coverImage = document.querySelector('.cover-image');
            if (coverImage) {
                coverImage.onerror = function() {
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = 'cover-placeholder';
                    placeholder.innerHTML = `
                        <div>
                            <i class="fas fa-image" style="font-size: 36px; margin-bottom: 8px;"></i>
                            <div>封面加载失败</div>
                        </div>
                    `;
                    this.parentNode.appendChild(placeholder);
                };
            }
        });
    </script>
</body>
</html>