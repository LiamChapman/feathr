<?php

# not working yet - still experimenting.

namespace Feathr\Extend;
use Feathr;

class less extends Feathr\Feathr {
	public function parse ($file) {		
		header('Content-Type: text/css');
		# https://github.com/leafo/lessphp
		require_once __DIR__ . '/thirdparty/lessc.inc.php';
		$less = new lessc($file . '.css');
		$less->setFormatter("compressed");
		echo $less->parse();
		exit;
	}
}