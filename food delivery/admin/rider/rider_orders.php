<?php
include '../components/connect.php';
session_start();

$rider_id = $_SESSION['rider_id'] ?? null;

if(!$rider_id){
    header('location:rider_login.php');
    exit;
}

if(isset($_POST['update_delivery'])){
    $order_id        = filter_var($_POST['order_id'], FILTER_SANITIZE_STRING);
    $delivery_status = filter_var($_POST['delivery_status'], FILTER_SANITIZE_STRING);

    // Update the delivery status
    $update_delivery_status = $conn->prepare("UPDATE `orders` SET delivery_status = ? WHERE id = ? AND rider_id = ?");
    $update_delivery_status->execute([$delivery_status, $order_id, $rider_id]);

    // If 'delivered', update payment status
    if($delivery_status == 'delivered'){
        $update_payment_status = $conn->prepare("UPDATE `orders` SET payment_status = 'completed' WHERE id = ? AND method = 'cash on delivery'");
        $update_payment_status->execute([$order_id]);
    }

    $message[] = 'Order status has been updated!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/rider_style.css">
</head>
<body>

<?php include '../components/rider_header.php'; ?>

<section class="placed-orders">
    <h1 class="heading">My Assigned Orders</h1>

    <div class="box-container">

    <?php
       $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE rider_id = ? ORDER BY placed_on DESC");
       $select_orders->execute([$rider_id]);

       if($select_orders->rowCount() > 0){
          while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
    ?>
    <div class="box">
        <table class="order-details-table">
            <tr>
                <td>Order ID:</td>
                <td><?= htmlspecialchars($fetch_orders['id']); ?></td>
            </tr>
            <tr>
                <td>Placed on:</td>
                <td><?= htmlspecialchars($fetch_orders['placed_on']); ?></td>
            </tr>
            <tr>
                <td>Customer Name:</td>
                <td><?= htmlspecialchars($fetch_orders['name']); ?></td>
            </tr>
            <tr>
                <td>Customer Phone:</td>
                <td><?= htmlspecialchars($fetch_orders['number']); ?></td>
            </tr>
            <tr>
                <td>Address:</td>
                <td><?= htmlspecialchars($fetch_orders['address']); ?></td>
            </tr>
            <tr>
                <td>Total Price:</td>
                <td>$<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</td>
            </tr>
            <tr>
                <td>Payment Method:</td>
                <td><?= htmlspecialchars($fetch_orders['method']); ?></td>
            </tr>
            <?php
                $payment_style = ($fetch_orders['payment_status'] == 'pending') ? 'color:red;' : 'color:green;';
            ?>
            <tr>
                <td>Payment Status:</td>
                <td><span style="<?= $payment_style; ?> font-weight:bold;"><?= htmlspecialchars($fetch_orders['payment_status']); ?></span></td>
            </tr>
        </table>
        <form action="" method="POST">
          <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
          
          <label for="delivery_status" style="font-size: 1.8rem; color: var(--black); margin: 1rem 0; display:block;">Update Delivery Status:</label>
          <select name="delivery_status" id="delivery_status" class="drop-down" required>
             <option value="" disabled selected><?= htmlspecialchars($fetch_orders['delivery_status']); ?></option>
             <option value="pending">Pending</option>
             <option value="picked">Picked Up</option>
             <option value="delivered">Delivered</option>
             <option value="cancelled">Cancelled</option>
          </select>
          <div class="flex-btn">
             <input type="submit" value="update" class="btn" name="update_delivery">
          </div>
       </form>
    </div>
    <?php
          }
       }else{
          echo '<p class="empty">No orders assigned to you yet!</p>';
       }
    ?>
    </div>
</section>

<script src="../js/rider_script.js"></script>

</body>
</html>