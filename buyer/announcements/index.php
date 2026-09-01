<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";


// ============================================================
// GET ONLY SALES ANNOUNCEMENTS CREATED BY SELLERS
// ============================================================

$stmt = $pdo->prepare("
    SELECT
        sa.AnnouncementId,
        sa.SellingDate,
        sa.SellingTime,
        sa.CampusLocation,
        sa.Status,

        s.sellerID,
        s.Name AS SellerName,
        s.bussinessName

    FROM sales_announcement sa

    INNER JOIN seller s
        ON sa.SellerId = s.sellerID

    ORDER BY
        sa.SellingDate ASC,
        sa.SellingTime ASC
");

$stmt->execute();

$announcements = $stmt->fetchAll();


include "../../includes/header.php";

?>


<div class="seller-dashboard">


    <!-- DECORATIONS -->

    <div class="academic-decoration decoration-top-left">
        <span>✦</span>
        <span>⌁</span>
    </div>

    <div class="academic-decoration decoration-bottom-right">
        <span>✦</span>
        <span>⌁</span>
    </div>


    <!-- PAGE INTRO -->

    <div class="dashboard-intro">

        <p class="dashboard-label">
            CAMPUS MARKETPLACE
        </p>

        <h1>
            Sales Announcements
        </h1>

        <div class="title-line"></div>

        <p class="dashboard-description">
            Discover selling sessions posted by student sellers.
        </p>

    </div>

    <?php if (isset($_GET["success"])): ?>

    <div class="reservation-success">

        <span class="success-icon">✓</span>

        <div>
            <strong>Reservation Successful!</strong>

            <p>
                <?= htmlspecialchars($_GET["success"]) ?>
            </p>
        </div>

    </div>

<?php endif; ?>


<?php if (isset($_GET["error"])): ?>

    <div class="reservation-error">

        <span class="error-icon">!</span>

        <div>
            <strong>Reservation Failed</strong>

            <p>
                <?= htmlspecialchars($_GET["error"]) ?>
            </p>
        </div>

    </div>

<?php endif; ?>


    <?php if (empty($announcements)): ?>

        <div class="dashboard-card empty-card">

            <div class="card-icon">
                📢
            </div>

            <h3>
                No Sales Announcements
            </h3>

            <p>
                There are currently no sales announcements available.
            </p>

        </div>


    <?php else: ?>


        <div class="announcement-list">


            <?php foreach ($announcements as $announcement): ?>


                <?php

                // ====================================================
                // IMPORTANT:
                // GET ONLY PRODUCTS ADDED TO THIS ANNOUNCEMENT
                // THROUGH THE "includes" TABLE.
                //
                // Products that exist only in Product Management
                // will NOT appear here.
                // ====================================================

                $productStmt = $pdo->prepare("
                    SELECT
                        p.ProductID,
                        p.ProductName,
                        p.Description,
                        p.Category,
                        p.Price,
                        p.Stock,
                        p.Status,

                        i.quantity AS SaleQuantity

                    FROM includes i

                    INNER JOIN product p
                        ON i.productID = p.ProductID

                    WHERE i.announcementID = :announcementID

                    ORDER BY p.ProductName ASC
                ");

                $productStmt->execute([
                    ":announcementID" =>
                        $announcement["AnnouncementId"]
                ]);

                $products = $productStmt->fetchAll();

                ?>


                <!-- ==================================================
                     ANNOUNCEMENT CARD
                     ================================================== -->

                <div class="announcement-card">


                    <!-- ANNOUNCEMENT HEADER -->

                    <div class="announcement-header">

                        <div>

                            <p class="dashboard-label">
                                SALES ANNOUNCEMENT
                            </p>

                            <h2>
                                <?= htmlspecialchars(
                                    $announcement["bussinessName"]
                                ) ?>
                            </h2>

                            <p class="seller-name">

                                Seller:
                                <?= htmlspecialchars(
                                    $announcement["SellerName"]
                                ) ?>

                            </p>

                        </div>


                        <span class="status-badge">

                            <?= htmlspecialchars(
                                $announcement["Status"]
                            ) ?>

                        </span>

                    </div>


                    <!-- ==================================================
                         SALES ANNOUNCEMENT DETAILS
                         ================================================== -->

                    <div class="announcement-info">


                        <div class="info-box">

                            <strong>
                                📅 Date
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $announcement["SellingDate"]
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                ⏰ Time
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $announcement["SellingTime"]
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                📍 Location
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $announcement["CampusLocation"]
                                ) ?>
                            </p>

                        </div>


                    </div>


                    <!-- ==================================================
                         PRODUCTS SPECIFICALLY INCLUDED IN THIS SALE
                         ================================================== -->

                    <div class="products-section">

                        <h3>
                            Products in This Sale
                        </h3>


                        <?php if (empty($products)): ?>

                            <p class="no-products">
                                No products have been added to this
                                sales announcement.
                            </p>


                        <?php else: ?>


                            <div class="product-grid">


                                <?php foreach ($products as $product): ?>


                                    <?php

                                    $stock =
                                        (int) $product["Stock"];

                                    $saleQuantity =
                                        (int) $product["SaleQuantity"];


                                    // ==================================================
                                    // FIND HOW MANY HAVE ALREADY BEEN RESERVED
                                    // FOR THIS ANNOUNCEMENT + PRODUCT
                                    // ==================================================

                                    $reservedStmt = $pdo->prepare("
                                        SELECT
                                            COALESCE(
                                                SUM(c.quantity),
                                                0
                                            ) AS reservedQuantity

                                        FROM reservation r

                                        INNER JOIN contains c
                                            ON r.ReservationID =
                                               c.reservationID

                                        WHERE r.announcementID =
                                              :announcementID

                                          AND c.productID =
                                              :productID

                                          AND r.Status <> 'Rejected'
                                    ");

                                    $reservedStmt->execute([

                                        ":announcementID" =>
                                            $announcement["AnnouncementId"],

                                        ":productID" =>
                                            $product["ProductID"]

                                    ]);

                                    $reservedData =
                                        $reservedStmt->fetch();


                                    $reservedQuantity =
                                        (int)
                                        $reservedData[
                                            "reservedQuantity"
                                        ];


                                    // ==================================================
                                    // REMAINING QUANTITY IN THIS SALES ANNOUNCEMENT
                                    // ==================================================

                                    $remainingSaleQuantity =
                                        max(
                                            0,
                                            $saleQuantity -
                                            $reservedQuantity
                                        );


                                    // ==================================================
                                    // BUYER CAN NEVER RESERVE MORE THAN:
                                    //
                                    // 1. CURRENT PRODUCT STOCK
                                    // 2. REMAINING SALE QUANTITY
                                    // ==================================================

                                    $maximumReservation =
                                        min(
                                            $stock,
                                            $remainingSaleQuantity
                                        );


                                    // ==================================================
                                    // DISPLAY PRODUCT STATUS
                                    // ==================================================

                                    if (
                                        $product["Status"]
                                        === "Unavailable"
                                    ) {

                                        $displayStatus =
                                            "Unavailable";

                                        $statusType =
                                            "unavailable";

                                    } elseif (
                                        $stock <= 0
                                    ) {

                                        $displayStatus =
                                            "Out of Stock";

                                        $statusType =
                                            "out";

                                    } elseif (
                                        $remainingSaleQuantity <= 0
                                    ) {

                                        $displayStatus =
                                            "Fully Reserved";

                                        $statusType =
                                            "out";

                                    } else {

                                        $displayStatus =
                                            "Available";

                                        $statusType =
                                            "available";

                                    }

                                    ?>


                                    <!-- PRODUCT CARD -->

                                    <div class="product-card">


                                        <div class="product-top">

                                            <h4>

                                                <?= htmlspecialchars(
                                                    $product["ProductName"]
                                                ) ?>

                                            </h4>


                                            <span
                                                class="product-status <?= $statusType ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $displayStatus
                                                ) ?>

                                            </span>

                                        </div>


                                        <p class="product-category">

                                            <?= htmlspecialchars(
                                                $product["Category"]
                                            ) ?>

                                        </p>


                                        <p class="product-description">

                                            <?= htmlspecialchars(
                                                $product["Description"]
                                            ) ?>

                                        </p>


                                        <!-- PRODUCT DETAILS -->

                                        <div class="product-details">


                                            <div>

                                                <strong>
                                                    Price
                                                </strong>

                                                <p>

                                                    ৳<?= number_format(
                                                        (float)
                                                        $product["Price"],
                                                        2
                                                    ) ?>

                                                </p>

                                            </div>


                                            <div>

                                                <strong>
                                                    Sale Quantity
                                                </strong>

                                                <p>
                                                    <?= $saleQuantity ?>
                                                </p>

                                            </div>


                                            <div>

                                                <strong>
                                                    Remaining
                                                </strong>

                                                <p>
                                                    <?= $remainingSaleQuantity ?>
                                                </p>

                                            </div>


                                        </div>


                                        <!-- ==================================================
                                             RESERVATION FORM
                                             ================================================== -->

                                        <?php if (
                                            $statusType === "available"
                                            &&
                                            $maximumReservation > 0
                                        ): ?>


                                            <form
                                                method="POST"
                                                action="reserve.php"
                                                class="reserve-form"
                                            >


                                                <input
                                                    type="hidden"
                                                    name="announcement_id"
                                                    value="<?= (int) $announcement["AnnouncementId"] ?>"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="product_id"
                                                    value="<?= (int) $product["ProductID"] ?>"
                                                >


                                                <label>
                                                    Quantity
                                                </label>


                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    min="1"
                                                    max="<?= $maximumReservation ?>"
                                                    value="1"
                                                    required
                                                >


                                                <button
                                                    type="submit"
                                                    class="btn"
                                                >
                                                    Reserve
                                                </button>


                                            </form>


                                        <?php else: ?>


                                            <button
                                                type="button"
                                                class="btn disabled-btn"
                                                disabled
                                            >

                                                <?= htmlspecialchars(
                                                    $displayStatus
                                                ) ?>

                                            </button>


                                        <?php endif; ?>


                                    </div>


                                <?php endforeach; ?>


                            </div>


                        <?php endif; ?>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


<style>

.announcement-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.announcement-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 8px 25px rgba(70, 54, 83, 0.08);
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 24px;
}

