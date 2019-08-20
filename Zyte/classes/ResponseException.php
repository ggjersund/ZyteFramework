<?php
/**
*
* The ZyteFrameworks Custom Response Exception class
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

class ResponseException extends \Exception {

  /**
  * Exception variable
  *
  * @var  array   Exception array
  */
  private $exception;

  /**
  * Construct exception response
  *
  * @access   public
  * @param   	integer     Response code
  * @param    string      User response message
  * @param    string      Developer response message
  * @param    integer     ZyteFramework error code
  * @param    string      URL to ZyteFramework error documentation
  * @param    object      Previous exception object
  */
  public function __construct ($status, $status_msg, $dev_msg, $code, $href, Exception $previous = NULL) {
    $this->exception = [
      'status' => $status,
      'status_msg' => $status_msg,
      'code' => $code,
      'dev_msg' => $dev_msg,
      'href' => $href
    ];

    parent::__construct($status, $code, $previous);
  }

  /**
  * Get exception array
  *
  * @access   public
  * @return   array       Exception array
  */
  public function exception () {
    return $this->exception;
  }

}
