<?php declare(strict_types=1);

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
   * @return mixed Same as the parameter $val.
   */
  public function set(string $var, $val)  {
    $this->pageVars[$var] = $val;
    return $val;
  }
  
  /**
   * Gets an already set variable in the template.
   * 
   * @param string $var Name of variable.
   */
  public function get(string $var) {
    return $this->pageVars[$var];
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