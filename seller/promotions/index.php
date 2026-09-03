<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";
require_once "../../includes/header.php";

$sellerID = $_SESSION["user_id"];

/* Get all offers created by this seller */
$stmt = $pdo->prepare("
    SELECT
        p.PromotionId,
        p.OfferType,
        p.DiscountValue,
        p.BuyQuantity,
        p.GetQuantity,
        p.StartDate,
        p.EndDate,
        p.SellerId
    FROM promotion p
    WHERE p.SellerId = ?
    ORDER BY p.PromotionId DESC
");

$stmt->execute([$sellerID]);

$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Use database time so Active/Upcoming status is accurate */
$dbNow = $pdo->query("SELECT NOW()")->fetchColumn();
$now = strtotime($dbNow);

?>

<style>

.seller-offers-page {
    max-width: 1100px;
    margin: 30px auto;
    padding: 0 20px;
}

.seller-offers-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 15px;
}

.seller-offers-header h2 {
    margin: 0;
    color: #5b3b82;
    font-size: 28px;
}

.create-offer-btn {
    background: #7b5aa6;
    color: white;
    text-decoration: none;
    padding: 11px 20px;
    border-radius: 9px;
    font-weight: 600;
    white-space: nowrap;
}

.create-offer-btn:hover {
    background: #624487;
}

.seller-offers-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.seller-offer-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 18px rgba(80, 50, 110, 0.12);
    border: 1px solid #eee5f7;
}

.seller-offer-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.seller-offer-type {
    background: #eee5f8;
    color: #63448a;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.offer-status {
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.offer-active {
    background: #e3f6e9;
    color: #24723d;
}

.offer-upcoming {
    background: #fff2d8;
    color: #986600;
}

.offer-expired {
    background: #f4e4e4;
    color: #8b3838;
}

.seller-offer-title {
    font-size: 23px;
    font-weight: 800;
    color: #553577;
    margin: 8px 0 12px;
}

.seller-offer-details {
    background: #faf7fd;
    border-radius: 10px;
    padding: 13px;
    margin-bottom: 15px;
}

.seller-offer-details p {
    margin: 7px 0;
    color: #555;
    font-size: 14px;
}

.seller-offer-details strong {
    color: #4f3769;
}

.seller-offer-date {
    font-size: 13px;
    color: #777;
    line-height: 1.6;
}

.seller-offer-actions {
    display: flex;
    gap: 10px;
    margin-top: 16px;
}

.seller-offer-actions a {
    flex: 1;
    text-align: center;
    padding: 9px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}

.edit-offer-btn {
    background: #e9def4;
    color: #604080;
}

.delete-offer-btn {
    background: #f5dddd;
    color: #963d3d;
}

.edit-offer-btn:hover {
    background: #dccbea;
}

.delete-offer-btn:hover {
    background: #eccaca;
}

.no-offers {
    text-align: center;
    background: #ffffff;
    padding: 45px 20px;
    border-radius: 16px;
    box-shadow: 0 5px 18px rgba(80, 50, 110, 0.10);
    color: #777;
}

@media (max-width: 750px) {

    .seller-offers-grid {
        grid-template-columns: 1fr;
    }

    .seller-offers-header {
        flex-direction: column;
        align-items: stretch;
    }

    .create-offer-btn {
        text-align: center;
    }

}

</style>


<div class="seller-offers-page">

    <div class="seller-offers-header">

        <h2>My Offers</h2>

        <a href="create.php" class="create-offer-btn">
            + Create Offer
        </a>

    </div>


    <?php if (empty($offers)): ?>

        <div class="no-offers">
            <h3>No Offers Yet</h3>
            <p>Create your first offer to attract buyers.</p>
        </div>

    <?php else: ?>

        <div class="seller-offers-grid">

            <?php foreach ($offers as $offer): ?>

                <?php

                /*
                 * Determine offer status
                 * using database server time.
                 */

                $start = strtotime($offer["StartDate"]);
                $end = strtotime($offer["EndDate"]);

                if ($now < $start) {

                    $status = "Upcoming";
                    $statusClass = "offer-upcoming";

                } elseif ($now <= $end) {

                    $status = "Active";
                    $statusClass = "offer-active";

                } else {

                    $status = "Expired";
                    $statusClass = "offer-expired";

                }


                /* Offer title */

                if ($offer["OfferType"] === "Percentage") {

                    $discount = (float) $offer["DiscountValue"];

                    $offerTitle = rtrim(
                        rtrim(number_format($discount, 2), "0"),
                        "."
                    ) . "% DISCOUNT";

                } else {

                    $buy = (int) $offer["BuyQuantity"];
                    $get = (int) $offer["GetQuantity"];

                    $offerTitle = "BUY " . $buy . " GET " . $get;

                }

                ?>

                <div class="seller-offer-card">

                    <div class="seller-offer-top">

                        <span class="seller-offer-type">
                            <?php
                            echo htmlspecialchars(
                                $offer["OfferType"] === "Percentage"
                                    ? "Percentage Discount"
                                    : "Buy X Get Y"
                            );
                            ?>
                        </span>

                        <span class="offer-status <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>

                    </div>


                    <div class="seller-offer-title">

                        <?php echo htmlspecialchars($offerTitle); ?>

                    </div>


                    <div class="seller-offer-details">

                        <?php if ($offer["OfferType"] === "Percentage"): ?>

                            <p>
                                <strong>Discount:</strong>
                                <?php echo htmlspecialchars($offer["DiscountValue"]); ?>%
                            </p>

                        <?php else: ?>

                            <p>
                                <strong>Buy Quantity:</strong>
                                <?php echo htmlspecialchars($offer["BuyQuantity"]); ?>
                            </p>

                            <p>
                                <strong>Free Quantity:</strong>
                                <?php echo htmlspecialchars($offer["GetQuantity"]); ?>
                            </p>

                        <?php endif; ?>


                        <p class="seller-offer-date">
                            <strong>Starts:</strong>
                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime($offer["StartDate"])
                            );
                            ?>
                        </p>


                        <p class="seller-offer-date">
                            <strong>Ends:</strong>
                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime($offer["EndDate"])
                            );
                            ?>
                        </p>

                    </div>


                    <div class="seller-offer-actions">

                        <a
                            href="edit.php?id=<?php echo (int)$offer["PromotionId"]; ?>"
                            class="edit-offer-btn"
                        >
                            Edit
                        </a>

                        <a
                            href="delete.php?id=<?php echo (int)$offer["PromotionId"]; ?>"
                            class="delete-offer-btn"
                            onclick="return confirm('Are you sure you want to delete this offer?');"
                        >
                            Delete
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<?php require_once "../../includes/footer.php"; ?>