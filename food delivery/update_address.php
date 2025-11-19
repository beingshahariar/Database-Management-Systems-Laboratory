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

// ADD new address
if(isset($_POST['submit'])){

   $address_type = $_POST['address_type']; // home, work, shipping etc.
   $address = $_POST['flat'] .', '.$_POST['building'].', '.$_POST['area'].', '.$_POST['town'] .', '. $_POST['city'] .', '. $_POST['state'] .', '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);

   $insert = $conn->prepare("INSERT INTO user_addresses (user_id, address_type, address_text) VALUES (?, ?, ?)");
   $insert->execute([$user_id, $address_type, $address]);

   $message[] = 'New address added!';
}

// DELETE address
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
   $delete->execute([$delete_id, $user_id]);
   $message[] = 'Address deleted!';
   header("Location: update_address.php"); 
   exit();
}

// fetch existing addresses
$select_addresses = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
$select_addresses->execute([$user_id]);
$addresses = $select_addresses->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update address</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php' ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Add New Address</h3>
      <label>Address Type</label>
      <select name="address_type" class="box" required>
         <option value="home">Home</option>
         <option value="work">Work</option>
         <option value="billing">Billing</option>
         <option value="shipping">Shipping</option>
      </select>
      <input type="text" class="box" placeholder="flat no." required maxlength="50" name="flat">
      <input type="text" class="box" placeholder="building no." required maxlength="50" name="building">
      <input type="text" class="box" placeholder="area name" required maxlength="50" name="area">
      <input type="text" class="box" placeholder="town name" required maxlength="50" name="town">
      <input type="text" class="box" placeholder="city name" required maxlength="50" name="city">
      <input type="text" class="box" placeholder="state name" required maxlength="50" name="state">
      <input type="text" class="box" placeholder="country name" required maxlength="50" name="country">
      <input type="number" class="box" placeholder="pin code" required max="999999" min="0" maxlength="6" name="pin_code">
      <input type="submit" value="Save Address" name="submit" class="btn">
   </form>

</section>

<section class="show-addresses">
   <h3>Your Saved Addresses</h3>
   <?php if(!empty($addresses)): ?>
      <?php foreach($addresses as $addr): ?>
         <div class="box">
            <p><strong><?= ucfirst(htmlspecialchars($addr['address_type'])); ?></strong></p>
            <p><?= htmlspecialchars($addr['address_text']); ?></p>
            <a href="update_address.php?delete=<?= $addr['id']; ?>" class="delete-btn" onclick="return confirm('Delete this address?')">Delete</a>
         </div>
      <?php endforeach; ?>
   <?php else: ?>
      <p>No addresses saved yet.</p>
   <?php endif; ?>
</section>

<?php include 'components/footer.php' ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
