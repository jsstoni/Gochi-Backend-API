<?php
namespace App;
class Router
{
	private $main = "/";
	private $routes = [];

	public function basePath($path = "") {
		define('APP_BASE_PATH', '/' . $path);
		$this->main = APP_BASE_PATH;
	}

	public function addRoute($method, $path, $handler)
	{
		$this->routes[] = [
			'method' => $method,
			'path' => $path,
			'handler' => $handler
		];
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