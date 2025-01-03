<?php
require 'db_connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("DELETE FROM leads WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: manage_leads.php");
        exit;
    } else {
        echo "Failed to delete lead.";
    }
}
?>
