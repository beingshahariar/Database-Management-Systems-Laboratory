<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
    exit();
}

$fetch_profile = [];
$get_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
$get_profile->execute([$user_id]);
if ($get_profile->rowCount() > 0) {
    $fetch_profile = $get_profile->fetch(PDO::FETCH_ASSOC);
}

// Fetch all saved user addresses
$user_addresses = [];
$get_addresses = $conn->prepare("SELECT address_text, address_type FROM user_addresses WHERE user_id = ? ORDER BY id DESC");
$get_addresses->execute([$user_id]);
if ($get_addresses->rowCount() > 0) {
    while ($fetch_addr = $get_addresses->fetch(PDO::FETCH_ASSOC)) {
        $user_addresses[] = $fetch_addr;
    }
}

// check for Green Tag Products in the cart
$is_eco_friendly_order = false;
$check_green_products = $conn->prepare("
    SELECT COUNT(c.product_id) 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? AND p.is_green = 1
");
$check_green_products->execute([$user_id]);
if ($check_green_products->fetchColumn() > 0) {
    $is_eco_friendly_order = true;
}


$final_total_carbon_impact = 0;
$final_total_calories = 0;

//  calculate all totals
$select_totals = $conn->prepare("
    SELECT c.quantity, p.price, p.calories, ci.carbon_value
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    LEFT JOIN carbon_impact ci ON p.id = ci.product_id
    WHERE c.user_id = ? AND p.deleted_at IS NULL
");
$select_totals->execute([$user_id]);

if ($select_totals->rowCount() > 0) {
    while ($fetch_totals = $select_totals->fetch(PDO::FETCH_ASSOC)) {
        // Calculate and accumulate Carbon and Calories
        $carbon_per_item = (float)($fetch_totals['carbon_value'] ?? 0);
        $calories_per_item = (float)($fetch_totals['calories'] ?? 0);

        $final_total_carbon_impact += $carbon_per_item * $fetch_totals['quantity'];
        $final_total_calories += $calories_per_item * $fetch_totals['quantity'];
    }
}



if (isset($_POST['submit'])) {

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
    $total_products = $_POST['total_products'];
    $total_price = $_POST['total_price'];
    
    
    $db_carbon = filter_var($_POST['db_carbon_impact'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $db_calories = filter_var($_POST['db_calories'], FILTER_SANITIZE_NUMBER_INT);


    // Handle Address Selection/Input
    $address = '';
    if (isset($_POST['address_select']) && $_POST['address_select'] !== 'new' && !empty($_POST['address_select'])) {
        $address = filter_var($_POST['address_select'], FILTER_SANITIZE_STRING);
    } elseif (isset($_POST['address_input']) && !empty($_POST['address_input'])) {
        $address = filter_var($_POST['address_input'], FILTER_SANITIZE_STRING);
    } else {
        $address = $fetch_profile['address'] ?? '';
    }

    $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
    $check_cart->execute([$user_id]);

    if ($check_cart->rowCount() > 0) {
        if (empty($address)) {
            $message[] = 'Please add your address!';
        } else {
            // === START: LOGIC FOR PAYMENT STATUS ===
            $payment_status = ($method == 'cash on delivery') ? 'pending' : 'completed';
            // === END: LOGIC FOR PAYMENT STATUS ===

            // INSERT 
            $insert_order = $conn->prepare("INSERT INTO orders
                (user_id, name, number, email, method, address, total_products, total_price, total_carbon_impact, total_calories, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            $insert_order->execute([
                $user_id, 
                $name, 
                $number, 
                $email, 
                $method, 
                $address, 
                $total_products, 
                $total_price, 
                $db_carbon, 
                $db_calories,
                $payment_status
            ]);

            // Delete cart items after placing order
            $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $delete_cart->execute([$user_id]);
            
            
            if ($is_eco_friendly_order) {
                // Eco-friendly message if cart contains at least one Green Tag product
                $message[] = '🌿 Small step, big impact! You’re a true climate hero 🌟 Your eco-friendly choice helps build a cleaner, greener world. Order placed successfully!';
            } else {
                // Standard message
                $message[] = '🎉 Order placed successfully! Thank you for choosing us. Enjoy your delicious food!';
            }
        }
    } else {
        $message[] = 'Your cart is empty!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>
<div class="heading">
    <h3>Checkout</h3>
    <p><a href="home.php">Home</a> <span> / Checkout</span></p>
</div>

<section class="checkout">
    <h1 class="title">Order Summary</h1>
    <form action="" method="post">
        <div class="cart-items">
            <h3>Cart Items</h3>
            <?php
            $grand_total = 0;
            $cart_items = [];
            $total_products = '';
            
            // --- Re-running query for display and final total calculation ---
            // Fetch cart details with Carbon/Calories for Summary Display
            $current_total_carbon_impact = 0;
            $current_total_calories = 0;

            $select_cart_display = $conn->prepare("
                SELECT c.quantity, p.name, p.price, p.calories, ci.carbon_value
                FROM cart c
                JOIN products p ON c.product_id = p.id
                LEFT JOIN carbon_impact ci ON p.id = ci.product_id
                WHERE c.user_id = ? AND p.deleted_at IS NULL
            ");
            $select_cart_display->execute([$user_id]);

            if ($select_cart_display->rowCount() > 0) {
                while ($fetch_cart = $select_cart_display->fetch(PDO::FETCH_ASSOC)) {
                    $item_price = $fetch_cart['price'] * $fetch_cart['quantity'];
                    $grand_total += $item_price;
                    
                    // Calculation for display totals
                    $carbon_per_item = (float)($fetch_cart['carbon_value'] ?? 0);
                    $calories_per_item = (float)($fetch_cart['calories'] ?? 0);
                    $current_total_carbon_impact += $carbon_per_item * $fetch_cart['quantity'];
                    $current_total_calories += $calories_per_item * $fetch_cart['quantity'];

                    $cart_items[] = htmlspecialchars($fetch_cart['name']) . ' (' . htmlspecialchars($fetch_cart['price']) . ' x ' . htmlspecialchars($fetch_cart['quantity']) . ')';
            ?>
            <p><span class="name"><?= htmlspecialchars($fetch_cart['name']); ?></span>
                <span class="price">$<?= htmlspecialchars($fetch_cart['price']); ?> x <?= htmlspecialchars($fetch_cart['quantity']); ?></span>
            </p>
            <?php
                }
                $total_products = implode(' - ', $cart_items);
            } else {
                echo '<p class="empty">Your cart is empty!</p>';
            }
            ?>
            
            <p class="grand-total">
                <span class="name">Grand Total :</span>
                <span class="price">$<?= number_format($grand_total, 2); ?></span>
            </p>
            
            <?php if ($current_total_carbon_impact > 0): ?>
            <p style="color: #757575; font-size: 1.6rem; margin-top: 1rem;">
                <i class="fa-solid fa-cloud"></i> Total Carbon Impact: 
                <span style="font-weight: bold;"><?= number_format($current_total_carbon_impact, 2); ?> kg CO₂</span>
            </p>
            <?php endif; ?>
            
            <?php if ($current_total_calories > 0): ?>
            <p style="color: #ff5722; font-size: 1.6rem;">
                <i class="fa-solid fa-fire"></i> Total Calories: 
                <span style="font-weight: bold;"><?= number_format($current_total_calories, 0); ?> kcal</span>
            </p>
            <?php endif; ?>
            <a href="cart.php" class="btn">View Cart</a>
        </div>

        <input type="hidden" name="total_products" value="<?= htmlspecialchars($total_products); ?>">
        <input type="hidden" name="total_price" value="<?= htmlspecialchars($grand_total); ?>">
        <input type="hidden" name="db_carbon_impact" value="<?= number_format($current_total_carbon_impact, 2, '.', ''); ?>">
        <input type="hidden" name="db_calories" value="<?= number_format($current_total_calories, 0, '.', ''); ?>">
        <div class="user-info">
            <h3>Your Info</h3>
            <p><i class="fas fa-user"></i><span><?= htmlspecialchars($fetch_profile['name'] ?? 'Guest'); ?></span></p>
            <p><i class="fas fa-phone"></i><span><?= htmlspecialchars($fetch_profile['number'] ?? ''); ?></span></p>
            <p><i class="fas fa-envelope"></i><span><?= htmlspecialchars($fetch_profile['email'] ?? ''); ?></span></p>
            <a href="update_profile.php" class="btn">Update Info</a>

            <h3>Delivery Address</h3>
            <p><i class="fas fa-map-marker-alt"></i> <span>Select or add a new address:</span></p>

            <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>">
            <input type="hidden" name="number" value="<?= htmlspecialchars($fetch_profile['number'] ?? ''); ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($fetch_profile['email'] ?? ''); ?>">
            
            <select name="address_select" class="box" id="address_select" onchange="toggleAddressInput(this.value)" required>
                <option value="" disabled selected>-- Select delivery address --</option>
                <?php if (!empty($fetch_profile['address'])): ?>
                    <option value="<?= htmlspecialchars($fetch_profile['address']); ?>">
                        Profile Address: <?= htmlspecialchars($fetch_profile['address']); ?>
                    </option>
                <?php endif; ?>
                <?php foreach ($user_addresses as $addr): ?>
                    <option value="<?= htmlspecialchars($addr['address_text']); ?>">
                        <?= htmlspecialchars(ucfirst($addr['address_type'])); ?>: <?= htmlspecialchars($addr['address_text']); ?>
                    </option>
                <?php endforeach; ?>
                <option value="new">➕ Add new address</option>
            </select>
            <textarea name="address_input" id="address_input" class="box" placeholder="e.g. flat, house no., street, city, country" style="display:none;"></textarea>
            
            <select name="method" class="box" required>
                <option value="" disabled selected>-- Select payment method --</option>
                <option value="cash on delivery">Cash on Delivery</option>
                <option value="credit card">Credit Card</option>
                <option value="bkash">bKash</option>
                <option value="nagad">Nagad</option>
            </select>
            
            <input type="submit" value="Place Order" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">
        </div>
    </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<script>
    function toggleAddressInput(selectedValue) {
        const addressInput = document.getElementById('address_input');
        if (selectedValue === 'new') {
            addressInput.style.display = 'block';
            addressInput.setAttribute('required', 'required');
        } else {
            addressInput.style.display = 'none';
            addressInput.removeAttribute('required');
            addressInput.value = '';
        }
    }
</script>
</body>
</html>