<?php
use App\Router;

Router::delete('/:id/:value', function($params) {
	var_dump($params);
});

Router::post('/:id/:value', function($params) {
	var_dump($params);
});

Router::put('/:id/:value', function($params) {
	var_dump($params);
});