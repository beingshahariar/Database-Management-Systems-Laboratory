<?php
if(isset($_POST['add_to_cart'])){

   // Check if user is logged in
   if($user_id == ''){
      header('location:login.php');
      exit();
   }

   // Sanitize inputs
   $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
   $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);

   // Check if product exists 
   $check_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND deleted_at IS NULL");
   $check_product->execute([$product_id]);

   if($check_product->rowCount() > 0){
      //if product is already in cart
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE product_id = ? AND user_id = ?");
      $check_cart->execute([$product_id, $user_id]);

      if($check_cart->rowCount() > 0){
         // Update quantity 
         $update_cart = $conn->prepare("UPDATE `cart` SET quantity = quantity + ? WHERE product_id = ? AND user_id = ?");
         $update_cart->execute([$qty, $product_id, $user_id]);
         $message[] = 'Cart quantity updated!';
      } else {
         // Insert new cart item
         $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, product_id, quantity) VALUES (?, ?, ?)");
         $insert_cart->execute([$user_id, $product_id, $qty]);
         $message[] = 'Added to cart!';
      }
   } else {
      $message[] = 'Product not available!';
   }
}
?>
