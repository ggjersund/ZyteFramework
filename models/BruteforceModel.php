<?php
namespace models;

class BruteforceModel {
  private $database;

  public function __construct ($db) {
    $this->database = $db;
  }

  public function get ($user, $ip) {
    $this->database->query(
      'SELECT `id`, `count`, `datetime` FROM Bruteforce WHERE user = ? AND ip = ?',
      [[$user, 'int'], [$ip, 'str']]
    );
    return $this->database->result();
  }

  public function put ($user, $ip) {
    $this->database->query(
      'INSERT INTO Bruteforce (`user`, `count`, `ip`, `datetime`) VALUES (?, 1, ?, NOW())',
      [[$user, 'int'], [$ip, 'str']]
    );
  }

  public function add ($user, $ip) {
    $this->database->query(
      'UPDATE Bruteforce SET `count` = `count` + 1, `datetime` = NOW() WHERE `user` = ? AND `ip` = ?',
      [[$user, 'int'], [$ip, 'str']]
    );
  }

  public function reset ($user, $ip) {
    $this->database->query(
      'UPDATE Bruteforce SET `count` = 1, `datetime` = NOW() WHERE `user` = ? AND `ip` = ?',
      [[$user, 'int'], [$ip, 'str']]
    );
  }

  public function delete ($user, $ip) {
    $this->database->query(
      'DELETE FROM Bruteforce WHERE `user` = ? AND `ip` = ?',
      [[$user, 'int'], [$ip, 'str']]
    );
  }
}
