<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "traffic_citation_db";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Get driver_id and citation_id from the request
  $driver_id = isset($_GET['driver_id']) ? (int)$_GET['driver_id'] : 0;
  $citation_id = isset($_GET['citation_id']) ? (int)$_GET['citation_id'] : 0;

  if ($driver_id <= 0) {
    throw new Exception("Invalid driver ID");
  }

  // Fetch driver information
  $driver_stmt = $conn->prepare("SELECT license_number, last_name, first_name, middle_initial, suffix FROM drivers WHERE driver_id = :id");
  $driver_stmt->execute(['id' => $driver_id]);
  $driver = $driver_stmt->fetch(PDO::FETCH_ASSOC);
  if (!$driver) {
    throw new Exception("Driver not found");
  }

  $driver_name = $driver['last_name'] . ', ' . $driver['first_name'] . ($driver['middle_initial'] ? ' ' . $driver['middle_initial'] : '') . ($driver['suffix'] ? ' ' . $driver['suffix'] : '');

  // Determine which offenses to fetch based on whether citation_id is provided
  if ($citation_id > 0) {
    // Fetch offenses for the specific citation
    $offense_stmt = $conn->prepare("
      SELECT c.apprehension_datetime AS date_time, vl.violation_type AS offense, vl.offense_count,
             CASE vl.violation_type
               WHEN 'No Helmet (Driver)' THEN 150
               WHEN 'No Helmet (Backrider)' THEN 150
               WHEN 'No Driver’s License / Minor' THEN 500
               WHEN 'No / Expired Vehicle Registration' THEN 2500
               WHEN 'No / Defective Parts & Accessories' THEN 500
               WHEN 'Noisy Muffler (98db above)' THEN 
                 CASE vl.offense_count WHEN 1 THEN 2500 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'No Muffler Attached' THEN 2500
               WHEN 'Reckless / Arrogant Driving' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Disregarding Traffic Sign' THEN 150
               WHEN 'Illegal Modification' THEN 150
               WHEN 'Passenger on Top of the Vehicle' THEN 150
               WHEN 'Illegal Parking' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Road Obstruction' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Blocking Pedestrian Lane' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Loading/Unloading in Prohibited Zone' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Double Parking' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 1500 END
               WHEN 'Drunk Driving' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 1000 WHEN 3 THEN 1500 END
               WHEN 'Colorum Operation' THEN 
                 CASE vl.offense_count WHEN 1 THEN 2500 WHEN 2 THEN 3000 WHEN 3 THEN 3000 END
               WHEN 'No Trashbin' THEN 
                 CASE vl.offense_count WHEN 1 THEN 1000 WHEN 2 THEN 2000 WHEN 3 THEN 2500 END
               WHEN 'Driving in Short / Sando' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 1000 END
               WHEN 'Overloaded Passenger' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Over Charging / Under Charging' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Refusal to Convey Passenger/s' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Drag Racing' THEN 
                 CASE vl.offense_count WHEN 1 THEN 1000 WHEN 2 THEN 1500 WHEN 3 THEN 2500 END
               WHEN 'No Enhanced Oplan Visa Sticker' THEN 300
               WHEN 'Failure to Present E-OV Match Card' THEN 200
               ELSE 200
             END AS fine,
             c.payment_status AS status
      FROM citations c
      JOIN violations vl ON c.citation_id = vl.citation_id
      WHERE c.driver_id = :driver_id AND c.citation_id = :citation_id AND c.is_archived = 0
    ");
    $offense_stmt->execute(['driver_id' => $driver_id, 'citation_id' => $citation_id]);
  } else {
    // Fetch all offenses for the driver (used by Driver Info modal)
    $offense_stmt = $conn->prepare("
      SELECT c.apprehension_datetime AS date_time, vl.violation_type AS offense, vl.offense_count,
             CASE vl.violation_type
               WHEN 'No Helmet (Driver)' THEN 150
               WHEN 'No Helmet (Backrider)' THEN 150
               WHEN 'No Driver’s License / Minor' THEN 500
               WHEN 'No / Expired Vehicle Registration' THEN 2500
               WHEN 'No / Defective Parts & Accessories' THEN 500
               WHEN 'Noisy Muffler (98db above)' THEN 
                 CASE vl.offense_count WHEN 1 THEN 2500 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'No Muffler Attached' THEN 2500
               WHEN 'Reckless / Arrogant Driving' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Disregarding Traffic Sign' THEN 150
               WHEN 'Illegal Modification' THEN 150
               WHEN 'Passenger on Top of the Vehicle' THEN 150
               WHEN 'Illegal Parking' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Road Obstruction' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Blocking Pedestrian Lane' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Loading/Unloading in Prohibited Zone' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
               WHEN 'Double Parking' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 1500 END
               WHEN 'Drunk Driving' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 1000 WHEN 3 THEN 1500 END
               WHEN 'Colorum Operation' THEN 
                 CASE vl.offense_count WHEN 1 THEN 2500 WHEN 2 THEN 3000 WHEN 3 THEN 3000 END
               WHEN 'No Trashbin' THEN 
                 CASE vl.offense_count WHEN 1 THEN 1000 WHEN 2 THEN 2000 WHEN 3 THEN 2500 END
               WHEN 'Driving in Short / Sando' THEN 
                 CASE vl.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 1000 END
               WHEN 'Overloaded Passenger' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Over Charging / Under Charging' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Refusal to Convey Passenger/s' THEN 
                 CASE vl.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
               WHEN 'Drag Racing' THEN 
                 CASE vl.offense_count WHEN 1 THEN 1000 WHEN 2 THEN 1500 WHEN 3 THEN 2500 END
               WHEN 'No Enhanced Oplan Visa Sticker' THEN 300
               WHEN 'Failure to Present E-OV Match Card' THEN 200
               ELSE 200
             END AS fine,
             c.payment_status AS status
      FROM citations c
      JOIN violations vl ON c.citation_id = vl.citation_id
      WHERE c.driver_id = :id AND c.is_archived = 0
    ");
    $offense_stmt->execute(['id' => $driver_id]);
  }

  $offenses = $offense_stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'license_number' => $driver['license_number'],
    'driver_name' => $driver_name,
    'offenses' => $offenses
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
  http_response_code(404);
  echo json_encode(['error' => $e->getMessage()]);
}
$conn = null;
?>