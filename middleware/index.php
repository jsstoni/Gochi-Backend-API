<?php
function check($params) {
	return ['error' => 'chupalo'];
}

function auth($params) {
	$headers = apache_request_headers();
	if (!isset($headers['Authorization'])) {
		return ['error' => 'No tienes permisos'];
	}
}

function checkBody($params) {
	if (isset($params['body'])) {
		foreach ($params['body'] as $param) {
			if (isset($param) && trim($param) === '') {
				return ['error' => 'complete formulario'];
			}
		}
	}
}