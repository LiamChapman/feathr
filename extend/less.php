<?php

namespace Feathr\Extend;
use Feathr;

class Less extends Feathr\FeathrApp {
	# put this in .htaccess -> 
	# RewriteRule (.*)\.css$ index.php?url=$1.css [L,QSA]
	# put it after Rewrite Engine on
	public function parse ($file) {				
		header('Content-Type: text/css');
		# https://github.com/leafo/lessphp
		require_once __DIR__ . '/thirdparty/lessc.inc.php';
		$less = new \lessc($this->root.$file . '.css');
		$less->setFormatter("compressed");
		echo $less->parse();
		exit;
	}
}