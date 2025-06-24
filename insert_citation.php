<?php
session_start();
header('Content-Type: application/json');

require 'config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate CSRF token
    $receivedToken = $_POST['csrf_token'] ?? 'null';
    $sessionToken = $_SESSION['csrf_token'] ?? 'null';
    error_log("Received CSRF Token: $receivedToken, Session CSRF Token: $sessionToken");
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

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

    // Handle vehicle type
    $vehicle_types = [];
    $vehicle_type_checkboxes = ['motorcycle', 'tricycle', 'suv', 'van', 'jeep', 'truck', 'kulong', 'othersVehicle'];
    foreach ($vehicle_type_checkboxes as $type) {
        if (isset($_POST[$type]) && !empty($_POST[$type])) {
            $vehicle_types[] = ($type === 'othersVehicle' && !empty($_POST['other_vehicle_input'])) 
                ? sanitize($_POST['other_vehicle_input']) 
                : ucfirst(str_replace('othersVehicle', 'Others', $type));
        }
    }
    $vehicle_type = !empty($vehicle_types) ? implode(', ', $vehicle_types) : null;

    $vehicle_description = sanitize($_POST['vehicle_description'] ?? '');
    $apprehension_datetime = sanitize($_POST['apprehension_datetime'] ?? '');
    $place_of_apprehension = sanitize($_POST['place_of_apprehension'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');

    // Handle violations
    $violations = isset($_POST['violations']) && is_array($_POST['violations']) ? array_map('sanitize', $_POST['violations']) : [];
    if (isset($_POST['other_violation']) && !empty($_POST['other_violation_input'])) {
        $other_violation = sanitize($_POST['other_violation_input']);
        if (strlen($other_violation) > 0) {
            $violations[] = $other_violation;
        }
    }
    error_log("Received Violations: " . print_r($violations, true));

    // Validate required fields
    $required_fields = [
        'ticket_number' => $ticket_number,
        'last_name' => $last_name,
        'first_name' => $first_name,
        'barangay' => $barangay,
        'license_number' => $license_number,
        'license_type' => $license_type,
        'plate_mv_engine_chassis_no' => $plate_mv_engine_chassis_no,
        'vehicle_type' => $vehicle_type,
        'apprehension_datetime' => $apprehension_datetime,
        'place_of_apprehension' => $place_of_apprehension
    ];
    foreach ($required_fields as $field_name => $value) {
        if (empty($value)) {
            throw new Exception("Missing required field: $field_name");
        }
    }

    // Validate violations
    if (empty($violations)) {
        throw new Exception("At least one violation must be selected");
    }
    $valid_violations_stmt = $conn->query("SELECT violation_type FROM violation_types");
    $valid_violations = $valid_violations_stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($violations as $violation) {
        if (!in_array($violation, $valid_violations) && $violation !== sanitize($_POST['other_violation_input'])) {
            error_log("Skipping invalid violation: $violation");
            // Skip custom violations for now
            continue;
        }
    }

    // Check for duplicate ticket number
    $stmt = $conn->prepare("SELECT COUNT(*) FROM citations WHERE ticket_number = :ticket_number");
    $stmt->execute([':ticket_number' => $ticket_number]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ticket number $ticket_number already exists");
    }

    $conn->beginTransaction();

    // Check for existing driver
    $stmt = $conn->prepare("SELECT driver_id FROM drivers WHERE license_number = :license_number");
    $stmt->execute([':license_number' => $license_number]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($driver) {
        $driver_id = $driver['driver_id'];
    } else {
        // Insert new driver
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
            ':middle_initial' => $middle_initial ?: null,
            ':suffix' => $suffix ?: null,
            ':zone' => $zone ?: null,
            ':barangay' => $barangay,
            ':municipality' => $municipality,
            ':province' => $province,
            ':license_number' => $license_number,
            ':license_type' => $license_type
        ]);
        $driver_id = $conn->lastInsertId();
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
        ':vehicle_description' => $vehicle_description ?: null
    ]);
    $vehicle_id = $conn->lastInsertId();

    // Insert into citations table
    $stmt = $conn->prepare("
        INSERT INTO citations (
            ticket_number, driver_id, vehicle_id, apprehension_datetime, place_of_apprehension,
            payment_status, payment_amount, payment_date
        ) VALUES (
            :ticket_number, :driver_id, :vehicle_id, :apprehension_datetime, :place_of_apprehension,
            'Unpaid', 0.00, NULL
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

    // Insert violations
    if (!empty($violations)) {
        $stmt_count = $conn->prepare("
            SELECT COUNT(*) AS count 
            FROM violations 
            WHERE driver_id = :driver_id AND violation_type = :violation_type
        ");
        $stmt_fine = $conn->prepare("
            SELECT fine_amount_1, fine_amount_2, fine_amount_3 
            FROM violation_types 
            WHERE violation_type = :violation_type
        ");
        $insertStmt = $conn->prepare("
            INSERT INTO violations (
                citation_id, driver_id, violation_type, offense_count, fine_amount
            ) VALUES (
                :citation_id, :driver_id, :violation_type, :offense_count, :fine_amount
            )
        ");
        foreach ($violations as $violation) {
            if (!in_array($violation, $valid_violations)) {
                error_log("Skipping invalid violation: $violation");
                continue;
            }
            $stmt_count->execute([':driver_id' => $driver_id, ':violation_type' => $violation]);
            $offense_count = min($stmt_count->fetchColumn() + 1, 3);
            $stmt_fine->execute([':violation_type' => $violation]);
            $fines = $stmt_fine->fetch(PDO::FETCH_ASSOC);
            $fine_amount = $fines ? $fines["fine_amount_$offense_count"] : 500.00;
            $insertStmt->execute([
                ':citation_id' => $citation_id,
                ':driver_id' => $driver_id,
                ':violation_type' => $violation,
                ':offense_count' => $offense_count,
                ':fine_amount' => $fine_amount
            ]);
            error_log("Inserted violation: citation_id=$citation_id, violation=$violation, offense_count=$offense_count, fine_amount=$fine_amount");
        }
    } else {
        $conn->rollBack();
        throw new Exception("No valid violations provided");
    }

    // Insert remarks
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
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo json_encode([
        'status' => 'success',
        'message' => 'Citation recorded successfully',
        'citation_id' => $citation_id,
        'new_csrf_token' => $_SESSION['csrf_token']
    ]);
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("PDOException: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Exception: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn = null;
?>