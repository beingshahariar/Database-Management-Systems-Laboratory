<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    // Check if the user is soft deleted
    $check_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? AND deleted_at IS NULL");
    $check_user->execute([$user_id]);
    if($check_user->rowCount() == 0){
        
        session_unset();
        session_destroy();
        $user_id = '';
    }

}else{
    $user_id = '';
}

include 'components/add_cart.php'; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>home</title>

    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="hero">

    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide slide">
                <div class="content">
                    <span>Order Online</span>
                    <h3>delicious pizza</h3>
                    <a href="menu.php" class="btn">see menus</a>
                </div>
                <div class="image">
                    <img src="images/home-img-1.png" alt="">
                </div>
            </div>

            <div class="swiper-slide slide">
                <div class="content">
                    <span>Order Online</span>
                    <h3>chezzy hamburger</h3>
                    <a href="menu.php" class="btn">see menus</a>
                </div>
                <div class="image">
                    <img src="images/home-img-2.png" alt="">
                </div>
            </div>

            <div class="swiper-slide slide">
                <div class="content">
                    <span>Order Online</span>
                    <h3>Roasted chicken</h3>
                    <a href="menu.php" class="btn">see menus</a>
                </div>
                <div class="image">
                    <img src="images/home-img-3.png" alt="">
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
    </div>

</section>

<section class="category">
    <h1 class="title">food category</h1>
    <div class="box-container">
        <a href="category.php?category=fast food" class="box">
            <img src="images/cat-1.png" alt="">
            <h3>fast food</h3>
        </a>
        <a href="category.php?category=main dish" class="box">
            <img src="images/cat-2.png" alt="">
            <h3>main dishes</h3>
        </a>
        <a href="category.php?category=drinks" class="box">
            <img src="images/cat-3.png" alt="">
            <h3>drinks</h3>
        </a>
        <a href="category.php?category=desserts" class="box">
            <img src="images/cat-4.png" alt="">
            <h3>desserts</h3>
        </a>
    </div>
</section>

<section class="products">

    <h1 class="title">latest dishes</h1>

    <div class="box-container">

        <?php
            
            $select_products = $conn->prepare("
                SELECT p.* FROM `products` p
                WHERE p.deleted_at IS NULL LIMIT 6
            ");
            $select_products->execute();
            if($select_products->rowCount() > 0){
                while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
        ?>
        <form action="" method="post" class="box">
            <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
            <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
            <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
            <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
            <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
            
            <div class="name">
                <?= htmlspecialchars($fetch_products['name']); ?>
                <?php 
                // Green Tag Display 
                if(isset($fetch_products['is_green']) && $fetch_products['is_green'] == 1): 
                ?>
                    <span style="color: green; margin-left: 5px; font-size: 2rem;">(🌿)</span>
                <?php endif; ?>
            </div>

            <div class="flex">
                <div class="price"><span>$</span><?= $fetch_products['price']; ?></div>
                <input type="number" name="qty" class="qty" id="qty_<?= $fetch_products['id']; ?>" min="1" max="99" value="1" maxlength="2">
            </div>
        </form>
        <?php
                }
            }else{
                echo '<p class="empty">no products added yet!</p>';
            }
        ?>

    </div>

    <div class="more-btn">
        <a href="menu.php" class="btn">view all</a>
    </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
var swiper = new Swiper(".hero-slider", {
    loop:true,
    grabCursor: true,
    effect: "flip",
    pagination: {
        el: ".swiper-pagination",
        clickable:true,
    },
});

</script>

</body>
</html>