<?php
/**
 * F1-主界面模块
 * 9 宫格卡片展示 + 访问码拦截
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$pageTitle = '欢迎来到海の小窝 🐋';

// 微博配置（可在 config/config.php 中修改）
$weiboUrl  = $config['app']['weibo_url']  ?? '#';
$weiboText = $config['app']['weibo_text'] ?? '微博@资源小站';

// 模块类型列表，用于动态渲染首页模块
$types = $db ? $db->query('SELECT * FROM manga_types ORDER BY sort_order, id') : [];

// 当前访问码是否已通过验证
$isAccessVerified = $session ? $session->isAccessVerified() : false;

// 各模块的展示元数据（图标 + 描述）
$moduleMeta = [
    'korean_collection' => ['icon' => '📚', 'desc' => '精选韩漫作品'],
    'daily_update'      => ['icon' => '📅', 'desc' => '每日更新资源'],
    'short_complete'    => ['icon' => '✅', 'desc' => '短篇完结作品'],
    'japan_recommend'   => ['icon' => '⭐', 'desc' => '精品日漫推荐'],
    'japan_collection'  => ['icon' => '🎁', 'desc' => '日漫资源合集'],
    'anime_collection'  => ['icon' => '🎬', 'desc' => '动画视频资源'],
    'drama_collection'  => ['icon' => '🎧', 'desc' => '精彩广播剧'],
    'feedback'          => ['icon' => '💬', 'desc' => '资源失效反馈'],
    'backup_link'       => ['icon' => '📍', 'desc' => '备用访问地址'],
];

$customCss = '
<style>
    body {
        background: linear-gradient(135deg, #FFF5E6 0%, #FFE4CC 100%);
        min-height: 100vh;
    }
    .main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    .welcome-card {
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border-radius: 20px;
        padding: 40px 30px;
        margin-bottom: 40px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
    }
    .welcome-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #ffffff;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    .welcome-desc {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    .weibo-btn {
        display: inline-block;
        margin-top: 10px;
        padding: 10px 30px;
        border-radius: 999px;
        border: 2px solid #fff;
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .weibo-btn:hover {
        background: #ffffff;
        color: #FF6B35;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    .module-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1);
    }
    .module-card:hover {
        transform: translateY(-8px);
        border-color: #FF6B35;
        box-shadow: 0 12px 30px rgba(255, 107, 53, 0.3);
        background: linear-gradient(135deg, #FFF5E6 0%, #ffffff 100%);
    }
    .module-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 15px;
        border-radius: 20px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    .module-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333333;
        margin-bottom: 6px;
    }
    .module-desc {
        font-size: 0.9rem;
        color: #999999;
    }

    /* 移动端双列布局 */
    @media (max-width: 768px) {
        .main-container {
            padding: 24px 14px;
        }
        .welcome-card {
            padding: 26px 18px;
        }
        .welcome-title {
            font-size: 1.9rem;
        }
        .welcome-desc {
            font-size: 0.95rem;
        }
        .module-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .module-card {
            padding: 22px 10px;
        }
        .module-icon {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }
        .module-title {
            font-size: 1.05rem;
        }
        .module-desc {
            font-size: 0.8rem;
        }
    }

    /* 访问码弹窗样式 */
    .access-modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .access-modal.show {
        display: flex;
    }
    .access-modal-content {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 18px 50px rgba(255, 107, 53, 0.35);
        overflow: hidden;
        animation: modalFade .25s ease;
    }
    @keyframes modalFade {
        from {
            opacity: 0;
            transform: translateY(25px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .access-modal-header {
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        color: #ffffff;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: bold;
    }
    .modal-close {
        border: none;
        background: transparent;
        color: #ffffff;
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
    }
    .access-modal-body {
        padding: 26px 26px 32px;
    }
    .access-code-input {
        font-size: 1.4rem;
        text-align: center;
        letter-spacing: 4px;
        border-radius: 10px;
        border: 2px solid #FFD4B8;
        padding: 14px;
        background: #FFF5E6;
    }
    .access-code-input:focus {
        border-color: #FF6B35;
        box-shadow: 0 0 0 0.18rem rgba(255, 107, 53, 0.25);
        background: #ffffff;
    }
    .btn-access-submit {
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border: none;
        padding: 10px 34px;
        font-size: 1.05rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    .btn-access-submit:hover {
        background: linear-gradient(135deg, #FF6B35 0%, #FF5722 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }
</style>
';

$customJs = '
<script>
$(document).ready(function() {
    var targetUrl = "";
    var isVerified = ' . ($isAccessVerified ? 'true' : 'false') . ';
    var $accessModal = $("#accessModal");

    // 点击模块卡片
    $(".module-card").on("click", function() {
        targetUrl = $(this).data("url");
        if (!targetUrl) {
            return;
        }

        // 已验证过访问码，直接跳转
        if (isVerified) {
            window.location.href = targetUrl;
            return;
        }

        // 未验证则弹出访问码输入框
        $accessModal.addClass("show");
        $("#accessCode").val("").focus();
    });

    // 提交访问码
    $("#verifyBtn").on("click", function() {
        var code = $.trim($("#accessCode").val());

        if (!code) {
            alert("请输入访问码");
            return;
        }

        $.ajax({
            url: "/verify-code",
            type: "POST",
            data: { code: code },
            dataType: "json",
            success: function(res) {
                if (res && res.success) {
                    isVerified = true;
                    $accessModal.removeClass("show");
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                } else {
                    alert(res && res.message ? res.message : "访问码错误");
                    $("#accessCode").val("").focus();
                }
            },
            error: function() {
                alert("验证失败，请稍后重试");
            }
        });
    });

    // 回车提交
    $("#accessCode").on("keypress", function(e) {
        if (e.which === 13) {
            $("#verifyBtn").click();
        }
    });

    $("#modalClose, #accessModal").on("click", function(e) {
        if (e.target.id === "modalClose" || e.target.id === "accessModal") {
            $accessModal.removeClass("show");
        }
    });

    $(".access-modal-content").on("click", function(e) {
        e.stopPropagation();
    });
});
</script>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="main-container">
    <!-- 欢迎卡片 -->
    <div class="welcome-card">
        <h1 class="welcome-title">欢迎来到海の小窝🐋</h1>
        <p class="welcome-desc">无偿分享 · 禁止盗卖 · 更多精彩资源等你发现</p>
        <a href="<?php echo htmlspecialchars($weiboUrl); ?>" target="_blank" class="weibo-btn">
            <?php echo htmlspecialchars($weiboText); ?>
        </a>
    </div>

    <!-- 功能模块九宫格 -->
    <div class="module-grid">
        <?php if (empty($types)): ?>
            <p class="text-muted text-center">尚未配置模块类型，请先在后台添加漫画类型。</p>
        <?php else: ?>
            <?php foreach ($types as $type): ?>
                <?php
                    $code = $type['type_code'];
                    $meta = $moduleMeta[$code] ?? ['icon' => '📖', 'desc' => '漫画资源模块'];
                    $url  = module_url($code);
                ?>
                <div class="module-card" data-url="<?php echo htmlspecialchars($url); ?>">
                    <div class="module-icon">
                        <?php echo htmlspecialchars($meta['icon']); ?>
                    </div>
                    <div class="module-title">
                        <?php echo htmlspecialchars($type['type_name']); ?>
                    </div>
                    <div class="module-desc">
                        <?php echo htmlspecialchars($meta['desc']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 访问码验证弹窗 -->
<div class="access-modal" id="accessModal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <span>请输入访问码</span>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="access-modal-body">
            <div class="mb-3">
                <input type="text"
                       class="form-control access-code-input"
                       id="accessCode"
                       placeholder="输入密码，不会就看下方取码教程">
            </div>
            <div class="text-center mb-3">
                <button type="button" class="btn btn-primary btn-access-submit" id="verifyBtn">提交</button>
            </div>
            <div class="text-center">
                <p class="text-muted small mb-2">🎉 取码教程</p>
                <p class="text-muted small mb-1">关注主页即可获取每日访问码</p>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
