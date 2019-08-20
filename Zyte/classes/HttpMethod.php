<?php
/**
*
* The ZyteFrameworks request method class
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

class RequestMethod {

  /**
  * Request method variable
  *
  * @var  string  Current request method used
  */
  private $method;

  /**
  * Construct the request method variable
  *
  * @access   public
  */
  public function __construct () {
    $this->method = $_SERVER['REQUEST_METHOD'];
  }

  /**
  * Get current request method
  *
  * @access   public
  * @return   string  The current request method
  */
  public function get () {
    return $this->method;
  }
  
}
