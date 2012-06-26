<?php

namespace Feathr\Validate;
#use Feathr;

class Validate extends Feathr {
	
	public function filter ($check, $key) {
		$counter = 0;
		$filters = array(
			'int' 		=> FILTER_SANITIZE_NUMBER_INT,
			'string'	=> FILTER_SANITIZE_STRING,
			'float'		=> FILTER_SANITIZE_NUMBER_FLOAT,
			'url'		=> FILTER_SANITIZE_URL,
			'html'		=> FILTER_SANITIZE_MAGIC_QUOTES,
			'email'		=> FILTER_VALIDATE_EMAIL
		);
		if(is_array($check)) {
			foreach ($check as $i => $c) {
				if(is_array($key)) {
					foreach ($key as $x => $k) {
						if(!filter_var($check[$x], $filters[$k])) {
							$this->feedback('Error validating', 'error');
							++$counter;	
						}
					}
				} else {
					if(!filter_var($c, $filters[$key])) {
						$this->feedback('Error validating', 'error');
						++$counter;
					}
				}
			}
		} else {
			if(!filter_var($check, $filters[$key])) {
				$this->feedback('Error validating', 'error');
				++$counter;
			}
		}
		if($counter > 1) {
			$this->E404(); //tempory until feedback and views working.
		}
	}
	
}