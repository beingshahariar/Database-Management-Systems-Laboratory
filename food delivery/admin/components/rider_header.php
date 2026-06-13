<?php
if (!empty($message) && is_array($message)) {
   foreach ($message as $msg) {
      echo '
      <div class="message">
         <span>'.htmlspecialchars($msg).'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <section class="flex">

      <a href="rider_dashboard.php" class="logo">Rider<span>Panel</span></a>

      <nav class="navbar">
         <a href="rider_dashboard.php">Home</a>
         <a href="rider_orders.php">My orders</a>
         <a href="update_rider_profile.php">Profile</a>
      </nav>

      <div class="icons">
         <!-- Removed the menu button -->
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
         if (session_status() === PHP_SESSION_NONE) {
             session_start();
         }

         $rider_id = $_SESSION['rider_id'] ?? null;

         if (!$rider_id) {
            header('location:rider_login.php');
            exit;
         }

         $select_profile = $conn->prepare("SELECT * FROM `riders` WHERE id = ?");
         $select_profile->execute([$rider_id]);
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= htmlspecialchars($fetch_profile['name']); ?></p>

         <?php if($rider_id): ?>
            <a href="update_rider_profile.php" class="btn">update profile</a>
            <a href="../components/rider_logout.php" onclick="return confirm('Logout from this website?');" class="delete-btn">logout</a>
         <?php else: ?>
            <div class="flex-btn">
               <a href="rider_login.php" class="option-btn">login</a>
               <a href="register_rider.php" class="option-btn">register</a>
            </div>
         <?php endif; ?>
      </div>

   </section>

</header>
