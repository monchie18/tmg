<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'traffic_citation_db';
    $username = 'root';
    $password = '';

    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if file was uploaded (modify this if not using file upload)
    // For testing, assume CSV content is provided directly or adjust to read from a file
    $file = 'path/to/NEW 2023-2024 CITATION TICKET (Responses) - Form Responses 1.csv'; // Update path
    if (!file_exists($file)) {
        throw new Exception('CSV file not found.');
    }
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'csv') {
        throw new Exception('Invalid file type. Please provide a CSV file.');
    }

    // Create temporary table
    $pdo->exec("
        CREATE TEMPORARY TABLE temp_citations (
            timestamp VARCHAR(50),
            ticket_number VARCHAR(20),
            last_name VARCHAR(50),
            first_name VARCHAR(50),
            middle_initial VARCHAR(5),
            barangay VARCHAR(100),
            zone VARCHAR(50),
            license_number VARCHAR(20),
            plate_number VARCHAR(50),
            vehicle_type VARCHAR(50),
            vehicle_description VARCHAR(255),
            date_apprehended VARCHAR(50), -- Store as string initially
            time_apprehension VARCHAR(50), -- Store as string initially
            place_apprehension VARCHAR(255),
            violations TEXT,
            apprehending_officer VARCHAR(255),
            remarks TEXT
        )
    ");

    // Load CSV data into temporary table
    $fileHandle = fopen($file, 'r');
    if ($fileHandle === false) {
        throw new Exception('Failed to open CSV file.');
    }
    fgetcsv($fileHandle); // Skip header row
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO temp_citations (
            timestamp, ticket_number, last_name, first_name, middle_initial, barangay, zone,
            license_number, plate_number, vehicle_type, vehicle_description, date_apprehended,
            time_apprehension, place_apprehension, violations, apprehending_officer, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    while (($row = fgetcsv($fileHandle)) !== false) {
        // Ensure row has 17 columns
        while (count($row) < 17) {
            $row[] = null;
        }

        // Convert empty strings to NULL
        $row = array_map(function($value) {
            $trimmed = trim($value ?? '');
            return empty($trimmed) || in_array(strtoupper($trimmed), ['N/A', 'NONE']) ? null : $trimmed;
        }, $row);

        $stmt->execute($row);
    }
    fclose($fileHandle);

    // Clean and normalize data
    $pdo->exec("
        UPDATE temp_citations
        SET
            middle_initial = NULLIF(TRIM(UPPER(middle_initial)), 'N/A'),
            license_number = NULLIF(TRIM(UPPER(license_number)), 'NONE'),
            plate_number = NULLIF(TRIM(plate_number), 'N/A'),
            vehicle_type = NULLIF(TRIM(vehicle_type), 'N/A'),
            vehicle_description = NULLIF(TRIM(vehicle_description), 'N/A'),
            zone = NULLIF(TRIM(zone), 'N/A'),
            barangay = NULLIF(TRIM(barangay), 'N/A'),
            ticket_number = NULLIF(TRIM(ticket_number), ''),
            remarks = NULLIF(TRIM(remarks), ''),
            violations = NULLIF(TRIM(violations), ''),
            place_apprehension = NULLIF(TRIM(place_apprehension), ''),
            date_apprehended = NULLIF(TRIM(date_apprehended), ''),
            time_apprehension = NULLIF(TRIM(time_apprehension), '')
    ");

    // Parse date and time into proper formats
    $pdo->exec("
        ALTER TABLE temp_citations
        ADD date_apprehended_formatted DATE,
        ADD time_apprehension_formatted TIME
    ");

    // Fetch all rows to parse dates and times
    $tempRows = $pdo->query("SELECT * FROM temp_citations")->fetchAll(PDO::FETCH_ASSOC);
    $updateStmt = $pdo->prepare("
        UPDATE temp_citations
        SET date_apprehended_formatted = :date,
            time_apprehension_formatted = :time
        WHERE ticket_number = :ticket
    ");

    foreach ($tempRows as $row) {
        $date_apprehended = null;
        $time_apprehension = null;

        if (!empty($row['date_apprehended']) && !empty($row['time_apprehension'])) {
            // Parse date (e.g., "11/17/2023")
            $date = DateTime::createFromFormat('m/d/Y', $row['date_apprehended']);
            if ($date) {
                $date_apprehended = $date->format('Y-m-d');
            }

            // Parse time (e.g., "3:07:00 PM")
            $time = DateTime::createFromFormat('h:i:s A', $row['time_apprehension'])
                  ?: DateTime::createFromFormat('h:i A', $row['time_apprehension']);
            if ($time) {
                $time_apprehension = $time->format('H:i:s');
            }
        }

        $updateStmt->execute([
            ':date' => $date_apprehended,
            ':time' => $time_apprehension,
            ':ticket' => $row['ticket_number']
        ]);
    }

    // Insert into drivers
    $pdo->exec("
        INSERT INTO drivers (last_name, first_name, middle_initial, barangay, zone, license_number, municipality, province)
        SELECT DISTINCT
            TRIM(last_name),
            TRIM(first_name),
            middle_initial,
            barangay,
            zone,
            license_number,
            'Baggao',
            'Cagayan'
        FROM temp_citations
        WHERE last_name IS NOT NULL
            AND first_name IS NOT NULL
            AND NOT EXISTS (
                SELECT 1
                FROM drivers d
                WHERE d.last_name = TRIM(temp_citations.last_name)
                    AND d.first_name = TRIM(temp_citations.first_name)
                    AND (d.middle_initial = temp_citations.middle_initial OR (d.middle_initial IS NULL AND temp_citations.middle_initial IS NULL))
                    AND (d.barangay = temp_citations.barangay OR (d.barangay IS NULL AND temp_citations.barangay IS NULL))
            )
    ");

    // Insert into vehicles
    $pdo->exec("
        INSERT INTO vehicles (plate_mv_engine_chassis_no, vehicle_type, vehicle_description)
        SELECT DISTINCT
            plate_number,
            COALESCE(vehicle_type, 'Motorcycle'),
            vehicle_description
        FROM temp_citations
        WHERE plate_number IS NOT NULL
            AND NOT EXISTS (
                SELECT 1
                FROM vehicles v
                WHERE v.plate_mv_engine_chassis_no = temp_citations.plate_number
                    AND (v.vehicle_description = temp_citations.vehicle_description OR (v.vehicle_description IS NULL AND temp_citations.vehicle_description IS NULL))
            )
    ");

    // Insert into citations
    $pdo->exec("
        INSERT INTO citations (ticket_number, driver_id, vehicle_id, apprehension_datetime, place_of_apprehension, payment_status, payment_date, payment_amount)
        SELECT
            tc.ticket_number,
            d.driver_id,
            v.vehicle_id,
            CONCAT(tc.date_apprehended_formatted, ' ', tc.time_apprehension_formatted),
            tc.place_apprehension,
            CASE WHEN UPPER(tc.remarks) = 'PAID' THEN 'Paid' ELSE 'Unpaid' END,
            CASE WHEN UPPER(tc.remarks) = 'PAID' THEN NOW() ELSE NULL END,
            CASE WHEN UPPER(tc.remarks) = 'PAID' THEN (
                SELECT SUM(COALESCE(vt.fine_amount_1, 150.00))
                FROM temp_citations t
                CROSS JOIN (
                    SELECT a.N + b.N * 10 + 1 AS n
                    FROM
                        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) a,
                        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) b
                ) n
                LEFT JOIN violation_types vt
                    ON TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.violations, ',', n.n), ',', -1)) = vt.violation_type
                WHERE t.ticket_number = tc.ticket_number
                    AND n.n <= (LENGTH(t.violations) - LENGTH(REPLACE(t.violations, ',', '')) + 1)
            ) ELSE 0.00 END
        FROM temp_citations tc
        JOIN drivers d
            ON d.last_name = TRIM(tc.last_name)
            AND d.first_name = TRIM(tc.first_name)
            AND (d.middle_initial = tc.middle_initial OR (d.middle_initial IS NULL AND tc.middle_initial IS NULL))
            AND (d.barangay = tc.barangay OR (d.barangay IS NULL AND tc.barangay IS NULL))
        JOIN vehicles v
            ON v.plate_mv_engine_chassis_no = tc.plate_number
            AND (v.vehicle_description = tc.vehicle_description OR (v.vehicle_description IS NULL AND tc.vehicle_description IS NULL))
        WHERE tc.ticket_number IS NOT NULL
            AND tc.date_apprehended_formatted IS NOT NULL
            AND tc.date_apprehended_formatted <= '2025-05-30'
            AND tc.time_apprehension_formatted IS NOT NULL
    ");

    // Insert into violations
    $pdo->exec("
        INSERT INTO violations (citation_id, violation_type, driver_id, fine_amount, offense_count)
        SELECT
            c.citation_id,
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(tc.violations, ',', n.n), ',', -1)),
            d.driver_id,
            COALESCE(vt.fine_amount_1, 150.00),
            1
        FROM temp_citations tc
        JOIN citations c ON c.ticket_number = tc.ticket_number
        JOIN drivers d
            ON d.last_name = TRIM(tc.last_name)
            AND d.first_name = TRIM(tc.first_name)
            AND (d.middle_initial = tc.middle_initial OR (d.middle_initial IS NULL AND tc.middle_initial IS NULL))
            AND (d.barangay = tc.barangay OR (d.barangay IS NULL AND tc.barangay IS NULL))
        CROSS JOIN (
            SELECT a.N + b.N * 10 + 1 AS n
            FROM
                (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) a,
                (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) b
        ) n
        LEFT JOIN violation_types vt
            ON TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(tc.violations, ',', n.n), ',', -1)) = vt.violation_type
        WHERE tc.violations IS NOT NULL
            AND n.n <= (LENGTH(tc.violations) - LENGTH(REPLACE(tc.violations, ',', '')) + 1)
    ");

    // Insert into remarks
    $pdo->exec("
        INSERT INTO remarks (citation_id, remark_text)
        SELECT
            c.citation_id,
            tc.remarks
        FROM temp_citations tc
        JOIN citations c ON c.ticket_number = tc.ticket_number
        WHERE tc.remarks IS NOT NULL
    ");

    // Drop temporary table
    $pdo->exec("DROP TEMPORARY TABLE temp_citations");

    // Commit transaction
    $pdo->commit();

    $_SESSION['message'] = 'CSV data imported successfully.';
} catch (Exception $e) {
    // Rollback on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
}

// Redirect or output result
header('Location: index.php');
exit;
?>