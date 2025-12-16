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

// 可选图标列表
$availableIcons = [
    'calendar-date' => '日历',
    'collection' => '合集',
    'book' => '书本',
    'star' => '星星',
    'gift' => '礼物',
    'film' => '电影',
    'headphones' => '耳机',
    'chat-dots' => '对话',
    'geo-alt' => '定位',
    'heart' => '爱心',
    'fire' => '火焰',
    'lightning' => '闪电',
    'trophy' => '奖杯',
    'bookmark' => '书签',
    'folder' => '文件夹',
    'grid' => '网格',
    'list' => '列表',
    'check-circle' => '勾选',
    'play-circle' => '播放',
    'music-note' => '音符',
    'camera' => '相机',
    'image' => '图片',
    'puzzle' => '拼图',
    'cup-hot' => '咖啡',
    'emoji-smile' => '笑脸',
];

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
                $icon       = trim($_POST['icon'] ?? 'book');
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
                        'icon'        => $icon,
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
                $icon       = trim($_POST['icon'] ?? 'book');
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
                        'icon'        => $icon,
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

<style>
    .icon-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        max-height: 200px;
        overflow-y: auto;
    }
    .icon-option {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }
    .icon-option:hover {
        border-color: #FF6B35;
        background: #FFF5E6;
    }
    .icon-option.selected {
        border-color: #FF6B35;
        background: #FF6B35;
        color: white;
    }
    .icon-option i {
        font-size: 1.3rem;
    }
    .icon-preview {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border-radius: 12px;
        color: white;
        font-size: 1.5rem;
    }
    .icon-select-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 15px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .icon-select-btn:hover {
        border-color: #FF6B35;
    }
    .icon-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        width: 300px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        padding: 15px;
    }
    .icon-dropdown.show {
        display: block;
    }
    .icon-wrapper {
        position: relative;
    }
    .table-icon-cell {
        width: 80px;
    }
    .table-icon-preview {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border-radius: 10px;
        color: white;
        font-size: 1.2rem;
    }
</style>

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
        <h5 class="mb-0"><i class="bi bi-plus-circle text-primary"></i> 添加新模块</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php echo $session->csrfField(); ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="icon" id="addIconInput" value="book">

            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold">模块名称 <span class="text-danger">*</span></label>
                    <input type="text" name="type_name" class="form-control" placeholder="如：日更板块" required>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-semibold">类型代码 <span class="text-danger">*</span></label>
                    <input type="text" name="type_code" class="form-control" placeholder="如：daily_update" required>
                </div>
                <div class="col-md-2 col-sm-4">
                    <label class="form-label fw-semibold">模块图标</label>
                    <div class="icon-wrapper">
                        <div class="icon-select-btn-new" id="addIconBtn">
                            <div class="icon-preview-small" id="addIconPreview">
                                <i class="bi bi-book"></i>
                            </div>
                            <span>选择图标</span>
                            <i class="bi bi-chevron-down ms-auto"></i>
                        </div>
                        <div class="icon-dropdown" id="addIconDropdown">
                            <div class="icon-selector">
                                <?php foreach ($availableIcons as $iconName => $iconLabel): ?>
                                    <div class="icon-option <?php echo $iconName === 'book' ? 'selected' : ''; ?>" 
                                         data-icon="<?php echo $iconName; ?>" 
                                         title="<?php echo $iconLabel; ?>">
                                        <i class="bi bi-<?php echo $iconName; ?>"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 col-4">
                    <label class="form-label fw-semibold">排序</label>
                    <input type="number" name="sort_order" class="form-control text-center" value="0">
                </div>
                <div class="col-md-1 col-sm-3 col-4">
                    <label class="form-label fw-semibold d-block">状态</label>
                    <div class="form-check-box">
                        <input class="form-check-input" type="checkbox" name="need_status" id="addNeedStatus">
                        <label class="form-check-label small" for="addNeedStatus">连载/完结</label>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 col-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> 添加
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* 统一的图标选择按钮样式 */
.icon-select-btn-new {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    color: #495057;
}
.icon-select-btn-new:hover {
    border-color: #FF6B35;
    background: #fff9f5;
}
.icon-preview-small {
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
    border-radius: 6px;
    color: white;
    font-size: 0.9rem;
}
.form-check-box {
    display: flex;
    align-items: center;
    gap: 6px;
    height: 38px;
    padding: 0 8px;
    background: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 6px;
}
.form-check-box .form-check-input {
    margin: 0;
}
.form-check-box .form-check-label {
    margin: 0;
    white-space: nowrap;
}
</style>

