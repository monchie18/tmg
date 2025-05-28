<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Driver Violation Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f3f5;
      font-family: 'Inter', sans-serif;
    }
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    .header {
      background: linear-gradient(90deg, rgb(8, 6, 119), rgb(11, 23, 185));
      color: white;
      padding: 24px;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 30px;
    }
    .table th, .table td {
      vertical-align: middle;
      text-align: center;
    }
    .btn-custom {
      transition: background-color 0.2s ease;
    }
    .btn-custom:hover {
      background-color: #2563eb;
      color: white;
    }
    .violation-list {
      text-align: left;
      padding-left: 20px;
    }
    .sort-select {
      margin-bottom: 20px;
    }
     .loading {
      text-align: center;
      padding: 2rem;
      color: #6b7280;
      font-weight: 500;
    }
    .loading i {
      animation: spin 5s linear infinite;
    }
  </style>
</head>
<body>
  <!-- Include the Sidebar -->
  <?php include 'sidebar.php'; ?>
  <div class="container">
    <div class="header">
      <h4 class="font-bold text-lg">REPUBLIC OF THE PHILIPPINES</h4>
      <h4 class="font-bold text-lg">PROVINCE OF CAGAYAN • MUNICIPALITY OF BAGGAO</h4>
      <h1 class="font-extrabold text-3xl">DRIVER VIOLATION RECORDS</h1>
    </div>

    <div class="mb-4">
      <a href="index.php" class="btn btn-primary btn-custom mb-2">Add New Citation</a>
      <select id="sortSelect" class="form-select sort-select" style="width: auto; display: inline-block; margin-left: 10px;">
        <option value="name_asc">Sort by Name (A-Z)</option>
        <option value="name_desc">Sort by Name (Z-A)</option>
        <option value="violation_count">Sort by Violation Count</option>
      </select>
    </div>
    <div id="loading" class="loading" style="display: none;">
      <i class="fas fa-spinner fa-2x"></i> Loading citations...
    </div>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "traffic_citation_db";

    try {
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $sql = "
        SELECT d.driver_id, 
               CONCAT(d.last_name, ', ', d.first_name, ' ', COALESCE(d.middle_initial, ''), ' ', COALESCE(d.suffix, '')) AS driver_name,
               COUNT(v.violation_id) AS violation_count,
               GROUP_CONCAT(v.violation_type SEPARATOR '<br>') AS violation_list,
               GROUP_CONCAT(v.offense_count SEPARATOR '<br>') AS offense_counts,
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
                 END SEPARATOR '<br>') AS fines
        FROM drivers d
        LEFT JOIN citations c ON d.driver_id = c.driver_id AND c.is_archived = 0
        LEFT JOIN violations v ON c.citation_id = v.citation_id
        GROUP BY d.driver_id, driver_name
        HAVING violation_count > 0 OR COUNT(c.citation_id) = 0
      ";

      // Handle sorting based on user selection
      $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
      switch ($sort) {
        case 'name_desc':
          $sql .= " ORDER BY d.last_name DESC, d.first_name DESC";
          break;
        case 'violation_count':
          $sql .= " ORDER BY violation_count DESC, d.last_name, d.first_name";
          break;
        case 'name_asc':
        default:
          $sql .= " ORDER BY d.last_name, d.first_name";
          break;
      }

      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (empty($drivers)) {
        echo "<p>No driver records found.</p>";
      } else {
        echo "<table class='table table-bordered table-striped' id='citationTable'>";
        echo "<thead><tr><th>Driver Name</th><th>Violation</th><th>Offense Count</th><th>Fine (₱)</th><th>Action</th></tr></thead>";
        echo "<tbody>";
        foreach ($drivers as $driver) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars($driver['driver_name']) . "</td>";
          echo "<td class='violation-list'>" . ($driver['violation_list'] ? nl2br($driver['violation_list']) : 'None') . "</td>";
          echo "<td>" . ($driver['offense_counts'] ? nl2br($driver['offense_counts']) : 'None') . "</td>";
          echo "<td>" . ($driver['fines'] ? nl2br($driver['fines']) : 'None') . "</td>";
          echo "<td><a href='add_violation_form.php?driver_id=" . $driver['driver_id'] . "' class='btn btn-sm btn-primary btn-custom'>Add Violation</a></td>";
          echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
      }
    } catch (PDOException $e) {
      echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    $conn = null;
    ?>
  </div>

  <script>
   document.addEventListener('DOMContentLoaded', () => {
  const loadingDiv = document.getElementById('loading');
  const citationTable = document.getElementById('citationTable');
  const sortSelect = document.getElementById('sortSelect');

  // Show loading state
  if (loadingDiv && citationTable) {
    loadingDiv.style.display = 'block';
    citationTable.style.opacity = '0';
    
    setTimeout(() => {
      loadingDiv.style.display = 'none';
      citationTable.style.opacity = '1';
      
      // Add hover effect for table rows after content is visible
      const rows = document.querySelectorAll('.table tr');
      rows.forEach(row => {
        row.addEventListener('mouseenter', () => {
          row.style.cursor = 'pointer';
        });
        row.addEventListener('mouseleave', () => {
          row.style.cursor = 'default';
        });
      });
    }, 500);
  }

  // Handle sort selection
  if (sortSelect) {
    sortSelect.addEventListener('change', function() {
      const sortValue = this.value;
      if (sortValue) {
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('sort', sortValue);
        window.location.href = newUrl.toString();
      }
    });

    // Set initial sort based on URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const sortParam = urlParams.get('sort');
    if (sortParam) {
      sortSelect.value = sortParam;
    }
  }
});
  </script>
</body>
</html>