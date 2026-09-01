```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$seller_id = $_SESSION["user_id"];

$reservation_id =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;

$status =
    trim($_GET["status"] ?? "");


/* Allowed statuses */

$allowed_statuses = [
    "Accepted",
    "Rejected",
    "Ready for Pickup",
    "Completed"
];


if (
    $reservation_id > 0 &&
    in_array($status, $allowed_statuses, true)
) {

    /*
     * Get the reservation and make sure
     * it belongs to the logged-in seller.
     */

    $stmt = $pdo->prepare(
        "SELECT
            ReservationID,
            Status,
            quantity,
            productID
         FROM reservation
         WHERE ReservationID = ?
         AND sellerID = ?"
    );

    $stmt->execute([
        $reservation_id,
        $seller_id
    ]);

    $reservation = $stmt->fetch();


    if ($reservation) {

        $current_status =
            $reservation["Status"];

        $valid_transition = false;


        /* Pending → Accepted */

        if (
            $current_status === "Pending" &&
            $status === "Accepted"
        ) {

            $valid_transition = true;

        }


        /* Pending → Rejected */

        elseif (
            $current_status === "Pending" &&
            $status === "Rejected"
        ) {

            $valid_transition = true;

        }


        /* Accepted → Ready for Pickup */

        elseif (
            $current_status === "Accepted" &&
            $status === "Ready for Pickup"
        ) {

            $valid_transition = true;

        }


        /* Ready for Pickup → Completed */

        elseif (
            $current_status === "Ready for Pickup" &&
            $status === "Completed"
        ) {

            $valid_transition = true;

        }


        /*
         * Update the reservation.
         */

        if ($valid_transition) {

            /*
             * When a reservation is accepted,
             * reduce the product stock.
             */

            if (
                $current_status === "Pending" &&
                $status === "Accepted"
            ) {

                $stock_stmt = $pdo->prepare(
                    "UPDATE PRODUCT
                     SET Stock = Stock - ?
                     WHERE ProductID = ?
                     AND Stock >= ?"
                );

                $stock_stmt->execute([
                    $reservation["quantity"],
                    $reservation["productID"],
                    $reservation["quantity"]
                ]);


                /*
                 * Only accept the reservation
                 * if enough stock exists.
                 */

                if (
                    $stock_stmt->rowCount() === 1
                ) {

                    $update_stmt = $pdo->prepare(
                        "UPDATE reservation
                         SET Status = ?
                         WHERE ReservationID = ?
                         AND sellerID = ?"
                    );

                    $update_stmt->execute([
                        $status,
                        $reservation_id,
                        $seller_id
                    ]);

                }

            }

            else {

                $update_stmt = $pdo->prepare(
                    "UPDATE reservation
                     SET Status = ?
                     WHERE ReservationID = ?
                     AND sellerID = ?"
                );

                $update_stmt->execute([
                    $status,
                    $reservation_id,
                    $seller_id
                ]);

            }

        }

    }

}


header(
    "Location: index.php"
);

exit;

?>
```
