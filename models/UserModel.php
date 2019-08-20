<?php
namespace models;

class UserModel {
  private $database;

  public function __construct ($db) {
    $this->database = $db;
  }

  public function get ($columns, $where, $values) {
    $this->database->select('User', $columns, $where, $values);
    return $this->database->result();
  }

  public function put ($name, $age, $email, $password, $newsletter) {
    $this->database->query(
      'INSERT INTO User (`name`, `age`, `email`, `password`, `newsletter`) VALUES (?, ?, ?, ?, ?)',
      [
        [$name, 'str'],
        [$age, 'str'],
        [$email, 'str'],
        [$password, 'str'],
        [$newsletter, 'int']
      ]
    );
  }

  public function update ($columns, $values) {
    # ID HAS TO BE LAST VALUE
    $this->database->update('User', $columns, ['id'], $values);
  }
}