<!-- 模块列表 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul text-info"></i> 模块列表</h5>
    </div>
    <div class="card-body">
        <?php if (empty($types)): ?>
            <p class="text-muted mb-0">当前尚未配置任何模块。</p>
        <?php else: ?>
            <!-- 桌面端表格视图 -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th class="table-icon-cell">图标</th>
                            <th>模块名称</th>
                            <th>类型代码</th>
                            <th width="80">排序</th>
                            <th width="60">状态</th>
                            <th width="160">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $type): ?>
                            <tr>
                                <td><?php echo (int)$type['id']; ?></td>
                                <td>
                                    <form method="POST" class="module-form" data-id="<?php echo (int)$type['id']; ?>">
                                        <?php echo $session->csrfField(); ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?php echo (int)$type['id']; ?>">
                                        <input type="hidden" name="icon" class="icon-input" value="<?php echo htmlspecialchars($type['icon'] ?? 'book'); ?>">
                                        
                                        <div class="icon-wrapper">
                                            <div class="table-icon-preview icon-trigger" style="cursor: pointer;">
                                                <i class="bi bi-<?php echo htmlspecialchars($type['icon'] ?? 'book'); ?>"></i>
                                            </div>
                                            <div class="icon-dropdown">
                                                <div class="icon-selector">
                                                    <?php foreach ($availableIcons as $iconName => $iconLabel): ?>
                                                        <div class="icon-option <?php echo ($type['icon'] ?? 'book') === $iconName ? 'selected' : ''; ?>" 
                                                             data-icon="<?php echo $iconName; ?>" 
                                                             title="<?php echo $iconLabel; ?>">
                                                            <i class="bi bi-<?php echo $iconName; ?>"></i>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                </td>
                                <td>
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
                                <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
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

            <!-- 移动端卡片视图 -->
            <div class="d-md-none">
                <?php foreach ($types as $type): ?>
                    <div class="mobile-card mb-3">
                        <form method="POST">
                            <?php echo $session->csrfField(); ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo (int)$type['id']; ?>">
                            <input type="hidden" name="icon" class="icon-input" value="<?php echo htmlspecialchars($type['icon'] ?? 'book'); ?>">
                            
                            <div class="mobile-card-header d-flex align-items-center gap-2">
                                <div class="table-icon-preview icon-trigger" style="cursor: pointer;">
                                    <i class="bi bi-<?php echo htmlspecialchars($type['icon'] ?? 'book'); ?>"></i>
                                </div>
                                <span class="badge bg-secondary">ID: <?php echo (int)$type['id']; ?></span>
                            </div>
                            
                            <div class="mobile-card-body">
                                <div class="mb-2">
                                    <label class="mobile-label">模块名称</label>
                                    <input type="text" name="type_name" class="form-control form-control-sm"
                                           value="<?php echo htmlspecialchars($type['type_name']); ?>">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="mobile-label">类型代码</label>
                                    <input type="text" name="type_code" class="form-control form-control-sm"
                                           value="<?php echo htmlspecialchars($type['type_code']); ?>">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="mobile-label">排序</label>
                                    <input type="number" name="sort_order" class="form-control form-control-sm"
                                           value="<?php echo (int)$type['sort_order']; ?>">
                                </div>
                                
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="need_status"
                                               id="mobile_status_<?php echo $type['id']; ?>"
                                               <?php echo $type['need_status'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label mobile-label" for="mobile_status_<?php echo $type['id']; ?>">
                                            需要状态（连载中/已完结）
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mobile-card-footer">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-save"></i> 保存
                                </button>
                        </form>
                        
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('确定删除该模块？');">
                            <?php echo $session->csrfField(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$type['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> 删除
                            </button>
                        </form>
                            </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <style>
        /* 移动端卡片样式 */
        .mobile-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .mobile-card-header {
            background: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .mobile-card-body {
            padding: 15px;
        }
        .mobile-card-footer {
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 8px;
        }
        .mobile-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
            display: block;
        }
        .mobile-card .form-control-sm {
            font-size: 0.85rem;
        }
        .mobile-card .btn-sm {
            font-size: 0.8rem;
            padding: 6px 12px;
        }
        </style>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 添加模块的图标选择
    var addIconBtn = document.getElementById('addIconBtn');
    var addIconDropdown = document.getElementById('addIconDropdown');
    var addIconInput = document.getElementById('addIconInput');
    var addIconPreview = document.getElementById('addIconPreview');
    
    if (addIconBtn) {
        addIconBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            addIconDropdown.classList.toggle('show');
        });
        
        addIconDropdown.querySelectorAll('.icon-option').forEach(function(option) {
            option.addEventListener('click', function() {
                var icon = this.dataset.icon;
                addIconInput.value = icon;
                addIconPreview.innerHTML = '<i class="bi bi-' + icon + '"></i>';
                addIconDropdown.querySelectorAll('.icon-option').forEach(function(o) {
                    o.classList.remove('selected');
                });
                this.classList.add('selected');
                addIconDropdown.classList.remove('show');
            });
        });
    }
    
    // 表格中的图标选择
    document.querySelectorAll('.icon-trigger').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var dropdown = this.closest('.icon-wrapper').querySelector('.icon-dropdown');
            // 关闭其他打开的下拉框
            document.querySelectorAll('.icon-dropdown.show').forEach(function(d) {
                if (d !== dropdown) d.classList.remove('show');
            });
            dropdown.classList.toggle('show');
        });
    });
    
    document.querySelectorAll('.icon-dropdown .icon-option').forEach(function(option) {
        option.addEventListener('click', function() {
            var icon = this.dataset.icon;
            var wrapper = this.closest('.icon-wrapper');
            var input = wrapper.closest('form').querySelector('.icon-input');
            var preview = wrapper.querySelector('.icon-trigger i');
            
            if (input) input.value = icon;
            if (preview) preview.className = 'bi bi-' + icon;
            
            wrapper.querySelectorAll('.icon-option').forEach(function(o) {
                o.classList.remove('selected');
            });
            this.classList.add('selected');
            wrapper.querySelector('.icon-dropdown').classList.remove('show');
        });
    });
    
    // 点击其他地方关闭下拉框
    document.addEventListener('click', function() {
        document.querySelectorAll('.icon-dropdown.show').forEach(function(d) {
            d.classList.remove('show');
        });
    });
});
</script>

<?php include APP_PATH . '/views/admin/layout_footer.php'; ?>
