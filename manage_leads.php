<?php
require 'db_connection.php';

// Pagination setup
$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Search/filter
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$sql = "SELECT * FROM leads WHERE 1=1";
$params = [];

// Apply search filter
if (!empty($searchQuery)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

// Apply status filter
if (!empty($filterStatus)) {
    $sql .= " AND status = :status";
    $params[':status'] = $filterStatus;
}

// Add pagination
$sql .= " ORDER BY date_added DESC LIMIT $offset, $recordsPerPage";

// Fetch leads
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM leads WHERE 1=1";
if (!empty($searchQuery)) {
    $countSql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
}
if (!empty($filterStatus)) {
    $countSql .= " AND status = :status";
}
$countStmt = $conn->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Lead Management</h1>
    <button onclick="window.location.href='import_leads.php';">Import</button>
    <button onclick="window.location.href='export_leads.php';">Export</button>

    <!-- Search and Filter Form -->
    <form method="GET" action="manage_leads.php">
        <input type="text" name="search" placeholder="Search by name, email, or phone" value="<?= htmlspecialchars($searchQuery) ?>">
        <select name="status">
            <option value="">All Statuses</option>
            <option value="New" <?= $filterStatus === 'New' ? 'selected' : '' ?>>New</option>
            <option value="In Progress" <?= $filterStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Closed" <?= $filterStatus === 'Closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <button type="submit">Search</button>
    </form>
    
    <!-- Lead Table -->
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($leads) > 0): ?>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?= htmlspecialchars($lead['id']) ?></td>
                        <td><?= htmlspecialchars($lead['name']) ?></td>
                        <td><?= htmlspecialchars($lead['email']) ?></td>
                        <td><?= htmlspecialchars($lead['phone']) ?></td>
                        <td><?= htmlspecialchars($lead['status']) ?></td>
                        <td><?= htmlspecialchars($lead['date_added']) ?></td>
                        <td>
                            <a href="edit_lead.php?id=<?= $lead['id'] ?>">Edit</a>
                            <a href="delete_lead.php?id=<?= $lead['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No leads found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="manage_leads.php?page=<?= $i ?>&search=<?= urlencode($searchQuery) ?>&status=<?= urlencode($filterStatus) ?>" <?= $i === $page ? 'style="font-weight:bold;"' : '' ?>></a>
        <?php endfor; ?>
    </div>
</body>
</html>
