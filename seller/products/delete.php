<?php

require_once "../../config/database.php";

require_once "../../includes/seller_check.php";


$seller_id =
    $_SESSION["user_id"];


$product_id =
    $_GET["id"] ?? "";


if ($product_id !== "") {


    $stmt = $pdo->prepare(
        "DELETE FROM PRODUCT
         WHERE ProductID = ?
         AND SellerID = ?"
    );


    $stmt->execute([
        $product_id,
        $seller_id
    ]);

}


header(
    "Location: index.php"
);

exit;

?>