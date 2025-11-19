<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

// RESTORE A USER
if (isset($_GET['restore'])) {
    $restore_id = (string)$_GET['restore'];
    $restore_user = $conn->prepare("UPDATE `users` SET deleted_at = NULL WHERE id = ?");
    $restore_user->execute([$restore_id]);
    header('location:users_trash.php');
    exit;
}

// PERMANENTLY DELETE A USER
if (isset($_GET['permanent_delete'])) {
    $delete_id = (string)$_GET['permanent_delete'];
    
    $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
    $delete_user->execute([$delete_id]);
    
    header('location:users_trash.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trashed User Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

    <div class="heading-container">
        <h1 class="heading">Trashed Users (🗑️)</h1>
        <a href="users_accounts.php" class="option-btn" style="margin-top: 1rem;">View Active Users</a>
    </div>

    <div class="box-container">
        <?php
        // Query for soft-deleted users
        $select_account = $conn->prepare("SELECT * FROM `users` WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        $select_account->execute();
        if ($select_account->rowCount() > 0) {
            while ($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <p> User ID : <span><?= htmlspecialchars($fetch_accounts['id']); ?></span> </p>
            <p> Username : <span><?= htmlspecialchars($fetch_accounts['name']); ?></span> </p>
            <p> Deleted On : <span><?= date('Y-m-d H:i:s', strtotime($fetch_accounts['deleted_at'])); ?></span> </p>
            <div class="flex-btn">
                <a href="users_trash.php?restore=<?= htmlspecialchars($fetch_accounts['id']); ?>" class="option-btn" onclick="return confirm('Restore this user account?');">Restore (🔄)</a>
                <a href="users_trash.php?permanent_delete=<?= htmlspecialchars($fetch_accounts['id']); ?>" class="delete-btn" onclick="return confirm('Permanently delete this user? This action cannot be undone and will delete all their related data.');">Delete Forever</a>
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