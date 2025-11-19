<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>quick view</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Temporary modal styling - can move to your CSS file later */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: #fff;
            margin: 10% auto;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
            text-align: center;
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        /* 🟡 Carbon Impact Button (Reverting color to yellow/brown/neutral for Carbon) */
        .impact-btn {
            background-color: #757575; /* Neutral for Carbon */
            color: #fff;
            padding: 8px 12px;
            margin-top: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .impact-btn:hover {
            background-color: #616161;
        }

        /* 🔵 Calories button color (kept blue) */
        .calories-btn {
            background-color: #3498db;
            color: #fff;
            padding: 8px 12px;
            margin-top: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .calories-btn:hover {
            background-color: #2e86c1;
        }
    </style>
</head>
<body>
    
<?php include 'components/user_header.php'; ?>

<section class="quick-view">

    <h1 class="title">quick view</h1>

    <?php
        $product_id = $_GET['pid'];
        // Join products with carbon_impact for data (is_green, calories, carbon_value)
        $select_products = $conn->prepare("
            SELECT p.*, ci.carbon_value 
            FROM `products` p
            LEFT JOIN `carbon_impact` ci ON p.id = ci.product_id
            WHERE p.id = ? AND p.deleted_at IS NULL
        ");
        $select_products->execute([$product_id]);
        
        if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
    ?>
    <form action="" method="post" class="box">
        <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
        <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
        <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
        <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">

        <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
        <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
        
        <div class="name">
            <?= htmlspecialchars($fetch_products['name']); ?>
            <?php if(isset($fetch_products['is_green']) && $fetch_products['is_green'] == 1): ?>
                <span style="color: green; margin-left: 5px; font-size: 2rem;">(🌿)</span>
            <?php endif; ?>
        </div>
        <div class="flex">
            <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
            <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
        </div>
        
        <button type="submit" name="add_to_cart" class="cart-btn">add to cart</button>

        <?php 
        // Show button only if data exists
        if(isset($fetch_products['carbon_value']) && $fetch_products['carbon_value'] !== null && $fetch_products['carbon_value'] > 0): 
        ?>
        <button type="button" class="impact-btn" onclick="openCarbonModal('<?= $fetch_products['id']; ?>')">
            <i class="fa-solid fa-cloud"></i> View Carbon Impact
        </button>
        <?php endif; ?>

        <?php 
        // Show button only if data exists
        if(isset($fetch_products['calories']) && $fetch_products['calories'] !== null && $fetch_products['calories'] > 0): 
        ?>
        <button type="button" class="calories-btn" onclick="openCaloriesModal('<?= $fetch_products['id']; ?>')">
            <i class="fa-solid fa-fire"></i> View Calories
        </button>
        <?php endif; ?>
        
    </form>
    <?php
            }
        }else{
            echo '<p class="empty">no products added yet!</p>';
        }
    ?>

</section>

<div id="carbonModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeCarbonModal()">&times;</span>
    <h2>Carbon Impact (💨)</h2>
    <div id="carbonData">Loading...</div>
  </div>
</div>

<div id="caloriesModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeCaloriesModal()">&times;</span>
    <h2>Calories (🔥)</h2>
    <div id="caloriesData">Loading...</div>
  </div>
</div>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
// 🟢 Carbon Modal Logic
function openCarbonModal(productId) {
    const modal = document.getElementById('carbonModal');
    const carbonData = document.getElementById('carbonData');
    modal.style.display = 'block';
    carbonData.innerHTML = 'Loading...';

    // 💡 Fetch data using AJAX/Fetch API
    fetch('get_carbon_impact.php?product_id=' + productId)
        .then(response => response.text())
        .then(data => {
            carbonData.innerHTML = data;
        })
        .catch(error => {
            carbonData.innerHTML = 'Error loading carbon impact.';
        });
}

function closeCarbonModal() {
    document.getElementById('carbonModal').style.display = 'none';
}

// 🔥 Calories Modal Logic
function openCaloriesModal(productId) {
    const modal = document.getElementById('caloriesModal');
    const caloriesData = document.getElementById('caloriesData');
    modal.style.display = 'block';
    caloriesData.innerHTML = 'Loading...';

    // 💡 Fetch data using AJAX/Fetch API
    fetch('get_calories.php?product_id=' + productId)
        .then(response => response.text())
        .then(data => {
            caloriesData.innerHTML = data;
        })
        .catch(error => {
            caloriesData.innerHTML = 'Error loading calories data.';
        });
}

function closeCaloriesModal() {
    document.getElementById('caloriesModal').style.display = 'none';
}

// Close modals if clicked outside
window.onclick = function(event) {
    const carbonModal = document.getElementById('carbonModal');
    const caloriesModal = document.getElementById('caloriesModal');
    if (event.target == carbonModal) {
        carbonModal.style.display = 'none';
    }
    if (event.target == caloriesModal) {
        caloriesModal.style.display = 'none';
    }
}
</script>

</body>
</html>