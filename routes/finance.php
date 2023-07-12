<?php
use App\Router;
use App\DB;

Router::get('/', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$result = $db->exec("SELECT t.id, t.type, t.category, t.date, t.description, t.amount, b.id AS wallet_id, b.name AS wallet, t.recurrence, t.allow FROM transactions AS t LEFT JOIN wallets AS b ON (t.wallet_id = b.id) WHERE t.user_id = '{$token}' ORDER BY t.id DESC");
	if ($result->num_rows) {
		$rows = $result->fetch_all(MYSQLI_ASSOC);
		$response = array('finance' => $rows);
	}
	echo json_encode($response);
	$result->close();
}, 'auth');

Router::get('/filter', function($req) {
	$db = new DB();
	$where = [];
	$query = $req['query'];
	$token = $req['body']['hash'];
	$where[] = "t.user_id = ?";
	$params = [$token];
	
	if (!empty($query['wallet'])) {
		$where[] = "t.wallet_id = ?";
		$params[] = $query['wallet'];
	}
	
	if (!empty($query['desde'])) {
		$where[] = "t.date >= ?";
		$params[] = $query['desde'];
	}
	
	if (!empty($query['hasta'])) {
		$where[] = "t.date <= ?";
		$params[] = $query['hasta'];
	}
	
	if (!empty($query['desde']) && !empty($query['hasta'])) {
		$where[] = "(t.date >= ? AND t.date <= ?)";
		$params[] = $query['desde'];
		$params[] = $query['hasta'];
	}
	
	if (!empty($query['origen'])) {
		$where[] = "t.type = ?";
		$params[] = $query['origen'];
	}
	
	if (!empty($query['concepto']) && $query['origen'] != 0) {
		$where[] = "t.category = ?";
		$params[] = $query['concepto'];
	}

	$whereClause = implode(" AND ", $where);
	$sql = "SELECT t.id, t.type, t.category, t.date, t.description, t.amount, b.id AS wallet_id, b.name AS wallet, t.recurrence, t.allow FROM transactions AS t LEFT JOIN wallets AS b ON (t.wallet_id = b.id) WHERE {$whereClause} ORDER BY t.id DESC";
	$result = $db->exec($sql, $params);
	if ($result->num_rows > 0) {
		$rows = $result->fetch_all(MYSQLI_ASSOC);
		$response = array('finance' => $rows);
	}else {
		$response = array('finance' => []);
	}
	echo json_encode($response);
});

Router::post('/create', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$id_billetera = $req['body']['wallet'];
	$tipo = $req['body']['tipo'];
	$categoria = $req['body']['tag'];
	$fecha = $req['body']['fecha'];
	$descripcion = $req['body']['desc'];
	$monto = $req['body']['amount'];
	$permite = $req['body']['permite'] ?? 0;
	$recurrencia = $req['body']['recurrencia'] ?? 'none';
	$validateWallet = $db->exec("SELECT id FROM wallets WHERE id = ? AND user_id = ? LIMIT 1", [$id_billetera, $token]);
	if ($validateWallet->num_rows > 0) {
		$createTransaction = $db->exec("INSERT INTO transactions (user_id, wallet_id, type, category, date, description, amount, recurrence, allow) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [$token, $id_billetera, $tipo, $categoria, $fecha, $descripcion, $monto, $recurrencia, $permite]);
		if (!$createTransaction) {
			if ($tipo == 1) {
				$updateWalletAmount = "UPDATE wallets SET amount = amount + ? WHERE id = ? AND user_id = ?";
			}else {
				$updateWalletAmount = "UPDATE wallets SET amount = amount - ? WHERE id = ? AND user_id = ?";
			}
			if (!$db->exec($updateWalletAmount, [$monto, $id_billetera, $token])) {
				$response = array('message' => 'Se creo la nueva transacción');
			}else {
				$response = array('error' => 'Error al actualizar el monto en la billetera');
			}
		}else {
			$response = array('error' => 'Error al crear transacción');
		}
	}else {
		$response = array('error' => 'La billetera selecciona no te pertenece');
	}
	echo json_encode($response);
}, 'auth', 'checkBody');

Router::post('/update', function($req) {
	$db = new DB();
	$token = $req['body']['hash'];
	$id_billetera = $req['body']['wallet'];
	$tipo = $req['body']['tipo'];
	$categoria = $req['body']['tag'];
	$fecha = $req['body']['fecha'];
	$descripcion = $req['body']['desc'];
	$monto = $req['body']['amount'];
	$recurrencia = $req['body']['recurrencia'] ?? 'none';
	$permite = $req['body']['permite'] ?? 0;
	$id = $req['body']['id'];

	//validar la billetera
	$validateWallet = $db->exec("SELECT id FROM wallets WHERE id = ? AND user_id = ? LIMIT 1", [$id_billetera, $token]);
	if ($validateWallet->num_rows < 1) {
		echo json_encode(array('error' => 'La billetera seleccionada no te pertenece'));
		return;
	}

	//validar existencia de transaccion
	$oldTransaction = $db->exec("SELECT wallet_id, amount, type FROM transactions WHERE id = ? AND user_id = ? LIMIT 1", [$id, $token]);
	if ($oldTransaction->num_rows < 1) {
		echo json_encode(array('error' => 'No tienes permiso para editar'));
		return;
	}

	$rows = $oldTransaction->fetch_all(MYSQLI_ASSOC);
	$oldAmount = $rows[0]['amount'];
	$oldWallet = $rows[0]['wallet_id'];
	$oldType = $rows[0]['type'];
	$oldOperation = $oldType == 1 ? " - " : " + ";
	$newOperation = $tipo == 1 ? " + " : " - ";

	if ($oldWallet == $id_billetera) {
		$updateWalletAmount = $db->exec("UPDATE wallets SET amount = amount {$oldOperation} ? {$newOperation} ? WHERE id = ? AND user_id = ?", [$oldAmount, $monto, $id_billetera, $token]);
	}else {
		$updateWalletAmount = $db->exec("UPDATE wallets SET amount = CASE WHEN id = '{$oldWallet}' THEN amount {$oldOperation} ? WHEN id = '{$id_billetera}' THEN amount {$newOperation} ? END WHERE id IN(".implode(",", [$oldWallet, $id_billetera]).") AND user_id = ?", [$oldAmount, $monto, $token]);
	}

	if ($updateWalletAmount) {
		 echo json_encode(array('error' => 'Hubo un error al actualizar el monto'));
		 return;
	}

	$updateTransaction = $db->exec("UPDATE transactions SET wallet_id = ?, type = ?, category = ?, date = ?, description = ?, amount = ?, recurrence = ?, allow = ? WHERE id = ? AND user_id = ?", [$id_billetera, $tipo, $categoria, $fecha, $descripcion, $monto, $recurrencia, $permite, $id, $token]);
	if (!$updateTransaction) {
		echo json_encode(array('message' => 'transacción actualizada correctamente'));
		return;
	}

	echo json_encode(array('error' => 'Hubo un error al actualizar'));
}, 'auth', 'checkBody');