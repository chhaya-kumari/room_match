<?php

class User
{
  private $db;

  public function __construct(PDO $db) /*Dependency Injection*/
  {
    $this->db = $db;
  }

  public function findById($userId)
  {
    $sql = "select * from users where user_id = :user_id limit 1";
    $stmt = $this->db->prepare($sql); //prepared statements to block sql injection attacks
    $stmt->execute([
      ':user_id' => $userId
    ]);
    return $stmt->fetch();
  }

  public function findByEmail($email)
  {
    $sql = "select * from users where email = :email limit 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':email' => $email
    ]);
    return $stmt->fetch();
  }

  public function findByContact($contactNumber)
  {
    $sql = "select * from users where contact_number = :contact limit 1";
    $stmt = $this->db->prepare($sql); //prepared statements to block sql injection attacks
    $stmt->execute([
      ':contact' => $contactNumber
    ]);
    return $stmt->fetch();
  }

  public function findProfile($userId)
  {
    $sql = "select user_id, full_name, email, contact_number, gender, role, created_at, updated_at from users where user_id = :user_id limit 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);

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
