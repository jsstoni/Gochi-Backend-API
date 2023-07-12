<?php
use App\Router;
use App\DB;

Router::get('/', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$result = $db->exec("SELECT * FROM wallets WHERE user_id = ?", [$token]);
	$reports = $db->exec("SELECT t.type, IFNULL(SUM(tr.amount), 0) AS total, IFNULL(COUNT(tr.id), 0) AS quantity FROM (SELECT 1 AS type UNION SELECT 2 AS type) t LEFT JOIN transactions tr ON t.type = tr.type AND tr.user_id = ? GROUP BY t.type", [$token]);
	$fecha = date("Y");
	$statistics = $db->exec("SELECT MONTH(date) AS month, SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) AS Ingresos, SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) AS Gastos FROM transactions WHERE user_id = ? AND YEAR(date) = YEAR(CURDATE()) GROUP BY YEAR(date), MONTH(date)", [$token]);
	$data = array();
	if ($reports->num_rows > 0) {
		$data['reports'] = $reports->fetch_all(MYSQLI_ASSOC);
	}
	if ($result->num_rows > 0) {
		$data['wallets'] = $result->fetch_all(MYSQLI_ASSOC);
	}
	if ($statistics->num_rows > 0) {
		$data['statistics'] = $statistics->fetch_all(MYSQLI_ASSOC);
	}
	echo json_encode($data);
	$result->close();
}, 'auth');

Router::post('/create', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$nombre = $req['body']['tag'];
	$monto = $req['body']['amount'];
	$result = $db->exec("INSERT INTO wallets (user_id, name, amount) VALUES (?, ?, ?)", [$token, $nombre, $monto]);
	if (!$result) {
		$response = array('message' => 'Billetera creada');
	}else {
		$response = array('error' => 'Hubo un error');
	}
	echo json_encode($response);
}, 'auth', 'checkBody');

Router::post('/update', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$nombre = $req['body']['tag'];
	$monto = $req['body']['amount'];
	$id = $req['body']['id'];
	$result = $db->exec("UPDATE wallets SET name = ?, amount = ? WHERE id = ? AND user_id = ?", [$nombre, $monto, $id, $token]);
	if (!$result) {
		$response = array('message' => 'Billetera actualizada');
	}else {
		$response = array('error' => 'Hubo un error');
	}
	echo json_encode($response);
	$result->close();
}, 'auth', 'checkBody');

Router::delete('/remove/:id', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$id = $req['params']['id'];
	$result = $db->exec("DELETE FROM wallets WHERE id = '{$id}' AND user_id = '{$token}'");
	if (!$result) {
		$response = array('message' => 'Billetera borrado con éxito');
	}else {
		$response = array('error' => 'Hubo un error');
	}
	echo json_encode($response);
}, 'auth');