
<?php version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : ''; ?>
<?php require_once dirname(__FILE__) . '/feathr.php'; ?>
<?php


$app = new Feathr\Feathr('Test Application');

$app->fetch('/', function () use ($app) {
	$app->view('home');
});

$app->fetch('/test', function () use ($app) {
	echo 'This is a test page';
});

$app->fetch('/test/:string/:any', function ($str, $int) use ($app) {
	echo 'This is a test parameter page. Here are the passed vars:<br />';
	echo $str . "<br />";
	echo $int;
});

$app->run();