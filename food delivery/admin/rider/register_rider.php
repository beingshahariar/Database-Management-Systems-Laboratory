<?php
include '../components/connect.php';
session_start();

if(isset($_POST['submit'])){

   $name  = trim($_POST['name']);
   $email = trim($_POST['email']);
   $phone = trim($_POST['phone']);
   $pass  = $_POST['pass'];
   $cpass = $_POST['cpass'];

   // Check for if rider email exists
   $select_rider = $conn->prepare("SELECT * FROM `riders` WHERE email = ?");
   $select_rider->execute([$email]);
   
   if($select_rider->rowCount() > 0){
      $message[] = 'Rider email already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'Confirm password not matched!';
      }else{
         // Securely hash password
         $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);

         $insert_rider = $conn->prepare("INSERT INTO `riders`(name, email, phone, password) VALUES(?,?,?,?)");
         $insert_rider->execute([$name, $email, $phone, $hashed_pass]);
         $message[] = 'Rider registered successfully! You can now login.';
         // Store rider id in session (optional, useful for dashboard)
         $rider_id = $conn->lastInsertId();
         $_SESSION['rider_id'] = $rider_id;

         header('Location: rider_dashboard.php');
         exit; 
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
   <title>Rider Registration</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/rider_style.css">
</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container">
   <form action="" method="POST">
      <h3>Rider Registration</h3>
      <input type="text" name="name" maxlength="50" required placeholder="Enter full name" class="box">
      <input type="email" name="email" maxlength="100" required placeholder="Enter email" class="box">
      <input type="text" name="phone" maxlength="15" required placeholder="Enter phone number" class="box">
      <input type="password" name="pass" maxlength="20" required placeholder="Enter password" class="box">
      <input type="password" name="cpass" maxlength="20" required placeholder="Confirm password" class="box">
      <input type="submit" value="Register Now" name="submit" class="btn">
      <p>Already registered? <a href="rider_login.php">Login here</a></p>
   </form>
</section>

<!-- rider js -->
<script src="../js/rider_script.js"></script>
</body>
</html>
