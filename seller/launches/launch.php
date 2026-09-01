<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = $_SESSION["user_id"];

$launchID = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;

if ($launchID <= 0) {

    header(
        "Location: index.php?error=" .
        urlencode("Invalid product launch.")
    );

    exit;
}

try {

    $pdo->beginTransaction();


    /*
     * Get and lock the product launch.
     */

    $stmt = $pdo->prepare("
        SELECT *
        FROM product_launch
        WHERE LaunchID = ?
        AND sellerID = ?
        FOR UPDATE
    ");

    $stmt->execute([
        $launchID,
        $sellerID
    ]);

    $launch = $stmt->fetch();


    if (!$launch) {

        throw new Exception(
            "Product launch not found."
        );

    }


    /*
     * Prevent launching twice.
     */

    if ($launch["Status"] !== "Upcoming") {

        throw new Exception(
            "This product launch has already been launched."
        );

    }


    /*
     * Calculate the actual confirmed quantity
     * from all buyers.
     */

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(Quantity), 0) AS total
        FROM reserves
        WHERE launchID = ?
    ");

    $stmt->execute([
        $launchID
    ]);

    $result = $stmt->fetch();

    $totalReserved = (int)$result["total"];

    $required = (int)$launch["RequiredReservations"];


    /*
     * Target must be reached before launching.
     */

    if ($totalReserved < $required) {

        throw new Exception(
            "The required confirmation quantity has not been reached."
        );

    }


    /*
     * STEP 1
     * Create the new product.
     */

    $stmt = $pdo->prepare("
        INSERT INTO product
        (
            ProductName,
            Description,
            Category,
            Stock,
            Price,
            Status,
            SellerID
        )
        VALUES
        (
            ?, ?, ?, ?, ?, 'Available', ?
        )
    ");

    $stmt->execute([
        $launch["ProductName"],
        $launch["Description"],
        $launch["Category"],
        $totalReserved,
        $launch["Price"],
        $sellerID
    ]);

    $newProductID = $pdo->lastInsertId();


    /*
     * STEP 2
     * Automatically create Sales Announcement.
     *
     * Selling Date = Launch Date
     * Selling Time = Launch Time
     * Seller = same seller
     * Status = Available
     */

    $sellingDate = date(
        "Y-m-d",
        strtotime($launch["LaunchDate"])
    );

    $sellingTime = $launch["LaunchTime"];


    $stmt = $pdo->prepare("
        INSERT INTO sales_announcement
        (
            SellingTime,
            SellingDate,
            Status,
            SellerId
        )
        VALUES
        (
            ?, ?, 'Available', ?
        )
    ");

    $stmt->execute([
        $sellingTime,
        $sellingDate,
        $sellerID
    ]);

    $announcementID = $pdo->lastInsertId();


    /*
     * STEP 3
     * Connect the new Sales Announcement
     * with the new Product.
     *
     * Quantity = total confirmed quantity.
     */

    $stmt = $pdo->prepare("
        INSERT INTO includes
        (
            announcementID,
            productID,
            quantity
        )
        VALUES
        (
            ?, ?, ?
        )
    ");

    $stmt->execute([
        $announcementID,
        $newProductID,
        $totalReserved
    ]);


    /*
     * STEP 4
     * Mark Product Launch as Launched.
     */

    $stmt = $pdo->prepare("
        UPDATE product_launch
        SET
            CurrentReservation = ?,
            Status = 'Launched',
            productID = ?
        WHERE LaunchID = ?
        AND sellerID = ?
    ");

    $stmt->execute([
        $totalReserved,
        $newProductID,
        $launchID,
        $sellerID
    ]);


    /*
     * Everything succeeded.
     */

    $pdo->commit();


    header(
        "Location: index.php?success=" .
        urlencode(
            "Product launched and Sales Announcement created successfully."
        )
    );

    exit;


} catch (Exception $e) {

    /*
     * If anything fails, undo everything.
     */

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    header(
        "Location: index.php?error=" .
        urlencode($e->getMessage())
    );

    exit;

}
?>