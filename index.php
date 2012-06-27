<?php version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : ''; ?>
<?php require_once dirname(__FILE__) . '/feathr.php'; ?>
<?php

$app = new Feathr\Feathr('Feathr');

$app->get('/', function () use ($app) {
	$app->view('home');
});

# blog start
$app->group('blog', array(
		'/blog' => $app->get( function () use ($app) {
			echo 'this is the blog';
		}),
		'/blog/article/:int' => $app->get( function ($id) use ($app) {
			echo 'this is an article: ' . $id;
		})
	)
);
# blog end

$app->get('/:string, /:string/:int', function ($str, $int) use ($app) {
	$app->view(null, array(
		'slug' 		=> $str,
		'value'		=> $int
	));
});

$app->run();