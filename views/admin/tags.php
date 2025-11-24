<?php
/**
 * A3-标签管理模块
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '标签管理';

// 处理标签操作
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $typeId = $_POST['type_id'] ?? 0;
            $tagName = trim($_POST['tag_name'] ?? '');
            $tagType = $_POST['tag_type'] ?? 'category';
            
            if ($typeId && $tagName) {
                $result = $db->insert('tags', [
                    'type_id' => $typeId,
                    'tag_name' => $tagName,
                    'tag_type' => $tagType,
                    'sort_order' => 0
                ]);
                
                $message = $result ? '标签添加成功' : '标签添加失败';
                $messageType = $result ? 'success' : 'danger';
            }
            break;
            
        case 'delete':
            $tagId = $_POST['tag_id'] ?? 0;
            if ($tagId) {
                // 先将使用此标签的漫画移至"未分类"
                $tagInfo = $db->queryOne("SELECT type_id FROM tags WHERE id = ?", [$tagId]);
                if ($tagInfo) {
                    $uncategorizedTag = $db->queryOne(
                        "SELECT id FROM tags WHERE type_id = ? AND tag_name = '未分类'",
                        [$tagInfo['type_id']]
                    );
                    
                    if ($uncategorizedTag) {
                        $db->execute(
                            "UPDATE mangas SET tag_id = ? WHERE tag_id = ?",
                            [$uncategorizedTag['id'], $tagId]
                        );
                    }
                }
                
                // 删除标签
                $result = $db->delete('tags', 'id = ?', [$tagId]);
                $message = $result ? '标签已删除，关联漫画已移至"未分类"' : '删除失败';
                $messageType = $result ? 'success' : 'danger';
            }
            break;
            
        case 'edit':
            // CSRF Token验证
            if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $message = 'CSRF验证失败，请刷新页面重试';
                $messageType = 'danger';
            } else {
                $tagId = $_POST['tag_id'] ?? 0;
                $tagName = trim($_POST['tag_name'] ?? '');
                
                if ($tagId && $tagName) {
                    $result = $db->update(
                        'tags',
                        ['tag_name' => $tagName],
                        'id = ?',
                        [$tagId]
                    );
                    
                    $message = $result !== false ? '标签更新成功' : '更新失败';
                    $messageType = $result !== false ? 'success' : 'danger';
                } else {
                    $message = '请填写完整信息';
                    $messageType = 'danger';
                }
            }
            break;
    }
}

// 获取所有类型
$types = $db->query("SELECT * FROM manga_types ORDER BY sort_order");

// 获取所有标签（按类型分组）
$allTags = $db->query(
    "SELECT t.*, mt.type_name, 
     (SELECT COUNT(*) FROM mangas WHERE tag_id = t.id) as manga_count
     FROM tags t
     LEFT JOIN manga_types mt ON t.type_id = mt.id
     ORDER BY t.type_id, t.sort_order, t.id"
);

// 按类型分组标签
$tagsByType = [];
foreach ($allTags as $tag) {
    $typeId = $tag['type_id'];
    if (!isset($tagsByType[$typeId])) {
        $tagsByType[$typeId] = [];
    }
    $tagsByType[$typeId][] = $tag;
}

include APP_PATH . '/views/admin/layout_header.php';
?>

<div class="content-header">
    <h2>标签管理</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin88/">控制台</a></li>
            <li class="breadcrumb-item active">标签管理</li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- 添加标签 -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">添加新标签</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="add">
            
            <div class="col-md-3">
                <label class="form-label">所属类型</label>
                <select class="form-select" name="type_id" required>
                    <option value="">选择类型</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type['id']; ?>">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">标签名称</label>
                <input type="text" class="form-control" name="tag_name" required placeholder="如：2024-11-16 或 A">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">标签类型</label>
                <select class="form-select" name="tag_type">
                    <option value="date">日期标签</option>
                    <option value="letter">字母标签</option>
                    <option value="category" selected>分类标签</option>
                    <option value="author">作者标签</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-custom w-100">
                    <i class="bi bi-plus-circle"></i> 添加标签
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 标签列表 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">所有标签</h5>
    </div>
    <div class="card-body">
        <?php foreach ($types as $type): ?>
            <h6 class="mt-3 mb-3">
                <i class="bi bi-folder"></i> <?php echo htmlspecialchars($type['type_name']); ?>
            </h6>
            
            <?php if (isset($tagsByType[$type['id']])): ?>
                <table class="table table-hover mb-4">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>标签名称</th>
                            <th width="120">标签类型</th>
                            <th width="100">关联漫画</th>
                            <th width="150">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tagsByType[$type['id']] as $tag): ?>
                            <tr>
                                <td><?php echo $tag['id']; ?></td>
                                <td>
                                    <span id="tag-name-<?php echo $tag['id']; ?>">
                                        <?php echo htmlspecialchars($tag['tag_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'date' => '日期',
                                        'letter' => '字母',
                                        'category' => '分类',
                                        'author' => '作者'
                                    ];
                                    echo $typeLabels[$tag['tag_type']] ?? $tag['tag_type'];
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $tag['manga_count']; ?> 个</span>
                                </td>
                                <td class="table-actions">
                                    <button class="btn btn-sm btn-outline-primary edit-tag" 
                                            data-id="<?php echo $tag['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($tag['tag_name']); ?>">
                                        <i class="bi bi-pencil"></i> 编辑
                                    </button>
                                    
                                    <?php if ($tag['tag_name'] !== '未分类'): ?>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('确定删除此标签？关联的漫画将移至【未分类】');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tag_id" value="<?php echo $tag['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> 删除
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">此类型暂无标签</p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- 编辑标签弹窗 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">编辑标签</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tag_id" id="edit-tag-id">
                    
                    <div class="mb-3">
                        <label class="form-label">标签名称</label>
                        <input type="text" class="form-control" name="tag_name" id="edit-tag-name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$customJs = '
<script>
$(document).ready(function() {
    $(".edit-tag").click(function() {
        var id = $(this).data("id");
        var name = $(this).data("name");
        
        $("#edit-tag-id").val(id);
        $("#edit-tag-name").val(name);
        $("#editModal").modal("show");
    });
});
</script>
';

include APP_PATH . '/views/admin/layout_footer.php';
?>


