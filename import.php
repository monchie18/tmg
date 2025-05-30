```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Traffic Citation CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #357abd;
        }
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .undo-button {
            background-color: #e94e77;
        }
        .undo-button:hover {
            background-color: #c43c5e;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Import Traffic Citation CSV</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">Select CSV File</label>
                <input type="file" id="csv_file" name="file" accept=".csv" required>
            </div>
            <button type="submit" name="submit">Upload CSV</button>
        </form>
        <?php
        session_start();
        // Display message if set in session
        if (isset($_SESSION['message'])) {
            $message_class = strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success';
            echo "<div class='message $message_class'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            // Show undo button only after successful import
            if (strpos($_SESSION['message'], 'successfully') !== false && isset($_SESSION['last_import_id'])) {
                echo '<form action="undo.php" method="post" onsubmit="return confirm(\'Are you sure you want to undo the last import? This cannot be reversed.\');">';
                echo '<button type="submit" class="undo-button" name="undo">Undo Last Import</button>';
                echo '</form>';
            }
            unset($_SESSION['message']);
        }
        ?>
    </div>
</body>
</html>