<?php version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : ''; ?>
<?php require_once dirname(__FILE__) . '/feathr.php'; ?>
<?php


$app = new Feathr\Feathr('Feathr');

#$app->fetch(':any.css', function ($file) use ($app) {
#	$app->css($file);
#});

$app->fetch('/', function () use ($app) {
	$app->view('home');
});

$app->run();