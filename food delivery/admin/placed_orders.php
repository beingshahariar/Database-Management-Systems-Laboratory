<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

// === IMPROVEMENT 1: COMBINED UPDATE LOGIC ===
if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];
    $rider_id = !empty($_POST['rider_id']) ? $_POST['rider_id'] : null;

    //query to update both payment status and rider assignment
    $stmt = $conn->prepare("UPDATE `orders` SET payment_status = ?, rider_id = ? WHERE id = ?");
    $stmt->execute([$payment_status, $rider_id, $order_id]);

    $message[] = 'Order updated successfully!';
}

// Delete order
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $stmt->execute([$delete_id]);
    header('location:placed_orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Placed Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="placed-orders">
    <h1 class="heading">Placed Orders</h1>

    <div class="box-container">
        <?php
        $select_orders = $conn->prepare("
            SELECT o.*, r.name AS rider_name, r.email AS rider_email 
            FROM orders o
            LEFT JOIN riders r ON o.rider_id = r.id
            ORDER BY placed_on DESC
        ");
        $select_orders->execute();

        if ($select_orders->rowCount() > 0) {
            while ($order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <p><strong>Order ID:</strong> <span><?= htmlspecialchars($order['id']); ?></span></p>
            <p><strong>User ID:</strong> <span><?= htmlspecialchars($order['user_id']); ?></span></p>
            <p><strong>Placed on:</strong> <span><?= htmlspecialchars($order['placed_on']); ?></span></p>
            <p><strong>Name:</strong> <span><?= htmlspecialchars($order['name']); ?></span></p>
            <p><strong>Address:</strong> <span><?= htmlspecialchars($order['address']); ?></span></p>
            <p><strong>Total Price:</strong> <span>$<?= htmlspecialchars($order['total_price']); ?>/-</span></p>
            <p><strong>Payment Method:</strong> <span><?= htmlspecialchars($order['method']); ?></span></p>

            <?php
                $payment_style = ($order['payment_status'] == 'pending') ? 'color:red;' : 'color:green;';
                $rider_style = empty($order['rider_id']) ? 'color:red;' : 'color:green;';
            ?>
            <p><strong>Payment Status:</strong> <span style="<?= $payment_style; ?> font-weight:bold;"><?= htmlspecialchars($order['payment_status']); ?></span></p>
            <p><strong>Delivery Status:</strong> <span><?= htmlspecialchars($order['delivery_status'] ?? 'pending'); ?></span></p>
            <p><strong>Assigned Rider:</strong> 
                <span style="<?= $rider_style; ?> font-weight:bold;">
                    <?= $order['rider_name'] ? htmlspecialchars($order['rider_name']) . " ({$order['rider_email']})" : 'Unassigned'; ?>
                </span>
            </p>
            
            <form action="" method="POST">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']); ?>">

                <select name="payment_status" class="drop-down" required>
                    <option value="pending" <?= ($order['payment_status'] == 'pending') ? 'selected' : ''; ?>>pending</option>
                    <option value="completed" <?= ($order['payment_status'] == 'completed') ? 'selected' : ''; ?>>completed</option>
                </select>

                <select name="rider_id" class="drop-down">
                    <option value="">-- Unassign --</option>
                    <?php
                    $riders = $conn->prepare("SELECT * FROM riders WHERE status='active' ORDER BY name ASC");
                    $riders->execute();
                    while ($rider = $riders->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($order['rider_id'] == $rider['id']) ? "selected" : "";
                        echo "<option value='{$rider['id']}' $selected>".htmlspecialchars($rider['name'])."</option>";
                    }
                    ?>
                </select>

                <div class="flex-btn">
                    <input type="submit" value="Update" class="btn" name="update_order">
                    <a href="placed_orders.php?delete=<?= htmlspecialchars($order['id']); ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
                </div>
            </form>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">No orders placed yet!</p>';
        }
        ?>
    </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>