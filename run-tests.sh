#!/bin/bash

##############################################
# PHP漫画管理系统测试运行脚本
# 用法：./run-tests.sh [选项]
##############################################

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 显示帮助信息
show_help() {
    echo "用法: ./run-tests.sh [选项]"
    echo ""
    echo "选项:"
    echo "  all          运行所有测试"
    echo "  unit         仅运行单元测试"
    echo "  api          仅运行API测试"
    echo "  integration  仅运行集成测试"
    echo "  coverage     生成代码覆盖率报告"
    echo "  help         显示此帮助信息"
    echo ""
    echo "示例:"
    echo "  ./run-tests.sh all"
    echo "  ./run-tests.sh unit"
    echo "  ./run-tests.sh coverage"
}

# 检查依赖
check_dependencies() {
    echo -e "${YELLOW}检查依赖...${NC}"
    
    # 检查PHP
    if ! command -v php &> /dev/null; then
        echo -e "${RED}错误: 未找到PHP${NC}"
        exit 1
    fi
    
    # 检查Composer
    if ! command -v composer &> /dev/null; then
        echo -e "${RED}错误: 未找到Composer${NC}"
        exit 1
    fi
    
    # 检查vendor目录
    if [ ! -d "vendor" ]; then
        echo -e "${YELLOW}安装依赖...${NC}"
        composer install
    fi
    
    echo -e "${GREEN}✓ 依赖检查完成${NC}"
}

# 准备测试数据库
prepare_test_db() {
    echo -e "${YELLOW}准备测试数据库...${NC}"
    
    # 读取数据库配置
    DB_HOST=${DB_HOST:-localhost}
    DB_USER=${DB_USER:-root}
    DB_PASS=${DB_PASS:-}
    DB_NAME=manhua_test
    
    # 创建测试数据库
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS $DB_NAME;"
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    # 导入表结构
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql
    
    # 导入测试数据
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/test_data.sql
    
    echo -e "${GREEN}✓ 测试数据库准备完成${NC}"
}

# 运行所有测试
run_all_tests() {
    echo -e "${YELLOW}运行所有测试...${NC}"
    ./vendor/bin/phpunit
}

# 运行单元测试
run_unit_tests() {
    echo -e "${YELLOW}运行单元测试...${NC}"
    ./vendor/bin/phpunit --testsuite Unit
}

# 运行API测试
run_api_tests() {
    echo -e "${YELLOW}运行API测试...${NC}"
    ./vendor/bin/phpunit --testsuite API
}

# 运行集成测试
run_integration_tests() {
    echo -e "${YELLOW}运行集成测试...${NC}"
    ./vendor/bin/phpunit --testsuite Integration
}

# 生成代码覆盖率
run_coverage() {
    echo -e "${YELLOW}生成代码覆盖率报告...${NC}"
    ./vendor/bin/phpunit --coverage-html coverage
    echo -e "${GREEN}✓ 覆盖率报告已生成到 coverage/ 目录${NC}"
}

# 主函数
main() {
    # 检查依赖
    check_dependencies
    
    # 准备测试数据库
    # prepare_test_db
    
    # 根据参数执行
    case "${1:-all}" in
        all)
            run_all_tests
            ;;
        unit)
            run_unit_tests
            ;;
        api)
            run_api_tests
            ;;
        integration)
            run_integration_tests
            ;;
        coverage)
            run_coverage
            ;;
        help)
            show_help
            ;;
        *)
            echo -e "${RED}未知选项: $1${NC}"
            show_help
            exit 1
            ;;
    esac
}

# 执行主函数
main "$@"
