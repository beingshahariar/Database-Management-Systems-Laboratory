<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
    header('location:admin_login.php');
    exit();
}

// Add new product 
if(isset($_POST['add_product'])){

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $calories = !empty($_POST['calories']) ? filter_var($_POST['calories'], FILTER_SANITIZE_STRING) : 0;
    $carbon_impact = !empty($_POST['carbon_impact']) ? filter_var($_POST['carbon_impact'], FILTER_SANITIZE_STRING) : null;
    
    //Green Tag value (1 if checked, 0 otherwise)
    $is_green = isset($_POST['is_green']) && $_POST['is_green'] == '1' ? 1 : 0;

    $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_img/'.$image;

    // Check for duplicate active product
    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ? AND deleted_at IS NULL");
    $select_products->execute([$name]);

    if($select_products->rowCount() > 0){
        $message[] = '❌ Active product with this name already exists!';
    } else {
        if($image_size > 2000000){
            $message[] = '❌ Image size is too large (max 2MB)';
        } else {
            try {
                $conn->beginTransaction();

                // 🆔 Generate UUID manually
                $product_id = bin2hex(random_bytes(16));
                $uuid = substr($product_id, 0, 8) . '-' .
                        substr($product_id, 8, 4) . '-' .
                        substr($product_id, 12, 4) . '-' .
                        substr($product_id, 16, 4) . '-' .
                        substr($product_id, 20);

                // Save image
                move_uploaded_file($image_tmp_name, $image_folder);

                // Insert into products table 
                $insert_product = $conn->prepare("INSERT INTO `products` (id, name, category, price, image, calories, is_green) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert_product->execute([$uuid, $name, $category, $price, $image, $calories, $is_green]);

                //Insert into carbon_impact table
                if($carbon_impact !== null){
                    $insert_impact = $conn->prepare("INSERT INTO `carbon_impact` (product_id, carbon_value) VALUES (?, ?)");
                    $insert_impact->execute([$uuid, $carbon_impact]);
                }

                $conn->commit();
                $message[] = '✅ New product added successfully!';

            } catch (Exception $e) {
                $conn->rollBack();
                $message[] = '❌ Error adding product: ' . $e->getMessage();
            }
        }
    }
}

//  Soft delete product 
if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];

    try {
        $conn->beginTransaction();

        // Soft delete the product
        $soft_delete_product = $conn->prepare("UPDATE `products` SET deleted_at = NOW() WHERE id = ?");
        $soft_delete_product->execute([$delete_id]);

        // Remove from cart
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE product_id = ?");
        $delete_cart->execute([$delete_id]);

        // Delete carbon impact data
        $delete_impact = $conn->prepare("DELETE FROM `carbon_impact` WHERE product_id = ?");
        $delete_impact->execute([$delete_id]);

        $conn->commit();
        header('location:products.php');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $message[] = '❌ Error deleting product: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php' ?>

<section class="add-products">
    <form action="" method="POST" enctype="multipart/form-data">
        <h3>Add Product</h3>
        <input type="text" required placeholder="enter product name" name="name" maxlength="100" class="box">
        <input type="number" min="0" max="9999999999" required placeholder="enter product price" name="price" onkeypress="if(this.value.length == 10) return false;" class="box">
        <select name="category" class="box" required>
            <option value="" disabled selected>select category --</option>
            <option value="main dish">main dish</option>
            <option value="fast food">fast food</option>
            <option value="drinks">drinks</option>
            <option value="desserts">desserts</option>
        </select>
        <input type="number" step="0.01" min="0" name="carbon_impact" placeholder="carbon impact (kg CO₂)" class="box">
        <input type="number" step="0.01" min="0" name="calories" placeholder="calories (kcal)" class="box">
        
        <div style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 1rem;">
            <input type="checkbox" name="is_green" value="1" id="is_green_checkbox" style="width: auto; height: 1.5rem; margin-right: 0.5rem; cursor: pointer;">
            <label for="is_green_checkbox" style="font-size: 1.6rem; color: var(--main-color); cursor: pointer;">
                🌿 **Mark as Green Tag / Eco-Friendly**
            </label>
        </div>
        <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
        <input type="submit" value="add product" name="add_product" class="btn">
    </form>
</section>

<section class="show-products" style="padding-top: 0;">
    <div class="heading-container">
        <h1 class="heading">Active Products</h1>
        <a href="trash.php" class="option-btn" style="margin-top: 1rem;">View Trash (🗑️)</a>
    </div>

    <div class="box-container">
    <?php
        // Fetch products with carbon impact
        $show_products = $conn->prepare("
            SELECT p.*, ci.carbon_value 
            FROM `products` p
            LEFT JOIN `carbon_impact` ci ON p.id = ci.product_id
            WHERE p.deleted_at IS NULL
            ORDER BY p.created_at DESC
        ");
        $show_products->execute();

        if($show_products->rowCount() > 0){
            while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){
    ?>
    <div class="box">
        <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
        <div class="flex">
            <div class="price"><span>$</span><?= htmlspecialchars($fetch_products['price']); ?><span>/-</span></div>
            <div class="category"><?= htmlspecialchars($fetch_products['category']); ?></div>
        </div>
        
        <div class="name">
            <?= htmlspecialchars($fetch_products['name']); ?>
            <?php if($fetch_products['is_green'] == 1): ?>
                <span style="color: green; margin-left: 5px;">(🌿)</span>
            <?php endif; ?>
        </div>
        <?php 
        
        if($fetch_products['carbon_value'] !== null): ?>
        <div class="carbon-info" style="margin-top: .5rem; color: green; font-weight: bold;">
            💨 Carbon Impact: <?= htmlspecialchars($fetch_products['carbon_value']); ?> kg CO₂
        </div>
        <?php endif; ?>

        <?php if($fetch_products['calories'] !== null && $fetch_products['calories'] > 0): ?>
        <div class="calories-info" style="margin-top: .3rem; color: #ff5722; font-weight: bold;">
            🔥 Calories: <?= htmlspecialchars($fetch_products['calories']); ?> kcal
        </div>
        <?php endif; ?>

        <div class="flex-btn">
            <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Update</a>
            <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Move this product to trash?');">Delete</a>
        </div>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">No active products found!</p>';
        }
    ?>
    </div>
</section>

<script src="../js/admin_script.js"></script>

</body>
</html>