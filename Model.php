<?php declare(strict_types=1);

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