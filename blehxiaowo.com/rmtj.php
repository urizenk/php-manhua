<?php
session_start();
require 'config.php';
require 'functions.php';

// 基础会话验证
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: index.php');
    exit;
}

// 密码版本验证（保持原有代码不变）
try {
    $latest_password_id = null;
    if ($redis && $redis->exists('latest_password_id')) {
        $latest_password_id = $redis->get('latest_password_id');
    } else {
        $latest_password_id = getLatestPasswordIdFromDB($pdo);
        if ($redis) {
            $redis->setex('latest_password_id', 18000, $latest_password_id);
        }
    }

    if ($_SESSION['password_version'] != $latest_password_id) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("密码版本验证失败: " . $e->getMessage());
}

// 示例数据（需放在搜索处理前）
$articles = [
    [
        'url' => 'https://flowus.cn/seacomic2/share/c08b86f0-9b28-47a6-aa00-7ee2d9aa04a7?code=KEKW5D&embed=true',
        'image' => 'images/幼驯染57.png',
        'title' => '被青梅竹马调教',
        'episodes' => '上篇＋下篇',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://flowus.cn/seacomic2/share/96d3480c-3f48-4d90-a365-70055e616e97?code=KEKW5D&embed=true',
        'image' => 'images/果报56.png',
        'title' => '静待因果报应',
        'episodes' => '【105P】完结',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://flowus.cn/seacomic2/share/b955ef78-0d24-4e05-b953-168b41830460?code=KEKW5D&embed=true',
        'image' => 'images/年兽55.webp',
        'title' => '草本年兽的机智生活 无光版',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://flowus.cn/seacomic2/share/7aee5b36-f360-425b-9a3c-d0a20e23b77d?code=KEKW5D&embed=true',
        'image' => 'images/金银54.webp',
        'title' => '金银细语的秘之夜',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://flowus.cn/seacomic2/share/c8dbec0b-c91b-4cc8-8cdb-c3a6db7ba130?code=KEKW5D&embed=true',
        'image' => 'images/探索者53.webp',
        'title' => '探索者系列',
        'episodes' => '1-14卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://flowus.cn/seacomic2/share/f743798a-5c39-4ea7-ad0f-404b44354121?code=KEKW5D&embed=true',
        'image' => 'images/双子鬼51.webp',
        'title' => '双子鬼爱慕的守护神',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://flowus.cn/seacomic2/share/88d30654-6d45-45cd-9bf6-39f57397430a?code=KEKW5D&embed=true',
        'image' => 'images/古高52.webp',
        'title' => '请让我抱您古高主任',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://flowus.cn/seacomic2/share/52262234-2058-454d-a0b9-2349cb5aa073?code=KEKW5D&embed=true',
        'image' => 'images/春水50.webp',
        'title' => '春之水',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/19FREHt3ucmdJB9iNjgxayA?pwd=bleh',
        'image' => 'images/紧咬48.webp',
        'title' => '地狱犬对神官紧咬不放',
        'episodes' => '第1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1FIdmvTNdJ1iEcgNjpR98BA?pwd=bleh',
        'image' => 'images/光夏49.webp',
        'title' => '光逝去的夏天',
        'episodes' => '更至5卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1_kQYiVUEHj6EH_-kVclLeQ?pwd=bleh',
        'image' => 'images/芭蕾47.webp',
        'title' => '放学后的芭蕾练习曲',
        'episodes' => '全3卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1bcLRJVqOGCRQnOBOLa4oxg?pwd=bleh ',
        'image' => 'images/爱恋46.webp',
        'title' => '爱恋与性欲',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/10haSpd4zY1HL4dblF31Fzg?pwd=bleh',
        'image' => 'images/伊古纳多27.webp',
        'title' => '伊谷纳多的新娘',
        'episodes' => '第1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://caiyun.139.com/w/i/2nQR78LCFyg1s', // 根据关键词“很可爱”更新
        'image' => 'images/反抗45.webp',
        'title' => '反抗的你真可爱', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1upAzSUzOM7IJUvBxiZ32dg?pwd=bleh', // 根据关键词“很可爱”更新
        'image' => 'images/牛奶44.webp',
        'title' => '恋爱牛奶王冠', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1jn8KhAdQPvUfG_m-3Q3SnQ?pwd=bleh ', // 根据关键词“很可爱”更新
        'image' => 'images/40岁43.webp',
        'title' => '40岁以前想达成的10件事', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/13bgGdWC-XvCAECBKZGkVxw?pwd=bleh', // 根据关键词“很可爱”更新
        'image' => 'images/养成42.webp',
        'title' => 'SAME OLD STORY ', 
        'episodes' => '全2册',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/13qjm_vOCkJXNui4cvEfrIg?pwd=bleh', // 根据关键词“很可爱”更新
        'image' => 'images/不可爱.webp',
        'title' => '是很可愛啦但不可愛', 
        'episodes' => '全9話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1anB_31flmhwWzMu3u4ujvw?pwd=bleh',
        'image' => 'images/冰雨41.webp',
        'title' => '冰雨降临之时结下恋之契约',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1hvOjMsr1lfai-L7Arn3vXw?pwd=bleh',
        'image' => 'images/代餐40.webp',
        'title' => '爸爸是性欲代餐',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/18Fl5cJnIulYti4HeUXzhbw?pwd=bleh',
        'image' => 'images/唇红39.webp',
        'title' => '处子唇红',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/117R6R3zCpzXpHMIQJRnxZg?pwd=bleh',
        'image' => 'images/深见2.jpg',
        'title' => '格外性感的深见',
        'episodes' => '1-18话（连载中）',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1yW8fQ67gr5Huwnk0IIHsGA?pwd=bleh',
        'image' => 'images/年龄差38.webp',
        'title' => '年龄差夫夫',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签[38]
    ],
    [
        'url' => 'https://caiyun.139.com/w/i/2prAKzdPcsy9p',
        'image' => 'images/舞台36.webp',
        'title' => '舞台下的单向爱恋',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签[36]
    ],
    [
        'url' => 'https://pan.baidu.com/s/1r_8EEeMS-gMt33ZEi1BRQQ?pwd=bleh',
        'image' => 'images/呼吸35.webp',
        'title' => '连命运都屏住呼吸',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://uwn61ldr2m3.feishu.cn/docx/TOD8dovAyot0yRx0yTfcfLornkc?from=from_copylink',
        'image' => 'images/长滨34.webp',
        'title' => '长滨To Be,or Not To Be',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.xunlei.com/s/VOTWCc1G0nQXrRs-11-wscF5A1?pwd=rk98#', // 根据关键词“甜美的”更新
        'image' => 'images/警察.webp',
        'title' => '发出甜美的娇喘吧，警察先生~', 
        'episodes' => '全7話',
        'member' => false
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOPt7s_PMRCHy2np9w3ZsvscA1?pwd=jziq#', // 根据关键词“攻或受”更新
        'image' => 'images/攻或受.webp',
        'title' => '攻或受你想當哪個？',
        'episodes' => '全3話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1Oc0Wg_2WzdRoNqDQQ33Bjg?pwd=bleh',
        'image' => 'images/白昼33.webp',
        'title' => '白昼限定甜蜜赏味期',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOPpv7aVKjtMjvAjm26w8_6rA1?pwd=cjd8#',
        'image' => 'images/脱掉.webp',
        'title' => '这么喜欢就亲自帮我脱掉',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1X-p68v_6TgVc-O0KfB3R2A?pwd=bleh', // 根据关键词“琥珀”更新
        'image' => 'images/爱情32.webp',
        'title' => '还算不上是爱情', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOOxJzW_w9uE1nBZ2V95E9CXA1?pwd=zuaf#', // 根据关键词“琥珀”更新
        'image' => 'images/爱我31.webp',
        'title' => '别说什么你爱我', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1TK7weP1lvpvyJiOaorlpyw?pwd=bleh', // 根据关键词“琥珀”更新
        'image' => 'images/星期五30.webp',
        'title' => '星期五的年下男友', 
        'episodes' => '全1卷',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1FHO2FJlzyf56Yf4SGyUQHg?pwd=bleh', // 根据关键词“琥珀”更新
        'image' => 'images/琥珀2.webp',
        'title' => '他的小琥珀His little amber', 
        'episodes' => '全11話',
        'member' => false
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/1JQPbGdZguJeDWVwsKlu-Tg?pwd=bleh',
        'image' => 'images/衣下30.webp',
        'title' => '衣下秘密不可告人',
        'episodes' => '全3话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => ' https://pan.baidu.com/s/1nw9Jp69aaOKv9cTuIEyl9A?pwd=bleh',
        'image' => 'images/近夜29.webp',
        'title' => '近夜黄昏的Sugar cat',
        'episodes' => '全5话',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1BxbyYi937SW5fXLvNvXHPA?pwd=bleh',
        'image' => 'images/治愈28.webp',
        'title' => '治愈悖论',
        'episodes' => '更至2卷',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.baidu.com/s/1qLc-IBxDqRgm6TYt1iNcSQ?pwd=bleh',
        'image' => 'images/count26.webp',
        'title' => '10 count',
        'episodes' => '全6卷',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.baidu.com/s/1mQYY6ZVd-Dia65GCid5USw?pwd=bleh ',
        'image' => 'images/青蓝25.webp',
        'title' => '于青蓝之夜唇齿交融',
        'episodes' => '更至01（连载中）',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1FkDhX0a98DqEtQcjW9wRJA?pwd=bleh  ',
        'image' => 'images/菁英24.webp',
        'title' => '菁英医生不可告人的烦恼',
        'episodes' => '全2卷',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://caiyun.139.com/w/i/2prAJYb7xdL29',
        'image' => 'images/怀孕23.webp',
        'title' => '不要让我怀孕',
        'episodes' => '全1卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1T19Vz7zyLy-27eDXvxVg4g?pwd=bleh',
        'image' => 'images/ppatta.webp',
        'title' => 'ppatta大合集',
        'episodes' => '全40篇',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1DhpuADcBeLqLjaX_UWZTjg?pwd=bleh ',
        'image' => 'images/离不开.webp',
        'title' => '离不开,也逃不了',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],

    [
        'url' => 'https://pan.baidu.com/s/1d4VyQHWLY7JlqISFKGJQJQ?pwd=bleh',
        'image' => 'images/无节操全.webp',
        'title' => '无节操☆Bitch社',
        'episodes' => '全5卷（更新中）',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/13uFrbiVEopsnjyoaKe5Oqw?pwd=bleh',
        'image' => 'images/维克多.webp',
        'title' => '我美丽的维克多',
        'episodes' => '全6话',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/1H-b7eu-sU29pc9LoKvU4ZQ?pwd=bleh',
        'image' => 'images/无节操2.webp',
        'title' => '无节操☆Bitch社 02',
        'episodes' => '第二卷',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1AmZMPFcxAibVZ4mxCKqLsg?pwd=bleh',
        'image' => 'images/无节操.webp',
        'title' => '无节操☆Bitch社 01',
        'episodes' => '第一卷',
        'member' => false // 是否显示会员标签
    ],
    
    [
        'url' => 'https://pan.baidu.com/s/1xCE8P3Wb1NXw8525H5WfnQ?pwd=bleh',
        'image' => 'images/极道.webp',
        'title' => 'Gotcha！距离感为零的极道的乳头今天也在诱惑着我～',
        'episodes' => '全6話',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.baidu.com/s/1jlNz1CZucsJm2EMbwUHJww?pwd=bleh',
        'image' => 'images/同床守则.webp',
        'title' => '同床守则',
        'episodes' => '全6話',
        'member' => false // 是否显示会员标签
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOTWAvOkibvrruF5OOpsuuPcA1?pwd=3h92#', // 根据关键词“山神”更新
        'image' => 'images/山神.webp',
        'title' => '山神大人与他的宠儿', 
        'episodes' => '全5話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/15EWe9Hw-HT8TO1OYpzBw5Q?pwd=bleh', // 根据关键词“共鸣恋情”更新
        'image' => 'images/新娘 .webp',
        'title' => 'α的新娘 共鸣恋情', 
        'episodes' => '全4卷',
        'member' => false
    ],


    [
        'url' => 'https://pan.baidu.com/s/1kg0yEsk72Q6SWSFOVRo8CA?pwd=bleh ', // 根据关键词“祭品”更新
        'image' => 'images/祭品.webp',
        'title' => '被退货的祭品',
        'episodes' => '全6話',
        'member' => false
    ],
    [
        'url' => 'https://caiyun.139.com/w/i/2prAL5g3tfqdu', // 根据关键词“人鱼”更新
        'image' => 'images/人鱼.webp',
        'title' => '捡到的人鱼想和我交尾',
        'episodes' => '全2話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1BqxpSgku-hy2w7MIH9B5fA?pwd=bleh', // 根据关键词“双子”更新
        'image' => 'images/双子.webp',
        'title' => '双子与老师',
        'episodes' => '全6話',
        'member' => false
    ],
    [
        'url' => 'https://pan.baidu.com/s/1ejNQccjjMTpKF-9490VuvQ?pwd=bleh', // 根据关键词“办公室”更新
        'image' => 'images/猎豹.webp',
        'title' => '办公室的猎豹', 
        'episodes' => '全6話',
        'member' => false
    ],
    [
        'url' => 'https://pan.xunlei.com/s/VOTW9kSPDGpgS-MAQcPQ1CnnA1?pwd=7jrn#', // 根据关键词“会长”更新
        'image' => 'images/会长.webp',
        'title' => '会长，别再装乖孩子了', 
        'episodes' => '全6話',
        'member' => false
    ],

    [
        'url' => 'https://pan.baidu.com/s/1zkJpMMeQhn4hJ-3YxBZMpg?pwd=bleh', // 根据关键词“狼先生”更新
        'image' => 'images/性癖.webp',
        'title' => '性癖是脾气不好的狼先生',
        'episodes' => '全7话',
        'member' => false
    ],
];

