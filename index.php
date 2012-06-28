<?php require_once __DIR__ . '/feathr.php'; ?>
<?php

$app  = new Feathr\Feathr('Feathr');

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

# home page
$app->get('/', function () use ($app) {
	$app->view('home');
});

# pages/default app
$app->application('pages');

# go, go, go!
$app->run();