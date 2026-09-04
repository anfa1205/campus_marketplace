<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$launchID =
    isset($_GET["id"])
    ? (int) $_GET["id"]
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
     * GET AND LOCK LAUNCH
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
     * PREVENT DOUBLE LAUNCH
     */

    if (
        $launch["Status"] !== "Upcoming"
    ) {

        throw new Exception(
            "This product launch has already been launched."
        );

    }


    /*
     * GET ACTUAL RESERVATION TOTAL
     */

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(
                SUM(Quantity),
                0
            )
        FROM reserves
        WHERE launchID = ?
    ");

    $stmt->execute([
        $launchID
    ]);

    $totalReserved =
        (int) $stmt->fetchColumn();


    $required =
        (int) $launch["RequiredReservations"];


    /*
     * REQUIRED RESERVATION TARGET
     * MUST BE REACHED
     */

    if (
        $totalReserved < $required
    ) {

        throw new Exception(
            "The required reservation quantity has not been reached yet."
        );

    }


    /*
     * STEP 1
     * CREATE ACTUAL PRODUCT
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


    $newProductID =
        (int) $pdo->lastInsertId();


    /*
     * STEP 2
     * CREATE SALES ANNOUNCEMENT
     */

    $sellingDate =
        date(
            "Y-m-d",
            strtotime(
                $launch["LaunchDate"]
            )
        );


    $sellingTime =
        $launch["LaunchTime"];


    $campusLocation =
        $launch["CampusLocation"];


    $stmt = $pdo->prepare("
        INSERT INTO sales_announcement
        (
            SellingTime,
            SellingDate,
            CampusLocation,
            Status,
            SellerId
        )
        VALUES
        (
            ?, ?, ?, 'Available', ?
        )
    ");

    $stmt->execute([
        $sellingTime,
        $sellingDate,
        $campusLocation,
        $sellerID
    ]);


    $announcementID =
        (int) $pdo->lastInsertId();


    /*
     * STEP 3
     * CONNECT PRODUCT TO SALES ANNOUNCEMENT
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
     * MARK PRODUCT LAUNCH AS LAUNCHED
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
     * EVERYTHING SUCCESSFUL
     */

    $pdo->commit();


    header(
        "Location: index.php?success=" .
        urlencode(
            "Product launched successfully and added to Sales Announcements."
        )
    );

    exit;


} catch (Exception $e) {


    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();

    }


    header(
        "Location: index.php?error=" .
        urlencode(
            $e->getMessage()
        )
    );

    exit;

}

?>