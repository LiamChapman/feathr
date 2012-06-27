<?php version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : ''; ?>
<?php require_once __DIR__ . '/feathr.php'; ?>
<?php

$app  = new Feathr\Feathr('Feathr');

# - experimenting with components - not working yet.
#$less = new Feathr\Extend\less();
#$less->parse(__DIR__.'/media/styles/feathr');

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