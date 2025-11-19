<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

//SOFT DELETE 
if (isset($_GET['delete'])) {
    $delete_id = (string)$_GET['delete'];

    $soft_delete_user = $conn->prepare("UPDATE `users` SET deleted_at = NOW() WHERE id = ?");
    $soft_delete_user->execute([$delete_id]);
    
    header('location:users_accounts.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active User Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

    <div class="heading-container">
        <h1 class="heading">Active User Accounts</h1>
        <a href="users_trash.php" class="option-btn" style="margin-top: 1rem;">View Trash (🗑️)</a>
    </div>

    <div class="box-container">
        <?php
        // Query for non-deleted users
        $select_account = $conn->prepare("SELECT * FROM `users` WHERE deleted_at IS NULL ORDER BY created_at DESC");
        $select_account->execute();
        if ($select_account->rowCount() > 0) {
            while ($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <p> User ID : <span><?= htmlspecialchars($fetch_accounts['id']); ?></span> </p>
            <p> Username : <span><?= htmlspecialchars($fetch_accounts['name']); ?></span> </p>
            <p> Email : <span><?= htmlspecialchars($fetch_accounts['email']); ?></span> </p>
            <p> Number : <span><?= htmlspecialchars($fetch_accounts['number']); ?></span> </p>
            <div class="flex-btn">
                <a href="users_accounts.php?delete=<?= htmlspecialchars($fetch_accounts['id']); ?>" class="delete-btn" onclick="return confirm('Move this user to trash?');">Delete</a>
            </div>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No active accounts available</p>';
        }
        ?>
    </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>