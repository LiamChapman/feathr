<?php 
namespace Feathr;

class Feathr {	

	public $app_name, $actions = array(), $data = array(), $groups = array(), $applications = array();	
	public $root, $method;
	public $view_path = '/views/';
	public $app_path  = '/applications/';
	public $header	  = 'includes/header.php';
	public $footer	  = 'includes/footer.php';	
	
	public function __construct ($app_name, $view_path = null, $app_path = null) {
		$this->root		 = $_SERVER['DOCUMENT_ROOT'];
		$this->method	 = strtolower($_SERVER['REQUEST_METHOD']);
		$this->app_name  = $app_name;
		$this->view_path = !is_null($view_path) ? $view_path : $this->view_path;
		$this->app_path  = !is_null($app_path) ? $app_path : $this->app_path;
	}	
	
	public function request ($route = null, $callback = null) {
		if (is_string($route)) {
			if (strpos($route, ",")) {
				$routes = explode(",", $route);
				foreach($routes as $r) {
					$this->actions[trim($r)] = $callback;
				}
			} else {
				$this->actions[$route] = $callback;
			}
			return $this;
		} else {
			return $route;
		}
	}		
	
	public function group ($id, $array) {
		if (isset($id)) {
			$this->groups[$id] = $array;
			if (is_array($array) && !empty($array)) {
				foreach ($array as $route => $app_callback) {
					$this->actions[$route] = $app_callback;
				}
			}
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
	
	public function defaults ($vars = array ()) {
		$defaults = array(
			'page_title' => $this->app_name
		);
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
		$instance = new self(); 		
		spl_autoload_register( function ($class) { # namespaces needs testing - probably won't work.
			if ( file_exists($this->root.'/vendor/feathr/'.$class.'.php') ) {
				#$class = $this->root.'/vendor/feathr/' . str_replace('\\', '/', $class) . '.php';
				$class = $this->root.'/vendor/feathr/'.$class.'.php';
				require_once($class);
			} else if ( file_exists($this->root.'/vendor/extend/'.$class.'.php') ) {
				#$class = $this->root.'/vendor/extend/' . str_replace('\\', '/', $class) . '.php';
				$class = $this->root.'/vendor/extend/'.$class.'.php';
				require_once($class);
			}
		});
	}
	
	public function route () {
		$uri 		= $_SERVER['REQUEST_URI'];
		$patterns	= array(
			':string' 	=> '([^\/]+)',
			':int'		=> '([0-9]+)',
			':any'	  	=> '(.+)'
		);
		$request = array();
		foreach ($this->actions as $route => $callback ) {
			$find = '!^'.str_replace(array_keys($patterns), array_values($patterns), $route).'\/?$!';
			if (preg_match($find, $uri, $params) && !isset($request['callback'])) {
				array_shift($params);
				$request['callback'] = $callback;
				$request['params']	 = $params;				
			}	
		}				
		if (!empty($request)) {
			call_user_func_array($request['callback'], $request['params']);
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
		$this->autoload();
		$this->route();
		exit;
	}
			
	public function E404 () {
		header( $_ENV['SERVER_PROTOCOL']." 404 Not Found", true, 404 );
		if ( file_exists($this->root.$this->view_path.'404.php') ) {
			$this->view('404', array('page_title' => '404 Error'));
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
		}
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
		} else {
			$this->E404();
		}
	}
		
}