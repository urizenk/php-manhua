<?php
/**
 * A1-漫画添加模块
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

if (!$db || !$session) {
    echo '系统初始化失败，请检查配置。';
    exit;
}

$pageTitle = '添加漫画';

// 处理表单提交
$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_manga'])) {
    // CSRF Token 验证
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message     = 'CSRF验证失败，请刷新页面重试';
        $messageType = 'danger';
    } else {
        $typeId       = (int)($_POST['type_id'] ?? 0);
        $tagId        = isset($_POST['tag_id']) && $_POST['tag_id'] !== '' ? (int)$_POST['tag_id'] : null;
        $title        = trim($_POST['title'] ?? '');
        $status       = $_POST['status'] ?? '';
        $resourceLink = trim($_POST['resource_link'] ?? '');
        $extractCode  = trim($_POST['extract_code'] ?? '');
        $mangaTags    = trim($_POST['manga_tags'] ?? '');
        $description  = trim($_POST['description'] ?? '');

        $errors = [];

        if ($typeId <= 0) {
            $errors[] = '请选择漫画类型';
        }
        if ($title === '') {
            $errors[] = '请输入漫画标题';
        }

        // 处理封面图片上传
        $coverImage = null;
        if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload       = new \App\Core\Upload($config['upload']);
            $uploadResult = $upload->uploadSingle($_FILES['cover_image'], 'covers');

            if ($uploadResult) {
                $coverImage = $uploadResult['path'];
            } else {
                $errors[] = '图片上传失败：' . $upload->getError();
            }
        }

        if (empty($errors)) {
            $insertData = [
                'type_id'       => $typeId,
                'title'         => $title,
                'resource_link' => $resourceLink,
                'extract_code'  => $extractCode,
                'manga_tags'    => $mangaTags,
                'description'   => $description,
            ];

            if ($tagId) {
                $insertData['tag_id'] = $tagId;
            }
            if ($coverImage) {
                $insertData['cover_image'] = $coverImage;
            }
            if ($status !== '') {
                $insertData['status'] = $status;
            }

            $result = $db->insert('mangas', $insertData);

            if ($result) {
                $message     = '漫画添加成功';
                $messageType = 'success';
            } else {
                $message     = '添加失败，请重试';
                $messageType = 'danger';
            }
        } else {
            $message     = implode('<br>', $errors);
            $messageType = 'danger';
        }
    }
}

// 获取所有类型
$types = $db->query('SELECT * FROM manga_types ORDER BY sort_order');

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
    #imagePreview img {
        max-width: 300px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-tips {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 0 8px 8px 0;
        font-size: 0.9rem;
        color: #856404;
    }
    .preview-card {
        background: #fff;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    .preview-title {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 15px;
    }
</style>

<div class="content-header">
    <h2>添加漫画</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin88/">控制台</a></li>
            <li class="breadcrumb-item active">添加漫画</li>
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
        <h5 class="mb-0"><i class="bi bi-plus-circle text-primary"></i> 漫画信息</h5>
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
                                <option value="<?php echo (int)$type['id']; ?>"
                                        data-need-cover="<?php echo (int)$type['need_cover']; ?>"
                                        data-need-status="<?php echo (int)$type['need_status']; ?>">
                                    <?php echo htmlspecialchars($type['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">所属标签（板块标签）</label>
                        <select class="form-select" name="tag_id" id="tagSelect">
                            <option value="">选择标签（可选）</option>
                        </select>
                        <small class="text-muted">或
                            <a href="javascript:void(0)" id="createNewTag">创建新标签</a>
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">漫画标题 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required placeholder="输入漫画标题">
                </div>

                <div class="mb-3">
                    <label class="form-label">漫画标签</label>
                    <input type="text" class="form-control" name="manga_tags" placeholder="如：职场、单恋攻、哭包攻、美人受、做炸受">
                    <small class="text-muted">漫画内容标签，多个用中文逗号分隔，显示在详情页</small>
                </div>
            </div>

            <!-- 状态选择（仅韩漫等需要） -->
            <div class="form-section" id="statusSection" style="display:none;">
                <div class="form-section-title"><i class="bi bi-flag"></i> 连载状态</div>

                <div class="mb-3">
                    <label class="form-label">状态</label>
                    <select class="form-select" name="status">
                        <option value="">请选择状态</option>
                        <option value="serializing">连载中</option>
                        <option value="completed">已完结</option>
                    </select>
                </div>
            </div>

            <!-- 封面图片 -->
            <div class="form-section" id="coverSection">
                <div class="form-section-title"><i class="bi bi-image"></i> 封面图片</div>

                <div class="mb-3">
                    <label class="form-label">上传封面（可选）</label>
                    <input type="file" class="form-control" name="cover_image" accept="image/*">
                    <small class="text-muted">支持 JPG、PNG、WEBP 格式，最大 5MB</small>
                </div>

                <div id="imagePreview"></div>
            </div>

            <!-- 资源链接 -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-link-45deg"></i> 资源链接</div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">资源链接</label>
                        <input type="text" class="form-control" name="resource_link" placeholder="输入资源链接URL">
                        <small class="text-muted">如百度网盘、阿里云盘等分享链接</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">提取码</label>
                        <input type="text" class="form-control" name="extract_code" placeholder="如：1234">
                        <small class="text-muted">网盘提取码（可选）</small>
                    </div>
                </div>
            </div>

            <!-- 简介 -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-file-text"></i> 简介</div>

                <div class="mb-3">
                    <label class="form-label">漫画简介（可选）</label>
                    <textarea class="form-control" name="description" rows="4"
                              placeholder="输入漫画简介，将显示在详情页"></textarea>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" name="submit_manga" class="btn btn-primary btn-custom">
                    <i class="bi bi-check-circle"></i> 提交添加
                </button>
                <a href="/admin88/manga/list" class="btn btn-secondary btn-custom">
                    <i class="bi bi-x-circle"></i> 取消
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$customJs = '
<script>
$(document).ready(function() {
    // 类型选择变化
    $("#typeSelect").change(function() {
        var typeId = $(this).val();
        var needStatus = $(this).find(":selected").data("need-status");

        // 显示/隐藏状态选择（连载中/已完结）
        if (needStatus == 1) {
            $("#statusSection").show();
        } else {
            $("#statusSection").hide();
        }

        // 加载该类型的标签
        if (typeId) {
            $.ajax({
                url: "/admin88/api/get-tags.php",
                type: "GET",
                data: { type_id: typeId },
                dataType: "json",
                success: function(tags) {
                    var options = "<option value=\"\">选择标签（可选）</option>";
                    tags.forEach(function(tag) {
                        options += "<option value=\"" + tag.id + "\">" + tag.tag_name + "</option>";
                    });
                    $("#tagSelect").html(options);
                }
            });
        }
    });

    // 图片预览
    $("input[name=\"cover_image\"]").change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#imagePreview").html("<img src=\"" + e.target.result + "\" style=\"max-width: 300px; border-radius: 8px;\">");
            };
            reader.readAsDataURL(file);
        }
    });

    // 创建新标签
    $("#createNewTag").click(function() {
        var typeId = $("#typeSelect").val();
        if (!typeId) {
            alert("请先选择漫画类型");
            return;
        }

        var tagName = prompt("请输入新标签名称");
        if (tagName) {
            var csrfToken = $("input[name=\'csrf_token\']").val();
            $.ajax({
                url: "/admin88/api/create-tag.php",
                type: "POST",
                data: { 
                    type_id: typeId, 
                    tag_name: tagName,
                    csrf_token: csrfToken
                },
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        var option = "<option value=\"" + res.tag_id + "\" selected>" + tagName + "</option>";
                        $("#tagSelect").append(option);
                        alert("标签创建成功");
                    } else {
                        alert(res.message || "创建失败");
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
