<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";
require_once "../../includes/offer_helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;
}


$buyerID =
    (int) $_SESSION["user_id"];

$announcementID =
    (int) ($_POST["announcement_id"] ?? 0);

$productID =
    (int) ($_POST["product_id"] ?? 0);

$paidQuantity =
    (int) ($_POST["quantity"] ?? 0);


if (
    $announcementID <= 0 ||
    $productID <= 0 ||
    $paidQuantity <= 0
) {

    header(
        "Location: index.php?error=" .
        urlencode(
            "Invalid reservation information."
        )
    );

    exit;
}


try {

    $pdo->beginTransaction();


    /* ANNOUNCEMENT */

    $stmt = $pdo->prepare("
        SELECT
            AnnouncementId,
            SellerId,
            SellingDate,
            SellingTime,
            CampusLocation,
            Status

        FROM sales_announcement

        WHERE AnnouncementId = ?

        FOR UPDATE
    ");

    $stmt->execute([
        $announcementID
    ]);

    $announcement =
        $stmt->fetch();


    if (!$announcement) {

        throw new Exception(
            "Sales announcement not found."
        );
    }


    $sellerID =
        (int) $announcement["SellerId"];


    /* PRODUCT */

    $stmt = $pdo->prepare("
        SELECT
            i.productID,
            i.quantity AS SaleQuantity,

            p.ProductID,
            p.ProductName,
            p.Price,
            p.Stock,
            p.Status

        FROM includes i

        INNER JOIN product p
            ON i.productID = p.ProductID

        WHERE i.announcementID = ?
          AND i.productID = ?

        FOR UPDATE
    ");

    $stmt->execute([
        $announcementID,
        $productID
    ]);

    $product =
        $stmt->fetch();


    if (!$product) {

        throw new Exception(
            "This product is not included in this sales announcement."
        );
    }


    if (
        strcasecmp(
            trim($product["Status"]),
            "Available"
        ) !== 0
    ) {

        throw new Exception(
            "This product is currently unavailable."
        );
    }


    $stock =
        (int) $product["Stock"];

    $saleQuantity =
        (int) $product["SaleQuantity"];


    if ($stock <= 0) {

        throw new Exception(
            "This product is out of stock."
        );
    }


    if ($saleQuantity <= 0) {

        throw new Exception(
            "No quantity is available in this announcement."
        );
    }


    /* ALREADY RESERVED */

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(
                SUM(c.quantity),
                0
            )

        FROM reservation r

        INNER JOIN contains c
            ON r.ReservationID =
               c.reservationID

        WHERE r.announcementID = ?
          AND c.productID = ?

          AND r.Status <> 'Rejected'
    ");

    $stmt->execute([
        $announcementID,
        $productID
    ]);

    $alreadyReserved =
        (int) $stmt->fetchColumn();


    $remainingSale =
        $saleQuantity -
        $alreadyReserved;


    if ($remainingSale <= 0) {

        throw new Exception(
            "No reservation quantity remains."
        );
    }


    /* ACTIVE OFFER */

    $offer =
        getActiveOffer(
            $pdo,
            $productID
        );


    $calculated =
        calculateOfferQuantity(
            $paidQuantity,
            $offer
        );


    $freeQuantity =
        (int)
        $calculated["freeQuantity"];

    $totalQuantity =
        (int)
        $calculated["totalQuantity"];


    $maximumUnits =
        min(
            $stock,
            $remainingSale
        );


    if (
        $totalQuantity >
        $maximumUnits
    ) {

        throw new Exception(
            "You can reserve a maximum of " .
            $maximumUnits .
            " total item(s), including free item(s)."
        );
    }


    /* PRICE */

    $unitPrice =
        getOfferPrice(
            (float) $product["Price"],
            $offer
        );


    $promotionID =
        $offer
            ? (int) $offer["PromotionId"]
            : null;


    /* BUYER */

    $stmt = $pdo->prepare("
        SELECT
            BuyerID,
            Phone

        FROM buyer

        WHERE BuyerID = ?

        FOR UPDATE
    ");

    $stmt->execute([
        $buyerID
    ]);

    $buyer =
        $stmt->fetch();


    if (!$buyer) {

        throw new Exception(
            "Buyer account not found."
        );
    }


    /* CREATE RESERVATION */

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
            ?,
            'Pending',
            ?,
            ?,
            ?
        )
    ");
    $stmt->execute([
        $buyer["Phone"],
        $buyerID,
        $announcementID,
        $sellerID
    ]);
    $reservationID =
        (int) $pdo->lastInsertId();


    /* SAVE CONTAINS */

    $stmt = $pdo->prepare("
        INSERT INTO contains
        (
            reservationID,
            productID,
            quantity,
            paidQuantity,
            freeQuantity,
            unitPrice,
            promotionID
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $reservationID,
        $productID,
        $totalQuantity,
        $paidQuantity,
        $freeQuantity,
        $unitPrice,
        $promotionID
    ]);


    $pdo->commit();


    header(
        "Location: index.php?success=" .
        urlencode(
            "Reservation #" .
            $reservationID .
            " created successfully. Waiting for seller confirmation."
        )
    );

    exit;


} catch (Exception $e) {

    if ($pdo->inTransaction()) {

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