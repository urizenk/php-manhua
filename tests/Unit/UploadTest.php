<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Upload;

/**
 * Upload类单元测试
 */
class UploadTest extends TestCase
{
    private $upload;
    private $config;
    private $testDir;

    protected function setUp(): void
    {
        $this->config = require __DIR__ . '/../bootstrap.php';
        $this->upload = new Upload($this->config['upload']);
        
        // 创建测试目录
        $this->testDir = APP_PATH . '/public/uploads/test_' . time();
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
    }

    /**
     * 测试配置加载
     */
    public function testConfigLoaded()
    {
        $this->assertInstanceOf(Upload::class, $this->upload);
    }

    /**
     * 测试文件类型验证 - 允许的类型
     */
    public function testAllowedFileType()
    {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        
        foreach ($allowedTypes as $type) {
            $filename = 'test.' . $type;
            $this->assertTrue(
                in_array($type, $this->config['upload']['allowed_types']),
                "{$type} 应该是允许的文件类型"
            );
        }
    }

    /**
     * 测试文件大小限制
     */
    public function testFileSizeLimit()
    {
        $maxSize = $this->config['upload']['max_size'];
        
        $this->assertEquals(5 * 1024 * 1024, $maxSize, '最大文件大小应为5MB');
    }

    /**
     * 测试生成唯一文件名
     */
    public function testGenerateUniqueFilename()
    {
        $filename1 = uniqid() . '_test.jpg';
        $filename2 = uniqid() . '_test.jpg';
        
        $this->assertNotEquals($filename1, $filename2, '生成的文件名应该是唯一的');
    }

    /**
     * 测试文件扩展名提取
     */
    public function testGetFileExtension()
    {
        $testCases = [
            'test.jpg' => 'jpg',
            'image.PNG' => 'png',
            'photo.jpeg' => 'jpeg',
            'pic.WEBP' => 'webp',
        ];

        foreach ($testCases as $filename => $expectedExt) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $this->assertEquals($expectedExt, $ext);
        }
    }

    /**
     * 测试验证文件类型 - 不允许的类型
     */
    public function testInvalidFileType()
    {
        $invalidTypes = ['exe', 'php', 'js', 'html', 'txt'];
        $allowedTypes = $this->config['upload']['allowed_types'];
        
        foreach ($invalidTypes as $type) {
            $this->assertNotContains(
                $type,
                $allowedTypes,
                "{$type} 不应该是允许的文件类型"
            );
        }
    }

    /**
     * 测试上传路径生成
     */
    public function testUploadPathGeneration()
    {
        $savePath = $this->config['upload']['save_path'];
        $subdir = 'covers';
        $expectedPath = $savePath . $subdir;
        
        $this->assertIsString($savePath);
        $this->assertStringStartsWith('/public/uploads/', $savePath);
    }

    /**
     * 测试缩略图配置
     */
    public function testThumbnailConfig()
    {
        $this->assertTrue($this->config['upload']['create_thumb']);
        $this->assertEquals(300, $this->config['upload']['thumb_width']);
        $this->assertEquals(400, $this->config['upload']['thumb_height']);
    }

    protected function tearDown(): void
    {
        // 清理测试目录
        if (is_dir($this->testDir)) {
            $files = glob($this->testDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->testDir);
        }
    }
}
