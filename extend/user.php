<?php

namespace Feathr\Extend;
use Feathr;

class User extends Feathr\Feathr {
	public function auth () {
		if(!isset($_SESSION['auth'])) {
			$this->E404();
		}
		return $this;
	}
}
