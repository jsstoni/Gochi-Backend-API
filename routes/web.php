<?php

use App\Router;
use App\DB;

function codificar($id)
{
    // No se utiliza IV en ECB, así que no es necesario generarlo
    return base64_encode(openssl_encrypt(
        $id, // string a codificar
        'aes-256-ecb',
        'cE&ED#24=BE&C937E.=8',
        true
    ));
}

Router::get('/', function () {
	echo json_encode(['success' => 1]);
});

Router::post('/login', function ($req) {
	$email = $req['body']['email'];
	$password = $req['body']['password'];
	if (!empty($email) && !empty($password)) {
		$db = new DB();
		$result = $db->exec("SELECT u.id, u.password, SUM(b.amount) AS amount FROM users AS u LEFT JOIN wallets AS b ON (b.user_id = u.id) WHERE u.email = ? GROUP BY b.user_id LIMIT 1", [$email]);
		if ($result->num_rows) {
			$rows = $result->fetch_array(MYSQLI_ASSOC);
			if (password_verify($password, $rows['password'])) {
				$response = array('message' => array('token' => codificar($rows['id']), 'amount' => $rows['amount']));
			} else {
				$response = array('error' => 'Contraseña incorrecta');
			}
		} else {
			$response = array('error' => 'Email no existe');
		}
		$result->close();
	} else {
		$response = array('error' => 'Complete formulario');
	}
	echo json_encode($response);
});

Router::post('/register', function ($req) {
	$email = $req['body']['email'];
	$password = password_hash($req['body']['password'], PASSWORD_DEFAULT);
	if (!empty($email) && !empty($password) && !empty($req['body']['repass'])) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			if ($req['body']['password'] === $req['body']['repass']) {
				$db = new DB();
				$result = $db->exec("INSERT INTO users (email, password) VALUES ('{$email}', '{$password}')");
				$result->close();
				$response = array('message' => 'Usuario registrado');
			} else {
				$response = array('error' => 'Contraseñas no son iguales');
			}
		} else {
			$response = array('error' => 'Email invalido');
		}
	} else {
		$response = array('error' => 'Complete formulario');
	}
	echo json_encode($response);
});
