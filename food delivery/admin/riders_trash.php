<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

// LOGIC TO RESTORE A RIDER
if (isset($_GET['restore'])) {
    $restore_id = (string)$_GET['restore'];
    // On restore, set deleted_at to NULL and status back to 'active'
    $restore_rider = $conn->prepare("UPDATE `riders` SET deleted_at = NULL, status = 'active' WHERE id = ?");
    $restore_rider->execute([$restore_id]);
    header('location:riders_trash.php');
    exit;
}

// PERMANENTLY DELETE A RIDER
if (isset($_GET['permanent_delete'])) {
    $delete_id = (string)$_GET['permanent_delete'];
    // This is the final, irreversible delete
    $delete_rider = $conn->prepare("DELETE FROM `riders` WHERE id = ?");
    $delete_rider->execute([$delete_id]);
    header('location:riders_trash.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trashed Rider Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

    <div class="heading-container">
        <h1 class="heading">Trashed Riders (🗑️)</h1>
        <a href="riders_accounts.php" class="option-btn" style="margin-top: 1rem;">View Active Riders</a>
    </div>

    <div class="box-container">
        <?php
        // Query to show ONLY soft-deleted riders
        $select_riders = $conn->prepare("SELECT * FROM `riders` WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        $select_riders->execute();

        if ($select_riders->rowCount() > 0) {
            while ($rider = $select_riders->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <p> Rider ID : <span><?= htmlspecialchars($rider['id']); ?></span> </p>
            <p> Name : <span><?= htmlspecialchars($rider['name']); ?></span> </p>
            <p> Email : <span><?= htmlspecialchars($rider['email']); ?></span> </p>
            <p> Status : <span style="color:red;"><?= htmlspecialchars($rider['status']); ?></span> </p>
            <p> Deleted On : <span><?= date('Y-m-d H:i:s', strtotime($rider['deleted_at'])); ?></span> </p>
            <div class="flex-btn">
                <a href="riders_trash.php?restore=<?= htmlspecialchars($rider['id']); ?>" class="option-btn" onclick="return confirm('Restore this rider account?');">Restore (🔄)</a>
                <a href="riders_trash.php?permanent_delete=<?= htmlspecialchars($rider['id']); ?>" class="delete-btn" onclick="return confirm('Permanently delete this rider? This action cannot be undone.');">Delete Forever</a>
            </div>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">Trash is empty!</p>';
        }
        ?>
    </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>