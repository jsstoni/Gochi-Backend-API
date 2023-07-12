<?php
use App\Router;
use App\DB;

Router::post('/connect/t/:id/:ecommerce', function($req) {
	$params = $req['params'];
	switch ($params['ecommerce']) {
		case 'woocommerce':
			$data = var_export($req, true);
			file_put_contents("text.txt", $data);
			break;
		case 'prestashop':
			break;
		case 'jumpseller':
			break;
		case 'komercia':
			break;
	}
	echo "Check";
});