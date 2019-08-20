<?php
namespace models;

class TokenModel {
  private $database;

  public function __construct ($db) {
    $this->database = $db;
  }

  public function get ($columns, $id) {
    $this->database->select('AuthToken', $columns, ['id'], [[$id, 'int']]);
    return $this->database->result();
  }

  public function put ($id, $expire, $remember) {
    $this->database->insert('AuthToken',
      ['user','expire','remember','device','ip'],
      [[$id, 'int'],
      [$expire, 'str'],
      [$remember, 'int'],
      [$_SERVER['HTTP_USER_AGENT'], 'str'],
      [$_SERVER['REMOTE_ADDR'], 'str']]);
  }

  public function update ($id, $expire) {
    $this->database->query(
      'UPDATE AuthToken SET `expire` = ? WHERE `id` = ?',
      [[$expire, 'str'],[$id, 'int']]
    );
  }

  public function delete ($id) {
    $this->database->query(
      'DELETE FROM AuthToken WHERE `id` = ?',
      [[$id, 'int']]
    );
  }
}
