<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "traffic_citation_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to sanitize input
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Sanitize inputs
    $ticket_number = sanitize($_POST['ticket_number'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $first_name = sanitize($_POST['first_name'] ?? '');
    $middle_initial = sanitize($_POST['middle_initial'] ?? '');
    $suffix = sanitize($_POST['suffix'] ?? '');
    $zone = sanitize($_POST['zone'] ?? '');
    $barangay = sanitize($_POST['barangay'] ?? '');
    $municipality = sanitize($_POST['municipality'] ?? 'Baggao');
    $province = sanitize($_POST['province'] ?? 'Cagayan');
    $license_number = sanitize($_POST['license_number'] ?? '');
    $license_type = isset($_POST['license_type']) ? ($_POST['license_type'] === 'prof' ? 'Professional' : 'Non-Professional') : null;
    $plate_mv_engine_chassis_no = sanitize($_POST['plate_mv_engine_chassis_no'] ?? '');

    // Handle vehicle type (checkboxes)
    $vehicle_types = [];
    $vehicle_type_checkboxes = ['motorcycle', 'tricycle', 'suv', 'van', 'jeep', 'truck', 'kulong', 'othersVehicle'];
    foreach ($vehicle_type_checkboxes as $type) {
        if (isset($_POST[$type]) && $_POST[$type]) {
            $vehicle_types[] = ($type === 'othersVehicle' && !empty($_POST['other_vehicle_input'])) 
                ? sanitize($_POST['other_vehicle_input']) 
                : ucfirst($type);
        }
    }
    $vehicle_type = !empty($vehicle_types) ? implode(', ', $vehicle_types) : 'Unknown';

    $vehicle_description = sanitize($_POST['vehicle_description'] ?? '');
    $apprehension_datetime = sanitize($_POST['apprehension_datetime'] ?? '');
    $place_of_apprehension = sanitize($_POST['place_of_apprehension'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');

    // Handle violations (checkboxes)
$violation_checkboxes = [
    'noHelmetDriver' => 'No Helmet (Driver)',
    'noHelmetBackrider' => 'No Helmet (Backrider)',
    'noLicense' => 'No Driver’s License / Minor',
    'expiredReg' => 'No / Expired Vehicle Registration',
    'defectiveAccessories' => 'No / Defective Parts & Accessories',
    'recklessDriving' => 'Reckless / Arrogant Driving',
    'disregardingSigns' => 'Disregarding Traffic Sign',
    'illegalModification' => 'Illegal Modification',
    'passengerOnTop' => 'Passenger on Top of the Vehicle',
    'loudMuffler' => 'Noisy Muffler (98db above)',
    'noMuffler' => 'No Muffler Attached',
    'illegalParking' => 'Illegal Parking',
    'roadObstruction' => 'Road Obstruction',
    'blockingPedestrianLane' => 'Blocking Pedestrian Lane',
    'loadingUnloadingProhibited' => 'Loading/Unloading in Prohibited Zone',
    'doubleParking' => 'Double Parking',
    'drunkDriving' => 'Drunk Driving',
    'colorumOperation' => 'Colorum Operation',
    'noTrashBin' => 'No Trashbin',
    'drivingInShortSando' => 'Driving in Short / Sando',
    'overloadedPassenger' => 'Overloaded Passenger',
    'overUnderCharging' => 'Over Charging / Under Charging',
    'refusalToConvey' => 'Refusal to Convey Passenger/s',
    'dragRacing' => 'Drag Racing',
    'noOplanVisaSticker' => 'No Enhanced Oplan Visa Sticker',
    'noEovMatchCard' => 'Failure to Present E-OV Match Card',
    'otherViolation' => !empty($_POST['other_violation_input']) ? sanitize($_POST['other_violation_input']) : null

    ];
    foreach ($violation_checkboxes as $key => $value) {
        if (isset($_POST[$key]) && $_POST[$key] && $value !== null) {
            $violations[] = $value;
        }
    }

    // Check for duplicate ticket number
    $stmt = $conn->prepare("SELECT COUNT(*) FROM citations WHERE ticket_number = :ticket_number");
    $stmt->execute(['ticket_number' => $ticket_number]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ticket number $ticket_number already exists");
    }

    $conn->beginTransaction();

    // Check if driver_id is provided (for existing drivers)
    if (isset($_POST['driver_id']) && !empty($_POST['driver_id'])) {
        $driver_id = (int)$_POST['driver_id'];
        // Verify driver exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM drivers WHERE driver_id = :driver_id");
        $stmt->execute([':driver_id' => $driver_id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Driver ID $driver_id does not exist in drivers table");
        }
    } else {
        // Insert into drivers table for new driver
        $stmt = $conn->prepare("
            INSERT INTO drivers (
                last_name, first_name, middle_initial, suffix, zone, barangay, municipality, 
                province, license_number, license_type
            ) VALUES (
                :last_name, :first_name, :middle_initial, :suffix, :zone, :barangay, :municipality, 
                :province, :license_number, :license_type
            )
        ");
        $stmt->execute([
            ':last_name' => $last_name,
            ':first_name' => $first_name,
            ':middle_initial' => $middle_initial,
            ':suffix' => $suffix,
            ':zone' => $zone,
            ':barangay' => $barangay,
            ':municipality' => $municipality,
            ':province' => $province,
            ':license_number' => $license_number,
            ':license_type' => $license_type
        ]);
        $driver_id = $conn->lastInsertId();
        if (!$driver_id) {
            throw new Exception("Failed to insert driver record");
        }
    }

    // Insert into vehicles table
    $stmt = $conn->prepare("
        INSERT INTO vehicles (
            plate_mv_engine_chassis_no, vehicle_type, vehicle_description
        ) VALUES (
            :plate_mv_engine_chassis_no, :vehicle_type, :vehicle_description
        )
    ");
    $stmt->execute([
        ':plate_mv_engine_chassis_no' => $plate_mv_engine_chassis_no,
        ':vehicle_type' => $vehicle_type,
        ':vehicle_description' => $vehicle_description
    ]);
    $vehicle_id = $conn->lastInsertId();

    // Insert into citations table
    $stmt = $conn->prepare("
        INSERT INTO citations (
            ticket_number, driver_id, vehicle_id, apprehension_datetime, place_of_apprehension
        ) VALUES (
            :ticket_number, :driver_id, :vehicle_id, :apprehension_datetime, :place_of_apprehension
        )
    ");
    $stmt->execute([
        ':ticket_number' => $ticket_number,
        ':driver_id' => $driver_id,
        ':vehicle_id' => $vehicle_id,
        ':apprehension_datetime' => $apprehension_datetime,
        ':place_of_apprehension' => $place_of_apprehension
    ]);
    $citation_id = $conn->lastInsertId();

    // Insert violations with offense count
    if (!empty($violations)) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS count 
            FROM violations 
            WHERE driver_id = :driver_id AND violation_type = :violation_type
        ");
        $insertStmt = $conn->prepare("
            INSERT INTO violations (citation_id, driver_id, violation_type, offense_count)
            VALUES (:citation_id, :driver_id, :violation_type, :offense_count)
        ");
        foreach ($violations as $violation) {
            $stmt->execute([':driver_id' => $driver_id, ':violation_type' => $violation]);
            $offense_count = $stmt->fetchColumn() + 1;
            $insertStmt->execute([
                ':citation_id' => $citation_id,
                ':driver_id' => $driver_id,
                ':violation_type' => $violation,
                ':offense_count' => $offense_count
            ]);
        }
    }

    // Insert remarks if provided
    if (!empty($remarks)) {
        $stmt = $conn->prepare("
            INSERT INTO remarks (citation_id, remark_text)
            VALUES (:citation_id, :remark_text)
        ");
        $stmt->execute([
            ':citation_id' => $citation_id,
            ':remark_text' => $remarks
        ]);
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Citation recorded successfully']);
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}

$conn = null;
?>