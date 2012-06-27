<?php

$app->get('/testing', function () use ($app) {
	$app->view();
});