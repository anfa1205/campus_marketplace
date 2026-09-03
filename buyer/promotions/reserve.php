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

$promotionID =
    (int) ($_POST["promotion_id"] ?? 0);

$productID =
    (int) ($_POST["product_id"] ?? 0);

$paidQuantity =
    (int) ($_POST["quantity"] ?? 0);


if (
    $promotionID <= 0 ||
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


    /* GET OFFER + PRODUCT */

    $stmt = $pdo->prepare("
        SELECT
            pr.PromotionId,
            pr.OfferType,
            pr.DiscountValue,
            pr.BuyQuantity,
            pr.GetQuantity,
            pr.StartDate,
            pr.EndDate,
            pr.SellerId,

            p.ProductID,
            p.ProductName,
            p.Price,
            p.Stock,
            p.Status

        FROM promotion pr

        INNER JOIN applies_to a
            ON pr.PromotionId = a.promotionID

        INNER JOIN product p
            ON a.productID = p.ProductID

        WHERE pr.PromotionId = ?
          AND p.ProductID = ?
          AND pr.SellerId = p.SellerID
          AND NOW() BETWEEN pr.StartDate AND pr.EndDate

        FOR UPDATE
    ");

    $stmt->execute([
        $promotionID,
        $productID
    ]);

    $offer =
        $stmt->fetch();


    if (!$offer) {

        throw new Exception(
            "This offer is no longer active."
        );
    }


    if (
        strcasecmp(
            trim($offer["Status"]),
            "Available"
        ) !== 0
    ) {

        throw new Exception(
            "This product is currently unavailable."
        );
    }


    $stock =
        (int) $offer["Stock"];


    /* CALCULATE QUANTITY */

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


    if ($totalQuantity <= 0) {

        throw new Exception(
            "Invalid reservation quantity."
        );
    }


    /* CHECK EXISTING ACTIVE RESERVATIONS */

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

        WHERE r.sellerID = ?
          AND c.productID = ?

          AND r.Status IN
          (
              'Pending',
              'Accepted',
              'Ready for Pickup'
          )
    ");

    $stmt->execute([
        $offer["SellerId"],
        $productID
    ]);

    $reserved =
        (int) $stmt->fetchColumn();


    $remainingStock =
        $stock - $reserved;


    if (
        $totalQuantity >
        $remainingStock
    ) {

        throw new Exception(
            "Only " .
            max(0, $remainingStock) .
            " item(s) remain available."
        );
    }


    /* PRICE */

    $unitPrice =
        getOfferPrice(
            (float) $offer["Price"],
            $offer
        );


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


    /* RESERVATION */

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
            NULL,
            ?
        )
    ");

    $stmt->execute([
        $buyer["Phone"],
        $buyerID,
        $offer["SellerId"]
    ]);


    $reservationID =
        (int) $pdo->lastInsertId();


    /* CONTAINS */

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