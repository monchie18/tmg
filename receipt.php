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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border: 2px solid #000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 16px;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 18px;
            margin: 5px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-details {
            margin-bottom: 20px;
        }

        .receipt-details p {
            margin: 5px 0;
            font-size: 14px;
        }

        .violations-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .violations-table th, .violations-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }

        .violations-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .payment-summary {
            margin-bottom: 20px;
        }

        .payment-summary p {
            margin: 5px 0;
            font-size: 14px;
        }

        .signature {
            margin-top: 30px;
            text-align: right;
        }

        .signature p {
            margin: 5px 0;
            font-size: 14px;
        }

        .signature .line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 40px;
        }

        @media print {
            body {
                background-color: white;
            }

            .receipt-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Republic of the Philippines</h1>
            <h1>Province of Cagayan</h1>
            <h1>Municipality of Baggao</h1>
            <h2>Traffic Citation Payment Receipt</h2>
        </div>

        <div class="receipt-details">
            <p><strong>Receipt Number:</strong> <?php echo htmlspecialchars($citation['ticket_number']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($payment_date); ?></p>
            <p><strong>Driver Name:</strong> <?php echo htmlspecialchars($citation['driver_name']); ?></p>
            <p><strong>License Number:</strong> <?php echo htmlspecialchars($citation['license_number']); ?></p>
        </div>

        <table class="violations-table">
            <thead>
                <tr>
                    <th>Violation</th>
                    <th>Fine Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($violations as $violation) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($violation['violation_type'] . ' (Offense ' . $violation['offense_count'] . ')') . "</td>";
                    echo "<td>₱" . number_format(500, 2) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="payment-summary">
            <p><strong>Total Fine:</strong> ₱<?php echo number_format($total_fine, 2); ?></p>
            <p><strong>Amount Paid:</strong> ₱<?php echo number_format($amount_paid, 2); ?></p>
            <p><strong>Change:</strong> ₱<?php echo number_format($change, 2); ?></p>
        </div>

        <div class="signature">
            <p>Received by:</p>
            <div class="line"></div>
            <p>Cashier/Authorized Representative</p>
        </div>
    </div>

    <script>
        window.onload = () => {
            window.print();
        };
    </script>
</body>
</html>