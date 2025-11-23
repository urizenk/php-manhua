<?php
/**
 * A2-漫画列表管理模块
 */
$pageTitle = '漫画列表';

// 处理批量操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_action'])) {
    $action = $_POST['batch_action'];
    $ids = $_POST['manga_ids'] ?? [];
    
    if (!empty($ids)) {
        switch ($action) {
            case 'delete':
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->execute("DELETE FROM mangas WHERE id IN ($placeholders)", $ids);
                $message = '批量删除成功';
                $messageType = 'success';
                break;
                
            case 'change_tag':
                $newTagId = $_POST['new_tag_id'] ?? 0;
                if ($newTagId) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $params = array_merge([$newTagId], $ids);
                    $db->execute("UPDATE mangas SET tag_id = ? WHERE id IN ($placeholders)", $params);
                    $message = '批量修改标签成功';
                    $messageType = 'success';
                }
                break;
                
            case 'change_status':
                $newStatus = $_POST['new_status'] ?? '';
                if ($newStatus) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $params = array_merge([$newStatus], $ids);
                    $db->execute("UPDATE mangas SET status = ? WHERE id IN ($placeholders)", $params);
                    $message = '批量修改状态成功';
                    $messageType = 'success';
                }
                break;
        }
    }
}

// 获取筛选参数
$typeFilter = $_GET['type'] ?? '';
$tagFilter = $_GET['tag'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;

// 构建WHERE条件
$where = [];
$params = [];

if ($typeFilter) {
    $where[] = 'm.type_id = ?';
    $params[] = $typeFilter;
}

if ($tagFilter) {
    $where[] = 'm.tag_id = ?';
    $params[] = $tagFilter;
}

if ($statusFilter) {
    $where[] = 'm.status = ?';
    $params[] = $statusFilter;
}

if ($keyword) {
    $escapedKeyword = addcslashes($keyword, '%_');
    $where[] = '(m.title LIKE ? OR m.description LIKE ?)';
    $params[] = "%{$escapedKeyword}%";
    $params[] = "%{$escapedKeyword}%";
}

$whereClause = empty($where) ? '1=1' : implode(' AND ', $where);

// 获取漫画列表（分页）
$sql = "SELECT m.*, t.type_name, tg.tag_name 
        FROM mangas m
        LEFT JOIN manga_types t ON m.type_id = t.id
        LEFT JOIN tags tg ON m.tag_id = tg.id
        WHERE {$whereClause}
        ORDER BY m.created_at DESC
        LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage);

$mangas = $db->query($sql, $params);

// 获取总数
$countSql = "SELECT COUNT(*) as total FROM mangas m WHERE {$whereClause}";
$countResult = $db->queryOne($countSql, $params);
$total = $countResult['total'] ?? 0;
$totalPages = ceil($total / $perPage);

// 获取所有类型和标签（用于筛选）
$types = $db->query("SELECT * FROM manga_types ORDER BY sort_order");
$tags = $db->query("SELECT * FROM tags WHERE tag_name != '未分类' ORDER BY type_id, sort_order");

include APP_PATH . '/views/admin/layout_header.php';
?>

