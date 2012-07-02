<?php

$app->get('/get-json', function () use ($app) {
	var_dump($app->json('test'));
});

$app->get('/save-json/:string', function ($slug) use ($app) {
	if(isset($slug)) {
		# save data test
		$save = $app->json('test', array(
			'yo' => 'hai'
		));		
		# create message
		if ($save) {			
			$response = 'Saved';
		} else {
			$response = 'Error Saving';
		}		
		# show view
		$app->view('default', array (
			'msg' 	=> $response
		));				
	} else {
		return $app->E404;
	}
});