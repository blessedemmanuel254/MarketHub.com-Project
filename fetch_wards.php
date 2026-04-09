<?php
include 'connection.php';

if (isset($_POST['county_id'])) {
    $county_id = intval($_POST['county_id']);

    $stmt = $conn->prepare("
        SELECT location_id, name 
        FROM locations 
        WHERE parent_id = ? AND type = 'ward'
        ORDER BY name ASC
    ");
    $stmt->bind_param("i", $county_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $wards = [];
    while ($row = $result->fetch_assoc()) {
        $wards[] = $row;
    }

    echo json_encode($wards);
}