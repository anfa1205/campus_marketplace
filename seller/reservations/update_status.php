<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$sellerID = (int) $_SESSION["user_id"];

$reservationID = isset($_POST["reservation_id"])
    ? (int) $_POST["reservation_id"]
    : 0;

$newStatus = $_POST["status"] ?? "";


/*
|--------------------------------------------------------------------------
| ONLY THESE STATUSES ARE ALLOWED
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    "Accepted",
    "Rejected",
    "Completed"
];

if (
    $reservationID <= 0 ||
    !in_array($newStatus, $allowedStatuses, true)
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
    |--------------------------------------------------------------------------
    | GET RESERVATION
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            ReservationID,
            Status,
            sellerID
        FROM reservation
        WHERE ReservationID = :reservationID
          AND sellerID = :sellerID
        FOR UPDATE
    ");

    $stmt->execute([
        ":reservationID" => $reservationID,
        ":sellerID" => $sellerID
    ]);

    $reservation = $stmt->fetch();


    if (!$reservation) {

        throw new Exception(
            "Reservation not found."
        );
    }


    $currentStatus = $reservation["Status"];


    /*
    |--------------------------------------------------------------------------
    | VALID FLOW
    |
    | Pending  → Accepted
    | Pending  → Rejected
    |
    | Accepted → Completed
    |--------------------------------------------------------------------------
    */

    if ($currentStatus === "Pending") {

        if (
            $newStatus !== "Accepted" &&
            $newStatus !== "Rejected"
        ) {

            throw new Exception(
                "Invalid reservation status change."
            );
        }

    } elseif ($currentStatus === "Accepted") {

        if ($newStatus !== "Completed") {

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
    |--------------------------------------------------------------------------
    | REDUCE PRODUCT STOCK ONLY WHEN COMPLETED
    |--------------------------------------------------------------------------
    */

    if ($newStatus === "Completed") {

        $stmt = $pdo->prepare("
            SELECT
                c.productID,
                c.quantity,
                p.Stock,
                p.ProductName

            FROM contains c

            INNER JOIN product p
                ON c.productID = p.ProductID

            WHERE c.reservationID = :reservationID

            FOR UPDATE
        ");

        $stmt->execute([
            ":reservationID" => $reservationID
        ]);

        $items = $stmt->fetchAll();


        if (empty($items)) {

            throw new Exception(
                "No product was found for this reservation."
            );
        }


        foreach ($items as $item) {

            $stock = (int) $item["Stock"];
            $quantity = (int) $item["quantity"];


            if ($stock < $quantity) {

                throw new Exception(
                    "Not enough stock for " .
                    $item["ProductName"] .
                    "."
                );
            }


            $stmt = $pdo->prepare("
                UPDATE product

                SET Stock = Stock - :quantity

                WHERE ProductID = :productID
            ");

            $stmt->execute([
                ":quantity" => $quantity,
                ":productID" => $item["productID"]
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE RESERVATION STATUS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE reservation

        SET Status = :status

        WHERE ReservationID = :reservationID
          AND sellerID = :sellerID
    ");

    $stmt->execute([
        ":status" => $newStatus,
        ":reservationID" => $reservationID,
        ":sellerID" => $sellerID
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

?>