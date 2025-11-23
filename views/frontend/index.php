<?php
/**
 * F1-主界面模块
 * 9宫格卡片展示 + 访问码拦截
 */
$pageTitle = '欢迎来到海の小窝 🐋';
$customCss = '
<style>
    .main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    .welcome-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 40px;
        text-align: center;
    }
    .welcome-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #1976D2;
        margin-bottom: 10px;
    }
    .welcome-desc {
        color: #666;
        font-size: 1.1rem;
    }
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    .module-card {
        background: white;
        border-radius: 15px;
        padding: 35px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .module-card:hover {
        transform: translateY(-10px);
        border-color: #1976D2;
        box-shadow: 0 15px 35px rgba(25, 118, 210, 0.3);
    }
    .module-icon {
        font-size: 3.5rem;
        margin-bottom: 15px;
    }
    .module-title {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    .module-desc {
        font-size: 0.9rem;
        color: #999;
    }
    
    /* 访问码弹窗样式 */
    .modal-content {
        border-radius: 20px;
        border: none;
    }
    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }
    .modal-body {
        padding: 30px;
    }
    .access-code-input {
        font-size: 1.5rem;
        text-align: center;
        letter-spacing: 5px;
        border-radius: 10px;
        border: 2px solid #ddd;
        padding: 15px;
    }
    .access-code-input:focus {
        border-color: #1976D2;
        box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
    }
</style>
';

$customJs = '
<script>
$(document).ready(function() {
    let targetUrl = "";
    
    // 点击模块卡片
    $(".module-card").click(function() {
        targetUrl = $(this).data("url");
        $("#accessCodeModal").modal("show");
        $("#accessCode").val("").focus();
    });
    
    // 提交访问码
    $("#verifyBtn").click(function() {
        const code = $("#accessCode").val().trim();
        
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
                if (res.success) {
                    window.location.href = targetUrl;
                } else {
                    alert(res.message || "访问码错误");
                    $("#accessCode").val("").focus();
                }
            },
            error: function() {
                alert("验证失败，请稍后重试");
            }
        });
    });
    
    // 回车提交
    $("#accessCode").keypress(function(e) {
        if (e.which == 13) {
            $("#verifyBtn").click();
        }
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
        <p class="welcome-desc">无偿分享 禁止盗卖 更多精彩</p>
        <p class="text-muted small">微博@资源小站</p>
    </div>
    
    <!-- 9大功能模块 -->
    <div class="module-grid">
        <!-- 1. 韩漫合集 -->
        <div class="module-card" data-url="/korean">
            <div class="module-icon">📚</div>
            <div class="module-title">韩漫合集</div>
            <div class="module-desc">精选韩漫作品</div>
        </div>
        
        <!-- 2. 日更板块 -->
        <div class="module-card" data-url="/daily">
            <div class="module-icon">📅</div>
            <div class="module-title">日更板块</div>
            <div class="module-desc">每日更新资源</div>
        </div>
        
        <!-- 3. 完结短漫 -->
        <div class="module-card" data-url="/short">
            <div class="module-icon">🏅</div>
            <div class="module-title">完结短漫</div>
            <div class="module-desc">短篇完结作品</div>
        </div>
        
        <!-- 4. 日漫推荐 -->
        <div class="module-card" data-url="/japan-recommend">
            <div class="module-icon">🎌</div>
            <div class="module-title">日漫推荐</div>
            <div class="module-desc">精品日漫推荐</div>
        </div>
        
        <!-- 5. 日漫合集 -->
        <div class="module-card" data-url="/japan-collection">
            <div class="module-icon">🎁</div>
            <div class="module-title">日漫合集</div>
            <div class="module-desc">日漫资源合集</div>
        </div>
        
        <!-- 6. 动漫合集 -->
        <div class="module-card" data-url="/anime">
            <div class="module-icon">🎬</div>
            <div class="module-title">动漫合集</div>
            <div class="module-desc">动画视频资源</div>
        </div>
        
        <!-- 7. 广播剧合集 -->
        <div class="module-card" data-url="/drama">
            <div class="module-icon">🎧</div>
            <div class="module-title">广播剧合集</div>
            <div class="module-desc">精彩广播剧</div>
        </div>
        
        <!-- 8. 失效反馈 -->
        <div class="module-card" data-url="/feedback">
            <div class="module-icon">💬</div>
            <div class="module-title">失效反馈</div>
            <div class="module-desc">资源失效报告</div>
        </div>
        
        <!-- 9. 防走丢 -->
        <div class="module-card" data-url="/backup">
            <div class="module-icon">📍</div>
            <div class="module-title">防走丢</div>
            <div class="module-desc">备用访问地址</div>
        </div>
    </div>
</div>

<!-- 访问码验证弹窗 -->
<div class="modal fade" id="accessCodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">请输入访问码</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control access-code-input" id="accessCode" placeholder="输入密码，不会就看下方取码教程">
                </div>
                <div class="text-center mb-3">
                    <button type="button" class="btn btn-primary btn-custom" id="verifyBtn">提交</button>
                </div>
                <div class="text-center">
                    <p class="text-muted small mb-2">🎉 取码教程</p>
                    <p class="text-muted small mb-1">获取每日访问码👇</p>
                    <div class="d-grid gap-2">
                        <a href="https://space.bilibili.com/example" target="_blank" class="btn btn-outline-primary btn-sm">UC</a>
                        <a href="https://kuke.com/example" target="_blank" class="btn btn-outline-success btn-sm">夸克</a>
                        <a href="https://www.xunlei.com/example" target="_blank" class="btn btn-outline-warning btn-sm">迅雷</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>


