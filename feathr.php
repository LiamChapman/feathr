<?php 

namespace Feathr;

class Feathr {	

	public $app_name, $actions = array(), $data = array();		
	
	public $root, $method;
	public $view_path = '/views/';		
	public $header	  = '/views/includes/header.php';
	public $footer	  = '/views/includes/footer.php';
	
	public function __construct ($app_name, $view_path = null) {
		$this->root		 = $_SERVER['DOCUMENT_ROOT'];
		$this->method	 = strtolower($_SERVER['REQUEST_METHOD']);
		$this->app_name  = $app_name;
		$this->view_path = !is_null($view_path) ? $view_path : $this->view_path;
	}
	
	public function get ($route, $callback) {
		if($this->method === 'get') {
			if(strpos($route, ",")) {
				$routes = explode(",", $route);
				foreach($routes as $r) {
					$this->actions[$r] = $callback;
				}
			} else {
				$this->actions[$route] = $callback;
			}
			return $this;
		} else {
			$this->E404();
		}
	}
	
	public function post ($route, $callback) {
		if($this->method === 'post') {
			if(strpos($route, ",")) {
				$routes = explode(",", $route);
				foreach($routes as $r) {
					$this->actions[$r] = $callback;
				}
			} else {
				$this->actions[$route] = $callback;
			}
			return $this;
		} else {
			$this->E404();
		}
	}
	
	public function feedback ($msg, $type='success', $flash = true) {
		//not complete yet.
	}
	
	public function view ($file, $vars, $hf = true) {
		$vars = $this->vars($vars);
		$file = is_null($file) ? 'default' : $file;
		if (file_exists($this->root.$this->view_path.$file.'.php')) {
			if (is_array($vars) && count($vars) > 0) {
				extract($vars, EXTR_PREFIX_SAME, "wddx");			
			}
			if ($hf) {
				include_once($this->root.$this->header);	
			}
			include_once($this->root.$this->view_path.$file.'.php');
			if ($hf) {
				include_once($this->root.$this->footer);	
			}
		}
		return $this;
	}
	
	public function vars ($vars = array ()) {
		$defaults = array(
			'app_name' => $this->app_name
		);
		if(!empty($vars)) {
			foreach($vars as $key => $value) {
				if(array_key_exists($key,$defaults)) {
					unset($defaults[$key]);
				}
			}
			$defaults = array_merge($vars, $defaults);
		}
		return $defaults;
	}
	
	public function autoload () {
		$instance = new self(); 
		# namespaces //needs testing - probably won't work.
		spl_autoload_register( function ($class) {
			if ( file_exists($this->root.'/vendor/feathr/'.$class.'.php') ) {
				$class = $this->root.'/vendor/feathr/' . str_replace('\\', '/', $class) . '.php';
				require_once($class);
			} else if ( file_exists($this->root.'/vendor/extend/'.$class.'.php') ) {
				$class = $this->root.'/vendor/extend/' . str_replace('\\', '/', $class) . '.php';
				require_once($class);
			}
		});
		# css
		$this->get(':any.css', function ($file) use ($instance) {
			$instance->css($file);
		});
		# js
		$this->get(':any.js', function ($file) use ($instance) {
			$instance->js($file);
		});
		# images
		$this->get(':any.png, :any.jpg, :any.gif', function ($file) use ($instance) {
			$instance->image($file);
		});
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
	
	public function css ($file) {
		header('Content-Type: text/css');
		echo(file_get_contents($this->root.$file.'.css'));
		exit;
	}
	
	public function js ($file) {
		header('Content-Type: application/javascript');
		echo(file_get_contents($this->root.$file.'.js'));
		exit;
	}
	
	public function image ($file) {		
		if (file_exists($this->root.$file.'.png')) {
			header('Content-Type: image/png');
			readfile($this->root.$file.'.png');
		} else if (file_exists($this->root.$file.'.jpg')) {
			header('Content-Type: image/jpeg');
			readfile($this->root.$file.'.jpg');
		} else if (file_exists($this->root.$file.'.gif')){
			header('Content-Type: image/gif');
			readfile($this->root.$file.'.gif');
		}
		exit;
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
