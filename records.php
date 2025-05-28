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
      transition: opacity 0.3s ease;
    }

    .modal.show {
      opacity: 1;
    }

    .modal-content {
      background-color: white;
      padding: 2rem;
      border-radius: 12px;
      width: 90%;
      max-width: 500px;
      position: relative;
      transform: scale(0.95);
      transition: transform 0.3s ease;
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

    .modal-content input {
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 0.75rem;
      width: 100%;
      font-size: 0.9rem;
      transition: border-color 0.3s ease;
    }

    .modal-content input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .modal-content .btn-custom {
      margin-top: 1rem;
    }

    .error-message {
      color: #b91c1c;
      font-size: 0.9rem;
      margin-top: 0.5rem;
      display: none;
      background-color: #fef2f2;
      padding: 0.5rem;
      border-radius: 8px;
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
    }
  </style>
</head>
<body>
  <!-- Include the Sidebar -->
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
    </div>

    <div id="loading" class="loading" style="display: none;">
      <i class="fas fa-spinner fa-2x"></i> Loading citations...
    </div>

    <div id="citationTable">
      <?php
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "traffic_citation_db";

      try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $show_archived = isset($_GET['show_archived']) && $_GET['show_archived'] == 1;

        $query = "
          SELECT c.citation_id, c.ticket_number, 
                 CONCAT(d.last_name, ', ', d.first_name, 
                        IF(d.middle_initial != '', CONCAT(' ', d.middle_initial), ''), 
                        IF(d.suffix != '', CONCAT(' ', d.suffix), '')) AS driver_name,
                 d.license_number, v.plate_mv_engine_chassis_no, v.vehicle_type, 
                 c.apprehension_datetime,
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
          echo "<th><i class='fas fa-ticket-alt me-2'></i>Ticket Number</th>";
          echo "<th><i class='fas fa-user me-2'></i>Driver Name</th>";
          echo "<th><i class='fas fa-id-card me-2'></i>License Number</th>";
          echo "<th><i class='fas fa-car me-2'></i>Vehicle Plate</th>";
          echo "<th><i class='fas fa-car-side me-2'></i>Vehicle Type</th>";
          echo "<th><i class='fas fa-clock me-2'></i>Apprehension Date</th>";
          echo "<th><i class='fas fa-exclamation-triangle me-2'></i>Violations</th>";
          echo "<th><i class='fas fa-info-circle me-2'></i>Archiving Reason</th>";
          echo "<th><i class='fas fa-cog me-2'></i>Actions</th>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";
          foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ticket_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['driver_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['license_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['plate_mv_engine_chassis_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['vehicle_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['apprehension_datetime']) . "</td>";
            echo "<td>" . htmlspecialchars($row['violations'] ?? 'None') . "</td>";
            echo "<td>" . htmlspecialchars($row['archiving_reason'] ?? 'N/A') . "</td>";
            echo "<td class='d-flex gap-2'>";
            if (!$show_archived) {
              echo "<a href='edit_citation.php?id=" . $row['citation_id'] . "' class='btn btn-sm btn-primary btn-custom'><i class='fas fa-edit'></i> Edit</a>";
              echo "<a href='delete_citation.php?id=" . $row['citation_id'] . "' class='btn btn-sm btn-danger btn-custom' onclick='return confirm(\"Are you sure you want to delete this citation?\")'><i class='fas fa-trash'></i> Delete</a>";
            }
            $actionText = $show_archived ? "Unarchive" : "Archive";
            $iconClass = $show_archived ? "fa-box-open" : "fa-archive";
            echo "<button class='btn btn-sm btn-archive archive-btn' data-id='" . $row['citation_id'] . "' data-action='" . ($show_archived ? 0 : 1) . "' data-is-tro='" . ($row['is_tro'] ? '1' : '0') . "'><i class='fas " . $iconClass . "'></i> " . $actionText . "</button>";
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
  </div>

  <!-- Archive Modal -->
  <div id="archiveModal" class="modal">
    <div class="modal-content">
      <span class="close">×</span>
      <h2>Remarks Note: Reason for Archiving</h2>
      <input type="text" id="remarksReason" class="form-control mb-3" placeholder="Enter reason for archiving/unarchiving (max 255 characters)" maxlength="255" required>
      <div id="errorMessage" class="error-message"></div>
      <button id="confirmArchive" class="btn btn-primary btn-custom">Confirm</button>
      <button id="cancelArchive" class="btn btn-secondary btn-custom">Cancel</button>
    </div>
  </div>

  <script>
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
          body: `id=${encodeURIComponent(currentCitationId)}&archive=${encodeURIComponent(currentAction)}&remarksReason=${encodeURIComponent(reason)}`
        })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.status === 'success') {
            window.location.reload();
          }
        })
        .catch(error => {
          alert('Error archiving citation: ' + error);
        });

        archiveModal.classList.remove('show');
        setTimeout(() => {
          archiveModal.style.display = 'none';
        }, 300);
      });

      window.addEventListener('click', (event) => {
        if (event.target === archiveModal) {
          archiveModal.classList.remove('show');
          setTimeout(() => {
            archiveModal.style.display = 'none';
          }, 300);
          errorMessage.style.display = 'none';
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
    });
  </script>
</body>
</html>