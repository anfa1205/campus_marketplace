<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";

$buyerID = (int) $_SESSION["user_id"];

$sellerID = isset($_GET["seller_id"])
    ? (int) $_GET["seller_id"]
    : 0;

$error = "";


/* =========================================================
   STAR FUNCTION
   ========================================================= */

function showStars($rating)
{
    $rating = max(0, min(5, (float) $rating));

    $full = floor($rating);
    $half = (($rating - $full) >= 0.5);

    $html = '<span class="feedback-stars">';

    for ($i = 1; $i <= 5; $i++) {

        if ($i <= $full) {

            $html .= '<span class="star-full">★</span>';

        } elseif ($i == $full + 1 && $half) {

            $html .= '<span class="star-half">★</span>';

        } else {

            $html .= '<span class="star-empty">☆</span>';

        }
    }

    $html .= '</span>';

    return $html;
}


/* =========================================================
   GET SELLER
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        sellerID,
        bussinessName,
        Name,
        AvgRating

    FROM seller

    WHERE sellerID = ?
");

$stmt->execute([
    $sellerID
]);

$seller = $stmt->fetch();


if (!$seller) {

    $error = "Seller not found.";

}


/* =========================================================
   SELLER OVERALL RATING
   ========================================================= */

$overallRating = 0;
$totalReviews = 0;

if ($seller) {

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(AVG(Rating), 0) AS AvgRating,
            COUNT(*) AS TotalReviews

        FROM feedback

        WHERE sellerID = ?
    ");

    $stmt->execute([
        $sellerID
    ]);

    $ratingData = $stmt->fetch();

    $overallRating =
        (float) $ratingData["AvgRating"];

    $totalReviews =
        (int) $ratingData["TotalReviews"];

}


/* =========================================================
   GET SELLER PRODUCTS
   ========================================================= */

$products = [];

