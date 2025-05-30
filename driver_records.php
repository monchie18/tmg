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
  <title>Driver Violation Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
  <style>
    :root {
      --primary: #1e3a8a;
      --primary-dark: #1e40af;
      --secondary: #6b7280;
      --success: #22c55e;
      --danger: #ef4444;
      --warning: #f97316;
      --bg-light: #f8fafc;
      --text-dark: #1f2937;
      --border: #d1d5db;
    }

    body {
      background-color: var(--bg-light);
      font-family: 'Inter', sans-serif;
      color: var(--text-dark);
      line-height: 1.6;
      margin: 0;
      padding: 0;
      overflow: hidden;
      height: 100vh;
      display: flex;
    }

    /* Sidebar Styles (Copied from Previous Fix) */
    .sidebar {
      width: 260px;
      background: linear-gradient(180deg, #1e3a8a 0%, #2b5dc9 70%, #3b82f6 100%);
      color: #fff;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding: 25px 20px;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease-in-out;
      z-index: 1000;
      overflow-y: auto;
      flex-shrink: 0;
    }

    .sidebar.collapsed {
      width: 80px;
    }

    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background-color: rgba(255, 255, 255, 0.3);
      border-radius: 3px;
    }

    .sidebar-header {
      text-align: center;
      margin-bottom: 35px;
      padding-bottom: 20px;
      border-bottom: 2px solid rgba(255, 255, 255, 0.3);
      position: relative;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .sidebar-header h4 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #facc15;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: opacity 0.3s ease, transform 0.3s ease;
      margin: 0;
    }

    .sidebar.collapsed .sidebar-header h4 {
      opacity: 0;
      transform: translateX(-20px);
    }

    .sidebar-toggle {
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      color: white;
      border: none;
      border-radius: 0 10px 10px 0;
      padding: 10px 12px;
      cursor: pointer;
      box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.25);
      transition: transform 0.3s ease;
      display: none;
    }

    .sidebar-toggle:hover {
      transform: scale(1.1);
    }

    .sidebar-nav {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-nav li {
      margin-bottom: 10px;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      color: #fff;
      padding: 14px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
      position: relative;
      overflow: hidden;
    }

    .sidebar-nav a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: all 0.4s ease;
      z-index: 0;
    }

    .sidebar-nav a:hover::before {
      left: 0;
    }

    .sidebar-nav a i {
      margin-right: 15px;
      width: 22px;
      text-align: center;
      font-size: 1.2rem;
      transition: transform 0.3s ease;
    }

    .sidebar-nav a:hover i {
      transform: translateX(5px);
    }

    .sidebar-nav a:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateX(5px);
    }

    .sidebar-nav a.active {
      background: #2563eb;
      font-weight: 600;
      box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .sidebar-nav a.active::before {
      display: none;
    }

    .sidebar.collapsed .sidebar-nav a span {
      display: none;
    }

    .sidebar.collapsed .sidebar-nav a {
      justify-content: center;
      padding: 14px;
    }

    .sidebar.collapsed .sidebar-nav a i {
      margin-right: 0;
      transform: scale(1.2);
    }

    .logout-link {
      position: absolute;
      bottom: 25px;
      width: calc(100% - 40px);
    }

    .logout-link a {
      display: flex;
      align-items: center;
      color: #ff4444;
      padding: 14px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
      background: rgba(255, 68, 68, 0.1);
    }

    .logout-link a:hover {
      background: rgba(255, 68, 68, 0.2);
      transform: translateX(5px);
    }

    .sidebar.collapsed .logout-link a {
      justify-content: center;
      padding: 14px;
    }

    .sidebar.collapsed .logout-link a span {
      display: none;
    }

    /* Content Styles */
    .content {
      flex: 1;
      padding: 1rem;
      overflow-y: auto;
      height: 100vh;
      margin-left: 260px;
      transition: margin-left 0.3s ease-in-out;
    }

    .content.collapsed {
      margin-left: 80px;
    }

    .container {
      max-width: 100%;
      max-height: calc(100vh - 2rem);
      margin: 0 auto;
      padding: 1.5rem;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow-y: auto;
    }

    .container:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .header {
      background: linear-gradient(135deg, var(--primary), #3b82f6);
      color: white;
      padding: 1.5rem;
      border-radius: 12px;
      text-align: center;
      margin-bottom: 1.5rem;
      position: relative;
      overflow: hidden;
    }

    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--warning), #facc15);
    }

    .header h4 {
      font-size: 1rem;
      font-weight: 500;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      opacity: 0.85;
      margin-bottom: 0.25rem;
    }

    .header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      margin: 0;
    }

    .sort-filter {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
      align-items: center;
    }

    .sort-select, .search-input {
      border-radius: 8px;
      border: 1px solid var(--border);
      padding: 0.5rem 0.75rem;
      font-size: 0.9rem;
      background-color: white;
      transition: all 0.3s ease;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .sort-select {
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

    .sort-select:focus, .search-input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .table-responsive {
      overflow-x: auto;
      border-radius: 8px;
      background-color: white;
      max-height: 50vh;
    }

    .table th {
      background-color: #f1f5f9;
      color: var(--primary);
      font-weight: 600;
      padding: 0.75rem;
      text-align: center;
      border-bottom: 2px solid var(--border);
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      white-space: nowrap;
    }

    .table td {
      padding: 0.75rem;
      vertical-align: middle;
      text-align: center;
      border-bottom: 1px solid var(--border);
      font-size: 0.85rem;
      white-space: nowrap;
    }

    .table tr {
      transition: background-color 0.2s ease;
    }

    .table tr:hover {
      background-color: #f8fafc;
    }

    .violation-list {
      text-align: left;
      padding-left: 20px;
      white-space: normal;
      line-height: 1.5;
    }

    .btn-custom {
      padding: 0.4rem 0.75rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.85rem;
      line-height: 1.5;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
      border: none;
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
      transform: translateY(-1px);
    }

    .empty-state {
      color: var(--danger);
      background-color: #fef2f2;
      padding: 0.75rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .loading {
      text-align: center;
      padding: 1.5rem;
      color: var(--secondary);
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

    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 260px;
        transform: translateX(-100%);
        position: fixed;
        top: 0;
        left: 0;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar.collapsed {
        width: 260px;
      }

      .sidebar-header h4 {
        opacity: 1;
        transform: none;
      }

      .sidebar-nav a span {
        display: inline;
      }

      .sidebar-nav a {
        justify-content: flex-start;
        padding: 14px 18px;
      }

      .sidebar-nav a i {
        margin-right: 15px;
        transform: none;
      }

      .logout-link a span {
        display: inline;
      }

      .sidebar-toggle {
        display: block;
        position: fixed;
        top: 20px;
        left: 10px;
        z-index: 1100;
      }

      .sidebar.open ~ .content .sidebar-toggle {
        left: 270px;
      }

      .content {
        margin-left: 0;
      }

      .content.collapsed {
        margin-left: 0;
      }

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

      .sort-select, .search-input {
        width: 100%;
        max-width: none;
      }

      .table th, .table td {
        font-size: 0.75rem;
        padding: 0.5rem;
      }

      .btn-custom {
        padding: 0.3rem 0.5rem;
        font-size: 0.75rem;
      }
    }

    @media (max-width: 480px) {
      .table th, .table td {
        font-size: 0.7rem;
        padding: 0.4rem;
      }

      .btn-custom {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
      }

      .sort-select, .search-input {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="content" id="content">
    <div class="container">
      <div class="header">
        <h4>Republic of the Philippines</h4>
        <h4>Province of Cagayan • Municipality of Baggao</h4>
        <h1>Driver Violation Records</h1>
      </div>

      <div class="sort-filter">
        <a href="index.php" class="btn btn-primary btn-custom" aria-label="Add New Citation"><i class="fas fa-plus"></i> Add New Citation</a>
        <a href="records.php" class="btn btn-primary btn-custom" aria-label="View Citation Records"><i class="fas fa-file-alt"></i> View Citation Records</a>
        <select id="sortSelect" class="sort-select" aria-label="Sort Options">
          <option value="name_asc">Sort by Name (A-Z)</option>
          <option value="name_desc">Sort by Name (Z-A)</option>
          <option value="violation_count">Sort by Violation Count</option>
        </select>
        <input type="text" id="searchInput" class="search-input" placeholder="Search by Driver Name" aria-label="Search Drivers">
      </div>

      <div id="loading" class="loading" style="display: none;">
        <i class="fas fa-spinner fa-2x"></i> Loading records...
      </div>

      <div id="driverTable" class="table-responsive">
        <?php
        try {
          $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $sql = "
            SELECT d.driver_id, 
                   CONCAT(d.last_name, ', ', d.first_name, ' ', COALESCE(d.middle_initial, ''), ' ', COALESCE(d.suffix, '')) AS driver_name,
                   COUNT(v.violation_id) AS violation_count,
                   GROUP_CONCAT(v.violation_type SEPARATOR '\n') AS violation_list,
                   GROUP_CONCAT(v.offense_count SEPARATOR '\n') AS offense_counts,
                   GROUP_CONCAT(
                     CASE v.violation_type
                       WHEN 'No Helmet (Driver)' THEN 150
                       WHEN 'No Helmet (Backrider)' THEN 150
                       WHEN 'No Driver’s License / Minor' THEN 500
                       WHEN 'No / Expired Vehicle Registration' THEN 2500
                       WHEN 'No / Defective Parts & Accessories' THEN 500
                       WHEN 'Noisy Muffler (98db above)' THEN 
                         CASE v.offense_count WHEN 1 THEN 2500 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
                       WHEN 'No Muffler Attached' THEN 2500
                       WHEN 'Reckless / Arrogant Driving' THEN 
                         CASE v.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
                       WHEN 'Disregarding Traffic Sign' THEN 150
                       WHEN 'Illegal Modification' THEN 150
                       WHEN 'Passenger on Top of the Vehicle' THEN 150
                       WHEN 'Illegal Parking' THEN 
                         CASE v.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
                       WHEN 'Road Obstruction' THEN 
                         CASE v.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
                       WHEN 'Blocking Pedestrian Lane' THEN 
                         CASE v.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
                       WHEN 'Loading/Unloading in Prohibited Zone' THEN 
                         CASE v.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 2500 END
                       WHEN 'Double Parking' THEN 
                         CASE v.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 1500 END
                       WHEN 'Drunk Driving' THEN 
                         CASE v.offense_count WHEN 1 THEN 500 WHEN 2 THEN 1000 WHEN 3 THEN 1500 END
                       WHEN 'Colorum Operation' THEN 
                         CASE v.offense_count WHEN 1 THEN 2500 WHEN 2 THEN 3000 WHEN 3 THEN 3000 END
                       WHEN 'No Trashbin' THEN 
                         CASE v.offense_count WHEN 1 THEN 1000 WHEN 2 THEN 2000 WHEN 3 THEN 2500 END
                       WHEN 'Driving in Short / Sando' THEN 
                         CASE v.offense_count WHEN 1 THEN 200 WHEN 2 THEN 500 WHEN 3 THEN 1000 END
                       WHEN 'Overloaded Passenger' THEN 
                         CASE v.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
                       WHEN 'Over Charging / Under Charging' THEN 
                         CASE v.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
                       WHEN 'Refusal to Convey Passenger/s' THEN 
                         CASE v.offense_count WHEN 1 THEN 500 WHEN 2 THEN 750 WHEN 3 THEN 1000 END
                       WHEN 'Drag Racing' THEN 
                         CASE v.offense_count WHEN 1 THEN 1000 WHEN 2 THEN 1500 WHEN 3 THEN 2500 END
                       WHEN 'No Enhanced Oplan Visa Sticker' THEN 300
                       WHEN 'Failure to Present E-OV Match Card' THEN 200
                       ELSE 200
                     END SEPARATOR '\n') AS fines
            FROM drivers d
            LEFT JOIN citations c ON d.driver_id = c.driver_id AND c.is_archived = 0
            LEFT JOIN violations v ON c.citation_id = v.citation_id
          ";

          $params = [];
          $whereClauses = [];

          // Add search condition to WHERE clause
          $search = isset($_GET['search']) ? trim($_GET['search']) : '';
          if ($search) {
            $whereClauses[] = "CONCAT(d.last_name, ' ', d.first_name) LIKE :search";
            $params['search'] = "%$search%";
          }

          // Append WHERE clause if there are conditions
          if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
          }

          // Group by driver
          $sql .= " GROUP BY d.driver_id, driver_name";

          // HAVING clause for filtering grouped results
          $sql .= " HAVING violation_count > 0 OR COUNT(c.citation_id) = 0";

          // Sorting
          $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
          switch ($sort) {
            case 'name_desc':
              $sql .= " ORDER BY driver_name DESC";
              break;
            case 'violation_count':
              $sql .= " ORDER BY violation_count DESC, driver_name";
              break;
            case 'name_asc':
            default:
              $sql .= " ORDER BY driver_name";
              break;
          }

          $stmt = $conn->prepare($sql);
          $stmt->execute($params);
          $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (empty($drivers)) {
            echo "<p class='empty-state'><i class='fas fa-info-circle'></i> No driver records found.</p>";
          } else {
            echo "<table class='table table-bordered table-striped' id='citationTable'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th><i class='fas fa-user me-2'></i>Driver Name</th>";
            echo "<th><i class='fas fa-exclamation-triangle me-2'></i>Violation</th>";
            echo "<th><i class='fas fa-sort-numeric-up me-2'></i>Offense Count</th>";
            echo "<th><i class='fas fa-money-bill-wave me-2'></i>Fine (₱)</th>";
            echo "<th><i class='fas fa-cog me-2'></i>Action</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            foreach ($drivers as $driver) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($driver['driver_name']) . "</td>";
              echo "<td class='violation-list'>" . ($driver['violation_list'] ? nl2br(htmlspecialchars($driver['violation_list'])) : 'None') . "</td>";
              echo "<td class='violation-list'>" . ($driver['offense_counts'] ? nl2br(htmlspecialchars($driver['offense_counts'])) : 'None') . "</td>";
              echo "<td class='violation-list'>" . ($driver['fines'] ? nl2br(htmlspecialchars($driver['fines'])) : 'None') . "</td>";
              echo "<td>";
              echo "<a href='add_violation_form.php?driver_id=" . htmlspecialchars($driver['driver_id']) . "' class='btn btn-sm btn-primary btn-custom' aria-label='Add Violation'><i class='fas fa-plus'></i> Add Violation</a>";
              echo "</td>";
              echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
          }
        } catch (PDOException $e) {
          echo "<p class='empty-state'><i class='fas fa-exclamation-circle'></i> Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        $conn = null;
        ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sidebar = document.getElementById('sidebar');
      const sidebarToggle = document.getElementById('sidebarToggle');
      const content = document.getElementById('content');
      const loadingDiv = document.getElementById('loading');
      const driverTable = document.getElementById('driverTable');
      const sortSelect = document.getElementById('sortSelect');
      const searchInput = document.getElementById('searchInput');

      // Sidebar toggle
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        sidebar.classList.toggle('collapsed');
        sidebarToggle.classList.toggle('active');
        content.classList.toggle('collapsed');

        if (window.innerWidth <= 768) {
          if (sidebar.classList.contains('open')) {
            sidebarToggle.style.left = '270px';
          } else {
            sidebarToggle.style.left = '10px';
          }
        }
      });

      // Close sidebar if clicked outside on mobile
      document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('open')) {
          sidebar.classList.remove('open');
          sidebarToggle.classList.remove('active');
          sidebarToggle.style.left = '10px';
        }
      });

      // Adjust layout on window resize
      window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
          sidebar.classList.remove('open');
          sidebarToggle.style.left = '10px';
          content.classList.toggle('collapsed', sidebar.classList.contains('collapsed'));
        }
      });

      // Show loading state
      if (loadingDiv && driverTable) {
        loadingDiv.style.display = 'block';
        driverTable.style.opacity = '0';
        setTimeout(() => {
          loadingDiv.style.display = 'none';
          driverTable.style.opacity = '1';

          // Add hover effect for table rows
          const rows = document.querySelectorAll('.table tr');
          rows.forEach(row => {
            row.addEventListener('mouseenter', () => {
              row.style.cursor = 'pointer';
            });
            row.addEventListener('mouseleave', () => {
              row.style.cursor = 'default';
            });
          });
        }, 300);
      }

      // Handle sort selection
      if (sortSelect) {
        sortSelect.addEventListener('change', function() {
          const sortValue = this.value;
          const url = new URL(window.location);
          url.searchParams.set('sort', sortValue);
          url.searchParams.delete('search'); // Reset search on sort
          window.location.href = url.toString();
        });

        const urlParams = new URLSearchParams(window.location.search);
        const sortParam = urlParams.get('sort') || 'name_asc';
        sortSelect.value = sortParam;
      }

      // Search functionality
      if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
          const url = new URL(window.location);
          url.searchParams.set('search', searchInput.value);
          window.location.href = url.toString();
        }, 300));

        const searchParam = urlParams.get('search') || '';
        searchInput.value = searchParam;
      }

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
    });
  </script>
</body>
</html>