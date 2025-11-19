<?php

include '../components/connect.php';

session_start();

$rider_id = $_SESSION['rider_id'] ?? null;

if(!$rider_id){
   header('location:rider_login.php');
   exit;
}

// fetch rider profile
$select_profile = $conn->prepare("SELECT * FROM `riders` WHERE id = ?");
$select_profile->execute([$rider_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// order stats 
$total_orders_stmt = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE rider_id = ?");
$total_orders_stmt->execute([$rider_id]);
$total_orders = $total_orders_stmt->fetchColumn();

$pending_orders_stmt = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE rider_id = ? AND delivery_status = 'pending'");
$pending_orders_stmt->execute([$rider_id]);
$pending_orders = $pending_orders_stmt->fetchColumn();

$delivered_orders_stmt = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE rider_id = ? AND delivery_status = 'delivered'");
$delivered_orders_stmt->execute([$rider_id]);
$delivered_orders = $delivered_orders_stmt->fetchColumn();

$cancelled_orders_stmt = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE rider_id = ? AND delivery_status = 'cancelled'");
$cancelled_orders_stmt->execute([$rider_id]);
$cancelled_orders = $cancelled_orders_stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rider Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- css file link  -->
 <link rel="stylesheet" href="../css/rider_style.css">

</head>
<body>

<?php include '../components/rider_header.php'; ?>

<!-- rider dashboard section starts -->

<section class="dashboard">

   <h1 class="heading">Rider Dashboard</h1>

   <div class="box-container">

      <div class="box">
         <h3>Welcome!</h3>
         <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
         <a href="update_rider_profile.php" class="btn">Update Profile</a>
      </div>

      <div class="box">
         <h3><?= $total_orders; ?></h3>
         <p>Total Orders Assigned</p>
         <a href="rider_orders.php" class="btn">See Orders</a>
      </div>

      <div class="box">
         <h3><?= $pending_orders; ?></h3>
         <p>Pending Deliveries</p>
         <a href="rider_orders.php?status=pending" class="btn">View Pending</a>
      </div>

      <div class="box">
         <h3><?= $delivered_orders; ?></h3>
         <p>Completed Deliveries</p>
         <a href="rider_orders.php?status=delivered" class="btn">View Delivered</a>
      </div>

      <div class="box">
         <h3><?= $cancelled_orders; ?></h3>
         <p>Cancelled Orders</p>
         <a href="rider_orders.php?status=cancelled" class="btn">View Cancelled</a>
      </div>

   </div>

</section>

<!-- rider dashboard section ends -->

<!-- custom js file link  -->
<script src="../js/rider_script.js"></script>


</body>
</html>
