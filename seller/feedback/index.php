<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

include "../../includes/header.php";

$sellerID = (int) $_SESSION["user_id"];


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
   SELLER
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


/* =========================================================
   OVERALL RATING
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT

        COALESCE(
            AVG(Rating),
            0
        ) AS AvgRating,

        COUNT(*) AS TotalReviews

    FROM feedback

    WHERE sellerID = ?
");

$stmt->execute([
    $sellerID
]);

$summary = $stmt->fetch();


$averageRating =
    (float) $summary["AvgRating"];

$totalReviews =
    (int) $summary["TotalReviews"];


/* =========================================================
   PRODUCT RATINGS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT

        p.ProductID,
        p.ProductName,

        COALESCE(
            AVG(f.Rating),
            0
        ) AS AvgRating,

        COUNT(f.FeedbackID)
            AS ReviewCount

    FROM product p

    LEFT JOIN feedback f
        ON f.productID = p.ProductID
        AND f.sellerID = ?

    WHERE p.SellerID = ?

    GROUP BY
        p.ProductID,
        p.ProductName

    ORDER BY
        p.ProductName ASC
");

$stmt->execute([
    $sellerID,
    $sellerID
]);

$products =
    $stmt->fetchAll();


/* =========================================================
   CUSTOMER FEEDBACK
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

    INNER JOIN product p
        ON f.productID = p.ProductID

    INNER JOIN buyer b
        ON f.buyerID = b.BuyerID

    WHERE f.sellerID = ?
      AND p.SellerID = ?

    ORDER BY
        f.FeedbackDate DESC
");

$stmt->execute([
    $sellerID,
    $sellerID
]);

$feedbacks =
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
            CUSTOMER FEEDBACK
        </p>

        <h1>
            <?= htmlspecialchars(
                $seller["bussinessName"]
            ) ?>
        </h1>

        <div class="title-line"></div>

        <p class="dashboard-description">
            View ratings and feedback from your customers.
        </p>

    </div>


    <!-- OVERALL RATING -->

    <div class="seller-feedback-summary">

        <div class="summary-number">

            <?= number_format(
                $averageRating,
                1
            ) ?>

        </div>

        <div>

            <?= showStars(
                $averageRating
            ) ?>

            <p>
                Overall Seller Rating
            </p>

        </div>

        <div class="summary-count">

            <strong>
                <?= $totalReviews ?>
            </strong>

            <span>
                Total Reviews
            </span>

        </div>

    </div>


    <!-- PRODUCT RATINGS -->

    <div class="section-heading">

        <p class="dashboard-label">
            PRODUCT RATINGS
        </p>

        <h2>
            Your Products
        </h2>

    </div>


    <div class="seller-product-rating-grid">

        <?php if (empty($products)): ?>

            <div class="feedback-empty">

                <h2>
                    No Products
                </h2>

            </div>

        <?php else: ?>

            <?php foreach ($products as $product): ?>

                <div class="seller-product-rating-card">

                    <h3>
                        <?= htmlspecialchars(
                            $product["ProductName"]
                        ) ?>
                    </h3>

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

            <?php endforeach; ?>

        <?php endif; ?>

    </div>


    <!-- INDIVIDUAL FEEDBACK -->

    <div class="section-heading feedback-heading">

        <p class="dashboard-label">
            CUSTOMER REVIEWS
        </p>

        <h2>
            Individual Feedback
        </h2>

    </div>


    <?php if (empty($feedbacks)): ?>

        <div class="feedback-empty">

            <div class="feedback-empty-icon">
                ★
            </div>

            <h2>
                No Customer Feedback Yet
            </h2>

            <p>
                Customer ratings and reviews will appear here.
            </p>

        </div>

    <?php else: ?>

        <div class="seller-feedback-list">

            <?php foreach ($feedbacks as $feedback): ?>

                <div class="seller-feedback-card">

                    <div class="feedback-review-top">

                        <div>

                            <strong>
                                <?= htmlspecialchars(
                                    $feedback["BuyerName"]
                                ) ?>
                            </strong>

                            <span class="review-product">

                                Product:
                                <?= htmlspecialchars(
                                    $feedback["ProductName"]
                                ) ?>

                            </span>

                        </div>

                        <div>

                            <?= showStars(
                                $feedback["Rating"]
                            ) ?>

                        </div>

                    </div>


                    <?php if (
                        !empty(
                            $feedback["Comment"]
                        )
                    ): ?>

                        <p class="customer-comment">

                            <?= nl2br(
                                htmlspecialchars(
                                    $feedback["Comment"]
                                )
                            ) ?>

                        </p>

                    <?php else: ?>

                        <p class="no-comment">
                            No written feedback provided.
                        </p>

                    <?php endif; ?>


                    <small class="feedback-date">

                        <?= htmlspecialchars(
                            $feedback["FeedbackDate"]
                        ) ?>

                    </small>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<style>

.feedback-stars {

    white-space: nowrap;

    letter-spacing: 2px;

    font-size: 18px;
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


/* SUMMARY */

