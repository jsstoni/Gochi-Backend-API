<?php
namespace App;
use mysqli;
class DB
{
	private $connect;
	public function __construct()
	{
		$this->connect = new mysqli($_ENV['host'], $_ENV['user'], $_ENV['pass'], $_ENV['db']);
		if ($this->connect->connect_errno) {
			die("La conexión a la base de datos falló: ". $this->connect->connect_error);
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