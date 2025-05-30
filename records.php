<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Traffic Citation Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      background-color: #f3f4f6;
      font-family: 'Inter', sans-serif;
      color: #1f2937;
      line-height: 1.6;
    }

    .container {
      max-width: 1280px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: white;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .header {
      background: linear-gradient(90deg, #1e3a8a, #3b82f6);
      color: white;
      padding: 2rem;
      border-radius: 12px;
      text-align: center;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
    }

    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #f97316, #facc15);
    }

    .header h4 {
      font-size: 1.125rem;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      opacity: 0.9;
    }

    .header h1 {
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      margin-top: 0.5rem;
    }

    .sort-filter {
      margin-bottom: 1.5rem;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: center;
    }

    .sort-select, .search-input {
      border-radius: 8px;
      border: 1px solid #d1d5db;
      padding: 0.5rem 2rem 0.5rem 1rem;
      font-size: 0.9rem;
      background-color: #f9fafb;
      transition: all 0.3s ease;
    }

    .sort-select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.75rem center;
      background-size: 1rem;
    }

    .search-input {
      padding: 0.5rem 1rem;
      width: 100%;
      max-width: 300px;
    }

    .sort-select:hover, .search-input:hover {
      border-color: #2563eb;
    }

    .sort-select:focus, .search-input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .table th {
      background-color: #f9fafb;
      color: #1e3a8a;
      font-weight: 600;
      padding: 1rem;
      text-align: left;
      border-bottom: 2px solid #e5e7eb;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .table td {
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #e5e7eb;
      font-size: 0.95rem;
    }

    .table tr {
      transition: background-color 0.2s ease;
    }

    .table tr:hover {
      background-color: #f1f5f9;
    }

    .btn-custom {
      padding: 0.5rem 1.25rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }

    .btn-primary {
      background-color: #2563eb;
      color: white;
      border: none;
    }

    .btn-primary:hover {
      background-color: #1e40af;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background-color: #6b7280;
      color: white;
      border: none;
    }

    .btn-secondary:hover {
      background-color: #4b5563;
      transform: translateY(-2px);
    }

    .btn-archive {
      background-color: #9ca3af;
      color: white;
      border: none;
    }

    .btn-archive:hover {
      background-color: #6b7280;
      transform: translateY(-2px);
    }

    .btn-danger {
      background-color: #ef4444;
      color: white;
      border: none;
    }

    .btn-danger:hover {
      background-color: #dc2626;
      transform: translateY(-2px);
    }

    .btn-success {
      background-color: #22c55e;
      color: white;
      border: none;
    }

    .btn-success:hover {
      background-color: #16a34a;
      transform: translateY(-2px);
    }

    .debug, .empty-state {
      color: #b91c1c;
      background-color: #fef2f2;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .loading {
      text-align: center;
      padding: 2rem;
      color: #6b7280;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .loading i {
      animation: spin 1s linear infinite;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
      z-index: 1000;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    .modal.show {
      opacity: 1;
    }

    .modal-content {
      background-color: white;
      padding: 2rem;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      position: relative;
      transform: scale(0.95);
      transition: transform 0.3s ease-in-out;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .modal.show .modal-content {
      transform: scale(1);
    }

    .close {
      position: absolute;
      top: 1rem;
      right: 1rem;
      font-size: 1.5rem;
      cursor: pointer;
      color: #6b7280;
      transition: color 0.2s ease;
    }

    .close:hover {
      color: #1e40af;
    }

    .modal-content h2 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1e3a8a;
      margin-bottom: 1.5rem;
    }

    .modal-content .driver-info, .modal-content .offense-table, .modal-content .payment-form {
      border: 1px solid #e5e7eb;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-radius: 8px;
    }

    .modal-content .driver-info {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .modal-content .driver-info .photo-placeholder {
      width: 100px;
      height: 100px;
      background-color: #e5e7eb;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .modal-content .driver-info .details {
      flex-grow: 1;
    }

    .modal-content .driver-info p {
      margin: 0.25rem 0;
    }

    .modal-content .offense-table table {
      width: 100%;
      border-collapse: collapse;
    }

    .modal-content .offense-table th, .modal-content .offense-table td {
      padding: 0.5rem;
      border: 1px solid #e5e7eb;
      text-align: left;
    }

    .modal-content .offense-table th {
      background-color: #f9fafb;
      color: #1e3a8a;
    }

    .modal-content .offense-table .total-row {
      font-weight: bold;
      background-color: #f1f5f9;
    }

    .modal-content .payment-form input {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .modal-content .payment-form p {
      margin: 0.5rem 0;
    }

    .modal-content .btn-custom {
      margin-top: 1rem;
    }

    .timeline-container {
      position: relative;
      padding: 2rem 0;
    }

    .timeline-item {
      position: relative;
      margin-bottom: 2rem;
      padding-left: 2rem;
      border-left: 2px solid #2563eb;
    }

    .timeline-item::before {
      content: '';
      position: absolute;
      left: -6px;
      top: 0;
      width: 12px;
      height: 12px;
      background-color: #2563eb;
      border-radius: 50%;
    }

    .timeline-item h5 {
      font-weight: 600;
      color: #1e3a8a;
    }

    .timeline-item p {
      margin: 0.25rem 0;
    }

    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }

    @media (max-width: 768px) {
      .container {
        margin: 1rem;
        padding: 1.5rem;
      }

      .header h1 {
        font-size: 1.5rem;
      }

      .table th, .table td {
        font-size: 0.85rem;
        padding: 0.75rem;
      }

      .btn-custom {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
      }

      .sort-filter {
        flex-direction: column;
      }

      .modal-content {
        width: 95%;
      }

      .modal-content .driver-info {
        flex-direction: column;
        text-align: center;
      }

      .modal-content .driver-info .photo-placeholder {
        margin: 0 auto 1rem;
      }
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="container">
    <div class="header">
      <h4>Republic of the Philippines</h4>
      <h4>Province of Cagayan • Municipality of Baggao</h4>
      <h1>Traffic Citation Records</h1>
    </div>

    <div class="sort-filter">
      <a href="index.php" class="btn btn-primary btn-custom"><i class="fas fa-plus"></i> Add New Citation</a>
      <a href="driver_records.php" class="btn btn-primary btn-custom"><i class="fas fa-users"></i> View Driver Records</a>
      <a href="?show_archived=1" class="btn btn-secondary btn-custom"><i class="fas fa-archive"></i> View Archived Citations</a>
      <select id="sortSelect" class="sort-select">
        <option value="apprehension_desc">Sort by Date (Newest)</option>
        <option value="apprehension_asc">Sort by Date (Oldest)</option>
        <option value="ticket_asc">Sort by Ticket Number (Asc)</option>
        <option value="driver_asc">Sort by Driver Name (A-Z)</option>
      </select>
      <input type="text" id="searchInput" class="search-input" placeholder="Search by Driver Name or Ticket Number">
      <select id="bulkActions" class="sort-select" style="max-width: 200px;">
        <option value="">Bulk Actions</option>
        <option value="archive">Archive Selected</option>
        <option value="unarchive">Unarchive Selected</option>
        <option value="delete">Delete Selected</option>
      </select>
      <button id="applyBulk" class="btn btn-primary btn-custom">Apply</button>
      <button id="exportCSV" class="btn btn-secondary btn-custom"><i class="fas fa-file-csv"></i> Export to CSV</button>
      <button id="toggleView" class="btn btn-secondary btn-custom"><i class="fas fa-stream"></i> Timeline View</button>
      <div class="dropdown">
        <button class="btn btn-secondary btn-custom dropdown-toggle" type="button" id="columnDropdown" data-bs-toggle="dropdown">
          <i class="fas fa-columns"></i> Columns
        </button>
        <ul class="dropdown-menu" aria-labelledby="columnDropdown">
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="0" checked> Ticket Number</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="1" checked> Driver Name</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="2" checked> License Number</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="3" checked> Vehicle Plate</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="4" checked> Vehicle Type</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="5" checked> Apprehension Date</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="6" checked> Violations</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="7" checked> Payment Status</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="8" checked> Archiving Reason</label></li>
          <li><label class="dropdown-item"><input type="checkbox" class="column-toggle" data-column="9" checked> Actions</label></li>
        </ul>
      </div>
    </div>

    <div id="loading" class="loading" style="display: none;">
      <i class="fas fa-spinner fa-2x"></i> Loading citations...
    </div>

    <div id="citationTable">
      <?php
      try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $show_archived = isset($_GET['show_archived']) && $_GET['show_archived'] == 1;

        $query = "
          SELECT c.citation_id, c.ticket_number, 
                 CONCAT(d.last_name, ', ', d.first_name, 
                        IF(d.middle_initial != '', CONCAT(' ', d.middle_initial), ''), 
                        IF(d.suffix != '', CONCAT(' ', d.suffix), '')) AS driver_name,
                 d.driver_id, d.license_number, d.zone, d.barangay, d.municipality, d.province, v.plate_mv_engine_chassis_no, v.vehicle_type, 
                 c.apprehension_datetime, c.payment_status,
                 GROUP_CONCAT(CONCAT(vl.violation_type, ' (Offense ', vl.offense_count, ')') SEPARATOR ', ') AS violations,
                 (SELECT COUNT(*) FROM violations vl2 WHERE vl2.citation_id = c.citation_id AND vl2.violation_type = 'Traffic Restriction Order Violation') > 0 AS is_tro,
                 r.remark_text AS archiving_reason
          FROM citations c
          JOIN drivers d ON c.driver_id = d.driver_id
          JOIN vehicles v ON c.vehicle_id = v.vehicle_id
          LEFT JOIN violations vl ON c.citation_id = vl.citation_id
          LEFT JOIN remarks r ON c.citation_id = r.citation_id
          WHERE c.is_archived = :is_archived
        ";

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $params = ['is_archived' => $show_archived ? 1 : 0];
        if ($search) {
          $query .= " AND (c.ticket_number LIKE :search OR CONCAT(d.last_name, ' ', d.first_name) LIKE :search)";
          $params['search'] = "%$search%";
        }

        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'apprehension_desc';
        switch ($sort) {
          case 'apprehension_asc':
            $query .= " GROUP BY c.citation_id ORDER BY c.apprehension_datetime ASC";
            break;
          case 'ticket_asc':
            $query .= " GROUP BY c.citation_id ORDER BY c.ticket_number ASC";
            break;
          case 'driver_asc':
            $query .= " GROUP BY c.citation_id ORDER BY d.last_name, d.first_name ASC";
            break;
          case 'apprehension_desc':
          default:
            $query .= " GROUP BY c.citation_id ORDER BY c.apprehension_datetime DESC";
            break;
        }

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
          echo "<p class='empty-state'><i class='fas fa-info-circle'></i> No " . ($show_archived ? "archived" : "active") . " citations found.</p>";
        } else {
          echo "<div class='table-responsive'>";
          echo "<table class='table table-bordered table-striped'>";
          echo "<thead>";
          echo "<tr>";
          echo "<th><input type='checkbox' id='selectAll'></th>";
          echo "<th><i class='fas fa-ticket-alt me-2'></i>Ticket Number</th>";
          echo "<th><i class='fas fa-user me-2'></i>Driver Name</th>";
          echo "<th><i class='fas fa-id-card me-2'></i>License Number</th>";
          echo "<th><i class='fas fa-car me-2'></i>Vehicle Plate</th>";
          echo "<th><i class='fas fa-car-side me-2'></i>Vehicle Type</th>";
          echo "<th><i class='fas fa-clock me-2'></i>Apprehension Date</th>";
          echo "<th><i class='fas fa-exclamation-triangle me-2'></i>Violations</th>";
          echo "<th><i class='fas fa-money-bill-wave me-2'></i>Payment Status</th>";
          echo "<th><i class='fas fa-info-circle me-2'></i>Archiving Reason</th>";
          echo "<th><i class='fas fa-cog me-2'></i>Actions</th>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";
          foreach ($rows as $row) {
            echo "<tr>";
            echo "<td><input type='checkbox' class='select-citation' value='" . $row['citation_id'] . "'></td>";
            echo "<td>" . htmlspecialchars($row['ticket_number']) . "</td>";
            echo "<td><a href='#' class='driver-link text-primary' data-driver-id='" . $row['driver_id'] . "' data-zone='" . htmlspecialchars($row['zone']) . "' data-barangay='" . htmlspecialchars($row['barangay']) . "' data-municipality='" . htmlspecialchars($row['municipality']) . "' data-province='" . htmlspecialchars($row['province']) . "'>" . htmlspecialchars($row['driver_name']) . "</a></td>";
            echo "<td>" . htmlspecialchars($row['license_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['plate_mv_engine_chassis_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['vehicle_type']) . "</td>";
            echo "<td>" . ($row['apprehension_datetime'] ? htmlspecialchars($row['apprehension_datetime']) : 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['violations'] ?? 'None') . "</td>";
            echo "<td>" . ($row['payment_status'] == 'Paid' ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-danger">Unpaid</span>') . "</td>";
            echo "<td>" . htmlspecialchars($row['archiving_reason'] ?? 'N/A') . "</td>";
            echo "<td class='d-flex gap-2'>";
            if (!$show_archived) {
              echo "<a href='edit_citation.php?id=" . $row['citation_id'] . "' class='btn btn-sm btn-primary btn-custom'><i class='fas fa-edit'></i> Edit</a>";
              echo "<a href='delete_citation.php?id=" . $row['citation_id'] . "' class='btn btn-sm btn-danger btn-custom' onclick='return confirm(\"Are you sure you want to delete this citation?\")'><i class='fas fa-trash'></i> Delete</a>";
            }
            $actionText = $show_archived ? "Unarchive" : "Archive";
            $iconClass = $show_archived ? "fa-box-open" : "fa-archive";
            echo "<button class='btn btn-sm btn-archive archive-btn' data-id='" . $row['citation_id'] . "' data-action='" . ($show_archived ? 0 : 1) . "' data-is-tro='" . ($row['is_tro'] ? '1' : '0') . "'><i class='fas " . $iconClass . "'></i> " . $actionText . "</button>";
            if ($row['payment_status'] == 'Unpaid' && !$show_archived) {
              echo "<a href='#' class='btn btn-sm btn-success btn-custom pay-now' data-citation-id='" . $row['citation_id'] . "' data-driver-id='" . $row['driver_id'] . "' data-zone='" . htmlspecialchars($row['zone']) . "' data-barangay='" . htmlspecialchars($row['barangay']) . "' data-municipality='" . htmlspecialchars($row['municipality']) . "' data-province='" . htmlspecialchars($row['province']) . "'><i class='fas fa-credit-card'></i> Pay Now</a>";
            }
            echo "</td>";
            echo "</tr>";
          }
          echo "</tbody>";
          echo "</table>";
          echo "</div>";
        }
      } catch(PDOException $e) {
        echo "<p class='debug'><i class='fas fa-exclamation-circle'></i> Error: " . htmlspecialchars($e->getMessage()) . "</p>";
      }
      $conn = null;
      ?>
    </div>

    <div id="timelineView" style="display: none;">
      <div class="timeline-container"></div>
    </div>

    <!-- Archive Modal -->
    <div id="archiveModal" class="modal">
      <div class="modal-content">
        <span class="close">×</span>
        <h2>Remarks Note: Reason for Archiving</h2>
        <input type="text" id="remarksReason" class="form-control mb-3" placeholder="Enter reason for archiving/unarchiving (max 255 characters)" maxlength="255" required>
        <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
        <button id="confirmArchive" class="btn btn-primary btn-custom">Confirm</button>
        <button id="cancelArchive" class="btn btn-secondary btn-custom">Cancel</button>
      </div>
    </div>

    <!-- Driver Information Modal -->
    <div id="driverInfoModal" class="modal" role="dialog" aria-labelledby="driverInfoTitle" aria-hidden="true">
      <div class="modal-content">
        <span class="close">×</span>
        <h2 id="driverInfoTitle">Driver Information</h2>
        <div class="driver-info">
          <div class="photo-placeholder"></div>
          <div class="details">
            <p><strong>License Number:</strong> <span id="licenseNumber"></span></p>
            <p><strong>Name:</strong> <span id="driverName"></span></p>
            <p><strong>Address:</strong> <span id="driverAddress"></span></p>
            <p><strong>Total Fines:</strong> <span id="totalFines">₱0.00</span></p>
          </div>
        </div>
        <div class="offense-table">
          <h3>Offense Records</h3>
          <table>
            <thead>
              <tr>
                <th>Date/Time</th>
                <th>Offense</th>
                <th>Fine</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="offenseRecords"></tbody>
            <tfoot>
              <tr class="total-row">
                <td colspan="2"><strong>Total</strong></td>
                <td><strong id="totalFineDisplay">₱0.00</strong></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <button id="printModal" class="btn btn-secondary btn-custom"><i class="fas fa-print"></i> Print</button>
        <button id="closeModal" class="btn btn-primary btn-custom">Close</button>
      </div>
    </div>

    <!-- Payment Processing Modal -->
    <div id="paymentModal" class="modal" role="dialog" aria-labelledby="paymentModalTitle" aria-hidden="true">
      <div class="modal-content">
        <span class="close">×</span>
        <h2 id="paymentModalTitle">Payment Processing</h2>
        <div class="driver-info">
          <div class="photo-placeholder"></div>
          <div class="details">
            <p><strong>License Number:</strong> <span id="paymentLicenseNumber"></span></p>
            <p><strong>Name:</strong> <span id="paymentDriverName"></span></p>
            <p><strong>Address:</strong> <span id="paymentDriverAddress"></span></p>
            <p><strong>Total Fines:</strong> <span id="paymentTotalFines">₱0.00</span></p>
          </div>
        </div>
        <div class="offense-table">
          <h3>Offense Records</h3>
          <table>
            <thead>
              <tr>
                <th>Date/Time</th>
                <th>Offense</th>
                <th>Fine</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="paymentOffenseRecords"></tbody>
            <tfoot>
              <tr class="total-row">
                <td colspan="2"><strong>Total</strong></td>
                <td><strong id="paymentTotalFineDisplay">₱0.00</strong></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="payment-form">
          <h3>Payment Details</h3>
          <p><strong>Amount Due:</strong> <span id="amountDue">₱0.00</span></p>
          <input type="number" id="cashInput" step="0.01" min="0" placeholder="Enter cash amount" required>
          <p><strong>Change:</strong> <span id="changeDisplay">₱0.00</span></p>
          <div id="paymentError" class="alert alert-danger" style="display: none;"></div>
          <button id="confirmPayment" class="btn btn-success btn-custom">Confirm Payment</button>
          <button id="cancelPayment" class="btn btn-secondary btn-custom">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";

    document.addEventListener('DOMContentLoaded', () => {
      const loadingDiv = document.getElementById('loading');
      const citationTable = document.getElementById('citationTable');

      loadingDiv.style.display = 'block';
      citationTable.style.opacity = '0';
      setTimeout(() => {
        loadingDiv.style.display = 'none';
        citationTable.style.opacity = '1';
      }, 500);

      const rows = document.querySelectorAll('.table tr');
      rows.forEach(row => {
        row.addEventListener('mouseenter', () => {
          row.style.cursor = 'pointer';
        });
        row.addEventListener('mouseleave', () => {
          row.style.cursor = 'default';
        });
      });

      const sortSelect = document.getElementById('sortSelect');
      sortSelect.addEventListener('change', () => {
        const url = new URL(window.location);
        url.searchParams.set('sort', sortSelect.value);
        window.location.href = url.toString();
      });

      const urlParams = new URLSearchParams(window.location.search);
      const sortParam = urlParams.get('sort') || 'apprehension_desc';
      sortSelect.value = sortParam;

      const searchInput = document.getElementById('searchInput');
      searchInput.addEventListener('input', debounce(() => {
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value);
        window.location.href = url.toString();
      }, 300));

      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }

      const searchParam = urlParams.get('search') || '';
      searchInput.value = searchParam;

      const archiveModal = document.getElementById('archiveModal');
      const closeModal = document.getElementById('cancelArchive');
      const confirmArchive = document.getElementById('confirmArchive');
      const remarksReason = document.getElementById('remarksReason');
      const errorMessage = document.getElementById('errorMessage');
      let currentCitationId = null;
      let currentAction = null;
      let isTRO = null;

      document.querySelectorAll('.archive-btn').forEach(button => {
        button.addEventListener('click', () => {
          currentCitationId = button.getAttribute('data-id');
          currentAction = button.getAttribute('data-action');
          isTRO = button.getAttribute('data-is-tro') === '1';
          archiveModal.style.display = 'flex';
          archiveModal.classList.add('show');
          remarksReason.value = '';
          errorMessage.style.display = 'none';
          remarksReason.focus();
          if (isTRO) {
            remarksReason.setAttribute('required', 'required');
            document.querySelector('#archiveModal h2').textContent = 'Remarks Note: Reason for TRO Archiving';
          } else {
            remarksReason.removeAttribute('required');
            document.querySelector('#archiveModal h2').textContent = 'Remarks Note: Reason for Archiving';
          }
        });
      });

      closeModal.addEventListener('click', () => {
        archiveModal.classList.remove('show');
        setTimeout(() => {
          archiveModal.style.display = 'none';
        }, 300);
        errorMessage.style.display = 'none';
      });

      confirmArchive.addEventListener('click', () => {
        const reason = remarksReason.value.trim();
        errorMessage.style.display = 'none';

        if (isTRO && !reason) {
          errorMessage.textContent = 'A remarks note is required for archiving/unarchiving a TRO.';
          errorMessage.style.display = 'block';
          return;
        }

        if (reason.length > 255) {
          errorMessage.textContent = 'Remarks note exceeds 255 characters.';
          errorMessage.style.display = 'block';
          return;
        }

        fetch('archive_citation.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `id=${encodeURIComponent(currentCitationId)}&archive=${encodeURIComponent(currentAction)}&remarksReason=${encodeURIComponent(reason)}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
          return response.json();
        })
        .then(data => {
          alert(data.message);
          if (data.status === 'success') {
            window.location.reload();
          }
        })
        .catch(error => {
          alert('Error archiving citation: ' + error.message);
        });

        archiveModal.classList.remove('show');
        setTimeout(() => {
          archiveModal.style.display = 'none';
        }, 300);
      });

      let isOutsideClick = false;
      window.addEventListener('click', (event) => {
        if (event.target === archiveModal && !isOutsideClick) {
          isOutsideClick = true;
          archiveModal.classList.remove('show');
          setTimeout(() => {
            archiveModal.style.display = 'none';
          }, 300);
          errorMessage.style.display = 'none';
        } else {
          isOutsideClick = false;
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && archiveModal.style.display === 'flex') {
          archiveModal.classList.remove('show');
          setTimeout(() => {
            archiveModal.style.display = 'none';
          }, 300);
          errorMessage.style.display = 'none';
        }
      });

      document.getElementById('selectAll').addEventListener('change', (e) => {
        document.querySelectorAll('.select-citation').forEach(checkbox => {
          checkbox.checked = e.target.checked;
        });
      });

      document.getElementById('applyBulk').addEventListener('click', () => {
        const action = document.getElementById('bulkActions').value;
        if (!action) return alert('Please select an action.');

        const selected = Array.from(document.querySelectorAll('.select-citation:checked')).map(checkbox => checkbox.value);
        if (selected.length === 0) return alert('Please select at least one citation.');

        if (action === 'delete' && !confirm('Are you sure you want to delete the selected citations?')) return;

        fetch('bulk_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=${encodeURIComponent(action)}&ids=${encodeURIComponent(JSON.stringify(selected))}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
          return response.json();
        })
        .then(data => {
          alert(data.message);
          if (data.status === 'success') window.location.reload();
        })
        .catch(error => alert('Error: ' + error.message));
      });

      document.getElementById('exportCSV').addEventListener('click', () => {
        const rows = document.querySelectorAll('#citationTable table tr');
        let csv = [];
        const headers = Array.from(rows[0].querySelectorAll('th')).map(th => th.textContent.trim());
        csv.push(headers.join(','));

        for (let i = 1; i < rows.length; i++) {
          const cols = Array.from(rows[i].querySelectorAll('td')).map(td => {
            let text = td.textContent.trim().replace(/"/g, '""');
            if (text.match(/^[+=@-]/)) text = `'${text}`;
            return `"${text}"`;
          });
          csv.push(cols.join(','));
        }

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'Traffic_Citation_Records.csv';
        link.click();
      });

      document.getElementById('toggleView').addEventListener('click', () => {
        const tableView = document.querySelector('#citationTable table');
        const timelineView = document.getElementById('timelineView');
        if (tableView.style.display !== 'none') {
          tableView.style.display = 'none';
          timelineView.style.display = 'block';
          document.getElementById('toggleView').innerHTML = '<i class="fas fa-table"></i> Table View';

          const rows = document.querySelectorAll('#citationTable table tbody tr');
          const timelineContainer = timelineView.querySelector('.timeline-container');
          timelineContainer.innerHTML = '';
          rows.forEach(row => {
            const cols = row.querySelectorAll('td');
            const item = document.createElement('div');
            item.className = 'timeline-item';
            item.innerHTML = `
              <h5>${cols[1].textContent} - ${cols[2].textContent}</h5>
              <p><strong>Date:</strong> ${cols[6].textContent}</p>
              <p><strong>Violations:</strong> ${cols[7].textContent}</p>
              <p><strong>Vehicle:</strong> ${cols[4].textContent} (${cols[5].textContent})</p>
            `;
            timelineContainer.appendChild(item);
          });
        } else {
          tableView.style.display = 'block';
          timelineView.style.display = 'none';
          document.getElementById('toggleView').innerHTML = '<i class="fas fa-stream"></i> Timeline View';
        }
      });

      document.querySelectorAll('.driver-link').forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const driverId = link.getAttribute('data-driver-id');
          const zone = link.getAttribute('data-zone');
          const barangay = link.getAttribute('data-barangay');
          const municipality = link.getAttribute('data-municipality');
          const province = link.getAttribute('data-province');

          loadingDiv.style.display = 'block';
          fetch(`get_driver_info.php?driver_id=${encodeURIComponent(driverId)}`, {
            headers: { 'Accept': 'application/json' }
          })
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
              const contentType = response.headers.get('content-type');
              if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Unexpected response format');
              }
              return response.json();
            })
            .then(data => {
              loadingDiv.style.display = 'none';
              document.getElementById('licenseNumber').textContent = data.license_number || 'N/A';
              document.getElementById('driverName').textContent = data.driver_name || 'N/A';
              document.getElementById('driverAddress').textContent = `${zone ? zone + ', ' : ''}${barangay ? barangay + ', ' : ''}${municipality}, ${province}`;
              const offenseTable = document.getElementById('offenseRecords');
              offenseTable.innerHTML = '';
              let totalFine = 0;
              data.offenses.forEach(offense => {
                const fine = 500; // Fixed fine per violation
                totalFine += fine;
                const row = document.createElement('tr');
                row.innerHTML = `
                  <td>${offense.date_time || 'N/A'}</td>
                  <td>${offense.offense || 'N/A'}</td>
                  <td>₱${fine.toFixed(2)}</td>
                  <td>${offense.status || 'N/A'}</td>
                `;
                offenseTable.appendChild(row);
              });
              document.getElementById('totalFines').textContent = `₱${totalFine.toFixed(2)}`;
              document.getElementById('totalFineDisplay').textContent = `₱${totalFine.toFixed(2)}`;

              const modal = document.getElementById('driverInfoModal');
              modal.style.display = 'flex';
              modal.classList.add('show');
            })
            .catch(error => {
              loadingDiv.style.display = 'none';
              document.getElementById('licenseNumber').textContent = 'Error';
              document.getElementById('offenseRecords').innerHTML = `<tr><td colspan="4">Error loading data: ${error.message}</td></tr>`;
              console.error('Fetch error:', error);
            });
        });
      });

      document.getElementById('closeModal').addEventListener('click', () => {
        const modal = document.getElementById('driverInfoModal');
        modal.classList.remove('show');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
      });

      document.getElementById('printModal').addEventListener('click', () => {
        window.print();
      });

      document.querySelector('#driverInfoModal .close').addEventListener('click', () => {
        const modal = document.getElementById('driverInfoModal');
        modal.classList.remove('show');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
      });

      let isModalClick = false;
      window.addEventListener('click', (event) => {
        const modal = document.getElementById('driverInfoModal');
        if (event.target === modal && !isModalClick) {
          isModalClick = true;
          modal.classList.remove('show');
          setTimeout(() => { modal.style.display = 'none'; }, 300);
        } else {
          isModalClick = false;
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.getElementById('driverInfoModal').style.display === 'flex') {
          const modal = document.getElementById('driverInfoModal');
          modal.classList.remove('show');
          setTimeout(() => { modal.style.display = 'none'; }, 300);
        }
      });

      document.querySelectorAll('.pay-now').forEach(button => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          const citationId = button.getAttribute('data-citation-id');
          const driverId = button.getAttribute('data-driver-id');
          const zone = button.getAttribute('data-zone');
          const barangay = button.getAttribute('data-barangay');
          const municipality = button.getAttribute('data-municipality');
          const province = button.getAttribute('data-province');

          loadingDiv.style.display = 'block';
          fetch(`get_driver_summary.php?driver_id=${encodeURIComponent(driverId)}`, {
            headers: { 'Accept': 'application/json' }
          })
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
              const contentType = response.headers.get('content-type');
              if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Unexpected response format');
              }
              return response.json();
            })
            .then(data => {
              loadingDiv.style.display = 'none';
              document.getElementById('paymentLicenseNumber').textContent = data.license_number || 'N/A';
              document.getElementById('paymentDriverName').textContent = data.driver_name || 'N/A';
              document.getElementById('paymentDriverAddress').textContent = `${zone ? zone + ', ' : ''}${barangay ? barangay + ', ' : ''}${municipality}, ${province}`;
              const offenseTable = document.getElementById('paymentOffenseRecords');
              offenseTable.innerHTML = '';
              let totalFine = 0;
              data.offenses.forEach(offense => {
                const fine = 500; // Fixed fine per violation
                totalFine += fine;
                const row = document.createElement('tr');
                row.innerHTML = `
                  <td>${offense.date_time || 'N/A'}</td>
                  <td>${offense.offense || 'N/A'}</td>
                  <td>₱${fine.toFixed(2)}</td>
                  <td>${offense.status || 'N/A'}</td>
                `;
                offenseTable.appendChild(row);
              });
              const unpaidFines = totalFine - (data.offenses.filter(o => o.status === 'Paid').reduce((sum, o) => sum + 500, 0));
              document.getElementById('paymentTotalFines').textContent = `₱${totalFine.toFixed(2)}`;
              document.getElementById('paymentTotalFineDisplay').textContent = `₱${totalFine.toFixed(2)}`;
              document.getElementById('amountDue').textContent = `₱${unpaidFines.toFixed(2)}`;

              const cashInput = document.getElementById('cashInput');
              const changeDisplay = document.getElementById('changeDisplay');
              const paymentError = document.getElementById('paymentError');

              cashInput.value = '';
              changeDisplay.textContent = '₱0.00';
              paymentError.style.display = 'none';

              cashInput.addEventListener('input', () => {
                const cash = parseFloat(cashInput.value) || 0;
                const change = cash - unpaidFines;
                changeDisplay.textContent = `₱${change >= 0 ? change.toFixed(2) : '0.00'}`;
                if (change < 0) {
                  paymentError.textContent = 'Insufficient cash amount.';
                  paymentError.style.display = 'block';
                } else {
                  paymentError.style.display = 'none';
                }
              });

              const paymentModal = document.getElementById('paymentModal');
              paymentModal.style.display = 'flex';
              paymentModal.classList.add('show');
            })
            .catch(error => {
              loadingDiv.style.display = 'none';
              alert('Error loading driver data: ' + error.message);
            });
        });
      });

      document.getElementById('confirmPayment').addEventListener('click', () => {
        const cashInput = document.getElementById('cashInput');
        const changeDisplay = document.getElementById('changeDisplay');
        const paymentError = document.getElementById('paymentError');
        const paymentModal = document.getElementById('paymentModal');
        const citationId = document.querySelector('.pay-now[data-citation-id]').getAttribute('data-citation-id');
        const cash = parseFloat(cashInput.value) || 0;
        const unpaidFines = parseFloat(document.getElementById('amountDue').textContent.replace('₱', '')) || 0;

        if (cash < unpaidFines) {
          paymentError.textContent = 'Insufficient cash amount.';
          paymentError.style.display = 'block';
          return;
        }

        const change = cash - unpaidFines;

        loadingDiv.style.display = 'block';
        fetch('pay_citation.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `citation_id=${encodeURIComponent(citationId)}&amount=${encodeURIComponent(cash)}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Unexpected response format');
          }
          return response.json();
        })
        .then(data => {
          loadingDiv.style.display = 'none';
          if (data.status === 'success') {
            const receiptUrl = `receipt.php?citation_id=${encodeURIComponent(citationId)}&amount_paid=${encodeURIComponent(cash)}&change=${encodeURIComponent(change)}&payment_date=${encodeURIComponent(data.payment_date)}`;
            window.open(receiptUrl, '_blank');
            window.location.reload();
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          loadingDiv.style.display = 'none';
          alert('Error processing payment: ' + error.message);
        });

        paymentModal.classList.remove('show');
        setTimeout(() => { paymentModal.style.display = 'none'; }, 300);
      });

      document.getElementById('cancelPayment').addEventListener('click', () => {
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.classList.remove('show');
        setTimeout(() => { paymentModal.style.display = 'none'; }, 300);
      });

      document.querySelectorAll('.column-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
          const columnIndex = checkbox.getAttribute('data-column');
          const cells = document.querySelectorAll(`#citationTable table th:nth-child(${parseInt(columnIndex) + 2}), #citationTable table td:nth-child(${parseInt(columnIndex) + 2})`);
          cells.forEach(cell => {
            cell.style.display = checkbox.checked ? '' : 'none';
          });
          localStorage.setItem(`column_${columnIndex}`, checkbox.checked);
        });
      });

      document.querySelectorAll('.column-toggle').forEach(checkbox => {
        const columnIndex = checkbox.getAttribute('data-column');
        const saved = localStorage.getItem(`column_${columnIndex}`);
        if (saved === 'false') {
          checkbox.checked = false;
          const cells = document.querySelectorAll(`#citationTable table th:nth-child(${parseInt(columnIndex) + 2}), #citationTable table td:nth-child(${parseInt(columnIndex) + 2})`);
          cells.forEach(cell => cell.style.display = 'none');
        }
      });

      let isPaymentModalClick = false;
      window.addEventListener('click', (event) => {
        const paymentModal = document.getElementById('paymentModal');
        if (event.target === paymentModal && !isPaymentModalClick) {
          isPaymentModalClick = true;
          paymentModal.classList.remove('show');
          setTimeout(() => { paymentModal.style.display = 'none'; }, 300);
        } else {
          isPaymentModalClick = false;
        }
      });

      document.querySelector('#paymentModal .close').addEventListener('click', () => {
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.classList.remove('show');
        setTimeout(() => { paymentModal.style.display = 'none'; }, 300);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.getElementById('paymentModal').style.display === 'flex') {
          const paymentModal = document.getElementById('paymentModal');
          paymentModal.classList.remove('show');
          setTimeout(() => { paymentModal.style.display = 'none'; }, 300);
        }
      });
    });
  </script>
</body>
</html>