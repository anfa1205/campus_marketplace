<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$seller_id = (int) $_SESSION["user_id"];

$product_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($product_id > 0) {

    try {

        $pdo->beginTransaction();

        /*
         * Find launches belonging to this product
         */
        $stmt = $pdo->prepare("
            SELECT LaunchID
            FROM product_launch
            WHERE productID = ?
            AND sellerID = ?
        ");

        $stmt->execute([
            $product_id,
            $seller_id
        ]);

        $launches = $stmt->fetchAll(PDO::FETCH_COLUMN);


        /*
         * Delete reservations for those launches
         */
        if (!empty($launches)) {

            $placeholders = implode(
                ",",
                array_fill(0, count($launches), "?")
            );

            $stmt = $pdo->prepare("
                DELETE FROM reserves
                WHERE launchID IN ($placeholders)
            ");

            $stmt->execute($launches);
        }


        /*
         * Delete product launches
         */
        $stmt = $pdo->prepare("
            DELETE FROM product_launch
            WHERE productID = ?
            AND sellerID = ?
        ");

        $stmt->execute([
            $product_id,
            $seller_id
        ]);


        /*
         * Delete the product
         */
        $stmt = $pdo->prepare("
            DELETE FROM PRODUCT
            WHERE ProductID = ?
            AND SellerID = ?
        ");

        $stmt->execute([
            $product_id,
            $seller_id
        ]);


        $pdo->commit();

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}


header("Location: index.php");
exit;

?>