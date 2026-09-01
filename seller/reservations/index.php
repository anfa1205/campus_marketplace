```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

include "../../includes/header.php";

$seller_id = $_SESSION["user_id"];


/* Get reservations for this seller */

$stmt = $pdo->prepare(
    "SELECT
        r.ReservationID,
        r.ReservationDate,
        r.ReservationTarget,
        r.Status,
        r.quantity,
        r.contactNumber,

        b.Name AS BuyerName,
        b.Email AS BuyerEmail,
        b.Phone AS BuyerPhone,

        p.ProductName,
        p.Price

     FROM reservation r

     INNER JOIN BUYER b
        ON r.buyerID = b.BuyerID

     INNER JOIN PRODUCT p
        ON r.productID = p.ProductID

     WHERE r.sellerID = ?

     ORDER BY r.ReservationDate DESC"
);

$stmt->execute([
    $seller_id
]);

$reservations = $stmt->fetchAll();

?>


<div class="card">

    <h1>
        Reservation & Pickup Management
    </h1>


    <?php if (count($reservations) === 0): ?>

        <p>
            No reservations found.
        </p>


    <?php else: ?>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            Reservation ID
                        </th>

                        <th>
                            Buyer
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Contact
                        </th>

                        <th>
                            Reservation Date
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach (
                    $reservations
                    as $reservation
                ): ?>

                    <tr>

                        <td>
                            #<?= (int)
                                $reservation[
                                    "ReservationID"
                                ] ?>
                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $reservation[
                                    "BuyerName"
                                ]
                            ) ?>

                            <br>

                            <small>
                                <?= htmlspecialchars(
                                    $reservation[
                                        "BuyerEmail"
                                    ]
                                ) ?>
                            </small>

                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation[
                                    "ProductName"
                                ]
                            ) ?>
                        </td>


                        <td>
                            <?= (int)
                                $reservation[
                                    "quantity"
                                ] ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation[
                                    "contactNumber"
                                ]
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation[
                                    "ReservationDate"
                                ]
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation[
                                    "Status"
                                ]
                            ) ?>
                        </td>


                        <td>


                            <?php if (
                                $reservation[
                                    "Status"
                                ] === "Pending"
                            ): ?>


                                <a
                                    href="update_status.php?id=<?= (int) $reservation["ReservationID"] ?>&status=Accepted"
                                    class="btn"
                                >
                                    Accept
                                </a>


                                <a
                                    href="update_status.php?id=<?= (int) $reservation["ReservationID"] ?>&status=Rejected"
                                    class="btn"
                                >
                                    Reject
                                </a>


                            <?php elseif (
                                $reservation[
                                    "Status"
                                ] === "Accepted"
                            ): ?>


                                <a
                                    href="update_status.php?id=<?= (int) $reservation["ReservationID"] ?>&status=Ready%20for%20Pickup"
                                    class="btn"
                                >
                                    Ready for Pickup
                                </a>


                            <?php elseif (
                                $reservation[
                                    "Status"
                                ] === "Ready for Pickup"
                            ): ?>


                                <a
                                    href="update_status.php?id=<?= (int) $reservation["ReservationID"] ?>&status=Completed"
                                    class="btn"
                                >
                                    Completed
                                </a>


                            <?php else: ?>

                                —

                            <?php endif; ?>


                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>


    <br>


    <a
        href="../dashboard.php"
        class="btn"
    >
        Back to Dashboard
    </a>

</div>


<?php

include "../../includes/footer.php";

?>
```
+