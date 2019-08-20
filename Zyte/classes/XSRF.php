<?php
/**
*
* The ZyteFrameworks XSRF class
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

class XSRF extends Cookie {

  /**
  * Create XSRF cookie
  *
  * @access   public
  * @param    string    Cookie path
  * @param    string    Cookie domain
  * @param    boolean   Cookie over SSL
  * @param    boolean   Cookie not accessible over javascript
  * @return   boolean   Set cookie was successfull
  */
  public function create ($path, $domain, $secure, $httponly) {
    return (!$this->get('XSRF-TOKEN')
      ? ($this->put('XSRF-TOKEN', bin2hex(openssl_random_pseudo_bytes(32)), NULL, $path, $domain, $secure, $httponly)
        ? true
        : false)
      : false);
  }

  /**
  * Validate XSRF cookie
  *
  * @access   public
  * @return   boolean   Valid XSRF cookie
  */
  public function validate () {
    return ($this->get('XSRF-TOKEN'))
      ? ((!empty($_SERVER['HTTP_X_XSRF_TOKEN']))
        ? (($this->get('XSRF-TOKEN') === $_SERVER['HTTP_X_XSRF_TOKEN'])
          ? true
          : false)
        : false)
      : false;
  }

}
