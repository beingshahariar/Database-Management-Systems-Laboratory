<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
    header('location:admin_login.php');
    exit(); // Added exit after redirect
}

//Fetch Admin Profile 
$select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// Check if profile was actually found
if ($fetch_profile === false) {
    session_unset();
    session_destroy();
    header('location:admin_login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php' ?>

<section class="dashboard">

    <h1 class="heading">Dashboard</h1>

    <div class="box-container">

        <div class="box">
            <h3>Welcome!</h3>
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p> 
            <a href="update_profile.php" class="btn">Update Profile</a>
        </div>

        <div class="box">
            <?php
                // Query for Total Pendings
                $select_pendings = $conn->prepare("SELECT SUM(total_price) AS total_pending_price FROM `orders` WHERE payment_status = ?");
                $select_pendings->execute(['pending']);
                
                $total_pendings = $select_pendings->fetchColumn() ?? 0;
            ?>
            <h3><span>$</span><?= number_format($total_pendings, 2); ?><span>/-</span></h3>
            <p>Total Pendings</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
        </div>

        <div class="box">
            <?php
                //Query for Total Completes
                $select_completes = $conn->prepare("SELECT SUM(total_price) AS total_completed_price FROM `orders` WHERE payment_status = ?");
                $select_completes->execute(['completed']);
                $total_completes = $select_completes->fetchColumn() ?? 0;
            ?>
            <h3><span>$</span><?= number_format($total_completes, 2); ?><span>/-</span></h3>
            <p>Total Completes</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
        </div>

        <div class="box">
            <?php
                // Query for Total Orders
                $select_orders = $conn->query("SELECT COUNT(*) FROM `orders`");
                $numbers_of_orders = $select_orders->fetchColumn() ?? 0;
            ?>
            <h3><?= $numbers_of_orders; ?></h3>
            <p>Total Orders</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
        </div>

        <div class="box">
            <?php
                // Query for Active Products ('deleted_at' column for soft-delete)
                $select_products = $conn->query("SELECT COUNT(*) FROM `products` WHERE deleted_at IS NULL");
                $numbers_of_products = $select_products->fetchColumn() ?? 0;
            ?>
            <h3><?= $numbers_of_products; ?></h3>
            <p>Active Products</p>
            <a href="products.php" class="btn">See Products</a>
        </div>

        <div class="box">
            <?php
                //Query for User Accounts
                $select_users = $conn->query("SELECT COUNT(*) FROM `users` WHERE deleted_at IS NULL");
                $numbers_of_users = $select_users->fetchColumn() ?? 0;
            ?>
            <h3><?= $numbers_of_users; ?></h3>
            <p>User Accounts</p>
            <a href="users_accounts.php" class="btn">See Users</a>
        </div>

        <div class="box">
            <?php
                $select_admins = $conn->query("SELECT COUNT(*) FROM `admin`");
                $numbers_of_admins = $select_admins->fetchColumn() ?? 0;
            ?>
            <h3><?= $numbers_of_admins; ?></h3>
            <p>Admins</p>
            <a href="admin_accounts.php" class="btn">See Admins</a>
        </div>
        
        <div class="box">
            <?php
                // Query for Active Riders
                $select_riders = $conn->query("SELECT COUNT(*) FROM `riders` WHERE status = 'active'");
                $numbers_of_riders = $select_riders->fetchColumn() ?? 0;
            ?>
            <h3><?= $numbers_of_riders; ?></h3>
            <p>Active Riders</p>
            <a href="riders_accounts.php" class="btn">See Riders</a>
        </div>

        <div class="box">
            <?php
                $select_messages = $conn->query("SELECT COUNT(*) FROM `messages`");
                $numbers_of_messages = $select_messages->fetchColumn() ?? 0;
            ?>
            <h3><?= $numbers_of_messages; ?></h3>
            <p>New Messages</p>
            <a href="messages.php" class="btn">See Messages</a>
        </div>

    </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>