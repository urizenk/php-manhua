<?php
/**
 * 后台控制台
 */
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

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-book" style="font-size: 3rem; color: #667eea;"></i>
                <h3 class="mt-3"><?php echo $totalMangas['count'] ?? 0; ?></h3>
                <p class="text-muted">漫画总数</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-tags" style="font-size: 3rem; color: #3498db;"></i>
                <h3 class="mt-3"><?php echo $totalTags['count'] ?? 0; ?></h3>
                <p class="text-muted">标签数量</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-clock-history" style="font-size: 3rem; color: #2ecc71;"></i>
                <h3 class="mt-3"><?php echo $todayMangas['count'] ?? 0; ?></h3>
                <p class="text-muted">今日新增</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-key" style="font-size: 3rem; color: #e74c3c;"></i>
                <h3 class="mt-3"><?php echo $currentAccessCode; ?></h3>
                <p class="text-muted">当前访问码</p>
            </div>
        </div>
    </div>
</div>

<!-- 快捷操作 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">快捷操作</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="/admin88/manga/add" class="btn btn-primary btn-custom w-100">
                    <i class="bi bi-plus-circle"></i> 添加漫画
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="/admin88/manga/list" class="btn btn-info btn-custom w-100">
                    <i class="bi bi-list"></i> 漫画列表
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="/admin88/tags" class="btn btn-success btn-custom w-100">
                    <i class="bi bi-tags"></i> 标签管理
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="/admin88/access-code" class="btn" style="background: #1976D2;" class="btn-custom w-100">
                    <i class="bi bi-key"></i> 更新访问码
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 最近添加 -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">最近添加的漫画</h5>
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


