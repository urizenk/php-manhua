<?php
/**
 * F1-主界面模块
 * 9 宫格卡片展示 + 访问码拦截
 */

// 从全局获取依赖
$db      = $GLOBALS['db'] ?? null;
$session = $GLOBALS['session'] ?? null;
$config  = $GLOBALS['config'] ?? null;

$pageTitle = '欢迎来到海の小窝';

// 从数据库读取配置
$siteConfig = $db->query("SELECT config_key, config_value FROM site_config");
$configSettings = [];
foreach ($siteConfig as $row) {
    $configSettings[$row['config_key']] = $row['config_value'];
}

$siteName = $configSettings['site_name'] ?? '海の小窝';
$siteDesc = $configSettings['site_desc'] ?? '无偿分享 · 禁止盗卖 · 更多精彩资源等你发现';
$weiboUrl = $configSettings['weibo_url'] ?? '#';
$weiboText = $configSettings['weibo_text'] ?? '微博@资源小站';
$homepageRedirectUrl = $configSettings['homepage_redirect_url'] ?? '';

// 使用首页跳转URL，如果没有设置则使用微博URL
$jumpUrl = $homepageRedirectUrl ?: $weiboUrl;

// 模块类型列表，用于动态渲染首页模块
$types = $db ? $db->query('SELECT * FROM manga_types ORDER BY sort_order, id') : [];

// 当前访问码是否已通过验证
$isAccessVerified = $session ? $session->isAccessVerified() : false;