<div class="content-header">
    <h2>漫画列表</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin88/">控制台</a></li>
            <li class="breadcrumb-item active">漫画列表</li>
        </ol>
    </nav>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- 筛选和搜索 -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="type">
                    <option value="">全部类型</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php echo $typeFilter == $type['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="tag">
                    <option value="">全部标签</option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>" <?php echo $tagFilter == $tag['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tag['tag_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">全部状态</option>
                    <option value="serializing" <?php echo $statusFilter == 'serializing' ? 'selected' : ''; ?>>连载中</option>
                    <option value="completed" <?php echo $statusFilter == 'completed' ? 'selected' : ''; ?>>已完结</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="keyword" 
                       placeholder="搜索标题或简介" value="<?php echo htmlspecialchars($keyword); ?>">
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> 筛选
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 批量操作 -->
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" id="batchForm">
            <div class="row g-2">
                <div class="col-auto">
                    <span class="badge bg-secondary">已选择：<span id="selectedCount">0</span> 个</span>
                </div>
                
                <div class="col-auto">
                    <select class="form-select form-select-sm" name="batch_action" id="batchAction" required>
                        <option value="">批量操作...</option>
                        <option value="delete">删除</option>
                        <option value="change_tag">修改标签</option>
                        <option value="change_status">修改状态</option>
                    </select>
                </div>
                
                <div class="col-auto" id="tagSelectDiv" style="display:none;">
                    <select class="form-select form-select-sm" name="new_tag_id">
                        <option value="">选择标签</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo $tag['id']; ?>">
                                <?php echo htmlspecialchars($tag['tag_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-auto" id="statusSelectDiv" style="display:none;">
                    <select class="form-select form-select-sm" name="new_status">
                        <option value="">选择状态</option>
                        <option value="serializing">连载中</option>
                        <option value="completed">已完结</option>
                    </select>
                </div>
                
                <div class="col-auto">
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-check2-square"></i> 执行
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 漫画列表 -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">漫画列表（共 <?php echo $total; ?> 个）</h5>
        <a href="/admin88/manga/add" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> 添加漫画
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($mangas)): ?>
            <p class="text-muted text-center py-5">暂无数据</p>
        <?php else: ?>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th width="60">ID</th>
                        <th width="80">封面</th>
                        <th>标题</th>
                        <th width="120">类型</th>
                        <th width="120">标签</th>
                        <th width="100">状态</th>
                        <th width="150">添加时间</th>
                        <th width="180">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mangas as $manga): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="manga-check" value="<?php echo $manga['id']; ?>">
                            </td>
                            <td><?php echo $manga['id']; ?></td>
                            <td>
                                <?php if ($manga['cover_image']): ?>
                                    <img src="<?php echo htmlspecialchars($manga['cover_image']); ?>" 
                                         style="width: 50px; height: 65px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 65px; background: #e9ecef; border-radius: 4px; 
                                                display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($manga['title']); ?></div>
                                <?php if ($manga['description']): ?>
                                    <small class="text-muted">
                                        <?php echo mb_substr(strip_tags($manga['description']), 0, 30) . '...'; ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($manga['type_name']); ?></td>
                            <td><?php echo htmlspecialchars($manga['tag_name'] ?? '-'); ?></td>
                            <td>
                                <?php if ($manga['status'] === 'serializing'): ?>
                                    <span class="badge bg-info">连载中</span>
                                <?php elseif ($manga['status'] === 'completed'): ?>
                                    <span class="badge bg-success">已完结</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($manga['created_at'])); ?></td>
                            <td class="table-actions">
                                <a href="/admin88/manga/edit?id=<?php echo $manga['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/detail/<?php echo $manga['id']; ?>" 
                                   class="btn btn-sm btn-outline-info" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-manga" 
                                        data-id="<?php echo $manga['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- 分页 -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $typeFilter; ?>&tag=<?php echo $tagFilter; ?>&status=<?php echo $statusFilter; ?>&keyword=<?php echo urlencode($keyword); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$customJs = '
<script>
$(document).ready(function() {
    // 全选/取消全选
    $("#checkAll").change(function() {
        $(".manga-check").prop("checked", $(this).prop("checked"));
        updateSelectedCount();
    });
    
    // 单个复选框变化
    $(".manga-check").change(function() {
        updateSelectedCount();
    });
    
    // 更新已选择数量
    function updateSelectedCount() {
        var count = $(".manga-check:checked").length;
        $("#selectedCount").text(count);
    }
    
    // 批量操作类型改变
    $("#batchAction").change(function() {
        var action = $(this).val();
        $("#tagSelectDiv, #statusSelectDiv").hide();
        
        if (action === "change_tag") {
            $("#tagSelectDiv").show();
        } else if (action === "change_status") {
            $("#statusSelectDiv").show();
        }
    });
    
    // 批量表单提交
    $("#batchForm").submit(function(e) {
        var action = $("#batchAction").val();
        var checkedIds = [];
        
        $(".manga-check:checked").each(function() {
            checkedIds.push($(this).val());
        });
        
        if (checkedIds.length === 0) {
            alert("请至少选择一个漫画");
            e.preventDefault();
            return false;
        }
        
        if (action === "delete") {
            if (!confirm("确定要删除选中的 " + checkedIds.length + " 个漫画吗？")) {
                e.preventDefault();
                return false;
            }
        }
        
        // 添加选中的ID到表单
        checkedIds.forEach(function(id) {
            $(\"<input>\").attr({
                type: "hidden",
                name: "manga_ids[]",
                value: id
            }).appendTo("#batchForm");
        });
    });
    
    // 删除单个漫画
    $(".delete-manga").click(function() {
        var id = $(this).data("id");
        if (confirm("确定要删除这个漫画吗？")) {
            $.ajax({
                url: "/admin88/api/delete-manga.php",
                type: "POST",
                data: { id: id },
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.message || "删除失败");
                    }
                }
            });
        }
    });
});
</script>
';

include APP_PATH . '/views/admin/layout_footer.php';
?>


