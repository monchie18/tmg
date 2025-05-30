<?php
header('Content-Type: application/json');
session_start();
require_once 'config.php';

try {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception("Invalid CSRF token.");
    }

    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $citation_id = $_POST['citation_id'] ?? null;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    error_log("Received request: citation_id=$citation_id, amount=$amount", 3, 'payment.log');

    if (!$citation_id) {
        throw new Exception("Missing required parameter: citation_id.");
    }

    if ($amount <= 0 || $amount > 100000) {
        throw new Exception("Invalid payment amount.");
    }

    $conn->beginTransaction();

    // Calculate total fine (₱500 per violation)
    $stmt = $conn->prepare("SELECT COUNT(*) AS violation_count FROM violations WHERE citation_id = :id");
    $stmt->execute(['id' => $citation_id]);
    $violation_count = $stmt->fetchColumn();
    if ($violation_count === false || $violation_count == 0) {
        throw new Exception("No violations found for this citation.");
    }
    $total_fine = $violation_count * 500; // Adjust if you have a fine structure
    if ($amount < $total_fine) {
        throw new Exception("Payment amount is less than the total fine of ₱$total_fine.");
    }

    // Update citation
    $stmt = $conn->prepare("UPDATE citations SET payment_status = 'Paid', payment_amount = :amount, payment_date = NOW() WHERE citation_id = :id AND payment_status = 'Unpaid'");
    $stmt->execute(['id' => $citation_id, 'amount' => $amount]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("No unpaid citations found or payment already processed.");
    }

    $conn->commit();

    echo json_encode(
        ['status' => 'success', 'message' => 'Payment processed successfully.', 'payment_date' => date('Y-m-d H:i:s')],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Payment processing error: " . $e->getMessage(), 3, 'payment.log');
    echo json_encode(
        ['status' => 'error', 'message' => 'An error occurred while processing the payment.'],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(
        ['status' => 'error', 'message' => $e->getMessage()],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} finally {
    $conn = null;
}
?>