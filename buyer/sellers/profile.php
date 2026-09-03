<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$seller_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


/* =========================================================
   GET SELLER INFORMATION + RATING
   ========================================================= */

$stmt = $pdo->prepare(
    "SELECT
        s.sellerID,
        s.StudentID,
        s.department,
        s.Name,
        s.Mail,
        s.bussinessName,
        s.Phone,

        COALESCE(
            (
                SELECT AVG(f.Rating)
                FROM feedback f
                WHERE f.sellerID = s.sellerID
            ),
            0
        ) AS average_rating

     FROM seller s

     WHERE s.sellerID = ?"
);

$stmt->execute([
    $seller_id
]);

$seller = $stmt->fetch();


if (!$seller) {

    include "../../includes/header.php";

    echo '
        <div class="profile-not-found">
            <h2>Seller not found.</h2>
            <a href="index.php" class="btn">
                Back to Sellers
            </a>
        </div>
    ';

    include "../../includes/footer.php";

    exit;
}


/* =========================================================
   GET SELLER PRODUCTS
   ========================================================= */

$stmt = $pdo->prepare(
    "SELECT
        ProductID,
        ProductName,
        Description,
        Category,
        Stock,
        Price,
        Status

     FROM product

     WHERE SellerID = ?

     ORDER BY ProductID DESC"
);

$stmt->execute([
    $seller_id
]);

$products = $stmt->fetchAll();


/* =========================================================
   GET CUSTOMER REVIEWS
   ========================================================= */

$stmt = $pdo->prepare(
    "SELECT
        f.FeedbackID,
        f.Rating,
        f.Comment,
        f.FeedbackDate,
        b.Name AS BuyerName,
        pu.PurchaseType

     FROM feedback f

     INNER JOIN buyer b
        ON f.buyerID = b.BuyerID

     LEFT JOIN purchase pu
        ON f.purchaseID = pu.PurchaseID

     WHERE f.sellerID = ?

     ORDER BY f.FeedbackDate DESC"
);

$stmt->execute([
    $seller_id
]);

$reviews = $stmt->fetchAll();


/* =========================================================
   RATING
   ========================================================= */

$rating = round(
    floatval($seller["average_rating"]),
    1
);

$full_stars = floor($rating);

$half_star =
    ($rating - $full_stars >= 0.5)
    ? 1
    : 0;

$empty_stars =
    5 - $full_stars - $half_star;


include "../../includes/header.php";

?>


