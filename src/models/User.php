<?php

class User
{
  private $db;

  public function __construct(PDO $db) /*Dependency Injection*/
  {
    $this->db = $db;
  }

  public function findByEmail($email)
  {
    $sql = "select * from users where email = :email limit 1";
    $stmt = $this->db->prepare($sql); //prepared statements to block sql injection attacks
    $stmt->execute([
      ':email' => $email
    ]);

    return $stmt->fetch();
  }

  public function create($fullName, $email, $passwordHash, $role, $contactNumber, $gender)
  {
    $sql = "insert into users(full_name, email, password_hash, role, contact_number, gender) values( :full_name,:email,:password_hash,:role,:contact_number,:gender)";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
      ':full_name' => $fullName,
      ':email' => $email,
      ':password_hash' => $passwordHash,
      ':role' => $role,
      ':contact_number' => $contactNumber,
      ':gender' => $gender
    ]);
  }
}
