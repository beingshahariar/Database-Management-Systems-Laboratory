<?php
include 'components/connect.php';

if(isset($_GET['product_id'])){
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT carbon_value FROM carbon_impact WHERE product_id = ?");
    $stmt->execute([$product_id]);

    if($stmt->rowCount() > 0){
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Carbon Impact: <strong>{$data['carbon_value']} kg CO₂</strong></p>";
    } else {
        echo "<p>No carbon impact data available for this product.</p>";
    }
}
?>
