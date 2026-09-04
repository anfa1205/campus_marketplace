<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];


/* =========================================================
   STAR FUNCTION
   ========================================================= */

function showProfileStars($rating)
{
    $rating = max(0, min(5, (float) $rating));

    $full = floor($rating);

    $half = (($rating - $full) >= 0.5);

    $html = '<span class="profile-stars">';

    for ($i = 1; $i <= 5; $i++) {

        if ($i <= $full) {

            $html .= '<span class="star-full">★</span>';

        } elseif (
            $i == $full + 1 &&
            $half
        ) {

            $html .= '<span class="star-half">★</span>';

        } else {

            $html .= '<span class="star-empty">☆</span>';

        }

    }

    $html .= '</span>';

    return $html;
}


/* =========================================================
   SELLER INFORMATION
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        sellerID,
        StudentID,
        department,
        Name,
        Mail,
        bussinessName,
        Phone,
        AvgRating
    FROM seller
    WHERE sellerID = ?
");

$stmt->execute([
    $sellerID
]);

$seller = $stmt->fetch();


if (!$seller) {

    die("Seller profile not found.");

}


/* =========================================================
   TOTAL PRODUCTS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM product
    WHERE SellerID = ?
");

$stmt->execute([
    $sellerID
]);

$totalProducts =
    (int) $stmt->fetchColumn();


/* =========================================================
   AVAILABLE PRODUCTS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM product
    WHERE SellerID = ?
      AND Status = 'Available'
      AND Stock > 0
");

$stmt->execute([
    $sellerID
]);

$availableProducts =
    (int) $stmt->fetchColumn();


/* =========================================================
   PRODUCTS SOLD
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        COALESCE(
            SUM(h.quantity),
            0
        )
    FROM purchase pu

    INNER JOIN has h
        ON pu.PurchaseID = h.purchaseID

    INNER JOIN product p
        ON h.productID = p.ProductID

    WHERE pu.sellerID = ?
      AND p.SellerID = ?
      AND pu.status = 'Completed'
");

$stmt->execute([
    $sellerID,
    $sellerID
]);

$totalSold =
    (int) $stmt->fetchColumn();


/* =========================================================
   TOTAL INCOME
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        COALESCE(
            SUM(
                h.quantity * p.Price
            ),
            0
        )
    FROM purchase pu

    INNER JOIN has h
        ON pu.PurchaseID = h.purchaseID

    INNER JOIN product p
        ON h.productID = p.ProductID

    WHERE pu.sellerID = ?
      AND p.SellerID = ?
      AND pu.status = 'Completed'
");

$stmt->execute([
    $sellerID,
    $sellerID
]);

$totalIncome =
    (float) $stmt->fetchColumn();


/* =========================================================
   COMPLETED SALES
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT pu.PurchaseID)

    FROM purchase pu

    WHERE pu.sellerID = ?
      AND pu.status = 'Completed'
");

$stmt->execute([
    $sellerID
]);

$totalSales =
    (int) $stmt->fetchColumn();


/* =========================================================
   TOTAL REVIEWS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)

    FROM feedback

    WHERE sellerID = ?
");

$stmt->execute([
    $sellerID
]);

$totalReviews =
    (int) $stmt->fetchColumn();


/* =========================================================
   AVERAGE RATING
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        COALESCE(
            AVG(Rating),
            0
        )

    FROM feedback

    WHERE sellerID = ?
");

$stmt->execute([
    $sellerID
]);

$averageRating =
    (float) $stmt->fetchColumn();


/* =========================================================
   COMPLETED RESERVATIONS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)

    FROM reservation

    WHERE sellerID = ?
      AND Status = 'Completed'
");

$stmt->execute([
    $sellerID
]);

$completedReservations =
    (int) $stmt->fetchColumn();



/* =========================================================
   LOW STOCK PRODUCTS
   ========================================================= */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)

        FROM product

        WHERE SellerID = ?
        AND Stock >= 0
        AND Stock <= 3
    ");

    $stmt->execute([
        $sellerID
    ]);

    $lowStockProducts =
        (int) $stmt->fetchColumn();




