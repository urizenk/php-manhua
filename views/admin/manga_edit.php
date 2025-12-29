<?php
/**
 * A2-编辑漫画模块
 */

// 从 GLOBALS 获取变量
$db = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config = $GLOBALS['config'] ?? null;

$pageTitle = '编辑漫画';

// 获取漫画ID
$mangaId = $_GET['id'] ?? 0;

if (!$mangaId) {
    header('Location: /admin88/manga/list');
    exit;
}

// 获取漫画信息
$manga = $db->queryOne("SELECT * FROM mangas WHERE id = ?", [$mangaId]);

if (!$manga) {
    header('Location: /admin88/manga/list');
    exit;
}

// 解析资源链接（支持 JSON 或多行文本）
$resourceLinksTitle = '资源链接';
$resourceLinksItems = [];
$rawResourceLink = trim((string)($manga['resource_link'] ?? ''));
if ($rawResourceLink !== '') {
    $decoded = json_decode($rawResourceLink, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['items']) && is_array($decoded['items'])) {
        $resourceLinksTitle = trim((string)($decoded['title'] ?? $resourceLinksTitle)) ?: $resourceLinksTitle;
        foreach ($decoded['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $url = trim((string)($item['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $resourceLinksItems[] = [
                'label' => trim((string)($item['label'] ?? '')),
                'url'   => $url,
            ];
        }
    } else {
        $lines = preg_split('/[\r\n]+/', $rawResourceLink);
        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }
            $resourceLinksItems[] = ['label' => '', 'url' => $line];
        }
    }
}

