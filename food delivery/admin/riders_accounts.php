<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

// *** SOFT DELETE ***
if (isset($_GET['delete'])) {
    $delete_id = (string)$_GET['delete'];

    // Soft delete the rider status to 'inactive'
    $soft_delete_rider = $conn->prepare("UPDATE `riders` SET deleted_at = NOW(), status = 'inactive' WHERE id = ?");
    $soft_delete_rider->execute([$delete_id]);

    // Unassign any PENDING orders from this rider
    $unassign_orders = $conn->prepare("UPDATE `orders` SET rider_id = NULL WHERE rider_id = ? AND delivery_status = 'pending'");
    $unassign_orders->execute([$delete_id]);

    header('location:riders_accounts.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Riders Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

    <div class="heading-container">
        <h1 class="heading">Active Riders</h1>
        <a href="riders_trash.php" class="option-btn" style="margin-top: 1rem;">View Trash (🗑️)</a>
    </div>

    <div class="box-container">
        <?php
        // Query selects ONLY active, non-deleted riders 
        $select_riders = $conn->prepare("SELECT * FROM `riders` WHERE deleted_at IS NULL ORDER BY created_at DESC");
        $select_riders->execute();

        if ($select_riders->rowCount() > 0) {
            while ($rider = $select_riders->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <p> Rider ID : <span><?= htmlspecialchars($rider['id']); ?></span> </p>
            <p> Name : <span><?= htmlspecialchars($rider['name']); ?></span> </p>
            <p> Email : <span><?= htmlspecialchars($rider['email']); ?></span> </p>
            <p> Phone : <span><?= htmlspecialchars($rider['phone']); ?></span> </p>
            <p> Status : <span style="color:<?= ($rider['status'] == 'active') ? 'limegreen' : 'red'; ?>"><?= htmlspecialchars($rider['status']); ?></span> </p>
            <div class="flex-btn">
                <a href="riders_accounts.php?delete=<?= htmlspecialchars($rider['id']); ?>" class="delete-btn" onclick="return confirm('Move this rider to trash?');">Delete</a>
            </div>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No active riders found</p>';
        }
        ?>
    </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>