<?php
namespace App;
class Router
{
	private $main = "/";
	private $routes = [];

	public function basePath($path = "/") {
		define('APP_BASE_PATH', '/' . $path . '/');
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
}