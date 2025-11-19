<?php

include '../components/connect.php';

session_start();


$rider_id = $_SESSION['rider_id'];

if(!isset($rider_id)){
   header('location:rider_login.php'); 
   exit;
}

// delete rider account
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_rider = $conn->prepare("DELETE FROM `riders` WHERE id = ?");
   $delete_rider->execute([$delete_id]);
   header('location:rider_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rider Accounts</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/rider_style.css">

</head>
<body>

<?php include '../components/rider_header.php'; ?>  

<!-- riders accounts section starts -->

<section class="accounts">

   <h1 class="heading">Rider Accounts</h1>

   <div class="box-container">

      <div class="box">
         <p>Register new rider</p>
         <a href="register_rider.php" class="option-btn">Register</a>
      </div>

      <?php
         $select_account = $conn->prepare("SELECT * FROM `riders`");
         $select_account->execute();
         if($select_account->rowCount() > 0){
            while($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)){  
      ?>
      <div class="box">
         <p> Rider ID : <span><?= $fetch_accounts['id']; ?></span> </p>
         <p> Name : <span><?= htmlspecialchars($fetch_accounts['name']); ?></span> </p>
         <p> Email : <span><?= htmlspecialchars($fetch_accounts['email']); ?></span> </p>
         <p> Phone : <span><?= htmlspecialchars($fetch_accounts['phone']); ?></span> </p>
         <p> Status : <span><?= ucfirst($fetch_accounts['status']); ?></span> </p>
         
         <div class="flex-btn">
            <a href="rider_accounts.php?delete=<?= $fetch_accounts['id']; ?>" 
               class="delete-btn" 
               onclick="return confirm('Delete this rider account?');">Delete</a>
            <?php
               if($fetch_accounts['id'] == $rider_id){
                  echo '<a href="update_rider_profile.php" class="option-btn">Update</a>';
               }
            ?>
         </div>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">No rider accounts available</p>';
         }
      ?>

   </div>

</section>

<!-- riders accounts section ends -->

<!-- custom js file link  -->
<script src="../js/rider_script.js"></script>


</body>
</html>
