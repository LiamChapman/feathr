<?php

namespace Feathr\Extend;
use Feathr;

class User extends Feathr\FeathrApp {		
	public function auth () {
		if(!isset($_SESSION['auth'])) {
			//die("You do not have permission to this area");
		}
		return $this;
	}
	public function set () {
		$_SESSION['auth'] = true;
		return $this;		
	}
}
