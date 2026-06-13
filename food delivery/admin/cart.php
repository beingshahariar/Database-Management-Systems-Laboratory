<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $check_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? AND deleted_at IS NULL");
    $check_user->execute([$user_id]);
    if($check_user->rowCount() == 0){
        session_unset();
        session_destroy();
        header('location:home.php');
        exit();
    }
}else{
    header('location:home.php');
    exit();
}

// Delete single item
if(isset($_POST['delete'])){
    $cart_id = $_POST['cart_id'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$cart_id]);
}

// Delete all
if(isset($_POST['delete_all'])){
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
}

// Update quantity
if(isset($_POST['update_qty'])){
    $cart_id = $_POST['cart_id'];
    $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);
    if($qty < 1) $qty = 1;
    if($qty > 99) $qty = 99;

    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $cart_id]);
}

$grand_total = 0;
$total_carbon_impact = 0; 
$total_calories = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<style>
/* ... (existing styles) ... */

/* 1. Restructure .cart-total using Flexbox */
.cart-total {
    display: flex; /* Enable flex layout */
    flex-wrap: wrap; /* Allow items to wrap to the next line */
    justify-content: center; /* Center items horizontally */
    gap: 1.5rem; /* Space between the total boxes */
    padding: 2rem 1rem; /* Adjust padding for the container */
    background-color: #f7f7f7; /* Light background for the total section */
    border: 1px solid #ddd;
    border-radius: .8rem;
    margin-top: 2rem;
}

/* 2. Style the individual total boxes */
.cart-total p,
.cart-total .carbon-total,
.cart-total .calorie-total {
    /* Set a minimum width for the boxes to ensure they fit three-across */
    min-width: 250px; 
    max-width: 300px;
    flex-grow: 1; /* Allow boxes to grow equally */
    
    padding: 1.5rem 1rem;
    background-color: var(--white);
    box-shadow: var(--box-shadow);
    border-radius: .5rem;
    margin-top: 0; /* Remove previous margins */
    font-size: 1.8rem; 
    text-align: center;
    line-height: 1.2;
}

/* Ensure the main price total stands out */
.cart-total p span {
    color: var(--red); /* Assuming red is your highlight color for price */
    font-weight: bolder;
    font-size: 2rem;
}

/* 3. Center the Proceed to Checkout Button and make it full width relative to its container */
.cart-total .btn {
    display: block; 
    width: 95%; /* Take almost full width of the container */
    max-width: 400px; /* Optional: limit maximum size on huge screens */
    margin: 1rem auto 0; /* Center horizontally with space above */
    text-align: center;
    font-size: 2rem;
    padding: 1rem 0;
}
</style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
    <h3>Shopping Cart</h3>
    <p><a href="home.php">Home</a> <span> / Cart</span></p>
</div>

<section class="products">
    <h1 class="title">Your Cart</h1>
    <div class="box-container">

        <?php
            // Fetch cart items with product details and carbon impact
            $select_cart = $conn->prepare("
                SELECT c.id AS cart_id, c.quantity, 
                       p.id AS product_id, p.name, p.price, p.image, p.category, p.calories,
                       ci.carbon_value
                FROM cart c
                JOIN products p ON c.product_id = p.id
                LEFT JOIN carbon_impact ci ON p.id = ci.product_id
                WHERE c.user_id = ? AND p.deleted_at IS NULL
            ");
            $select_cart->execute([$user_id]);

            if($select_cart->rowCount() > 0){
                while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                    $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                    $grand_total += $sub_total;

                    //Calculate cumulative carbon impact and calories
                    $item_carbon = (float)$fetch_cart['carbon_value'] * $fetch_cart['quantity'];
                    $item_calories = (float)$fetch_cart['calories'] * $fetch_cart['quantity'];

                    $total_carbon_impact += $item_carbon;
                    $total_calories += $item_calories;
        ?>
        <form action="" method="post" class="box">
            <input type="hidden" name="cart_id" value="<?= $fetch_cart['cart_id']; ?>">
            <a href="quick_view.php?pid=<?= $fetch_cart['product_id']; ?>" class="fas fa-eye"></a>
            <button type="submit" class="fas fa-times" name="delete" onclick="return confirm('delete this item?');"></button>
            <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
            <div class="name"><?= $fetch_cart['name']; ?></div>
            <div class="flex">
                <div class="price"><span>$</span><?= $fetch_cart['price']; ?></div>
                <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" maxlength="2">
                <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </div>
            <div class="sub-total">
                Sub total : <span>$<?= number_format($sub_total, 2); ?>/-</span>
            </div>
            <?php if(!is_null($fetch_cart['carbon_value']) && $fetch_cart['carbon_value'] > 0): ?>
            <div class="carbon-impact" style="color: #757575;">
                💨 Total Carbon: <span><?= number_format($item_carbon, 2); ?> kg CO₂</span>
            </div>
            <?php endif; ?>
            <?php if(!is_null($fetch_cart['calories']) && $fetch_cart['calories'] > 0): ?>
            <div class="calories-count" style="color: #ff5722;">
                🔥 Total Calories: <span><?= number_format($item_calories, 0); ?> kcal</span>
            </div>
            <?php endif; ?>
        </form>
        <?php
                }
            } else {
                echo '<p class="empty">Your cart is empty</p>';
            }
        ?>

    </div>

    <div class="cart-total">
        <p>Cart total : <span>$<?= number_format($grand_total, 2); ?></span></p>
        
        <?php if($total_carbon_impact > 0): ?>
        <p class="carbon-total">
            Total Carbon Impact : <span style="color: #757575;">💨 <?= number_format($total_carbon_impact, 2); ?> kg CO₂</span>
        </p>
        <?php endif; ?>

        <?php if($total_calories > 0): ?>
        <p class="calorie-total">
            Total Calories : <span style="color: #ff5722;">🔥 <?= number_format($total_calories, 0); ?> kcal</span>
        </p>
        <?php endif; ?>

        <a href="checkout.php" class="btn <?= ($grand_total > 0)?'':'disabled'; ?>">Proceed to checkout</a>
    </div>

    <div class="more-btn">
        <form action="" method="post">
            <button type="submit" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" name="delete_all" onclick="return confirm('delete all from cart?');">Delete all</button>
        </form>
        <a href="menu.php" class="btn">Continue shopping</a>
    </div>

</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>