<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Traffic Citation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f1f3f5;
      font-family: 'Inter', sans-serif;
    }
    .ticket-container {
      max-width: 1000px;
      background-color: white;
      margin: 40px auto;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    .header {
      background: linear-gradient(90deg, rgb(8, 6, 119), rgb(11, 23, 185));
      color: white;
      padding: 24px;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 30px;
    }
    .section {
      background-color: #f8fafc;
      padding: 24px;
      border-radius: 10px;
      margin-bottom: 30px;
      border: 1px solid #e2e8f0;
    }
    .section h5 {
      font-weight: 700;
      margin-bottom: 20px;
      color: #1f2937;
    }
    .form-label {
      font-weight: 500;
      color: #374151;
    }
    .violation-category {
      margin-bottom: 1.5rem;
    }
    .violation-category h6 {
      font-weight: 600;
      margin-bottom: 12px;
      color: #2563eb;
    }
    .violation-list .form-check {
      margin-bottom: 0.5rem;
    }
    .remarks textarea {
      resize: none;
      border-color: #d1d5db;
    }
    .btn-custom {
      transition: background-color 0.2s ease;
    }
    .btn-custom:hover {
      background-color: #2563eb;
      color: white;
    }
    #otherViolationInput, #otherVehicleInput {
      display: none;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <?php
  session_start();
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "traffic_citation_db";

  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $citation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = $conn->prepare("
      SELECT c.ticket_number, c.apprehension_datetime, c.place_of_apprehension,
             d.last_name, d.first_name, d.middle_initial, d.suffix, d.zone, d.barangay,
             d.municipality, d.province, d.license_number, d.license_type,
             v.plate_mv_engine_chassis_no, v.vehicle_type, v.vehicle_description,
             GROUP_CONCAT(vl.violation_type SEPARATOR ',') AS violations,
             r.remark_text
      FROM citations c
      JOIN drivers d ON c.driver_id = d.driver_id
      JOIN vehicles v ON c.vehicle_id = v.vehicle_id
      LEFT JOIN violations vl ON c.citation_id = vl.citation_id
      LEFT JOIN remarks r ON c.citation_id = r.citation_id
      WHERE c.citation_id = :citation_id
      GROUP BY c.citation_id
    ");
    $stmt->execute(['citation_id' => $citation_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
      echo "<div class='container'><p>Citation not found.</p></div>";
      exit;
    }

    // Handle vehicle type as a single value, convert to lowercase for comparison
    $vehicle_type = strtolower($data['vehicle_type']);
    $vehicle_types = [$vehicle_type];
    $violations = explode(',', $data['violations'] ?? '');
    $apprehension_datetime = $data['apprehension_datetime'] == '0000-00-00 00:00:00' ? '' : date('Y-m-d\TH:i', strtotime($data['apprehension_datetime']));
  } catch(PDOException $e) {
    echo "<div class='container'><p>Error: " . $e->getMessage() . "</p></div>";
    exit;
  }
  ?>

  <form id="editCitationForm" action="update_citation.php" method="POST">
    <div class="ticket-container">
      <input type="hidden" name="citation_id" value="<?php echo $citation_id; ?>">
      <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($data['ticket_number']); ?>">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <div class="header">
        <h4 class="font-bold text-lg">REPUBLIC OF THE PHILIPPINES</h4>
        <h4 class="font-bold text-lg">PROVINCE OF CAGAYAN • MUNICIPALITY OF BAGGAO</h4>
        <h1 class="font-extrabold text-3xl">EDIT TRAFFIC CITATION TICKET</h1>
      </div>

      <!-- Driver Info -->
      <div class="section">
        <h5>Driver Information</h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($data['last_name']); ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($data['first_name']); ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">M.I.</label>
            <input type="text" name="middle_initial" class="form-control" value="<?php echo htmlspecialchars($data['middle_initial']); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Suffix</label>
            <input type="text" name="suffix" class="form-control" value="<?php echo htmlspecialchars($data['suffix']); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Zone</label>
            <input type="text" name="zone" class="form-control" value="<?php echo htmlspecialchars($data['zone']); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Barangay</label>
            <select name="barangay" class="form-select" id="barangaySelect" required>
              <option value="" <?php echo empty($data['barangay']) ? 'selected' : ''; ?>>Select Barangay</option>
              <option value="Adag" <?php echo $data['barangay'] == 'Adag' ? 'selected' : ''; ?>>Adag</option>
              <option value="Agaman" <?php echo $data['barangay'] == 'Agaman' ? 'selected' : ''; ?>>Agaman</option>
              <option value="Agaman Norte" <?php echo $data['barangay'] == 'Agaman Norte' ? 'selected' : ''; ?>>Agaman Norte</option>
              <option value="Agaman Sur" <?php echo $data['barangay'] == 'Agaman Sur' ? 'selected' : ''; ?>>Agaman Sur</option>
              <option value="Alaguia" <?php echo $data['barangay'] == 'Alaguia' ? 'selected' : ''; ?>>Alaguia</option>
              <option value="Alba" <?php echo $data['barangay'] == 'Alba' ? 'selected' : ''; ?>>Alba</option>
              <option value="Annayatan" <?php echo $data['barangay'] == 'Annayatan' ? 'selected' : ''; ?>>Annayatan</option>
              <option value="Asassi" <?php echo $data['barangay'] == 'Asassi' ? 'selected' : ''; ?>>Asassi</option>
              <option value="Asinga-Via" <?php echo $data['barangay'] == 'Asinga-Via' ? 'selected' : ''; ?>>Asinga-Via</option>
              <option value="Awallan" <?php echo $data['barangay'] == 'Awallan' ? 'selected' : ''; ?>>Awallan</option>
              <option value="Bacagan" <?php echo $data['barangay'] == 'Bacagan' ? 'selected' : ''; ?>>Bacagan</option>
              <option value="Bagunot" <?php echo $data['barangay'] == 'Bagunot' ? 'selected' : ''; ?>>Bagunot</option>
              <option value="Barsat East" <?php echo $data['barangay'] == 'Barsat East' ? 'selected' : ''; ?>>Barsat East</option>
              <option value="Barsat West" <?php echo $data['barangay'] == 'Barsat West' ? 'selected' : ''; ?>>Barsat West</option>
              <option value="Bitag Grande" <?php echo $data['barangay'] == 'Bitag Grande' ? 'selected' : ''; ?>>Bitag Grande</option>
              <option value="Bitag Pequeño" <?php echo $data['barangay'] == 'Bitag Pequeño' ? 'selected' : ''; ?>>Bitag Pequeño</option>
              <option value="Bungel" <?php echo $data['barangay'] == 'Bungel' ? 'selected' : ''; ?>>Bungel</option>
              <option value="Canagatan" <?php echo $data['barangay'] == 'Canagatan' ? 'selected' : ''; ?>>Canagatan</option>
              <option value="Carupian" <?php echo $data['barangay'] == 'Carupian' ? 'selected' : ''; ?>>Carupian</option>
              <option value="Catayauan" <?php echo $data['barangay'] == 'Catayauan' ? 'selected' : ''; ?>>Catayauan</option>
              <option value="Dabburab" <?php echo $data['barangay'] == 'Dabburab' ? 'selected' : ''; ?>>Dabburab</option>
              <option value="Dalin" <?php echo $data['barangay'] == 'Dalin' ? 'selected' : ''; ?>>Dalin</option>
              <option value="Dallang" <?php echo $data['barangay'] == 'Dallang' ? 'selected' : ''; ?>>Dallang</option>
              <option value="Furagui" <?php echo $data['barangay'] == 'Furagui' ? 'selected' : ''; ?>>Furagui</option>
              <option value="Hacienda Intal" <?php echo $data['barangay'] == 'Hacienda Intal' ? 'selected' : ''; ?>>Hacienda Intal</option>
              <option value="Immurung" <?php echo $data['barangay'] == 'Immurung' ? 'selected' : ''; ?>>Immurung</option>
              <option value="Jomlo" <?php echo $data['barangay'] == 'Jomlo' ? 'selected' : ''; ?>>Jomlo</option>
              <option value="Mabangguc" <?php echo $data['barangay'] == 'Mabangguc' ? 'selected' : ''; ?>>Mabangguc</option>
              <option value="Masical" <?php echo $data['barangay'] == 'Masical' ? 'selected' : ''; ?>>Masical</option>
              <option value="Mission" <?php echo $data['barangay'] == 'Mission' ? 'selected' : ''; ?>>Mission</option>
              <option value="Mocag" <?php echo $data['barangay'] == 'Mocag' ? 'selected' : ''; ?>>Mocag</option>
              <option value="Nangalinan" <?php echo $data['barangay'] == 'Nangalinan' ? 'selected' : ''; ?>>Nangalinan</option>
              <option value="Pallagao" <?php echo $data['barangay'] == 'Pallagao' ? 'selected' : ''; ?>>Pallagao</option>
              <option value="Paragat" <?php echo $data['barangay'] == 'Paragat' ? 'selected' : ''; ?>>Paragat</option>
              <option value="Piggatan" <?php echo $data['barangay'] == 'Piggatan' ? 'selected' : ''; ?>>Piggatan</option>
              <option value="Poblacion" <?php echo $data['barangay'] == 'Poblacion' ? 'selected' : ''; ?>>Poblacion</option>
              <option value="Remus" <?php echo $data['barangay'] == 'Remus' ? 'selected' : ''; ?>>Remus</option>
              <option value="San Antonio" <?php echo $data['barangay'] == 'San Antonio' ? 'selected' : ''; ?>>San Antonio</option>
              <option value="San Francisco" <?php echo $data['barangay'] == 'San Francisco' ? 'selected' : ''; ?>>San Francisco</option>
              <option value="San Isidro" <?php echo $data['barangay'] == 'San Isidro' ? 'selected' : ''; ?>>San Isidro</option>
              <option value="San Jose" <?php echo $data['barangay'] == 'San Jose' ? 'selected' : ''; ?>>San Jose</option>
              <option value="San Vicente" <?php echo $data['barangay'] == 'San Vicente' ? 'selected' : ''; ?>>San Vicente</option>
              <option value="Santa Margarita" <?php echo $data['barangay'] == 'Santa Margarita' ? 'selected' : ''; ?>>Santa Margarita</option>
              <option value="Santor" <?php echo $data['barangay'] == 'Santor' ? 'selected' : ''; ?>>Santor</option>
              <option value="Taguing" <?php echo $data['barangay'] == 'Taguing' ? 'selected' : ''; ?>>Taguing</option>
              <option value="Taguntungan" <?php echo $data['barangay'] == 'Taguntungan' ? 'selected' : ''; ?>>Taguntungan</option>
              <option value="Tallang" <?php echo $data['barangay'] == 'Tallang' ? 'selected' : ''; ?>>Tallang</option>
              <option value="Taytay" <?php echo $data['barangay'] == 'Taytay' ? 'selected' : ''; ?>>Taytay</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Municipality</label>
            <input type="text" name="municipality" class="form-control" id="municipalityInput" value="<?php echo htmlspecialchars($data['municipality']); ?>" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label">Province</label>
            <input type="text" name="province" class="form-control" id="provinceInput" value="<?php echo htmlspecialchars($data['province']); ?>" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">License Number</label>
            <input type="text" name="license_number" class="form-control" value="<?php echo htmlspecialchars($data['license_number']); ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label d-block">License Type</label>
            <div class="form-check">
              <input type="radio" class="form-check-input" name="license_type" value="nonProf" id="nonProf" <?php echo $data['license_type'] == 'Non-Professional' ? 'checked' : ''; ?> required>
              <label class="form-check-label" for="nonProf">Non-Prof</label>
            </div>
          </div>
          <div class="col-md-2">
            <label class="form-label d-block"> </label>
            <div class="form-check">
              <input type="radio" class="form-check-input" name="license_type" value="prof" id="prof" <?php echo $data['license_type'] == 'Professional' ? 'checked' : ''; ?>>
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
            <label class="form-label">Plate / MV File / Engine / Chassis No.</label>
            <input type="text" name="plate_mv_engine_chassis_no" class="form-control" value="<?php echo htmlspecialchars($data['plate_mv_engine_chassis_no']); ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Vehicle Type</label>
            <div class="d-flex flex-wrap gap-3 mt-1">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="motorcycle" id="motorcycle" <?php echo in_array('motorcycle', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="motorcycle">Motorcycle</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="tricycle" id="tricycle" <?php echo in_array('tricycle', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="tricycle">Tricycle</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="suv" id="suv" <?php echo in_array('suv', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="suv">SUV</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="van" id="van" <?php echo in_array('van', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="van">Van</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="jeep" id="jeep" <?php echo in_array('jeep', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="jeep">Jeep</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="truck" id="truck" <?php echo in_array('truck', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="truck">Truck</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="kulong" id="kulong" <?php echo in_array('kulong', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="kulong">Kulong Kulong</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="othersVehicle" id="othersVehicle" <?php echo !in_array('motorcycle', $vehicle_types) && !in_array('tricycle', $vehicle_types) && !in_array('suv', $vehicle_types) && !in_array('van', $vehicle_types) && !in_array('jeep', $vehicle_types) && !in_array('truck', $vehicle_types) && !in_array('kulong', $vehicle_types) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="othersVehicle">Others</label>
              </div>
            </div>
            <input type="text" name="other_vehicle_input" class="form-control" id="otherVehicleInput" value="<?php echo !in_array('motorcycle', $vehicle_types) && !in_array('tricycle', $vehicle_types) && !in_array('suv', $vehicle_types) && !in_array('van', $vehicle_types) && !in_array('jeep', $vehicle_types) && !in_array('truck', $vehicle_types) && !in_array('kulong', $vehicle_types) ? htmlspecialchars($data['vehicle_type']) : ''; ?>" placeholder="Specify other vehicle type">
          </div>
          <div class="col-12">
            <label class="form-label">Vehicle Description</label>
            <input type="text" name="vehicle_description" class="form-control" value="<?php echo htmlspecialchars($data['vehicle_description']); ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Apprehension Date & Time</label>
            <div class="input-group">
              <input type="datetime-local" name="apprehension_datetime" class="form-control" id="apprehensionDateTime" value="<?php echo htmlspecialchars($apprehension_datetime); ?>">
              <button class="btn btn-outline-secondary btn-custom" type="button" id="toggleDateTime" title="Set/Clear">📅</button>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Place of Apprehension</label>
            <input type="text" name="place_of_apprehension" class="form-control" value="<?php echo htmlspecialchars($data['place_of_apprehension']); ?>">
          </div>
        </div>
      </div>

      <!-- Violations -->
      <div class="section">
        <h5 class="text-red-600">Violation(s)</h5>
        <div class="row violation-list">
          <div class="col-md-6">
            <div class="violation-category">
              <h6>Helmet Violations</h6>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="noHelmetDriver" id="noHelmetDriver" <?php echo in_array('No Helmet (Driver)', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="noHelmetDriver">No Helmet (Driver)</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="noHelmetBackrider" id="noHelmetBackrider" <?php echo in_array('No Helmet (Backrider)', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="noHelmetBackrider">No Helmet (Backrider)</label>
              </div>
            </div>
            <div class="violation-category">
              <h6>License / Registration</h6>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="noLicense" id="noLicense" <?php echo in_array('No Driver’s License / Minor', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="noLicense">No Driver’s License / Minor</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="expiredReg" id="expiredReg" <?php echo in_array('Expired Registration', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="expiredReg">Expired Registration</label>
              </div>
            </div>
            <div class="violation-category">
              <h6>Vehicle Condition</h6>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="defectiveAccessories" id="defectiveAccessories" <?php echo in_array('Defective Accessories', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="defectiveAccessories">Defective Accessories</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="loudMuffler" id="loudMuffler" <?php echo in_array('Loud Muffler', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="loudMuffler">Loud Muffler</label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="violation-category">
              <h6>Reckless / Improper Driving</h6>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="recklessDriving" id="recklessDriving" <?php echo in_array('Reckless Driving', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="recklessDriving">Reckless Driving</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="dragRacing" id="dragRacing" <?php echo in_array('Drag Racing', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="dragRacing">Drag Racing</label>
              </div>
            </div>
            <div class="violation-category">
              <h6>Traffic Rules</h6>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="disregardingSigns" id="disregardingSigns" <?php echo in_array('Disregarding Signs', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="disregardingSigns">Disregarding Signs</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="illegalParking" id="illegalParking" <?php echo in_array('Illegal Parking', $violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="illegalParking">Illegal Parking</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="otherViolation" id="otherViolation" <?php echo !in_array('No Helmet (Driver)', $violations) && !in_array('No Helmet (Backrider)', $violations) && !in_array('No Driver’s License / Minor', $violations) && !in_array('Expired Registration', $violations) && !in_array('Defective Accessories', $violations) && !in_array('Loud Muffler', $violations) && !in_array('Reckless Driving', $violations) && !in_array('Drag Racing', $violations) && !in_array('Disregarding Signs', $violations) && !in_array('Illegal Parking', $violations) && !empty($violations) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="otherViolation">Other Violation</label>
              </div>
              <input type="text" name="other_violation_input" class="form-control" id="otherViolationInput" value="<?php echo !in_array('No Helmet (Driver)', $violations) && !in_array('No Helmet (Backrider)', $violations) && !in_array('No Driver’s License / Minor', $violations) && !in_array('Expired Registration', $violations) && !in_array('Defective Accessories', $violations) && !in_array('Loud Muffler', $violations) && !in_array('Reckless Driving', $violations) && !in_array('Drag Racing', $violations) && !in_array('Disregarding Signs', $violations) && !in_array('Illegal Parking', $violations) ? htmlspecialchars($violations[0]) : ''; ?>" placeholder="Specify other violation">
            </div>
          </div>
          <div class="col-12 mt-3 remarks">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3"><?php echo htmlspecialchars($data['remark_text']); ?></textarea>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-custom mt-4">Update Citation</button>
      <a href="records.php" class="btn btn-secondary btn-custom mt-4">Cancel</a>
    </div>
  </form>

  <script>
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
    otherViolationInput.style.display = otherViolationCheckbox.checked ? 'block' : 'none';

    otherViolationCheckbox.addEventListener('change', () => {
      otherViolationInput.style.display = otherViolationCheckbox.checked ? 'block' : 'none';
    });

    // Show/hide Other Vehicle Type input
    const otherVehicleCheckbox = document.getElementById('othersVehicle');
    const otherVehicleInput = document.getElementById('otherVehicleInput');
    otherVehicleInput.style.display = otherVehicleCheckbox.checked ? 'block' : 'none';

    otherVehicleCheckbox.addEventListener('change', () => {
      otherVehicleInput.style.display = otherVehicleCheckbox.checked ? 'block' : 'none';
    });

    // Ensure only one license type is selected
    const nonProfCheckbox = document.getElementById('nonProf');
    const profCheckbox = document.getElementById('prof');

    nonProfCheckbox.addEventListener('change', () => {
      if (nonProfCheckbox.checked) {
        profCheckbox.checked = false;
      }
    });

    profCheckbox.addEventListener('change', () => {
      if (profCheckbox.checked) {
        nonProfCheckbox.checked = false;
      }
    });

    // Handle form submission with AJAX and validation
    document.getElementById('editCitationForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const requiredFields = ['last_name', 'first_name', 'license_number', 'barangay'];
      for (const field of requiredFields) {
        if (!this[field].value.trim()) {
          alert(`${field.replace('_', ' ')} is required.`);
          return;
        }
      }

      const vehicleTypes = ['motorcycle', 'tricycle', 'suv', 'van', 'jeep', 'truck', 'kulong', 'othersVehicle'];
      const isVehicleSelected = vehicleTypes.some(type => document.getElementById(type).checked);
      if (!isVehicleSelected) {
        alert('Please select at least one vehicle type.');
        return;
      }

      const violationCheckboxes = ['noHelmetDriver', 'noHelmetBackrider', 'noLicense', 'expiredReg', 'defectiveAccessories', 'loudMuffler', 'recklessDriving', 'dragRacing', 'disregardingSigns', 'illegalParking', 'otherViolation'];
      const isViolationSelected = violationCheckboxes.some(type => document.getElementById(type).checked);
      if (!isViolationSelected) {
        alert('Please select at least one violation.');
        return;
      }

      const formData = new FormData(this);
      document.getElementById('editCitationForm').classList.add('opacity-50');
      fetch('update_citation.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        document.getElementById('editCitationForm').classList.remove('opacity-50');
        alert(data.message);
        if (data.status === 'success') {
          window.location.href = 'records.php';
        }
      })
      .catch(error => {
        document.getElementById('editCitationForm').classList.remove('opacity-50');
        alert('Error: ' + error);
      });
    });
  </script>
</body>
</html>