/* =========================================================
   MY PRODUCTS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT

        p.ProductID,
        p.ProductName,
        p.Category,
        p.Price,
        p.Stock,
        p.Status,

        COALESCE(
            (
                SELECT SUM(h.quantity)

                FROM has h

                INNER JOIN purchase pu
                    ON h.purchaseID =
                       pu.PurchaseID

                WHERE h.productID =
                      p.ProductID

                  AND pu.sellerID =
                      ?

                  AND pu.status =
                      'Completed'
            ),
            0
        ) AS SoldQuantity,

        COALESCE(
            (
                SELECT AVG(f.Rating)

                FROM feedback f

                WHERE f.productID =
                      p.ProductID

                  AND f.sellerID =
                      ?
            ),
            0
        ) AS ProductRating,

        (
            SELECT COUNT(*)

            FROM feedback f

            WHERE f.productID =
                  p.ProductID

              AND f.sellerID =
                  ?
        ) AS ProductReviews

    FROM product p

    WHERE p.SellerID = ?

    ORDER BY p.ProductID DESC
");

$stmt->execute([
    $sellerID,
    $sellerID,
    $sellerID,
    $sellerID
]);

$products =
    $stmt->fetchAll();


/* =========================================================
   RECENT REVIEWS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT

        f.FeedbackID,
        f.Rating,
        f.Comment,
        f.FeedbackDate,

        p.ProductName,

        b.Name AS BuyerName

    FROM feedback f

    INNER JOIN buyer b
        ON f.buyerID =
           b.BuyerID

    LEFT JOIN product p
        ON f.productID =
           p.ProductID

    WHERE f.sellerID = ?

    ORDER BY
        f.FeedbackDate DESC

    LIMIT 5
");

$stmt->execute([
    $sellerID
]);

$recentReviews =
    $stmt->fetchAll();


/* =========================================================
   BEST SELLING PRODUCT
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT

        p.ProductName,

        COALESCE(
            SUM(h.quantity),
            0
        ) AS SoldQuantity

    FROM product p

    INNER JOIN has h
        ON p.ProductID =
           h.productID

    INNER JOIN purchase pu
        ON h.purchaseID =
           pu.PurchaseID

    WHERE p.SellerID = ?

      AND pu.sellerID = ?

      AND pu.status = 'Completed'

    GROUP BY
        p.ProductID,
        p.ProductName

    ORDER BY
        SoldQuantity DESC

    LIMIT 1
");

$stmt->execute([
    $sellerID,
    $sellerID
]);

$bestProduct =
    $stmt->fetch();


include "../../includes/header.php";

?>


<div class="my-profile-page">


    <!-- DECORATION -->

    <div class="academic-decoration decoration-top-left">

        <span>✦</span>
        <span>⌁</span>

    </div>


    <div class="academic-decoration decoration-bottom-right">

        <span>✦</span>
        <span>⌁</span>

    </div>


    <!-- HEADER -->

    <div class="profile-page-header">

        <p class="dashboard-label">
            SELLER AREA
        </p>

        <h1>
            My Profile
        </h1>

        <div class="title-line"></div>

        <p class="profile-description">
            Manage your seller information and view your marketplace performance.
        </p>

    </div>


    <!-- =====================================================
         PROFILE INFORMATION
    ====================================================== -->

    <div class="profile-main-card">


        <div class="profile-avatar">
            <?= strtoupper(
                substr(
                    $seller["Name"],
                    0,
                    1
                )
            ) ?>
        </div>


        <div class="profile-main-info">

            <p class="profile-small-label">
                SELLER
            </p>

            <h2>
                <?= htmlspecialchars(
                    $seller["Name"]
                ) ?>
            </h2>

            <h3>
                <?= htmlspecialchars(
                    $seller["bussinessName"]
                ) ?>
            </h3>

            <div class="profile-rating-line">

                <?= showProfileStars(
                    $averageRating
                ) ?>

                <strong>
                    <?= number_format(
                        $averageRating,
                        1
                    ) ?>
                    / 5
                </strong>

                <span>
                    <?= $totalReviews ?>
                    reviews
                </span>

            </div>

        </div>


        <div class="profile-edit-area">

            <a
                href="edit.php"
                class="profile-edit-button"
            >
                Edit Profile
            </a>

        </div>

    </div>


    <!-- =====================================================
         SELLER INFORMATION
    ====================================================== -->

    <div class="profile-section">

        <div class="profile-section-title">

            <p class="dashboard-label">
                ACCOUNT INFORMATION
            </p>

            <h2>
                Seller Information
            </h2>

        </div>


        <div class="profile-info-grid">


            <div class="profile-info-box">

                <span>
                    Full Name
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["Name"]
                    ) ?>
                </strong>

            </div>


            <div class="profile-info-box">

                <span>
                    Business Name
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["bussinessName"]
                    ) ?>
                </strong>

            </div>


            <div class="profile-info-box">

                <span>
                    Student ID
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["StudentID"]
                    ) ?>
                </strong>

            </div>


            <div class="profile-info-box">

                <span>
                    Department
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["department"]
                    ) ?>
                </strong>

            </div>


            <div class="profile-info-box">

                <span>
                    Email
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["Mail"]
                    ) ?>
                </strong>

            </div>


            <div class="profile-info-box">

                <span>
                    Phone
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["Phone"]
                    ) ?>
                </strong>

            </div>


        </div>

    </div>


    <!-- =====================================================
         PERFORMANCE
    ====================================================== -->

    <div class="profile-section">

        <div class="profile-section-title">

            <p class="dashboard-label">
                PERFORMANCE
            </p>

            <h2>
                Seller Overview
            </h2>

        </div>


        <div class="performance-grid">


            <!-- PRODUCTS -->

            <div class="performance-card">

                <div class="performance-icon">
                    ◈
                </div>

                <span>
                    My Products
                </span>

                <strong>
                    <?= $totalProducts ?>
                </strong>

                <small>
                    <?= $availableProducts ?>
                    currently available
                </small>

            </div>


            <!-- SOLD -->

            <div class="performance-card">

                <div class="performance-icon">
                    ◆
                </div>

                <span>
                    Products Sold
                </span>

                <strong>
                    <?= $totalSold ?>
                </strong>

                <small>
                    total units sold
                </small>

            </div>


            <!-- INCOME -->

            <div class="performance-card income-card">

                <div class="performance-icon">
                    ৳
                </div>

                <span>
                    Total Income
                </span>

                <strong>
                    ৳<?= number_format(
                        $totalIncome,
                        2
                    ) ?>
                </strong>

                <small>
                    completed sales
                </small>

            </div>


            <!-- RATING -->

            <div class="performance-card rating-performance">

                <div class="performance-icon">
                    ★
                </div>

                <span>
                    Seller Rating
                </span>

                <strong>
                    <?= number_format(
                        $averageRating,
                        1
                    ) ?>
                    / 5
                </strong>

                <small>
                    <?= $totalReviews ?>
                    reviews
                </small>

            </div>


            <!-- SALES -->

            <div class="performance-card">

                <div class="performance-icon">
                    ✓
                </div>

                <span>
                    Completed Sales
                </span>

                <strong>
                    <?= $totalSales ?>
                </strong>

                <small>
                    completed purchases
                </small>

            </div>


            <!-- RESERVATIONS -->

            <div class="performance-card">

                <div class="performance-icon">
                    ♢
                </div>

                <span>
                    Completed Reservations
                </span>

                <strong>
                    <?= $completedReservations ?>
                </strong>

                <small>
                    successfully completed
                </small>

            </div>


        </div>

    </div>


    <!-- =====================================================
         BEST PRODUCT + LOW STOCK
    ====================================================== -->

    <div class="profile-highlight-grid">


        <div class="profile-highlight-card">

            <p class="dashboard-label">
                TOP PERFORMANCE
            </p>

            <h2>
                Best Selling Product
            </h2>


            <?php if ($bestProduct): ?>

                <div class="best-product">

                    <div class="best-product-icon">
                        🏆
                    </div>

                    <div>

                        <strong>
                            <?= htmlspecialchars(
                                $bestProduct["ProductName"]
                            ) ?>
                        </strong>

                        <span>
                            <?= (int)
                                $bestProduct["SoldQuantity"] ?>

                            units sold
                        </span>

                    </div>

                </div>

            <?php else: ?>

                <div class="highlight-empty">

                    No completed sales yet.

                </div>

            <?php endif; ?>

        </div>


        <div class="profile-highlight-card">

            <p class="dashboard-label">
                INVENTORY
            </p>

            <h2>
                Stock Overview
            </h2>

            <div class="stock-overview">

                <div>

                    <strong>
                        <?= $availableProducts ?>
                    </strong>

                    <span>
                        Available Products
                    </span>

                </div>


                <div>

                    <strong>
                        <?= $lowStockProducts ?>
                    </strong>

                    <span>
                        Low Stock
                    </span>

                </div>

            </div>

        </div>


    </div>


    <!-- =====================================================
         MY PRODUCTS
    ====================================================== -->

    <div class="profile-section">

        <div class="profile-section-title">

            <p class="dashboard-label">
                MY PRODUCTS
            </p>

            <h2>
                Products & Performance
            </h2>

        </div>


        <?php if (empty($products)): ?>


            <div class="profile-empty">

                <div>
                    ◈
                </div>

                <h3>
                    No Products Yet
                </h3>

                <p>
                    You have not added any products.
                </p>

                <a
                    href="../products/add.php"
                    class="profile-edit-button"
                >
                    Add Product
                </a>

            </div>


        <?php else: ?>


            <div class="profile-products-container">

                <table class="profile-products-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Sold
                            </th>

                            <th>
                                Rating
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach (
                            $products
                            as $product
                        ): ?>


                            <tr>


                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $product[
                                                "ProductName"
                                            ]
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= htmlspecialchars(
                                            $product[
                                                "Category"
                                            ]
                                        ) ?>
                                    </small>

                                </td>


                                <td>

                                    ৳<?= number_format(
                                        $product["Price"],
                                        2
                                    ) ?>

                                </td>


                                <td>

                                    <?= (int)
                                        $product["Stock"] ?>

                                </td>


                                <td>

                                    <?= (int)
                                        $product[
                                            "SoldQuantity"
                                        ] ?>

                                </td>


                                <td>

                                    <div class="product-rating-cell">

                                        <?= showProfileStars(
                                            $product[
                                                "ProductRating"
                                            ]
                                        ) ?>

                                        <strong>

                                            <?= number_format(
                                                (float)
                                                $product[
                                                    "ProductRating"
                                                ],
                                                1
                                            ) ?>

                                        </strong>

                                        <small>

                                            <?= (int)
                                                $product[
                                                    "ProductReviews"
                                                ] ?>

                                            reviews

                                        </small>

                                    </div>

                                </td>


                                <td>

                                    <?php

                                    if (
                                        $product["Status"]
                                        === "Unavailable"
                                    ) {

                                        $status =
                                            "Unavailable";

                                    } elseif (
                                        $product["Stock"] <= 0
                                    ) {

                                        $status =
                                            "Out of Stock";

                                    } else {

                                        $status =
                                            "Available";

                                    }

                                    ?>

                                    <span
                                        class="profile-status"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ) ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </div>


    <!-- =====================================================
         CUSTOMER REVIEWS
    ====================================================== -->

    <div class="profile-section">

        <div class="profile-section-title">

            <p class="dashboard-label">
                CUSTOMER FEEDBACK
            </p>

            <h2>
                Recent Reviews
            </h2>

        </div>


        <?php if (empty($recentReviews)): ?>


            <div class="profile-empty">

                <div>
                    ★
                </div>

                <h3>
                    No Reviews Yet
                </h3>

                <p>
                    Customer reviews and ratings will appear here.
                </p>

            </div>


        <?php else: ?>


            <div class="profile-reviews-list">


                <?php foreach (
                    $recentReviews
                    as $review
                ): ?>


                    <div class="profile-review-card">


                        <div class="review-left">

                            <div class="review-avatar">

                                <?= strtoupper(
                                    substr(
                                        $review["BuyerName"],
                                        0,
                                        1
                                    )
                                ) ?>

                            </div>


                            <div>

                                <strong>
                                    <?= htmlspecialchars(
                                        $review[
                                            "BuyerName"
                                        ]
                                    ) ?>
                                </strong>

                                <span>

                                    Product:

                                    <?= htmlspecialchars(
                                        $review[
                                            "ProductName"
                                        ] ?? "Product"
                                    ) ?>

                                </span>

                            </div>

                        </div>


                        <div class="review-right">

                            <div>

                                <?= showProfileStars(
                                    $review["Rating"]
                                ) ?>

                            </div>

                            <small>

                                <?= htmlspecialchars(
                                    $review[
                                        "FeedbackDate"
                                    ]
                                ) ?>

                            </small>

                        </div>


                        <?php if (
                            !empty(
                                $review["Comment"]
                            )
                        ): ?>

                            <p class="profile-review-comment">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $review["Comment"]
                                    )
                                ) ?>

                            </p>

                        <?php else: ?>

                            <p class="profile-no-comment">

                                No written feedback provided.

                            </p>

                        <?php endif; ?>


                    </div>


                <?php endforeach; ?>


            </div>


            <div class="view-all-feedback">

                <a
                    href="../feedback/index.php"
                    class="profile-edit-button"
                >
                    View All Feedback →
                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


<style>

/* =========================================================
   MY PROFILE PAGE
========================================================= */

