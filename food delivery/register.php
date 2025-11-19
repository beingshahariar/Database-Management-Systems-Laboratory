<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

$message = array();

if(isset($_POST['submit'])){
   $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
   $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
   $number = filter_var(trim($_POST['number']), FILTER_SANITIZE_STRING);
   $pass = $_POST['pass']; // Plain password for hashing
   $cpass = $_POST['cpass'];

   // Validate inputs
   if(strlen($name) < 3){
      $message[] = 'Name must be at least 3 characters!';
   }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = 'Invalid email format!';
   }elseif(strlen($number) != 11 || !preg_match('/^01[3-9]\d{8}$/', $number)){
      $message[] = 'Invalid phone number! Use 01XXXXXXXXX format.';
   }elseif(strlen($pass) < 6){
      $message[] = 'Password must be at least 6 characters!';
   }elseif($pass != $cpass){
      $message[] = 'Confirm password not matched!';
   }else{
      // Check if user exists 
      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? OR number = ? AND deleted_at IS NULL");
      $select_user->execute([$email, $number]);
      
      if($select_user->rowCount() > 0){
         $message[] = 'Email or phone number already exists!';
      }else{
         // SECURE: Use password_hash() 
         $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
         
         $insert_user = $conn->prepare("INSERT INTO `users`(name, email, number, password) VALUES(?,?,?,?)");
         $insert_user->execute([$name, $email, $number, $hashed_pass]);
         
         if($insert_user){
            $message[] = 'Registration successful! Please <a href="login.php">login now</a>.';
            // Clear form data after success
            $_POST = array();
         }else{
            $message[] = 'Registration failed! Please try again.';
         }
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
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="form-container">
   <form action="" method="post">
      <h3>Register Now</h3>
      
      <input type="text" name="name" required placeholder="Enter your name" class="box" maxlength="50" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
      <input type="email" name="email" required placeholder="Enter your email" class="box" maxlength="50" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="number" name="number" required placeholder="Enter your number (01XXXXXXXXX)" class="box" maxlength="11" value="<?= isset($_POST['number']) ? htmlspecialchars($_POST['number']) : '' ?>">
      <input type="password" name="pass" required placeholder="Enter your password (min 6 chars)" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="Confirm password" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Register Now" name="submit" class="btn">
      <p>Already have an account? <a href="login.php">Login Now</a></p>
   </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>