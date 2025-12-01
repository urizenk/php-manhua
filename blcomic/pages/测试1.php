<?php
// 漫画数据结构
$comic = [
    'id' => 1,
    'title' => '单相思的经营战略1-10完结',
    'status' => '完结',
    'tags' => ['职场', '单恋攻', '黑包攻', '美人受', '傲娇受'],
    'description' => '智源在第一家公司写几时玩伴的亲哥哥，也是自己的初恋延秀重逢了。认为这是个机会的智源，积极地向延秀表达好感，但两人的关系却毫无进展，让他越来越越急⋯在此期间，两人间发生的事让延秀误会智源是个随性的男人，两人的关系总是错过。但智源毫不在意，向误会自己的延秀靠近。
不擅长爱情的真智源社员，赢得自延秀代理芳心大作战！',
    'resource_links' => [
        [
            'type' => '资源链接',
            'url' => 'https://pan.baidu.com/s/1PrHrsgd4dTbvPqCZMmIXwg'
        ],
        [
            'type' => '提取码',
            'url' => 'https://drive.uc.cn/s/86fdbb2d61c34?public=1'
        ]
    ],
    'banner_image' => 'banner.jpg' // 顶部横幅图片
];

// 状态颜色映射
$statusColors = [
    '完结' => '#ffe4e4',
    '连载' => '#e4f4ff',
    '暂停' => '#fff4e4'
];

