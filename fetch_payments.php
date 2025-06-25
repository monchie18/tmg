<?php
session_start();
require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

try {
    // Validate CSRF token
    $receivedToken = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_STRING) ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    error_log("Received CSRF Token: $receivedToken, Session CSRF Token: $sessionToken");
    if (empty($receivedToken) || $receivedToken !== $sessionToken) {
        throw new Exception('Invalid CSRF token');
    }

    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sanitize parameters
    $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $recordsPerPage = filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?: 20;
    $offset = ($page - 1) * $recordsPerPage;
    $search = htmlspecialchars(trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? ''), ENT_QUOTES, 'UTF-8');
    $payment_status = filter_input(INPUT_GET, 'payment_status', FILTER_SANITIZE_STRING) ?? 'Unpaid';
    $payment_status = in_array($payment_status, ['Unpaid', 'Paid', 'All']) ? $payment_status : 'Unpaid';
    $date_from = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_STRING) ?: '';
    $date_to = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_STRING) ?: '';

    // Fetch violation types
    $stmt = $conn->query("SELECT violation_type, fine_amount_1, fine_amount_2, fine_amount_3 FROM violation_types");
    $violation_fines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $fine_map = [];
    foreach ($violation_fines as $vf) {
        $fine_map[$vf['violation_type']] = [
            1 => (float)$vf['fine_amount_1'],
            2 => (float)$vf['fine_amount_2'],
            3 => (float)$vf['fine_amount_3']
        ];
    }

    // Main query
    $query = "
        SELECT c.citation_id, c.ticket_number, 
               CONCAT(d.last_name, ', ', d.first_name, 
                      IF(d.middle_initial != '', CONCAT(' ', d.middle_initial), ''), 
                      IF(d.suffix != '', CONCAT(' ', d.suffix), '')) AS driver_name,
               d.driver_id, d.license_number, d.zone, d.barangay, d.municipality, d.province, 
               v.plate_mv_engine_chassis_no, v.vehicle_type, 
               c.apprehension_datetime, c.payment_status, c.payment_amount, c.payment_date,
               GROUP_CONCAT(
                   CONCAT(vl.violation_type, ' (Offense ', vl.offense_count, ' - ₱', 
                          COALESCE(
                              CASE vl.offense_count
                                  WHEN 1 THEN vt.fine_amount_1
                                  WHEN 2 THEN vt.fine_amount_2
                                  WHEN 3 THEN vt.fine_amount_3
                              END, 200
                          ), ')'
                   ) SEPARATOR ', '
               ) AS violations,
               COALESCE(SUM(
                   COALESCE(
                       CASE vl.offense_count
                           WHEN 1 THEN vt.fine_amount_1
                           WHEN 2 THEN vt.fine_amount_2
                           WHEN 3 THEN vt.fine_amount_3
                       END, 200
                   )
               ), 0) AS total_fine
        FROM citations c
        JOIN drivers d ON c.driver_id = d.driver_id
        JOIN vehicles v ON c.vehicle_id = v.vehicle_id
        LEFT JOIN violations vl ON c.citation_id = vl.citation_id
        LEFT JOIN violation_types vt ON vl.violation_type = vt.violation_type
        WHERE c.is_archived = 0
    ";

    $params = [];
    if ($payment_status !== 'All') {
        $query .= " AND c.payment_status = :payment_status";
        $params[':payment_status'] = $payment_status;
    }

    if ($search) {
        $query .= " AND (c.ticket_number LIKE :search OR CONCAT(d.last_name, ' ', d.first_name) LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if ($date_from) {
        $query .= " AND c.apprehension_datetime >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if ($date_to) {
        $query .= " AND c.apprehension_datetime <= :date_to";
        $params[':date_to'] = $date_to . ' 23:59:59';
    }

    // Sort validation
    $allowedSorts = ['apprehension_desc', 'apprehension_asc', 'ticket_asc', 'driver_asc', 'payment_asc', 'payment_desc'];
    $sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc';
    $sort = in_array($sort, $allowedSorts) ? $sort : 'apprehension_desc';
    $query .= " GROUP BY c.citation_id";
    switch ($sort) {
        case 'apprehension_asc':
            $query .= " ORDER BY c.apprehension_datetime ASC";
            break;
        case 'ticket_asc':
            $query .= " ORDER BY c.ticket_number ASC";
            break;
        case 'driver_asc':
            $query .= " ORDER BY d.last_name, d.first_name ASC";
            break;
        case 'payment_asc':
            $query .= " ORDER BY c.payment_status ASC";
            break;
        case 'payment_desc':
            $query .= " ORDER BY c.payment_status DESC";
            break;
        case 'apprehension_desc':
        default:
            $query .= " ORDER BY c.apprehension_datetime DESC";
            break;
    }

    $query .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $recordsPerPage;
    $params[':offset'] = $offset;

    // Prepare and bind
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $type);
    }

    error_log("Query: $query");
    error_log("Params: " . print_r($params, true));

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Fetched Rows: " . print_r(array_map(function($row) {
        return [
            'citation_id' => $row['citation_id'],
            'ticket_number' => $row['ticket_number'],
            'payment_status' => $row['payment_status'],
            'payment_amount' => $row['payment_amount'],
            'payment_date' => $row['payment_date'],
            'total_fine' => $row['total_fine']
        ];
    }, $rows), true));

    // Inline CSS (aligned with original treasury_payments.php but enhanced for consistency)
    ?>
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --secondary: #4b5563;
            --accent: #10b981;
            --danger: #dc2626;
            --warning: #f59e0b;
            --background: #f9fafb;
            --card-bg: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
        }

        .table-container {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border);
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge.bg-success {
            background-color: var(--accent);
            color: white;
        }

        .badge.bg-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-custom {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-success {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
        }

        .text-primary {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .text-primary:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        .empty-state, .debug {
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-primary);
        }

        .empty-state {
            background-color: #e0f2fe;
            color: #1e40af;
        }

        .debug {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 768px) {
            .table { font-size: 0.85rem; }
            .table th, .table td { padding: 0.75rem; }
            .btn-custom { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        }

        @media (max-width: 576px) {
            .table th, .table td { padding: 0.5rem; }
            .badge { padding: 0.4rem 0.8rem; font-size: 0.75rem; }
            .btn-custom { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        }
    </style>

    <?php
    if (empty($rows)) {
        echo "<div class='empty-state'><i class='fas fa-info-circle'></i> No citations found for the selected filters.</div>";
    } else {
        echo "<div class='table-container'>";
        echo "<div class='table-responsive'>";
        echo "<table class='table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th><i class='fas fa-ticket-alt me-2'></i>Ticket Number</th>";
        echo "<th><i class='fas fa-user me-2'></i>Driver Name</th>";
        echo "<th><i class='fas fa-id-card me-2'></i>License Number</th>";
        echo "<th><i class='fas fa-car me-2'></i>Vehicle Plate</th>";
        echo "<th><i class='fas fa-car-side me-2'></i>Vehicle Type</th>";
        echo "<th><i class='fas fa-clock me-2'></i>Apprehension Date</th>";
        echo "<th><i class='fas fa-exclamation-triangle me-2'></i>Violations</th>";
        echo "<th><i class='fas fa-money-bill-wave me-2'></i>Total Fine</th>";
        echo "<th><i class='fas fa-money-bill-wave me-2'></i>Payment Status</th>";
        echo "<th><i class='fas fa-money-bill-wave me-2'></i>Payment Amount</th>";
        echo "<th><i class='fas fa-calendar-alt me-2'></i>Payment Date</th>";
        echo "<th><i class='fas fa-cog me-2'></i>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ticket_number'] ?? '') . "</td>";
            echo "<td><a href='#' class='driver-link text-primary' data-driver-id='" . htmlspecialchars($row['driver_id'] ?? '') . "' data-zone='" . htmlspecialchars($row['zone'] ?? '') . "' data-barangay='" . htmlspecialchars($row['barangay'] ?? '') . "' data-municipality='" . htmlspecialchars($row['municipality'] ?? '') . "' data-province='" . htmlspecialchars($row['province'] ?? '') . "' data-license-number='" . htmlspecialchars($row['license_number'] ?? '') . "' title='View Driver Details' aria-label='View Driver Details for " . htmlspecialchars($row['driver_name'] ?? '') . "'>" . htmlspecialchars($row['driver_name'] ?? '') . "</a></td>";
            echo "<td>" . htmlspecialchars($row['license_number'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['plate_mv_engine_chassis_no'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['vehicle_type'] ?? '') . "</td>";
            echo "<td>" . ($row['apprehension_datetime'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($row['apprehension_datetime']))) : 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['violations'] ?? 'None') . "</td>";
            echo "<td>₱" . number_format($row['total_fine'] ?? 0, 2) . "</td>";
            echo "<td>" . ($row['payment_status'] == 'Paid' 
                ? '<span class="badge bg-success">Paid</span>' 
                : '<span class="badge bg-danger">Unpaid</span>') . "</td>";
            echo "<td>" . ($row['payment_amount'] ? '₱' . number_format($row['payment_amount'], 2) : 'N/A') . "</td>";
            echo "<td>" . ($row['payment_date'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($row['payment_date']))) : 'N/A') . "</td>";
            echo "<td>";
            if ($row['payment_status'] == 'Unpaid') {
                echo "<a href='#' class='btn-custom btn-success pay-now' data-citation-id='" . htmlspecialchars($row['citation_id'] ?? '') . "' data-driver-id='" . htmlspecialchars($row['driver_id'] ?? '') . "' data-zone='" . htmlspecialchars($row['zone'] ?? '') . "' data-barangay='" . htmlspecialchars($row['barangay'] ?? '') . "' data-municipality='" . htmlspecialchars($row['municipality'] ?? '') . "' data-province='" . htmlspecialchars($row['province'] ?? '') . "' data-license-number='" . htmlspecialchars($row['license_number'] ?? '') . "' title='Pay Citation' aria-label='Pay Citation for Ticket " . htmlspecialchars($row['ticket_number'] ?? '') . "'><i class='fas fa-credit-card me-2'></i>Pay Now</a>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
} catch (PDOException $e) {
    error_log("PDOException in fetch_payments.php: " . $e->getMessage());
    echo "<div class='debug'><i class='fas fa-exclamation-circle'></i> Database Error: Unable to fetch citations. Please try again later.</div>";
} catch (Exception $e) {
    error_log("Exception in fetch_payments.php: " . $e->getMessage());
    echo "<div class='debug'><i class='fas fa-exclamation-circle'></i> Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn = null;
}
?>