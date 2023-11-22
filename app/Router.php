<?php

namespace App;

class Router
{
	private $main = "/";
	private static $routes = [];
	private static $group = "";
	private $request = null;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->readRoutes();
	}

	public function basePath($path = "")
	{
		define('APP_BASE_PATH', $path);
		$this->main = APP_BASE_PATH == '/' ? '' : APP_BASE_PATH;
	}

	private function readRoutes()
	{
		$files = glob(dirname(__DIR__) . '/routes/*.php');
		foreach ($files as $file) {
			if (basename($file) == "web.php") {
				self::$group = "";
				include $file;
			} else {
				self::$group = '/' . str_replace(".php", "", basename($file));
				include $file;
			}
		}
	}

	public static function get($path, $handler, ...$middleware)
	{
		self::addRoute('GET', $path, $handler, $middleware);
	}

	public static function post($path, $handler, ...$middleware)
	{
		self::addRoute('POST', $path, $handler, $middleware);
	}

	public static function put($path, $handler, ...$middleware)
	{
		self::addRoute('PUT', $path, $handler, $middleware);
	}

	public static function delete($path, $handler, ...$middleware)
	{
		self::addRoute('DELETE', $path, $handler, $middleware);
	}

	private static function addRoute($method, $path, $handler, $middleware)
	{
		$path = (self::$group != '') ? (($path != '/') ? (self::$group . $path) : self::$group) : $path;
		self::$routes[] = [
			'method' => $method,
			'path' => $path,
			'handler' => $handler,
			'middleware' => $middleware
		];
	}

	public function dispatch($method, $url)
	{
		$urlParts = parse_url($url);
		$pathWithQuery = $urlParts['path'] . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');
		foreach (self::$routes as $route) {
			if ($route['method'] == $method) {
				$path = $this->main . $route['path'];
				$pattern = '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '(/?)?(\?.*)?$#';
				if (preg_match($pattern, $pathWithQuery, $matches)) {
					$params = array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));
					$this->request->setParams($params);
					$params = $this->request->getParams();
					foreach ($route['middleware'] as $middleware) {
						if (function_exists($middleware)) {
							$response = call_user_func($middleware, $params);
							if (isset($response['error'])) {
								echo json_encode($response);
								return;
							}
						}
					}
					return call_user_func($route['handler'], $params);
				}
			}
		}
		return null;
	}
}
