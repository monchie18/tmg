```php
<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Treasury Payment Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
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

    * {
      box-sizing: border-box;
    }

    body {
      background-color: var(--background);
      font-family: 'Roboto', sans-serif;
      color: var(--text-primary);
      line-height: 1.6;
      margin: 0;
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
      scroll-behavior: smooth;
    }

    .sidebar {
      width: 250px;
      background-color: var(--primary);
      color: white;
      padding: 1.5rem 1rem;
      position: sticky;
      top: 0;
      height: 100vh;
      z-index: 1000;
      transition: transform 0.3s ease;
    }

    .sidebar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .sidebar-toggle {
      display: none;
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      color: white;
      padding: 0.75rem 1rem;
      text-decoration: none;
      border-radius: 6px;
      margin-bottom: 0.5rem;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .sidebar a:hover {
      background-color: var(--primary-light);
      transform: translateX(4px);
    }

    .sidebar a.active {
      background-color: var(--primary-light);
      font-weight: 500;
    }

    .content {
      flex: 1;
      padding: 2rem;
      margin-left: 250px;
      overflow-y: auto;
    }

    .container {
      background-color: var(--card-bg);
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
      transition: box-shadow 0.3s ease;
    }

    .container:hover {
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    }

    .header {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      padding: 1.5rem;
      border-radius: 8px;
      text-align: center;
      margin-bottom: 2rem;
      position: relative;
    }

    .header h1 {
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0.5rem 0;
    }

    .header h4 {
      font-size: 1rem;
      font-weight: 500;
      margin: 0;
    }

    .sort-filter {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.5rem;
      align-items: center;
    }

    .sort-select, .search-input, .records-per-page, .status-select {
      border-radius: 6px;
      border: 1px solid var(--border);
      padding: 0.75rem;
      font-size: 0.9rem;
      background-color: white;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .sort-select, .records-per-page, .status-select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.5rem center;
      background-size: 1rem;
      min-width: 150px;
      max-width: 200px;
    }

    .search-input {
      flex: 1;
      max-width: 250px;
    }

    .sort-select:focus, .search-input:focus, .records-per-page:focus, .status-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      outline: none;
    }

    .summary-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      background-color: #f1f5f9;
      padding: 1.5rem;
      border-radius: 8px;
      margin-bottom: 2rem;
    }

    .summary-item {
      background-color: var(--card-bg);
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s ease;
    }

    .summary-item:hover {
      transform: translateY(-2px);
    }

    .summary-item h5 {
      font-size: 0.9rem;
      color: var(--text-secondary);
      margin-bottom: 0.5rem;
    }

    .summary-item p {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
    }

    .summary-item.paid p {
      color: var(--accent);
    }

    .summary-item.unpaid p {
      color: var(--danger);
    }

    .summary-item.total p {
      color: var(--primary);
    }

    .table-container {
      background-color: var(--card-bg);
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .table-responsive {
      overflow-x: auto;
      max-height: 60vh;
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
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .table tbody tr {
      transition: background-color 0.2s ease;
    }

    .table tbody tr:nth-child(even) {
      background-color: #f9fafb;
    }

    .table tbody tr:hover {
      background-color: #f1f5f9 Echinacea purpurea9;
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

    .btn-secondary {
      background-color: var(--secondary);
      border-color: var(--secondary);
      color: white;
    }

    .btn-secondary:hover {
      background-color: #374151;
      transform: translateY(-2px);
    }

    .text-primary {
      color: var(--primary);
      text-decoration: none;
    }

    .text-primary:hover {
      color: var(--primary-light);
      text-decoration: underline;
    }

    .debug, .empty-state {
      padding: 1.5rem;
      border-radius: 8px;
      margin: 1rem 0;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .empty-state {
      background-color: #e0f2fe;
      color: #1e40af;
    }

    .debug {
      background-color: #fee2e2;
      color: #b91c1c;
    }

    .loading {
      text-align: center;
      padding: 1.5rem;
      color: var(--text-secondary);
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
      font-size: 1rem;
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
      background-color: rgba(0, 0, 0, 0.6);
      align-items: center;
      justify-content: center;
      z-index: 2000;
    }

    .modal.show {
      display: flex;
      opacity: 1;
    }

    .modal-content {
      background-color: var(--card-bg);
      padding: 1.5rem;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
      transform: scale(0.9);
      transition: transform 0.3s ease-in-out;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
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
      color: var(--text-secondary);
      transition: color 0.2s ease;
    }

    .close:hover {
      color: var(--primary);
    }

    .modal-content h2 {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .modal-content .driver-info, .modal-content .offense-table, .modal-content .payment-form {
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .modal-content .driver-info {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: center;
    }

    .modal-content .driver-info .photo-placeholder {
      width: 100px;
      height: 100px;
      background-color: var(--border);
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      color: var(--text-secondary);
    }

    .modal-content .driver-info .details {
      flex-grow: 1;
    }

    .modal-content .driver-info p {
      margin: 0.5rem 0;
      font-size: 0.9rem;
    }

    .modal-content .offense-table table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
    }

    .modal-content .offense-table th, .modal-content .offense-table td {
      padding: 0.5rem;
      border: 1px solid var(--border);
      text-align: left;
    }

    .modal-content .offense-table th {
      background-color: #f1f5f9;
      color: var(--primary);
      font-weight: 600;
    }

    .modal-content .offense-table .total-row {
      font-weight: 600;
      background-color: #e2e8f0;
    }

    .modal-content .payment-form input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: 6px;
      margin-bottom: 0.75rem;
      font-size: 0.9rem;
      transition: border-color 0.3s ease;
    }

    .modal-content .payment-form input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .modal-content .payment-form p {
      margin: 0.5rem 0;
      font-size: 0.9rem;
    }

    .pagination-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .pagination {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      justify-content: center;
    }

    .page-item {
      display: flex;
    }

    .page-link {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border);
      background-color: white;
      color: var(--primary);
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .page-link:hover {
      background-color: var(--primary);
      color: white;
      border-color: var(--primary-light);
      transform: translateY(-2px);
    }

    .page-item.active .page-link {
      background-color: var(--primary);
      color: white;
      border-color: var(--primary-light);
      font-weight: 600;
    }

    .page-item.disabled .page-link {
      color: var(--text-secondary);
      cursor: not-allowed;
      background-color: #f8fafc;
      border-color: var(--border);
    }

    .pagination-info {
      font-size: 0.9rem;
      color: var(--text-secondary);
    }

    .ellipsis {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      color: var(--text-secondary);
    }

    @keyframes spin {
      100% { transform: rotate(360deg); }
    }

    @media (max-width: 1024px) {
      .content {
        margin-left: 0;
      }

      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar-toggle {
        display: block;
      }

      .container {
        padding: 1.5rem;
      }
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }

      .header h1 {
        font-size: 1.5rem;
      }

      .header h4 {
        font-size: 0.9rem;
      }

      .sort-filter {
        flex-direction: column;
        align-items: stretch;
      }

      .sort-select, .search-input, .records-per-page, .status-select {
        width: 100%;
        max-width: none;
      }

      .table th, .table td {
        font-size: 0.85rem;
        padding: 0.75rem;
      }

      .modal-content {
        width: 95%;
        max-height: 70vh;
      }

      .modal-content .driver-info {
        flex-direction: column;
        text-align: center;
      }

      .modal-content .driver-info .photo-placeholder {
        margin: 0 auto 0.75rem;
      }
    }

    @media (max-width: 576px) {
      .summary-section {
        grid-template-columns: 1fr;
      }

      .table th, .table td {
        font-size: 0.75rem;
        padding: 0.5rem;
      }

      .btn-custom {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
      }

      .page-link {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h3 class="text-lg font-semibold">Menu</h3>
      <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="fas fa-bars"></i></button>
    </div>
    <?php include 'sidebar.php'; ?>
  </div>

  <div class="content">
    <div class="container">
      <div class="header">
        <h4>Republic of the Philippines</h4>
        <h4>Province of Cagayan • Municipality of Baggao</h4>
        <h1>Treasury Payment Management</h1>
      </div>

      <!-- Payment Summary Section -->
      <?php
      try {
          $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $summaryQuery = "
              SELECT 
                  COUNT(DISTINCT c.ticket_number) AS total_citations,
                  SUM(CASE WHEN c.payment_status = 'Paid' THEN 1 ELSE 0 END) AS paid_citations,
                  SUM(CASE WHEN c.payment_status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid_citations,
                  COALESCE(SUM(
                      COALESCE(
                          CASE vl.offense_count
                              WHEN 1 THEN vt.fine_amount_1
                              WHEN 2 THEN vt.fine_amount_2
                              WHEN 3 THEN vt.fine_amount_3
                          END, 200
                      )
                  ), 0) AS total_fines,
                  COALESCE(SUM(CASE WHEN c.payment_status = 'Paid' THEN c.payment_amount ELSE 0 END), 0) AS total_paid
              FROM citations c
              LEFT JOIN violations vl ON c.citation_id = vl.citation_id
              LEFT JOIN violation_types vt ON vl.violation_type = vt.violation_type
              WHERE c.is_archived = 0
          ";
          $summaryStmt = $conn->prepare($summaryQuery);
          $summaryStmt->execute();
          $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

          // Debug
          error_log("Summary Query: " . $summaryQuery);
          error_log("Summary Result: " . print_r($summary, true));
          $paidRows = $conn->query("SELECT citation_id, ticket_number, payment_amount FROM citations WHERE payment_status = 'Paid' AND is_archived = 0")->fetchAll(PDO::FETCH_ASSOC);
          error_log("Paid Citations: " . print_r($paidRows, true));
      } catch(PDOException $e) {
          $summary = ['total_citations' => 0, 'paid_citations' => 0, 'unpaid_citations' => 0, 'total_fines' => 0, 'total_paid' => 0];
          error_log("Summary PDOException: " . $e->getMessage());
      }
      $conn = null;
      ?>
      <div class="summary-section">
        <div class="summary-item total">
          <h5>Total Citations</h5>
          <p><?php echo htmlspecialchars($summary['total_citations']); ?></p>
        </div>
        <div class="summary-item paid">
          <h5>Paid Citations</h5>
          <p><?php echo htmlspecialchars($summary['paid_citations']); ?></p>
        </div>
        <div class="summary-item unpaid">
          <h5>Unpaid Citations</h5>
          <p><?php echo htmlspecialchars($summary['unpaid_citations']); ?></p>
        </div>
        <div class="summary-item total">
          <h5>Total Fines</h5>
          <p>₱<?php echo number_format($summary['total_fines'], 2); ?></p>
        </div>
        <div class="summary-item paid">
          <h5>Total Paid</h5>
          <p>₱<?php echo number_format($summary['total_paid'], 2); ?></p>
        </div>
      </div>

      <div class="sort-filter">
        <select id="statusSelect" class="status-select" aria-label="Filter by Payment Status">
          <option value="Unpaid" <?php echo (filter_input(INPUT_GET, 'payment_status', FILTER_SANITIZE_STRING) ?? 'Unpaid') == 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
          <option value="Paid" <?php echo (filter_input(INPUT_GET, 'payment_status', FILTER_SANITIZE_STRING) ?? 'Unpaid') == 'Paid' ? 'selected' : ''; ?>>Paid</option>
          <option value="All" <?php echo (filter_input(INPUT_GET, 'payment_status', FILTER_SANITIZE_STRING) ?? 'Unpaid') == 'All' ? 'selected' : ''; ?>>All</option>
        </select>
        <select id="sortSelect" class="sort-select" aria-label="Sort Citations">
          <option value="apprehension_desc" <?php echo (filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc') == 'apprehension_desc' ? 'selected' : ''; ?>>Date (Newest)</option>
          <option value="apprehension_asc" <?php echo (filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc') == 'apprehension_asc' ? 'selected' : ''; ?>>Date (Oldest)</option>
          <option value="ticket_asc" <?php echo (filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc') == 'ticket_asc' ? 'selected' : ''; ?>>Ticket Number (Asc)</option>
          <option value="driver_asc" <?php echo (filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc') == 'driver_asc' ? 'selected' : ''; ?>>Driver Name (A-Z)</option>
          <option value="payment_asc" <?php echo (filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc') == 'payment_asc' ? 'selected' : ''; ?>>Payment Status (Paid)</option>
          <option value="payment_desc" <?php echo (filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'apprehension_desc') == 'payment_desc' ? 'selected' : ''; ?>>Payment Status (Unpaid)</option>
        </select>
        <input type="text" id="searchInput" class="search-input" placeholder="Search by Driver or Ticket" value="<?php echo htmlspecialchars(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? ''); ?>" aria-label="Search Citations">
        <select id="recordsPerPage" class="records-per-page" aria-label="Records Per Page">
          <option value="10" <?php echo (filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?? 20) == 10 ? 'selected' : ''; ?>>10</option>
          <option value="20" <?php echo (filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?? 20) == 20 ? 'selected' : ''; ?>>20</option>
          <option value="30" <?php echo (filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?? 20) == 30 ? 'selected' : ''; ?>>30</option>
          <option value="50" <?php echo (filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?? 20) == 50 ? 'selected' : ''; ?>>50</option>
          <option value="100" <?php echo (filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?? 20) == 100 ? 'selected' : ''; ?>>100</option>
        </select>
        <button id="exportCSV" class="btn btn-secondary btn-custom" title="Export to CSV" aria-label="Export to CSV"><i class="fas fa-file-csv me-1"></i> Export CSV</button>
      </div>

      <div id="loading" class="loading" style="display: none;">
        <i class="fas fa-spinner fa-2x"></i> Loading citations...
      </div>

      <div id="paymentTable" class="table-container">
        <div class="table-responsive">
          <!-- Populated by fetch_payments.php -->
        </div>
      </div>

      <?php
      try {
          $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          // Sanitize inputs
          $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
          $recordsPerPage = filter_input(INPUT_GET, 'records_per_page', FILTER_VALIDATE_INT) ?: 20;
          $search = htmlspecialchars(trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? ''), ENT_QUOTES, 'UTF-8');
          $payment_status = filter_input(INPUT_GET, 'payment_status', FILTER_SANITIZE_STRING) ?: 'Unpaid';
          $payment_status = in_array($payment_status, ['Unpaid', 'Paid', 'All']) ? $payment_status : 'Unpaid';

          // Pagination count query
          $countQuery = "SELECT COUNT(DISTINCT c.ticket_number) as total 
                        FROM citations c 
                        JOIN drivers d ON c.driver_id = d.driver_id 
                        WHERE c.is_archived = 0";
          $params = [];
          if ($payment_status !== 'All') {
              $countQuery .= " AND c.payment_status = :payment_status";
              $params[':payment_status'] = $payment_status;
          }
          if ($search) {
              $countQuery .= " AND (c.ticket_number LIKE :search OR CONCAT(d.last_name, ' ', d.first_name) LIKE :search)";
              $params[':search'] = "%$search%";
          }

          $countStmt = $conn->prepare($countQuery);
          foreach ($params as $key => $value) {
              $countStmt->bindValue($key, $value, PDO::PARAM_STR);
          }

          error_log("Count Query: " . $countQuery);
          error_log("Count Params: " . print_r($params, true));

          $countStmt->execute();
          $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
          $totalPages = ceil($totalRecords / $recordsPerPage);
      } catch(PDOException $e) {
          echo "<div class='debug'><i class='fas fa-exclamation-circle'></i> Error: Unable to fetch pagination data.</div>";
          error_log("Pagination PDOException: " . $e->getMessage());
          $totalRecords = 0;
          $totalPages = 1;
      }
      $conn = null;
      ?>

      <div class="pagination-container" id="paginationContainer" data-total-records="<?php echo $totalRecords; ?>" data-total-pages="<?php echo $totalPages; ?>" data-current-page="<?php echo $page; ?>" data-records-per-page="<?php echo $recordsPerPage; ?>">
        <div class="pagination-info">
          Showing <span id="recordStart"><?php echo ($page - 1) * $recordsPerPage + 1; ?></span> to <span id="recordEnd"><?php echo min($page * $recordsPerPage, $totalRecords); ?></span> of <span id="totalRecords"><?php echo $totalRecords; ?></span> citations
        </div>
        <nav aria-label="Page navigation">
          <ul class="pagination" id="pagination"></ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <div id="driverInfoModal" class="modal" role="dialog" aria-labelledby="driverInfoTitle" aria-hidden="true">
    <div class="modal-content">
      <span class="close" aria-label="Close Driver Info Modal">×</span>
      <h2 id="driverInfoTitle">Driver Information</h2>
      <div class="driver-info">
        <div class="photo-placeholder">No Photo</div>
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
      <button id="printModal" class="btn btn-secondary btn-custom" title="Print Driver Info" aria-label="Print Driver Info"><i class="fas fa-print me-1"></i> Print</button>
      <button id="closeModal" class="btn btn-primary btn-custom" title="Close Modal" aria-label="Close Driver Info Modal"><i class="fas fa-times me-1"></i> Close</button>
    </div>
  </div>

  <div id="paymentModal" class="modal" role="dialog" aria-labelledby="paymentModalTitle" aria-hidden="true">
    <div class="modal-content">
      <span class="close" aria-label="Close Payment Modal">×</span>
      <h2 id="paymentModalTitle">Payment Processing</h2>
      <div class="driver-info">
        <div class="photo-placeholder">No Photo</div>
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
        <input type="number" id="cashInput" step="0.01" min="0" placeholder="Enter cash amount" required aria-label="Cash Amount">
        <p><strong>Change:</strong> <span id="changeDisplay">₱0.00</span></p>
        <div id="paymentError" class="alert alert-danger" style="display: none;"></div>
        <button id="confirmPayment" class="btn btn-success btn-custom" title="Confirm Payment" aria-label="Confirm Payment"><i class="fas fa-check me-1"></i> Confirm</button>
        <button id="cancelPayment" class="btn btn-secondary btn-custom" title="Cancel Payment" aria-label="Cancel Payment"><i class="fas fa-times me-1"></i> Cancel</button>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
    const csrfToken = "<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>";

    document.addEventListener('DOMContentLoaded', () => {
      const elements = {
        loadingDiv: document.getElementById('loading'),
        paymentTable: document.getElementById('paymentTable'),
        sidebar: document.getElementById('sidebar'),
        sidebarToggle: document.getElementById('sidebarToggle'),
        content: document.querySelector('.content'),
        statusSelect: document.getElementById('statusSelect'),
        sortSelect: document.getElementById('sortSelect'),
        searchInput: document.getElementById('searchInput'),
        recordsPerPageSelect: document.getElementById('recordsPerPage'),
        paginationContainer: document.getElementById('paginationContainer'),
        pagination: document.getElementById('pagination'),
        driverInfoModal: document.getElementById('driverInfoModal'),
        paymentModal: document.getElementById('paymentModal'),
        exportCSV: document.getElementById('exportCSV')
      };

      // Modal control
      const showModal = (modal) => {
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
        modal.querySelector('.modal-content').focus();
      };

      const hideModal = (modal) => {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
      };

      // Sidebar toggle
      elements.sidebarToggle.addEventListener('click', () => {
        elements.sidebar.classList.toggle('open');
        elements.content.style.marginLeft = elements.sidebar.classList.contains('open') ? '250px' : '0';
      });

      // Initialize parameters
      const urlParams = new URLSearchParams(window.location.search);
      elements.statusSelect.value = urlParams.get('payment_status') || 'Unpaid';
      elements.sortSelect.value = urlParams.get('sort') || 'apprehension_desc';
      elements.searchInput.value = urlParams.get('search') || '';
      elements.recordsPerPageSelect.value = urlParams.get('records_per_page') || '20';
      let currentPage = parseInt(urlParams.get('page')) || 1;

      // Fetch table data
      const fetchTableData = (search, sort, paymentStatus, page, recordsPerPage) => {
        elements.loadingDiv.style.display = 'block';
        elements.paymentTable.style.opacity = '0';
        const params = new URLSearchParams({
          search: encodeURIComponent(search),
          sort: encodeURIComponent(sort),
          payment_status: encodeURIComponent(paymentStatus),
          page: encodeURIComponent(page),
          records_per_page: encodeURIComponent(recordsPerPage),
          csrf_token: encodeURIComponent(csrfToken)
        });
        const fetchUrl = 'fetch_payments.php?' + params.toString();
        console.log('Fetching:', fetchUrl);
        fetch(fetchUrl)
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            return response.text();
          })
          .then(data => {
            elements.paymentTable.querySelector('.table-responsive').innerHTML = data.trim() || "<div class='empty-state'><i class='fas fa-info-circle'></i> No citations found.</div>";
            elements.loadingDiv.style.display = 'none';
            elements.paymentTable.style.opacity = '1';
            attachTableEventListeners();
            updatePagination(page, parseInt(recordsPerPage));
          })
          .catch(error => {
            elements.loadingDiv.style.display = 'none';
            elements.paymentTable.querySelector('.table-responsive').innerHTML = `<div class='debug'><i class='fas fa-exclamation-circle'></i> Error: ${error.message}</div>`;
            console.error('Fetch error:', error);
          });
      };

      // Initial fetch
      fetchTableData(
        elements.searchInput.value,
        elements.sortSelect.value,
        elements.statusSelect.value,
        currentPage,
        elements.recordsPerPageSelect.value
      );

      // Filter and sort handlers
      elements.statusSelect.addEventListener('change', () => {
        currentPage = 1;
        fetchTableData(
          elements.searchInput.value,
          elements.sortSelect.value,
          elements.statusSelect.value,
          currentPage,
          elements.recordsPerPageSelect.value
        );
      });

      elements.sortSelect.addEventListener('change', () => {
        currentPage = 1;
        fetchTableData(
          elements.searchInput.value,
          elements.sortSelect.value,
          elements.statusSelect.value,
          currentPage,
          elements.recordsPerPageSelect.value
        );
      });

      elements.searchInput.addEventListener('input', debounce(() => {
        currentPage = 1;
        fetchTableData(
          elements.searchInput.value,
          elements.sortSelect.value,
          elements.statusSelect.value,
          currentPage,
          elements.recordsPerPageSelect.value
        );
      }, 500));

      elements.recordsPerPageSelect.addEventListener('change', () => {
        currentPage = 1;
        fetchTableData(
          elements.searchInput.value,
          elements.sortSelect.value,
          elements.statusSelect.value,
          currentPage,
          elements.recordsPerPageSelect.value
        );
      });

      // Debounce utility
      function debounce(func, wait) {
        let timeout;
        return function (...args) {
          clearTimeout(timeout);
          timeout = setTimeout(() => func(...args), wait);
        };
      }

      // Pagination
      const updatePagination = (currentPage, recordsPerPage) => {
        const totalRecords = parseInt(elements.paginationContainer.dataset.totalRecords);
        const totalPages = parseInt(elements.paginationContainer.dataset.totalPages);
        const maxPagesToShow = 5;
        elements.pagination.innerHTML = '';

        // Previous
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage <= 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">«</a>`;
        elements.pagination.appendChild(prevLi);

        // Pages
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        if (endPage - startPage < maxPagesToShow - 1) {
          startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        if (startPage > 1) {
          const firstPage = document.createElement('li');
          firstPage.className = 'page-item';
          firstPage.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
          elements.pagination.appendChild(firstPage);
          if (startPage > 2) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = `<span class="ellipsis">...</span>`;
            elements.pagination.appendChild(ellipsis);
          }
        }

        for (let i = startPage; i <= endPage; i++) {
          const pageLi = document.createElement('li');
          pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
          pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
          elements.pagination.appendChild(pageLi);
        }

        if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = `<span class="ellipsis">...</span>`;
            elements.pagination.appendChild(ellipsis);
          }
          const lastPage = document.createElement('li');
          lastPage.className = 'page-item';
          lastPage.innerHTML = `<a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>`;
          elements.pagination.appendChild(lastPage);
        }

        // Next
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage >= totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">»</a>`;
        elements.pagination.appendChild(nextLi);

        // Update info
        const recordStart = (currentPage - 1) * recordsPerPage + 1;
        const recordEnd = Math.min(currentPage * recordsPerPage, totalRecords);
        document.getElementById('recordStart').textContent = recordStart;
        document.getElementById('recordEnd').textContent = recordEnd;
        document.getElementById('totalRecords').textContent = totalRecords;

        // Page link listeners
        document.querySelectorAll('.page-link').forEach(link => {
          link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(link.getAttribute('data-page'));
            if (page && !link.parentElement.classList.contains('disabled')) {
              currentPage = page;
              fetchTableData(
                elements.searchInput.value,
                elements.sortSelect.value,
                elements.statusSelect.value,
                currentPage,
                elements.recordsPerPageSelect.value
              );
            }
          });
        });
      };

      // Table event listeners
      const attachTableEventListeners = () => {
        document.querySelectorAll('.driver-link').forEach(link => {
          link.addEventListener('click', (e) => {
            e.preventDefault();
            const driverId = link.getAttribute('data-driver-id');
            const zone = link.getAttribute('data-zone');
            const barangay = link.getAttribute('data-barangay');
            const municipality = link.getAttribute('data-municipality');
            const province = link.getAttribute('data-province');

            elements.loadingDiv.style.display = 'block';
            fetch(`get_driver_info.php?driver_id=${encodeURIComponent(driverId)}`, {
              headers: { 'Accept': 'application/json' }
            })
              .then(response => {
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                return response.json();
              })
              .then(data => {
                if (data.error) throw new Error(data.error);
                elements.loadingDiv.style.display = 'none';
                document.getElementById('licenseNumber').textContent = data.license_number || 'N/A';
                document.getElementById('driverName').textContent = data.driver_name || 'N/A';
                document.getElementById('driverAddress').textContent = `${zone ? zone + ', ' : ''}${barangay ? barangay + ', ' : ''}${municipality}, ${province}`;
                const offenseTable = document.getElementById('offenseRecords');
                offenseTable.innerHTML = '';
                let totalFine = 0;
                data.offenses.forEach(offense => {
                  const fine = parseFloat(offense.fine) || 0;
                  totalFine += fine;
                  const row = document.createElement('tr');
                  row.innerHTML = `
                    <td>${offense.date_time || 'N/A'}</td>
                    <td>${offense.offense}${offense.offense_count ? ' (Offense ' + offense.offense_count + ')' : ''}</td>
                    <td>₱${fine.toFixed(2)}</td>
                    <td>${offense.status || 'N/A'}</td>
                  `;
                  offenseTable.appendChild(row);
                });
                document.getElementById('totalFines').textContent = `₱${totalFine.toFixed(2)}`;
                document.getElementById('totalFineDisplay').textContent = `₱${totalFine.toFixed(2)}`;
                showModal(elements.driverInfoModal);
              })
              .catch(error => {
                elements.loadingDiv.style.display = 'none';
                document.getElementById('offenseRecords').innerHTML = `<tr><td colspan="4">Error: ${error.message}</td></tr>`;
                console.error('Fetch error:', error);
              });
          });
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

            elements.loadingDiv.style.display = 'block';
            fetch(`get_driver_info.php?driver_id=${encodeURIComponent(driverId)}&citation_id=${encodeURIComponent(citationId)}`, {
              headers: { 'Accept': 'application/json' }
            })
              .then(response => {
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                return response.json();
              })
              .then(data => {
                if (data.error) throw new Error(data.error);
                elements.loadingDiv.style.display = 'none';
                document.getElementById('paymentLicenseNumber').textContent = data.license_number || 'N/A';
                document.getElementById('paymentDriverName').textContent = data.driver_name || 'N/A';
                document.getElementById('paymentDriverAddress').textContent = `${zone ? zone + ', ' : ''}${barangay ? barangay + ', ' : ''}${municipality}, ${province}`;
                const offenseTable = document.getElementById('paymentOffenseRecords');
                offenseTable.innerHTML = '';
                let totalFine = 0;
                let unpaidFine = 0;
                data.offenses.forEach(offense => {
                  const fine = parseFloat(offense.fine) || 0;
                  totalFine += fine;
                  if (offense.status !== 'Paid') unpaidFine += fine;
                  const row = document.createElement('tr');
                  row.innerHTML = `
                    <td>${offense.date_time || 'N/A'}</td>
                    <td>${offense.offense}${offense.offense_count ? ' (Offense ' + offense.offense_count + ')' : ''}</td>
                    <td>₱${fine.toFixed(2)}</td>
                    <td>${offense.status || 'N/A'}</td>
                  `;
                  offenseTable.appendChild(row);
                });
                document.getElementById('paymentTotalFines').textContent = `₱${totalFine.toFixed(2)}`;
                document.getElementById('paymentTotalFineDisplay').textContent = `₱${totalFine.toFixed(2)}`;
                document.getElementById('amountDue').textContent = `₱${unpaidFine.toFixed(2)}`;
                const cashInput = document.getElementById('cashInput');
                const changeDisplay = document.getElementById('changeDisplay');
                const paymentError = document.getElementById('paymentError');
                cashInput.value = '';
                changeDisplay.textContent = '₱0.00';
                paymentError.style.display = 'none';
                const newCashInput = cashInput.cloneNode(true);
                cashInput.parentNode.replaceChild(newCashInput, cashInput);
                newCashInput.addEventListener('input', () => {
                  const cash = parseFloat(newCashInput.value) || 0;
                  const change = cash - unpaidFine;
                  changeDisplay.textContent = `₱${change >= 0 ? change.toFixed(2) : '0.00'}`;
                  paymentError.textContent = change < 0 ? 'Insufficient cash amount.' : '';
                  paymentError.style.display = change < 0 ? 'block' : 'none';
                });
                elements.paymentModal.dataset.citationId = citationId;
                showModal(elements.paymentModal);
              })
              .catch(error => {
                elements.loadingDiv.style.display = 'none';
                alert(`Error loading citation: ${error.message}`);
                console.error('Fetch error:', error);
              });
          });
        });
      };

      // Modal controls
      document.getElementById('closeModal').addEventListener('click', () => hideModal(elements.driverInfoModal));
      document.getElementById('printModal').addEventListener('click', () => window.print());
      document.querySelector('#driverInfoModal .close').addEventListener('click', () => hideModal(elements.driverInfoModal));
      document.getElementById('cancelPayment').addEventListener('click', () => hideModal(elements.paymentModal));
      document.querySelector('#paymentModal .close').addEventListener('click', () => hideModal(elements.paymentModal));

      // Modal click outside
      let isDriverModalClick = false;
      let isPaymentModalClick = false;
      window.addEventListener('click', (event) => {
        if (event.target === elements.driverInfoModal && !isDriverModalClick) {
          isDriverModalClick = true;
          hideModal(elements.driverInfoModal);
        } else if (event.target === elements.paymentModal && !isPaymentModalClick) {
          isPaymentModalClick = true;
          hideModal(elements.paymentModal);
        } else {
          isDriverModalClick = isPaymentModalClick = false;
        }
      });

      // Escape key
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          if (elements.driverInfoModal.classList.contains('show')) hideModal(elements.driverInfoModal);
          if (elements.paymentModal.classList.contains('show')) hideModal(elements.paymentModal);
        }
      });

      // Confirm payment
      document.getElementById('confirmPayment').addEventListener('click', () => {
        const cashInput = document.getElementById('cashInput');
        const changeDisplay = document.getElementById('changeDisplay');
        const paymentError = document.getElementById('paymentError');
        const citationId = elements.paymentModal.dataset.citationId;
        const cash = parseFloat(cashInput.value) || 0;
        const unpaidFines = parseFloat(document.getElementById('amountDue').textContent.replace('₱', '')) || 0;

        if (cash < unpaidFines) {
          paymentError.textContent = 'Insufficient cash amount.';
          paymentError.style.display = 'block';
          return;
        }

        const change = cash - unpaidFines;
        elements.loadingDiv.style.display = 'block';
        fetch('pay_citation.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `citation_id=${encodeURIComponent(citationId)}&amount=${encodeURIComponent(cash)}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
          .then(response => {
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            return response.json();
          })
          .then(data => {
            elements.loadingDiv.style.display = 'none';
            if (data.status === 'success') {
              const receiptUrl = `receipt.php?citation_id=${encodeURIComponent(citationId)}&amount_paid=${encodeURIComponent(cash)}&change=${encodeURIComponent(change)}&payment_date=${encodeURIComponent(data.payment_date)}`;
              window.open(receiptUrl, '_blank');
              fetchTableData(
                elements.searchInput.value,
                elements.sortSelect.value,
                elements.statusSelect.value,
                currentPage,
                elements.recordsPerPageSelect.value
              );
              hideModal(elements.paymentModal);
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            elements.loadingDiv.style.display = 'none';
            alert(`Payment error: ${error.message}`);
            console.error('Fetch error:', error);
          });
      });

      // CSV export
      elements.exportCSV.addEventListener('click', () => {
        const rows = document.querySelectorAll('#paymentTable table tr');
        if (!rows.length) return;
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
        link.download = 'Treasury_Payment_Records.csv';
        link.click();
      });
    });
  </script>
</body>
</html>