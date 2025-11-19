<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
    exit();
}

$select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// **CRITICAL FIX:** Check if profile data was successfully fetched. 
// If not (i.e., $fetch_profile is false), the user ID is invalid, 
// so destroy the session and redirect to prevent the warnings.
if ($fetch_profile === false) {
    session_destroy();
    header('location:login.php'); 
    exit();
}


// Fetch user addresses 

$select_addresses = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC");
$select_addresses->execute([$user_id]);
$addresses = $select_addresses->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

</head>
<body>
    
<?php include 'components/user_header.php'; ?>
<section class="user-details">

    <div class="user">
      <img src="images/user-icon.png" alt="">
      
      <p><i class="fas fa-user"></i><span><?= htmlspecialchars($fetch_profile['name']); ?></span></p>
      <p><i class="fas fa-phone"></i><span><?= htmlspecialchars($fetch_profile['number']); ?></span></p>
      <p><i class="fas fa-envelope"></i><span><?= htmlspecialchars($fetch_profile['email']); ?></span></p>
      <a href="update_profile.php" class="btn">Update Info</a>

      <div class="addresses">
          <h3>Your Saved Addresses</h3>
          <?php if (!empty($addresses)) : ?>
            <?php foreach ($addresses as $addr) : ?>
                <p class="address">
                    <i class="fas fa-map-marker-alt"></i>
                    <strong><?= ucfirst(htmlspecialchars($addr['address_type'])); ?>:</strong>
                    <?= htmlspecialchars($addr['address_text']); ?>
                </p>
            <?php endforeach; ?>
          <?php else : ?>
             <p class="address">
                <i class="fas fa-map-marker-alt"></i>
                You haven't added any addresses yet.
             </p>
          <?php endif; ?>
          <a href="update_address.php" class="btn">Add / Manage Addresses</a>
      </div>

    </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>