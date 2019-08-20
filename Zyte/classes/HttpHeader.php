<?php
/**
*
* The ZyteFrameworks http header class
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

class HttpHeader {

  /**
  * Get all HTTP request headers
  *
  * @access   public
  * @return   array     List of all request headers
  */
  public function getRequestHeader () {
    return apache_request_headers();
  }

  /**
  * Set HTTP response headers
  *
  * @access   public
  * @param    array     Array of headers to set
  */
  public function setResponseHeader ($headers) {
    foreach ($headers as $h) {
      header($h);
    }
  }

  /**
  * Set HTTP response code
  *
  * @access   public
  * @param    integer   HTTP Response Code
  */
  public function setResponseCode ($code) {
    http_response_code($code);
  }

  /**
  * Use TLS on HTTP response
  *
  * @access   public
  * @param    integer   HTTP Response Code
  */
  public function setResponseTls () {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  }

}
