<?php
namespace App;
class Request
{
	private $params = array();
	private $contentType = "";
	private $method = "";

	public function __construct()
	{
		$body = file_get_contents('php://input');
		$this->contentType = $_SERVER['CONTENT_TYPE'] ?? '';
		$this->params['query'] = $_GET ?? [];
		if ($this->contentType === "application/x-www-form-urlencoded" || strpos($this->contentType, 'multipart/form-data') !== false) {
			$this->processRequest($body);
		}else if ($this->contentType === 'application/json') {
			$this->params['body'] = json_decode($body, true);
		}
	}

	public function processRequest($body)
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method == 'PUT' || $method == 'DELETE') {
			//procesando
		}else {
			$this->params['body'] = $_POST;
			$this->params['files'] = $_FILES;
		}
	}

	public function setParams($params, $k = '')
	{
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$this->params['params'][$key] = $value;
			}
		}else {
			$this->params['params'][$k] = $params;
		}
	}

	public function getParams()
	{
		return $this->params;
	}
}