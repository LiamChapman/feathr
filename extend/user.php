<?php

namespace Feathr\Extend;
use Feathr;

class User extends Feathr\FeathrApp {		
	public function auth () {
		if (!isset($_SESSION['auth'])) {
			parent::$bool = false;		
			$this->error_msg = "No Session set";			
		} 
		return $this;
	}
	public function set () {
		$_SESSION['auth'] = true;
		return $this;		
	}
}
