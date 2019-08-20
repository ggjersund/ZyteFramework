<?php
namespace models;

class VerificationModel {
  /*
  PS: TYPES ARE 1 = REGISTRATION, 2 = FORGOTTEN PASSWORD
  */

  private $database;

  public function __construct ($db) {
    $this->database = $db;
  }

  public function put ($code, $user, $type, $expire) {
    $this->database->query(
      'INSERT INTO Verification (`code`, `user`, `type`, `expire`) VALUES (?, ?, ?, ?)',
      [
        [$code, 'str'],
        [$user, 'int'],
        [$type, 'int'],
        [$expire, 'str']
      ]
    );
  }
  public function deleteExisting ($user, $type) {
    $this->database->query(
      'DELETE FROM Verification WHERE `user` = ? AND `type` = ?',
      [[$user, 'int'], [$type, 'int']]
    );
  }
  public function getById ($id, $type, $columns) {
    $this->database->select('Verification', $columns, ['id', 'type'], [[$id, 'int'], [$type, 'int']]);
    return $this->database->result();
  }

  public function delete ($id, $type) {
    $this->database->query(
      'DELETE FROM Verification WHERE `id` = ? AND `type` = ?',
      [[$id, 'int'], [$type, 'int']]
    );
  }
}
