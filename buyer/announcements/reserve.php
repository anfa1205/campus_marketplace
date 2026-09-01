<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$buyerID = (int) $_SESSION["user_id"];

$announcementID = isset($_POST["announcement_id"])
    ? (int) $_POST["announcement_id"]
    : 0;

$productID = isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;

$quantity = isset($_POST["quantity"])
    ? (int) $_POST["quantity"]
    : 0;

if ($announcementID <= 0 || $productID <= 0 || $quantity <= 0) {
    header("Location: index.php?error=" . urlencode("Invalid reservation information."));
    exit;
}

try {

    $pdo->beginTransaction();

    /*
    ----------------------------------------------------
    1. GET SALES ANNOUNCEMENT
    ----------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            AnnouncementId,
            SellerId,
            SellingDate,
            SellingTime,
            CampusLocation,
            Status
        FROM sales_announcement
        WHERE AnnouncementId = :announcementID
        FOR UPDATE
    ");

    $stmt->execute([
        ":announcementID" => $announcementID
    ]);

    $announcement = $stmt->fetch();

    if (!$announcement) {
        throw new Exception("Sales announcement not found.");
    }

    $sellerID = (int) $announcement["SellerId"];


    /*
    ----------------------------------------------------
    2. GET PRODUCT INCLUDED IN THIS ANNOUNCEMENT
    ----------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            i.productID,
            i.quantity AS SaleQuantity,
            p.ProductID,
            p.ProductName,
            p.Stock,
            p.Status
        FROM includes i
        INNER JOIN product p
            ON i.productID = p.ProductID
        WHERE i.announcementID = :announcementID
          AND i.productID = :productID
        FOR UPDATE
    ");

    $stmt->execute([
        ":announcementID" => $announcementID,
        ":productID" => $productID
    ]);

    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception(
            "This product is not included in this sales announcement."
        );
    }


    /*
    ----------------------------------------------------
    3. CHECK PRODUCT STATUS
    ----------------------------------------------------
    */

    if ($product["Status"] === "Unavailable") {
        throw new Exception("This product is currently unavailable.");
    }

    $stock = (int) $product["Stock"];

    if ($stock <= 0) {
        throw new Exception("This product is out of stock.");
    }


    /*
    ----------------------------------------------------
    4. GET SALE QUANTITY
    ----------------------------------------------------
    */

    $saleQuantity = (int) $product["SaleQuantity"];

    if ($saleQuantity <= 0) {
        throw new Exception(
            "This product is not available for reservation."
        );
    }


    /*
    ----------------------------------------------------
    5. CHECK ALREADY RESERVED QUANTITY
    ----------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.quantity), 0) AS reservedQuantity
        FROM reservation r
        INNER JOIN contains c
            ON r.ReservationID = c.reservationID
        WHERE r.announcementID = :announcementID
          AND c.productID = :productID
          AND r.Status <> 'Rejected'
    ");

    $stmt->execute([
        ":announcementID" => $announcementID,
        ":productID" => $productID
    ]);

    $reservedData = $stmt->fetch();

    $alreadyReserved = (int) $reservedData["reservedQuantity"];


    /*
    ----------------------------------------------------
    6. CALCULATE REMAINING QUANTITY
    ----------------------------------------------------
    */

    $remainingSaleQuantity =
        $saleQuantity - $alreadyReserved;

    if ($remainingSaleQuantity <= 0) {
        throw new Exception(
            "No reservation quantity remains for this product."
        );
    }


    /*
    ----------------------------------------------------
    7. LIMIT RESERVATION BY STOCK AND SALE QUANTITY
    ----------------------------------------------------
    */

    $maximumReservation = min(
        $stock,
        $remainingSaleQuantity
    );

    if ($quantity > $maximumReservation) {

        throw new Exception(
            "You can reserve a maximum of "
            . $maximumReservation
            . " item(s)."
        );
    }


    /*
    ----------------------------------------------------
    8. GET BUYER
    ----------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            BuyerID,
            Name,
            Phone,
            Email
        FROM buyer
        WHERE BuyerID = :buyerID
        FOR UPDATE
    ");

    $stmt->execute([
        ":buyerID" => $buyerID
    ]);

    $buyer = $stmt->fetch();

    if (!$buyer) {
        throw new Exception("Buyer account not found.");
    }


    /*
    ----------------------------------------------------
    9. CREATE RESERVATION
    ----------------------------------------------------
    */

    $reservationTarget = $buyer["Phone"];

    $stmt = $pdo->prepare("
        INSERT INTO reservation
        (
            ReservationDate,
            ReservationTarget,
            Status,
            buyerID,
            announcementID,
            sellerID
        )
        VALUES
        (
            NOW(),
            :reservationTarget,
            'Pending',
            :buyerID,
            :announcementID,
            :sellerID
        )
    ");

    $stmt->execute([
        ":reservationTarget" => $reservationTarget,
        ":buyerID" => $buyerID,
        ":announcementID" => $announcementID,
        ":sellerID" => $sellerID
    ]);

    $reservationID = (int) $pdo->lastInsertId();

    if ($reservationID <= 0) {
        throw new Exception(
            "Reservation could not be created."
        );
    }


    /*
    ----------------------------------------------------
    10. ADD PRODUCT TO CONTAINS TABLE
    ----------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO contains
        (
            reservationID,
            productID,
            quantity
        )
        VALUES
        (
            :reservationID,
            :productID,
            :quantity
        )
    ");

    $stmt->execute([
        ":reservationID" => $reservationID,
        ":productID" => $productID,
        ":quantity" => $quantity
    ]);


    /*
    ----------------------------------------------------
    11. FINISH TRANSACTION
    ----------------------------------------------------
    */

    $pdo->commit();


    /*
    ----------------------------------------------------
    12. RETURN TO SALES ANNOUNCEMENTS
    ----------------------------------------------------
    */

    header(
        "Location: index.php?success="
        . urlencode(
            "Reservation #"
            . $reservationID
            . " created successfully."
        )
    );

    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        "Location: index.php?error="
        . urlencode($e->getMessage())
    );

    exit;
}

?>