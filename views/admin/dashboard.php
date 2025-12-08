<?php
/**
 * 后台控制台
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '控制台';

include APP_PATH . '/views/admin/layout_header.php';

// 获取统计数据
$totalMangas = $db->queryOne("SELECT COUNT(*) as count FROM mangas");
$totalTags = $db->queryOne("SELECT COUNT(*) as count FROM tags WHERE tag_name != '未分类'");
$todayMangas = $db->queryOne("SELECT COUNT(*) as count FROM mangas WHERE DATE(created_at) = CURDATE()");
$currentAccessCode = $session->getAccessCode();
?>

<div class="content-header">
    <h2>控制台</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">控制台</li>
        </ol>
    </nav>
</div>

<!-- ========== 功能导航区域 ========== -->
<div class="card mb-4">
    <div class="card-header bg-gradient">
        <h5 class="mb-0 text-white"><i class="bi bi-grid-3x3-gap"></i> 功能导航</h5>
    </div>
    <div class="card-body p-3">
        <!-- 统计卡片 - 紧凑布局 -->
        <div class="row mb-3">
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center p-3">
                <div class="stat-icon me-3">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-info">
                    <h4 class="mb-0"><?php echo $totalMangas['count'] ?? 0; ?></h4>
                    <small class="text-muted">漫画总数</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center p-3">
                <div class="stat-icon me-3 bg-info">
                    <i class="bi bi-tags"></i>
                </div>
                <div class="stat-info">
                    <h4 class="mb-0"><?php echo $totalTags['count'] ?? 0; ?></h4>
                    <small class="text-muted">标签数量</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center p-3">
                <div class="stat-icon me-3 bg-success">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-info">
                    <h4 class="mb-0"><?php echo $todayMangas['count'] ?? 0; ?></h4>
                    <small class="text-muted">今日新增</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center p-3">
                <div class="stat-icon me-3 bg-danger">
                    <i class="bi bi-key"></i>
                </div>
                <div class="stat-info">
                    <h4 class="mb-0"><?php echo $currentAccessCode; ?></h4>
                    <small class="text-muted">当前访问码</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    border-radius: 10px;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-icon.bg-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}
.stat-icon.bg-success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}
.stat-icon.bg-danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}
.stat-icon i {
    font-size: 1.5rem;
    color: white;
}
.stat-info h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}
.stat-info small {
    font-size: 0.8rem;
    display: block;
}
@media (max-width: 768px) {
    .stat-icon {
        width: 40px;
        height: 40px;
    }
    .stat-icon i {
        font-size: 1.2rem;
    }
    .stat-info h4 {
        font-size: 1.2rem;
    }
    .stat-info small {
        font-size: 0.7rem;
    }
}
.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

        <!-- 快捷操作按钮 -->
        <div class="row">
            <div class="col-md-2 col-6 mb-2">
                <a href="/admin88/manga/add" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-plus-circle"></i> 添加漫画
                </a>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <a href="/admin88/manga/list" class="btn btn-info btn-sm w-100">
                    <i class="bi bi-list"></i> 漫画列表
                </a>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <a href="/admin88/tags" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-tags"></i> 标签管理
                </a>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <a href="/admin88/types" class="btn btn-dark btn-sm w-100">
                    <i class="bi bi-grid-3x3-gap"></i> 模块管理
                </a>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <a href="/admin88/access-code" class="btn btn-warning btn-sm w-100">
                    <i class="bi bi-key"></i> 更新访问码
                </a>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <a href="/admin88/site-config" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-gear-fill"></i> 网站配置
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========== 内容展示区域 ========== -->
<!-- 最近添加的漫画 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> 最近添加的漫画</h5>
    </div>
    <div class="card-body">
        <?php
        $recentMangas = $db->query(
            "SELECT m.*, t.type_name, tg.tag_name 
             FROM mangas m 
             LEFT JOIN manga_types t ON m.type_id = t.id 
             LEFT JOIN tags tg ON m.tag_id = tg.id 
             ORDER BY m.created_at DESC 
             LIMIT 10"
        );
        
        if (empty($recentMangas)): ?>
            <p class="text-muted text-center">暂无数据</p>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>标题</th>
                        <th>类型</th>
                        <th>标签</th>
                        <th>添加时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMangas as $manga): ?>
                        <tr>
                            <td><?php echo $manga['id']; ?></td>
                            <td><?php echo htmlspecialchars($manga['title']); ?></td>
                            <td><?php echo htmlspecialchars($manga['type_name']); ?></td>
                            <td><?php echo htmlspecialchars($manga['tag_name'] ?? '-'); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($manga['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layout_footer.php'; ?>


