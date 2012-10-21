<?php 

# default namespace
namespace Feathr;

# this class runs on 5.3 or greater
version_compare(PHP_VERSION, '5.3', '<') ? exit("PHP 5.3 or Higher") : '';

/**
 * @author Liam Chapman
 * @version 1.0
 * @example:
 * $app = new Feathr\FeathrApp('My App');
 * $app->get('/', function () use ($app) {
 * 		$app->view('home');
 * });
 * $app->run();
 * 
 */
class FeathrApp {	

	public $app_name, $root, $method, $uri,
		   $call	 	 = array(),
		   $actions 	 = array(), 
		   $data 		 = array(), 
		   $groups 		 = array(), 
		   $applications = array(), 
		   $extended 	 = array(),
		   $view_path 	 = '/views/',
		   $app_path  	 = '/apps/',
		   $json_path 	 = '/json/',
		   $header	  	 = 'includes/header.php',
		   $footer	  	 = 'includes/footer.php';		
	
	/**
	 *  __construct
	 *  sets up root, uri, paths, directory, app_name, methods
	 *  @return void
	 */
	public function __construct ($app_name = null, $ext = array (), $dir = null, $view_path = null, $app_path = null, $json_path = null) {
		$this->root		 = !is_null($dir) ? $_SERVER['DOCUMENT_ROOT'] . $dir : $_SERVER['DOCUMENT_ROOT'];
		$this->uri 		 = !is_null($dir) ? str_replace($dir, '', $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];
		$this->dir 		 = $dir;
		$this->method	 = strtolower($_SERVER['REQUEST_METHOD']);
		$this->app_name  = $app_name;		
		$this->view_path = !is_null($view_path) ? $view_path : $this->view_path;
		$this->app_path  = !is_null($app_path) ? $app_path : $this->app_path;
		$this->json_path = !is_null($json_path) ? $json_path : $this->json_path;		
		$this->autoload();
		$this->extend($ext);
	}	
	
	/**
	 *  request
	 *  Pass through path and action, gets used for all requests GET, POST and XHR (Could also be used for PUT & DELETE)
	 *  @example:
	 *  $app->request('/my-url', function () use ($app) {} )
	 *  @return $this || @string
	 */
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
	
	/**
	 *  json
	 *  Save or Get a Json file
	 *  @example:
	 *  # get - if second par bool is true, won't return header and it will be an object.
	 *	$app->json('myjson', [bool = false], [bool = true]);
	 *  @return JSON
	 *  # save
	 *  $app->json('myjson', array(1=> 'test'), [bool = true]);
	 *  @return Boolean
	 */
	public function json ($file = null, $data = array(), $base64 = true) {
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

	/**
	 *  group
	 *	@example:
	 *  $app->group('mygroup_id', array(
	 *		'/my-route' => function () use ($app) {},
	 *		'/my-url'	=> function () use ($app) {}
	 *	));
	 *  @return $this 
	 */
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
		
	/**
	 *	application
	 *	@example:
	 *  $app->application('myapp');
	 *	@return $this
	 */
	public function application ($name = null, $var = 'app') {
		if (!is_null($name)) {
			$$var = $this;
			$this->applications[$name] = require_once($this->root.$this->app_path.$name.'.php');
			return $this;
		}
	}	
	
	/**
	 *	view
	 *	@example:
	 *	$app->view('homepage', [Array, Variable], [Bool = true]);
	 *  $app->view('homepage', array('test' => 123)); 
	 *	- Then in the view you can use $test, which will return 123
	 *	- $hf = header and footer
	 * 	@return $this
	 */
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
	
	/**
	 *	defaults
	 *  default variables to be used, additional ones can be set and passed
	 * 	$app->defaults(array('test' => 123))
	 *	@return Array
	 */
	public function defaults ($vars = array ()) {
		$defaults = array(
			'page_title' => $this->app_name,
			'_DIR' 		 => $this->dir
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
	
	/**
	 * autoload
	 * autoload classes and they can also be loaded via namespace as long as directory matches
	 * @return void
	 */
	public function autoload () { 		
		$instance = $this;
		spl_autoload_register( function ($class) use ($instance) {				
			$class = str_replace(array('\\','feathr'), array('/', ''), strtolower($class));
			if ( file_exists($instance->root.$class.'.php') ) {			
				require_once($instance->root.$class.'.php');
			}
		});	
	}
	
	/**
	 * extend
	 * when class names included in extend directory, they can be extended in the app instance
	 * @example $app->extend(array('facebook', 'user'));
	 * - $app->user->test()
	 * @return $this
	 */
	public function extend ($classes) {
		foreach ($classes as $class) {
			$namespace = 'Feathr\Extend\\'.ucfirst($class);
			$this->extended[strtolower($class)] = new $namespace;
		}
		return $this;
	}
	
	/**
	 * route
	 * - where the magic happens, routes urls to callback, 
	 * - optionally remove query string as it can cause conflicts with third partys e.g. facebook
	 * - urls can by sanitised / checked
	 * @example:
	 * /:string/:int/:any/my-url
	 * @return void
	 */
	public function route ($ignore_qs=false) {		
		$patterns	= array(
			':string' 	=> '([^\/]+)',
			':int'		=> '([0-9]+)',
			':any'	  	=> '(.+)'
		);		
		foreach ($this->actions as $route => $callback) {
			$this->uri = $ignore_qs ? str_replace('?'. $_SERVER['QUERY_STRING'], '', $this->uri) : $this->uri; # ignore query string
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
	
	/**
	 * run
	 * initialises app at the end of all the calls/requests
	 * sets character encoding, error_reporting, enabling and disabling query string
	 * enables sessions too.
	 * @example: $app->run();
	 * @return void
	 */			
	public function run ($error_reporting = 0, $ignore_qs = false, $charset = 'utf-8') {		
		error_reporting($error_reporting);
		ini_set('default_charset', $charset);
		mb_internal_encoding($charset);
		mb_detect_order($charset);
		session_start();
		$this->route($ignore_qs);
		exit;
	}
		
	/**
	 * E404
	 * default 404 page to show when route not found and currently general errors.
	 * @return void
	 */
	public function E404 () {	
		header( $_ENV['SERVER_PROTOCOL']." 404 Not Found", true, 404 );
		if ( file_exists($this->root.$this->view_path.'404.php') ) {
			$this->view('404', array('page_title' => '404 Error'));
			exit;
		} else {			
			exit('404 Error');
		}
	}
	
	/**
	 * __call
	 * magic method to be used for POST, GET and XHR to detect request method
	 * all runs through request method.
	 * for third-parties such as facebook, there are some issues with this. 
	 * @example $app->get('/my-route', function()); $app->post('/my-route', function)
	 * @return $this
	 */
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

	/**
	 * __set
	 * magic method - sets vars to $data va so that custom vars can be used and passed around app
	 * @example $app->test = 'test';
	 * @return void;
	 */
	public function __set ($name, $value) {
		$this->data[$name] = $value;
	}	
	
	/**
	 *	__get
	 * magic method to get method, variable or extended function, if nothing found returns 404
	 * @return void;
	 */
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