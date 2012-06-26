<?php version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : ''; ?>
<?php require_once dirname(__FILE__) . '/feathr.php'; ?>
<?php

$app = new Feathr\Feathr('Feathr');

$app->fetch('/', function () use ($app) {
	var_dump($validate);
	$app->view('home');
});

$app->run();