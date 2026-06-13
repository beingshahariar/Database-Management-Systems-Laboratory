<?php
include '../components/connect.php';
session_start();

if(isset($_POST['submit'])){
   $email = trim($_POST['email']);
   $pass  = $_POST['pass'];

   $select_rider = $conn->prepare("SELECT * FROM `riders` WHERE email = ? AND deleted_at IS NULL");
   $select_rider->execute([$email]);

   if($select_rider->rowCount() > 0){
      $fetch_rider = $select_rider->fetch(PDO::FETCH_ASSOC);

      if(password_verify($pass, $fetch_rider['password'])){
         $_SESSION['rider_id'] = $fetch_rider['id'];
         header('location:rider_dashboard.php');
         exit;
      } else {
         $message[] = 'Incorrect password!';
      }
   } else {
      $message[] = 'Account not found or has been deactivated!';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rider Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/rider_style.css">
</head>
<body>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo "<div class='message'><span>{$msg}</span>
      <i class='fas fa-times' onclick='this.parentElement.remove();'></i></div>";
   }
}
?>

<section class="form-container">
   <form action="" method="POST">
      <h3>Rider Login</h3>
      <input type="email" name="email" required placeholder="Enter your email" class="box" maxlength="100">
      <input type="password" name="pass" required placeholder="Enter your password" class="box" maxlength="255">
      <input type="submit" value="Login Now" name="submit" class="btn">
      <p>New rider? <a href="register_rider.php">Register here</a></p>
   </form>
</section>

</body>
</html>