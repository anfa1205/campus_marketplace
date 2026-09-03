<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";
require_once "../../includes/offer_helper.php";

include "../../includes/header.php";


$productID =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


$product = null;


if ($productID > 0) {

    $stmt = $pdo->prepare("
        SELECT
            p.ProductID,
            p.ProductName,
            p.Description,
            p.Category,
            p.Stock,
            p.Price,
            p.Status,

            s.sellerID,
            s.Name AS SellerName,
            s.bussinessName,
            s.department

        FROM product p

        INNER JOIN seller s
            ON p.SellerID = s.sellerID

        WHERE p.ProductID = ?
    ");

    $stmt->execute([
        $productID
    ]);

    $product =
        $stmt->fetch();

}


if (!$product) {

    echo "<div class='card'>";
    echo "<h2>Product not found.</h2>";
    echo "<a href='index.php' class='btn'>Back to Products</a>";
    echo "</div>";

    include "../../includes/footer.php";

    exit;
}


$stock =
    (int) $product["Stock"];

$status =
    trim(
        $product["Status"] ?? ""
    );


if ($stock <= 0) {

    $displayStatus =
        "Out of Stock";

} elseif (
    strcasecmp(
        $status,
        "Unavailable"
    ) === 0
) {

    $displayStatus =
        "Unavailable";

} else {

    $displayStatus =
        "Available";

}


$offer =
    getActiveOffer(
        $pdo,
        (int) $product["ProductID"]
    );


$originalPrice =
    (float) $product["Price"];

$offerPrice =
    getOfferPrice(
        $originalPrice,
        $offer
    );

?>

<div class="product-details-page">

    <div class="product-details-card">


        <div class="product-details-header">

            <span class="product-category">

                <?= htmlspecialchars(
                    $product["Category"]
                ) ?>

            </span>


            <?php if ($offer): ?>

                <?php if (
                    $offer["OfferType"]
                    === "Percentage"
                ): ?>

                    <div style="
                        display:inline-block;
                        margin:12px 0;
                        padding:10px 18px;
                        border-radius:25px;
                        background:var(--purple);
                        color:white;
                        font-weight:800;
                    ">

                        <?= number_format(
                            (float)
                            $offer["DiscountValue"],
                            0
                        ) ?>% DISCOUNT

                    </div>

                <?php elseif (
                    $offer["OfferType"]
                    === "BuyXGetY"
                ): ?>

                    <div style="
                        display:inline-block;
                        margin:12px 0;
                        padding:10px 18px;
                        border-radius:25px;
                        background:var(--purple);
                        color:white;
                        font-weight:800;
                    ">

                        BUY
                        <?= (int)
                            $offer["BuyQuantity"] ?>

                        GET

                        <?= (int)
                            $offer["GetQuantity"] ?>

                    </div>

                <?php endif; ?>

            <?php endif; ?>


            <h1>

                <?= htmlspecialchars(
                    $product["ProductName"]
                ) ?>

            </h1>

        </div>


        <div class="product-details-content">


            <div class="product-detail-section">

                <h3>
                    Product Description
                </h3>

                <p>

                    <?= nl2br(
                        htmlspecialchars(
                            $product["Description"]
                                ?? "No description available."
                        )
                    ) ?>

                </p>

            </div>


            <div class="product-detail-section">

                <h3>
                    Price
                </h3>


                <?php if (
                    $offer &&
                    $offer["OfferType"]
                    === "Percentage"
                ): ?>

                    <p class="product-price">

                        <span style="
                            text-decoration:line-through;
                            opacity:.6;
                            font-size:16px;
                        ">
                            ৳<?= number_format(
                                $originalPrice,
                                2
                            ) ?>
                        </span>

                        <strong style="
                            color:var(--purple);
                            margin-left:10px;
                            font-size:24px;
                        ">
                            ৳<?= number_format(
                                $offerPrice,
                                2
                            ) ?>
                        </strong>

                    </p>

                    <p style="
                        color:var(--purple);
                        font-weight:600;
                    ">
                        Offer price per item
                    </p>

                <?php else: ?>

                    <p class="product-price">

                        ৳<?= number_format(
                            $originalPrice,
                            2
                        ) ?>

                    </p>

                <?php endif; ?>

            </div>


            <?php if (
                $offer &&
                $offer["OfferType"]
                === "BuyXGetY"
            ): ?>

                <div class="product-detail-section">

                    <h3>
                        Special Offer
                    </h3>

                    <p style="
                        color:var(--purple);
                        font-weight:700;
                        font-size:18px;
                    ">

                        Buy
                        <?= (int)
                            $offer["BuyQuantity"] ?>

                        Get

                        <?= (int)
                            $offer["GetQuantity"] ?>

                        Free

                    </p>

                    <p>
                        Example:
                        Buy
                        <?= (int)
                            $offer["BuyQuantity"] ?>

                        paid item(s) and receive
                        <?= (int)
                            $offer["GetQuantity"] ?>

                        additional free item(s).
                    </p>

                </div>

            <?php endif; ?>


            <div class="product-detail-section">

                <h3>
                    Stock
                </h3>

                <p>
                    <?= $stock ?>
                </p>

            </div>


            <div class="product-detail-section">

                <h3>
                    Availability
                </h3>

                <p>

                    <?= htmlspecialchars(
                        $displayStatus
                    ) ?>

                </p>

            </div>


            <div class="product-detail-section">

                <h3>
                    Seller
                </h3>

                <p>

                    <strong>
                        <?= htmlspecialchars(
                            $product["bussinessName"]
                        ) ?>
                    </strong>

                    <br>

                    Seller:
                    <?= htmlspecialchars(
                        $product["SellerName"]
                    ) ?>

                    <br>

                    Department:
                    <?= htmlspecialchars(
                        $product["department"]
                    ) ?>

                </p>

            </div>


        </div>


        <div class="product-details-actions">

            <a
                href="index.php"
                class="btn"
            >
                Back to Products
            </a>

        </div>


    </div>

</div>


<?php

include "../../includes/footer.php";

?>