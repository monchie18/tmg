<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "traffic_citation_db";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  // Get the highest ticket number
  $stmt = $conn->query("SELECT MAX(CAST(ticket_number AS UNSIGNED)) AS max_ticket FROM citations");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $max_ticket = $row['max_ticket'] ? (int)$row['max_ticket'] : 6100; // Start at 06100 if no records
  $next_ticket = sprintf("%05d", $max_ticket + 1); // Format as 5 digits, e.g., 06101
} catch (PDOException $e) {
  $next_ticket = "06101"; // Fallback in case of error
}
$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Traffic Citation Ticket</title>
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
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .sidebar {
      width: 220px;
      height: 100vh;
      background-color: var(--primary);
      padding: 1rem;
      color: white;
      transition: transform 0.3s ease;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      overflow-y: auto;
    }

    .sidebar-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
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
      border-radius: 8px;
      margin-bottom: 0.5rem;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .sidebar a:hover {
      background-color: var(--primary-dark);
      transform: translateX(4px);
    }

    .sidebar a.active {
      background-color: #3b82f6;
      font-weight: 600;
    }

    .content {
      flex: 1;
      margin-left: 220px;
      padding: 1rem;
      overflow-y: auto;
      height: 100vh;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
      }

      .sidebar-toggle {
        display: block;
      }
    }

    .ticket-container {
      background-color: white;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin: 1rem;
      width: calc(100% - 2rem);
      max-width: 1200px;
    }

    .ticket-container:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .header {
      background: linear-gradient(90deg, var(--primary), #3b82f6);
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
      height: 4px;
      background: linear-gradient(90deg, var(--warning), #facc15);
    }

    .ticket-number {
      position: absolute;
      top: 1rem;
      right: 1.5rem;
      font-weight: 600;
      background: #fef3c7;
      padding: 0.5rem 1rem;
      border: 2px solid var(--warning);
      border-radius: 8px;
      font-size: 1rem;
      color: var(--text-dark);
      transition: transform 0.2s ease;
    }

    .ticket-number:hover {
      transform: scale(1.05);
    }

    .section {
      background-color: #f9fafb;
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      border: 1px solid var(--border);
      transition: background-color 0.2s ease;
    }

    .section h5 {
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--primary);
      font-size: 1.2rem;
      border-bottom: 2px solid var(--border);
      padding-bottom: 0.5rem;
    }

    .form-label {
      font-weight: 500;
      color: #374151;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid var(--border);
      padding: 0.5rem;
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      outline: none;
    }

    .form-control.is-invalid {
      border-color: var(--danger);
      background-color: #fef2f2;
    }

    .violation-category {
      margin-bottom: 1.5rem;
    }

    .violation-category h6 {
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: #2563eb;
      font-size: 1rem;
    }

    .violation-list .form-check {
      margin-bottom: 0.5rem;
      padding-left: 2rem;
    }

    .form-check-input:checked {
      background-color: #2563eb;
      border-color: #2563eb;
    }

    .remarks textarea {
      resize: vertical;
      min-height: 80px;
      font-size: 0.9rem;
    }

    .footer {
      font-size: 0.85rem;
      color: var(--secondary);
      padding: 1rem 0;
      border-top: 1px solid var(--border);
      text-align: justify;
      line-height: 1.6;
    }

    .signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .signature-box {
      flex: 1 1 45%;
      min-width: 200px;
    }

    .signature-line {
      border-top: 2px solid var(--text-dark);
      margin-top: 2rem;
    }

    .btn-custom {
      background-color: #2563eb;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 500;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }

    .btn-outline-secondary, .btn-outline-danger {
      border-radius: 8px;
      padding: 0.4rem 0.8rem;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    #otherViolationInput, #otherVehicleInput {
      display: none;
      margin-top: 0.5rem;
      border-radius: 8px;
    }

    @media print {
      .sidebar, .ticket-number, .btn-custom, .btn-outline-secondary, .btn-outline-danger {
        display: none;
      }

      .content {
        margin-left: 0;
      }

      .ticket-container {
        box-shadow: none;
        border: none;
        margin: 0;
        padding: 1rem;
        width: 100%;
      }

      .section {
        border: none;
        padding: 1rem;
      }
    }

    @media (max-width: 1024px) {
      .ticket-container {
        padding: 1.5rem;
        margin: 0.5rem;
      }

      .content {
        margin-left: 0;
      }

      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }
    }

    @media (max-width: 768px) {
      .ticket-container {
        padding: 1rem;
      }

      .header h1 {
        font-size: 1.5rem;
      }

      .header h4 {
        font-size: 0.9rem;
      }

      .ticket-number {
        position: static;
        margin: 0 auto 1rem;
        text-align: center;
        display: block;
      }

      .section {
        padding: 1rem;
      }

      .form-control, .form-select {
        font-size: 0.85rem;
        padding: 0.4rem;
      }

      .signature-box {
        flex: 1 1 100%;
      }
    }

    @media (max-width: 480px) {
      .ticket-container {
        padding: 0.75rem;
      }

      .header h1 {
        font-size: 1.25rem;
      }

      .form-label {
        font-size: 0.8rem;
      }

      .form-control, .form-select {
        font-size: 0.8rem;
        padding: 0.3rem;
      }

      .btn-custom {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h3 class="text-lg font-semibold">Menu</h3>
      <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    </div>
    <?php include 'sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="content">
    <form id="citationForm" action="insert_citation.php" method="POST">
      <div class="ticket-container position-relative">
        <div class="header">
          <h4 class="font-bold text-lg">REPUBLIC OF THE PHILIPPINES</h4>
          <h4 class="font-bold text-lg">PROVINCE OF CAGAYAN • MUNICIPALITY OF BAGGAO</h4>
          <h1 class="font-extrabold text-3xl mt-2">TRAFFIC CITATION TICKET</h1>
          <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($next_ticket); ?>">
          <div class="ticket-number"><?php echo htmlspecialchars($next_ticket); ?></div>
        </div>

        <!-- Driver Info -->
        <div class="section">
          <h5>Driver Information</h5>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">M.I.</label>
              <input type="text" name="middle_initial" class="form-control" placeholder="M.I.">
            </div>
            <div class="col-md-2">
              <label class="form-label">Suffix</label>
              <input type="text" name="suffix" class="form-control" placeholder="e.g., Jr.">
            </div>
            <div class="col-md-3">
              <label class="form-label">Zone</label>
              <input type="text" name="zone" class="form-control" placeholder="Enter zone">
            </div>
            <div class="col-md-3">
              <label class="form-label">Barangay *</label>
              <select name="barangay" class="form-select" id="barangaySelect" required>
                <option value="" disabled selected>Select Barangay</option>
                <option value="Adag">Adag</option>
                <option value="Agaman">Agaman</option>
                <option value="Agaman Norte">Agaman Norte</option>
                <option value="Agaman Sur">Agaman Sur</option>
                <option value="Alaguia">Alaguia</option>
                <option value="Alba">Alba</option>
                <option value="Annayatan">Annayatan</option>
                <option value="Asassi">Asassi</option>
                <option value="Asinga-Via">Asinga-Via</option>
                <option value="Awallan">Awallan</option>
                <option value="Bacagan">Bacagan</option>
                <option value="Bagunot">Bagunot</option>
                <option value="Barsat East">Barsat East</option>
                <option value="Barsat West">Barsat West</option>
                <option value="Bitag Grande">Bitag Grande</option>
                <option value="Bitag Pequeño">Bitag Pequeño</option>
                <option value="Bungel">Bungel</option>
                <option value="Canagatan">Canagatan</option>
                <option value="Carupian">Carupian</option>
                <option value="Catayauan">Catayauan</option>
                <option value="Dabburab">Dabburab</option>
                <option value="Dalin">Dalin</option>
                <option value="Dallang">Dallang</option>
                <option value="Furagui">Furagui</option>
                <option value="Hacienda Intal">Hacienda Intal</option>
                <option value="Immurung">Immurung</option>
                <option value="Jomlo">Jomlo</option>
                <option value="Mabangguc">Mabangguc</option>
                <option value="Masical">Masical</option>
                <option value="Mission">Mission</option>
                <option value="Mocag">Mocag</option>
                <option value="Nangalinan">Nangalinan</option>
                <option value="Pallagao">Pallagao</option>
                <option value="Paragat">Paragat</option>
                <option value="Piggatan">Piggatan</option>
                <option value="Poblacion">Poblacion</option>
                <option value="Remus">Remus</option>
                <option value="San Antonio">San Antonio</option>
                <option value="San Francisco">San Francisco</option>
                <option value="San Isidro">San Isidro</option>
                <option value="San Jose">San Jose</option>
                <option value="San Vicente">San Vicente</option>
                <option value="Santa Margarita">Santa Margarita</option>
                <option value="Santor">Santor</option>
                <option value="Taguing">Taguing</option>
                <option value="Taguntungan">Taguntungan</option>
                <option value="Tallang">Tallang</option>
                <option value="Taytay">Taytay</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Municipality</label>
              <input type="text" name="municipality" class="form-control" id="municipalityInput" value="Baggao" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Province</label>
              <input type="text" name="province" class="form-control" id="provinceInput" value="Cagayan" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label">License Number *</label>
              <input type="text" name="license_number" class="form-control" placeholder="Enter license number" required>
            </div>
            <div class="col-md-2">
              <label class="form-label d-block">License Type *</label>
              <div class="form-check">
                <input type="radio" class="form-check-input" name="license_type" value="nonProf" id="nonProf" required>
                <label class="form-check-label" for="nonProf">Non-Prof</label>
              </div>
            </div>
            <div class="col-md-2">
              <label class="form-label d-block">&nbsp;</label>
              <div class="form-check">
                <input type="radio" class="form-check-input" name="license_type" value="prof" id="prof" required>
                <label class="form-check-label" for="prof">Prof</label>
              </div>
            </div>
          </div>
        </div>

        <!-- Vehicle Info -->
        <div class="section">
          <h5>Vehicle Information</h5>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Plate / MV File / Engine / Chassis No. *</label>
              <input type="text" name="plate_mv_engine_chassis_no" class="form-control" placeholder="Enter plate or other number" required>
            </div>
            <div class="col-12">
              <label class="form-label">Vehicle Type *</label>
              <div class="d-flex flex-wrap gap-3 mt-1">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="motorcycle" id="motorcycle">
                  <label class="form-check-label" for="motorcycle">Motorcycle</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="tricycle" id="tricycle">
                  <label class="form-check-label" for="tricycle">Tricycle</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="suv" id="suv">
                  <label class="form-check-label" for="suv">SUV</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="van" id="van">
                  <label class="form-check-label" for="van">Van</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="jeep" id="jeep">
                  <label class="form-check-label" for="jeep">Jeep</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="truck" id="truck">
                  <label class="form-check-label" for="truck">Truck</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="kulong" id="kulong">
                  <label class="form-check-label" for="kulong">Kulong Kulong</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="othersVehicle" id="othersVehicle">
                  <label class="form-check-label" for="othersVehicle">Others</label>
                </div>
              </div>
              <input type="text" name="other_vehicle_input" class="form-control" id="otherVehicleInput" placeholder="Specify other vehicle type">
            </div>
            <div class="col-12">
              <label class="form-label">Vehicle Description</label>
              <input type="text" name="vehicle_description" class="form-control" placeholder="Brand, Model, CC, Color, etc.">
            </div>
            <div class="col-12">
              <label class="form-label">Apprehension Date & Time *</label>
              <div class="input-group">
                <input type="datetime-local" name="apprehension_datetime" class="form-control" id="apprehensionDateTime" required>
                <button class="btn btn-outline-secondary btn-custom" type="button" id="toggleDateTime" title="Set/Clear">📅</button>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Place of Apprehension *</label>
              <input type="text" name="place_of_apprehension" class="form-control" placeholder="Enter place of apprehension" required>
            </div>
          </div>
        </div>

        <!-- Violations -->
        <div class="section">
          <h5 class="text-red-600">Violation(s) *</h5>
          <div class="row violation-list">
            <div class="col-md-6">
              <div class="violation-category">
                <h6>Helmet Violations</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noHelmetDriver" id="noHelmetDriver">
                  <label class="form-check-label" for="noHelmetDriver">No Helmet (Driver)</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noHelmetBackrider" id="noHelmetBackrider">
                  <label class="form-check-label" for="noHelmetBackrider">No Helmet (Backrider)</label>
                </div>
              </div>
              <div class="violation-category">
                <h6>License / Registration</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noLicense" id="noLicense">
                  <label class="form-check-label" for="noLicense">No Driver’s License / Minor</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="expiredReg" id="expiredReg">
                  <label class="form-check-label" for="expiredReg">No / Expired Vehicle Registration</label>
                </div>
              </div>
              <div class="violation-category">
                <h6>Vehicle Condition</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="defectiveAccessories" id="defectiveAccessories">
                  <label class="form-check-label" for="defectiveAccessories">No / Defective Parts & Accessories</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noisyMuffler" id="noisyMuffler">
                  <label class="form-check-label" for="noisyMuffler">Noisy Muffler (99db above)</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noMuffler" id="noMuffler">
                  <label class="form-check-label" for="noMuffler">No Muffler Attached</label>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="violation-category">
                <h6>Reckless / Improper Driving</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="recklessDriving" id="recklessDriving">
                  <label class="form-check-label" for="recklessDriving">Reckless / Arrogant Driving</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="dragRacing" id="dragRacing">
                  <label class="form-check-label" for="dragRacing">Drag Racing</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="drunkDriving" id="drunkDriving">
                  <label class="form-check-label" for="drunkDriving">Drunk Driving</label>
                </div>
              </div>
              <div class="violation-category">
                <h6>Traffic Rules</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="disregardingSigns" id="disregardingSigns">
                  <label class="form-check-label" for="disregardingSigns">Disregarding Traffic Sign</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="illegalModification" id="illegalModification">
                  <label class="form-check-label" for="illegalModification">Illegal Modification</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="passengerOnTop" id="passengerOnTop">
                  <label class="form-check-label" for="passengerOnTop">Passenger on Top of the Vehicle</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="illegalParking" id="illegalParking">
                  <label class="form-check-label" for="illegalParking">Illegal Parking</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="roadObstruction" id="roadObstruction">
                  <label class="form-check-label" for="roadObstruction">Road Obstruction</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="blockingPedestrianLane" id="blockingPedestrianLane">
                  <label class="form-check-label" for="blockingPedestrianLane">Blocking Pedestrian Lane</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="loadingUnloadingProhibited" id="loadingUnloadingProhibited">
                  <label class="form-check-label" for="loadingUnloadingProhibited">Loading/Unloading in Prohibited Zone</label>
                </div>
              </div>
              <div class="violation-category">
                <h6>Miscellaneous</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="colorumOperation" id="colorumOperation">
                  <label class="form-check-label" for="colorumOperation">Colorum Operation</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noTrashBin" id="noTrashBin">
                  <label class="form-check-label" for="noTrashBin">No Trashbin</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="drivingInShortSando" id="drivingInShortSando">
                  <label class="form-check-label" for="drivingInShortSando">Driving in Short / Sando</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="overloadedPassenger" id="overloadedPassenger">
                  <label class="form-check-label" for="overloadedPassenger">Overloaded Passenger</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="overUnderCharging" id="overUnderCharging">
                  <label class="form-check-label" for="overUnderCharging">Over Charging / Under Charging</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="refusalToConvey" id="refusalToConvey">
                  <label class="form-check-label" for="refusalToConvey">Refusal to Convey Passenger/s</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noOplanVisaSticker" id="noOplanVisaSticker">
                  <label class="form-check-label" for="noOplanVisaSticker">No Enhanced Oplan Visa Sticker</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="noEovMatchCard" id="noEovMatchCard">
                  <label class="form-check-label" for="noEovMatchCard">Failure to Present E-OV Match Card</label>
                </div>
              </div>
              <div class="violation-category">
                <h6>Other</h6>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="otherViolation" id="otherViolation">
                  <label class="form-check-label" for="otherViolation">Other Violation</label>
                </div>
                <input type="text" name="other_violation_input" class="form-control" id="otherViolationInput" placeholder="Specify other violation">
              </div>
            </div>
            <div class="col-12 mt-3 remarks">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3" placeholder="Enter additional remarks"></textarea>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="footer">
          <p>
            All apprehensions are deemed admitted unless contested by filing a written contest at the Traffic Management Office within five (5) working days from date of issuance.
            Failure to pay the corresponding penalty at the Municipal Treasury Office within fifteen (15) days from date of apprehension, shall be the ground for filing a formal complaint against you.
            Likewise, a copy of this ticket shall be forwarded to concerned agencies for proper action/disposition.
          </p>
        </div>

        <!-- Signatures -->
        <div class="signatures">
          <div class="signature-box">
            <p class="font-medium">Signature of Vehicle Driver</p>
            <div class="signature-line"></div>
          </div>
          <div class="signature-box">
            <p class="font-medium">Name, Rank & Signature of Apprehending Officer</p>
            <div class="signature-line"></div>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-custom mt-4">Submit Citation</button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sidebar = document.getElementById('sidebar');
      const sidebarToggle = document.getElementById('sidebarToggle');
      const content = document.querySelector('.content');

      // Sidebar toggle
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        content.style.marginLeft = sidebar.classList.contains('open') ? '220px' : '0';
      });

      // Auto-populate Municipality and Province when Barangay is selected
      const barangaySelect = document.getElementById('barangaySelect');
      const municipalityInput = document.getElementById('municipalityInput');
      const provinceInput = document.getElementById('provinceInput');

      barangaySelect.addEventListener('change', () => {
        if (barangaySelect.value) {
          municipalityInput.value = 'Baggao';
          provinceInput.value = 'Cagayan';
        } else {
          municipalityInput.value = '';
          provinceInput.value = '';
        }
      });

      // Toggle DateTime button
      const toggleBtn = document.getElementById('toggleDateTime');
      const dateTimeInput = document.getElementById('apprehensionDateTime');
      let isAutoFilled = false;

      toggleBtn.addEventListener('click', () => {
        if (!isAutoFilled) {
          const now = new Date();
          const offset = now.getTimezoneOffset();
          now.setMinutes(now.getMinutes() - offset);
          const formatted = now.toISOString().slice(0, 16);
          dateTimeInput.value = formatted;
          isAutoFilled = true;
          toggleBtn.innerText = '❌';
          toggleBtn.classList.remove('btn-outline-secondary');
          toggleBtn.classList.add('btn-outline-danger');
        } else {
          dateTimeInput.value = '';
          isAutoFilled = false;
          toggleBtn.innerText = '📅';
          toggleBtn.classList.remove('btn-outline-danger');
          toggleBtn.classList.add('btn-outline-secondary');
        }
      });

      // Show/hide Other Violation input
      const otherViolationCheckbox = document.getElementById('otherViolation');
      const otherViolationInput = document.getElementById('otherViolationInput');

      otherViolationCheckbox.addEventListener('change', () => {
        otherViolationInput.style.display = otherViolationCheckbox.checked ? 'block' : 'none';
        otherViolationInput.required = otherViolationCheckbox.checked;
        if (!otherViolationCheckbox.checked) {
          otherViolationInput.value = '';
        }
      });

      // Show/hide Other Vehicle Type input
      const otherVehicleCheckbox = document.getElementById('othersVehicle');
      const otherVehicleInput = document.getElementById('otherVehicleInput');

      otherVehicleCheckbox.addEventListener('change', () => {
        otherVehicleInput.style.display = otherVehicleCheckbox.checked ? 'block' : 'none';
        otherVehicleInput.required = otherVehicleCheckbox.checked;
        if (!otherVehicleCheckbox.checked) {
          otherVehicleInput.value = '';
        }
      });

      // Ensure only one license type is selected
      const nonProfCheckbox = document.getElementById('nonProf');
      const profCheckbox = document.getElementById('prof');

      nonProfCheckbox.addEventListener('change', () => {
        if (nonProfCheckbox.checked) profCheckbox.checked = false;
      });

      profCheckbox.addEventListener('change', () => {
        if (profCheckbox.checked) nonProfCheckbox.checked = false;
      });

      // Validate at least one vehicle type and violation is selected
      const vehicleCheckboxes = document.querySelectorAll('input[name="motorcycle"], input[name="tricycle"], input[name="suv"], input[name="van"], input[name="jeep"], input[name="truck"], input[name="kulong"], input[name="othersVehicle"]');
      const violationCheckboxes = document.querySelectorAll('input[name="noHelmetDriver"], input[name="noHelmetBackrider"], input[name="noLicense"], input[name="expiredReg"], input[name="defectiveAccessories"], input[name="noisyMuffler"], input[name="noMuffler"], input[name="recklessDriving"], input[name="dragRacing"], input[name="drunkDriving"], input[name="disregardingSigns"], input[name="illegalModification"], input[name="passengerOnTop"], input[name="illegalParking"], input[name="roadObstruction"], input[name="blockingPedestrianLane"], input[name="loadingUnloadingProhibited"], input[name="colorumOperation"], input[name="noTrashBin"], input[name="drivingInShortSando"], input[name="overloadedPassenger"], input[name="overUnderCharging"], input[name="refusalToConvey"], input[name="noOplanVisaSticker"], input[name="noEovMatchCard"], input[name="otherViolation"]');

      // Handle form submission with AJAX
      document.getElementById('citationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate vehicle type
        let vehicleSelected = false;
        vehicleCheckboxes.forEach(checkbox => {
          if (checkbox.checked) vehicleSelected = true;
        });

        if (!vehicleSelected) {
          alert('Please select at least one vehicle type.');
          return;
        }

        // Validate violation
        let violationSelected = false;
        violationCheckboxes.forEach(checkbox => {
          if (checkbox.checked) violationSelected = true;
        });

        if (!violationSelected) {
          alert('Please select at least one violation.');
          return;
        }

        const formData = new FormData(this);
        fetch('insert_citation.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.status === 'success') {
            document.getElementById('citationForm').reset();
            municipalityInput.value = 'Baggao';
            provinceInput.value = 'Cagayan';
            otherViolationInput.style.display = 'none';
            otherVehicleInput.style.display = 'none';
            otherViolationInput.required = false;
            otherVehicleInput.required = false;
            isAutoFilled = false;
            toggleBtn.innerText = '📅';
            toggleBtn.classList.remove('btn-outline-danger');
            toggleBtn.classList.add('btn-outline-secondary');
            window.location.reload(); // Refresh to get new ticket number
          }
        })
        .catch(error => {
          alert('Error submitting form: ' + error);
        });
      });

      // Real-time form validation
      const requiredInputs = document.querySelectorAll('input[required], select[required]');
      requiredInputs.forEach(input => {
        input.addEventListener('input', () => {
          if (input.value.trim() === '') {
            input.classList.add('is-invalid');
          } else {
            input.classList.remove('is-invalid');
          }
        });
      });
    });
  </script>
</body>
</html>