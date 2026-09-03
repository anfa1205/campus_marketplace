<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";

$buyerID = (int) $_SESSION["user_id"];

$success = $_GET["success"] ?? "";
$error = $_GET["error"] ?? "";


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
   GET ALL SELLERS
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        s.sellerID,
        s.bussinessName,
        s.Name,

        COALESCE(
            AVG(f.Rating),
            0
        ) AS AvgRating,

        COUNT(f.FeedbackID) AS ReviewCount

    FROM seller s

    LEFT JOIN feedback f
        ON f.sellerID = s.sellerID

    GROUP BY
        s.sellerID,
        s.bussinessName,
        s.Name

    ORDER BY
        s.bussinessName ASC
");

$stmt->execute();

$sellers = $stmt->fetchAll();

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
            FEEDBACK & RATING
        </p>

        <h1>
            Seller Reviews
        </h1>

        <div class="title-line"></div>

        <p class="dashboard-description">
            Select a seller to view products, ratings and customer reviews.
        </p>

    </div>


    <?php if ($success !== ""): ?>

        <div class="feedback-alert success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="feedback-alert error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <?php if (empty($sellers)): ?>

        <div class="feedback-empty">

            <div class="feedback-empty-icon">
                ★
            </div>

            <h2>
                No Sellers Available
            </h2>

            <p>
                There are currently no sellers to review.
            </p>

        </div>

    <?php else: ?>

        <div class="feedback-seller-grid">

            <?php foreach ($sellers as $seller): ?>

                <a
                    href="products.php?seller_id=<?= (int) $seller["sellerID"] ?>"
                    class="feedback-seller-card"
                >

                    <div class="seller-card-icon">
                        ♧
                    </div>

                    <div class="seller-card-content">

                        <p class="seller-card-label">
                            SHOP
                        </p>

                        <h2>
                            <?= htmlspecialchars(
                                $seller["bussinessName"]
                            ) ?>
                        </h2>

                        <p class="seller-owner">
                            <?= htmlspecialchars(
                                $seller["Name"]
                            ) ?>
                        </p>

                        <div class="seller-card-rating">

                            <?= showStars(
                                $seller["AvgRating"]
                            ) ?>

                            <strong>
                                <?= number_format(
                                    (float) $seller["AvgRating"],
                                    1
                                ) ?>
                            </strong>

                        </div>

                        <p class="review-count">

                            <?= (int) $seller["ReviewCount"] ?>

                            <?= ((int) $seller["ReviewCount"] == 1)
                                ? "Review"
                                : "Reviews" ?>

                        </p>

                    </div>

                    <span class="seller-card-arrow">
                        →
                    </span>

                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<style>

.feedback-alert {
    max-width: 1100px;
    margin: 0 auto 20px;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 14px;
}

.feedback-alert.success {
    background: #EEE8F1;
    color: var(--purple);
    border: 1px solid var(--border);
}

.feedback-alert.error {
    background: #F4E5E8;
    color: var(--danger);
    border: 1px solid #E3C5C9;
}


/* SELLER GRID */

.feedback-seller-grid {
    max-width: 1100px;
    margin: 0 auto;

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 22px;
}


/* SELLER CARD */

.feedback-seller-card {
    position: relative;

    display: flex;

    align-items: center;

    gap: 18px;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 17px;

    padding: 25px;

    box-shadow:
        0 10px 30px rgba(70,54,83,.06);

    transition: all .25s ease;

    color: inherit;
}

.feedback-seller-card:hover {

    transform: translateY(-4px);

    border-color: var(--lavender);

    box-shadow:
        0 15px 35px rgba(70,54,83,.12);
}


/* ICON */

.seller-card-icon {

    width: 50px;
    height: 50px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    background: var(--light-lavender);

    color: var(--gold);

    border-radius: 13px;

    font-size: 25px;
}


/* CONTENT */

.seller-card-content {
    min-width: 0;
}

.seller-card-label {

    color: var(--purple);

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 2px;

    margin-bottom: 5px;
}

.feedback-seller-card h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 21px;

    font-weight: 500;

    margin: 0 0 4px;
}

.seller-owner {

    color: var(--gray-text);

    font-size: 13px;

    margin-bottom: 10px;
}


/* RATING */

.seller-card-rating {

    display: flex;

    align-items: center;

    gap: 8px;
}

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

.seller-card-rating strong {

    color: var(--deep-purple);

    font-size: 13px;
}

.review-count {

    color: var(--light-text);

    font-size: 11px;

    margin-top: 5px;
}


/* ARROW */

.seller-card-arrow {

    position: absolute;

    right: 18px;
    bottom: 17px;

    color: var(--purple);

    font-size: 18px;
}


/* EMPTY */

.feedback-empty {

    max-width: 1100px;

    margin: 0 auto;

    text-align: center;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 17px;

    padding: 70px 30px;
}

.feedback-empty-icon {

    font-size: 42px;

    color: var(--gold);

    margin-bottom: 15px;
}

.feedback-empty h2 {

    color: var(--deep-purple);
}

.feedback-empty p {

    color: var(--gray-text);
}


@media (max-width: 950px) {

    .feedback-seller-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}

@media (max-width: 650px) {

    .feedback-seller-grid {
        grid-template-columns: 1fr;
    }

}

</style>


<?php include "../../includes/footer.php"; ?>