<!-- 移动端通用样式 -->
<style>
/* ========== 移动端卡片样式 ========== */
.mobile-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 15px;
}

.mobile-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mobile-card-header .badge {
    background: rgba(255,255,255,0.3) !important;
    color: white;
    font-size: 0.75rem;
}

.mobile-card-title {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    margin: 0;
}

.mobile-card-body {
    padding: 12px 15px;
}

.mobile-card-footer {
    padding: 10px 15px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.mobile-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 4px;
    display: block;
}

.mobile-value {
    font-size: 0.85rem;
    color: #212529;
    margin-bottom: 10px;
}

.mobile-field {
    margin-bottom: 12px;
}

/* ========== 移动端表单优化 ========== */
@media (max-width: 768px) {
    .mobile-card .form-control,
    .mobile-card .form-select {
        font-size: 0.85rem;
        padding: 6px 10px;
    }
    
    .mobile-card .form-control-sm {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
    
    .mobile-card .btn-sm {
        font-size: 0.75rem;
        padding: 5px 10px;
    }
    
    .mobile-card .form-check-label {
        font-size: 0.8rem;
    }
    
    /* 表格隐藏 */
    .table-responsive.desktop-only {
        display: none !important;
    }
    
    /* 添加表单优化 */
    .card-body .row.g-3 > div {
        padding: 0 8px;
        margin-bottom: 10px;
    }
    
    .card-body .form-label {
        font-size: 0.85rem;
        margin-bottom: 4px;
    }
    
    .card-body .form-control,
    .card-body .form-select {
        font-size: 0.85rem;
    }
    
    .card-body .btn-custom {
        font-size: 0.85rem;
        padding: 8px 16px;
    }
}

@media (min-width: 769px) {
    .mobile-only {
        display: none !important;
    }
    
    .desktop-only {
        display: block !important;
    }
}

/* ========== 移动端按钮组优化 ========== */
.mobile-btn-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.mobile-btn-group .btn {
    flex: 1;
    min-width: 80px;
    font-size: 0.75rem;
    padding: 6px 10px;
}

/* ========== 移动端标签优化 ========== */
@media (max-width: 768px) {
    .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
}

/* ========== 移动端分隔线 ========== */
.mobile-divider {
    border-top: 1px solid #e9ecef;
    margin: 10px 0;
}

/* ========== 移动端信息行 ========== */
.mobile-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.mobile-info-row:last-child {
    border-bottom: none;
}

.mobile-info-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 600;
}

.mobile-info-value {
    font-size: 0.85rem;
    color: #212529;
}

/* ========== 移动端操作按钮 ========== */
.mobile-actions {
    display: flex;
    gap: 6px;
    margin-top: 10px;
}

.mobile-actions .btn {
    flex: 1;
    font-size: 0.75rem;
    padding: 6px 10px;
}
</style>
