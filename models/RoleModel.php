<?php
namespace models;

class RoleModel {
  private $database;

  public function __construct ($db) {
    $this->database = $db;
  }

  public function get ($user, $company) {
    $this->database->query(
      'SELECT r.type FROM Role r LEFT JOIN UserRole ur ON r.id = ur.role WHERE ur.user = ? AND r.company = ?',
      [[$user, 'int'], [$company, 'int']]
    );
    return $this->database->result();
  }
}
