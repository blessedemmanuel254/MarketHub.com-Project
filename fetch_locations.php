<?php
include 'connection.php';

$country_id = intval($_POST['country_id'] ?? 0);

if (!$country_id) {
  echo json_encode([]);
  exit;
}

/*
==================================================
STEP 1: GET REGIONS UNDER COUNTRY
==================================================
*/
$stmt = $conn->prepare("
  SELECT location_id, name
  FROM locations
  WHERE parent_id = ? AND type = 'region'
  ORDER BY name ASC
");
$stmt->bind_param("i", $country_id);
$stmt->execute();
$result = $stmt->get_result();

$regions = [];

while ($row = $result->fetch_assoc()) {
  $row['counties'] = [];
  $regions[$row['location_id']] = $row;
}
$stmt->close();

/*
==================================================
STEP 2: GET COUNTIES UNDER ALL REGIONS
==================================================
*/
if (!empty($regions)) {

  $regionIds = array_keys($regions);
  $placeholders = implode(',', array_fill(0, count($regionIds), '?'));

  $types = str_repeat('i', count($regionIds));

  $sql = "
    SELECT location_id, name, parent_id
    FROM locations
    WHERE parent_id IN ($placeholders)
    AND type = 'county'
    ORDER BY name ASC
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$regionIds);
  $stmt->execute();
  $result = $stmt->get_result();

  $counties = [];

  while ($row = $result->fetch_assoc()) {
    $row['wards'] = [];
    $regions[$row['parent_id']]['counties'][$row['location_id']] = $row;
  }

  $stmt->close();
}

/*
==================================================
STEP 3: GET WARDS UNDER ALL COUNTIES
==================================================
*/
$allCountyIds = [];

foreach ($regions as $region) {
  foreach ($region['counties'] as $county) {
    $allCountyIds[] = $county['location_id'];
  }
}

if (!empty($allCountyIds)) {

  $placeholders = implode(',', array_fill(0, count($allCountyIds), '?'));
  $types = str_repeat('i', count($allCountyIds));

  $sql = "
    SELECT location_id, name, parent_id
    FROM locations
    WHERE parent_id IN ($placeholders)
    AND type = 'ward'
    ORDER BY name ASC
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$allCountyIds);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $countyId = $row['parent_id'];

    foreach ($regions as &$region) {
      if (isset($region['counties'][$countyId])) {
        $region['counties'][$countyId]['wards'][] = $row;
      }
    }
  }

  $stmt->close();
}

/*
==================================================
FINAL OUTPUT
==================================================
*/
echo json_encode(array_values($regions));
?>