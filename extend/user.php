<?php

namespace Feathr\Extend;
use Feathr;

class User extends Feathr\Feathr {		
	public function auth () {
		if(isset($_SESSION['auth'])) {
			return true;
		} else {
			return false;
		}
		return $this;
	}
	public function set () {
		#$_SESSION['auth'] = true;		
		return false;		
	}
}
