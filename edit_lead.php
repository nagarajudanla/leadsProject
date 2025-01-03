<?php
require 'db_connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Fetch lead details
    $stmt = $conn->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$id]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lead) {
        die("Lead not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $status = trim($_POST['status']);

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($status)) {
        echo "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE leads SET name = ?, email = ?, phone = ?, status = ?, last_updated = NOW() WHERE id = ?");
        if ($stmt->execute([$name, $email, $phone, $status, $id])) {
            header("Location: manage_leads.php");
            exit;
        } else {
            echo "Failed to update lead.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lead</title>
</head>
<body>
    <h1>Edit Lead</h1>
    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($lead['name']) ?>" placeholder="Name" required>
        <input type="email" name="email" value="<?= htmlspecialchars($lead['email']) ?>" placeholder="Email" required>
        <input type="text" name="phone" value="<?= htmlspecialchars($lead['phone']) ?>" placeholder="Phone" required>
        <select name="status" required>
            <option value="New" <?= $lead['status'] === 'New' ? 'selected' : '' ?>>New</option>
            <option value="In Progress" <?= $lead['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Closed" <?= $lead['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <button type="submit">Update</button>
    </form>
</body>
</html>
