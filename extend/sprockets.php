<?php

namespace Feathr\Extend;
use Feathr;

class Sprockets extends Feathr\FeathrApp {
	# put this in .htaccess -> 
	# RewriteRule (.*)\.js$ index.php?url=$1.js [L,QSA]
	# put it after Rewrite Engine on
	public function parse ($file, $js_folder = '/media/scripts', $debug = true) {
		header('Content-Type: application/javascript');
		# https://github.com/stuartloxton/php-sprockets/
		require_once __DIR__.'/thirdparty/phpsprocket.php';
		$_GET['debug'] = $debug;
		$sprocket = new \PHPSprocket( $this->root . $file. '.js', array(
				'baseUri'	 => $this->root . $js_folder,
				'baseFolder' => $this->root . $js_folder,
				'debugMode'  => isset($_GET['debug']),
				'minify' 	 => !isset($_GET['debug'])
			));
		exit;
	}
}