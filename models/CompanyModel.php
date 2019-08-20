<?php
namespace models;

class CompanyModel {
  private $database;

  public function __construct ($db) {
    $this->database = $db;
  }

  public function allowedAccess ($user) {
    $this->database->query(
      'SELECT
      c.id,
      c.logo,
      c.name AS companyname,
      c.description,
      c.orgnumber,
      c.url,
      r.type AS roletype,
      r.name AS rolename,
      (CASE
        WHEN r.type = 2 THEN "kunde"
        WHEN r.type = 3 THEN "leverandor"
        ELSE "ansatt"
      END) AS roletypename
      FROM Company c
      LEFT JOIN Role r ON c.id = r.company
      LEFT JOIN UserRole ur ON r.id = ur.role
      WHERE ur.user = ? AND r.company != ?',
      [[$user, 'int'], [0, 'int']]
    );
    return $this->database->result();
  }

}
