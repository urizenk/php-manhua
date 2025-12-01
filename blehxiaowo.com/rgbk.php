<?php
session_start();
require 'config.php';
require 'functions.php'; // 引入辅助函数文件

// 基础会话验证
if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: index.php');
    exit;
}

// 密码版本验证
try {
    // 从缓存或数据库获取最新密码ID
    $latest_password_id = null;
    if ($redis && $redis->exists('latest_password_id')) {
        $latest_password_id = $redis->get('latest_password_id');
    } else {
        $latest_password_id = getLatestPasswordIdFromDB($pdo);
        if ($redis) {
            $redis->setex('latest_password_id',18000, $latest_password_id); // 缓存5分钟
        }
    }

    // 对比会话中的密码版本
    if ($_SESSION['password_version'] != $latest_password_id) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    // 降级处理：允许访问但记录日志
    error_log("密码版本验证失败: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日更板块</title>
        <!-- 引入 FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* 全局样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFF0; /* 奶黄色背景 */
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            color: #1B1212d0; /* 深灰色字体 */
            font-size: 32px;
            font-weight: bold;
            padding: 10px 0; /* 上下内边距 */
            display: block; /* 独占一行 */
            margin-bottom: 20px; /* 标题与内容之间的间距 */
        }

        /* 日期样式 */
        .date-title {
            display: inline-block;
            background-color: #FFA500; /* 橙色背景 */
            color: white; /* 白色字体 */
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            margin: 10px 0; /* 上下外边距相同 */
            border-radius: 5px; /* 圆角矩形 */
        }
        /* Tip 标签样式 */
        .tip {
            background-color: #FFE4B5; /* 浅橙色背景 */
            padding: 8px 12px; /* 内边距 */
            border-radius: 25px; /* 圆角 */
            margin-bottom: 20px; /* 与下方内容的间距 */
            font-size: 14px;
            color: #333;
            display: block; /* 修改为块级元素，独占一行 */
            width: fit-content; /* 宽度根据内容自适应 */
        }

        /* 回到目录按钮样式 */
        .back-to-index {
            display: block; /* 修改为块级元素，独占一行 */
            background-color: #EC5800; /* 绿色背景 */
            color: white; /* 白色文字 */
            padding: 10px 15px;
            border-radius: 5px; /* 圆角矩形 */
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 20px; /* 与下方内容的间距 */
            width: fit-content; /* 宽度根据内容自适应 */
        }

        .back-to-index:hover {
            background-color: #e69500; /* 悬停时颜色加深 */
        }

        p {
            font-size: 16px;
            margin: 5px 0;
        }
        
                /* 回到首页按钮样式 */
        .back-to-index {
            display: inline-flex;
            align-items: center;
            gap: 8px; /* 图标和文字之间的间距 */
            background-color: #EC5800; /* 绿色背景 */
            color: white; /* 白色文字 */
            padding: 10px 15px;
            border-radius: 5px; /* 圆角矩形 */
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 20px; /* 与下方内容的间距 */
        }

        a {
            color: #0077ff;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            display: inline-block; /* 图标和文字在同一行 */
            margin: 5px 0;
        }

        a:hover {
            text-decoration: underline;
        }

        .section {
            margin-bottom: 30px;
        }

        .section .link {
            display: flex;
            align-items: center;
            gap: 5px; /* 图标和文字之间的间距 */
        }

        .section .link::before {
            content: "🔗"; /* 默认链接图标 */
            font-size: 16px;
        }

        .section .key::before {
            content: "🗝️"; /* 钥匙图标 */
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>日更板块</h1>
    
        <div class="tip">
            Tip ：日更保留一个月，往期资源见微博铁粉群
        </div>
    
            <!-- 回到首页按钮 -->
        <a href="index.php" class="back-to-index">
            <i class="fas fa-arrow-left"></i> <!-- FontAwesome 向左箭头图标 -->
            回到目录
        </a>
    <div class="section">
      <div class="date-title">1124</div>
      <p>【韩漫】巢穴1-9完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/170512a0-a67d-44f8-9b15-28b38d30e667?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1123</div>
      <p>【1123单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3sv0fa">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/22d20784ddfd">点我点我</a></div>
      <p>【韩漫】深秋入冬 无光版【1册正篇＋薄暮篇】</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/8bf16c31-1da5-4600-97f9-d04eb92b346d?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1122</div>
      <p>【1122单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3ssbve">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/d88197a32d1a">点我点我</a></div>
      <p>【韩漫】Bad best 1-7完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/8c84800c-0637-41d1-91e0-a32a3c5bcbf7?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1121</div>
      <p>【1121单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3spy5i">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/3a7acea1126b">点我点我</a></div>
      <p>【韩漫】香的秘密1-12完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/61f45bf0-6643-4f28-a42f-21de97d7cab7?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1120</div>
      <p>【1120单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3sn8oj">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/24c13eab2959">点我点我</a></div>
      <p>【韩漫】Under develop1-5完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/7d6acc9a-1d8d-4561-8fea-1719e091541a?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1119</div>
      <p>【1119单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3skcri">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/22f80581a4aa">点我点我</a></div>
      <p>【日漫】糖霜色的独占欲 无光版 全2话</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/cb4a1722-8200-4ba9-b10d-cb42d89f26ce?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1118</div>
      <p>【1118单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3shtrc">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/68dff3b10787">点我点我</a></div>
      <p>【韩漫】实果/编织陷阱1-6完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/22b7ad71-b6c9-4446-8e8d-6f160ce95644?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1117</div>
      <p>【1117单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3segkj">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/f2c0aea01356">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1116</div>
      <p>【1116单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3sbync">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/4f33a8acbf5f">点我点我</a></div>
      <p>【日漫】蛇神的新娘 无光版 全2话</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/7c82adb4-95a7-456e-a09c-bedfce231d4a?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1115</div>
      <p>【1115单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3sa2qh">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/f6013bb2751d">点我点我</a></div>
      <p>【韩漫】Zero Sugar Love 台版1-9完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/383e552a-eb76-4289-a44c-f15358a4ac4b?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1114</div>
      <p>【1114单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3s6w8f">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/416a274b2dca">点我点我</a></div>
      <p>【韩漫】到现在为止输入的提示语都忘掉 只爱你正篇4外传3完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/fda8dbad-2455-429f-b6ed-7608299cb079?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1113</div>
      <p>【1113单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3s4aqj">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/e29c39b2210e">点我点我</a></div>
      <p>【韩漫】迷惑的境界 无光版1-28</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/7e1690ba-4e0a-49a6-a0a4-1f50cdfe4899?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1112</div>
      <p>【1112单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3s1kaf">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/57ffba8cbb87">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1111</div>
      <p>【1111单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3ryesj">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/c4591b20fe71">点我点我</a></div>
      <p>【日漫】被青梅竹马调教 全</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/c08b86f0-9b28-47a6-aa00-7ee2d9aa04a7?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1110</div>
      <p>【1110单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rvk7g">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/4d9024fe5af5">点我点我</a></div>
      <p>【韩漫】皮格马利翁 正篇34外传27完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/a1c736c2-fa07-4399-ac1f-982b1ae55d07?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1109</div>
      <p>【1109单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rt0bi">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/6c28cce794e6">点我点我</a></div>
      <p>【韩漫】禁止放生渣男 正篇6外传5完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/fb3c4bd2-0470-4349-a8d7-dbe3652cd417?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1108</div>
      <p>【1108单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rqjxg">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/9ebee08b1a69">点我点我</a></div>
      <p>【韩漫】我的唯一 1-9完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/69df00d5-cf48-4670-9ff8-34924529d1f9?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1107</div>
      <p>【1107单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rniba">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/cf4ae8c45816">点我点我</a></div>
      <p>【韩漫】触手之夜 1-5完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/112d1fd2-af13-4ce5-92fb-dac0640a2a60?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1106</div>
      <p>【1106单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rl2xe">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/42420d6ae9ec">点我点我</a></div>
      <p>【韩漫】你让我好羞耻 无光版正篇5外传4完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/29fd35d4-0d49-4c89-b963-8e10c5d64620?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1105</div>
      <p>【1105单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3ri3hg">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/711a9ddaf273">点我点我</a></div>
      <p>【韩漫】蜜桃少年 无光版1-40</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/dbe7c12f-0ff9-4671-948f-8ed43c5ae437?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】不要对我说谎 无光版1-27第一季完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/5c054c75-a4b9-474b-9505-f8a38772009d?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1104</div>
      <p>【1104单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rfcrc">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/d6df3f607dd8">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1103</div>
      <p>【1103单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3rb56f">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/05b8d6bd7f9e">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1102</div>
      <p>【1102单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3r7t6f">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/c47bdf7dca96">点我点我</a></div>
      <p>【韩漫】薄荷恋上糖</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/8beab38c-76a3-4e52-87a9-09ee48747174?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【日漫】静待因果报应 【105P】完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/96d3480c-3f48-4d90-a365-70055e616e97?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1101</div>
      <p>【1101单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3r52uf">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/8093ce5b6b58">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1031</div>
      <p>【1031单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3r1w8j">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/713336ba9522">点我点我</a></div>
      <p>【韩漫】迷惑的境界台无光1-26</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/4ac00898-223a-437a-8bcb-9ab2fe3bf2e8?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】3秒后，即将回归 1-10完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/80481ec0-64b9-40fa-8298-175c16302452?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1030</div>
      <p>【1030单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qz7aj">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/3bc39949a6fd">点我点我</a></div>
      <p>【ppatta最新】黑魔法师的交易</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/f4d476fc-198f-47ff-b75f-0a4d3c22517a?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1029</div>
      <p>【1029单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qw6xi">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/6b2f37b8b887">点我点我</a></div>
      <p>【韩漫】追捕1-50完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/023a636c-509f-46d9-a05c-967b7990b9ab?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】无家可归的吸血鬼1-11完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/be6bd32f-e74a-4a7f-8553-7f4196b7ed5e?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1028</div>
      <p>【1028单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qto0f">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/04ac551f612b">点我点我</a></div>
      <p>【韩漫】网路直播主：邦尼 台无光1-27完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/d1ce109e-d5d0-41a1-abe5-457826335a06?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】多情佛心 正篇10外传4完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/39f4cf55-078d-43c9-afb7-ddbcc94fafeb?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1027</div>
      <p>【1027单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qr22d">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/c2d89ee029414?public=1">点我点我</a></div>
      <p>【韩漫】美男与野兽1-4完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/2cdae30e-4ee9-4de0-ae03-714a5068171b?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】祈祷/心诚祈愿正篇95完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/7b7047ae-e983-45be-900f-c559b5d9ca0b?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1026</div>
      <p>【1026单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qoqcj">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/b9a4b7ff1e78">点我点我</a></div>
      <p>【韩漫】二次偏离1-8完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/ae1cb117-cf08-47c0-a908-165fe56ed791?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】安慰剂 无光版正篇58+外传10完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/a8f6cbf3-4f99-4460-8be4-0f24673a333f?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1025</div>
      <p>【1025单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qlomj">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/f17366728d174?public=1">点我点我</a></div>
      <p>【韩漫】恋爱幻想曲 正篇+外传全＋Queen篇</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/fcc384db-b9cd-4277-ae77-5cea3c59c221?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1024</div>
      <p>【1024单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qjcbe">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/7b9acbd0d6b44?public=1">点我点我</a></div>
      <p>【韩漫】偶像妄想症/糊咖情结1-7完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/17f9c30d-0af4-4c61-bab4-16fc6a12a3c3?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1023</div>
      <p>【1023单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qgybi">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/69369bc6251c4?public=1">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1022</div>
      <p>【1022单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qeevg">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/b6e22abad5594?public=1">点我点我</a></div>
      <p>【韩漫】迷惑的境界 台无光1-25</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/eeefeb9a-1214-4ca6-ac3f-561af78e4117?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1021</div>
      <p>【1021单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3qbcrc">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/234a9284377c4?public=1">点我点我</a></div>
      <p>【韩漫】隔壁的他1-12完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/7a4b6a47-2835-4c3b-a099-ac1567344a18?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1020</div>
      <p>【1020单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3q8kpa">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/8d4017a22b834?public=1">点我点我</a></div>
      <p>【韩漫】半夜1-7完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/19013ce6-20b7-4db2-b7c9-f2984ae76481?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1019</div>
      <p>【1019单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3q61ed">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/77184e72331b4?public=1">点我点我</a></div>
      <p>【动漫】奏多君工作日常-后篇~11min【中文字幕】</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/769318cf-9357-4eca-805f-5550a254da16?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1018</div>
      <p>【1018单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3q3s7a">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/53a1a876e1ea4?public=1">点我点我</a></div>
      <p>【韩漫】亲密的兄弟1-8完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/509c2e7e-ef65-46e6-aa56-8305f4693ce2?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1017</div>
      <p>【1017单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3q15jc">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/3887f63e384b4?public=1">点我点我</a></div>
      <p>【韩漫】寻找辛德瑞拉1-18完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/155fa60a-e864-458e-a938-2441396001eb?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1016</div>
      <p>【1016单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3py17i">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/beefd9d0412f4?public=1">点我点我</a></div>
      <p>【韩漫】不准偷看我的心！无光版正篇8完结＋外传7完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/6410a795-0e18-4a15-94f4-ddc1cf0a1c9e?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1015</div>
      <p>【1015单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3pv5tg">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/6772ab2f71e84">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1014</div>
      <p>【1014单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3pru5i">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/57c68ac8d2614">点我点我</a></div>
      <p>【韩漫】水花splash1-7完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/8580e174-e62a-466a-a26f-4ae18f64c35e?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1013</div>
      <p>【1013单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3ppm3g">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/8aa6110141db4">点我点我</a></div>
      <p>【韩漫】贝果太过分了/童颜太过分了！1-5完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/75e166db-ebd9-4ae6-8564-3f8570cb6331?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1012</div>
      <p>【1012单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3pn3sf">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/db8e929126994">点我点我</a></div>
      <p>【动漫】同人动漫</p>
      <div class="link"><a href="https://pan.baidu.com/s/15eXf8Oqmos7EEhYml7cKoA?pwd=bleh">点我点我</a></div>
      <p>【韩漫】未曾说喜欢 1-5完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/b2712bec-e91e-4e1e-8d3a-9b803005dd9a?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1011</div>
      <p>【1011单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3pk7xg">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/6079098ceb5c4">点我点我</a></div>
      <p>【韩漫】八男七夜/七日八色 台无光正篇14外传2完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/66ea2940-20c9-403d-bb08-90ab974b1ff3?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1010</div>
      <p>【1010单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3phvqf">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/590eb5baff6a4?public=1">点我点我</a></div>
      <p>【韩漫】V博士和三个恋人1-20第一季完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/03a9f944-6772-4524-a518-09e09935ba17?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1009</div>
      <p>【1009单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3pf4te">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/cee9d0ad069d4?public=1">点我点我</a></div>
      <p>【韩漫】请与我相爱1-7完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/4c9f2ae0-a7ef-45fc-926e-e28639955b5f?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1008</div>
      <p>【1008单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3pcjah">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/5b9e88a8a9a34?public=1">点我点我</a></div>
      <p>【韩漫】迷惑的境界 台无光1-23</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/4ac00898-223a-437a-8bcb-9ab2fe3bf2e8?code=KEKW5D&embed=true">点我点我</a></div>
      <p>【韩漫】影子怪物和新郎1-10完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/bf610863-582d-4f7c-adae-676d232257e2?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1007</div>
      <p>【1007单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3p9hjg">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/81aba1e16ef74?public=1">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1006</div>
      <p>【1006单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3p6wih">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/da24273eb11f">点我点我</a></div>
      <p>【韩漫】首席顾问1-8完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/fc558155-85a2-4292-802c-50c7d6be4c9d?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1005</div>
      <p>【1005单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3p4skd">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/584a494d533c">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1004</div>
      <p>【1004单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3p27uf">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/94f35432e0d7">点我点我</a></div>
      <p>【韩漫】和青梅竹马一起驱魔中1-8end</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/8b7b8287-3711-4b73-a001-a31ad454f315?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1003</div>
      <p>【1003单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3p031a">点我点我</a></div>
      <div class="key"><a href="https://drive.uc.cn/s/83dc1eaa46de4?public=1">点我点我</a></div>
      <p>【动漫】透明人：温泉旅馆篇【中文字幕】28min</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/3623bc51-aa94-4ff8-9cae-49ed055b8938?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1002</div>
      <p>【1002单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3oxtbi">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/af06a89caa08">点我点我</a></div>
      <p>【韩漫】迷惑的境界-台无光 1-22</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/4ac00898-223a-437a-8bcb-9ab2fe3bf2e8?code=KEKW5D&embed=true">点我点我</a></div>
    </div>
    <div class="section">
      <div class="date-title">1001</div>
      <p>【1001单集日更汇总】</p>
      <div class="link"><a href="https://jjnztxsb.lanzov.com/b00g3ovd7g">点我点我</a></div>
      <div class="key"><a href="https://pan.quark.cn/s/9df70bef696a">点我点我</a></div>
      <p>【韩漫】单相思的经营战略1-10完结</p>
      <div class="link"><a href="https://flowus.cn/seacomic2/share/f38cb876-a56a-469f-8042-4a1022c0cf37?code=KEKW5D&embed=true">点我点我</a></div>
    </div>

</body>
</html>