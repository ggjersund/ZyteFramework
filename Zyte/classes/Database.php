<?php
/**
*
* The ZyteFrameworks database class
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

use \PDO as PDO;

class Database {

  /**
  * Database variables
  *
  * @var  mixed   Anonymous function for database instance creation
  * @var  object  Database connection object
  */
  protected $provider = NULL;
  protected $connection = NULL;

  /**
  * Construct database connection provider
  *
  * @access   public
  */
  public function __construct () {
    $this->provider = function () {
      $host = 'mysql:host=' . ZYTE_DB_HOST . ';';
      $database = 'dbname=' . ZYTE_DB_NAME . ';';
      $charset = 'charset=' . ZYTE_DB_CHARSET;
      $username = ZYTE_DB_USERNAME;
      $password = ZYTE_DB_PASSWORD;
      $instance = new PDO($host . $database . $charset, $username, $password);
      $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      return $instance;
    };
  }

  /**
  * Construct database connection provider
  *
  * @access   public
  */
  public function connect () {
    if ($this->connection === NULL) {
      $this->connection = call_user_func($this->provider);
    };
  }

}
