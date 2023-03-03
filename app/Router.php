<?php
namespace App;
class Router
{
	private $main = "/";
	private $routes = [];
	private $group = "";

	public function __construct()
	{
		$this->readRoutes();
	}

	public function basePath($path = "") {
		define('APP_BASE_PATH', '/' . $path);
		$this->main = APP_BASE_PATH;
	}

	public function addRoute($method, $path, $handler)
	{
		$path = $this->group == '' ? $this->group . $path : $this->group;
		$this->routes[] = [
			'method' => $method,
			'path' => $path,
			'handler' => $handler
		];
	}

	public function get($path, $handler)
	{
		$this->addRoute('GET', $path, $handler);
	}

	public function post($path, $handler)
	{
		$this->addRoute('POST', $path, $handler);
	}

	public function put($path, $handler)
	{
		$this->addRoute('PUT', $path, $handler);
	}

	public function delete($path, $handler)
	{
		$this->addRoute('DELETE', $path, $handler);
	}

	public function readRoutes()
	{
		$files = glob(dirname(__DIR__) . '/routes/*.php');
		foreach ($files as $file) {
			if (basename($file) == "web.php") {
				$this->group = "";
				include $file;
			}else {
				$this->group = '/' . str_replace(".php", "", basename($file));
				include $file;
			}
		}
	}

	public function dispatch($method, $url)
	{
		foreach ($this->routes as $route) {
			if ($route['method'] == $method) {
				$path = $this->main . $route['path'];
				$pattern = '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '$#';
				if (preg_match($pattern, $url, $matches)) {
					$params = array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));
					return call_user_func($route['handler'], $params);
				}
			}
		}
		return null;
	}
}