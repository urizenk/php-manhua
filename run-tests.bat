@echo off
REM ##############################################
REM PHP漫画管理系统测试运行脚本 (Windows)
REM 用法：run-tests.bat [选项]
REM ##############################################

setlocal enabledelayedexpansion

REM 显示帮助信息
if "%1"=="help" goto :show_help
if "%1"=="/?" goto :show_help
if "%1"=="-h" goto :show_help

REM 检查依赖
echo 检查依赖...
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo 错误: 未找到PHP
    exit /b 1
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo 错误: 未找到Composer
    exit /b 1
)

if not exist "vendor" (
    echo 安装依赖...
    call composer install
)

echo 依赖检查完成
echo.

REM 根据参数执行
if "%1"=="" goto :run_all
if "%1"=="all" goto :run_all
if "%1"=="unit" goto :run_unit
if "%1"=="api" goto :run_api
if "%1"=="integration" goto :run_integration
if "%1"=="coverage" goto :run_coverage

echo 未知选项: %1
goto :show_help

:run_all
echo 运行所有测试...
call vendor\bin\phpunit.bat
goto :end

:run_unit
echo 运行单元测试...
call vendor\bin\phpunit.bat --testsuite Unit
goto :end

:run_api
echo 运行API测试...
call vendor\bin\phpunit.bat --testsuite API
goto :end

:run_integration
echo 运行集成测试...
call vendor\bin\phpunit.bat --testsuite Integration
goto :end

:run_coverage
echo 生成代码覆盖率报告...
call vendor\bin\phpunit.bat --coverage-html coverage
echo 覆盖率报告已生成到 coverage\ 目录
goto :end

:show_help
echo 用法: run-tests.bat [选项]
echo.
echo 选项:
echo   all          运行所有测试 (默认)
echo   unit         仅运行单元测试
echo   api          仅运行API测试
echo   integration  仅运行集成测试
echo   coverage     生成代码覆盖率报告
echo   help         显示此帮助信息
echo.
echo 示例:
echo   run-tests.bat all
echo   run-tests.bat unit
echo   run-tests.bat coverage
goto :end

:end
endlocal
