<?php
session_start();
require_once 'config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $citation_id = $_GET['citation_id'] ?? null;
    $amount_paid = isset($_GET['amount_paid']) ? floatval($_GET['amount_paid']) : 0;
    $change = isset($_GET['change']) ? floatval($_GET['change']) : 0;
    $payment_date = $_GET['payment_date'] ?? date('Y-m-d H:i:s');

    if (!$citation_id) {
        throw new Exception("Citation ID is required.");
    }

    // Fetch citation details
    $query = "
        SELECT c.citation_id, c.ticket_number, c.payment_amount, c.payment_date,
               CONCAT(d.last_name, ', ', d.first_name,
                      IF(d.middle_initial != '', CONCAT(' ', d.middle_initial), ''),
                      IF(d.suffix != '', CONCAT(' ', d.suffix), '')) AS driver_name,
               d.license_number,
               GROUP_CONCAT(CONCAT(vl.violation_type, ' (Offense ', vl.offense_count, ')') SEPARATOR ': ') AS violations
        FROM citations c
        JOIN drivers d ON c.driver_id = d.driver_id
        LEFT JOIN violations vl ON c.citation_id = vl.citation_id
        WHERE c.citation_id = :citation_id
        GROUP BY c.citation_id
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute(['citation_id' => $citation_id]);
    $citation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$citation) {
        throw new Exception("Citation not found.");
    }

    // Fetch violations for fine calculation
    $stmt = $conn->prepare("SELECT violation_type, offense_count FROM violations WHERE citation_id = :cid");
    $stmt->execute(['cid' => $citation_id]);
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_fine = count($violations) * 500; // ₱500 per violation

} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo "<html><body><h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p></body></html>";
    exit;
} finally {
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt-container {
            width: 350px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid #e0e0e0;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            background-color: #e6e6e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }

        .header {
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            color: #ffffff;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 20px;
        }

        .dashed-line {
            border-top: 2px dashed #d1d5db;
            margin: 20px 0;
        }

        .receipt-details, .violations-list, .payment-summary {
            margin: 15px 0;
            text-align: left;
        }

        .receipt-details p, .violations-list p, .payment-summary p {
            margin: 8px 0;
            color: #1f2937;
            font-size: 13px;
        }

        .receipt-details p strong, .violations-list p strong, .payment-summary p strong {
            color: #111827;
            font-weight: 600;
        }

        .violations-list {
            background-color: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .payment-summary {
            padding: 15px;
            background-color: #f1f5f9;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .payment-summary p.total {
            font-size: 16px;
            font-weight: 700;
            color: #b91c1c;
            margin-top: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
            text-align: center;
        }

        .barcode {
            font-family: 'Libre Barcode 39', cursive;
            font-size: 36px;
            margin-top: 20px;
            background-color: #f3f4f6;
            padding: 8px;
            border-radius: 6px;
            color: #111827;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                border: none;
                width: 100%;
                max-width: 350px;
            }

            .header {
                background: linear-gradient(90deg, #1e3a8a, #3b82f6);
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .payment-summary p.total {
                color: #b91c1c;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="logo">Agency Logo</div>
        <div class="header">
            Official Payment Receipt
        </div>

        <div class="receipt-details">
            <p><strong>Receipt #:</strong> <?php echo htmlspecialchars($citation['ticket_number']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($payment_date); ?></p>
            <p><strong>Driver:</strong> <?php echo htmlspecialchars($citation['driver_name']); ?></p>
            <p><strong>License:</strong> <?php echo htmlspecialchars($citation['license_number']); ?></p>
        </div>

        <div class="dashed-line"></div>

        <div class="violations-list">
            <?php
            foreach ($violations as $index => $violation) {
                echo "<p>" . ($index + 1) . ". " . htmlspecialchars($violation['violation_type']) . " (Offense " . $violation['offense_count'] . "): ₱" . number_format(500, 2) . "</p>";
            }
            ?>
        </div>

        <div class="dashed-line"></div>

        <div class="payment-summary">
            <p><strong>TOTAL:</strong> <span class="total">₱<?php echo number_format($total_fine, 2); ?></span></p>
            <p><strong>CASH:</strong> ₱<?php echo number_format($amount_paid, 2); ?></p>
            <p><strong>CHANGE:</strong> ₱<?php echo number_format($change, 2); ?></p>
            <p><strong>Bank Card:</strong> **** **** **** ****</p>
            <p><strong>Approval #:</strong> 123456</p>
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>Issued by: Traffic Management Authority</p>
        </div>

        <div class="barcode">
            *<?php echo htmlspecialchars($citation['ticket_number']); ?>*
        </div>
    </div>

    <script>
        window.onload = () => {
            window.print();
        };
    </script>
</body>
</html>