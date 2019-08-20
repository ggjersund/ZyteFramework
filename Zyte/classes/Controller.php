<?php
/**
*
* The ZyteFrameworks default extendable controller class
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

class Controller {

  /**
  * Controller variables
  *
  * @var array  Parameter array
  * @var object Payload data as object
  */
  protected $parameters = [];
  protected $payload;

  /**
  * Add parameters and payload to variables
  *
  * @param array Parameters from valid route
  */
  public function __construct ($param) {
    $this->parameters = $param;
    $this->payload = json_decode(file_get_contents('php://input'));
  }

}