$customCss = '
<style>
    body {
        background: linear-gradient(135deg, #FFF8E1 0%, #FFE0B2 100%);
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
        font-size: 2.2rem;
        font-weight: bold;
        color: #ffffff;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    .welcome-desc {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1rem;
        margin-bottom: 20px;
    }
    .weibo-btn {
        display: inline-block;
        margin-top: 10px;
        padding: 12px 35px;
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
        box-shadow: 0 12px 30px rgba(255, 107, 53, 0.25);
        background: linear-gradient(135deg, #FFF5E6 0%, #ffffff 100%);
    }
    .module-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 15px;
        border-radius: 16px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    .module-title {
        font-size: 1.15rem;
        font-weight: bold;
        color: #333333;
        margin-bottom: 6px;
    }
    .module-desc {
        font-size: 0.85rem;
        color: #999999;
    }

    /* 移动端双列布局 */
    @media (max-width: 768px) {
        .main-container {
            padding: 20px 12px;
        }
        .welcome-card {
            padding: 24px 16px;
            border-radius: 16px;
            margin-bottom: 25px;
        }
        .welcome-title {
            font-size: 1.6rem;
        }
        .welcome-desc {
            font-size: 0.9rem;
        }
        .weibo-btn {
            padding: 10px 28px;
            font-size: 0.9rem;
        }
        .module-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .module-card {
            padding: 20px 12px;
            border-radius: 12px;
        }
        .module-icon {
            width: 54px;
            height: 54px;
            font-size: 1.4rem;
            border-radius: 12px;
            margin-bottom: 12px;
        }
        .module-title {
            font-size: 0.95rem;
        }
        .module-desc {
            font-size: 0.75rem;
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
        font-size: 1.3rem;
        text-align: center;
        letter-spacing: 4px;
        border-radius: 10px;
        border: 2px solid #FFD4B8;
        padding: 14px;
        background: #FFF8E1;
    }
    .access-code-input:focus {
        border-color: #FF6B35;
        box-shadow: 0 0 0 0.18rem rgba(255, 107, 53, 0.25);
        background: #ffffff;
    }
    .btn-access-submit {
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border: none;
        padding: 12px 40px;
        font-size: 1.05rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        color: white;
    }
    .btn-access-submit:hover {
        background: linear-gradient(135deg, #FF6B35 0%, #FF5722 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        color: white;
    }
</style>
';

// 模块描述映射
$moduleDescMap = [
    'korean_collection' => '精选韩漫作品',
    'daily_update'      => '每日更新资源',
    'short_complete'    => '短篇完结作品',
    'japan_recommend'   => '精品日漫推荐',
    'japan_collection'  => '日漫资源合集',
    'anime_collection'  => '动画视频资源',
    'drama_collection'  => '精彩广播剧',
    'feedback'          => '资源失效反馈',
    'backup_link'       => '备用访问地址',
];

$customJs = '
<script>
(function() {
    var targetUrl = "";
    var isVerified = ' . ($isAccessVerified ? 'true' : 'false') . ';
    var accessModal = document.getElementById("accessModal");
    var modalContent = document.querySelector(".access-modal-content");
    var accessInput = document.getElementById("accessCode");
    var verifyBtn = document.getElementById("verifyBtn");
    var closeBtn = document.getElementById("modalClose");

    if (!accessModal || !accessInput || !verifyBtn) {
        return;
    }

    function openModal() {
        accessModal.classList.add("show");
        accessInput.value = "";
        setTimeout(function() {
            accessInput.focus();
        }, 50);
    }

    function closeModal() {
        accessModal.classList.remove("show");
    }

    function postVerify(code) {
        var payload = new URLSearchParams({ code: code }).toString();

        if (window.fetch) {
            return fetch("/verify-code", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                },
                body: payload
            }).then(function(resp) { return resp.json(); });
        }

        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "/verify-code");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
            xhr.onload = function() {
                try {
                    resolve(JSON.parse(xhr.responseText));
                } catch (err) {
                    reject(err);
                }
            };
            xhr.onerror = reject;
            xhr.send(payload);
        });
    }

    document.querySelectorAll(".module-card").forEach(function(card) {
        card.addEventListener("click", function() {
            targetUrl = card.getAttribute("data-url") || "";
            if (!targetUrl) {
                return;
            }

            if (isVerified) {
                window.location.href = targetUrl;
                return;
            }

            openModal();
        });
    });

    verifyBtn.addEventListener("click", function() {
        var code = accessInput.value.trim();
        if (!code) {
            alert("请输入访问码");
            return;
        }

        postVerify(code).then(function(res) {
            if (res && res.success) {
                isVerified = true;
                closeModal();
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            } else {
                alert(res && res.message ? res.message : "访问码错误");
                accessInput.value = "";
                accessInput.focus();
            }
        }).catch(function() {
            alert("验证失败，请稍后重试");
        });
    });

    accessInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            verifyBtn.click();
        }
    });

    closeBtn.addEventListener("click", closeModal);
    accessModal.addEventListener("click", function(e) {
        if (e.target === accessModal) {
            closeModal();
        }
    });
    if (modalContent) {
        modalContent.addEventListener("click", function(e) {
            e.stopPropagation();
        });
    }
})();
</script>
';

include APP_PATH . '/views/layouts/header.php';
?>

<div class="main-container">
    <!-- 欢迎卡片 -->
    <div class="welcome-card">
        <h1 class="welcome-title">欢迎来到<?php echo htmlspecialchars($siteName); ?></h1>
        <p class="welcome-desc"><?php echo htmlspecialchars($siteDesc); ?></p>
        <a href="<?php echo htmlspecialchars($jumpUrl); ?>" target="_blank" class="weibo-btn">
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
                    $icon = $type['icon'] ?? 'book';
                    $desc = $moduleDescMap[$code] ?? '漫画资源模块';
                    $url  = module_url($code);
                ?>
                <div class="module-card" data-url="<?php echo htmlspecialchars($url); ?>">
                    <div class="module-icon">
                        <i class="bi bi-<?php echo htmlspecialchars($icon); ?>"></i>
                    </div>
                    <div class="module-title">
                        <?php echo htmlspecialchars($type['type_name']); ?>
                    </div>
                    <div class="module-desc">
                        <?php echo htmlspecialchars($desc); ?>
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
                       placeholder="输入访问码">
            </div>
            <div class="text-center mb-3">
                <button type="button" class="btn btn-primary btn-access-submit" id="verifyBtn">提交</button>
            </div>
            <div class="text-center">
                <p class="text-muted small mb-2">取码教程</p>
                <p class="text-muted small mb-1">关注主页即可获取每日访问码</p>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
