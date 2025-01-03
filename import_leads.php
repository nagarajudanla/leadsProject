<?php
require 'vendor/autoload.php'; // For PhpSpreadsheet
require 'db_connection.php'; // Your database connection file

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check for valid file extensions
        if ($fileExtension === 'xlsx' || $fileExtension === 'xls') {
            try {
                $spreadsheet = IOFactory::load($fileTmpPath);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                $successCount = 0;
                $failureCount = 0;
                $errorDetails = [];

                // Loop through rows (skip header row)
                foreach ($sheetData as $rowIndex => $row) {
                    if ($rowIndex === 1) continue; // Skip header row

                    // Map data from the row
                    $name = trim($row['A'] ?? '');
                    $email = trim($row['B'] ?? '');
                    $phone = trim($row['C'] ?? '');
                    $status = trim($row['D'] ?? '');

                    // Validate required fields
                    if (empty($name) || empty($email) || empty($phone) || empty($status)) {
                        $errorDetails[] = "Row $rowIndex: Missing required fields.";
                        $failureCount++;
                        continue;
                    }

                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errorDetails[] = "Row $rowIndex: Invalid email format.";
                        $failureCount++;
                        continue;
                    }

                    // Validate phone number format
                    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
                        $errorDetails[] = "Row $rowIndex: Invalid phone number.";
                        $failureCount++;
                        continue;
                    }

                    // Validate status
                    $validStatuses = ['New', 'In Progress', 'Closed'];
                    if (!in_array($status, $validStatuses)) {
                        $errorDetails[] = "Row $rowIndex: Invalid status. Allowed values: New, In Progress, Closed.";
                        $failureCount++;
                        continue;
                    }

                    // Insert into database
                    try {
                        $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, status, date_added, last_updated) VALUES (?, ?, ?, ?, NOW(), NOW())");
                        if ($stmt->execute([$name, $email, $phone, $status])) {
                            $successCount++;
                        } else {
                            $errorDetails[] = "Row $rowIndex: Database insertion failed.";
                            $failureCount++;
                        }
                    } catch (Exception $e) {
                        $errorDetails[] = "Row $rowIndex: " . $e->getMessage();
                        $failureCount++;
                    }
                }

                // Display summary
                echo "<h3>Import Summary</h3>";
                echo "Successful: $successCount<br>";
                echo "Failed: $failureCount<br>";
                if (!empty($errorDetails)) {
                    echo "<h4>Error Details:</h4><ul>";
                    foreach ($errorDetails as $error) {
                        echo "<li>$error</li>";
                    }
                    echo "</ul>";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Invalid file type. Please upload a valid Excel file (.xlsx or .xls).";
        }
    } else {
        echo "File upload error. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Leads</title>
</head>
<body>
    <h2>Import Leads</h2>
    <form action="import_leads.php" method="post" enctype="multipart/form-data">
        <label for="file">Select Excel File:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit" name="import">Import</button>
    </form>
    <button type="submit" name="export"><a href="export_leads.php">Export</a></button>
</body>
</html>