.my-profile-page {

    position: relative;

    min-height: calc(100vh - 180px);

    padding: 25px 10px 70px;

    overflow: hidden;

}


/* =========================================================
   HEADER
========================================================= */

.profile-page-header {

    position: relative;

    z-index: 2;

    text-align: center;

    margin-bottom: 35px;

}


.profile-page-header h1 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 38px;

    font-weight: 500;

}


.profile-description {

    color: var(--gray-text);

    font-size: 13px;

    margin-top: 12px;

}


/* =========================================================
   MAIN PROFILE CARD
========================================================= */

.profile-main-card {

    position: relative;

    z-index: 2;

    max-width: 1100px;

    margin: 0 auto 35px;

    padding: 30px;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 19px;

    box-shadow:
        0 12px 35px rgba(70,54,83,.07);

    display: flex;

    align-items: center;

    gap: 25px;

}


.profile-avatar {

    width: 78px;

    height: 78px;

    flex-shrink: 0;

    border-radius: 50%;

    background: var(--light-lavender);

    border: 1px solid var(--border);

    color: var(--deep-purple);

    display: flex;

    align-items: center;

    justify-content: center;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 31px;

    font-weight: 600;

}


.profile-main-info {

    flex: 1;

}


.profile-small-label {

    color: var(--purple);

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 2px;

}


