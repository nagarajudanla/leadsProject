<?php
require 'vendor/autoload.php';
require 'db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filter = $_GET['filter'] ?? '';

// Fetch leads based on filter
$query = "SELECT name, email, phone, status, date_added FROM leads WHERE status = ? OR ? = ''";
$stmt = $conn->prepare($query);
$stmt->execute([$filter, $filter]);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create Excel file
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['Name', 'Email', 'Phone', 'Status', 'Date Added'], NULL, 'A1');
$sheet->fromArray($leads, NULL, 'A2');

$writer = new Xlsx($spreadsheet);
$fileName = 'leads_export.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
?>
