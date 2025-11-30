<?php
/**
 * 通用模块列表页
 * 用于新增加的漫画类型，按列表形式展示该类型下的所有漫画
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$moduleCode = $GLOBALS['moduleCode'] ?? '';

if (!$db || !$moduleCode) {
    echo '模块配置错误';
    exit;
}

// 查找类型信息
$type = $db->queryOne(
    'SELECT * FROM manga_types WHERE type_code = ?',
    [$moduleCode]
);

if (!$type) {
    echo '模块不存在';
    exit;
}

// 获取该类型下所有漫画
$mangas = $db->query(
    "SELECT m.*, t.tag_name 
     FROM mangas m
     LEFT JOIN tags t ON m.tag_id = t.id
     WHERE m.type_id = ?
     ORDER BY m.sort_order DESC, m.created_at DESC",
    [$type['id']]
);

$pageTitle = htmlspecialchars($type['type_name']) . ' - 海の小窝';

$customCss = '
<style>
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .page-header {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        text-align: center;
    }
    .page-title {
        font-size: 2.2rem;
        font-weight: bold;
        color: #1976D2;
        margin-bottom: 10px;
    }
    .page-subtitle {
        color: #666;
        font-size: 1rem;
    }
    .manga-section {
        background: #ffffff;
        border-radius: 15px;
        padding: 25px;
    }
    .manga-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .manga-item {
        padding: 15px 10px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.25s ease;
    }
    .manga-item:last-child {
        border-bottom: none;
    }
    .manga-item:hover {
        background: #f8f9ff;
        padding-left: 20px;
    }
    .manga-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        color: #333;
        font-size: 1rem;
    }
    .manga-link:hover {
        color: #1976D2;
    }
    .manga-title {
        flex: 1;
    }
    .manga-meta {
        margin-left: 15px;
        font-size: 0.85rem;
        color: #999;
        white-space: nowrap;
    }
    .back-btn {
        background: #ffffff;
        color: #1976D2;
        border: 2px solid #1976D2;
        border-radius: 25px;
        padding: 10px 30px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        margin-top: 25px;
    }
    .back-btn:hover {
        background: #1976D2;
        color: #ffffff;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 15px;
    }
    @media (max-width: 768px) {
        .content-wrapper {
            padding: 20px 12px;
        }
        .page-title {
            font-size: 1.6rem;
        }
    }
</style>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1 class="page-title"><?php echo htmlspecialchars($type['type_name']); ?></h1>
        <p class="page-subtitle">当前模块下的漫画资源列表</p>
    </div>

    <div class="manga-section">
        <?php if (empty($mangas)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>该模块下暂时还没有添加漫画资源。</p>
            </div>
        <?php else: ?>
            <ul class="manga-list">
                <?php foreach ($mangas as $manga): ?>
                    <li class="manga-item">
                        <?php if (!empty($manga['resource_link'])): ?>
                            <a href="<?php echo htmlspecialchars($manga['resource_link']); ?>"
                               target="_blank"
                               class="manga-link">
                                <span class="manga-title">
                                    <?php echo htmlspecialchars($manga['title']); ?>
                                </span>
                                <span class="manga-meta">
                                    <?php echo htmlspecialchars($manga['tag_name'] ?? ''); ?>
                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </span>
                            </a>
                        <?php else: ?>
                            <a href="/detail/<?php echo (int)$manga['id']; ?>" class="manga-link">
                                <span class="manga-title">
                                    <?php echo htmlspecialchars($manga['title']); ?>
                                </span>
                                <span class="manga-meta">
                                    <?php echo htmlspecialchars($manga['tag_name'] ?? ''); ?>
                                    <i class="bi bi-chevron-right ms-1"></i>
                                </span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="text-center">
        <a href="/" class="back-btn">
            <i class="bi bi-arrow-left"></i> 返回首页
        </a>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>