// 标签颜色映射
$tagColors = [
    '#ffecd2', '#fff4d2', '#e4ffe4', '#f4e4ff', '#e4e4ff'
];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($comic['title']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Microsoft YaHei', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .banner {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #FFD89B 0%, #FF9A9E 50%, #FF6B6B 100%);
            position: relative;
            overflow: hidden;
        }

        .banner-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(transparent, rgba(0,0,0,0.3));
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            background: white;
            min-height: calc(100vh - 280px);
        }

        .comic-title {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .info-row {
            display: flex;
            margin-bottom: 25px;
            align-items: flex-start;
        }

        .info-label {
            width: 100px;
            font-size: 16px;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            padding-top: 8px;
        }

        .info-label::before {
            content: '≡';
            margin-right: 8px;
            font-size: 20px;
        }

        .info-content {
            flex: 1;
            font-size: 16px;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 20px;
            border-radius: 4px;
            font-size: 15px;
            color: #d63031;
            background-color: <?php echo $statusColors[$comic['status']] ?? '#f0f0f0'; ?>;
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag {
            padding: 6px 16px;
            border-radius: 4px;
            font-size: 14px;
            color: #666;
            cursor: default;
            transition: transform 0.2s;
        }

        .tag:hover {
            transform: translateY(-2px);
        }

        .description {
            line-height: 1.9;
            color: #555;
            font-size: 15px;
        }

        .link-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .link-icon {
            margin-right: 8px;
            color: #95a5a6;
        }

        .link-url {
            color: #3498db;
            text-decoration: none;
            font-size: 15px;
        }

        .link-url:hover {
            text-decoration: underline;
        }

        /* 管理面板样式 */
        .admin-panel {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .admin-btn {
            padding: 8px 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }

        .admin-btn:hover {
            background: #2980b9;
        }

        /* 编辑模态框 */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-title {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .tag-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 40px;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            background: #e0e0e0;
            border-radius: 4px;
            font-size: 13px;
        }

        .tag-remove {
            margin-left: 6px;
            cursor: pointer;
            color: #999;
        }

        .tag-remove:hover {
            color: #d63031;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-primary {
            flex: 1;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            flex: 1;
            padding: 10px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <!-- 顶部横幅 -->
    <div class="banner">
        <div class="banner-content"></div>
    </div>

    <!-- 主要内容 -->
    <div class="container">
        <h1 class="comic-title"><?php echo htmlspecialchars($comic['title']); ?></h1>

        <!-- 名称 -->
        <div class="info-row">
            <div class="info-label">名称</div>
            <div class="info-content">
                <?php echo htmlspecialchars($comic['title']); ?>
            </div>
        </div>

        <!-- 状态 -->
        <div class="info-row">
            <div class="info-label">状态</div>
            <div class="info-content">
                <span class="status-badge" id="statusDisplay">
                    <?php echo htmlspecialchars($comic['status']); ?>
                </span>
            </div>
        </div>

        <!-- 标签 -->
        <div class="info-row">
            <div class="info-label">标签</div>
            <div class="info-content">
                <div class="tags-container" id="tagsDisplay">
                    <?php foreach ($comic['tags'] as $index => $tag): ?>
                        <span class="tag" style="background-color: <?php echo $tagColors[$index % count($tagColors)]; ?>">
                            <?php echo htmlspecialchars($tag); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 简介 -->
        <div class="info-row">
            <div class="info-label">简介</div>
            <div class="info-content">
                <div class="description" id="descriptionDisplay">
                    <?php echo nl2br(htmlspecialchars($comic['description'])); ?>
                </div>
            </div>
        </div>

        <!-- 资源链接 -->
        <?php foreach ($comic['resource_links'] as $link): ?>
        <div class="info-row">
            <div class="info-label">
                <span class="link-icon">🔗</span> <?php echo htmlspecialchars($link['type']); ?>
            </div>
            <div class="info-content">
                <a href="<?php echo htmlspecialchars($link['url']); ?>" class="link-url" target="_blank">
                    <?php echo htmlspecialchars($link['url']); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 管理面板 -->
    <div class="admin-panel">
        <button class="admin-btn" onclick="openStatusModal()">修改状态</button>
        <button class="admin-btn" onclick="openTagsModal()">编辑标签</button>
    </div>

    <!-- 状态编辑模态框 -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <h2 class="modal-title">修改漫画状态</h2>
            <div class="form-group">
                <label>选择状态</label>
                <select id="statusSelect">
                    <option value="完结" <?php echo $comic['status'] == '完结' ? 'selected' : ''; ?>>完结</option>
                    <option value="连载" <?php echo $comic['status'] == '连载' ? 'selected' : ''; ?>>连载</option>
                    <option value="暂停" <?php echo $comic['status'] == '暂停' ? 'selected' : ''; ?>>暂停</option>
                </select>
            </div>
            <div class="btn-group">
                <button class="btn-primary" onclick="saveStatus()">保存</button>
                <button class="btn-secondary" onclick="closeStatusModal()">取消</button>
            </div>
        </div>
    </div>

    <!-- 标签编辑模态框 -->
    <div class="modal" id="tagsModal">
        <div class="modal-content">
            <h2 class="modal-title">编辑漫画标签</h2>
            <div class="form-group">
                <label>当前标签</label>
                <div class="tag-input-container" id="tagInputContainer"></div>
            </div>
            <div class="form-group">
                <label>添加新标签</label>
                <input type="text" id="newTagInput" placeholder="输入标签后按回车添加">
            </div>
            <div class="btn-group">
                <button class="btn-primary" onclick="saveTags()">保存</button>
                <button class="btn-secondary" onclick="closeTagsModal()">取消</button>
            </div>
        </div>
    </div>

    <script>
        // 当前标签数据
        let currentTags = <?php echo json_encode($comic['tags']); ?>;
        const tagColors = <?php echo json_encode($tagColors); ?>;
        const statusColors = <?php echo json_encode($statusColors); ?>;

        // 状态模态框
        function openStatusModal() {
            document.getElementById('statusModal').classList.add('active');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.remove('active');
        }

        function saveStatus() {
            const newStatus = document.getElementById('statusSelect').value;
            const statusDisplay = document.getElementById('statusDisplay');
            statusDisplay.textContent = newStatus;
            statusDisplay.style.backgroundColor = statusColors[newStatus] || '#f0f0f0';
            
            // 这里可以添加AJAX调用来保存到服务器
            alert('状态已更新为: ' + newStatus);
            closeStatusModal();
        }

        // 标签模态框
        function openTagsModal() {
            document.getElementById('tagsModal').classList.add('active');
            renderTagInputs();
        }

        function closeTagsModal() {
            document.getElementById('tagsModal').classList.remove('active');
        }

        function renderTagInputs() {
            const container = document.getElementById('tagInputContainer');
            container.innerHTML = '';
            
            currentTags.forEach((tag, index) => {
                const tagItem = document.createElement('span');
                tagItem.className = 'tag-item';
                tagItem.innerHTML = `
                    ${tag}
                    <span class="tag-remove" onclick="removeTag(${index})">×</span>
                `;
                container.appendChild(tagItem);
            });
        }

        function removeTag(index) {
            currentTags.splice(index, 1);
            renderTagInputs();
        }

        // 添加新标签
        document.addEventListener('DOMContentLoaded', function() {
            const newTagInput = document.getElementById('newTagInput');
            if (newTagInput) {
                newTagInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && this.value.trim()) {
                        currentTags.push(this.value.trim());
                        renderTagInputs();
                        this.value = '';
                    }
                });
            }
        });

        function saveTags() {
            const tagsDisplay = document.getElementById('tagsDisplay');
            tagsDisplay.innerHTML = '';
            
            currentTags.forEach((tag, index) => {
                const tagSpan = document.createElement('span');
                tagSpan.className = 'tag';
                tagSpan.textContent = tag;
                tagSpan.style.backgroundColor = tagColors[index % tagColors.length];
                tagsDisplay.appendChild(tagSpan);
            });
            
            // 这里可以添加AJAX调用来保存到服务器
            alert('标签已更新');
            closeTagsModal();
        }

        // 点击模态框外部关闭
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>