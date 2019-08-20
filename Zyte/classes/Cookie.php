<?php
/**
*
* The ZyteFrameworks cookie class
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

class Cookie {

  /**
  * Return a selected cookie
  *
  * @access   public
  * @param    string Name of cookie as string
  * @return   string Value of cookie as string
  */
  public function get ($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
  }

  /**
  * Delete a selected cookie
  *
  * @access   public
  * @param    string    Name of cookie as string
  * @param    string    Path of cookie as string
  * @param    string    Domain of cookie as string
  * @param    boolean   Cookie ONLY transfered via SSL
  * @param    boolean   Cookie accessable via javascript
  * @return   boolean   Cookie was removed
  */
  public function delete ($name, $path, $domain, $secure, $httponly) {
    setcookie($name, '', time() - (60 * 60 * 24 * 90), $path, $domain, $secure, $httponly);
    unset($_COOKIE[$name]);
  }

  /**
  * Create a cookie
  *
  * @access   public
  * @param    string    Name of cookie as string
  * @param    mixed     Value of cookie
  * @param    integer   Expiration of cookie as UNIX Timestamp
  * @param    string    Path of cookie as string
  * @param    string    Domain of cookie as string
  * @param    boolean   Cookie ONLY transfered via SSL
  * @param    boolean   Cookie accessable via javascript
  * @return   boolean   Cookie was added
  */
  public function put ($name, $value, $expire = 0, $path, $domain, $secure, $httponly) {
    setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
  }

}
