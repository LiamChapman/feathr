<?php version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : ''; ?>
<?php require_once dirname(__FILE__) . '/feathr.php'; ?>
<?php

$app = new Feathr\Feathr('Feathr');

$app->get('/', function () use ($app) {
	$app->view('home');
});

$app->application('test');

$app->get('/:string', function ($str) use ($app) {
	$app->view('home', array(
		'str' => $str
	));
});

# removed test routes.

$app->run();