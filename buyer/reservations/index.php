<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyerID = (int) $_SESSION["user_id"];

$success =
    isset($_GET["success"])
        ? $_GET["success"]
        : "";

$error =
    isset($_GET["error"])
        ? $_GET["error"]
        : "";

$stmt = $pdo->prepare("
    SELECT

        r.ReservationID,
        r.ReservationDate,
        r.ReservationTarget,
        r.Status,

        p.ProductID,
        p.ProductName,
        p.Price,

        s.sellerID,
        s.Name AS SellerName,
        s.bussinessName,

        c.quantity,
        c.paidQuantity,
        c.freeQuantity,
        c.unitPrice,
        c.promotionID,

        pr.OfferType,
        pr.DiscountValue,
        pr.BuyQuantity,
        pr.GetQuantity,

        sa.SellingDate,
        sa.SellingTime,
        sa.CampusLocation,

        pu.PurchaseID,

        f.FeedbackID

    FROM reservation r

    INNER JOIN contains c
        ON r.ReservationID = c.reservationID

    INNER JOIN product p
        ON c.productID = p.ProductID

    INNER JOIN seller s
        ON r.sellerID = s.sellerID

    LEFT JOIN promotion pr
        ON c.promotionID = pr.PromotionId

    LEFT JOIN sales_announcement sa
        ON r.announcementID = sa.AnnouncementId

    LEFT JOIN purchase pu
        ON pu.ReservationID = r.ReservationID

    LEFT JOIN feedback f
        ON f.purchaseID = pu.PurchaseID
       AND f.buyerID = r.buyerID

    WHERE r.buyerID = ?

    ORDER BY r.ReservationID DESC
");

$stmt->execute([$buyerID]);

$reservations = $stmt->fetchAll();

include "../../includes/header.php";

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


    <?php if ($success !== ""): ?>

        <div
            class="form-error"
            style="
                background:#EEE8F1;
                border-color:var(--border);
                color:var(--purple);
            "
        >
            <?= htmlspecialchars($success) ?>
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
                            Price
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

                        <?php

                        $quantity =
                            (int)
                            $reservation["quantity"];

                        $paidQuantity =
                            $reservation["paidQuantity"] !== null
                                ? (int)
                                    $reservation["paidQuantity"]
                                : $quantity;

                        $freeQuantity =
                            $reservation["freeQuantity"] !== null
                                ? (int)
                                    $reservation["freeQuantity"]
                                : 0;

                        $unitPrice =
                            $reservation["unitPrice"] !== null
                                ? (float)
                                    $reservation["unitPrice"]
                                : (float)
                                    $reservation["Price"];

                        $totalAmount =
                            $paidQuantity *
                            $unitPrice;

                        $statusClass =
                            strtolower(
                                str_replace(
                                    " ",
                                    "-",
                                    $reservation["Status"]
                                )
                            );

                        ?>

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

                                <?php if (
                                    $reservation[
                                        "promotionID"
                                    ] !== null
                                ): ?>

                                    <?php if (
                                        $reservation[
                                            "OfferType"
                                        ] === "Percentage"
                                    ): ?>

                                        <span style="
                                            color:var(--purple);
                                            font-weight:700;
                                        ">
                                            <?= number_format(
                                                (float)
                                                $reservation[
                                                    "DiscountValue"
                                                ],
                                                0
                                            ) ?>% OFF
                                        </span>

                                    <?php elseif (
                                        $reservation[
                                            "OfferType"
                                        ] === "BuyXGetY"
                                    ): ?>

                                        <span style="
                                            color:var(--purple);
                                            font-weight:700;
                                        ">
                                            Buy
                                            <?= (int)
                                                $reservation[
                                                    "BuyQuantity"
                                                ] ?>

                                            Get

                                            <?= (int)
                                                $reservation[
                                                    "GetQuantity"
                                                ] ?>
                                        </span>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $reservation[
                                        "bussinessName"
                                    ]
                                ) ?>

                                <br>

                                <small>
                                    <?= htmlspecialchars(
                                        $reservation[
                                            "SellerName"
                                        ]
                                    ) ?>
                                </small>

                            </td>


                            <td>

                                <?php if (
                                    $freeQuantity > 0
                                ): ?>

                                    Paid:
                                    <?= $paidQuantity ?>

                                    <br>

                                    Free:
                                    <?= $freeQuantity ?>

                                    <br>

                                    <strong>
                                        Total:
                                        <?= $quantity ?>
                                    </strong>

                                <?php else: ?>

                                    <?= $quantity ?>

                                <?php endif; ?>

                            </td>


                            <td>

                                ৳<?= number_format(
                                    $unitPrice,
                                    2
                                ) ?>

                                <br>

                                <small>
                                    Total:
                                    ৳<?= number_format(
                                        $totalAmount,
                                        2
                                    ) ?>
                                </small>

                            </td>


                            <td>

                                <?php if (
                                    !empty(
                                        $reservation[
                                            "SellingDate"
                                        ]
                                    )
                                ): ?>

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
                                        $reservation[
                                            "Status"
                                        ] === "Rejected"
                                    ): ?>

                                        Reservation rejected

                                    <?php else: ?>

                                        Waiting for seller confirmation

                                    <?php endif; ?>

                                <?php else: ?>

                                    <span style="
                                        color:var(--purple);
                                        font-weight:600;
                                    ">
                                        Offer Reservation
                                    </span>

                                    <br>

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

                                        Seller will provide
                                        pickup details.

                                    <?php elseif (
                                        $reservation[
                                            "Status"
                                        ] === "Rejected"
                                    ): ?>

                                        Reservation rejected

                                    <?php else: ?>

                                        Waiting for seller confirmation

                                    <?php endif; ?>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span
                                    class="status-badge <?= $statusClass ?>"
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


                                <?php elseif (
                                    $reservation["Status"]
                                    === "Completed"
                                    &&
                                    !empty(
                                        $reservation["PurchaseID"]
                                    )
                                    &&
                                    empty(
                                        $reservation["FeedbackID"]
                                    )
                                ): ?>

                                    <a
                                        href="../feedback/rate.php?purchase_id=<?= (int) $reservation["PurchaseID"] ?>"
                                        class="edit-button"
                                    >
                                        ★ Give Feedback
                                    </a>


                                <?php elseif (
                                    $reservation["Status"]
                                    === "Completed"
                                    &&
                                    !empty(
                                        $reservation["FeedbackID"]
                                    )
                                ): ?>

                                    <span style="
                                        color:var(--purple);
                                        font-weight:600;
                                    ">
                                        ★ Rated
                                    </span>

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