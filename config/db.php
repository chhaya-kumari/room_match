<?php
class Database
{
  private $servername = "localhost";
  private $username = "root";
  private $password = "";
  private $dbname = "pg_recommendation_system";

  public $conn = null;

  public function __construct()
  {
    try {

      $dsn = "mysql:host={$this->servername};dbname={$this->dbname}; charset=utf8mb4";

      $this->conn = new PDO(
        $dsn,
        $this->username,
        $this->password
      );


      // set the PDO error mode to exception
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // set attribute for default fetch mode
      
      $this->conn->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
      );

      //echo "Connected successfully";
    } catch (PDOException $e) {
      die("Database connection failed.");
    }
  }
}
