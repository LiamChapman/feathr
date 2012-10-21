<?php version_compare(PHP_VERSION, '5.4') >= 0 ? require_once __DIR__ . '/5.4/feathr.php' : require_once __DIR__ . '/feathr.php'; ?>
<?php

$app  = new Feathr\FeathrApp('Feathr');

# less
$app->get(':any.css', function ($file) {
	$less = new Feathr\Extend\Less;
	$less->parse($file);
});

# home page
$app->get('/', function () use ($app) {
	$app->view('home');
});

# pages/default app
$app->application('pages');

# go, go, go!
$app->run();