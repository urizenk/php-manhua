<?php
/**
 * F1-主界面模块
 * 9宫格卡片展示 + 访问码拦截
 */
$pageTitle = '欢迎来到海の小窝 🐋';
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
        color: white;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    .welcome-desc {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1.1rem;
    }
    .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
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
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1);
    }
    .module-card:hover {
        transform: translateY(-10px);
        border-color: #FF6B35;
        box-shadow: 0 15px 35px rgba(255, 107, 53, 0.3);
        background: linear-gradient(135deg, #FFF5E6 0%, white 100%);
    }
    .module-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        transition: all 0.3s ease;
    }
    .module-card:hover .module-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
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
        box-shadow: 0 10px 40px rgba(255, 107, 53, 0.2);
    }
    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        color: white;
        border-radius: 20px 20px 0 0;
    }
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    .modal-body {
        padding: 30px;
    }
    .access-code-input {
        font-size: 1.5rem;
        text-align: center;
        letter-spacing: 5px;
        border-radius: 10px;
        border: 2px solid #FFD4B8;
        padding: 15px;
        background: #FFF5E6;
    }
    .access-code-input:focus {
        border-color: #FF6B35;
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        background: white;
    }
    .btn-custom {
        background: linear-gradient(135deg, #FF9966 0%, #FF6B35 100%);
        border: none;
        padding: 12px 40px;
        font-size: 1.1rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    .btn-custom:hover {
        background: linear-gradient(135deg, #FF6B35 0%, #FF5722 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }
    .btn-outline-primary {
        color: #FF6B35;
        border-color: #FF6B35;
    }
    .btn-outline-primary:hover {
        background: #FF6B35;
        border-color: #FF6B35;
    }
    .btn-outline-success {
        color: #FF9966;
        border-color: #FF9966;
    }
    .btn-outline-success:hover {
        background: #FF9966;
        border-color: #FF9966;
    }
    .btn-outline-warning {
        color: #FFA726;
        border-color: #FFA726;
    }
    .btn-outline-warning:hover {
        background: #FFA726;
        border-color: #FFA726;
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
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M19 2H5c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM7 20H5v-2h2v2zm0-4H5v-2h2v2zm0-4H5V8h2v2zm0-4H5V4h2v2zm4 12H9v-2h2v2zm0-4H9v-2h2v2zm0-4H9V8h2v2zm0-4H9V4h2v2zm4 12h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V8h2v2zm0-4h-2V4h2v2zm4 12h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V8h2v2zm0-4h-2V4h2v2z"/>
                </svg>
            </div>
            <div class="module-title">韩漫合集</div>
            <div class="module-desc">精选韩漫作品</div>
        </div>
        
        <!-- 2. 日更板块 -->
        <div class="module-card" data-url="/daily">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V9h14v10zM5 7V5h14v2H5zm2 4h10v2H7v-2z"/>
                </svg>
            </div>
            <div class="module-title">日更板块</div>
            <div class="module-desc">每日更新资源</div>
        </div>
        
        <!-- 3. 完结短漫 -->
        <div class="module-card" data-url="/short">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="module-title">完结短漫</div>
            <div class="module-desc">短篇完结作品</div>
        </div>
        
        <!-- 4. 日漫推荐 -->
        <div class="module-card" data-url="/japan-recommend">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
            </div>
            <div class="module-title">日漫推荐</div>
            <div class="module-desc">精品日漫推荐</div>
        </div>
        
        <!-- 5. 日漫合集 -->
        <div class="module-card" data-url="/japan-collection">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/>
                </svg>
            </div>
            <div class="module-title">日漫合集</div>
            <div class="module-desc">日漫资源合集</div>
        </div>
        
        <!-- 6. 动漫合集 -->
        <div class="module-card" data-url="/anime">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M18 3v2h-2V3H8v2H6V3H4v18h2v-2h2v2h8v-2h2v2h2V3h-2zM8 17H6v-2h2v2zm0-4H6v-2h2v2zm0-4H6V7h2v2zm6 10h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V9h4v2zm0-4h-4V5h4v2zm4 12h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2z"/>
                </svg>
            </div>
            <div class="module-title">动漫合集</div>
            <div class="module-desc">动画视频资源</div>
        </div>
        
        <!-- 7. 广播剧合集 -->
        <div class="module-card" data-url="/drama">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
            </div>
            <div class="module-title">广播剧合集</div>
            <div class="module-desc">精彩广播剧</div>
        </div>
        
        <!-- 8. 失效反馈 -->
        <div class="module-card" data-url="/feedback">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                </svg>
            </div>
            <div class="module-title">失效反馈</div>
            <div class="module-desc">资源失效报告</div>
        </div>
        
        <!-- 9. 防走丢 -->
        <div class="module-card" data-url="/backup">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            </div>
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


