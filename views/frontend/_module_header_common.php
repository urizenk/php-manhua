<?php
/**
 * 漫画板块通用头部组件
 * 包含：Tip提示框、返回按钮、搜索框
 * 
 * 使用方法：
 * $headerConfig = [
 *     'title' => '板块标题',
 *     'tip' => 'Tip提示内容',
 *     'show_search' => true,
 *     'show_new_btn' => true,
 *     'type_code' => 'korean_collection',
 *     'selected_tag' => $selectedTag,
 *     'selected_status' => $selectedStatus,
 *     'keyword' => $keyword
 * ];
 * include APP_PATH . '/views/frontend/_module_header_common.php';
 */

$title = $headerConfig['title'] ?? '漫画板块';
$tip = $headerConfig['tip'] ?? 'Tip：单部漫的密码就是每日访问码，一码通用！刷新后才能看到新漫画！';
$showSearch = $headerConfig['show_search'] ?? true;
$showNewBtn = $headerConfig['show_new_btn'] ?? true;
$selectedTag = $headerConfig['selected_tag'] ?? 'all';
$selectedStatus = $headerConfig['selected_status'] ?? 'all';
$keyword = $headerConfig['keyword'] ?? '';
?>

<div class="page-header">
    <h1 class="page-title"><?php echo htmlspecialchars($title); ?></h1>
    
    <!-- Tip提示框 -->
    <div class="tip-box">
        <i class="bi bi-info-circle"></i>
        <?php echo htmlspecialchars($tip); ?>
    </div>
    
    <!-- 返回按钮 -->
    <a href="/" class="back-btn-top">
        <i class="bi bi-arrow-left"></i> 回到目录
    </a>
    
    <?php if ($showSearch): ?>
    <!-- 搜索框 -->
    <div class="search-box">
        <form method="GET" class="search-form">
            <input type="hidden" name="tag" value="<?php echo htmlspecialchars($selectedTag); ?>">
            <?php if (isset($selectedStatus)): ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($selectedStatus); ?>">
            <?php endif; ?>
            <input type="text" 
                   name="keyword" 
                   class="search-input" 
                   placeholder="搜索不用打全称，用关键词搜索..." 
                   value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="search-btn">查看</button>
        </form>
    </div>
    <?php endif; ?>
    
    <?php if ($showNewBtn): ?>
    <!-- 新推漫按钮 -->
    <a href="?tag=<?php echo $selectedTag; ?><?php echo isset($selectedStatus) ? '&status=' . $selectedStatus : ''; ?>" class="new-manga-btn">
        新推漫
    </a>
    <?php endif; ?>
</div>

<style>
/* 通用头部样式 */
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
}
</style>
