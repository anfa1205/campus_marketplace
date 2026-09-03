<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyerID = (int) $_SESSION["user_id"];

$productID = isset($_GET["product_id"])
    ? (int) $_GET["product_id"]
    : (int) ($_POST["product_id"] ?? 0);

$purchaseID = isset($_GET["purchase_id"])
    ? (int) $_GET["purchase_id"]
    : (int) ($_POST["purchase_id"] ?? 0);

$error = "";

$product = null;


/* =========================================================
   NORMAL PRODUCT FLOW
   ========================================================= */

if ($productID > 0) {

    $stmt = $pdo->prepare("
        SELECT

            p.ProductID,
            p.ProductName,
            p.Category,
            p.Description,
            p.Price,

            s.sellerID,
            s.bussinessName,
            s.Name AS SellerName

        FROM product p

        INNER JOIN seller s
            ON p.SellerID = s.sellerID

        WHERE p.ProductID = ?

        LIMIT 1
    ");

    $stmt->execute([
        $productID
    ]);

    $product = $stmt->fetch();

}


/* =========================================================
   OLD RESERVATION FLOW
   ========================================================= */

if (!$product && $purchaseID > 0) {

    $stmt = $pdo->prepare("
        SELECT

            pu.PurchaseID,

            p.ProductID,
            p.ProductName,
            p.Category,
            p.Description,
            p.Price,

            s.sellerID,
            s.bussinessName,
            s.Name AS SellerName

        FROM purchase pu

        INNER JOIN has h
            ON pu.PurchaseID = h.purchaseID

        INNER JOIN product p
            ON h.productID = p.ProductID

        INNER JOIN seller s
            ON p.SellerID = s.sellerID

        WHERE pu.PurchaseID = ?
          AND pu.BuyerID = ?

        LIMIT 1
    ");

    $stmt->execute([
        $purchaseID,
        $buyerID
    ]);

    $product = $stmt->fetch();

    if ($product) {

        $productID =
            (int) $product["ProductID"];
    }

}


/* =========================================================
   PRODUCT NOT FOUND
   ========================================================= */

if (!$product) {

    $error =
        "Product not found.";

}


/* =========================================================
   EXISTING FEEDBACK
   ========================================================= */

$existingFeedback = null;

if ($product) {

    $stmt = $pdo->prepare("
        SELECT

            FeedbackID,
            Rating,
            Comment,
            purchaseID

        FROM feedback

        WHERE buyerID = ?
          AND productID = ?

        LIMIT 1
    ");

    $stmt->execute([
        $buyerID,
        $productID
    ]);

    $existingFeedback =
        $stmt->fetch();

}


/* =========================================================
   SUBMIT
   ========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $product
) {

    $rating =
        (int) ($_POST["rating"] ?? 0);

    $comment =
        trim(
            $_POST["comment"] ?? ""
        );


    if (
        $rating < 1 ||
        $rating > 5
    ) {

        $error =
            "Please select a rating from 1 to 5 stars.";

    } else {

        try {

            $pdo->beginTransaction();


            /* ---------------------------------------------
               CHECK AGAIN
            --------------------------------------------- */

            $stmt = $pdo->prepare("
                SELECT
                    FeedbackID

                FROM feedback

                WHERE buyerID = ?
                  AND productID = ?

                LIMIT 1

                FOR UPDATE
            ");

            $stmt->execute([
                $buyerID,
                $productID
            ]);

            $existing =
                $stmt->fetch();


            /* ---------------------------------------------
               UPDATE EXISTING
            --------------------------------------------- */

            if ($existing) {

                $stmt = $pdo->prepare("
                    UPDATE feedback

                    SET
                        Rating = ?,
                        Comment = ?,
                        FeedbackDate = NOW()

                    WHERE FeedbackID = ?
                      AND buyerID = ?
                      AND productID = ?
                ");

                $stmt->execute([

                    $rating,

                    $comment !== ""
                        ? $comment
                        : null,

                    $existing["FeedbackID"],

                    $buyerID,

                    $productID
                ]);

            }


            /* ---------------------------------------------
               INSERT NEW
            --------------------------------------------- */

            else {

                $stmt = $pdo->prepare("
                    INSERT INTO feedback
                    (
                        Comment,
                        Rating,
                        FeedbackDate,
                        sellerID,
                        buyerID,
                        purchaseID,
                        productID
                    )

                    VALUES
                    (
                        ?,
                        ?,
                        NOW(),
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                $stmt->execute([

                    $comment !== ""
                        ? $comment
                        : null,

                    $rating,

                    $product["sellerID"],

                    $buyerID,

                    $purchaseID > 0
                        ? $purchaseID
                        : null,

                    $productID

                ]);

            }


            /* ---------------------------------------------
               UPDATE SELLER AVERAGE
            --------------------------------------------- */

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
                $product["sellerID"]
            ]);

            $average =
                (float) $stmt->fetchColumn();


            $stmt = $pdo->prepare("
                UPDATE seller

                SET AvgRating = ?

                WHERE sellerID = ?
            ");

            $stmt->execute([

                $average,

                $product["sellerID"]

            ]);


            $pdo->commit();


            header(
                "Location: products.php?seller_id="
                . (int) $product["sellerID"]
                . "&success=1"
            );

            exit;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();

            }

            $error =
                $e->getMessage();

        }

    }

}


/* =========================================================
   CURRENT PRODUCT RATING
   ========================================================= */

$currentAverage = 0;
$currentReviews = 0;

if ($product) {

    $stmt = $pdo->prepare("
        SELECT

            COALESCE(
                AVG(Rating),
                0
            ) AS AvgRating,

            COUNT(*) AS ReviewCount

        FROM feedback

        WHERE productID = ?
    ");

    $stmt->execute([
        $productID
    ]);

    $ratingData =
        $stmt->fetch();

    $currentAverage =
        (float) $ratingData["AvgRating"];

    $currentReviews =
        (int) $ratingData["ReviewCount"];

}


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
            FEEDBACK & RATING
        </p>

        <h1>
            Rate Product
        </h1>

        <div class="title-line"></div>

        <p class="dashboard-description">
            Share your rating and feedback.
        </p>

    </div>


    <?php if ($error !== ""): ?>

        <div class="feedback-alert error">

            <?= htmlspecialchars(
                $error
            ) ?>

        </div>

    <?php endif; ?>


    <?php if ($product): ?>

        <div class="rating-card">


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


            <p class="seller-name">

                Shop:

                <strong>
                    <?= htmlspecialchars(
                        $product["bussinessName"]
                    ) ?>
                </strong>

            </p>


            <!-- CURRENT RATING -->

            <div class="current-product-rating">

                <div>

                    <span class="feedback-stars">

                        <?php

                        $full =
                            floor($currentAverage);

                        $half =
                            (($currentAverage - $full) >= 0.5);

                        for (
                            $i = 1;
                            $i <= 5;
                            $i++
                        ) {

                            if ($i <= $full) {

                                echo
                                    '<span class="star-full">★</span>';

                            } elseif (
                                $i == $full + 1
                                && $half
                            ) {

                                echo
                                    '<span class="star-half">★</span>';

                            } else {

                                echo
                                    '<span class="star-empty">☆</span>';
                            }
                        }

                        ?>

                    </span>

                </div>

                <strong>

                    <?= number_format(
                        $currentAverage,
                        1
                    ) ?>

                    / 5

                </strong>

                <span>

                    <?= $currentReviews ?>

                    reviews

                </span>

            </div>


            <!-- FORM -->

            <form
                method="POST"
                class="rating-form"
            >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) $productID ?>"
                >


                <?php if ($purchaseID > 0): ?>

                    <input
                        type="hidden"
                        name="purchase_id"
                        value="<?= (int) $purchaseID ?>"
                    >

                <?php endif; ?>


                <div class="rating-section">

                    <label>
                        Your Rating
                    </label>


                    <div class="star-rating">

                        <?php for (
                            $i = 5;
                            $i >= 1;
                            $i--
                        ): ?>

                            <input
                                type="radio"
                                id="star<?= $i ?>"
                                name="rating"
                                value="<?= $i ?>"
                                <?= (
                                    $existingFeedback
                                    && (int)
                                    $existingFeedback["Rating"]
                                    === $i
                                )
                                    ? "checked"
                                    : "" ?>
                                required
                            >

                            <label
                                for="star<?= $i ?>"
                                title="<?= $i ?> stars"
                            >
                                ★
                            </label>

                        <?php endfor; ?>

                    </div>

                </div>


                <div class="comment-section">

                    <label>
                        Your Feedback
                    </label>

                    <textarea
                        name="comment"
                        rows="6"
                        placeholder="Write your feedback here..."
                    ><?= $existingFeedback
                        ? htmlspecialchars(
                            $existingFeedback["Comment"] ?? ""
                        )
                        : "" ?></textarea>

                </div>


                <button
                    type="submit"
                    class="feedback-submit"
                >

                    <?= $existingFeedback
                        ? "Update Feedback"
                        : "Submit Feedback" ?>

                </button>


                <a
                    href="products.php?seller_id=<?= (int) $product["sellerID"] ?>"
                    class="back-feedback"
                >
                    ← Back to Seller
                </a>

            </form>

        </div>

    <?php endif; ?>

</div>


<style>

.feedback-alert {

    max-width: 800px;

    margin: 0 auto 20px;

    padding: 14px 18px;

    border-radius: 10px;

    background: #F4E5E8;

    color: var(--danger);

    border: 1px solid #E3C5C9;
}


.rating-card {

    max-width: 700px;

    margin: 0 auto;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 17px;

    padding: 35px;

    box-shadow:
        0 10px 30px rgba(70,54,83,.06);
}


.rating-card h2 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 28px;

    font-weight: 500;

    margin-bottom: 7px;
}

.product-category {

    color: var(--purple);

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 2px;
}

.seller-name {

    color: var(--gray-text);

    font-size: 13px;

    margin-bottom: 20px;
}

.seller-name strong {

    color: var(--deep-purple);
}


/* CURRENT RATING */

.current-product-rating {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 15px;

    background: var(--light-lavender);

    border-radius: 10px;

    margin-bottom: 25px;
}

.current-product-rating strong {

    color: var(--deep-purple);

    font-size: 14px;
}

.current-product-rating > span {

    color: var(--light-text);

    font-size: 11px;
}


/* STARS */

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


/* FORM */

.rating-section {

    margin-top: 15px;
}

.rating-section label,
.comment-section label {

    display: block;

    color: var(--deep-purple);

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 10px;
}


/* CLICKABLE STARS */

.star-rating {

    display: flex;

    flex-direction: row-reverse;

    justify-content: flex-end;

    gap: 4px;

    margin-bottom: 25px;
}

.star-rating input {

    display: none;
}

.star-rating label {

    width: auto;

    padding: 0;

    margin: 0;

    font-size: 35px;

    color: #D9D3DD;

    cursor: pointer;

    transition: .2s;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {

    color: var(--gold);
}


/* TEXTAREA */

.comment-section textarea {

    width: 100%;

    min-height: 140px;

    padding: 14px;

    border: 1px solid var(--border);

    border-radius: 10px;

    resize: vertical;

    font-family: inherit;

    color: var(--dark-text);

    background: #FDFCFE;

    outline: none;
}

.comment-section textarea:focus {

    border-color: var(--purple);

    box-shadow:
        0 0 0 3px rgba(112,87,127,.11);
}


/* BUTTON */

.feedback-submit {

    width: 100%;

    margin-top: 25px;

    padding: 13px;

    border: none;

    border-radius: 9px;

    background: var(--deep-purple);

    color: white;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;
}

.feedback-submit:hover {

    background: var(--purple);
}


/* BACK */

.back-feedback {

    display: block;

    text-align: center;

    margin-top: 15px;

    color: var(--purple);

    font-size: 13px;

    font-weight: 600;
}

</style>


<?php include "../../includes/footer.php"; ?>