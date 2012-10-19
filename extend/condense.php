<?php

/*
 * e.g.
 $c = condense('*', 'js', 'js');
 echo $c;
 * need to reduce request by checking times before streaming/getting
 * needs tidying up, commenting, few custom bits done for feathr..
*/

namespace Feathr\Extend;
use Feathr;

class Condense { 

	public 	$files 	  = null, 
			$type	  = 'js',
			$path 	  = 'js',			
			$cache 	  = true,
			$newline  = false,
			$dir      = 'cache',			
			$root	  = '',
			$filename = 'app';

	private $fetch 	  = array(),			
			$times    = array(),
			$contents = null;

	public function __construct ($files = null, $type = 'js', $path = 'js', $dir = 'cache', $root = false) {		
		$this->var   = gettype($files);
		$this->root  = $root ? $_SERVER['DOCUMENT_ROOT'] : __DIR__ .'/';
		$this->type  = isset($type)  ? $type  : $this->type;
		$this->path  = isset($path)  ? $path  : $this->path;
		$this->files = isset($files) ? $files : $this->files;
		$this->dir   = isset($dir)   ? $dir   : $this->dir;
	}

	public function get () {		
		switch (strtolower($this->var)) {
			case 'string':
			default:				
				if($this->files == '*' || is_null($this->files)) {	
					$path = $this->root.$this->path.'/*.'.$this->type;					
					foreach (glob($path) as $file) {
						$this->fetch[] = $file; 
					}				
				}
			break;
			case 'array':				
				foreach ($this->files as $file) {
					$this->fetch[] = $this->root.$this->path.'/'.$file.'.'.$this->type;					
				}
			break;
		}		
		return $this->fetch;		
	}

	public function contents () {		
		if (!empty($this->fetch)) {
			foreach ($this->fetch as $file) {				
				$this->contents[] = trim(file_get_contents($file)) . ($this->newline ? '\n':'');
				$this->times[] 	  = filemtime($file);
			}		
			return trim(implode("",$this->contents));
		}		
	}

	public function file ($return = false) {
		$this->get();
		if ($contents = $this->contents()) {
			$name = $this->filename.'.'.$this->type;
			$path = $this->root.$this->dir.'/'.$name;
			if (!file_exists($path)) {
				file_put_contents($path, $contents);				
			} else {
				if (max($this->times) > filemtime($path)) {
					file_put_contents($path, $contents);
				}
			}
			if (!$return) {
				switch (strtolower($this->type)) {				
					case 'js':			
						return '<script type="text/javascript" src="'.$this->dir.'/'.$name.'"></script>';
					break;
					case 'css':
						return '<link rel="stylesheet" type="type/css" href="'.$this->dir.'/'.$name.'" />';
					break; 
				}
			} else {
				return $this->dir.'/'.$name;
			}
		}
	}

	public function __toString () {
		return $this->file();
	}
	
}