.seller-feedback-summary {

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

.summary-number {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 48px;
}

.seller-feedback-summary p {

    color: var(--gray-text);

    font-size: 12px;

    margin-top: 5px;
}

.summary-count {

    margin-left: 25px;

    padding-left: 25px;

    border-left: 1px solid var(--border);

    text-align: center;
}

.summary-count strong {

    display: block;

    color: var(--deep-purple);

    font-size: 27px;
}

.summary-count span {

    color: var(--gray-text);

    font-size: 12px;
}


/* SECTION */

.section-heading {

    max-width: 1100px;

    margin: 0 auto 20px;
}

.section-heading h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 27px;

    font-weight: 500;
}


/* PRODUCT RATINGS */

.seller-product-rating-grid {

    max-width: 1100px;

    margin: 0 auto;

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;
}

.seller-product-rating-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 15px;

    padding: 20px;

    box-shadow:
        0 8px 25px rgba(70,54,83,.05);
}

.seller-product-rating-card h3 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-weight: 500;

    margin-bottom: 10px;
}

.seller-product-rating-card strong {

    color: var(--deep-purple);

    margin-left: 7px;
}

.seller-product-rating-card > span {

    display: block;

    color: var(--light-text);

    font-size: 11px;

    margin-top: 5px;
}


/* FEEDBACK */

.feedback-heading {

    margin-top: 50px;
}

.seller-feedback-list {

    max-width: 1100px;

    margin: 0 auto;

    display: grid;

    gap: 16px;
}

.seller-feedback-card {

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 15px;

    padding: 22px;

    box-shadow:
        0 8px 25px rgba(70,54,83,.05);
}

.feedback-review-top {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;
}

.feedback-review-top strong {

    display: block;

    color: var(--deep-purple);

    font-size: 14px;
}

.review-product {

    display: block;

    color: var(--light-text);

    font-size: 11px;

    margin-top: 4px;
}

.customer-comment {

    color: var(--gray-text);

    font-size: 13px;

    line-height: 1.6;

    margin-top: 15px;
}

.no-comment {

    color: var(--light-text);

    font-size: 12px;

    font-style: italic;

    margin-top: 15px;
}

.feedback-date {

    display: block;

    color: var(--light-text);

    font-size: 10px;

    margin-top: 13px;

    padding-top: 10px;

    border-top: 1px solid var(--border);
}


/* EMPTY */

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
}

.feedback-empty h2 {

    color: var(--deep-purple);
}

.feedback-empty p {

    color: var(--gray-text);
}


@media (max-width: 850px) {

    .seller-product-rating-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}

@media (max-width: 600px) {

    .seller-product-rating-grid {
        grid-template-columns: 1fr;
    }

    .seller-feedback-summary {
        flex-direction: column;
    }

    .summary-count {

        margin-left: 0;

        padding-left: 0;

        padding-top: 15px;

        border-left: none;

        border-top: 1px solid var(--border);
    }

    .feedback-review-top {
        flex-direction: column;
    }

}

</style>


<?php include "../../includes/footer.php"; ?>