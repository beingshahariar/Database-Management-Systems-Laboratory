<?php

// Include the database connection component
include '../components/connect.php';

// Start the session
session_start();

if(isset($_POST['submit'])){

    // 1. Sanitize Input Data
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    
    // Hash the password input using SHA1 (to match the database format)
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    // 2. Select Admin from the database using prepared statement
    // Checks if both the username and the SHA1-hashed password match a record in the 'admin' table
    $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ? AND password = ?");
    $select_admin->execute([$name, $pass]);
    
    if($select_admin->rowCount() > 0){
      // Login successful
      $fetch_admin_id = $select_admin->fetch(PDO::FETCH_ASSOC);
      
      // Set the session variable for the admin ID
      $_SESSION['admin_id'] = $fetch_admin_id['id'];
      
      // Redirect to the dashboard
      header('location:dashboard.php');
      exit(); // Always exit after a header redirection
    }else{
      // Login failed
      $message[] = 'incorrect username or password!';
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php
// Display messages (e.g., login errors)
if(isset($message)){
    foreach($message as $message){
      echo '
      <div class="message">
          <span>'.$message.'</span>
          <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
    }
}
?>

<section class="form-container">

    <form action="" method="POST">
      <h3>login now</h3>
      <p>default username = <span>admin</span> & password = <span>111</span></p>
      
      <input type="text" name="name" maxlength="20" required placeholder="enter your username" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      
      <input type="password" name="pass" maxlength="20" required placeholder="enter your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      
      <input type="submit" value="login now" name="submit" class="btn">
    </form>

</section>

</body>
</html>