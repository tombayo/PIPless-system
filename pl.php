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
  * Will load a controller based on the $_GET['p']-variable, if url_rewrite
  * is disabled, otherwise it loads a controller based on the requested uri.
  * 
  * @author  Gilbert Pellegrom, Dev7studios
  * @author  Tom Andre Munkhaug, @tombayo <me@tombayo.com>
  * @license GPLv3
  * 
  * @global $config Used to save some dynamic global variables from DB.
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

/**
 * Model
 * 
 * 
 * Used for requests to the database.
 * 
 * @author Tom Andre Munkhaug <me@tombayo.com>
 * @package pipless
 * @subpackage system
 * 
 */
class Model {
  
  /**
  * Initialize RedBeanPHP ORM for mysql.
  * 
  * Initializes RedBeanPHP and returns the Toolbox, for more details:
  * @link http://redbeanphp.com/api/class-RedBeanPHP.ToolBox.html
  * 
  * @global $config Used to get database-settings
  * 
  * @return RedBeanPHP\ToolBox
  */
  public static function initDB():RedBeanPHP\ToolBox {
    global $config;
    if (!class_exists('R')) {
      Load::plugin("rb"); // Loads RedBeanPHP, our ORM
      R::setup('mysql:host='.$config['db_host'].';dbname='.$config['db_name'],$config['db_username'],$config['db_password']); // Setup RedBeanPHP
      if ($config['db_freeze']) {
        R::freeze();
      }
    }
    return R::getToolBox(); // Returns RedBeanPHP's toolbox
  }
  
}

/**
* View
* 
* 
* Used by Controller to load views.
* 
* @package pipless
* @subpackage system
* 
* @author  Gilbert Pellegrom, Dev7studios
* @author  Tom Andre Munkhaug, @tombayo <me@tombayo.com>
* @license GPLv3
* 
*/
class View {
  
  /**
   * Holds the variables to be exposed to the view.
   * 
   * @var array
   */
  private $pageVars = array();
  
  /**
   * The URL of the View's template.
   * 
   * @var string
   */
  private $template;

  /**
   * Constructor
   * Creates a new instance of View by loading given template.
   *
   * @param string $template Name of template
   */
  public function __construct(string $template)  {
    $this->template = APP_DIR .'views/'. $template .'.php';
  }
  
  /**
  * Sets variables to be forwarded to the template.
  *
  * @param string $var Name of variable.
  * @param mixed  $val The variable's value.
  */
  public function set(string $var, $val)  {
    $this->pageVars[$var] = $val;
  }

  /**
   * Renders the view
   * 
   * If $ob_return is set to true, the function returns the output buffer.
   * Otherwise echo the output buffer and return an empty string.
   * 
   * @param bool $ob_return
   * @return string
   */
  public function render(bool $ob_return=false):string  {
    extract($this->pageVars);

    ob_start();
    require($this->template);
    if ($ob_return) {
      return ob_get_clean();
    } else {
      echo ob_get_clean();
      return '';
    }
  }
}

/**
 * Load
 * 
 *
 * Used for loading controllers, views or plugins.
 * 
 * @package pipless
 * @subpackage system
 * 
 * @author  Gilbert Pellegrom, Dev7studios
 * @author  @tombayo <me@tombayo.com>
 * @license GPLv3
 *
 */
class Load {
  /**
   * Loads a given controller, and returns an instance of it.
   * 
   * @param string $name
   * @return Controller
   */
  public static function controller(string $name):Controller {
    require_once(APP_DIR . 'controllers/' . $name . '.php');
    $controller = new $name;
    return $controller;
  }
  
  /**
  * Loads given View-class. Note: The name of the class must match filename.
  * Returns a new instance of given class.
  *
  * @param string $name Name of class.
  *
  * @return View
  */
  public static function view(string $name):View  {
    $view = new View($name);
    return $view;
  }
  
  /**
  * Loads given Plugin by filename.
  *
  * @param string $name Name of plugin/filename
  */
  public static function plugin(string $name)  {
    require_once(APP_DIR .'plugins/'. strtolower($name) .'.php');
  }
}

/**
* Parent class for Controllers.
* 
* Declared abstract to be able to force methods to children.
* 
* @package pipless
* @subpackage system
* 
* @author   Gilbert Pellegrom, Dev7studios
* @author   Tom Andre Munkhaug, @tombayo <me@tombayo.com>
* @license  GPLv3
*  
*/
abstract class Controller {

  /**
   * Force the children of this class to implement a method index().
   */
  abstract public static function index();
  
  /**
  * Redirects the client to a different url-suffix using base_url as prefix.
  * 
  * Also exits further code-execution.
  *
  * @param string $loc Given url-suffix
  */
  protected static function redirect(string $loc) {
    header('Location: '. BASE_URL . $loc);
    exit;
  }
}

/**
 * A trait used to define a basic controller.
 * 
 * A trait that loads the view "view.classname.php" and supplies the view with some basic variables.
 * Used by controllers that only displays a standard index-view with no other logic.
 * Should NOT be used by classes that don't extend Controller
 * 
 * @author Tom Andre Munkhaug <me@tombayo.com>
 * @package pipless
 * @subpackage traits 
 */
trait BasicWebpage {
  
  /**
   * Loads the view "view.classname.php", supplies some basic variables, and renders the view.
   */
  public static function index() {
    global $config;
    $template = Load::view('view.'.__CLASS__);
    $template->set('controller', __CLASS__);
    $template->set('lang', $config['language']);
    $template->render();
  }
}

/**
 * A trait for controllers supplying JSON-encoded data.
 * 
 * Calling setHeaders() will prepare the correct headers for the response.
 * Also implements a basic index()-method which can be used for testing the controller from the front-end.
 * 
 * @author Tom Andre Munkhaug <me@tombayo.com>
 * @package pipless
 * @subpackage traits
 * 
 */
trait JsonController {
  
  /**
   * Sets headers for JSON-data.
   */
  private static function setHeaders() {
    header("Content-Type: application/json; charset=UTF-8");
  }
  
  /**
   * Echoes "empty"=>"null" as JSON.
   */
  public static function index() {
    self::setHeaders();
    echo json_encode(['empty'=>'null']);
  }  
}

/**
 * A trait to enable calling static methods in an object context.
 * 
 * Used by controllers to enable them to be createable instances in order to
 * use their private static functions within other controllers.
 * 
 * @author Tom Andre Munkhaug <me@tombayo.com>
 * @package pipless
 * @subpackage traits
 *
 */
trait Objectify {
  
  /**
   * Tries to call any unaccessible methods in this class.
   * 
   * @param string $name
   * @param array $args
   * 
   * @return mixed
   */
  public function __call(string $name, array $args) {
    return $this->$name(...$args);
  }
}