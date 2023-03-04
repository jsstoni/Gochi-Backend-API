<?php
namespace App;
class Router
{
	private $main = "/";
	private $routes = [];
	private $group = "";
	private $request = null;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->readRoutes();
	}

	public function basePath($path = "") {
		define('APP_BASE_PATH', '/' . $path);
		$this->main = APP_BASE_PATH;
	}

	public function addRoute($method, $path, $handler)
	{
		$path = $this->group != '' ? $this->group . $path : $path;
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
		$urlParts = parse_url($url);
		$pathWithQuery = $urlParts['path'] . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');
		foreach ($this->routes as $route) {
			if ($route['method'] == $method) {
				$path = $this->main . $route['path'];
				$pattern = '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '(\?.*)?$#';
				if (preg_match($pattern, $pathWithQuery, $matches)) {
					$params = array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));
					$this->request->setParams($params);
					return call_user_func($route['handler'], $this->request->getParams());
				}
			}
		}
		return null;
	}
}