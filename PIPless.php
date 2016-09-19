<?php declare(strict_types=1);
/**
 * PIPless
 * 
 * This is the main class of the system, and handles the initial execution
 * of the framework.
 * 
 * @package pipless
 * 
 * @link https://github.com/tombayo/PIPless
 * @author  Gilbert Pellegrom, Dev7studios
 * @author  Tom Andre Munkhaug, @tombayo <me@tombayo.com>
 * @license GPLv3
 */
class PIPless {
  
  /**
  * Called to execute the framework.
  * 
  * Will load a controller based on the $_GET['p']-variable if url_rewrite
  * is disabled, otherwise it loads a controller based on the requested uri.
  * 
  * @author  Gilbert Pellegrom, Dev7studios
  * @author  Tom Andre Munkhaug, @tombayo <me@tombayo.com>
  * @license GPLv3
  * 
  * @global $config Used to load configuration variables from the config file.
  * 
  */
  static public function go() {
	  global $config;
      
    // Set our defaults
    $controller = $config['default_controller'];
    $action = 'index';
    $argument = '';

    if ($config['url_rewrite']) {
      // Get request url, script url, and the requested path(minus any GET variables and such)
      $request_url = $_SERVER['REQUEST_URI'] ?? '';
      $script_url  = $_SERVER['PHP_SELF'] ?? '';
      $request_path = parse_url($request_url, PHP_URL_PATH);
       
      // Get our url path and trim the / of the left and the right
      $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_path, 1), '/');
    } else {
      $url = $_GET['p'] ?? '';
    }
    
	  // Split the url into segments
	  $segments = explode('/', $url);
	  
	  // Do our default checks
	  // Below lines cannot be replaced by the null-coalescing operator due to $segments[0] never will be null.
	  if(isset($segments[0]) && $segments[0] != '') $controller = $segments[0];
	  if(isset($segments[1]) && $segments[1] != '') $action = $segments[1];
	  if(isset($segments[2]) && $segments[2] != '') $argument = $segments[2];
    
	  // Get our controller file
    $path = APP_DIR . 'controllers/' . $controller . '.php';
	  if(file_exists($path)) {
      require_once($path);
	  } else {
      $controller = $config['error_controller'];
      require_once(APP_DIR . 'controllers/' . $controller . '.php');
	  }
      
    // Check if the action is callable
    if(!is_callable([$controller, $action])) {
      $action = 'index';
    }
    
    // Runs the selected method from the selected controller, then exits.
    die($controller::$action($argument));
  }
}