if ($seller) {

    $stmt = $pdo->prepare("
        SELECT

            p.ProductID,
            p.ProductName,
            p.Description,
            p.Category,
            p.Price,
            p.Stock,
            p.Status,

            COALESCE(
                AVG(f.Rating),
                0
            ) AS AvgRating,

            COUNT(f.FeedbackID)
                AS ReviewCount

        FROM product p

        LEFT JOIN feedback f
            ON f.productID = p.ProductID

        WHERE p.SellerID = ?

        GROUP BY
            p.ProductID,
            p.ProductName,
            p.Description,
            p.Category,
            p.Price,
            p.Stock,
            p.Status

        ORDER BY
            p.ProductName ASC
    ");

    $stmt->execute([
        $sellerID
    ]);

    $products = $stmt->fetchAll();

}


/* =========================================================
   GET ALL REVIEWS FOR THIS SELLER
   ========================================================= */

$sellerReviews = [];

if ($seller) {

    $stmt = $pdo->prepare("
        SELECT

            f.FeedbackID,
            f.Rating,
            f.Comment,
            f.FeedbackDate,

            p.ProductName,

            b.Name AS BuyerName

        FROM feedback f

        INNER JOIN product p
            ON f.productID = p.ProductID

        INNER JOIN buyer b
            ON f.buyerID = b.BuyerID

        WHERE f.sellerID = ?

        ORDER BY
            f.FeedbackDate DESC
    ");

    $stmt->execute([
        $sellerID
    ]);

    $sellerReviews = $stmt->fetchAll();

}


/* =========================================================
   GET PRODUCT REVIEWS
   ========================================================= */

$productReviews = [];

if (!empty($products)) {

    $productIDs = array_column(
        $products,
        "ProductID"
    );

    $placeholders =
        implode(
            ",",
            array_fill(
                0,
                count($productIDs),
                "?"
            )
        );

    $stmt = $pdo->prepare("
        SELECT

            f.FeedbackID,
            f.productID,
            f.Rating,
            f.Comment,
            f.FeedbackDate,

            b.Name AS BuyerName

        FROM feedback f

        INNER JOIN buyer b
            ON f.buyerID = b.BuyerID

        WHERE f.sellerID = ?

        AND f.productID IN ($placeholders)

        ORDER BY
            f.FeedbackDate DESC
    ");

    $params = [
        $sellerID
    ];

    foreach ($productIDs as $id) {
        $params[] = $id;
    }

    $stmt->execute($params);

    $reviews = $stmt->fetchAll();

    foreach ($reviews as $review) {

        $productReviews[
            $review["productID"]
        ][] = $review;

    }
}

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


    <?php if ($error !== ""): ?>

        <div class="feedback-alert error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php else: ?>


        <!-- =================================================
             SELLER HEADER
        ================================================== -->

        <div class="dashboard-intro">

            <p class="dashboard-label">
                SELLER
            </p>

            <h1>
                <?= htmlspecialchars(
                    $seller["bussinessName"]
                ) ?>
            </h1>

            <div class="title-line"></div>

            <p class="dashboard-description">
                <?= htmlspecialchars(
                    $seller["Name"]
                ) ?>
            </p>

        </div>


        <!-- =================================================
             OVERALL RATING
        ================================================== -->

        <div class="seller-overall-rating">

            <div class="overall-number">

                <?= number_format(
                    $overallRating,
                    1
                ) ?>

            </div>

            <div class="overall-details">

                <div class="overall-stars">

                    <?= showStars(
                        $overallRating
                    ) ?>

                </div>

                <p>
                    <?= (int) $totalReviews ?>

                    <?= $totalReviews == 1
                        ? "customer review"
                        : "customer reviews" ?>
                </p>

            </div>

        </div>


        <!-- =================================================
             PRODUCTS
        ================================================== -->

        <div class="section-heading">

            <p class="dashboard-label">
                PRODUCTS
            </p>

            <h2>
                Products & Ratings
            </h2>

        </div>


        <?php if (empty($products)): ?>

            <div class="feedback-empty">

                <div class="feedback-empty-icon">
                    ♧
                </div>

                <h2>
                    No Products
                </h2>

                <p>
                    This seller has no products yet.
                </p>

            </div>

        <?php else: ?>

            <div class="feedback-product-grid">

                <?php foreach ($products as $product): ?>

                    <?php

                    $pid =
                        (int) $product["ProductID"];

                    $reviewsForProduct =
                        $productReviews[$pid] ?? [];

                    ?>

                    <div class="feedback-product-card">


                        <!-- PRODUCT INFO -->

                        <div class="product-card-header">

                            <div>

                                <p class="product-category">
                                    <?= htmlspecialchars(
                                        $product["Category"]
                                    ) ?>
                                </p>

                                <h2>
                                    <?= htmlspecialchars(
                                        $product["ProductName"]
                                    ) ?>
                                </h2>

                            </div>

                        </div>


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


                        <!-- PRODUCT RATING -->

                        <div class="product-rating-box">

                            <div>

                                <?= showStars(
                                    $product["AvgRating"]
                                ) ?>

                            </div>

                            <strong>

                                <?= number_format(
                                    (float)
                                    $product["AvgRating"],
                                    1
                                ) ?>

                                / 5

                            </strong>

                            <span>

                                <?= (int)
                                    $product["ReviewCount"] ?>

                                reviews

                            </span>

                        </div>


                        <!-- REVIEWS -->

                        <div class="product-review-section">

                            <h3>
                                Customer Reviews
                            </h3>


                            <?php if (
                                empty(
                                    $reviewsForProduct
                                )
                            ): ?>

                                <p class="no-reviews">
                                    No reviews yet.
                                </p>

                            <?php else: ?>

                                <div class="review-list">

                                    <?php foreach (
                                        $reviewsForProduct
                                        as $review
                                    ): ?>

                                        <div class="review-item">

                                            <div
                                                class="review-top"
                                            >

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $review["BuyerName"]
                                                    ) ?>
                                                </strong>

                                                <span>

                                                    <?= showStars(
                                                        $review["Rating"]
                                                    ) ?>

                                                </span>

                                            </div>


                                            <?php if (
                                                !empty(
                                                    $review["Comment"]
                                                )
                                            ): ?>

                                                <p>
                                                    <?= nl2br(
                                                        htmlspecialchars(
                                                            $review["Comment"]
                                                        )
                                                    ) ?>
                                                </p>

                                            <?php endif; ?>


                                            <small>

                                                <?= htmlspecialchars(
                                                    $review["FeedbackDate"]
                                                ) ?>

                                            </small>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- RATE BUTTON -->

                        <a
                            href="rate.php?product_id=<?= $pid ?>"
                            class="feedback-rate-button"
                        >
                            ★ Rate / Update Review
                        </a>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             ALL SELLER REVIEWS
        ================================================== -->

        <div class="section-heading seller-review-heading">

            <p class="dashboard-label">
                CUSTOMER FEEDBACK
            </p>

            <h2>
                Reviews About This Seller
            </h2>

        </div>


        <?php if (empty($sellerReviews)): ?>

            <div class="feedback-empty">

                <div class="feedback-empty-icon">
                    ★
                </div>

                <h2>
                    No Seller Reviews Yet
                </h2>

                <p>
                    No customers have reviewed this seller yet.
                </p>

            </div>

        <?php else: ?>

            <div class="seller-review-list">

                <?php foreach (
                    $sellerReviews
                    as $review
                ): ?>

                    <div class="seller-review-card">

                        <div class="review-top">

                            <div>

                                <strong>
                                    <?= htmlspecialchars(
                                        $review["BuyerName"]
                                    ) ?>
                                </strong>

                                <span class="review-product-name">

                                    Product:
                                    <?= htmlspecialchars(
                                        $review["ProductName"]
                                    ) ?>

                                </span>

                            </div>

                            <?= showStars(
                                $review["Rating"]
                            ) ?>

                        </div>


                        <?php if (
                            !empty(
                                $review["Comment"]
                            )
                        ): ?>

                            <p class="seller-review-comment">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $review["Comment"]
                                    )
                                ) ?>

                            </p>

                        <?php endif; ?>


                        <small>

                            <?= htmlspecialchars(
                                $review["FeedbackDate"]
                            ) ?>

                        </small>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <div class="feedback-back">

            <a href="index.php">
                ← Back to Sellers
            </a>

        </div>

    <?php endif; ?>

