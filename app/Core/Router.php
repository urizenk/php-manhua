<?php
/**
 * C2-路由模块
 * 提供URL路由分发和伪静态处理
 */

namespace App\Core;

class Router
{
    private $routes = [];
    private $notFoundCallback = null;

    /**
     * 添加GET路由
     */
    public function get($pattern, $callback)
    {
        $this->addRoute('GET', $pattern, $callback);
    }

    /**
     * 添加POST路由
     */
    public function post($pattern, $callback)
    {
        $this->addRoute('POST', $pattern, $callback);
    }

    /**
     * 添加路由（支持GET和POST）
     */
    public function any($pattern, $callback)
    {
        $this->addRoute('GET|POST', $pattern, $callback);
    }

    /**
     * 添加路由规则
     */
    private function addRoute($method, $pattern, $callback)
    {
        $this->routes[] = [
            'method'   => $method,
            'pattern'  => $pattern,
            'callback' => $callback
        ];
    }

    /**
     * 设置404回调
     */
    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * 执行路由匹配
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // 移除base path（如果部署在子目录）
        $basePath = $this->getBasePath();
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        $requestUri = $requestUri ?: '/';

        foreach ($this->routes as $route) {
            // 检查请求方法
            if (!preg_match('/^' . $route['method'] . '$/i', $requestMethod)) {
                continue;
            }

            // 转换路由模式为正则表达式
            $pattern = $this->convertPattern($route['pattern']);
            
            // 匹配路由
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // 移除完整匹配
                
                // 执行回调
                return $this->executeCallback($route['callback'], $matches);
            }
        }

        // 未找到匹配路由
        return $this->execute404();
    }

    /**
     * 转换路由模式为正则表达式
     * 支持：/user/:id -> /user/(\d+)
     *      /post/:slug -> /post/([\w-]+)
     */
    private function convertPattern($pattern)
    {
        // 转义斜杠
        $pattern = str_replace('/', '\/', $pattern);
        
        // 转换参数占位符
        $pattern = preg_replace('/\:(\w+)/', '([^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * 执行回调函数
     */
    private function executeCallback($callback, $params = [])
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        // 支持 Controller@method 格式
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controller, $method) = explode('@', $callback);
            
            // 完整控制器类名
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                if (method_exists($instance, $method)) {
                    return call_user_func_array([$instance, $method], $params);
                }
            }
        }

        return $this->execute404();
    }

    /**
     * 执行404处理
     */
    private function execute404()
    {
        header('HTTP/1.0 404 Not Found');
        
        if ($this->notFoundCallback && is_callable($this->notFoundCallback)) {
            return call_user_func($this->notFoundCallback);
        }

        echo '<h1>404 Not Found</h1>';
        exit;
    }

    /**
     * 获取基础路径（如果部署在子目录）
     */
    private function getBasePath()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = str_replace('\\', '/', dirname($scriptName));
        return $basePath === '/' ? '' : $basePath;
    }

    /**
     * 生成URL
     */
    public static function url($path = '', $params = [])
    {
        global $config;
        $baseUrl = rtrim($config['app']['base_url'], '/');
        $path = ltrim($path, '/');
        
        $url = $baseUrl . '/' . $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    /**
     * 重定向
     */
    public static function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}


