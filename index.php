<?php require_once __DIR__ . '/feathr.php'; ?>
<?php

$app  = new Feathr\Feathr('Feathr');

$app->get('/', function () use ($app) {
	$app->view('home');
});

$app->application('test');

$app->get('/:string', function ($str) use ($app) {
	$app->view('home', array(
		'str' => $str
	));
});

# less
$app->get(':any.css', function ($file) {
	$less = new Feathr\Extend\Less;
	$less->parse($file);
});

# Sprockets - not working properly yet - speak to stu when ready.
# Also uncomment rule in .htaccess.
/*
$app->get(':any.js', function ($file) {
	$sprock = new Feathr\Extend\Sprockets;
	$sprock->parse($file);
});
*/

$app->run();