// 处理表单提交
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_manga'])) {
    // CSRF Token验证
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'CSRF验证失败，请刷新页面重试';
        $messageType = 'danger';
    } else {
        $typeId = $_POST['type_id'] ?? 0;
        $tagId = $_POST['tag_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $status = $_POST['status'] ?? null;
        $resourceLinkTitle  = trim($_POST['resource_links_title'] ?? '资源链接');
        $resourceLinkLabels = $_POST['resource_links_label'] ?? [];
        $resourceLinkUrls   = $_POST['resource_links_url'] ?? [];
        $resourceLink       = '';
        $extractCode = trim($_POST['extract_code'] ?? '');
        $mangaTags = trim($_POST['manga_tags'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $coverPosition = $_POST['cover_position'] ?? 'center';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        $errors = [];
        
        if (!$typeId) $errors[] = '请选择漫画类型';
        if (!$title) $errors[] = '请输入漫画标题';
    
    // 处理封面图片上传
    $coverImage = $manga['cover_image']; // 保留原图
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload = new \App\Core\Upload($config['upload']);
        $uploadResult = $upload->uploadSingle($_FILES['cover_image'], 'covers');
        
        if ($uploadResult) {
            // 删除旧图片
            if ($manga['cover_image']) {
                $oldImagePath = APP_PATH . $manga['cover_image'];
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }
            $coverImage = $uploadResult['path'];
        } else {
            $errors[] = '图片上传失败：' . $upload->getError();
        }
    }
    
    if (empty($errors)) {
        $resourceItems = [];
        if (is_array($resourceLinkUrls)) {
            foreach ($resourceLinkUrls as $i => $url) {
                $url = trim((string)$url);
                if ($url === '') {
                    continue;
                }
                $label = '';
                if (is_array($resourceLinkLabels) && array_key_exists($i, $resourceLinkLabels)) {
                    $label = trim((string)$resourceLinkLabels[$i]);
                }
                $resourceItems[] = [
                    'label' => $label,
                    'url'   => $url,
                ];
            }
        }
        if (!empty($resourceItems)) {
            $resourceLink = json_encode([
                'title' => $resourceLinkTitle !== '' ? $resourceLinkTitle : '资源链接',
                'items' => $resourceItems,
            ], JSON_UNESCAPED_UNICODE);
        }

        $updateData = [
            'type_id' => $typeId,
            'title' => $title,
            'resource_link' => $resourceLink,
            'extract_code' => $extractCode,
            'manga_tags' => $mangaTags,
            'description' => $description,
            'cover_position' => $coverPosition,
            'sort_order' => $sortOrder,
        ];
        
        if ($tagId) {
            $updateData['tag_id'] = $tagId;
        }
        if ($coverImage) {
            $updateData['cover_image'] = $coverImage;
        }
        if ($status) {
            $updateData['status'] = $status;
        }
        
        $result = $db->update('mangas', $updateData, 'id = ?', [$mangaId]);
        
        if ($result !== false) {
            $message = '漫画更新成功！';
            $messageType = 'success';
            // 重新获取更新后的数据
            $manga = $db->queryOne("SELECT * FROM mangas WHERE id = ?", [$mangaId]);
        } else {
            $message = '更新失败，请重试';
            $messageType = 'danger';
        }
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'danger';
        }
    }
}

// 获取所有类型
$types = $db->query("SELECT * FROM manga_types ORDER BY sort_order");

// 获取当前类型的标签
$tags = [];
if ($manga['type_id']) {
    $tags = $db->query(
        "SELECT * FROM tags WHERE type_id = ? ORDER BY sort_order, id",
        [$manga['type_id']]
    );
}

include APP_PATH . '/views/admin/layout_header.php';
?>

<style>
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }
    .form-section-title {
        font-weight: bold;
        margin-bottom: 20px;
        color: #495057;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title i {
        color: #FF6B35;
    }
    .current-cover {
        max-width: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>

<div class="content-header">
    <h2>编辑漫画</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin88/">控制台</a></li>
            <li class="breadcrumb-item"><a href="/admin88/manga/list">漫画列表</a></li>
            <li class="breadcrumb-item active">编辑漫画</li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil-square text-primary"></i> 编辑漫画信息 - ID: <?php echo $manga['id']; ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="mangaForm">
            <?php echo $session->csrfField(); ?>
            
            <!-- 基本信息 -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-info-circle"></i> 基本信息</div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">漫画类型 <span class="text-danger">*</span></label>
                        <select class="form-select" name="type_id" id="typeSelect" required>
                            <option value="">请选择类型</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        data-need-cover="<?php echo $type['need_cover']; ?>"
                                        data-need-status="<?php echo $type['need_status']; ?>"
                                        <?php echo $manga['type_id'] == $type['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">所属标签（板块标签）</label>
                        <select class="form-select" name="tag_id" id="tagSelect">
                            <option value="">选择标签（可选）</option>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>"
                                        <?php echo $manga['tag_id'] == $tag['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tag['tag_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">漫画标题 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required 
                           value="<?php echo htmlspecialchars($manga['title']); ?>" 
                           placeholder="输入漫画标题">
                </div>

                <div class="mb-3">
                    <label class="form-label">漫画标签</label>
                    <input type="text" class="form-control" name="manga_tags" 
                           value="<?php echo htmlspecialchars($manga['manga_tags'] ?? ''); ?>"
                           placeholder="如：职场、单恋攻、哭包攻、美人受、做炸受">
                    <small class="text-muted">漫画内容标签，多个用中文逗号分隔，显示在详情页</small>
                </div>
            </div>
            
            <!-- 状态选择 -->
            <?php if ($manga['status']): ?>
            <div class="form-section" id="statusSection">
                <div class="form-section-title"><i class="bi bi-flag"></i> 连载状态</div>
                
                <div class="mb-3">
                    <label class="form-label">状态</label>
                    <select class="form-select" name="status">
                        <option value="">请选择状态</option>
                        <option value="serializing" <?php echo $manga['status'] == 'serializing' ? 'selected' : ''; ?>>连载中</option>
                        <option value="completed" <?php echo $manga['status'] == 'completed' ? 'selected' : ''; ?>>已完结</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 封面图片 -->
            <div class="form-section" id="coverSection">
                <div class="form-section-title"><i class="bi bi-image"></i> 封面图片</div>
                
                <?php if ($manga['cover_image']): ?>
                <div class="mb-3">
                    <label class="form-label">当前封面</label>
                    <div>
                        <img src="<?php echo htmlspecialchars($manga['cover_image']); ?>" 
                             class="current-cover" alt="当前封面">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label">上传新封面</label>
                    <input type="file" class="form-control" name="cover_image" accept="image/*">
                    <small class="text-muted">支持 JPG、PNG、WEBP 格式，最大 5MB。不上传则保留原封面。</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">封面展示位置</label>
                    <select class="form-select" name="cover_position">
                        <option value="center" <?php echo ($manga['cover_position'] ?? 'center') == 'center' ? 'selected' : ''; ?>>居中</option>
                        <option value="top" <?php echo ($manga['cover_position'] ?? '') == 'top' ? 'selected' : ''; ?>>顶部</option>
                        <option value="bottom" <?php echo ($manga['cover_position'] ?? '') == 'bottom' ? 'selected' : ''; ?>>底部</option>
                    </select>
                    <small class="text-muted">用于横幅图片展示时的焦点位置</small>
                </div>
            </div>
            
            <!-- 资源链接 -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-link-45deg"></i> 资源信息</div>
                
                <div class="mb-3">
                    <label class="form-label">链接模块标题（可修改）</label>
                    <input type="text" class="form-control" name="resource_links_title"
                           value="<?php echo htmlspecialchars($resourceLinksTitle); ?>"
                           placeholder="例如：资源链接 / 下载链接 / 在线阅读">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">链接列表</label>
                    <div id="resourceLinksList">
                        <?php if (!empty($resourceLinksItems)): ?>
                            <?php foreach ($resourceLinksItems as $item): ?>
                                <div class="row g-2 mb-2 resource-link-item">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="resource_links_label[]"
                                               value="<?php echo htmlspecialchars($item['label']); ?>"
                                               placeholder="按钮文字（可选）">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="resource_links_url[]"
                                               value="<?php echo htmlspecialchars($item['url']); ?>"
                                               placeholder="https://...">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row g-2 mb-2 resource-link-item">
                                <div class="col-4">
                                    <input type="text" class="form-control" name="resource_links_label[]" placeholder="按钮文字（可选）">
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" name="resource_links_url[]" placeholder="https://...">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addResourceLinkRow">
                        <i class="bi bi-plus-circle"></i> 添加链接
                    </button>
                    <small class="text-muted d-block mt-2">每个链接会在详情页中单独展示为一个按钮</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">提取码</label>
                    <input type="text" class="form-control" name="extract_code" 
                           value="<?php echo htmlspecialchars($manga['extract_code'] ?? ''); ?>"
                           placeholder="如：1234（多个提取码用空格或逗号分隔）">
                    <small class="text-muted">网盘提取码（可选）</small>
                </div>
            </div>
            
            <!-- 简介 -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-file-text"></i> 简介</div>
                
                <div class="mb-3">
                    <label class="form-label">漫画简介（可选）</label>
                    <textarea class="form-control" name="description" rows="4" 
                              placeholder="输入漫画简介或说明"><?php echo htmlspecialchars($manga['description']); ?></textarea>
                </div>
            </div>
            
            <!-- 排序 -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-sort-numeric-down"></i> 排序</div>
                
                <div class="mb-3">
                    <label class="form-label">排序权重</label>
                    <input type="number" class="form-control" name="sort_order" 
                           value="<?php echo (int)($manga['sort_order'] ?? 0); ?>"
                           placeholder="数字越大排越前面">
                    <small class="text-muted">数字越大，在列表中排序越靠前</small>
                </div>
            </div>
            
            <!-- 提交按钮 -->
            <div class="text-end">
                <a href="/admin88/manga/list" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> 取消
                </a>
                <button type="submit" name="update_manga" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> 更新漫画
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$customJs = '
<script>
$(document).ready(function() {
    // 类型切换时加载对应标签
    $("#typeSelect").change(function() {
        var typeId = $(this).val();
        if (typeId) {
            $.get("/admin88/api/get-tags.php?type_id=" + typeId, function(tags) {
                var options = "<option value=\"\">选择标签（可选）</option>";
                tags.forEach(function(tag) {
                    options += "<option value=\"" + tag.id + "\">" + tag.tag_name + "</option>";
                });
                $("#tagSelect").html(options);
            });
        }
    });

    // 添加资源链接行
    $("#addResourceLinkRow").on("click", function() {
        var row = `
            <div class="row g-2 mb-2 resource-link-item">
                <div class="col-4">
                    <input type="text" class="form-control" name="resource_links_label[]" placeholder="按钮文字（可选）">
                </div>
                <div class="col-8">
                    <input type="text" class="form-control" name="resource_links_url[]" placeholder="https://...">
                </div>
            </div>
        `;
        $("#resourceLinksList").append(row);
    });
});
</script>
';

include APP_PATH . '/views/admin/layout_footer.php';
?>