.announcement-header h2 {
    color: var(--deep-purple);
    margin: 8px 0 5px;
}

.seller-name {
    color: var(--gray-text);
    margin: 0;
}

.announcement-info {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.info-box {
    background: var(--light-lavender);
    padding: 16px;
    border-radius: 12px;
}

.info-box strong {
    display: block;
    color: var(--purple);
    margin-bottom: 6px;
}

.info-box p {
    margin: 0;
    color: var(--dark-text);
}

.products-section {
    margin-top: 10px;
}

.products-section h3 {
    color: var(--deep-purple);
    margin-bottom: 16px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.product-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    background: var(--cream);
}

.product-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.product-top h4 {
    color: var(--deep-purple);
    margin: 0;
    font-size: 18px;
}

.product-status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.product-status.available {
    background: #e9f5ec;
    color: #356b43;
}

.product-status.unavailable,
.product-status.out {
    background: #f8e9eb;
    color: var(--danger);
}

.product-category {
    color: var(--purple);
    font-size: 13px;
    margin: 8px 0;
}

.product-description {
    color: var(--gray-text);
    font-size: 14px;
    line-height: 1.5;
    min-height: 42px;
}

.product-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin: 18px 0;
}

.product-details div {
    background: var(--white);
    padding: 10px;
    border-radius: 9px;
}

