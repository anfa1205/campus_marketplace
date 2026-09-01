```php
<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";

$buyer_id = $_SESSION["user_id"];

$success =
    isset($_GET["success"]);

$error =
    $_GET["error"] ?? "";


/*
    Get all reservations made by
    this buyer.

    A buyer can have reservations
    from many different sellers
    and announcements.
*/

$stmt = $pdo->prepare(
    "SELECT
        r.ReservationID,
        r.ReservationDate,
        r.ReservationTarget,
        r.Status,

        p.ProductName,
        p.Price,

        c.quantity,

        sa.SellingDate,
        sa.SellingTime,
        sa.CampusLocation,

        s.bussinessName

     FROM reservation r

     INNER JOIN contains c
        ON r.ReservationID =
           c.reservationID

     INNER JOIN product p
        ON c.productID =
           p.ProductID

     INNER JOIN sales_announcement sa
        ON r.announcementID =
           sa.AnnouncementId

     INNER JOIN seller s
        ON r.sellerID =
           s.sellerID

     WHERE r.buyerID = ?

     ORDER BY
        r.ReservationDate DESC"
);

$stmt->execute([
    $buyer_id
]);

$reservations =
    $stmt->fetchAll();

?>


<div class="seller-dashboard">


    <div class="academic-decoration decoration-top-left">
        <span>✦</span>
        <span>⌁</span>
    </div>

    <div class="academic-decoration decoration-bottom-right">
        <span>✦</span>
        <span>⌁</span>
    </div>


    <div class="dashboard-intro">

        <p class="dashboard-label">
            RESERVATIONS
        </p>

        <h1>
            My Reservations
        </h1>

        <div class="title-line"></div>

    </div>


    <?php if ($success): ?>

        <div
            class="form-error"
            style="
                background:#EEE8F1;
                border-color:var(--border);
                color:var(--purple);
            "
        >
            Reservation submitted successfully.
            Waiting for the seller to confirm.
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="form-error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <?php if (count($reservations) === 0): ?>


        <div class="card">

            <h2>
                No Reservations Yet
            </h2>

            <br>

            <p>
                You have not made any reservations.
            </p>

        </div>


    <?php else: ?>


        <div class="product-table-container">

            <table class="product-table">

                <thead>

                    <tr>

                        <th>
                            Reservation
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Seller
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Pickup Details
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


                    <?php foreach ($reservations as $reservation): ?>


                        <tr>


                            <td>

                                <strong>
                                    #<?= (int)
                                        $reservation[
                                            "ReservationID"
                                        ] ?>
                                </strong>

                                <br>

                                <small>
                                    <?= htmlspecialchars(
                                        $reservation[
                                            "ReservationDate"
                                        ]
                                    ) ?>
                                </small>

                            </td>


                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $reservation[
                                            "ProductName"
                                        ]
                                    ) ?>
                                </strong>

                                <br>

                                ৳<?= number_format(
                                    $reservation["Price"],
                                    2
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $reservation[
                                        "bussinessName"
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
                                        "SellingDate"
                                    ]
                                ) ?>

                            </td>


                            <td>


                                <?php if (
                                    in_array(
                                        $reservation["Status"],
                                        [
                                            "Accepted",
                                            "Ready for Pickup",
                                            "Completed"
                                        ],
                                        true
                                    )
                                ): ?>


                                    <strong>
                                        <?= htmlspecialchars(
                                            $reservation[
                                                "SellingDate"
                                            ]
                                        ) ?>
                                    </strong>

                                    <br>

                                    <?= htmlspecialchars(
                                        $reservation[
                                            "SellingTime"
                                        ]
                                    ) ?>

                                    <br>

                                    <?= htmlspecialchars(
                                        $reservation[
                                            "CampusLocation"
                                        ]
                                    ) ?>


                                <?php elseif (
                                    $reservation["Status"]
                                    === "Rejected"
                                ): ?>


                                    <span>
                                        Reservation rejected
                                    </span>


                                <?php else: ?>


                                    <span>
                                        Waiting for seller confirmation
                                    </span>


                                <?php endif; ?>


                            </td>


                            <td>


                                <?php

                                $status_class =
                                    strtolower(
                                        str_replace(
                                            " ",
                                            "-",
                                            $reservation["Status"]
                                        )
                                    );

                                ?>


                                <span
                                    class="status-badge <?= $status_class ?>"
                                >
                                    <?= htmlspecialchars(
                                        $reservation["Status"]
                                    ) ?>
                                </span>


                            </td>


                            <td>


                                <?php if (
                                    $reservation["Status"]
                                    === "Pending"
                                ): ?>


                                    <a
                                        href="delete.php?id=<?= (int) $reservation["ReservationID"] ?>"
                                        class="delete-button"
                                        onclick="return confirm('Cancel this reservation?');"
                                    >
                                        Cancel
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


</div>


<?php

include "../../includes/footer.php";

?>
```
