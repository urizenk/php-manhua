<?php
// 引入安全HTTP头
require_once APP_PATH . '/views/admin/security_headers.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? '后台管理'; ?> - 海の小窝</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: #f5f6fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            color: white;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h4 {
            margin: 0;
            font-weight: bold;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .menu-item {
            margin: 5px 0;
        }
        .menu-item a {
            display: block;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #3498db;
        }
        .menu-item i {
            margin-right: 10px;
            width: 20px;
        }
        .submenu {
            display: none;
            background: rgba(0,0,0,0.2);
            padding: 5px 0;
        }
        .submenu.show {
            display: block;
        }
        .submenu a {
            padding: 10px 25px 10px 55px;
            font-size: 0.9rem;
        }
        .menu-item.has-submenu > a::after {
            content: "▼";
            float: right;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }
        .menu-item.has-submenu.open > a::after {
            transform: rotate(180deg);
        }
        .main-content {
            padding: 30px;
        }
        .content-header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .content-header h2 {
            margin: 0;
            color: #2c3e50;
        }
        .breadcrumb {
            margin: 10px 0 0 0;
            background: transparent;
            padding: 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn-custom {
            border-radius: 5px;
            padding: 8px 20px;
        }
        .table-actions a,
        .table-actions button {
            margin: 0 5px;
        }
    </style>
    <?php echo $customCss ?? ''; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 左侧菜单 -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-header">
                    <h4>🐋 海の小窝</h4>
                    <p class="mb-0 small">管理后台</p>
                </div>
                
                <div class="sidebar-menu">
                    <div class="menu-item">
                        <a href="/admin88/">
                            <i class="bi bi-speedometer2"></i>
                            控制台
                        </a>
                    </div>
                    
                    <div class="menu-item has-submenu">
                        <a href="javascript:void(0)" class="submenu-toggle">
                            <i class="bi bi-book"></i>
                            漫画管理
                        </a>
                        <div class="submenu">
                            <a href="/admin88/manga/add">添加漫画</a>
                            <a href="/admin88/manga/list">漫画列表</a>
                        </div>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/admin88/tags">
                            <i class="bi bi-tags"></i>
                            标签管理
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/admin88/access-code">
                            <i class="bi bi-key"></i>
                            访问码更新
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/" target="_blank">
                            <i class="bi bi-globe"></i>
                            前台预览
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/admin88/logout">
                            <i class="bi bi-box-arrow-right"></i>
                            退出登录
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 右侧内容区 -->
            <div class="col-md-10 main-content">


