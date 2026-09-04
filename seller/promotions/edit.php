```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];
$promotionID = (int) ($_GET["id"] ?? 0);

$error = "";


/* =========================================================
   GET EXISTING OFFER
========================================================= */

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


/* =========================================================
   GET SELLER PRODUCTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        ProductID,
        ProductName,
        Price,
        Stock,
        Status
    FROM product
    WHERE SellerID = ?
    ORDER BY ProductName
");

$stmt->execute([$sellerID]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   GET PRODUCTS CURRENTLY ATTACHED
========================================================= */

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


/* =========================================================
   UPDATE OFFER
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $offerType =
        trim($_POST["offer_type"] ?? "");

    $discountValue =
        (float) (
            $_POST["discount_value"] ?? 0
        );

    $buyQuantity =
        (int) (
            $_POST["buy_quantity"] ?? 0
        );

    $getQuantity =
        (int) (
            $_POST["get_quantity"] ?? 0
        );

    $startDate =
        trim($_POST["start_date"] ?? "");

    $endDate =
        trim($_POST["end_date"] ?? "");

    $pickupLocation =
        trim($_POST["pickup_location"] ?? "");

    $selectedProducts =
        $_POST["products"] ?? [];


    if (!is_array($selectedProducts)) {

        $selectedProducts = [];

    }


    $selectedProducts =
        array_values(
            array_unique(
                array_map(
                    "intval",
                    $selectedProducts
                )
            )
        );


    /* =====================================================
       VALIDATION
    ===================================================== */

    if (
        !in_array(
            $offerType,
            ["Percentage", "BuyXGetY"],
            true
        )
    ) {

        $error =
            "Please select a valid offer type.";

    } elseif (
        $startDate === "" ||
        $endDate === ""
    ) {

        $error =
            "Please enter both start and end dates.";

    } else {

        $startTimestamp =
            strtotime($startDate);

        $endTimestamp =
            strtotime($endDate);


        if (
            $startTimestamp === false ||
            $endTimestamp === false
        ) {

            $error =
                "Please enter valid start and end dates.";

        } elseif (
            $endTimestamp <= $startTimestamp
        ) {

            $error =
                "End date must be after start date.";

        } elseif (
            $pickupLocation === ""
        ) {

            $error =
                "Please enter the pickup location.";

        } elseif (
            empty($selectedProducts)
        ) {

            $error =
                "Please select at least one product.";

        } elseif (
            $offerType === "Percentage" &&
            (
                $discountValue <= 0 ||
                $discountValue > 100
            )
        ) {

            $error =
                "Discount must be between 1% and 100%.";

        } elseif (
            $offerType === "BuyXGetY" &&
            (
                $buyQuantity <= 0 ||
                $getQuantity <= 0
            )
        ) {

            $error =
                "Buy and Get quantities must be greater than zero.";

        } else {

            $startDateDB =
                date(
                    "Y-m-d H:i:s",
                    $startTimestamp
                );

            $endDateDB =
                date(
                    "Y-m-d H:i:s",
                    $endTimestamp
                );


            try {

                $pdo->beginTransaction();


                /* CHECK OFFER */

                $checkOffer =
                    $pdo->prepare("
                        SELECT PromotionId
                        FROM promotion
                        WHERE PromotionId = ?
                          AND SellerId = ?
                    ");

                $checkOffer->execute([
                    $promotionID,
                    $sellerID
                ]);


                if (!$checkOffer->fetch()) {

                    throw new Exception(
                        "This offer could not be found."
                    );
                }


                /* CHECK PRODUCTS */

                foreach (
                    $selectedProducts
                    as $productID
                ) {

                    if ($productID <= 0) {

                        throw new Exception(
                            "Invalid product selected."
                        );
                    }


                    $checkProduct =
                        $pdo->prepare("
                            SELECT ProductID
                            FROM product
                            WHERE ProductID = ?
                              AND SellerID = ?
                        ");

                    $checkProduct->execute([
                        $productID,
                        $sellerID
                    ]);


                    if (!$checkProduct->fetch()) {

                        throw new Exception(
                            "One or more selected products are invalid."
                        );
                    }

                }


                /* DATABASE VALUES */

                if ($offerType === "Percentage") {

                    $dbDiscount =
                        $discountValue;

                    $dbBuyQuantity =
                        0;

                    $dbGetQuantity =
                        0;

                } else {

                    $dbDiscount =
                        0;

                    $dbBuyQuantity =
                        $buyQuantity;

                    $dbGetQuantity =
                        $getQuantity;

                }


                /* UPDATE OFFER */

                $updateOffer =
                    $pdo->prepare("
                        UPDATE promotion

                        SET
                            OfferType = ?,
                            DiscountValue = ?,
                            BuyQuantity = ?,
                            GetQuantity = ?,
                            StartDate = ?,
                            EndDate = ?,
                            PickupLocation = ?

                        WHERE PromotionId = ?
                          AND SellerId = ?
                    ");


                $updateOffer->execute([

                    $offerType,

                    $dbDiscount,

                    $dbBuyQuantity,

                    $dbGetQuantity,

                    $startDateDB,

                    $endDateDB,

                    $pickupLocation,

                    $promotionID,

                    $sellerID

                ]);


                /* DELETE OLD PRODUCT CONNECTIONS */

                $deleteProducts =
                    $pdo->prepare("
                        DELETE FROM applies_to
                        WHERE promotionID = ?
                    ");

                $deleteProducts->execute([
                    $promotionID
                ]);


                /* INSERT UPDATED PRODUCTS */

                $insertProduct =
                    $pdo->prepare("
                        INSERT INTO applies_to
                        (
                            promotionID,
                            productID
                        )

                        VALUES
                        (
                            ?,
                            ?
                        )
                    ");


                foreach (
                    $selectedProducts
                    as $productID
                ) {

                    $insertProduct->execute([
                        $promotionID,
                        $productID
                    ]);

                }


                $pdo->commit();


                header(
                    "Location: index.php?success=" .
                    urlencode(
                        "Offer updated successfully."
                    )
                );

                exit;


            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {

                    $pdo->rollBack();

                }

                $error =
                    "Update failed: " .
                    $e->getMessage();
            }

        }

    }

}


/* =========================================================
   VALUES SHOWN IN FORM
========================================================= */

$currentOfferType =
    $_POST["offer_type"]
    ?? $offer["OfferType"];


$currentDiscount =
    $_POST["discount_value"]
    ?? ($offer["DiscountValue"] ?? 0);


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


$currentPickupLocation =
    $_POST["pickup_location"]
    ?? ($offer["PickupLocation"] ?? "");


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $selectedProducts =
        $_POST["products"] ?? [];


    if (!is_array($selectedProducts)) {

        $selectedProducts = [];

    }


    $selectedProducts =
        array_values(
            array_unique(
                array_map(
                    "intval",
                    $selectedProducts
                )
            )
        );

}


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
    transition: 0.2s ease;
}

.offer-product:hover {
    background: #f3ebf9;
    border-color: #cbb6dc;
}

.offer-product input {
    width: auto;
    cursor: pointer;
}

.offer-product span {
    flex: 1;
}

.offer-product strong {
    color: #50336b;
}

.offer-product small {
    color: #777;
}

.form-error {
    margin-bottom: 20px;
    padding: 12px 15px;
    border-radius: 9px;
    background: #fff0f0;
    border: 1px solid #efcaca;
    color: #a33b3b;
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
                Update the offer type, discount, dates, pickup location and products.
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="form-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="edit.php?id=<?= (int) $promotionID ?>"
        >


            <input
                type="hidden"
                name="update_offer"
                value="1"
            >


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
                        value="<?= htmlspecialchars(
                            (string) $currentDiscount
                        ) ?>"
                    >

                </div>

            </div>


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
                            value="<?= htmlspecialchars(
                                (string) $currentBuy
                            ) ?>"
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
                            value="<?= htmlspecialchars(
                                (string) $currentGet
                            ) ?>"
                        >

                    </div>

                </div>

            </div>


            <div class="offer-two-columns">

                <div class="offer-form-group">

                    <label for="start_date">
                        Start Date & Time
                    </label>

                    <input
                        type="datetime-local"
                        id="start_date"
                        name="start_date"
                        value="<?= htmlspecialchars(
                            $currentStart
                        ) ?>"
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
                        value="<?= htmlspecialchars(
                            $currentEnd
                        ) ?>"
                        required
                    >

                </div>

            </div>


            <!-- PICKUP LOCATION -->

            <div class="offer-form-group">

                <label for="pickup_location">
                    Pickup Location / Place
                </label>

                <input
                    type="text"
                    id="pickup_location"
                    name="pickup_location"
                    maxlength="255"
                    value="<?= htmlspecialchars(
                        $currentPickupLocation
                    ) ?>"
                    placeholder="Example: BRAC University Cafeteria"
                    required
                >

                <small>
                    Enter the exact campus place where buyers can collect their offer.
                </small>

            </div>


            <div class="offer-form-group">

                <label>
                    Select Products
                </label>

                <div class="offer-products">

                    <?php if (empty($products)): ?>

                        <p>
                            No products found.
                        </p>

                    <?php else: ?>

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

                    <?php endif; ?>

                </div>

            </div>


            <div class="offer-form-buttons">

                <button
                    type="submit"
                    class="btn"
                    name="submit_update"
                    value="1"
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
        discountInput.disabled = false;

        buyInput.required = false;
        buyInput.disabled = true;

        getInput.required = false;
        getInput.disabled = true;

    } else {

        percentageBox.style.display = "none";

        buyGetBox.style.display = "block";

        discountInput.required = false;
        discountInput.disabled = true;

        buyInput.required = true;
        buyInput.disabled = false;

        getInput.required = true;
        getInput.disabled = false;

    }

}


offerType.addEventListener(
    "change",
    updateOfferFields
);


updateOfferFields();


const offerForm =
    document.querySelector("form");

if (offerForm) {

    offerForm.addEventListener(
        "submit",
        function () {

            const submitButton =
                offerForm.querySelector(
                    'button[type="submit"]'
                );

            if (submitButton) {

                submitButton.disabled = true;

                submitButton.innerText =
                    "Updating...";
            }

        }
    );

}

</script>


<?php

include "../../includes/footer.php";

?>
```
