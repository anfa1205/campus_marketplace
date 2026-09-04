```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$error = "";


/* AVAILABLE PRODUCTS */

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

$products = $stmt->fetchAll();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $offerType =
        trim($_POST["offer_type"] ?? "");

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

    $pickupLocation =
        trim($_POST["pickup_location"] ?? "");

    $selectedProducts =
        $_POST["products"] ?? [];


    /* VALIDATION */
    if (!in_array(  $offerType, ["Percentage", "BuyXGetY"],true)
    ) {
        $error ="Please select a valid offer type.";
    } elseif (
        empty($startDate) ||
        empty($endDate)
    ) {
        $error =
            "Please enter both start and end date.";
    } elseif (
        strtotime($endDate)
        <=
        strtotime($startDate)
    ) {
        $error =
            "End date must be after start date.";

    } elseif (
        empty($pickupLocation)
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
        $error =  "Discount must be between 1% and 100%.";

    } elseif ( offerType === "BuyXGetY" &&($buyQuantity <= 0 ||$getQuantity <= 0)
    ) {
        $error = "Buy and Get quantities must be greater than zero.";
    } else {
        try {
            $pdo->beginTransaction();

            /*
             * Percentage:
             * BuyQuantity = 0
             * GetQuantity = 0
             *
             * Buy X Get Y:
             * DiscountValue = 0
             */

            $dbBuyQuantity =
                $offerType === "BuyXGetY"
                    ? $buyQuantity
                    : 0;

            $dbGetQuantity =
                $offerType === "BuyXGetY"
                    ? $getQuantity
                    : 0;


            $dbDiscount =
                $offerType === "Percentage"
                    ? $discountValue
                    : 0;


            /* INSERT OFFER */

            $stmt = $pdo->prepare("
                INSERT INTO promotion
                (
                    OfferType,
                    DiscountValue,
                    BuyQuantity,
                    GetQuantity,
                    StartDate,
                    EndDate,
                    PickupLocation,
                    SellerId
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");


            $stmt->execute([

                $offerType,

                $dbDiscount,

                $dbBuyQuantity,

                $dbGetQuantity,

                $startDate,

                $endDate,

                $pickupLocation,

                $sellerID

            ]);


            $promotionID =
                (int) $pdo->lastInsertId();


            /* PRODUCTS */

            $checkProduct =
                $pdo->prepare("
                    SELECT ProductID
                    FROM product
                    WHERE ProductID = ?
                      AND SellerID = ?
                      AND Stock > 0
                      AND Status = 'Available'
                ");


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


            foreach ($selectedProducts as $productID) {

                $productID =
                    (int) $productID;


                $checkProduct->execute([
                    $productID,
                    $sellerID
                ]);


                if (!$checkProduct->fetch()) {

                    throw new Exception(
                        "Invalid product selected."
                    );
                }


                $insertProduct->execute([
                    $promotionID,
                    $productID
                ]);
            }


            $pdo->commit();


            header(
                "Location: index.php?success=" .
                urlencode(
                    "Offer created successfully."
                )
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


include "../../includes/header.php";

?>


<div class="offer-form-page">

    <div class="offer-form-card">


        <div class="form-heading">

            <p class="page-label">
                OFFERS
            </p>

            <h1>
                Create Offer
            </h1>

            <p>
                Create a special offer for your products.
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="form-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="offer-form-group">

                <label>
                    Offer Type
                </label>

                <select
                    name="offer_type"
                    id="offer_type"
                    required
                >

                    <option value="">
                        Select Offer Type
                    </option>

                    <option value="Percentage">
                        Percentage Discount
                    </option>

                    <option value="BuyXGetY">
                        Buy X Get Y
                    </option>

                </select>

            </div>


            <div
                id="percentage_box"
                class="offer-special-box"
            >

                <label>
                    Discount Percentage
                </label>

                <input
                    type="number"
                    name="discount_value"
                    id="discount_value"
                    min="1"
                    max="100"
                    step="0.01"
                    placeholder="Example: 20"
                >

                <small>
                    Example: 20 means 20% discount.
                </small>

            </div>


            <div
                id="buy_get_box"
                class="offer-special-box"
            >

                <div class="offer-two-columns">

                    <div>

                        <label>
                            Buy Quantity
                        </label>

                        <input
                            type="number"
                            name="buy_quantity"
                            id="buy_quantity"
                            min="1"
                            placeholder="Example: 2"
                        >

                    </div>


                    <div>

                        <label>
                            Get Quantity
                        </label>

                        <input
                            type="number"
                            name="get_quantity"
                            id="get_quantity"
                            min="1"
                            placeholder="Example: 1"
                        >

                    </div>

                </div>


                <small>
                    Example: Buy 2 Get 1.
                </small>

            </div>


            <div class="offer-two-columns">

                <div class="offer-form-group">

                    <label>
                        Start Date & Time
                    </label>

                    <input
                        type="datetime-local"
                        name="start_date"
                        required
                    >

                </div>


                <div class="offer-form-group">

                    <label>
                        End Date & Time
                    </label>

                    <input
                        type="datetime-local"
                        name="end_date"
                        required
                    >

                </div>

            </div>


            <!-- PICKUP LOCATION -->

            <div class="offer-form-group">

                <label>
                    Pickup Location / Place
                </label>

                <input
                    type="text"
                    name="pickup_location"
                    maxlength="255"
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

                        <p class="offer-no-products">
                            No available products.
                        </p>

                    <?php else: ?>

                        <?php foreach ($products as $product): ?>

                            <label class="offer-product">

                                <input
                                    type="checkbox"
                                    name="products[]"
                                    value="<?= (int) $product["ProductID"] ?>"
                                >

                                <span>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $product["ProductName"]
                                        ) ?>
                                    </strong>

                                    <small>

                                        ৳<?= number_format(
                                            (float) $product["Price"],
                                            2
                                        ) ?>

                                        ·

                                        Stock:
                                        <?= (int) $product["Stock"] ?>

                                    </small>

                                </span>

                            </label>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>


            <button
                type="submit"
                class="btn"
            >
                Create Offer
            </button>


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

    } else if (offerType.value === "BuyXGetY") {

        percentageBox.style.display = "none";

        buyGetBox.style.display = "block";

        discountInput.required = false;
        discountInput.disabled = true;

        buyInput.required = true;
        buyInput.disabled = false;

        getInput.required = true;
        getInput.disabled = false;

    } else {

        percentageBox.style.display = "none";

        buyGetBox.style.display = "none";

        discountInput.required = false;
        discountInput.disabled = true;

        buyInput.required = false;
        buyInput.disabled = true;

        getInput.required = false;
        getInput.disabled = true;
    }
}


offerType.addEventListener(
    "change",
    updateOfferFields
);


updateOfferFields();

</script>


<?php

include "../../includes/footer.php";

?>
```
