<?php
/**
 * A5-模块/类型管理模块
 * 用于管理首页展示的漫画模块（manga_types）
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

if (!$db || !$session) {
    echo '系统初始化失败，请检查配置。';
    exit;
}

$pageTitle   = '模块管理';
$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 所有写操作统一做 CSRF 校验
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message     = 'CSRF验证失败，请刷新页面重试';
        $messageType = 'danger';
    } else {
        switch ($action) {
            case 'add':
                $typeName   = trim($_POST['type_name'] ?? '');
                $typeCode   = trim($_POST['type_code'] ?? '');
                $sortOrder  = (int)($_POST['sort_order'] ?? 0);
                $needCover  = isset($_POST['need_cover']) ? 1 : 0;
                $needStatus = isset($_POST['need_status']) ? 1 : 0;

                if ($typeName === '' || $typeCode === '') {
                    $message     = '类型名称和类型代码不能为空';
                    $messageType = 'danger';
                    break;
                }

                try {
                    $db->insert('manga_types', [
                        'type_name'   => $typeName,
                        'type_code'   => $typeCode,
                        'sort_order'  => $sortOrder,
                        'need_cover'  => $needCover,
                        'need_status' => $needStatus,
                    ]);
                    $message     = '模块添加成功';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message     = '添加失败：' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;

            case 'update':
                $id         = (int)($_POST['id'] ?? 0);
                $typeName   = trim($_POST['type_name'] ?? '');
                $typeCode   = trim($_POST['type_code'] ?? '');
                $sortOrder  = (int)($_POST['sort_order'] ?? 0);
                $needCover  = isset($_POST['need_cover']) ? 1 : 0;
                $needStatus = isset($_POST['need_status']) ? 1 : 0;

                if ($id <= 0 || $typeName === '' || $typeCode === '') {
                    $message     = '参数不完整';
                    $messageType = 'danger';
                    break;
                }

                $result = $db->update(
                    'manga_types',
                    [
                        'type_name'   => $typeName,
                        'type_code'   => $typeCode,
                        'sort_order'  => $sortOrder,
                        'need_cover'  => $needCover,
                        'need_status' => $needStatus,
                    ],
                    'id = ?',
                    [$id]
                );

                if ($result !== false) {
                    $message     = '模块更新成功';
                    $messageType = 'success';
                } else {
                    $message     = '模块更新失败';
                    $messageType = 'danger';
                }
                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $message     = '参数错误';
                    $messageType = 'danger';
                    break;
                }

                // 检查是否已有漫画使用该类型
                $countRow = $db->queryOne(
                    'SELECT COUNT(*) AS cnt FROM mangas WHERE type_id = ?',
                    [$id]
                );
                if (!empty($countRow['cnt'])) {
                    $message     = '该模块下已有漫画，无法直接删除';
                    $messageType = 'danger';
                    break;
                }

                $result = $db->delete('manga_types', 'id = ?', [$id]);
                if ($result) {
                    $message     = '模块已删除';
                    $messageType = 'success';
                } else {
                    $message     = '删除失败';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// 获取所有模块类型
$types = $db->query('SELECT * FROM manga_types ORDER BY sort_order, id');

include APP_PATH . '/views/admin/layout_header.php';
?>

<div class="content-header">
    <h2>模块管理</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin88/">控制台</a></li>
            <li class="breadcrumb-item active">模块管理</li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- 添加模块 -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">添加新模块</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <?php echo $session->csrfField(); ?>
            <input type="hidden" name="action" value="add">

            <div class="col-md-3">
                <label class="form-label">模块名称</label>
                <input type="text" name="type_name" class="form-control" placeholder="如：日更板块" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">类型代码</label>
                <input type="text" name="type_code" class="form-control" placeholder="如：daily_update" required>
                <small class="text-muted">用于程序识别，建议使用英文+下划线</small>
            </div>
            <div class="col-md-2">
                <label class="form-label">排序</label>
                <input type="number" name="sort_order" class="form-control" value="0">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="need_cover" id="addNeedCover">
                    <label class="form-check-label" for="addNeedCover">需要封面</label>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="need_status" id="addNeedStatus">
                    <label class="form-check-label" for="addNeedStatus">需要状态</label>
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary btn-custom">
                    <i class="bi bi-plus-circle"></i> 添加模块
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 模块列表 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">模块列表</h5>
    </div>
    <div class="card-body">
        <?php if (empty($types)): ?>
            <p class="text-muted mb-0">当前尚未配置任何模块。</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>模块名称</th>
                            <th>类型代码</th>
                            <th width="80">排序</th>
                            <th width="80">封面</th>
                            <th width="80">状态</th>
                            <th width="160">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $type): ?>
                            <tr>
                                <td><?php echo (int)$type['id']; ?></td>
                                <td>
                                    <form method="POST" class="d-flex align-items-center gap-2">
                                        <?php echo $session->csrfField(); ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?php echo (int)$type['id']; ?>">
                                        <input type="text" name="type_name" class="form-control form-control-sm"
                                               value="<?php echo htmlspecialchars($type['type_name']); ?>">
                                </td>
                                <td>
                                        <input type="text" name="type_code" class="form-control form-control-sm"
                                               value="<?php echo htmlspecialchars($type['type_code']); ?>">
                                </td>
                                <td>
                                        <input type="number" name="sort_order" class="form-control form-control-sm"
                                               value="<?php echo (int)$type['sort_order']; ?>">
                                </td>
                                <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="need_cover"
                                                   <?php echo $type['need_cover'] ? 'checked' : ''; ?>>
                                        </div>
                                </td>
                                <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="need_status"
                                                   <?php echo $type['need_status'] ? 'checked' : ''; ?>>
                                        </div>
                                </td>
                                <td>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-save"></i> 保存
                                        </button>
                                    </form>

                                    <form method="POST" style="display:inline;"
                                          onsubmit="return confirm('确定删除该模块？（仅在没有漫画使用该模块时可删除）');">
                                        <?php echo $session->csrfField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$type['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger mt-1">
                                            <i class="bi bi-trash"></i> 删除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layout_footer.php'; ?>

