<?php
require 'config.php';

function migrateComicData($pdo, $title, $url, $categoryId, $status = '完结', $coverImage = null) {
    try {
        // 检查是否已存在
        $stmt = $pdo->prepare("SELECT id FROM comics WHERE title = ?");
        $stmt->execute([$title]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "漫画已存在: $title\n";
            return $existing['id'];
        }
        
        // 插入漫画数据
        $stmt = $pdo->prepare("
            INSERT INTO comics (title, status, category_id, cover_image, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $status, $categoryId, $coverImage]);
        $comicId = $pdo->lastInsertId();
        
        // 插入资源链接
        $platform = getPlatformFromUrl($url);
        $type = getTypeFromPlatform($platform);
        
        $stmt = $pdo->prepare("
            INSERT INTO comic_resources (comic_id, type, platform, url, sort_order) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$comicId, $type, $platform, $url]);
        
        echo "导入成功: $title (ID: $comicId)\n";
        return $comicId;
        
    } catch (PDOException $e) {
        echo "导入失败: $title - " . $e->getMessage() . "\n";
        return false;
    }
}

function getPlatformFromUrl($url) {
    if (strpos($url, 'baidu.com') !== false) return '百度网盘';
    if (strpos($url, 'flowus.cn') !== false) return 'Flowus';
    if (strpos($url, 'lanzov.com') !== false) return '蓝奏云';
    if (strpos($url, 'xunlei.com') !== false) return '迅雷云盘';
    if (strpos($url, 'quark.cn') !== false) return '夸克网盘';
    return '其他平台';
}

function getTypeFromPlatform($platform) {
    $types = [
        '百度网盘' => '网盘资源',
        '蓝奏云' => '网盘资源',
        '迅雷云盘' => '网盘资源',
        '夸克网盘' => '网盘资源',
        'Flowus' => '在线文档'
    ];
    return $types[$platform] ?? '其他资源';
}

// 示例：迁移完结短漫数据
$finishedComics = [
    ['ACT OUT1-4完结', 'https://pan.baidu.com/s/1D7xjWb12fXr_-aZg7AgxFw?pwd=bleh', 1, '完结'],
    ['爱恨缠绵1-10完结', 'https://flowus.cn/seacomic2/share/a76759bf-5adc-454b-854b-a2a4fd0659bf?code=KEKW5D&embed=true', 1, '完结'],
    ['爱情滴答滴 1-6完结', 'https://flowus.cn/seacomic2/share/3adfb090-aa31-4f44-b6fa-dc98056ffe0d?code=KEKW5D&embed=true', 1, '完结'],
    // 添加更多漫画数据...
];

// 示例：迁移韩漫数据
$koreanComics = [
    ['黯缘 台无光', 'https://jjnztxsb.lanzov.com/b00g322ufa', 2, '连载中'],
    ['ANAN', 'https://jjnztxsb.lanzov.com/b04edv2je', 2, '连载中'],
    ['爱情是幻想/恋爱幻想曲', 'https://jjnztxsb.lanzov.com/b04ef6x9c', 2, '连载中'],
    // 添加更多漫画数据...
];

echo "开始迁移完结短漫数据...\n";
foreach ($finishedComics as $comic) {
    migrateComicData($pdo, $comic[0], $comic[1], $comic[2], $comic[3]);
}

echo "\n开始迁移韩漫数据...\n";
foreach ($koreanComics as $comic) {
    migrateComicData($pdo, $comic[0], $comic[1], $comic[2], $comic[3]);
}

echo "\n数据迁移完成！\n";
?>