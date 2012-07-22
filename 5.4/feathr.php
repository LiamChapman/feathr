<?php 

namespace Feathr;

version_compare(PHP_VERSION, '5.4', '<') ? exit("PHP 5.4 or Higher") : '';

class FeathrApp {	

	public $app_name, $root, $method, $uri,
		   $call		 = [],
		   $actions 	 = [], 
		   $data 		 = [], 
		   $groups 		 = [], 
		   $applications = [], 
		   $extended 	 = [],
		   $view_path 	 = '/views/',
		   $app_path  	 = '/apps/',
		   $json_path 	 = '/json/',
		   $header	  	 = 'includes/header.php',
		   $footer	  	 = 'includes/footer.php';	
	
	static $instance;
	private static function instance () {
		if(!self::$instance)
			self::$instance = $this;
						
		return self::$instance;
	}
	
	public function __construct ($app_name = null, $ext = [], $view_path = null, $app_path = null, $json_path = null) {
		$this->root		 = $_SERVER['DOCUMENT_ROOT'];
		$this->method	 = strtolower($_SERVER['REQUEST_METHOD']);
		$this->app_name  = $app_name;		
		$this->view_path = !is_null($view_path) ? $view_path : $this->view_path;
		$this->app_path  = !is_null($app_path) ? $app_path : $this->app_path;
		$this->json_path = !is_null($json_path) ? $json_path : $this->json_path;		
		$this->autoload();
		$this->extend($ext);
	}	
	
	public function request ($route = null, $callback = null) {
		if (is_string($route)) {
			if (strpos($route, ",")) {
				$routes = explode(",", $route);
				foreach($routes as $r) {
					$this->actions[trim($r)] = $callback;
				}
			} else {
				$this->actions[trim($route)] = $callback;
			}
			return $this;
		} else {
			return $route;
		}
	}
	
	public function json ($file = null, $data = [], $base64 = true) {
		if (!is_null($file)) {
			$file = $this->root.$this->json_path.$file.'.json';						
			if(empty($data) && !is_bool($data)) {
				$json = file_get_contents($file);
				if(!is_bool($data)) {
					header('Content-Type: application/json');
					if ($base64) {
						$json = base64_decode($json);
						return json_decode($json);
					} else {
						return json_decode($json);
					}
					exit;
				} else if(is_bool($data) && $data == true) {
					if ($base64) {
						$json = base64_decode($json);
						return (object) json_decode($json);
					} else {
						return (object) json_decode($json);
					}
				}
			} else {
				if (!file_exists($file)) {
					$data = json_encode($data);					
					if (file_put_contents($file, ($base64 ? base64_encode($data) : $data))) {
						return true;
					} else {
						return false;
					}
				} else {
					$current = $base64 ? base64_decode(file_get_contents($file)) : file_get_contents($file);					
					$json	 = json_decode($current);					
					$merge	 = array_merge((array) $json, (array) $data);
					$data	 = json_encode($merge);					
					if (file_put_contents($file, ($base64 ? base64_encode($data) : $data))) {
						return true;
					} else {
						return false;
					}
				}
			}				
		}
	}
	
	public function group ($id, $array) {
		if (isset($id)) {
			$this->groups[$id] = $array;
			if (is_array($array) && !empty($array)) {
				foreach ($array as $route => $callback) {
					$this->actions[$route] = $callback;
				}
			}
			return $this;
		}
	}	
	
	public function application ($name = null, $var = 'app') {
		if (!is_null($name)) {
			$$var = $this;
			$this->applications[$name] = require_once($this->root.$this->app_path.$name.'.php');
			return $this;
		}
	}	
	
	public function view ($file = null, $vars, $hf = true) {
		header("Content-Type: text/html");
		$vars = $this->defaults($vars);
		$file = is_null($file) ? 'default' : $file;
		if (file_exists($this->root.$this->view_path.$file.'.php')) {
			if (is_array($vars) && count($vars) > 0) {
				extract($vars, EXTR_PREFIX_SAME, "wddx");			
			}
			if ($hf) {
				include_once($this->root.$this->view_path.$this->header);	
			}
			include_once($this->root.$this->view_path.$file.'.php');
			if ($hf) {
				include_once($this->root.$this->view_path.$this->footer);	
			}
		}
		return $this;
	}	
	
	public function defaults ($vars = []) {
		$defaults = [
			'page_title' => $this->app_name
		];
		if (!empty($vars)) {
			foreach ($vars as $key => $value) {
				if (array_key_exists($key,$defaults)) {
					unset($defaults[$key]);
				}
			}
			$defaults = array_merge($vars, $defaults);
		}
		return $defaults;
	}	
	
	public function autoload () { 		
		spl_autoload_register( function ($class) {				
			$class = str_replace(['\\','feathr'], ['/', ''], strtolower($class));
			if ( file_exists($this->root.$class.'.php') ) {			
				require_once($this->root.$class.'.php');
			}
		});	
	}
	
	public function extend ($classes) {
		foreach ($classes as $class) {
			$namespace = 'Feathr\Extend\\'.ucfirst($class);
			$this->extended[strtolower($class)] = new $namespace;
		}
		return $this;
	}
	
	public function route () {
		$this->uri 	= $_SERVER['REQUEST_URI'];
		$patterns	= [
			':string' 	=> '([^\/]+)',
			':int'		=> '([0-9]+)',
			':any'	  	=> '(.+)'
		];
		foreach ($this->actions as $route => $callback ) {
			$find = '!^'.str_replace(array_keys($patterns), array_values($patterns), $route).'\/?$!';
			if (preg_match($find, $this->uri, $params) && !isset($this->call['callback'])) {
				array_shift($params);
				$this->call['callback']  = $callback;
				$this->call['params']	 = $params;				
			}	
		}
		if (!empty($this->call)) {			
			call_user_func_array($this->call['callback'], $this->call['params']);
		} else {
			$this->E404();
		} 
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
		if ( file_exists($this->root.$this->view_path.'404.php') ) {
			$this->view('404', ['page_title' => '404 Error']);
			exit;
		} else {			
			exit('404 Error');
		}
	}
	
	public function __call ($call, $args) {
		$call = strtolower($call);
		if ($call === 'get' && $this->method === 'get') {
			$this->request($args[0], $args[1]);
		} else if ($call === 'post' && $this->method === 'post') {
			$this->request($args[0], $args[1]);
		} else if ($call === 'xhr' && $_SERVER['HTTP_X_REQUESTED_WITH'] && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->request($args[0], $args[1]);
		} else {
			if (!method_exists($this, $call)) {
				$this->E404(); //throw exception instead?
			}
		}
		return $this;
	}
	
	public function __set ($name, $value) {
		$this->data[$name] = $value;
	}	
	
	public function __get ($name) {
		if (isset($this->$name)) {
			return $this->$name;
		} else if (method_exists($this, $name)) {
			return $this->$name();
		} else if (isset($this->data[$name])) {
			return $this->data[$name];
		} else if (isset($this->extended[$name])) {
			return $this->extended[$name];
		} else {
			$this->E404(); //throw exception instead?
		}
	}
	
}