<?php
/**
 * C4-文件上传模块
 * 提供图片上传、格式校验、缩略图生成
 */

namespace App\Core;

class Upload
{
    private $config = [];
    private $error = '';

    public function __construct($config = [])
    {
        $this->config = array_merge([
            'max_size'      => 5 * 1024 * 1024,  // 5MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
            'save_path'     => '/public/uploads/',
            'create_thumb'  => true,
            'thumb_width'   => 300,
            'thumb_height'  => 400,
        ], $config);
    }

    /**
     * 上传单个文件
     * @param array $file $_FILES中的文件数组
     * @param string $subDir 子目录（如 'covers'）
     * @return array|false ['path' => '', 'thumb_path' => '', 'filename' => ''] 或 false
     */
    public function uploadSingle($file, $subDir = '')
    {
        // 检查文件是否有效
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->error = '无效的文件';
            return false;
        }

        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // 检查文件大小
        if ($file['size'] > $this->config['max_size']) {
            $this->error = '文件大小超过限制（最大' . ($this->config['max_size'] / 1024 / 1024) . 'MB）';
            return false;
        }

        // 检查文件类型
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->config['allowed_types'])) {
            $this->error = '不支持的文件类型（仅支持：' . implode(', ', $this->config['allowed_types']) . '）';
            return false;
        }

        // 验证是否为真实图片
        if (!$this->isValidImage($file['tmp_name'])) {
            $this->error = '无效的图片文件';
            return false;
        }
        
        // 验证MIME类型
        if (!$this->validateMimeType($file)) {
            return false;
        }

        // 生成唯一文件名
        $filename = $this->generateFilename($ext);
        
        // 构建保存路径
        $savePath = $this->prepareSavePath($subDir);
        $fullPath = $savePath . $filename;

        // 移动上传文件
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            $this->error = '文件保存失败';
            return false;
        }

        // 生成缩略图
        $thumbPath = '';
        if ($this->config['create_thumb']) {
            $thumbPath = $this->createThumbnail($fullPath, $savePath, $filename);
        }

        // 返回相对路径（用于存储到数据库）
        $relativePath = $this->getRelativePath($fullPath);
        $relativeThumbPath = $thumbPath ? $this->getRelativePath($thumbPath) : '';

        return [
            'path'       => $relativePath,
            'thumb_path' => $relativeThumbPath,
            'filename'   => $filename,
            'size'       => $file['size'],
            'ext'        => $ext,
        ];
    }

    /**
     * 批量上传文件
     * @param array $files $_FILES['field_name']
     * @param string $subDir 子目录
     * @return array 成功上传的文件列表
     */
    public function uploadMultiple($files, $subDir = '')
    {
        $uploaded = [];

        // 规范化$files数组结构
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $result = $this->uploadSingle($file, $subDir);
            if ($result) {
                $uploaded[] = $result;
            }
        }

        return $uploaded;
    }

    /**
     * 删除文件
     * @param string $path 文件路径
     * @return bool
     */
    public function deleteFile($path)
    {
        $fullPath = APP_PATH . $path;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return true;
    }

    /**
     * 创建缩略图
     */
    private function createThumbnail($sourcePath, $savePath, $originalFilename)
    {
        // 检查GD扩展是否可用
        if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
            // GD扩展不可用，返回空字符串（不生成缩略图）
            return '';
        }

        $thumbFilename = 'thumb_' . $originalFilename;
        $thumbPath = $savePath . $thumbFilename;

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return '';
        }

        list($srcWidth, $srcHeight, $srcType) = $imageInfo;

        // 创建源图像
        switch ($srcType) {
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $srcImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return '';
        }

        // 计算缩略图尺寸（保持比例）
        $thumbWidth = $this->config['thumb_width'];
        $thumbHeight = $this->config['thumb_height'];

        $ratio = min($thumbWidth / $srcWidth, $thumbHeight / $srcHeight);
        $newWidth = round($srcWidth * $ratio);
        $newHeight = round($srcHeight * $ratio);

        // 创建缩略图
        $thumbImage = imagecreatetruecolor($newWidth, $newHeight);

        // 保持PNG透明度
        if ($srcType == IMAGETYPE_PNG || $srcType == IMAGETYPE_WEBP) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
        }

        // 重采样
        imagecopyresampled(
            $thumbImage, $srcImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $srcWidth, $srcHeight
        );

        // 保存缩略图
        $saved = false;
        switch ($srcType) {
            case IMAGETYPE_JPEG:
                $saved = imagejpeg($thumbImage, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                $saved = imagepng($thumbImage, $thumbPath, 8);
                break;
            case IMAGETYPE_WEBP:
                $saved = imagewebp($thumbImage, $thumbPath, 85);
                break;
        }

        // 释放内存
        imagedestroy($srcImage);
        imagedestroy($thumbImage);

        return $saved ? $thumbPath : '';
    }

    /**
     * 验证是否为真实图片
     */
    private function isValidImage($filePath)
    {
        $imageInfo = @getimagesize($filePath);
        return $imageInfo !== false;
    }
    
    /**
     * 验证MIME类型
     */
    private function validateMimeType($file)
    {
        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        // 使用finfo检测真实MIME类型
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedMimes)) {
                $this->error = '文件MIME类型不匹配（检测到：' . $mimeType . '）';
                return false;
            }
        }
        
        return true;
    }

    /**
     * 生成唯一文件名
     */
    private function generateFilename($ext)
    {
        return date('YmdHis') . '_' . uniqid() . '.' . $ext;
    }

    /**
     * 准备保存路径（创建目录）
     */
    private function prepareSavePath($subDir = '')
    {
        $basePath = APP_PATH . $this->config['save_path'];
        
        if ($subDir) {
            $basePath .= trim($subDir, '/') . '/';
        }

        // 按年月分目录
        $datePath = date('Y') . '/' . date('m') . '/';
        $fullPath = $basePath . $datePath;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        return $fullPath;
    }

    /**
     * 获取相对路径（相对于APP_PATH）
     */
    private function getRelativePath($fullPath)
    {
        return str_replace(APP_PATH, '', $fullPath);
    }

    /**
     * 获取上传错误信息
     */
    private function getUploadErrorMessage($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => '文件大小超过php.ini限制',
            UPLOAD_ERR_FORM_SIZE  => '文件大小超过表单限制',
            UPLOAD_ERR_PARTIAL    => '文件仅部分上传',
            UPLOAD_ERR_NO_FILE    => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时目录',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION  => 'PHP扩展阻止了文件上传',
        ];

        return $errors[$code] ?? '未知上传错误';
    }

    /**
     * 获取错误信息
     */
    public function getError()
    {
        return $this->error;
    }
}


