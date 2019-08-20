<?php
/**
*
* The ZyteFrameworks database query class
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

class Query extends Database {

  /**
  * Query variables
  *
  * @var  boolean   Error has occured
  * @var  string    Error message
  * @var  object    Query instance
  */
  private $error = false;
  private $message = NULL;
  private $query = NULL;

  /**
  * Prepare database for query
  *
  * @access   private
  * @param   	string  	SQL query to be performed
  */
  private function prepare ($sql) {
    $this->query = $this->connection->prepare($sql);
  }

  /**
  * Execute database query
  *
  * @access   private
  */
  private function execute () {
    $this->query->execute();
  }

  /**
  * Get query result
  *
  * @access   public
  * @return   object    Query result
  */
  public function result () {
    return $this->query->fetchAll(PDO::FETCH_OBJ);
  }

  /**
  * Count affected rows
  *
  * @access   public
  * @return   integer   Number of affected rows
  */
  public function counter () {
    return $this->query->rowCount();
  }

  /**
  * Error handling
  *
  * @access   public
  * @return   array     If error and message if error
  */
  public function error () {
    return array($this->error, $this->message);
  }

  /**
  * Query value binding
  *
  * @access   private
  * @param    array     Values to bind [[value, type], +]
  */
  private function bindValues ($values) {
    $i = 1;
    foreach($values as $v) {
      switch ($v[1]) {
        case 'str':
          $this->query->bindValue($i, $v[0], PDO::PARAM_STR);
          break;
        case 'int':
          $this->query->bindValue($i, $v[0], PDO::PARAM_INT);
          break;
        default:
          $this->query->bindValue($i, $v[0], PDO::PARAM_STR);
      };
      $i++;
    };
  }

  /**
  * Query insert statement
  *
  * INSERT INTO $table ($columns) VALUES ($values)
  *
  * @access   public
  * @param    string    Table to insert to
  * @param    array     Table columns as array [column1, column2]
  * @param    array     Values to insert into columns [[value, type], +]
  * @return   boolean   Insert without error
  */
  public function insert ($table, $columns, $values) {
    try {
      $sql = 'INSERT INTO ' . $table . '(';
      foreach ($columns as $i) {
        $sql .= $i . ',';
      };
      $sql = rtrim($sql, ',') . ') VALUES(';
      for ($v = 0; $v < count($columns); $v++) {
        $sql .= '?,';
      };
      $sql = rtrim($sql, ',') . ')';
      $this->prepare($sql);
      $this->bindValues($values);
      $this->execute();
      return true;
    } catch (PDOException $exception) {
      $this->error = true;
      $this->message = $exception;
      return false;
    };
  }

  /**
  * Query delete statement
  *
  * DELETE FROM $table WHERE [ $columns = ? ] -> $values
  *
  * @access   public
  * @param    string    Table to delete from
  * @param    array     Comparing columns [column1, column2]
  * @param    array     Values found in columns [[value, type], +]
  * @return   boolean   Deleting without error
  */
  public function delete ($table, $wcolumns, $values) {
    try {
      $sql = 'DELETE FROM ' . $table . ' WHERE ';
      foreach ($wcolumns as $w) { $sql .= $w . '=? AND '; };
      $sql = rtrim($sql, ' AND ');
      $this->prepare($sql);
      $this->bindValues($values);
      $this->execute();
      return true;
    } catch (PDOException $exception) {
      $this->error = true;
      $this->message = $exception;
      return false;
    };
  }

  /**
  * Query select statement
  *
  * SELECT $select FROM $table WHERE [ $columns = ? ] -> $values
  *
  * @access   public
  * @param    string    Table to select from
  * @param    array     Columns to select [column1, column2]
  * @param    array     Comparing columns [column1, column2]
  * @param    array     Values found in comparing columns [[value, type], +]
  * @return   boolean   Select without error
  */
  public function select ($table, $scolumns, $wcolumns, $values) {
    try {
      $sql = 'SELECT ';
      foreach ($scolumns as $s) { $sql .= $s . ','; };
      $sql = rtrim($sql, ',');
      $sql .= ' FROM ' . $table . ' WHERE ';
      foreach ($wcolumns as $w) { $sql .= $w . '=? AND '; };
      $sql = rtrim($sql, ' AND ');
      $this->prepare($sql);
      $this->bindValues($values);
      $this->execute();
      return true;
    } catch (PDOException $exception) {
      $this->error = true;
      $this->message = $exception;
      return false;
    };
  }

  /**
  * Query update statement
  *
  * UPDATE $table SET [ $ucolumns = ? WHERE $wcolumns = ? ] -> $values
  *
  * @access   public
  * @param    string    Table to update
  * @param    array     Columns to update [column1, column2]
  * @param    array     Comparing columns [column1, column2]
  * @param    array     Update values and values in compare columns [[value, type], +]
  * @return   boolean   Update without error
  */
  public function update ($table, $ucolumns, $wcolumns, $values) {
    try {
      $sql = 'UPDATE ' . $table . ' SET ';
      foreach ($ucolumns as $u) { $sql .= $u . '= ?, '; };
      $sql = rtrim($sql, ', ');
      $sql .= ' WHERE ';
      foreach ($wcolumns as $w) { $sql .= $w . '= ? AND '; };
      $sql = rtrim($sql, ' AND ');
      $this->prepare($sql);
      $this->bindValues($values);
      $this->execute();
      return true;
    } catch (PDOException $exception) {
      $this->error = true;
      $this->message = $exception;
      return false;
    };
  }

  /**
  * Query default statement
  *
  * SQL -> $values
  *
  * @access   public
  * @param    string    SQL statement
  * @param    array     Values to bind in SQL statement [[value, type], +]
  * @return   boolean   Query without error
  */
  public function query ($sql, $values = NULL) {
    try {
      $this->prepare($sql);
      if ($values) {
        $this->bindValues($values);
      }
      $this->execute();
      return true;
    } catch (PDOException $exception) {
      $this->error = true;
      $this->message = $exception;
      return false;
    };
  }

  public function lastInsertId () {
    return intval($this->connection->lastInsertId());
  }

}
