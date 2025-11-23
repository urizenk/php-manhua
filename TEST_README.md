# 单元测试与接口测试文档

## 📦 已创建的测试文件

### 配置文件
- `composer.json` - Composer依赖配置（包含PHPUnit）
- `phpunit.xml` - PHPUnit配置文件
- `tests/bootstrap.php` - 测试引导文件

### 单元测试 (tests/Unit/)
- `DatabaseTest.php` - 数据库类测试（10个测试方法）
- `SessionTest.php` - Session类测试（10个测试方法）
- `UploadTest.php` - 文件上传类测试（8个测试方法）

### API接口测试 (tests/API/)
- `AccessCodeApiTest.php` - 访问码验证API测试
- `MangaApiTest.php` - 漫画管理API测试

### 集成测试 (tests/Integration/)
- `MangaWorkflowTest.php` - 完整工作流程测试

### 测试脚本
- `run-tests.sh` - Linux/Mac测试运行脚本
- `run-tests.bat` - Windows测试运行脚本

---

## 🚀 快速开始

### 1. 安装依赖

```bash
composer install
```

### 2. 配置测试数据库

创建测试数据库：
```bash
mysql -u root -p -e "CREATE DATABASE manhua_test"
mysql -u root -p manhua_test < database/schema.sql
mysql -u root -p manhua_test < database/test_data.sql
```

### 3. 运行测试

**Windows:**
```cmd
run-tests.bat all
```

**Linux/Mac:**
```bash
chmod +x run-tests.sh
./run-tests.sh all
```

---

## 📊 测试覆盖

- **单元测试**: 28个测试方法
- **API测试**: 7个接口测试
- **集成测试**: 1个完整流程测试
- **总计**: 36+个测试用例

---

## 📖 使用说明

### 运行所有测试
```bash
./vendor/bin/phpunit
```

### 仅运行单元测试
```bash
./vendor/bin/phpunit --testsuite Unit
```

### 仅运行API测试
```bash
./vendor/bin/phpunit --testsuite API
```

### 生成代码覆盖率
```bash
./vendor/bin/phpunit --coverage-html coverage
```

查看报告：打开 `coverage/index.html`
