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
        /* 移动端优化 */
        @media (max-width: 768px) {
            body {
                font-size: 13px;
            }
            .sidebar {
                min-height: auto;
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            .sidebar.show {
                left: 0;
            }
            .sidebar-header {
                padding: 15px;
            }
            .sidebar-header h4 {
                font-size: 1.1rem;
            }
            .sidebar-header p {
                font-size: 0.75rem;
            }
            .menu-item a {
                padding: 10px 15px;
                font-size: 0.85rem;
            }
            .menu-item i {
                margin-right: 8px;
                width: 16px;
                font-size: 0.9rem;
            }
            .submenu a {
                padding: 8px 15px 8px 40px;
                font-size: 0.8rem;
            }
            .main-content {
                padding: 10px 8px;
                margin-left: 0 !important;
                width: 100%;
            }
            .content-header {
                padding: 12px 15px;
                margin-bottom: 15px;
            }
            .content-header h2 {
                font-size: 1.2rem;
            }
            .breadcrumb {
                font-size: 0.75rem;
            }
            .card {
                margin-bottom: 15px;
            }
            .card-header {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            .card-body {
                padding: 12px;
            }
            .card-body h3 {
                font-size: 1.5rem;
            }
            .card-body p {
                font-size: 0.8rem;
            }
            .card-body i {
                font-size: 2rem !important;
            }
            .btn-custom {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            .table {
                font-size: 0.75rem;
            }
            .table th,
            .table td {
                padding: 6px 4px;
                white-space: nowrap;
            }
            .table-actions a,
            .table-actions button {
                margin: 0 2px;
                padding: 4px 8px;
                font-size: 0.7rem;
            }
            .table-responsive {
                font-size: 0.75rem;
            }
            /* 统计卡片优化 */
            .row.mb-4 .col-md-3 {
                padding: 0 5px;
                margin-bottom: 10px;
            }
            /* 快捷操作按钮优化 */
            .row .col-md-3.mb-3 {
                padding: 0 5px;
                margin-bottom: 8px;
            }
            /* 表单优化 */
            .form-label {
                font-size: 0.85rem;
                margin-bottom: 4px;
            }
            .form-control,
            .form-select {
                font-size: 0.85rem;
                padding: 6px 10px;
            }
            .form-text {
                font-size: 0.7rem;
            }
            /* 移动端菜单按钮 */
            .mobile-menu-toggle {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1001;
                background: #2c3e50;
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 5px;
                font-size: 1.2rem;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
    </style>
    <?php echo $customCss ?? ''; ?>
    <?php include APP_PATH . '/views/admin/mobile_styles.php'; ?>
</head>
<body>
    <!-- 移动端菜单按钮 -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- 侧边栏遮罩层 -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- 左侧菜单 -->
            <div class="col-md-2 sidebar" id="sidebar">
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
                        <a href="/admin88/types">
                            <i class="bi bi-grid-3x3-gap"></i>
                            模块管理
                        </a>
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


