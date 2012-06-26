<?php 

namespace Application;

class Feathr {	

	public $app_name, $actions = array(), $data = array();		
	
	public $view_path = '/views';		
	public $header	  = '/views/header.php';
	public $footer	  = '/views/footer.php';
	
	public function __construct ($app_name) {
		$this->app_name = $app_name;		
	}
	
	public function fetch ($route, $callback) {
		$this->actions[$route] = $callback;
		return $this;
	}

	public function route () {
		$uri 		= $_SERVER['REQUEST_URI'];
		$patterns	= array(
			':string' 	=> '([^\/]+)',
			':int'		=> '([0-9]+)',
			':any'	  	=> '(.+)'
		);
		$counter = 0;
		foreach ($this->actions as $route => $callback ) {
			$find = '!^'.str_replace(array_keys($patterns), array_values($patterns), $route).'\/?$!';
			if (preg_match($find, $uri, $params)) {
				array_shift($params);
				++$counter;
				call_user_func_array($callback, $params);
			}	
		}
		if($counter === 0) {
			$this->E404();
		}
	}	
	
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
							$this->feedback('Error validating');
							++$counter;	
						}
					}
				} else {
					if(!filter_var($c, $filters[$key])) {
						$this->feedback('Error validating');
						++$counter;
					}
				}
			}
		} else {
			if(!filter_var($check, $filters[$key])) {
				$this->feedback('Error validating');
				++$counter;
			}
		}
		if($counter > 1) {
			$this->E404(); //tempory until feedback and views working.
		}
	}
	
	public function feedback ($msg, $type='success', $flash = true) {
		
	}
	
	public function view ($file, $vars, $hf = true) {
		$file = is_null($file) ? 'default' : $file;
		if (file_exists($this->view_path.$file.'.php')) {
			if (is_array($vars) && count($vars) > 0) {
				extract($vars, EXTR_PREFIX_SAME, "wddx");			
			}
			if ($hf) {
				include_once($this->header);	
			}
			include_once($this->view_path.$file.'.php');
			if ($hf) {
				include_once($this->footer);	
			}
		}
		return $this;
	}
	
	public function run ($error_reporting = 0, $charset = 'utf-8') {
		error_reporting($error_reporting);
		ini_set('default_charset', $charset);
		mb_internal_encoding($charset);
		mb_detect_order($charset);
		session_start();
		$this->route();
		exit;
	}	
	
	public function E404 () {
		header( $_ENV['SERVER_PROTOCOL']." 404 Not Found", true, 404 );
		exit('404 Error');
	}
	
	public function __set ($name, $value) {
		$this->data[$name] = $value;
	}
	
	public function __get ($name) {
		if(isset($this->$name)) {
			return $this->$name;
		} else if(method_exists($this, $name)) {
			return $this->$name();
		} else if(isset($this->data[$name])) {
			return $this->data[$name];
		} else {
			$this->E404();
		}
	}
	
}