.product-details strong {
    display: block;
    color: var(--purple);
    font-size: 12px;
}

.product-details p {
    margin: 4px 0 0;
    color: var(--dark-text);
    font-weight: 600;
}

.reserve-form {
    display: flex;
    align-items: end;
    gap: 10px;
    flex-wrap: wrap;
}

.reserve-form label {
    width: 100%;
    color: var(--purple);
    font-weight: 600;
}

.reserve-form input {
    width: 90px;
}

.disabled-btn {
    opacity: 0.6;
    cursor: not-allowed;
}

.empty-card {
    text-align: center;
    padding: 50px;
}

.no-products {
    color: var(--gray-text);
}

@media (max-width: 800px) {

    .announcement-info {
        grid-template-columns: 1fr;
    }

    .product-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 600px) {

    .announcement-header {
        flex-direction: column;
    }

}

.reservation-success {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #e9f5ec;
    border: 1px solid #b9d8c0;
    color: #356b43;
    padding: 18px 22px;
    border-radius: 14px;
    margin-bottom: 25px;
}

.success-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #356b43;
    color: white;
    border-radius: 50%;
    font-size: 20px;
    font-weight: bold;
}

.reservation-success strong {
    display: block;
    font-size: 16px;
    margin-bottom: 3px;
}

.reservation-success p {
    margin: 0;
}


.reservation-error {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8e9eb;
    border: 1px solid #e2b9c0;
    color: var(--danger);
    padding: 18px 22px;
    border-radius: 14px;
    margin-bottom: 25px;
}

.error-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--danger);
    color: white;
    border-radius: 50%;
    font-size: 20px;
    font-weight: bold;
}

.reservation-error strong {
    display: block;
    font-size: 16px;
    margin-bottom: 3px;
}

.reservation-error p {
    margin: 0;
}

</style>


<?php include "../../includes/footer.php"; ?>