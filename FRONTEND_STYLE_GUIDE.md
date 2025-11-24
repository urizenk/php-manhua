# 🎨 前端样式自定义指南

## 📍 样式文件位置

**CSS样式在PHP视图文件中：**
```
views/frontend/index.php
```

样式代码在文件的 `$customCss` 变量中（第7-180行左右）

---

## ✅ 已完成的修改

### 1. 移动端双列布局
- ✅ 屏幕宽度 ≤ 768px 时显示2列
- ✅ 卡片间距缩小为15px
- ✅ 图标尺寸调整为60x60px
- ✅ 字体大小适配移动端

---

## 🎨 如何自定义样式

### 方法1：直接修改PHP文件中的CSS（推荐）

**文件位置：**
```
c:\Users\123\Desktop\jd\php-manhua\views\frontend\index.php
```

**找到这段代码（第6行开始）：**
```php
$customCss = '
<style>
    /* 这里是所有的CSS样式 */
    body {
        background: linear-gradient(135deg, #FFF5E6 0%, #FFE4CC 100%);
    }
    ...
</style>
';
```

**直接在这里修改CSS即可！**

---

## 🔧 常用样式调整

### 1. 修改主题颜色

**找到这些颜色值并替换：**
```css
/* 主色调 */
#FF6B35  /* 橙色 - 主要按钮、边框 */
#FF9966  /* 浅橙色 - 渐变、辅助色 */
#FFF5E6  /* 米黄色 - 背景色 */
#FFE4CC  /* 深米黄 - 背景渐变 */
```

**示例：改成蓝色系**
```css
body {
    background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
}
.welcome-card {
    background: linear-gradient(135deg, #42A5F5 0%, #1976D2 100%);
}
.module-icon {
    background: linear-gradient(135deg, #42A5F5 0%, #1976D2 100%);
}
```

---

### 2. 调整卡片大小

**找到 `.module-card` 样式：**
```css
.module-card {
    padding: 35px 20px;  /* 修改这里调整卡片内边距 */
}
```

**移动端卡片：**
```css
@media (max-width: 768px) {
    .module-card {
        padding: 25px 15px;  /* 修改移动端卡片大小 */
    }
}
```

---

### 3. 调整图标大小

**PC端图标：**
```css
.module-icon {
    width: 80px;   /* 修改宽度 */
    height: 80px;  /* 修改高度 */
    font-size: 2.5rem;  /* 修改图标内部大小 */
}
```

**移动端图标：**
```css
@media (max-width: 768px) {
    .module-icon {
        width: 60px;   /* 移动端宽度 */
        height: 60px;  /* 移动端高度 */
        font-size: 2rem;
    }
}
```

---

### 4. 调整网格布局

**PC端布局：**
```css
.module-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;  /* 卡片间距 */
}
```

**移动端双列布局：**
```css
@media (max-width: 768px) {
    .module-grid {
        grid-template-columns: repeat(2, 1fr);  /* 2列 */
        gap: 15px;  /* 间距 */
    }
}
```

**改成移动端单列：**
```css
@media (max-width: 768px) {
    .module-grid {
        grid-template-columns: 1fr;  /* 改成1列 */
        gap: 20px;
    }
}
```

---

### 5. 调整字体大小

**标题字体：**
```css
.welcome-title {
    font-size: 2.5rem;  /* PC端 */
}

@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.8rem;  /* 移动端 */
    }
}
```

**卡片标题：**
```css
.module-title {
    font-size: 1.3rem;  /* PC端 */
}

@media (max-width: 768px) {
    .module-title {
        font-size: 1.1rem;  /* 移动端 */
    }
}
```

---

### 6. 调整圆角

**卡片圆角：**
```css
.module-card {
    border-radius: 15px;  /* 修改这个值 */
}
```

**图标圆角：**
```css
.module-icon {
    border-radius: 20px;  /* 修改这个值 */
}
```

---

### 7. 调整阴影效果

**卡片阴影：**
```css
.module-card {
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1);
}

.module-card:hover {
    box-shadow: 0 15px 35px rgba(255, 107, 53, 0.3);
}
```

**图标阴影：**
```css
.module-icon {
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
}
```

---

### 8. 调整动画效果

**悬停上移距离：**
```css
.module-card:hover {
    transform: translateY(-10px);  /* 修改上移距离 */
}
```

**图标旋转角度：**
```css
.module-card:hover .module-icon {
    transform: scale(1.1) rotate(5deg);  /* 修改旋转角度 */
}
```

**动画速度：**
```css
.module-card {
    transition: all 0.3s ease;  /* 修改动画时长 */
}
```

---

## 📱 响应式断点

当前设置的断点：
```css
@media (max-width: 768px) {
    /* 移动端样式 */
}
```

**可以添加更多断点：**
```css
/* 平板 */
@media (max-width: 992px) and (min-width: 769px) {
    .module-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* 小屏手机 */
@media (max-width: 480px) {
    .module-grid {
        grid-template-columns: 1fr;
    }
}
```

---

## 🎨 完整的颜色方案示例

### 暖橙色系（当前）
```css
主色：#FF6B35
辅色：#FF9966
背景：#FFF5E6
```

### 清新蓝色系
```css
主色：#1976D2
辅色：#42A5F5
背景：#E3F2FD
```

### 优雅紫色系
```css
主色：#7B1FA2
辅色：#AB47BC
背景：#F3E5F5
```

### 活力绿色系
```css
主色：#388E3C
辅色：#66BB6A
背景：#E8F5E9
```

---

## 🚀 修改后如何生效

### 1. 保存文件
```
Ctrl + S 保存 index.php
```

### 2. 推送到Git
```bash
cd c:\Users\123\Desktop\jd\php-manhua
git add views/frontend/index.php
git commit -m "style: 自定义前端样式"
git push origin main:master
```

### 3. 服务器拉取
```bash
cd ~/php-manhua
git checkout -- views/frontend/index.php
git pull origin master
```

### 4. 刷新浏览器
```
按 Ctrl + F5 强制刷新
```

---

## 💡 调试技巧

### 1. 使用浏览器开发者工具
- 按 **F12** 打开开发者工具
- 点击"元素"标签
- 选择要修改的元素
- 在右侧"样式"面板实时调整CSS
- 满意后复制到代码中

### 2. 临时测试
在浏览器控制台执行：
```javascript
// 临时修改背景色
document.body.style.background = 'linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%)';

// 临时修改卡片颜色
document.querySelectorAll('.module-icon').forEach(el => {
    el.style.background = 'linear-gradient(135deg, #42A5F5 0%, #1976D2 100%)';
});
```

---

## 📝 注意事项

1. ✅ **修改前先备份**
2. ✅ **一次只改一个地方，测试后再继续**
3. ✅ **使用Git版本控制，方便回滚**
4. ✅ **移动端和PC端分别调整**
5. ✅ **注意颜色对比度，确保可读性**

---

## 🎯 快速参考

| 要修改的内容 | 找到的CSS类 | 位置 |
|------------|-----------|------|
| 背景颜色 | `body` | 第10行 |
| 欢迎卡片 | `.welcome-card` | 第17行 |
| 卡片样式 | `.module-card` | 第75行 |
| 图标样式 | `.module-icon` | 第86行 |
| 移动端布局 | `@media (max-width: 768px)` | 第48行 |
| 按钮样式 | `.btn-custom` | 第150行 |

---

**现在您可以自由调整样式了！修改 `views/frontend/index.php` 文件中的CSS即可！** 🎨
