<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Core\Session;

/**
 * 漫画管理完整流程集成测试
 */
class MangaWorkflowTest extends TestCase
{
    private $db;
    private $session;
    private $config;
    private $testMangaId;

    protected function setUp(): void
    {
        $this->config = require __DIR__ . '/../bootstrap.php';
        $this->db = Database::getInstance($this->config['database']);
        $this->session = new Session($this->config['session']);
        $this->session->setDatabase($this->db);
    }

    /**
     * 测试完整的漫画管理工作流程
     */
    public function testCompleteManagaWorkflow()
    {
        // Step 1: 管理员登录
        $this->adminLogin();
        
        // Step 2: 创建标签
        $tagId = $this->createTag();
        
        // Step 3: 添加漫画
        $mangaId = $this->addManga($tagId);
        
        // Step 4: 查询漫画
        $manga = $this->getManga($mangaId);
        $this->assertNotNull($manga);
        
        // Step 5: 更新漫画
        $this->updateManga($mangaId);
        
        // Step 6: 验证更新
        $updated = $this->getManga($mangaId);
        $this->assertEquals('Updated Test Manga', $updated['title']);
        
        // Step 7: 删除漫画
        $this->deleteManga($mangaId);
        
        // Step 8: 验证删除
        $deleted = $this->getManga($mangaId);
        $this->assertNull($deleted);
        
        // Step 9: 清理标签
        $this->deleteTag($tagId);
        
        // Step 10: 登出
        $this->session->adminLogout();
        $this->assertFalse($this->session->isAdminLoggedIn());
    }

    /**
     * 管理员登录
     */
    private function adminLogin()
    {
        $this->session->adminLogin(1, 'admin');
        $this->assertTrue($this->session->isAdminLoggedIn());
    }

    /**
     * 创建测试标签
     */
    private function createTag()
    {
        $tagId = $this->db->insert('tags', [
            'type_id' => 1,
            'tag_name' => 'Integration Test Tag',
            'tag_type' => 'category',
            'sort_order' => 0
        ]);
        
        $this->assertIsInt($tagId);
        $this->assertGreaterThan(0, $tagId);
        
        return $tagId;
    }

    /**
     * 添加测试漫画
     */
    private function addManga($tagId)
    {
        $mangaId = $this->db->insert('mangas', [
            'type_id' => 1,
            'tag_id' => $tagId,
            'title' => 'Test Manga ' . time(),
            'resource_link' => 'https://test.com/link',
            'description' => 'Test Description',
            'sort_order' => 0
        ]);
        
        $this->assertIsInt($mangaId);
        $this->assertGreaterThan(0, $mangaId);
        
        return $mangaId;
    }

    /**
     * 查询漫画
     */
    private function getManga($mangaId)
    {
        return $this->db->queryOne(
            "SELECT * FROM mangas WHERE id = ?",
            [$mangaId]
        );
    }

    /**
     * 更新漫画
     */
    private function updateManga($mangaId)
    {
        $result = $this->db->update(
            'mangas',
            ['title' => 'Updated Test Manga'],
            'id = ?',
            [$mangaId]
        );
        
        $this->assertNotFalse($result);
    }

    /**
     * 删除漫画
     */
    private function deleteManga($mangaId)
    {
        $result = $this->db->delete('mangas', 'id = ?', [$mangaId]);
        $this->assertTrue($result);
    }

    /**
     * 删除标签
     */
    private function deleteTag($tagId)
    {
        $result = $this->db->delete('tags', 'id = ?', [$tagId]);
        $this->assertTrue($result);
    }
}
