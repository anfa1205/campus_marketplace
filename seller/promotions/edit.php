<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$promotionID = (int) ($_GET["id"] ?? 0);

$error = "";


/* Get existing offer */

$stmt = $pdo->prepare("
    SELECT *
    FROM promotion
    WHERE PromotionId = ?
      AND SellerId = ?
");

$stmt->execute([
    $promotionID,
    $sellerID
]);

$offer = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$offer) {
    header("Location: index.php");
    exit;
}


/* Get seller products */

$stmt = $pdo->prepare("
    SELECT
        ProductID,
        ProductName,
        Price,
        Stock,
        Status
    FROM product
    WHERE SellerID = ?
      AND Stock > 0
      AND Status = 'Available'
    ORDER BY ProductName
");

$stmt->execute([$sellerID]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Get products currently attached to this offer */

$stmt = $pdo->prepare("
    SELECT productID
    FROM applies_to
    WHERE promotionID = ?
");

$stmt->execute([$promotionID]);

$selectedProducts = array_map(
    "intval",
    array_column(
        $stmt->fetchAll(PDO::FETCH_ASSOC),
        "productID"
    )
);


/* Update offer */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $offerType = $_POST["offer_type"] ?? "";

    $discountValue =
        (float) ($_POST["discount_value"] ?? 0);

    $buyQuantity =
        (int) ($_POST["buy_quantity"] ?? 0);

    $getQuantity =
        (int) ($_POST["get_quantity"] ?? 0);

    $startDate =
        trim($_POST["start_date"] ?? "");

    $endDate =
        trim($_POST["end_date"] ?? "");

    $selectedProducts =
        $_POST["products"] ?? [];


    $selectedProducts = array_map(
        "intval",
        $selectedProducts
    );


    /* Convert datetime-local to MySQL DATETIME */

    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);


    if (
        !in_array(
            $offerType,
            ["Percentage", "BuyXGetY"],
            true
        )
    ) {

        $error = "Please select a valid offer type.";

    } elseif (
        !$startTimestamp ||
        !$endTimestamp
    ) {

        $error = "Please enter valid start and end dates.";

    } elseif (
        $endTimestamp <= $startTimestamp
    ) {

        $error = "End date must be after start date.";

    } elseif (
        empty($selectedProducts)
    ) {

        $error = "Please select at least one product.";

    } elseif (
        $offerType === "Percentage" &&
        ($discountValue <= 0 || $discountValue > 100)
    ) {

        $error = "Discount must be between 1% and 100%.";

    } elseif (
        $offerType === "BuyXGetY" &&
        ($buyQuantity <= 0 || $getQuantity <= 0)
    ) {

        $error = "Buy and Get quantities must be greater than zero.";

    } else {

        $startDateDB =
            date("Y-m-d H:i:s", $startTimestamp);

        $endDateDB =
            date("Y-m-d H:i:s", $endTimestamp);


        try {

            $pdo->beginTransaction();


            /* Make sure every selected product belongs to seller */

            foreach ($selectedProducts as $productID) {

                $check = $pdo->prepare("
                    SELECT ProductID
                    FROM product
                    WHERE ProductID = ?
                      AND SellerID = ?
                      AND Stock > 0
                      AND Status = 'Available'
                ");

                $check->execute([
                    $productID,
                    $sellerID
                ]);

                if (!$check->fetch()) {

                    throw new Exception(
                        "One or more selected products are invalid."
                    );

                }
            }


            /* Update promotion */

            $stmt = $pdo->prepare("
                UPDATE promotion
                SET
                    OfferType = ?,
                    DiscountValue = ?,
                    BuyQuantity = ?,
                    GetQuantity = ?,
                    StartDate = ?,
                    EndDate = ?
                WHERE PromotionId = ?
                  AND SellerId = ?
            ");


            /*
             * IMPORTANT:
             * Percentage offers use 0 instead of NULL
             * because your database does not allow NULL.
             */

            $dbDiscount =
                $offerType === "Percentage"
                    ? $discountValue
                    : 0;

            $dbBuyQuantity =
                $offerType === "BuyXGetY"
                    ? $buyQuantity
                    : 0;

            $dbGetQuantity =
                $offerType === "BuyXGetY"
                    ? $getQuantity
                    : 0;


            $stmt->execute([
                $offerType,
                $dbDiscount,
                $dbBuyQuantity,
                $dbGetQuantity,
                $startDateDB,
                $endDateDB,
                $promotionID,
                $sellerID
            ]);


            /* Remove old products */

            $stmt = $pdo->prepare("
                DELETE FROM applies_to
                WHERE promotionID = ?
            ");

            $stmt->execute([
                $promotionID
            ]);


            /* Add selected products again */

            $stmt = $pdo->prepare("
                INSERT INTO applies_to
                (
                    promotionID,
                    productID
                )
                VALUES (?, ?)
            ");


            foreach ($selectedProducts as $productID) {

                $stmt->execute([
                    $promotionID,
                    $productID
                ]);

            }


            $pdo->commit();


            header(
                "Location: index.php?success=" .
                urlencode("Offer updated successfully.")
            );

            exit;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}


/* Values shown in form */

$currentOfferType =
    $_POST["offer_type"]
    ?? $offer["OfferType"];

$currentDiscount =
    $_POST["discount_value"]
    ?? $offer["DiscountValue"];

$currentBuy =
    $_POST["buy_quantity"]
    ?? ($offer["BuyQuantity"] ?? 0);

$currentGet =
    $_POST["get_quantity"]
    ?? ($offer["GetQuantity"] ?? 0);

$currentStart =
    $_POST["start_date"]
    ?? date(
        "Y-m-d\TH:i",
        strtotime($offer["StartDate"])
    );

$currentEnd =
    $_POST["end_date"]
    ?? date(
        "Y-m-d\TH:i",
        strtotime($offer["EndDate"])
    );


include "../../includes/header.php";

?>


<style>

.offer-form-page {
    max-width: 850px;
    margin: 35px auto;
    padding: 0 20px;
}

.offer-form-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 7px 25px rgba(75, 45, 100, 0.12);
    border: 1px solid #eee5f6;
}

.offer-form-heading {
    margin-bottom: 28px;
}

.offer-form-heading .page-label {
    color: #76539c;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 7px;
}

.offer-form-heading h1 {
    color: #563578;
    margin: 0 0 8px;
    font-size: 30px;
}

.offer-form-heading p {
    color: #777;
    margin: 0;
}

.offer-form-group {
    margin-bottom: 20px;
}

.offer-form-group label {
    display: block;
    font-weight: 700;
    color: #54386d;
    margin-bottom: 8px;
}

.offer-form-group input,
.offer-form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 13px;
    border: 1px solid #d9cce5;
    border-radius: 9px;
    background: #fff;
    font-size: 14px;
}

.offer-form-group input:focus,
.offer-form-group select:focus {
    outline: none;
    border-color: #8060a5;
    box-shadow: 0 0 0 3px rgba(128, 96, 165, 0.10);
}

.offer-special-box {
    background: #faf7fd;
    border: 1px solid #eadff3;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 20px;
}

.offer-special-box h3 {
    margin: 0 0 14px;
    color: #634385;
    font-size: 16px;
}

.offer-two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.offer-products {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.offer-product {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 12px;
    background: #faf7fd;
    border: 1px solid #e9def2;
    border-radius: 9px;
    cursor: pointer;
    color: #4f3a60;
}

.offer-product:hover {
    background: #f3ebf9;
}

.offer-product input {
    width: auto;
}

.form-error {
    margin-bottom: 20px;
}

.offer-form-buttons {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.offer-form-buttons .btn {
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.offer-cancel {
    background: #eee8f2 !important;
    color: #5e4770 !important;
}

@media (max-width: 700px) {

    .offer-two-columns,
    .offer-products {
        grid-template-columns: 1fr;
    }

    .offer-form-card {
        padding: 22px;
    }

    .offer-form-buttons {
        flex-direction: column;
    }

}

</style>


<div class="offer-form-page">

    <div class="offer-form-card">

        <div class="offer-form-heading">

            <p class="page-label">
                OFFERS
            </p>

            <h1>
                Update Offer
            </h1>

            <p>
                Update the offer type, discount, dates and products.
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="form-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- Offer Type -->

            <div class="offer-form-group">

                <label for="offer_type">
                    Offer Type
                </label>

                <select
                    name="offer_type"
                    id="offer_type"
                    required
                >

                    <option
                        value="Percentage"
                        <?= $currentOfferType === "Percentage"
                            ? "selected"
                            : "" ?>
                    >
                        Percentage Discount
                    </option>

                    <option
                        value="BuyXGetY"
                        <?= $currentOfferType === "BuyXGetY"
                            ? "selected"
                            : "" ?>
                    >
                        Buy X Get Y
                    </option>

                </select>

            </div>


            <!-- Percentage -->

            <div
                id="percentage_box"
                class="offer-special-box"
            >

                <h3>
                    Percentage Discount
                </h3>

                <div class="offer-form-group">

                    <label for="discount_value">
                        Discount Percentage
                    </label>

                    <input
                        type="number"
                        id="discount_value"
                        name="discount_value"
                        min="1"
                        max="100"
                        step="0.01"
                        value="<?= htmlspecialchars($currentDiscount) ?>"
                    >

                </div>

            </div>


            <!-- Buy X Get Y -->

            <div
                id="buy_get_box"
                class="offer-special-box"
            >

                <h3>
                    Buy X Get Y Offer
                </h3>

                <div class="offer-two-columns">

                    <div class="offer-form-group">

                        <label for="buy_quantity">
                            Buy Quantity
                        </label>

                        <input
                            type="number"
                            id="buy_quantity"
                            name="buy_quantity"
                            min="1"
                            value="<?= htmlspecialchars($currentBuy) ?>"
                        >

                    </div>


                    <div class="offer-form-group">

                        <label for="get_quantity">
                            Get Quantity Free
                        </label>

                        <input
                            type="number"
                            id="get_quantity"
                            name="get_quantity"
                            min="1"
                            value="<?= htmlspecialchars($currentGet) ?>"
                        >

                    </div>

                </div>

            </div>


            <!-- Dates -->

            <div class="offer-two-columns">

                <div class="offer-form-group">

                    <label for="start_date">
                        Start Date & Time
                    </label>

                    <input
                        type="datetime-local"
                        id="start_date"
                        name="start_date"
                        value="<?= htmlspecialchars($currentStart) ?>"
                        required
                    >

                </div>


                <div class="offer-form-group">

                    <label for="end_date">
                        End Date & Time
                    </label>

                    <input
                        type="datetime-local"
                        id="end_date"
                        name="end_date"
                        value="<?= htmlspecialchars($currentEnd) ?>"
                        required
                    >

                </div>

            </div>


            <!-- Products -->

            <div class="offer-form-group">

                <label>
                    Select Products
                </label>

                <div class="offer-products">

                    <?php foreach ($products as $product): ?>

                        <label class="offer-product">

                            <input
                                type="checkbox"
                                name="products[]"
                                value="<?= (int) $product["ProductID"] ?>"
                                <?= in_array(
                                    (int) $product["ProductID"],
                                    $selectedProducts,
                                    true
                                )
                                    ? "checked"
                                    : "" ?>
                            >

                            <span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $product["ProductName"]
                                    ) ?>
                                </strong>

                                <br>

                                <small>
                                    ৳<?= number_format(
                                        (float) $product["Price"],
                                        2
                                    ) ?>
                                    —
                                    Stock:
                                    <?= (int) $product["Stock"] ?>
                                </small>

                            </span>

                        </label>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- Buttons -->

            <div class="offer-form-buttons">

                <button
                    type="submit"
                    class="btn"
                >
                    Update Offer
                </button>

                <a
                    href="index.php"
                    class="btn offer-cancel"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>


<script>

const offerType =
    document.getElementById("offer_type");

const percentageBox =
    document.getElementById("percentage_box");

const buyGetBox =
    document.getElementById("buy_get_box");

const discountInput =
    document.getElementById("discount_value");

const buyInput =
    document.getElementById("buy_quantity");

const getInput =
    document.getElementById("get_quantity");


function updateOfferFields() {

    if (offerType.value === "Percentage") {

        percentageBox.style.display = "block";

        buyGetBox.style.display = "none";

        discountInput.required = true;

        buyInput.required = false;

        getInput.required = false;

    } else {

        percentageBox.style.display = "none";

        buyGetBox.style.display = "block";

        discountInput.required = false;

        buyInput.required = true;

        getInput.required = true;

    }
}


offerType.addEventListener(
    "change",
    updateOfferFields
);


updateOfferFields();

</script>


<?php include "../../includes/footer.php"; ?>