// 处理搜索逻辑 - 服务器端优化
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
// 如果关键词为空且URL中存在keyword参数，则重定向至无参数版本
if($keyword === '' && isset($_GET['keyword'])) {
    $redirectUrl = 'deep.php';
    if(isset($_GET['page'])) {
        $redirectUrl .= '?page=' . intval($_GET['page']);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

if($keyword) {
    $filteredArticles = [];
    foreach($articles as $article) {
        if(stripos($article['title'], $keyword) !== false || 
           stripos($article['episodes'], $keyword) !== false) {
            $filteredArticles[] = $article;
        }
    }
    $articles = $filteredArticles;
}

// 分页设置（必须在搜索处理后）
$perPage = 18; // 改为12个每页
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalItems = count($articles);
$totalPages = ceil($totalItems / $perPage);
$currentItems = array_slice($articles, ($page-1)*$perPage, $perPage);
?>

<!DOCTYPE HTML>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日漫推荐</title>
    <!-- 引入 FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- 样式部分已省略 -->
        <style>
        /* 基础重置 */
        * {
            margin: 0; /* 移除所有元素的外边距 */
            padding: 0; /* 移除所有元素的内边距 */
            box-sizing: border-box; /* 设置盒模型为 border-box，使 padding 和 border 包含在元素宽度内 */
        }
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif; /* 设置全局字体为 Arial 或 sans-serif 备用 */
            background-color: #FFFFF0; /* 设置页面背景颜色为浅黄色 */
            margin: 0; /* 移除 body 的外边距 */
            padding: 5px; /* 设置 body 的内边距为 5px，使内容与页面边缘有一定距离 */
            color: #333; /* 设置全局文字颜色为深灰色 */
        }

        /* 容器样式 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px; /* 两端留白 */
        }
        
        /* Tip 标签样式 */
        .tip {
            background-color: #FFE4B5; /* 设置背景颜色为浅橙色 */
            padding: 8px 12px; /* 设置内边距为上下 8px，左右 12px */
            border-radius: 25px; /* 设置圆角为 25px，使标签呈现圆角矩形 */
            margin-bottom: 20px; /* 设置下方外边距为 20px，与下方内容保持距离 */
            font-size: 14px; /* 设置字体大小为 14px */
            color: #333; /* 设置文字颜色为深灰色 */
            display: block; /* 设置为块级元素，独占一行 */
            width: fit-content; /* 宽度根据内容自适应，避免撑满容器 */
        }
        
        /* 回到目录按钮样式 */
        .back-to-index {
            display: block; /* 设置为块级元素，独占一行 */
            background-color: #EC5800; /* 设置背景颜色为橙色 */
            color: white; /* 设置文字颜色为白色 */
            padding: 10px 15px; /* 设置内边距为上下 10px，左右 15px */
            border-radius: 5px; /* 设置圆角为 5px，使按钮呈现圆角矩形 */
            text-decoration: none; /* 移除链接的下划线 */
            font-size: 16px; /* 设置字体大小为 16px */
            font-weight: bold; /* 设置字体加粗 */
            transition: background-color 0.3s ease; /* 设置背景颜色变化的过渡效果 */
            margin-bottom: 20px; /* 设置下方外边距为 20px，与下方内容保持距离 */
            width: fit-content; /* 宽度根据内容自适应，避免撑满容器 */
        }
        
        .back-to-index:hover {
            background-color: #e69500; /* 设置悬停时背景颜色为深橙色 */
        }
        /* 网格布局系统 */
        .excerpts {
            display: grid; /* 使用网格布局 */
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* 设置列宽为最小 200px，最大 1fr（等分剩余空间） */
            gap: 15px; /* 设置网格项之间的间距为 15px */
            padding: 10px 0; /* 设置上下内边距为 10px，左右内边距为 0 */
        }
      

                /* 搜索框和按钮容器样式 */
        .search-container {
            display: flex; /* 使用 Flex 布局，使子元素水平排列 */
            align-items: center; /* 垂直居中对齐子元素 */
            gap: 10px; /* 设置子元素之间的间距为 10px */
            margin-bottom: 20px; /* 设置容器下方的外边距为 20px */
        }
        
        /* 搜索框样式 */
        #searchInput {
            flex: 1; /* 搜索框占据容器剩余的全部空间 */
            padding: 10px 15px; /* 设置内边距为上下 10px，左右 15px */
            border: 2px solid #FFA500; /* 设置边框为 2px 宽的橙色实线 */
            border-radius: 25px; /* 设置圆角为 25px，使搜索框呈现圆角矩形 */
            font-size: 16px; /* 设置字体大小为 16px */
            outline: none; /* 移除输入框聚焦时的默认轮廓线 */
            transition: all 0.3s ease; /* 设置所有属性的过渡效果，持续 0.3 秒 */
        }
        
        /* 搜索框聚焦状态样式 */
        #searchInput:focus {
            box-shadow: 0 0 8px rgba(255,165,0,0.3); /* 聚焦时添加橙色阴影效果 */
        }
        
        /* 查看按钮样式 */
        #searchBtn {
            padding: 10px 20px; /* 设置内边距为上下 10px，左右 20px */
            background-color: #FFA500; /* 设置背景颜色为橙色 */
            color: white; /* 设置文字颜色为白色 */
            border: none; /* 移除边框 */
            border-radius: 25px; /* 设置圆角为 25px，使按钮呈现圆角矩形 */
            font-size: 16px; /* 设置字体大小为 16px */
            cursor: pointer; /* 设置鼠标悬停时显示手型指针 */
            transition: background-color 0.3s ease; /* 设置背景颜色的过渡效果，持续 0.3 秒 */
        }
        
        /* 查看按钮悬停状态样式 */
        #searchBtn:hover {
            background-color: #e69500; /* 悬停时背景颜色变为深橙色 */
        }

        /* 移动端3列布局 */
        @media (max-width: 768px) {
            .excerpts {
                grid-template-columns: repeat(3, 1fr); /* 在移动端强制显示为 3 列 */
                gap: 10px; /* 设置网格项之间的间距为 10px */
            }
        }
        /* 图片容器 */
        .item {
            background: transparent; /* 设置背景为透明 */
            position: relative; /* 设置相对定位，用于内部元素的定位 */
            overflow: hidden; /* 隐藏超出容器的内容 */
        }

        /* 缩略图样式 */
        .thumbnail {
            display: block; /* 设置为块级元素 */
            position: relative; /* 设置相对定位 */
            padding-top: 140%; /* 设置顶部内边距为 140%，保持图片的宽高比 */
            border-radius: 12px; /* 设置圆角为 12px，使图片呈现圆角 */
            overflow: hidden; /* 隐藏超出容器的内容 */
        }
        
        .thumb {
            position: absolute; /* 设置绝对定位，相对于父容器定位 */
            top: 0; /* 距离顶部 0 */
            left: 0; /* 距离左侧 0 */
            width: 100%; /* 宽度占满父容器 */
            height: 100%; /* 高度占满父容器 */
            object-fit: cover; /* 保持图片比例，覆盖整个容器 */
            transition: transform 0.3s ease; /* 设置图片缩放过渡效果 */
            border-radius: 8px; /* 设置图片圆角为 8px */
        }
        h1 {
            color: #1B1212d0; /* 深灰色字体 */
            font-size: 32px;
            font-weight: bold;
            padding: 10px 0; /* 上下内边距 */
            display: block; /* 独占一行 */
            margin-bottom: 20px; /* 标题与内容之间的间距 */
        }

        /* 漫名样式 */
        h3 {
            margin: 12px 0;
            text-align: center;
        }

        h3 a {
            color: #28282B !important; /* 浅灰色 */
            font-weight: 600; /* 加粗 */
            text-decoration: none;
            font-size: 14px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* 限制两行 */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* 话数样式 */
        footer {
            text-align: center;
            padding: 2px 0; /* 减少间距 */
            margin-top: -10px; /* 上移 */
            color: #999;
            font-size: 13px;
            
        }

        /* 会员标签 */
        .label {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 72, 87, 0.9); /* 半透明效果 */
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 15px;
            backdrop-filter: blur(2px); /* 毛玻璃效果 */
        }

        /* 悬停效果 */
        .thumbnail:hover .thumb {
            transform: scale(1.08);
        }
        /* 新增分页样式 */
        /* 分页容器样式 */
        .pagination {
            /* 使用弹性布局(Flexbox) */
            display: flex;
            /* 水平居中对齐子元素 */
            justify-content: center;
            /* 上下边距50px，左右边距0 */
            margin: 60px 0;
            /* 允许子元素换行 */
            flex-wrap: wrap;
            /* 子元素之间的间隙为8px */
            gap: 10px;
        }
        
        /* 分页链接和页码数字的共同样式 */
        .pagination a, 
        .pagination span {
            /* 文字颜色为深灰色(#333) */
            color: #333;
            /* 内边距：上下8px，左右12px */
            padding: 10px 16px;
            /* 去除下划线等文本装饰 */
            text-decoration: none;
            /* 1px宽的浅灰色边框 */
            border: 1px solid #ddd;
            /* 4px圆角边框 */
            border-radius: 4px;
            /* 所有属性变化时添加0.3秒过渡效果 */
            transition: all 0.3s;
            /* 字体大小18px */
            font-size: 22px;
            /* 最小宽度32px */
            min-width: 40px;
            /* 文字居中对齐 */
            text-align: center;
        }
        
        /* 分页链接的悬停状态 */
        .pagination a:hover {
            /* 背景色变为橙色 */
            background-color: #FFA500;
            /* 文字颜色变为白色 */
            color: white;
            /* 边框颜色也变为橙色 */
            border-color: #FFA500;
        }
        
        /* 当前页码的样式 */
        .pagination .current {
            /* 背景色为橙色 */
            background-color: #FFA500;
            /* 文字颜色为白色 */
            color: white;
            /* 边框颜色为橙色 */
            border-color: #FFA500;
            /* 文字加粗 */
            font-weight: bold;
        }
        
        /* 禁用状态的分页元素(如不可点击的"上一页") */
        .pagination .disabled {
            /* 文字颜色为浅灰色 */
            color: #ddd;
            /* 禁用鼠标事件 */
            pointer-events: none;
            /* 边框颜色为浅灰色 */
            border-color: #ddd;
        }
        
        /* 移动端适配(屏幕宽度小于480px时生效) */
        @media (max-width: 480px) {
            /* 分页容器在移动端的调整 */
            .pagination {
                /* 缩小元素间隙为3px */
                gap: 3px;
            }
            
            /* 分页链接和页码数字在移动端的调整 */
            .pagination a, 
            .pagination span {
                /* 减小内边距 */
                padding: 6px 12px;
                /* 减小字体大小 */
                font-size: 16px;
                /* 减小最小宽度 */
                min-width: 36px;
            }
        }
    </style>