.profile-main-info h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 26px;

    font-weight: 500;

}


.profile-main-info h3 {

    color: var(--purple);

    font-size: 15px;

    font-weight: 600;

    margin-top: 2px;

}


.profile-rating-line {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 10px;

}


.profile-rating-line strong {

    color: var(--deep-purple);

    font-size: 13px;

}


.profile-rating-line span:last-child {

    color: var(--light-text);

    font-size: 11px;

}


.profile-edit-area {

    margin-left: auto;

}


.profile-edit-button {

    display: inline-block;

    padding: 10px 18px;

    border-radius: 8px;

    background: var(--deep-purple);

    color: var(--white);

    font-size: 12px;

    font-weight: 600;

    transition: .25s ease;

}


.profile-edit-button:hover {

    background: var(--purple);

    transform: translateY(-1px);

}


/* =========================================================
   STARS
========================================================= */

.profile-stars {

    white-space: nowrap;

    letter-spacing: 1px;

    font-size: 17px;

}


.star-full {

    color: var(--gold);

}


.star-empty {

    color: #D9D3DD;

}


.star-half {

    background:
        linear-gradient(
            90deg,
            var(--gold) 50%,
            #D9D3DD 50%
        );

    -webkit-background-clip: text;

    background-clip: text;

    -webkit-text-fill-color: transparent;

}


