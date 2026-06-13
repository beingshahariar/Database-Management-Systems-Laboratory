<?php

include '../components/connect.php';

session_start();

$rider_id = $_SESSION['rider_id'] ?? null;

if(!$rider_id){
   header('location:rider_login.php');
   exit;
}

// fetch rider profile
$select_profile = $conn->prepare("SELECT * FROM `riders` WHERE id = ?");
$select_profile->execute([$rider_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

   if(!empty($name)){
      $update_name = $conn->prepare("UPDATE `riders` SET name = ? WHERE id = ?");
      $update_name->execute([$name, $rider_id]);
      $message[] = 'Name updated!';
   }
   if(!empty($email)){
      // check if email already exists for another rider
      $check_email = $conn->prepare("SELECT * FROM `riders` WHERE email = ? AND id != ?");
      $check_email->execute([$email, $rider_id]);
      if($check_email->rowCount() > 0){
         $message[] = 'Email already taken!';
      } else {
         $update_email = $conn->prepare("UPDATE `riders` SET email = ? WHERE id = ?");
         $update_email->execute([$email, $rider_id]);
         $message[] = 'Email updated!';
      }
   }
   if(!empty($phone)){
      $update_phone = $conn->prepare("UPDATE `riders` SET phone = ? WHERE id = ?");
      $update_phone->execute([$phone, $rider_id]);
      $message[] = 'Phone updated!';
   }

   // --- update password ---
   $empty_pass = sha1('');
   $select_old_pass = $conn->prepare("SELECT password FROM `riders` WHERE id = ?");
   $select_old_pass->execute([$rider_id]);
   $fetch_prev_pass = $select_old_pass->fetch(PDO::FETCH_ASSOC);
   $prev_pass = $fetch_prev_pass['password'];

   $old_pass = sha1($_POST['old_pass']);
   $new_pass = sha1($_POST['new_pass']);
   $confirm_pass = sha1($_POST['confirm_pass']);

   if(!empty($_POST['old_pass']) || !empty($_POST['new_pass']) || !empty($_POST['confirm_pass'])){
      if($old_pass != $prev_pass){
         $message[] = 'Old password not matched!';
      } elseif($new_pass != $confirm_pass){
         $message[] = 'Confirm password not matched!';
      } elseif($new_pass == $empty_pass){
         $message[] = 'Please enter a new password!';
      } else {
         $update_pass = $conn->prepare("UPDATE `riders` SET password = ? WHERE id = ?");
         $update_pass->execute([$confirm_pass, $rider_id]);
         $message[] = 'Password updated successfully!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rider Profile Update</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
  <link rel="stylesheet" href="../css/rider_style.css">

</head>
<body>

<?php include '../components/rider_header.php'; ?>

<section class="form-container">

   <form action="" method="POST">
      <h3>Update Rider Profile</h3>

      <input type="text" name="name" maxlength="50" class="box" placeholder="Current: <?= $fetch_profile['name']; ?>" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="email" name="email" maxlength="100" class="box" placeholder="Current: <?= $fetch_profile['email']; ?>">
      <input type="text" name="phone" maxlength="15" class="box" placeholder="Current: <?= $fetch_profile['phone']; ?>" oninput="this.value = this.value.replace(/\D/g, '')">

      <input type="password" name="old_pass" maxlength="20" placeholder="Enter old password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" maxlength="20" placeholder="Enter new password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="confirm_pass" maxlength="20" placeholder="Confirm new password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

      <input type="submit" value="Update Now" name="submit" class="btn">
   </form>

</section>

<!-- custom js -->
<script src="../js/rider_script.js"></script>


</body>
</html>
