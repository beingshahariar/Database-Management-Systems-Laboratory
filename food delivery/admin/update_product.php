<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? '';
if(!$admin_id){
    header('location:admin_login.php');
    exit();
}

if(isset($_POST['update'])){

    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $carbon_value = filter_var($_POST['carbon_value'], FILTER_SANITIZE_STRING);
    $calories = filter_var($_POST['calories'], FILTER_SANITIZE_STRING);
    

    $is_green = isset($_POST['is_green']) && $_POST['is_green'] == '1' ? 1 : 0;

    // UPDATE:product info
    $update_product = $conn->prepare("
        UPDATE `products` 
        SET name = ?, category = ?, price = ?, calories = ?, is_green = ? 
        WHERE id = ?
    ");
    
    $update_product->execute([$name, $category, $price, $calories, $is_green, $pid]);
    $message[] = 'Product details updated!';

    // Update or insert carbon impact 
    $check_carbon = $conn->prepare("SELECT * FROM `carbon_impact` WHERE product_id = ?");
    $check_carbon->execute([$pid]);
    if($check_carbon->rowCount() > 0){
        $update_carbon = $conn->prepare("UPDATE `carbon_impact` SET carbon_value = ? WHERE product_id = ?");
        $update_carbon->execute([$carbon_value, $pid]);
    } else {
        $insert_carbon = $conn->prepare("INSERT INTO `carbon_impact` (product_id, carbon_value) VALUES (?, ?)");
        $insert_carbon->execute([$pid, $carbon_value]);
    }
    $message[] = 'Carbon impact updated!';

    // image update 
    $old_image = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_img/'.$image;

    if(!empty($image)){
        if($image_size > 2000000){
            $message[] = 'Image size is too large!';
        } else {
            $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $pid]);
            move_uploaded_file($image_tmp_name, $image_folder);
            if(file_exists('../uploaded_img/'.$old_image)){
                unlink('../uploaded_img/'.$old_image);
            }
            $message[] = 'Image updated!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="update-product">
    <h1 class="heading">Update Product</h1>

    <?php
        $update_id = $_GET['update'] ?? '';
        
        
        $show_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?"); 
        $show_products->execute([$update_id]);

        if($show_products->rowCount() > 0){
            while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){

                //Fetch carbon impact 
                $carbon_value = 0;
                $get_carbon = $conn->prepare("SELECT carbon_value FROM `carbon_impact` WHERE product_id = ?");
                $get_carbon->execute([$fetch_products['id']]);
                if($get_carbon->rowCount() > 0){
                    $carbon_value = $get_carbon->fetch(PDO::FETCH_ASSOC)['carbon_value'];
                }
    ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
        <input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">

        <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
        
        <span>Update Name</span>
        <input type="text" required placeholder="enter product name" name="name" maxlength="100" class="box" value="<?= $fetch_products['name']; ?>">

        <span>Update Price</span>
        <input type="number" min="0" max="9999999999" required placeholder="enter product price" name="price" class="box" value="<?= $fetch_products['price']; ?>">

        <span>Update Category</span>
        <select name="category" class="box" required>
            <option selected value="<?= $fetch_products['category']; ?>"><?= $fetch_products['category']; ?></option>
            <option value="main dish">main dish</option>
            <option value="fast food">fast food</option>
            <option value="drinks">drinks</option>
            <option value="desserts">desserts</option>
        </select>

        <span>Update Calories (kcal)</span>
        <input type="number" step="1" min="0" placeholder="enter calories" name="calories" class="box" value="<?= $fetch_products['calories']; ?>">

        <span>Update Carbon Impact (kg CO₂)</span>
        <input type="number" step="0.01" min="0" placeholder="enter carbon value" name="carbon_value" class="box" value="<?= $carbon_value; ?>">
        
        <div style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 1rem; margin-top: 1rem;">
            <input 
                type="checkbox" 
                name="is_green" 
                value="1" 
                id="is_green_checkbox" 
                style="width: auto; height: 1.5rem; margin-right: 0.5rem; cursor: pointer;"
                <?= $fetch_products['is_green'] == 1 ? 'checked' : ''; ?>
            >
            <label for="is_green_checkbox" style="font-size: 1.6rem; color: var(--main-color); cursor: pointer;">
                🌿 **Mark as Green Tag / Eco-Friendly**
            </label>
        </div>
        <span>Update Image</span>
        <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">

        <div class="flex-btn">
            <input type="submit" value="update" class="btn" name="update">
            <a href="products.php" class="option-btn">go back</a>
        </div>
    </form>
    <?php
            }
        } else {
            echo '<p class="empty">No products found!</p>';
        }
    ?>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>