/* =========================================================
   SECTION
========================================================= */

.profile-section {

    position: relative;

    z-index: 2;

    max-width: 1100px;

    margin: 0 auto 40px;

}


.profile-section-title {

    margin-bottom: 20px;

}


.profile-section-title h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 27px;

    font-weight: 500;

}


/* =========================================================
   INFORMATION GRID
========================================================= */

.profile-info-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

}


.profile-info-box {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 13px;

    padding: 18px;

}


.profile-info-box span {

    display: block;

    color: var(--light-text);

    font-size: 11px;

    margin-bottom: 6px;

}


.profile-info-box strong {

    color: var(--deep-purple);

    font-size: 14px;

    word-break: break-word;

}


/* =========================================================
   PERFORMANCE GRID
========================================================= */

.performance-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

}


.performance-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 16px;

    padding: 23px;

    min-height: 165px;

    box-shadow:
        0 7px 22px rgba(70,54,83,.05);

}


.performance-icon {

    width: 39px;

    height: 39px;

    border-radius: 10px;

    background: var(--light-lavender);

    color: var(--gold);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    margin-bottom: 14px;

}


.performance-card > span {

    display: block;

    color: var(--gray-text);

    font-size: 12px;

}


.performance-card > strong {

    display: block;

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 27px;

    margin-top: 4px;

}


.performance-card > small {

    color: var(--light-text);

    font-size: 10px;

}


.income-card > strong {

    font-size: 23px;

}


.rating-performance > strong {

    color: var(--gold);

}


/* =========================================================
   HIGHLIGHTS
========================================================= */

.profile-highlight-grid {

    position: relative;

    z-index: 2;

    max-width: 1100px;

    margin: 0 auto 40px;

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

}


.profile-highlight-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 16px;

    padding: 25px;

}


