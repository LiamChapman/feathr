<?php

$app->get('/testing', function () use ($app) {
	$app->view('home', array(
		'str' => 'This is the testing application'
	));
});