</div>


<style>

.feedback-alert {
    max-width: 1100px;
    margin: 0 auto 20px;
    padding: 14px 18px;
    border-radius: 10px;
    background: #F4E5E8;
    color: var(--danger);
    border: 1px solid #E3C5C9;
}


/* OVERALL RATING */

.seller-overall-rating {

    max-width: 1100px;

    margin: 0 auto 35px;

    padding: 28px;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 17px;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 25px;

    box-shadow:
        0 10px 30px rgba(70,54,83,.06);
}

.overall-number {

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 48px;

    color: var(--deep-purple);
}

.overall-stars {

    margin-bottom: 4px;
}

.overall-details p {

    color: var(--gray-text);

    font-size: 13px;
}


/* SECTION */

.section-heading {

    max-width: 1100px;

    margin:
        0 auto 20px;
}

.section-heading h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-weight: 500;

    font-size: 27px;
}


/* PRODUCT GRID */

.feedback-product-grid {

    max-width: 1100px;

    margin: 0 auto;

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 22px;
}


/* PRODUCT CARD */

.feedback-product-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 17px;

    padding: 25px;

    box-shadow:
        0 10px 30px rgba(70,54,83,.06);
}

.product-category {

    color: var(--purple);

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 2px;

    margin-bottom: 5px;
}

.feedback-product-card h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 22px;

    font-weight: 500;
}

.product-description {

    color: var(--gray-text);

    font-size: 13px;

    margin-top: 10px;

    line-height: 1.6;
}


/* PRODUCT RATING */

.product-rating-box {

    margin-top: 18px;

    padding: 13px;

    background: var(--light-lavender);

    border-radius: 10px;

    display: flex;

    align-items: center;

    gap: 10px;
}

.product-rating-box strong {

    color: var(--deep-purple);

    font-size: 13px;
}

.product-rating-box span:last-child {

    color: var(--light-text);

    font-size: 11px;
}


/* STARS */

.feedback-stars {

    white-space: nowrap;

    letter-spacing: 1px;

    font-size: 16px;
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


/* PRODUCT REVIEWS */

.product-review-section {

    margin-top: 20px;
}

.product-review-section h3 {

    color: var(--deep-purple);

    font-size: 15px;

    margin-bottom: 10px;
}

.review-list {

    display: grid;

    gap: 10px;
}

.review-item {

    padding: 13px;

    background: #FCFAFD;

    border: 1px solid var(--border);

    border-radius: 10px;
}

.review-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;
}

.review-top strong {

    color: var(--deep-purple);

    font-size: 13px;
}

.review-item p {

    color: var(--gray-text);

    font-size: 13px;

    line-height: 1.5;

    margin-top: 7px;
}

.review-item small,
.seller-review-card small {

    display: block;

    color: var(--light-text);

    font-size: 10px;

    margin-top: 8px;
}

.no-reviews {

    color: var(--light-text);

    font-size: 12px;

    font-style: italic;
}


/* RATE BUTTON */

.feedback-rate-button {

    display: block;

    margin-top: 18px;

    text-align: center;

    padding: 11px;

    background: var(--deep-purple);

    color: white;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 600;
}

.feedback-rate-button:hover {

    background: var(--purple);

    color: white;
}


/* SELLER REVIEWS */

.seller-review-heading {

    margin-top: 50px;
}

.seller-review-list {

    max-width: 1100px;

    margin: 0 auto;

    display: grid;

    gap: 15px;
}

.seller-review-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 15px;

    padding: 20px;

    box-shadow:
        0 8px 25px rgba(70,54,83,.05);
}

.review-product-name {

    display: block;

    color: var(--light-text);

    font-size: 11px;

    margin-top: 4px;
}

.seller-review-comment {

    color: var(--gray-text);

    font-size: 13px;

    line-height: 1.6;

    margin-top: 12px;
}


/* BACK */

.feedback-back {

    max-width: 1100px;

    margin: 30px auto 0;
}

.feedback-back a {

    color: var(--purple);

    font-size: 13px;

    font-weight: 600;
}


.feedback-empty {

    max-width: 1100px;

    margin: 0 auto;

    text-align: center;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 17px;

    padding: 60px 30px;
}

.feedback-empty-icon {

    font-size: 40px;

    color: var(--gold);

    margin-bottom: 12px;
}

.feedback-empty h2 {

    color: var(--deep-purple);
}

.feedback-empty p {

    color: var(--gray-text);
}


@media (max-width: 850px) {

    .feedback-product-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 600px) {

    .seller-overall-rating {
        flex-direction: column;
        text-align: center;
    }

    .review-top {
        flex-direction: column;
    }

}

</style>


<?php include "../../includes/footer.php"; ?>