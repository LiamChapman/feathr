<?php

namespace Feathr\Extend;
use Feathr;

class Facebook  {
	public $fb;
	public function __construct ($id, $secret) {	
		require_once __DIR__ . '/thirdparty/facebook/facebook.php';		
		$facebook = new \Facebook(array(
			'appId' 	=> $id,
			'secret'	=> $secret,
			'cookie'	=> true
		));
		$this->fb = $facebook;			
	}
}