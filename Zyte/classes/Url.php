<?php
/**
*
* The ZyteFrameworks URL class
*
* @package            ZyteFramework
* @subpackage         classes
* @author             ZyteFramework Open Source Team
* @copyright          Copyright (c) 2017 - 2017, ZyteFramework
* @license            Open Source
* @link               https://zyteframework.com/
*
*/

namespace Zyte\classes;

class Url {

  /**
  * Full URL variable
  *
  * @var  string    Full URL
  */
  private $url;

  /**
  * Construct URL
  *
  * @access   public
  */
  public function __construct () {
    if ($this->secure()) {
      $this->url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    } else {
      $this->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
  }

  /**
  * Check if protocol is secure
  *
  * @access   public
  * @return   boolean   Protocol is HTTPS
  */
  public function secure () {
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] === 443)) ? true : false;
  }

  /**
  * Get URL path
  *
  * @access   public
  * @return   array     URL path
  */
  public function path () {
    return array_values(array_filter(explode('/', rtrim(ltrim(parse_url($this->url, PHP_URL_PATH), '/'), '/'))));
  }

  /**
  * Get URL parameters
  *
  * @access   public
  * @return   array     URL parameters
  */
  public function parameters () {
    return array_filter(explode('&', parse_url($this->url, PHP_URL_QUERY)));
  }

}
