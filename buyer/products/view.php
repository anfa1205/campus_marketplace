```php
<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";


$product_id =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


$product = null;


if ($product_id > 0) {

    $stmt = $pdo->prepare(
        "SELECT
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

         FROM PRODUCT p

         INNER JOIN SELLER s
            ON p.SellerID = s.sellerID

         WHERE p.ProductID = ?"
    );

    $stmt->execute([
        $product_id
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


if (
    $stock <= 0
) {

    $display_status =
        "Out of Stock";

} elseif (
    strcasecmp(
        $status,
        "Unavailable"
    ) === 0
) {

    $display_status =
        "Unavailable";

} else {

    $display_status =
        "Available";

}

?>


<div class="product-details-page">

    <div class="product-details-card">


        <div class="product-details-header">

            <span class="product-category">

                <?= htmlspecialchars(
                    $product["Category"]
                ) ?>

            </span>


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

                <p class="product-price">

                    ৳<?= number_format(
                        (float) $product["Price"],
                        2
                    ) ?>

                </p>

            </div>


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
                        $display_status
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
```
