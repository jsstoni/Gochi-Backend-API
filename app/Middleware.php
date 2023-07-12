<?php
namespace App;

class Middleware
{
	public function __construct()
	{
		$this->readMiddleware();
	}

	private function readMiddleware()
	{
		$files = glob(dirname(__DIR__) . '/middleware/*.php');
		foreach ($files as $file) {
			include $file;
		}
	}
}