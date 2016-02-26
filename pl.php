<?php
/**
* PIPless v1.0.2
* 
* @package  PIPless
* @version  1.0.2
* @desc     PIPless is a tiny application framework forked from Gilbert Pellegrom's PIP. PIPless is modified to use GET-variables and static objects for an even simpler framework. It is also designed to work directly with RedBeanPHP (redbeanphp.com)
* @author   @tombayo <me@tombayo.com>
* @license  GPLv3
* @link     https://github.com/tombayo/PIPless
*/
class PIPless {
  
  /**
  * Called to execute the framework.
  * 
  * Will load a controller based on the $_GET['p']-variable.
  * 
  * @author  Gilbert Pellegrom, Dev7studios
  * @license MIT
  * @see     PIP.license.txt
  * 
  * @param void
  * @returns void
  * @static
  * @access public
  */
  static public function go() {
	  global $config;
      
    // Set our defaults
    $controller = $config['default_controller'];
    $action = 'index';
    $url = isset($_GET['p']) ? $_GET['p'] : '';
    
	  // Split the url into segments
	  $segments = explode('/', $url);
	  
	  // Do our default checks
	  if(isset($segments[0]) && $segments[0] != '') $controller = $segments[0];
	  if(isset($segments[1]) && $segments[1] != '') $action = $segments[1];

	  // Get our controller file
    $path = APP_DIR . 'controllers/' . $controller . '.php';
	  if(file_exists($path)) {
      require_once($path);
	  } else {
      $controller = $config['error_controller'];
      require_once(APP_DIR . 'controllers/' . $controller . '.php');
	  }
      
    // Check the action exists
    if(!method_exists($controller, $action)) {
      $action = 'index';
    }
    die($controller::$action());
  }
}

/**
* View
*
* @desc    Used by Controller to load views.
* @author  Gilbert Pellegrom, Dev7studios
* @license MIT
*
* Copyright (c) 2011 Gilbert Pellegrom, Dev7studios
* This source file is subject to the License that is bundled
* with this source code in the file license.txt.
*/
class View {
  
  /**
   * @var array
   */
  private $pageVars = array();
  
  /**
   * @var string
   */
  private $template;

  /**
  * Constructor
  * Creates a new instance of View by loading given template.
  *
  * @param string    $template   Name of template
  */
  public function __construct($template)  {
    $this->template = APP_DIR .'views/'. $template .'.php';
  }
  
  /**
  * Sets variables to be forwarded to the template.
  *
  * @param string    $var   Name of variable.
  * @param mixed    $val   The variable's value.
  *
  * @return void
  */
  public function set($var, $val)  {
    $this->pageVars[$var] = $val;
  }

  /**
  * Renders the View.
  *
  * @return void
  */
  public function render()  {
    extract($this->pageVars);

    ob_start();
    require($this->template);
    echo ob_get_clean();
  }
}

class Load {
  
  /**
  * Loads given View-class. Note: The name of the class must match filename.
  * Returns a new instance of given class.
  *
  * @param string    $name   Name of class.
  *
  * @return object
  */
  public static function view($name)  {
    $view = new View($name);
    return $view;
  }
  
  /**
  * Loads given Plugin by filename.
  *
  * @param string    $name   Name of plugin/filename
  *
  * @return void
  */
  public static function plugin($name)  {
    require(APP_DIR .'plugins/'. strtolower($name) .'.php');
  }
  
  /**
  * Loads given Helper-class. Note: The name of the class must match filename.
  * Returns a new instance of given class.
  *
  * @param string    $name   Name of helper.
  *
  * @return object
  */
  public static function helper($name)  {
    require(APP_DIR .'helpers/'. strtolower($name) .'.php');
    $helper = new $name;
    return $helper;
  }
}

/**
* Interface for forcing subclasses to implement an index() method.
*/
interface Controller_demands {
  public static function index();
}

/**
* Parent class for Controllers.
* Implements the Controller_demands interface to force error if the
* controller don't have an index() method.
*/
abstract class Controller implements Controller_demands {
    
  /**
  * Redirects the client to a different url-suffix using base_url as prefix.
  *
  * @param string    $loc   Given url-suffix
  *
  * @return void
  */
  protected static function redirect($loc) {
    header('Location: '. BASE_URL . $loc);
  }
  
  /**
  * Initialize RedBeanPHP ORM for mysql.
  * @todo This method should be implemented in a more framework-like way
  * 
  * @return void
  */
  protected static function initDB() {
    global $config;
    if (!class_exists('R')) {
      Load::plugin("rb"); // Loads RedBeanPHP, our ORM
      R::setup('mysql:host='.$config['db_host'].';dbname='.$config['db_name'],$config['db_username'],$config['db_password']); // Setup RedBeanPHP
    }
  }
}
?>
