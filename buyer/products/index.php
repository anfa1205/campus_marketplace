<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";
require_once "../../includes/offer_helper.php";

$category =
    isset($_GET["category"])
        ? trim($_GET["category"])
        : "";


/* CATEGORIES */

$stmt = $pdo->query("
    SELECT DISTINCT Category
    FROM product
    ORDER BY Category
");

$categories = $stmt->fetchAll();


/* PRODUCTS */

if ($category !== "") {

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
            s.bussinessName

        FROM product p

        INNER JOIN seller s
            ON p.SellerID = s.sellerID

        WHERE p.Category = ?

        ORDER BY p.ProductID DESC
    ");

    $stmt->execute([$category]);

} else {

    $stmt = $pdo->query("
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
            s.bussinessName

        FROM product p

        INNER JOIN seller s
            ON p.SellerID = s.sellerID

        ORDER BY p.ProductID DESC
    ");

}

$products = $stmt->fetchAll();

include "../../includes/header.php";

?>

<div class="buyer-products-page">

    <div class="products-header">

        <p class="dashboard-label">
            CAMPUS MARKETPLACE
        </p>

        <h1>
            Browse Products
        </h1>

        <div class="title-line"></div>

    </div>


    <div class="product-filter">

        <form method="GET">

            <label for="category">
                Category
            </label>

            <select
                name="category"
                id="category"
                onchange="this.form.submit()"
            >

                <option value="">
                    All Categories
                </option>

                <?php foreach ($categories as $item): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $item["Category"]
                        ) ?>"
                        <?= $category === $item["Category"]
                            ? "selected"
                            : "" ?>
                    >
                        <?= htmlspecialchars(
                            $item["Category"]
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </form>

    </div>


    <?php if (count($products) === 0): ?>

        <div class="empty-products">

            <h3>
                No Products Found
            </h3>

            <p>
                There are no products available in this category.
            </p>

        </div>

    <?php else: ?>

        <div class="products-grid">

            <?php foreach ($products as $product): ?>

                <?php

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

                <div class="product-card">

                    <div class="product-card-content">

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
                                    margin:10px 0;
                                    padding:7px 13px;
                                    border-radius:20px;
                                    background:var(--purple);
                                    color:white;
                                    font-weight:800;
                                    font-size:13px;
                                ">

                                    <?= number_format(
                                        (float)
                                        $offer["DiscountValue"],
                                        0
                                    ) ?>% OFF

                                </div>

                            <?php elseif (
                                $offer["OfferType"]
                                === "BuyXGetY"
                            ): ?>

                                <div style="
                                    display:inline-block;
                                    margin:10px 0;
                                    padding:7px 13px;
                                    border-radius:20px;
                                    background:var(--purple);
                                    color:white;
                                    font-weight:800;
                                    font-size:13px;
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


                        <h2>

                            <?= htmlspecialchars(
                                $product["ProductName"]
                            ) ?>

                        </h2>


                        <p class="product-description">

                            <?= htmlspecialchars(
                                $product["Description"]
                                    ?? ""
                            ) ?>

                        </p>


                        <?php if (
                            $offer &&
                            $offer["OfferType"]
                            === "Percentage"
                        ): ?>

                            <p
                                class="product-price"
                                style="
                                    margin-bottom:4px;
                                "
                            >

                                <span style="
                                    text-decoration:line-through;
                                    opacity:.6;
                                    font-size:15px;
                                ">
                                    ৳<?= number_format(
                                        $originalPrice,
                                        2
                                    ) ?>
                                </span>

                                <strong style="
                                    color:var(--purple);
                                    margin-left:8px;
                                ">
                                    ৳<?= number_format(
                                        $offerPrice,
                                        2
                                    ) ?>
                                </strong>

                            </p>

                        <?php else: ?>

                            <p class="product-price">

                                ৳<?= number_format(
                                    $originalPrice,
                                    2
                                ) ?>

                            </p>

                        <?php endif; ?>


                        <p class="product-stock">

                            Stock:
                            <?= $stock ?>

                        </p>


                        <p class="product-status">

                            <?= htmlspecialchars(
                                $displayStatus
                            ) ?>

                        </p>


                        <p class="product-seller">

                            Seller:
                            <?= htmlspecialchars(
                                $product["bussinessName"]
                            ) ?>

                        </p>


                        <a
                            href="view.php?id=<?= (int) $product["ProductID"] ?>"
                            class="btn"
                        >
                            View Details
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<?php

include "../../includes/footer.php";

?>