<div class="seller-profile-page">


    <!-- =====================================================
         PROFILE HEADER
         ===================================================== -->

    <div class="seller-profile-hero">

        <div class="seller-profile-icon">

            <?= strtoupper(
                substr(
                    $seller["bussinessName"],
                    0,
                    1
                )
            ) ?>

        </div>


        <div class="seller-profile-title">

            <p class="dashboard-label">
                SELLER PROFILE
            </p>

            <h1>
                <?= htmlspecialchars(
                    $seller["bussinessName"]
                ) ?>
            </h1>

            <p class="seller-profile-subtitle">
                <?= htmlspecialchars(
                    $seller["department"]
                ) ?>
            </p>

        </div>

    </div>


    <!-- =====================================================
         SELLER INFORMATION
         ===================================================== -->

    <div class="seller-profile-grid">


        <div class="seller-info-card">

            <div class="seller-section-title">
                Seller Information
            </div>


            <div class="seller-info-row">

                <span>
                    Seller Name
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["Name"]
                    ) ?>
                </strong>

            </div>


            <div class="seller-info-row">

                <span>
                    Department
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["department"]
                    ) ?>
                </strong>

            </div>


            <div class="seller-info-row">

                <span>
                    Business
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["bussinessName"]
                    ) ?>
                </strong>

            </div>


            <div class="seller-info-row">

                <span>
                    Phone
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["Phone"]
                    ) ?>
                </strong>

            </div>


            <div class="seller-info-row">

                <span>
                    Email
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $seller["Mail"]
                    ) ?>
                </strong>

            </div>

        </div>


        <!-- =================================================
             RATING
             ================================================= -->

        <div class="seller-rating-card">

            <div class="seller-section-title">
                Seller Rating
            </div>


            <div class="big-rating">

                <span class="rating-number">
                    <?= number_format(
                        $rating,
                        1
                    ) ?>
                </span>


                <div class="rating-stars">

                    <?php for (
                        $i = 0;
                        $i < $full_stars;
                        $i++
                    ): ?>

                        <span class="star filled">
                            ★
                        </span>

                    <?php endfor; ?>


                    <?php if ($half_star): ?>

                        <span class="star half">
                            ★
                        </span>

                    <?php endif; ?>


                    <?php for (
                        $i = 0;
                        $i < $empty_stars;
                        $i++
                    ): ?>

                        <span class="star empty">
                            ★
                        </span>

                    <?php endfor; ?>

                </div>

            </div>


            <p class="rating-description">

                <?= $rating > 0
                    ? "Average seller rating"
                    : "No ratings yet"
                ?>

            </p>

        </div>

    </div>


    <!-- =====================================================
         CHAT BUTTON
         ===================================================== -->

    <div class="seller-chat-section">

        <a
            href="../chat/index.php?seller_id=<?= $seller["sellerID"] ?>"
            class="seller-chat-button"
        >

            <span>
                💬
            </span>

            Chat with Seller

        </a>

    </div>


    <!-- =====================================================
         PRODUCTS
         ===================================================== -->

    <div class="seller-products-section">

        <div class="seller-products-heading">

            <div>

                <p class="dashboard-label">
                    MARKETPLACE
                </p>

                <h2>
                    Products
                </h2>

            </div>


            <span class="product-count">

                <?= count($products) ?>

                <?= count($products) == 1
                    ? "Product"
                    : "Products" ?>

            </span>

        </div>


        <?php if (empty($products)): ?>

            <div class="no-products">

                <h3>
                    No products available
                </h3>

                <p>
                    This seller has not added any products yet.
                </p>

            </div>


        <?php else: ?>


            <div class="seller-products-grid">


                <?php foreach ($products as $product): ?>


                    <?php

                    $status =
                        $product["Status"];

                    if (
                        intval($product["Stock"]) <= 0
                    ) {

                        $display_status =
                            "Out of Stock";

                        $status_class =
                            "out";

                    } elseif (
                        strtolower($status)
                        === "unavailable"
                    ) {

                        $display_status =
                            "Unavailable";

                        $status_class =
                            "unavailable";

                    } else {

                        $display_status =
                            "Available";

                        $status_class =
                            "available";
                    }

                    ?>


                    <div class="seller-product-card">


                        <div class="product-card-top">

                            <span class="product-category">

                                <?= htmlspecialchars(
                                    $product["Category"]
                                ) ?>

                            </span>


                            <span
                                class="product-status
                                <?= $status_class ?>"
                            >

                                <?= $display_status ?>

                            </span>

                        </div>


                        <h3>
                            <?= htmlspecialchars(
                                $product["ProductName"]
                            ) ?>
                        </h3>


                        <?php if (
                            !empty(
                                $product["Description"]
                            )
                        ): ?>

                            <p class="product-description">

                                <?= htmlspecialchars(
                                    $product["Description"]
                                ) ?>

                            </p>

                        <?php endif; ?>


                        <div class="product-card-bottom">

                            <strong class="product-price">

                                ৳<?= number_format(
                                    floatval(
                                        $product["Price"]
                                    ),
                                    2
                                ) ?>

                            </strong>


                            <span class="product-stock">

                                Stock:
                                <?= intval(
                                    $product["Stock"]
                                ) ?>

                            </span>

                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </div>


    <!-- =====================================================
         CUSTOMER REVIEWS
         ===================================================== -->

    <div class="seller-reviews-section">

        <div class="seller-products-heading">

            <div>

                <p class="dashboard-label">
                    CUSTOMER FEEDBACK
                </p>

                <h2>
                    Customer Reviews
                </h2>

            </div>


            <span class="product-count">

                <?= count($reviews) ?>

                <?= count($reviews) == 1
                    ? "Review"
                    : "Reviews" ?>

            </span>

        </div>


        <?php if (empty($reviews)): ?>

            <div class="no-products">

                <h3>
                    No reviews yet
                </h3>

                <p>
                    This seller has not received any customer reviews yet.
                </p>

            </div>


        <?php else: ?>


            <div class="seller-reviews-list">


                <?php foreach ($reviews as $review): ?>


                    <?php

                    $review_rating =
                        floatval(
                            $review["Rating"]
                        );

                    $review_full_stars =
                        floor(
                            $review_rating
                        );

                    $review_half_star =
                        (
                            $review_rating
                            - $review_full_stars
                            >= 0.5
                        )
                        ? 1
                        : 0;

                    $review_empty_stars =
                        5
                        - $review_full_stars
                        - $review_half_star;

                    ?>


                    <div class="seller-review-card">


                        <div class="review-header">


                            <div>

                                <strong class="review-buyer">

                                    <?= htmlspecialchars(
                                        $review["BuyerName"]
                                    ) ?>

                                </strong>

                                <br>

                                <span class="review-date">

                                    <?= htmlspecialchars(
                                        date(
                                            "d M Y",
                                            strtotime(
                                                $review["FeedbackDate"]
                                            )
                                        )
                                    ) ?>

                                </span>

                            </div>


                            <div class="review-stars">


                                <?php for (
                                    $i = 0;
                                    $i < $review_full_stars;
                                    $i++
                                ): ?>

                                    <span class="star filled">
                                        ★
                                    </span>

                                <?php endfor; ?>


                                <?php if ($review_half_star): ?>

                                    <span class="star half">
                                        ★
                                    </span>

                                <?php endif; ?>


                                <?php for (
                                    $i = 0;
                                    $i < $review_empty_stars;
                                    $i++
                                ): ?>

                                    <span class="star empty">
                                        ★
                                    </span>

                                <?php endfor; ?>


                                <span class="review-rating-number">

                                    <?= number_format(
                                        $review_rating,
                                        1
                                    ) ?>

                                </span>

                            </div>

                        </div>


                        <?php if (
                            !empty(
                                $review["Comment"]
                            )
                        ): ?>

                            <p class="review-comment">

                                <?= htmlspecialchars(
                                    $review["Comment"]
                                ) ?>

                            </p>

                        <?php else: ?>

                            <p class="review-comment">

                                No comment provided.

                            </p>

                        <?php endif; ?>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </div>


    <!-- =====================================================
         BACK BUTTON
         ===================================================== -->

    <div class="seller-profile-back">

        <a
            href="index.php"
            class="btn"
        >
            ← Back to Sellers
        </a>

    </div>


</div>


<?php

include "../../includes/footer.php";

?>