.profile-highlight-card h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 23px;

    font-weight: 500;

    margin-bottom: 20px;

}


.best-product {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 15px;

    background: var(--light-lavender);

    border-radius: 12px;

}


.best-product-icon {

    font-size: 28px;

}


.best-product strong {

    display: block;

    color: var(--deep-purple);

    font-size: 14px;

}


.best-product span {

    display: block;

    color: var(--gray-text);

    font-size: 11px;

    margin-top: 4px;

}


.highlight-empty {

    color: var(--gray-text);

    font-size: 13px;

}


.stock-overview {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

}


.stock-overview div {

    background: var(--light-lavender);

    padding: 18px;

    border-radius: 12px;

}


.stock-overview strong {

    display: block;

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 28px;

}


.stock-overview span {

    color: var(--gray-text);

    font-size: 11px;

}


/* =========================================================
   PRODUCTS TABLE
========================================================= */

.profile-products-container {

    overflow-x: auto;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 16px;

}


.profile-products-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 850px;

}


.profile-products-table th {

    background: var(--light-lavender);

    color: var(--deep-purple);

    padding: 15px;

    text-align: left;

    font-size: 11px;

    letter-spacing: .3px;

}


.profile-products-table td {

    padding: 16px 15px;

    border-top: 1px solid var(--border);

    color: var(--gray-text);

    font-size: 12px;

}


.profile-products-table td strong {

    display: block;

    color: var(--deep-purple);

    font-size: 13px;

}


.profile-products-table td small {

    display: block;

    color: var(--light-text);

    font-size: 10px;

    margin-top: 3px;

}


.product-rating-cell {

    display: flex;

    align-items: center;

    gap: 5px;

}


.product-rating-cell .profile-stars {

    font-size: 14px;

}


.product-rating-cell strong {

    display: inline !important;

    font-size: 12px !important;

}


.product-rating-cell small {

    margin-left: 3px;

}


.profile-status {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    background: var(--light-lavender);

    color: var(--purple);

    font-size: 10px;

    font-weight: 600;

}


/* =========================================================
   REVIEWS
========================================================= */

.profile-reviews-list {

    display: grid;

    gap: 15px;

}


.profile-review-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 15px;

    padding: 20px;

}


.review-left {

    display: flex;

    align-items: center;

    gap: 12px;

}


.review-avatar {

    width: 40px;

    height: 40px;

    border-radius: 50%;

    background: var(--light-lavender);

    color: var(--deep-purple);

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 700;

    font-size: 13px;

}


.review-left strong {

    display: block;

    color: var(--deep-purple);

    font-size: 13px;

}


.review-left span {

    display: block;

    color: var(--light-text);

    font-size: 10px;

    margin-top: 3px;

}


.review-right {

    float: right;

    text-align: right;

    margin-top: -40px;

}


.review-right small {

    display: block;

    color: var(--light-text);

    font-size: 9px;

    margin-top: 4px;

}


.profile-review-comment {

    clear: both;

    color: var(--gray-text);

    font-size: 13px;

    line-height: 1.6;

    margin-top: 17px;

    padding-top: 14px;

    border-top: 1px solid var(--border);

}


.profile-no-comment {

    clear: both;

    color: var(--light-text);

    font-size: 11px;

    font-style: italic;

    margin-top: 17px;

    padding-top: 14px;

    border-top: 1px solid var(--border);

}


.view-all-feedback {

    text-align: center;

    margin-top: 20px;

}


/* =========================================================
   EMPTY
========================================================= */

.profile-empty {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 16px;

    text-align: center;

    padding: 45px 25px;

}


.profile-empty > div {

    color: var(--gold);

    font-size: 35px;

}


.profile-empty h3 {

    color: var(--deep-purple);

    margin-top: 8px;

}


.profile-empty p {

    color: var(--gray-text);

    font-size: 12px;

    margin: 5px 0 18px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .performance-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

    .profile-info-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 700px) {

    .profile-main-card {

        flex-direction: column;

        text-align: center;

    }

    .profile-rating-line {

        justify-content: center;

    }

    .profile-edit-area {

        margin-left: 0;

    }

    .profile-highlight-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 550px) {

    .performance-grid {

        grid-template-columns: 1fr;

    }

    .profile-info-grid {

        grid-template-columns: 1fr;

    }

    .profile-main-info h2 {

        font-size: 22px;

    }

    .profile-page-header h1 {

        font-size: 31px;

    }

}

</style>


<?php

include "../../includes/footer.php";

?>