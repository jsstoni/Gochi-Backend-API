<?php

namespace App;

use mysqli;

class DB
{
	private $connect;
	public function __construct()
	{
		$config = include_once __DIR__ . '/../config/db.php';
		$this->connect = new mysqli($config['server'], $config['user'], $config['password'], $config['database']);
		if ($this->connect->connect_errno) {
			die("La conexión a la base de datos falló: " . $this->connect->connect_error);
		}
	}

	public function exec($query, $params = [])
	{
		$stmt = $this->connect->prepare($query);
		if (!$stmt) {
			return $this->connect->error;
		}
		if ($params) {
			$types = str_repeat('s', count($params));
			$stmt->bind_param($types, ...$params);
		}
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		return $result;
	}

	public function __destruct()
	{
		$this->connect->close();
	}
}
