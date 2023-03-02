<?php
namespace App;
class Router
{
	private $main = "/";
	public function basePath($path = "/") {
		define('APP_BASE_PATH', '/' . $path . '/');
		$this->main = APP_BASE_PATH;
	}
}