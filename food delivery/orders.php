<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:home.php');
   exit();
}

// Order Cancel Logic
if(isset($_POST['cancel_order'])){

   $order_id = $_POST['order_id'];
   $order_id = filter_var($order_id, FILTER_SANITIZE_STRING);

   $verify_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? AND delivery_status = 'pending'");
   $verify_order->execute([$order_id, $user_id]);

   if($verify_order->rowCount() > 0){
      $update_order = $conn->prepare("UPDATE `orders` SET delivery_status = 'cancelled' WHERE id = ?");
      $update_order->execute([$order_id]);
      $_SESSION['message'][] = 'Order has been cancelled successfully!';     
      header('location:orders.php');
      exit();
   } else {
      $_SESSION['message'][] = 'Order could not be cancelled or is no longer pending.';
   }

}

if(isset($_SESSION['message'])){
   foreach($_SESSION['message'] as $msg){
      echo '<div class="message"><span>'. $msg .'</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
   unset($_SESSION['message']); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>orders</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
    
<?php include 'components/user_header.php'; ?>

<div class="heading">
    <h3>orders</h3>
    <p><a href="home.php">Home</a> <span> / Orders</span></p>
</div>

<section class="orders">

    <h1 class="title">your orders</h1>

    <div class="box-container">

    <?php
       if($user_id == ''){
          echo '<p class="empty">please login to see your orders</p>';
       }else{
          // SELECT * FROM orders will now fetch the new columns: total_carbon_impact and total_calories
          $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY placed_on DESC");
          $select_orders->execute([$user_id]);
          if($select_orders->rowCount() > 0){
             while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
    ?>
    <div class="box">
      <p>placed on : <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></p>
      <p>name : <span><?= htmlspecialchars($fetch_orders['name']); ?></span></p>
      <p>email : <span><?= htmlspecialchars($fetch_orders['email']); ?></span></p>
      <p>number : <span><?= htmlspecialchars($fetch_orders['number']); ?></span></p>
      <p>address : <span><?= htmlspecialchars($fetch_orders['address']); ?></span></p>
      <p>payment method : <span><?= htmlspecialchars($fetch_orders['method']); ?></span></p>
      <p>your orders : <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></p>
      <p>total price : <span>$<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span></p>

      <?php if (!empty($fetch_orders['total_carbon_impact']) && $fetch_orders['total_carbon_impact'] > 0): ?>
      <p style="color: #757575; font-weight: 500;">
          <i class="fa-solid fa-cloud"></i> Carbon Impact: 
          <span style="font-weight: bold;"><?= number_format($fetch_orders['total_carbon_impact'], 2); ?> kg CO₂</span>
      </p>
      <?php endif; ?>

      <?php if (!empty($fetch_orders['total_calories']) && $fetch_orders['total_calories'] > 0): ?>
      <p style="color: #ff5722; font-weight: 500;">
          <i class="fa-solid fa-fire"></i> Calories Consumed: 
          <span style="font-weight: bold;"><?= number_format($fetch_orders['total_calories'], 0); ?> kcal</span>
      </p>
      <?php endif; ?>

      <p> payment status : 
          <span style="color:
            <?php 
              if($fetch_orders['payment_status'] == 'pending'){ 
                 echo 'red'; 
              } else { 
                 echo 'green'; 
              } 
            ?>">
            <?= htmlspecialchars($fetch_orders['payment_status']); ?>
          </span> 
      </p>

      <p> delivery status : 
        <span style="color:
            <?php 
                if($fetch_orders['delivery_status'] == 'pending'){ 
                   echo 'red'; 
                } elseif($fetch_orders['delivery_status'] == 'picked'){ 
                   echo 'orange'; 
                } elseif($fetch_orders['delivery_status'] == 'delivered'){ 
                   echo 'green'; 
                } elseif($fetch_orders['delivery_status'] == 'cancelled'){ 
                   echo 'gray'; 
                } else { 
                   echo 'black'; 
                } 
            ?>">
            <?= htmlspecialchars($fetch_orders['delivery_status']); ?>
        </span> 
      </p>

      <?php if ($fetch_orders['delivery_status'] == 'pending'): ?>
          <form action="" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
              <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
              <input type="submit" value="Cancel Order ❌" name="cancel_order" class="btn delete-btn">
          </form>
      <?php endif; ?>
    </div>

    <?php
             }
          }else{
             echo '<p class="empty">no orders placed yet!</p>';
          }
       }
    ?>

    </div>

</section>


<?php include 'components/footer.php'; ?>


<script src="js/script.js"></script>

</body>
</html>