</head>
<body>
    <h1>日漫推荐</h1>
    <div class="tip">
        Tip ：①蓝奏云链接打不开的把链接lanzoum改成lanzov ②提取码都是:bleh
    </div>

    <a href="index.php" class="back-to-index">
        <i class="fas fa-arrow-left"></i>
        回到目录
    </a>

    <!-- 搜索表单 - 客户端优化 -->
    <div class="search-container">
        <form method="get" style="display: contents;" id="searchForm">
            <input type="text" 
                   id="searchInput" 
                   name="keyword"
                   placeholder="漫名不用打全称，用关键词搜索..."
                   value="<?= htmlspecialchars($keyword) ?>"
                   autocomplete="off">
            <button type="submit" id="searchBtn">搜索</button>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <section class="container excerpts-wrap">
        <?php if(!empty($currentItems)): ?>
        <div class="excerpts">
            <?php foreach ($currentItems as $article): ?>
            <div class="item">
                <a href="<?= htmlspecialchars($article['url']) ?>" class="thumbnail" target="_blank">
                    <img class="thumb" src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                    <?php if($article['member']): ?>
                    <span class="label">会员</span>
                    <?php endif; ?>
                </a>
                <h3>
                    <a href="<?= htmlspecialchars($article['url']) ?>" target="_blank">
                        <?= htmlspecialchars($article['title']) ?>
                    </a>
                </h3>
                <footer><?= htmlspecialchars($article['episodes']) ?></footer>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页导航 -->
        <div class="pagination">
            <?php 
            $queryString = $keyword ? '&keyword='.urlencode($keyword) : '';
            
            if ($page > 1): ?>
                <a href="?page=1<?= $queryString ?>">首页</a>
                <a href="?page=<?= $page-1 ?><?= $queryString ?>">上一页</a>
            <?php else: ?>
                <span class="disabled">首页</span>
                <span class="disabled">上一页</span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if($start > 1) echo '<span>...</span>';
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if($end < $totalPages) echo '<span>...</span>'; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?><?= $queryString ?>">下一页</a>
                <a href="?page=<?= $totalPages ?><?= $queryString ?>">末页</a>
            <?php else: ?>
                <span class="disabled">下一页</span>
                <span class="disabled">末页</span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div id="noResults" style="text-align: center; padding: 20px; color: #333;">
            没有搜到相关漫画哦！试试用漫画关键词搜索呀！例如：格外性感的深见 搜索：深见
        </div>
        <?php endif; ?>
    </section>

    <!-- 脚本部分 -->
    <script>
        // 获取元素
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchBtn');
        const items = document.querySelectorAll('.item');
        const noResults = document.getElementById('noResults');
    
        // 保存原始标题HTML（用于清除高亮）
        items.forEach(item => {
            const titleElement = item.querySelector('h3 a');
            titleElement.dataset.original = titleElement.innerHTML; // 存储原始HTML
        });
    
        function highlightMatches(text, searchTerm) {
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }
    
        function performSearch() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            let hasMatch = false;
            let visibleCount = 0;
    
            items.forEach(item => {
                const titleElement = item.querySelector('h3 a');
                const originalTitle = titleElement.textContent; // 保留原始大小写
                const footer = item.querySelector('footer').textContent.toLowerCase();
                
                // 恢复原始内容（避免高亮残留）
                titleElement.innerHTML = titleElement.dataset.original;
    
                // 匹配逻辑（标题保留大小写匹配）
                const titleMatch = originalTitle.toLowerCase().includes(searchTerm);
                const footerMatch = footer.includes(searchTerm);
                const isVisible = titleMatch || footerMatch;
    
                item.style.display = isVisible ? 'block' : 'none';
                if (isVisible) {
                    visibleCount++;
                    // 高亮处理（保留原始大小写）
                    if (searchTerm) {
                        titleElement.innerHTML = highlightMatches(originalTitle, searchTerm);
                    }
                }
            });
    
            // 处理无结果提示
            noResults.style.display = (searchTerm && visibleCount === 0) ? 'block' : 'none';
    
            // 清空时完全恢复
            if (!searchTerm) {
                items.forEach(item => {
                    item.style.display = 'block';
                    const titleElement = item.querySelector('h3 a');
                    titleElement.innerHTML = titleElement.dataset.original; // 还原原始HTML
                });
                noResults.style.display = 'none';
            }
        }
    
        // 绑定事件（增加input监听实现实时搜索）
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('input', performSearch); // 输入时实时触发
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch();
        });
    </script>
</body>
</html>