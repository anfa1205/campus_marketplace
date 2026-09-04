<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;
}


$sellerID =
    (int) $_SESSION["user_id"];

$reservationID =
    (int) ($_POST["reservation_id"] ?? 0);

$newStatus =
    $_POST["status"] ?? "";


$allowedStatuses = [
    "Accepted",
    "Rejected",
    "Completed"
];


if (
    $reservationID <= 0 ||
    !in_array(
        $newStatus,
        $allowedStatuses,
        true
    )
) {

    header(
        "Location: index.php?error=" .
        urlencode("Invalid reservation action.")
    );

    exit;
}


try {

    $pdo->beginTransaction();


    /*
    GET RESERVATION
    */

    $stmt = $pdo->prepare("
        SELECT
            ReservationID,
            buyerID,
            sellerID,
            Status

        FROM reservation

        WHERE ReservationID = ?
          AND sellerID = ?

        FOR UPDATE
    ");

    $stmt->execute([
        $reservationID,
        $sellerID
    ]);

    $reservation =
        $stmt->fetch();


    if (!$reservation) {

        throw new Exception(
            "Reservation not found."
        );
    }


    $currentStatus =
        $reservation["Status"];


    /*STATUS FLOW*/

    if ($currentStatus === "Pending") {
        if (
            $newStatus !== "Accepted" &&
            $newStatus !== "Rejected"
        ) {
            throw new Exception(
                "Invalid reservation status change."
            );
        }
    } elseif (
        $currentStatus === "Accepted"
    ) {
        if (
            $newStatus !== "Completed"
        ) {
            throw new Exception(
                "Invalid reservation status change."
            );
        }
    } else {
        throw new Exception(
            "This reservation can no longer be changed."
        );
    }


    /*
    COMPLETE PURCHASE
    */

    if ($newStatus === "Completed") {


        $stmt = $pdo->prepare("
            SELECT
                c.productID,
                c.quantity,
                c.paidQuantity,
                c.freeQuantity,
                c.unitPrice,
                c.promotionID,

                p.Stock,
                p.ProductName,
                p.Price

            FROM contains c

            INNER JOIN product p
                ON c.productID =
                   p.ProductID

            WHERE c.reservationID = ?

            FOR UPDATE
        ");

        $stmt->execute([
            $reservationID
        ]);

        $items =
            $stmt->fetchAll();


        if (empty($items)) {

            throw new Exception(
                "No product was found for this reservation."
            );
        }


        /*
        CHECK STOCK
        */

        foreach ($items as $item) {

            $stock =
                (int) $item["Stock"];

            $quantity =
                (int) $item["quantity"];


            if ($stock < $quantity) {

                throw new Exception(
                    "Not enough stock for " .
                    $item["ProductName"] .
                    "."
                );
            }
        }


        /*
        CREATE PURCHASE
        */

        $stmt = $pdo->prepare("
            INSERT INTO purchase
            (
                PurchaseType,
                purchaseDate,
                status,
                sellerID,
                BuyerID,
                ReservationID
            )
            VALUES
            (
                'Online',
                NOW(),
                'Completed',
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $sellerID,
            $reservation["buyerID"],
            $reservationID
        ]);


        $purchaseID =
            (int) $pdo->lastInsertId();


        /*
        ADD PRODUCTS TO PURCHASE
        */

        foreach ($items as $item) {


            $paidQuantity =
                $item["paidQuantity"] !== null
                    ? (int) $item["paidQuantity"]
                    : (int) $item["quantity"];


            $freeQuantity =
                $item["freeQuantity"] !== null
                    ? (int) $item["freeQuantity"]
                    : 0;


            $unitPrice =
                $item["unitPrice"] !== null
                    ? (float) $item["unitPrice"]
                    : (float) $item["Price"];


            $promotionID =
                $item["promotionID"] !== null
                    ? (int) $item["promotionID"]
                    : null;


            /*
            PURCHASE RECORD
            */

            $stmt = $pdo->prepare("
                INSERT INTO has
                (
                    purchaseID,
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
                $purchaseID,
                $item["productID"],
                $item["quantity"],
                $paidQuantity,
                $freeQuantity,
                $unitPrice,
                $promotionID
            ]);


            /*
            REDUCE ACTUAL STOCK

            BOGO:
            Buy 2 Get 1
            quantity = 3
            Stock decreases by 3
            */

            $stmt = $pdo->prepare("
                UPDATE product

                SET Stock =
                    Stock - ?

                WHERE ProductID = ?
            ");

            $stmt->execute([
                $item["quantity"],
                $item["productID"]
            ]);
        }
    }


    /*UPDATE RESERVATION*/

    $stmt = $pdo->prepare("
        UPDATE reservation

        SET Status = ?

        WHERE ReservationID = ?
          AND sellerID = ?
    ");

    $stmt->execute([
        $newStatus,
        $reservationID,
        $sellerID
    ]);
    $pdo->commit();
    header(
        "Location: index.php?success=" .
        urlencode(
            "Reservation #" .
            $reservationID .
            " updated successfully."
        )
    );

    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        "Location: index.php?error=" .
        urlencode($e->getMessage())
    );

    exit;
}