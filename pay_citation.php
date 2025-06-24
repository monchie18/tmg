<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require_once 'config.php';

try {
    // Validate CSRF token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING) ?? '';
    if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        throw new Exception("Invalid CSRF token.");
    }

    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sanitize inputs
    $citation_id = filter_input(INPUT_POST, 'citation_id', FILTER_VALIDATE_INT) ?: null;
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT) ?: 0;

    error_log("Payment request: citation_id=$citation_id, amount=$amount", 3, 'payment.log');

    if (!$citation_id) {
        throw new Exception("Missing required parameter: citation_id.");
    }

    if ($amount <= 0 || $amount > 100000) {
        throw new Exception("Invalid payment amount.");
    }

    $conn->beginTransaction();

    // Calculate total fine from violations
    $stmt = $conn->prepare("
        SELECT vl.violation_type, vl.offense_count,
               COALESCE(
                   CASE vl.offense_count
                       WHEN 1 THEN vt.fine_amount_1
                       WHEN 2 THEN vt.fine_amount_2
                       WHEN 3 THEN vt.fine_amount_3
                   END, 200
               ) AS fine
        FROM violations vl
        LEFT JOIN violation_types vt ON vl.violation_type = vt.violation_type
        WHERE vl.citation_id = :id
    ");
    $stmt->execute(['id' => $citation_id]);
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($violations)) {
        throw new Exception("No violations found for this citation.");
    }

    $total_fine = 0;
    foreach ($violations as $violation) {
        $total_fine += (float)$violation['fine'];
    }

    if ($amount < $total_fine) {
        throw new Exception("Payment amount ₱" . number_format($amount, 2) . " is less than the total fine of ₱" . number_format($total_fine, 2) . ".");
    }

    // Update citation
    $stmt = $conn->prepare("
        UPDATE citations 
        SET payment_status = 'Paid', payment_amount = :amount, payment_date = NOW() 
        WHERE citation_id = :id AND payment_status = 'Unpaid'
    ");
    $stmt->execute(['id' => $citation_id, 'amount' => $amount]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("No unpaid citations found or payment already processed.");
    }

    // Fetch updated payment date
    $stmt = $conn->prepare("SELECT payment_date FROM citations WHERE citation_id = :id");
    $stmt->execute(['id' => $citation_id]);
    $payment_date = $stmt->fetchColumn();

    $conn->commit();

    echo json_encode(
        [
            'status' => 'success',
            'message' => 'Payment processed successfully.',
            'payment_date' => $payment_date ? date('Y-m-d H:i:s', strtotime($payment_date)) : date('Y-m-d H:i:s')
        ],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {
    if ($conn) $conn->rollBack();
    error_log("PDOException in pay_citation.php: " . $e->getMessage(), 3, 'payment.log');
    http_response_code(500);
    echo json_encode(
        ['status' => 'error', 'message' => 'Database error: Unable to process payment'],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (Exception $e) {
    if ($conn) $conn->rollBack();
    error_log("Exception in pay_citation.php: " . $e->getMessage(), 3, 'payment.log');
    http_response_code(400);
    echo json_encode(
        ['status' => 'error', 'message' => $e->getMessage()],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} finally {
    $conn = null;
}
?>