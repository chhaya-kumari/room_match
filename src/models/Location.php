<?php

class Location
{
  private $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function findByDetails($state, $city, $area)
  {
    $sql = "select location_id from locations where state = :state and city = :city and area= :area limit 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":state" => $state, ":city" => $city, ":area" => $area]);

    $locationId = $stmt->fetchColumn();

    return $locationId !== false ? $locationId : null;
  }

  public function findByLocationId($locationId)
  {
    $sql = "select * from locations where location_id = :location_id  limit 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':location_id' => $locationId
    ]);

    $location = $stmt->fetch();

    return $location ?: null;
  }

  public function getStates()
  {
    $sql = "select distinct state from locations order by state";

    $stmt = $this->db->query($sql);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function getCitiesByState($state)
  {
    $sql = "select distinct city from locations where state = :state order by city";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([":state" => $state]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function getAreasByCity($state, $city)
  {
    $sql = "select location_id, area from locations where state = :state and city = :city order by area";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ":state" => $state,
      ":city" => $city
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
