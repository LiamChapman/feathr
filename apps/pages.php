<?php

$app->get('/log/check', function () use ($app) {
	echo 'huzzah!';	
})->user->auth();

$app->get('/log/set', function () use ($app) {
	echo 'session set';
})->user->set();

$app->get('/get/:string', function ($slug) use ($app) {	
	if(isset($slug)) {
		#$app->json_path = '/';
		var_dump($app->json($slug));
	}
});

$app->get('/save/:string', function ($slug) use ($app) {
	if(isset($slug)) {
		#$app->json_path = '/';
		$app->json($slug, array(
			'slug' => $slug
		));
	}
});