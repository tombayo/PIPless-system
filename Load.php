<?php declare(strict_types=1);

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
    require_once(APP_DIR .'plugins/'. $name .'.php');
  }
}