<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

// RESTORE A PRODUCT
if(isset($_GET['restore'])){
   $restore_id = $_GET['restore'];
   $restore_product = $conn->prepare("UPDATE `products` SET deleted_at = NULL WHERE id = ?");
   $restore_product->execute([$restore_id]);
   header('location:trash.php');
   $message[] = 'Product restored successfully!';
}

// PERMANENTLY DELETE A PRODUCT
if(isset($_GET['permanent_delete'])){
   $delete_id = $_GET['permanent_delete'];
   
   // image delete
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   if($fetch_delete_image && file_exists('../uploaded_img/'.$fetch_delete_image['image'])){
      unlink('../uploaded_img/'.$fetch_delete_image['image']);
   }
   
   // permanently delete
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   
   header('location:trash.php');
   $message[] = 'Product permanently deleted!';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Trashed Products</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php' ?>

<section class="show-products">

   <div class="heading-container">
      <h1 class="heading">Trashed Products (🗑️)</h1>
      <a href="products.php" class="option-btn" style="margin-top: 1rem;">View Active Products</a>
   </div>

   <div class="box-container">

   <?php
      // QUERY for SOFT-DELETED PRODUCTS
      $show_products = $conn->prepare("SELECT * FROM `products` WHERE deleted_at IS NOT NULL");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="flex">
         <div class="price"><span>$</span><?= $fetch_products['price']; ?><span>/-</span></div>
         <div class="category"><?= $fetch_products['category']; ?></div>
      </div>
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="flex-btn">
         <a href="trash.php?restore=<?= $fetch_products['id']; ?>" class="option-btn" onclick="return confirm('Restore this product?');">Restore (🔄)</a>
         <a href="trash.php?permanent_delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Permanently delete this product? This action cannot be undone.');">Delete Forever</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">Trash is empty!</p>';
      }
   ?>
   </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>