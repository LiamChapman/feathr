<?php

namespace Feathr\Extend;
use Feathr;

class User extends Feathr\FeathrApp {		
	public function auth () {
		if(!isset($_SESSION['auth'])) {
//			parent::$bool = false;
//			throw new Exception("No session set");
		}
		return $this;
	}
	public function set () {
		$_SESSION['auth'] = true;
		return $this;		
	}
}
