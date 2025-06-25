<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    // Validate CSRF token
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Determine date range
    $period = filter_input(INPUT_GET, 'period', FILTER_SANITIZE_STRING) ?? 'yearly';
    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2025]]) ?? date('Y');
    $start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);

    $date_condition = '';
    $params = [];
    $max_date = '2025-06-30 23:59:59'; // Cap dates to June 30, 2025
    if ($period === 'custom' && $start_date && $end_date) {
        $start = DateTime::createFromFormat('Y-m-d', $start_date);
        $end = DateTime::createFromFormat('Y-m-d', $end_date);
        if (!$start || !$end || $start > $end || $end > new DateTime('2025-06-30')) {
            throw new Exception('Invalid date range');
        }
        $date_condition = "WHERE c.apprehension_datetime BETWEEN :start_date AND :end_date AND c.is_archived = 0";
        $params[':start_date'] = $start_date . ' 00:00:00';
        $params[':end_date'] = min($end_date . ' 23:59:59', $max_date);
    } else {
        $date_condition = "WHERE YEAR(c.apprehension_datetime) = :year AND c.apprehension_datetime <= :max_date AND c.is_archived = 0";
        $params[':year'] = $year;
        $params[':max_date'] = $max_date;
    }

    // Most common violations
    $violations_query = "
        SELECT v.violation_type, COUNT(*) AS count, COALESCE(SUM(v.fine_amount), 0) AS total_fines
        FROM violations v
        JOIN citations c ON v.citation_id = c.citation_id
        $date_condition
        GROUP BY v.violation_type
        ORDER BY count DESC
        LIMIT 10
    ";
    $stmt = $conn->prepare($violations_query);
    $stmt->execute($params);
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Barangays with most violations
    $barangays_query = "
        SELECT COALESCE(d.barangay, 'Unknown') AS barangay, COUNT(*) AS count
        FROM citations c
        JOIN drivers d ON c.driver_id = d.driver_id
        $date_condition
        GROUP BY d.barangay
        ORDER BY count DESC
        LIMIT 10
    ";
    $stmt = $conn->prepare($barangays_query);
    $stmt->execute($params);
    $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Payment status
    $payment_status_query = "
        SELECT c.payment_status AS status, COUNT(*) AS count
        FROM citations c
        $date_condition
        GROUP BY c.payment_status
    ";
    $stmt = $conn->prepare($payment_status_query);
    $stmt->execute($params);
    $payment_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Violation trends
    $trends_query = '';
    if ($period === 'monthly') {
        $trends_query = "
            SELECT DATE_FORMAT(c.apprehension_datetime, '%Y-%m') AS period, COUNT(*) AS count
            FROM citations c
            WHERE YEAR(c.apprehension_datetime) = :year AND c.apprehension_datetime <= :max_date AND c.is_archived = 0
            GROUP BY DATE_FORMAT(c.apprehension_datetime, '%Y-%m')
            ORDER BY period
        ";
    } elseif ($period === 'quarterly') {
        $trends_query = "
            SELECT CONCAT(YEAR(c.apprehension_datetime), ' Q', QUARTER(c.apprehension_datetime)) AS period, COUNT(*) AS count
            FROM citations c
            WHERE YEAR(c.apprehension_datetime) = :year AND c.apprehension_datetime <= :max_date AND c.is_archived = 0
            GROUP BY YEAR(c.apprehension_datetime), QUARTER(c.apprehension_datetime)
            ORDER BY period
        ";
    } else {
        $trends_query = "
            SELECT YEAR(c.apprehension_datetime) AS period, COUNT(*) AS count
            FROM citations c
            $date_condition
            GROUP BY YEAR(c.apprehension_datetime)
            ORDER BY period
        ";
    }
    $stmt = $conn->prepare($trends_query);
    $stmt->execute($params);
    $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vehicle types
    $vehicle_query = "
        SELECT COALESCE(v.vehicle_type, 'Unknown') AS vehicle_type, COUNT(*) AS count
        FROM citations c
        JOIN vehicles v ON c.vehicle_id = v.vehicle_id
        $date_condition
        GROUP BY v.vehicle_type
        ORDER BY count DESC
    ";
    $stmt = $conn->prepare($vehicle_query);
    $stmt->execute($params);
    $vehicle_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Repeat offenders
    $repeat_offenders_query = "
        SELECT CONCAT(d.first_name, ' ', d.last_name) AS driver_name, COALESCE(d.license_number, 'N/A') AS license_number,
               COUNT(c.citation_id) AS citation_count, COALESCE(SUM(v.fine_amount), 0) AS total_fines
        FROM citations c
        JOIN drivers d ON c.driver_id = d.driver_id
        JOIN violations v ON c.citation_id = v.citation_id
        $date_condition
        GROUP BY d.driver_id, d.first_name, d.last_name, d.license_number
        HAVING COUNT(c.citation_id) > 1
        ORDER BY citation_count DESC
        LIMIT 10
    ";
    $stmt = $conn->prepare($repeat_offenders_query);
    $stmt->execute($params);
    $repeat_offenders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'violations' => $violations,
        'barangays' => $barangays,
        'payment_status' => $payment_status,
        'trends' => $trends,
        'vehicle_types' => $vehicle_types,
        'repeat_offenders' => $repeat_offenders
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("PDOException in fetch_reports.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: Unable to fetch reports']);
} catch (Exception $e) {
    error_log("Exception in fetch_reports.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>