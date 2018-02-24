<?php declare(strict_types=1);

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
  
  /**
   * Creates an url based on the application's config in respect to url rewrite
   * 
   * @param string $controller The controller to link to
   * @param string $method The method within the controller to link to (optional)
   * @param array $get Optional $_GET assoc-array to append to the url as a query
   * @param string $frag Optional fragment(after #) string to be appended to the url.
   */
  protected static function createUrl(string $controller, string $method='', array $get=[], string $frag=''):string {
    global $config;
    
    $path = $controller;
    $query = '';
    $url = '';
    
    if ($method) $path .= '/'.$method;
    if ($get) $query = http_build_query($get);
    
    if ($config['url_rewrite']) {
      $url = BASE_URL . $path;
      if ($query) $url .= '?'. $query;
    } else {
      $url = BASE_URL . '?p=' . $path;
      if ($query) $url .= '&' . $query;
    }
    
    if ($frag) $url .= '#'.$frag;
    
    return $url;
  }
  
  /**
   * Redirects the client to a different controller within the application.
   *
   * Also exits further code-execution.
   *
   * @param string $controller The controller to redirect to
   * @param string $method The method within the controller to redirect to (optional)
   * @param array $get Optional $_GET assoc-array to append to the url as a query
   * @param string $frag Optional fragment(after #) string to be appended to the url.
   */
  protected static function redirectCtrl(string $controller, string $method='', array $get=[], $frag='') {
    header('Location: '. self::createUrl($controller, $method, $get, $frag));
    exit;
  }
}