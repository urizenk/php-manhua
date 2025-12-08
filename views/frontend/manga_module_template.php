<?php
/**
 * 通用漫画板块模板
 * 用于快速创建统一风格的漫画板块页面
 * 
 * 使用方法：
 * 1. 复制此模板
 * 2. 修改 $moduleConfig 配置
 * 3. 保存为对应的板块文件
 */

// 板块配置示例
$moduleConfig = [
    'type_code' => 'korean_collection',  // 类型代码
    'title' => '韩漫合集',                // 板块标题
    'tip' => 'Tip：单部漫的密码就是每日访问码，一码通用！刷新后才能看到新漫画！',
    'enable_search' => true,              // 是否启用搜索
    'enable_filters' => true,             // 是否启用筛选
    'enable_status_filter' => true,       // 是否启用状态筛选
    'group_by_status' => true,            // 是否按状态分组
];

// 通用CSS样式（所有板块共用）
$commonStyles = '
<style>
    .content-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .page-header {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
    }
    .page-title {
        font-size: 1.8rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
    }
    .tip-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        margin: 15px 0;
        border-radius: 5px;
        font-size: 0.9rem;
        color: #856404;
        text-align: left;
    }
    .tip-box i {
        margin-right: 8px;
    }
    .back-btn-top {
        display: inline-block;
        background: #ff5722;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        margin: 15px 0;
        transition: all 0.3s ease;
    }
    .back-btn-top:hover {
        background: #e64a19;
        color: white;
        transform: translateY(-2px);
    }
    .search-box {
        margin: 20px 0;
    }
    .search-form {
        display: flex;
        gap: 10px;
        max-width: 600px;
        margin: 0 auto;
    }
    .search-input {
        flex: 1;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 25px;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.3s ease;
    }
    .search-input:focus {
        border-color: #2196F3;
    }
    .search-btn {
        padding: 10px 30px;
        background: #ffc107;
        color: #333;
        border: none;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-btn:hover {
        background: #ffb300;
        transform: translateY(-2px);
    }
    .new-manga-btn {
        display: inline-block;
        background: #ffc107;
        color: #333;
        padding: 10px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    .new-manga-btn:hover {
        background: #ffb300;
        color: #333;
        transform: translateY(-2px);
    }
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }
    .filter-group {
        margin-bottom: 20px;
    }
    .filter-group:last-child {
        margin-bottom: 0;
    }
    .filter-label {
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .filter-tag {
        padding: 8px 20px;
        border-radius: 20px;
        background: #f0f0f0;
        color: #666;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .filter-tag:hover {
        background: #1976D2;
        color: white;
        transform: translateY(-2px);
    }
    .filter-tag.active {
        background: #1976D2;
        color: white;
        font-weight: bold;
    }
    .manga-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .manga-list-item {
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.3s ease;
    }
    .manga-list-item:last-child {
        border-bottom: none;
    }
    .manga-list-item:hover {
        background: #f8f9ff;
        padding-left: 15px;
    }
    .manga-link {
        color: #2196F3;
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.3s ease;
    }
    .manga-link:hover {
        color: #1976D2;
        text-decoration: underline;
    }
    .manga-subtitle {
        margin-left: 10px;
        color: #999;
        font-size: 0.85rem;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    .back-btn {
        background: white;
        color: #1976D2;
        border: 2px solid #1976D2;
        border-radius: 25px;
        padding: 10px 30px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        margin-top: 25px;
    }
    .back-btn:hover {
        background: #1976D2;
        color: white;
    }
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }
        .search-form {
            flex-direction: column;
        }
        .search-btn {
            width: 100%;
        }
        .filter-tags {
            gap: 8px;
        }
        .filter-tag {
            font-size: 0.85rem;
            padding: 6px 15px;
        }
    }
</style>
';
?>
