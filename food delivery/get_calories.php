<?php
include 'components/connect.php';

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $stmt = $conn->prepare("SELECT calories, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>{$data['name']}</strong> contains approximately <b>{$data['calories']} kcal</b>.</p>";
    } else {
        echo "<p>Calories data not found for this product.</p>";
    }
} else {
    echo "<p>Invalid product ID.</p>";
}
?>
