<?php declare(strict_types=1);
/**
 * PIPless
 * 
 * This is the main class of the system, and handles the initial execution
 * of the framework.
 * 
 * @package pipless
 * @version v1.1.0
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
    
    // Loads files, configs, define constants, and prepares the system for execution:
    self::initSystem();
      
    // Set our defaults
    $controller = $config['default_controller'];
    $action = 'index';

    if ($config['url_rewrite']) {
      // Get request url, script url, and the requested path(minus any GET variables and such)
      $request_url = $_SERVER['REQUEST_URI'] ?? '';
      $script_url  = $_SERVER['PHP_SELF'] ?? '';
      $request_path = parse_url(strtolower($request_url), PHP_URL_PATH);
       
      // Get our url path and trim the / of the left and the right
      $url = trim(preg_replace('/'. str_replace('/', '\/', strtolower(str_replace('index.php', '', $script_url))) .'/', '', $request_path, 1), '/');
    } else {
      $url = $_GET['p'] ?? '';
    }
    
	  // Split the url into segments. This should also prevent file-inclusion exploiting.
	  $segments = explode('/', $url);
	  
	  // Do our default checks
	  // Below lines cannot be replaced by the null-coalescing operator due to $segments[0] never will be null.
	  if(isset($segments[0]) && $segments[0] != '') $controller = $segments[0];
	  if(isset($segments[1]) && $segments[1] != '') $action = $segments[1];
    
	  // Get our controller file
    $path = APP_DIR . 'controllers/' . $controller . '.php';
    
    try {
      // Loads the controller file
      if(!file_exists($path)) Throw New Exception('No controller "'.$controller.'"('.$request_url.').', 404);
      require_once($path); 
      
      // Check if the action is callable
      if(!is_callable([$controller, $action])) Throw New Exception('Not callable "'.$controller.'::'.$action.'"('.$request_url.').', 404);

      // Runs the selected method from the selected controller, then exits.
      exit($controller::$action());
      
    } catch(Throwable $e) {
      require_once(APP_DIR . 'controllers/' . $config['error_controller'] . '.php');
      $config['error_controller']::index($e);
    }
  }

  /**
  * Initializes the framework-system
  * 
  * Sets constants, loads files, and loads the configs. Also prepares stuff based
  * on the config-file.
  *
  * @author  Gilbert Pellegrom, Dev7studios
  * @author  Tom Andre Munkhaug, @tombayo <me@tombayo.com>
  * @license GPLv3
  * 
  */
  static private function initSystem() {
    /**
     * A constant for the project's root directory
     */
    define('ROOT_DIR', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) .'/');
    /**
     * A constant for the application directory
     */
    define('APP_DIR', ROOT_DIR .'application/');

    /**
     * Load the config file
     */
    require(APP_DIR .'config/config.php');
    /**
     * Load the system-files
     */
    require(ROOT_DIR .'system/Controller.php');
    require(ROOT_DIR .'system/Load.php');
    require(ROOT_DIR .'system/Model.php');
    require(ROOT_DIR .'system/View.php');

    /**
     * @global array $config
     */
    global $config;
    /**
     * A constant for the project's base url.
     */
    define('BASE_URL', $config['base_url']);

    /**
     * Sets the ini-file's display_errors variable and sets our error-reporting level.
     * Prevents the webserver's setting to cause unwanted behavior in our application.
     */
    if ($config['report_errors']) {
      ini_set("display_errors", "1");
      error_reporting(-1);
    } else {
      ini_set("display_errors", "0");
      error_reporting(0);
    }

    /**#@+
     * Set the session settings
     */
    $session_lifetime = $config['session_lifetime'];
    session_start(['gc_maxlifetime'=>$session_lifetime]);
    setcookie(session_name(),session_id(),time()+$session_lifetime,"/","",$config['force_https'],true);
    /**#@-*/

    /**
     * Loads all the defined extras
     */
    foreach($config['include_extras'] as $extra) {
      require(APP_DIR .'extras/'.$extra.'.php');
    }

    // Force redirection to HTTPS
    $https = $_SERVER['HTTPS'] ?? 'off';
    if($https == 'off' && $config['force_https']) {
      header('Location: '.BASE_URL);
      exit;
    }
  }
}
