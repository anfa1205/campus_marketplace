<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT
        r.ReservationID,
        r.ReservationDate,
        r.ReservationTarget,
        r.Status,

        b.Name AS BuyerName,
        b.Email AS BuyerEmail,
        b.Phone AS BuyerPhone,

        p.ProductID,
        p.ProductName,
        p.Price,

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
        sa.CampusLocation

    FROM reservation r

    INNER JOIN buyer b
        ON r.buyerID = b.BuyerID

    INNER JOIN contains c
        ON r.ReservationID = c.reservationID

    INNER JOIN product p
        ON c.productID = p.ProductID

    LEFT JOIN promotion pr
        ON c.promotionID = pr.PromotionId

    LEFT JOIN sales_announcement sa
        ON r.announcementID = sa.AnnouncementId

    WHERE r.sellerID = ?

    ORDER BY r.ReservationID DESC
");

$stmt->execute([$sellerID]);

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
            Reservation Management
        </h1>

        <div class="title-line"></div>

    </div>


    <?php if (count($reservations) === 0): ?>

        <div class="card">

            <h2>
                No Reservations
            </h2>

            <br>

            <p>
                No buyer reservations have been made yet.
            </p>

        </div>

    <?php else: ?>


        <?php foreach ($reservations as $reservation): ?>

            <?php

            $status = $reservation["Status"];

            $totalQuantity =
                (int) $reservation["quantity"];

            $paidQuantity =
                $reservation["paidQuantity"] !== null
                    ? (int) $reservation["paidQuantity"]
                    : $totalQuantity;

            $freeQuantity =
                $reservation["freeQuantity"] !== null
                    ? (int) $reservation["freeQuantity"]
                    : 0;

            $unitPrice =
                $reservation["unitPrice"] !== null
                    ? (float) $reservation["unitPrice"]
                    : (float) $reservation["Price"];

            $totalAmount =
                $paidQuantity * $unitPrice;

            ?>

            <div class="card" style="margin-bottom:25px;">

                <div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:flex-start;
                    gap:20px;
                    flex-wrap:wrap;
                ">

                    <div>

                        <p class="dashboard-label">
                            RESERVATION #<?= (int) $reservation["ReservationID"] ?>
                        </p>

                        <h2>
                            <?= htmlspecialchars($reservation["ProductName"]) ?>
                        </h2>

                    </div>


                    <?php

                    $statusClass =
                        strtolower(
                            str_replace(
                                " ",
                                "-",
                                $status
                            )
                        );

                    ?>

                    <span class="status-badge <?= $statusClass ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>

                </div>


                <hr style="margin:20px 0;">


                <div style="
                    display:grid;
                    grid-template-columns:
                        repeat(auto-fit,minmax(220px,1fr));
                    gap:20px;
                ">


                    <div>

                        <strong>Buyer</strong>

                        <p>
                            <?= htmlspecialchars(
                                $reservation["BuyerName"]
                            ) ?>
                        </p>

                        <p>
                            <?= htmlspecialchars(
                                $reservation["BuyerEmail"]
                            ) ?>
                        </p>

                        <p>
                            <?= htmlspecialchars(
                                $reservation["BuyerPhone"]
                            ) ?>
                        </p>

                    </div>


                    <div>

                        <strong>Quantity</strong>

                        <?php if ($freeQuantity > 0): ?>

                            <p>
                                Paid:
                                <?= $paidQuantity ?>
                            </p>

                            <p>
                                Free:
                                <?= $freeQuantity ?>
                            </p>

                            <p>
                                Total:
                                <?= $totalQuantity ?>
                            </p>

                        <?php else: ?>

                            <p>
                                <?= $totalQuantity ?>
                            </p>

                        <?php endif; ?>

                    </div>


                    <div>

                        <strong>Price</strong>

                        <p>
                            ৳<?= number_format(
                                $unitPrice,
                                2
                            ) ?>
                            per paid item
                        </p>

                        <strong>
                            Total:
                            ৳<?= number_format(
                                $totalAmount,
                                2
                            ) ?>
                        </strong>

                    </div>


                    <div>

                        <strong>Offer</strong>

                        <?php if (
                            $reservation["promotionID"] !== null
                        ): ?>

                            <?php if (
                                $reservation["OfferType"]
                                === "Percentage"
                            ): ?>

                                <p style="
                                    color:var(--purple);
                                    font-weight:700;
                                ">
                                    <?= number_format(
                                        (float)
                                        $reservation[
                                            "DiscountValue"
                                        ],
                                        0
                                    ) ?>% Discount
                                </p>

                            <?php elseif (
                                $reservation["OfferType"]
                                === "BuyXGetY"
                            ): ?>

                                <p style="
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
                                </p>

                            <?php endif; ?>

                        <?php else: ?>

                            <p>
                                No Offer
                            </p>

                        <?php endif; ?>

                    </div>


                    <div>

                        <strong>Reservation Date</strong>

                        <p>
                            <?= htmlspecialchars(
                                $reservation[
                                    "ReservationDate"
                                ]
                            ) ?>
                        </p>

                    </div>


                    <div>

                        <strong>Pickup</strong>

                        <?php if (
                            !empty(
                                $reservation[
                                    "SellingDate"
                                ]
                            )
                        ): ?>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation[
                                        "SellingDate"
                                    ]
                                ) ?>
                            </p>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation[
                                        "SellingTime"
                                    ]
                                ) ?>
                            </p>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation[
                                        "CampusLocation"
                                    ]
                                ) ?>
                            </p>

                        <?php else: ?>

                            <p>
                                Offer reservation
                            </p>

                            <p>
                                No pickup details yet.
                            </p>

                        <?php endif; ?>

                    </div>

                </div>


                <div style="
                    margin-top:25px;
                    display:flex;
                    gap:10px;
                    flex-wrap:wrap;
                ">


                    <?php if ($status === "Pending"): ?>

                        <form
                            method="POST"
                            action="update_status.php"
                        >

                            <input
                                type="hidden"
                                name="reservation_id"
                                value="<?= (int) $reservation["ReservationID"] ?>"
                            >

                            <input
                                type="hidden"
                                name="status"
                                value="Accepted"
                            >

                            <button
                                type="submit"
                                class="btn"
                            >
                                Accept Reservation
                            </button>

                        </form>


                        <form
                            method="POST"
                            action="update_status.php"
                        >

                            <input
                                type="hidden"
                                name="reservation_id"
                                value="<?= (int) $reservation["ReservationID"] ?>"
                            >

                            <input
                                type="hidden"
                                name="status"
                                value="Rejected"
                            >

                            <button
                                type="submit"
                                class="delete-button"
                                onclick="return confirm('Reject this reservation?');"
                            >
                                Reject
                            </button>

                        </form>


                    <?php elseif (
                        $status === "Accepted"
                    ): ?>

                        <form
                            method="POST"
                            action="update_status.php"
                        >

                            <input
                                type="hidden"
                                name="reservation_id"
                                value="<?= (int) $reservation["ReservationID"] ?>"
                            >

                            <input
                                type="hidden"
                                name="status"
                                value="Completed"
                            >

                            <button
                                type="submit"
                                class="btn"
                                onclick="return confirm('Mark this reservation as completed?');"
                            >
                                Complete Sale
                            </button>

                        </form>


                    <?php elseif (
                        $status === "Completed"
                    ): ?>

                        <span style="
                            color:var(--purple);
                            font-weight:700;
                        ">
                            ✓ Sale Completed
                        </span>


                    <?php elseif (
                        $status === "Rejected"
                    ): ?>

                        <span style="
                            color:#9a4d4d;
                            font-weight:700;
                        ">
                            Reservation Rejected
                        </span>

                    <?php endif; ?>


                    <a
                        href="delete.php?id=<?= (int) $reservation["ReservationID"] ?>"
                        class="delete-button"
                        onclick="return confirm('Delete this reservation record?');"
                    >
                        Delete
                    </a>

                </div>

            </div>

        <?php endforeach; ?>


    <?php endif; ?>


</div>


<?php

include "../../includes/footer.php";

?>