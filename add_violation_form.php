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
    $max_ticket = $row['max_ticket'] ? (int)$row['max_ticket'] : 6100;
    $next_ticket = sprintf("%05d", $max_ticket + 1);

    // Pre-fill driver info if driver_id is provided
    $driver_data = [];
    $offense_counts = [];
    if (isset($_GET['driver_id'])) {
        $driver_id = (int)$_GET['driver_id'];
        $stmt = $conn->prepare("SELECT * FROM drivers WHERE driver_id = :driver_id");
        $stmt->execute([':driver_id' => $driver_id]);
        $driver_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch offense counts for this driver
        $stmt = $conn->prepare("
            SELECT violation_type, MAX(offense_count) AS offense_count
            FROM violations
            WHERE driver_id = :driver_id
            GROUP BY violation_type
        ");
        $stmt->execute([':driver_id' => $driver_id]);
        $offense_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
} catch (PDOException $e) {
    $next_ticket = "06101";
    $driver_data = [];
}
$conn = null;

// Map violation keys to display names
$violation_checkboxes = [
    'noHelmetDriver' => 'No Helmet (Driver)',
    'noHelmetBackrider' => 'No Helmet (Backrider)',
    'noLicense' => 'No Driver’s License / Minor',
    'expiredReg' => 'No / Expired Vehicle Registration',
    'defectiveAccessories' => 'No / Defective Parts & Accessories',
    'recklessDriving' => 'Reckless / Arrogant Driving',
    'disregardingSigns' => 'Disregarding Traffic Sign',
    'illegalModification' => 'Illegal Modification',
    'passengerOnTop' => 'Passenger on Top of the Vehicle',
    'noisyMuffler' => 'Noisy Muffler (98db above)',
    'noMuffler' => 'No Muffler Attached',
    'illegalParking' => 'Illegal Parking',
    'roadObstruction' => 'Road Obstruction',
    'blockingPedestrianLane' => 'Blocking Pedestrian Lane',
    'loadingUnloadingProhibited' => 'Loading/Unloading in Prohibited Zone',
    'doubleParking' => 'Double Parking',
    'drunkDriving' => 'Drunk Driving',
    'colorumOperation' => 'Colorum Operation',
    'noTrashBin' => 'No Trashbin',
    'drivingInShortSando' => 'Driving in Short / Sando',
    'overloadedPassenger' => 'Overloaded Passenger',
    'overUnderCharging' => 'Over Charging / Under Charging',
    'refusalToConvey' => 'Refusal to Convey Passenger/s',
    'dragRacing' => 'Drag Racing',
    'noOplanVisaSticker' => 'No Enhanced Oplan Visa Sticker',
    'noEovMatchCard' => 'Failure to Present E-OV Match Card',
    'otherViolation' => !empty($_POST['other_violation_input']) ? sanitize($_POST['other_violation_input']) : null
];

// Adjust offense counts for display
$violation_offenses = [];
foreach ($violation_checkboxes as $key => $value) {
    $offense_count = isset($offense_counts[$value]) ? (int)$offense_counts[$value] + 1 : 1;
    $violation_offenses[$key] = [
        'name' => $value,
        'offense_count' => $offense_count,
        'label' => $value . ($offense_count > 1 ? " - {$offense_count}" . ($offense_count == 2 ? "nd" : ($offense_count == 3 ? "rd" : "th")) . " Offense" : "")
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Add Violation Form</title>
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
        .ticket-number {
            position: absolute;
            top: 20px;
            right: 50px;
            font-weight: 600;
            background: #fff3cd;
            padding: 10px 20px;
            border: 2px solid #f97316;
            border-radius: 8px;
            font-size: 1.2rem;
            color: #1f2937;
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
        .footer {
            font-size: 0.85rem;
            color: #6b7280;
            padding-top: 20px;
            border-top: 1px dashed #d1d5db;
            text-align: justify;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .signature-box {
            flex: 0 0 48%;
        }
        .signature-line {
            border-top: 2px solid #1f2937;
            margin-top: 50px;
        }
        .form-select {
            border-color: #d1d5db;
            transition: border-color 0.2s ease;
        }
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
        @media print {
            .ticket-number, .btn-custom {
                display: none;
            }
            .ticket-container {
                box-shadow: none;
                border: none;
                margin: 0;
            }
        }
        @media (max-width: 576px) {
            .ticket-number {
                position: static;
                margin-bottom: 20px;
                text-align: center;
                display: block;
            }
        }
    </style>
</head>
<body>
    <form id="citationForm" action="insert_citation.php" method="POST">
        <div class="ticket-container position-relative">
            <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($next_ticket); ?>">
            <input type="hidden" name="driver_id" value="<?php echo htmlspecialchars($driver_data['driver_id'] ?? ''); ?>">
            <div class="ticket-number"><?php echo htmlspecialchars($next_ticket); ?></div>
            <a href="driver_records.php" class="btn btn-secondary btn-custom" style="position: absolute; top: 20px; left: 20px;">View Driver Records</a>

            <div class="header">
                <h4 class="font-bold text-lg">REPUBLIC OF THE PHILIPPINES</h4>
                <h4 class="font-bold text-lg">PROVINCE OF CAGAYAN • MUNICIPALITY OF BAGGAO</h4>
                <h1 class="font-extrabold text-3xl">ADD VIOLATION FORM</h1>
            </div>

            <!-- Driver Info -->
            <div class="section">
                <h5>Driver Information</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($driver_data['last_name'] ?? ''); ?>" placeholder="Enter last name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($driver_data['first_name'] ?? ''); ?>" placeholder="Enter first name">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">M.I.</label>
                        <input type="text" name="middle_initial" class="form-control" value="<?php echo htmlspecialchars($driver_data['middle_initial'] ?? ''); ?>" placeholder="M.I.">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" class="form-control" value="<?php echo htmlspecialchars($driver_data['suffix'] ?? ''); ?>" placeholder="e.g., Jr.">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Zone</label>
                        <input type="text" name="zone" class="form-control" value="<?php echo htmlspecialchars($driver_data['zone'] ?? ''); ?>" placeholder="Enter zone">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Barangay</label>
                        <select name="barangay" class="form-select" id="barangaySelect">
                            <option value="" disabled <?php echo (!isset($driver_data['barangay']) || $driver_data['barangay'] == '') ? 'selected' : ''; ?>>Select Barangay</option>
                            <option value="Adag" <?php echo (isset($driver_data['barangay']) && $driver_data['barangay'] == 'Adag') ? 'selected' : ''; ?>>Adag</option>
                            <option value="Agaman" <?php echo (isset($driver_data['barangay']) && $driver_data['barangay'] == 'Agaman') ? 'selected' : ''; ?>>Agaman</option>
                            <option value="Taytay" <?php echo (isset($driver_data['barangay']) && $driver_data['barangay'] == 'Taytay') ? 'selected' : ''; ?>>Taytay</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Municipality</label>
                        <input type="text" name="municipality" class="form-control" id="municipalityInput" value="<?php echo htmlspecialchars($driver_data['municipality'] ?? 'Baggao'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Province</label>
                        <input type="text" name="province" class="form-control" id="provinceInput" value="<?php echo htmlspecialchars($driver_data['province'] ?? 'Cagayan'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-control" value="<?php echo htmlspecialchars($driver_data['license_number'] ?? ''); ?>" placeholder="Enter license number">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">License Type</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="license_type" value="nonProf" id="nonProf" <?php echo (!isset($driver_data['license_type']) || $driver_data['license_type'] == 'Non-Professional') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="nonProf">Non-Prof</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block"> </label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="license_type" value="prof" id="prof" <?php echo (isset($driver_data['license_type']) && $driver_data['license_type'] == 'Professional') ? 'checked' : ''; ?>>
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
                        <input type="text" name="plate_mv_engine_chassis_no" class="form-control" placeholder="Enter plate or other number">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Vehicle Type</label>
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
                                <label class="form-check-label" for="kulong">Kulong</label>
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
                        <label class="form-label">Apprehension Date & Time</label>
                        <div class="input-group">
                            <input type="datetime-local" name="apprehension_datetime" class="form-control" id="apprehensionDateTime">
                            <button class="btn btn-outline-secondary btn-custom" type="button" id="toggleDateTime" title="Set/Clear">📅</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Place of Apprehension</label>
                        <input type="text" name="place_of_apprehension" class="form-control" placeholder="Enter place of apprehension">
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
                    <input type="checkbox" class="form-check-input" name="noHelmetDriver" id="noHelmetDriver">
                    <label class="form-check-label" for="noHelmetDriver"><?php echo htmlspecialchars($violation_offenses['noHelmetDriver']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noHelmetBackrider" id="noHelmetBackrider">
                    <label class="form-check-label" for="noHelmetBackrider"><?php echo htmlspecialchars($violation_offenses['noHelmetBackrider']['label']); ?></label>
                </div>
            </div>
            <div class="violation-category">
                <h6>License & Registration</h6>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noLicense" id="noLicense">
                    <label class="form-check-label" for="noLicense"><?php echo htmlspecialchars($violation_offenses['noLicense']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="expiredReg" id="expiredReg">
                    <label class="form-check-label" for="expiredReg"><?php echo htmlspecialchars($violation_offenses['expiredReg']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noOplanVisaSticker" id="noOplanVisaSticker">
                    <label class="form-check-label" for="noOplanVisaSticker"><?php echo htmlspecialchars($violation_offenses['noOplanVisaSticker']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noEovMatchCard" id="noEovMatchCard">
                    <label class="form-check-label" for="noEovMatchCard"><?php echo htmlspecialchars($violation_offenses['noEovMatchCard']['label']); ?></label>
                </div>
            </div>
            <div class="violation-category">
                <h6>Vehicle Condition</h6>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="defectiveAccessories" id="defectiveAccessories">
                    <label class="form-check-label" for="defectiveAccessories"><?php echo htmlspecialchars($violation_offenses['defectiveAccessories']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noisyMuffler" id="noisyMuffler">
                    <label class="form-check-label" for="noisyMuffler"><?php echo htmlspecialchars($violation_offenses['noisyMuffler']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noMuffler" id="noMuffler">
                    <label class="form-check-label" for="noMuffler"><?php echo htmlspecialchars($violation_offenses['noMuffler']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="illegalModification" id="illegalModification">
                    <label class="form-check-label" for="illegalModification"><?php echo htmlspecialchars($violation_offenses['illegalModification']['label']); ?></label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="violation-category">
                <h6>Driving Behavior</h6>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="recklessDriving" id="recklessDriving">
                    <label class="form-check-label" for="recklessDriving"><?php echo htmlspecialchars($violation_offenses['recklessDriving']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="dragRacing" id="dragRacing">
                    <label class="form-check-label" for="dragRacing"><?php echo htmlspecialchars($violation_offenses['dragRacing']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="disregardingSigns" id="disregardingSigns">
                    <label class="form-check-label" for="disregardingSigns"><?php echo htmlspecialchars($violation_offenses['disregardingSigns']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="drunkDriving" id="drunkDriving">
                    <label class="form-check-label" for="drunkDriving"><?php echo htmlspecialchars($violation_offenses['drunkDriving']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="drivingInShortSando" id="drivingInShortSando">
                    <label class="form-check-label" for="drivingInShortSando"><?php echo htmlspecialchars($violation_offenses['drivingInShortSando']['label']); ?></label>
                </div>
            </div>
            <div class="violation-category">
                <h6>Traffic Violations</h6>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="illegalParking" id="illegalParking">
                    <label class="form-check-label" for="illegalParking"><?php echo htmlspecialchars($violation_offenses['illegalParking']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="roadObstruction" id="roadObstruction">
                    <label class="form-check-label" for="roadObstruction"><?php echo htmlspecialchars($violation_offenses['roadObstruction']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="blockingPedestrianLane" id="blockingPedestrianLane">
                    <label class="form-check-label" for="blockingPedestrianLane"><?php echo htmlspecialchars($violation_offenses['blockingPedestrianLane']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="loadingUnloadingProhibited" id="loadingUnloadingProhibited">
                    <label class="form-check-label" for="loadingUnloadingProhibited"><?php echo htmlspecialchars($violation_offenses['loadingUnloadingProhibited']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="doubleParking" id="doubleParking">
                    <label class="form-check-label" for="doubleParking"><?php echo htmlspecialchars($violation_offenses['doubleParking']['label']); ?></label>
                </div>
            </div>
            <div class="violation-category">
                <h6>Passenger & Operator Violations</h6>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="passengerOnTop" id="passengerOnTop">
                    <label class="form-check-label" for="passengerOnTop"><?php echo htmlspecialchars($violation_offenses['passengerOnTop']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="colorumOperation" id="colorumOperation">
                    <label class="form-check-label" for="colorumOperation"><?php echo htmlspecialchars($violation_offenses['colorumOperation']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="noTrashBin" id="noTrashBin">
                    <label class="form-check-label" for="noTrashBin"><?php echo htmlspecialchars($violation_offenses['noTrashBin']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="overloadedPassenger" id="overloadedPassenger">
                    <label class="form-check-label" for="overloadedPassenger"><?php echo htmlspecialchars($violation_offenses['overloadedPassenger']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="overUnderCharging" id="overUnderCharging">
                    <label class="form-check-label" for="overUnderCharging"><?php echo htmlspecialchars($violation_offenses['overUnderCharging']['label']); ?></label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="refusalToConvey" id="refusalToConvey">
                    <label class="form-check-label" for="refusalToConvey"><?php echo htmlspecialchars($violation_offenses['refusalToConvey']['label']); ?></label>
                </div>
            </div>
            <div class="violation-category">
                <h6>Other Violations</h6>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="otherViolation" id="otherViolation">
                    <label class="form-check-label" for="otherViolation"><?php echo htmlspecialchars($violation_offenses['otherViolation']['label']); ?></label>
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
            <button type="submit" class="btn btn-primary btn-custom mt-4">Submit Violation</button>
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

        otherViolationCheckbox.addEventListener('change', () => {
            otherViolationInput.style.display = otherViolationCheckbox.checked ? 'block' : 'none';
        });

        // Show/hide Other Vehicle Type input
        const otherVehicleCheckbox = document.getElementById('othersVehicle');
        const otherVehicleInput = document.getElementById('otherVehicleInput');

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

        // Handle form submission with AJAX
        document.getElementById('citationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('insert_citation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    window.location.href = 'driver_records.php';
                }
            })
            .catch(error => {
                alert('Error submitting form: ' + error);
            });
        });
    </script>
</body>
</html>