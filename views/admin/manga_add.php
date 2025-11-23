<?php
/**
 * A1-添加漫画模块
 */
$pageTitle = '添加漫画';

// 处理表单提交
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_manga'])) {
    $typeId = $_POST['type_id'] ?? 0;
    $tagId = $_POST['tag_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $status = $_POST['status'] ?? null;
    $resourceLink = trim($_POST['resource_link'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $errors = [];
    
    if (!$typeId) $errors[] = '请选择漫画类型';
    if (!$title) $errors[] = '请输入漫画标题';
    
    // 处理封面图片上传
    $coverImage = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload = new \App\Core\Upload($config['upload']);
        $uploadResult = $upload->uploadSingle($_FILES['cover_image'], 'covers');
        
        if ($uploadResult) {
            $coverImage = $uploadResult['path'];
        } else {
            $errors[] = '图片上传失败：' . $upload->getError();
        }
    }
    
    if (empty($errors)) {
        $insertData = [
            'type_id' => $typeId,
            'title' => $title,
            'resource_link' => $resourceLink,
            'description' => $description,
        ];
        
        if ($tagId) $insertData['tag_id'] = $tagId;
        if ($coverImage) $insertData['cover_image'] = $coverImage;
        if ($status) $insertData['status'] = $status;
        
        $result = $db->insert('mangas', $insertData);
        
        if ($result) {
            $message = '漫画添加成功！';
            $messageType = 'success';
        } else {
            $message = '添加失败，请重试';
            $messageType = 'danger';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}

// 获取所有类型
$types = $db->query("SELECT * FROM manga_types ORDER BY sort_order");

include APP_PATH . '/views/admin/layout_header.php';
?>

<style>
    .form-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .form-section-title {
        font-weight: bold;
        margin-bottom: 15px;
        color: #495057;
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
        <h5 class="mb-0">漫画信息</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="mangaForm">
            <!-- 基本信息 -->
            <div class="form-section">
                <div class="form-section-title">基本信息</div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">漫画类型 <span class="text-danger">*</span></label>
                        <select class="form-select" name="type_id" id="typeSelect" required>
                            <option value="">请选择类型</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        data-need-cover="<?php echo $type['need_cover']; ?>"
                                        data-need-status="<?php echo $type['need_status']; ?>">
                                    <?php echo htmlspecialchars($type['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">所属标签</label>
                        <select class="form-select" name="tag_id" id="tagSelect">
                            <option value="">选择标签（可选）</option>
                        </select>
                        <small class="text-muted">或 <a href="javascript:void(0)" id="createNewTag">创建新标签</a></small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">漫画标题 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required placeholder="输入漫画标题">
                </div>
            </div>
            
            <!-- 状态选择（仅韩漫合集等需要） -->
            <div class="form-section" id="statusSection" style="display:none;">
                <div class="form-section-title">连载状态</div>
                
                <div class="mb-3">
                    <label class="form-label">状态</label>
                    <select class="form-select" name="status">
                        <option value="">请选择状态</option>
                        <option value="serializing">连载中</option>
                        <option value="completed">已完结</option>
                    </select>
                </div>
            </div>
            
            <!-- 封面图片（仅韩漫/日漫等需要） -->
            <div class="form-section" id="coverSection" style="display:none;">
                <div class="form-section-title">封面图片</div>
                
                <div class="mb-3">
                    <label class="form-label">上传封面</label>
                    <input type="file" class="form-control" name="cover_image" accept="image/*">
                    <small class="text-muted">支持JPG、PNG、WEBP格式，最大5MB</small>
                </div>
                
                <div id="imagePreview"></div>
            </div>
            
            <!-- 资源链接 -->
            <div class="form-section">
                <div class="form-section-title">资源链接</div>
                
                <div class="mb-3">
                    <label class="form-label">资源链接</label>
                    <textarea class="form-control" name="resource_link" rows="4" 
                              placeholder="输入资源链接&#10;多个链接请换行输入"></textarea>
                    <small class="text-muted">如百度网盘、阿里云盘等分享链接</small>
                </div>
            </div>
            
            <!-- 简介 -->
            <div class="form-section">
                <div class="form-section-title">简介</div>
                
                <div class="mb-3">
                    <label class="form-label">漫画简介（可选）</label>
                    <textarea class="form-control" name="description" rows="3" 
                              placeholder="输入漫画简介"></textarea>
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
    // 类型选择变化时
    $("#typeSelect").change(function() {
        var typeId = $(this).val();
        var needCover = $(this).find(":selected").data("need-cover");
        var needStatus = $(this).find(":selected").data("need-status");
        
        // 显示/隐藏封面上传
        if (needCover == 1) {
            $("#coverSection").show();
        } else {
            $("#coverSection").hide();
        }
        
        // 显示/隐藏状态选择
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
        
        var tagName = prompt("请输入新标签名称：");
        if (tagName) {
            $.ajax({
                url: "/admin88/api/create-tag.php",
                type: "POST",
                data: { type_id: typeId, tag_name: tagName },
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


