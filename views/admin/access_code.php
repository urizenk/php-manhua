<?php
/**
 * A4-访问码更新模块
 */
$pageTitle = '访问码更新';

// 处理表单提交
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_code'])) {
    $newCode = trim($_POST['new_code']);
    
    if (empty($newCode)) {
        $message = '访问码不能为空';
        $messageType = 'danger';
    } else {
        $result = $db->update(
            'site_config',
            ['config_value' => $newCode, 'updated_at' => date('Y-m-d H:i:s')],
            'config_key = ?',
            ['access_code']
        );
        
        if ($result !== false) {
            $message = '访问码更新成功！';
            $messageType = 'success';
        } else {
            $message = '访问码更新失败，请检查数据库连接';
            $messageType = 'danger';
        }
    }
}

// 获取当前访问码
$currentCode = $session->getAccessCode();

// 获取历史访问记录
$accessLogs = $db->query(
    "SELECT * FROM access_logs ORDER BY created_at DESC LIMIT 50"
);

include APP_PATH . '/views/admin/layout_header.php';
?>

<div class="content-header">
    <h2>访问码更新</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin88/">控制台</a></li>
            <li class="breadcrumb-item active">访问码更新</li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">更新访问码</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">当前访问码</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($currentCode); ?>" readonly>
                        <small class="text-muted">前台用户需要输入此访问码才能访问内容</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">新访问码 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="new_code" required placeholder="输入新的访问码">
                        <small class="text-muted">建议使用4-8位数字或字母组合</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-custom">
                        <i class="bi bi-check-circle"></i> 更新访问码
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">说明</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>访问码用于控制前台用户访问权限</li>
                    <li>建议每日更新访问码，提升安全性</li>
                    <li>更新后，用户需要输入新访问码才能访问内容</li>
                    <li>已验证过的用户会话在2小时内有效</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">访问记录（最近50条）</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($accessLogs)): ?>
                        <p class="text-muted text-center">暂无访问记录</p>
                    <?php else: ?>
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>IP地址</th>
                                    <th>访问码</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accessLogs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        <td><?php echo htmlspecialchars($log['access_code'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($log['is_success']): ?>
                                                <span class="badge bg-success">成功</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">失败</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('m-d H:i', strtotime($log['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layout_footer.php'; ?>


