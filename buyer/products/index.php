```php
<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";


/* Get selected category */

$category = trim($_GET["category"] ?? "");


/* Get all categories */

$category_stmt = $pdo->query(
    "SELECT DISTINCT Category
     FROM PRODUCT
     ORDER BY Category"
);

$categories = $category_stmt->fetchAll();


/* Get products */

if ($category !== "") {

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
            s.bussinessName

         FROM PRODUCT p

         INNER JOIN SELLER s
            ON p.SellerID = s.sellerID

         WHERE p.Category = ?

         ORDER BY p.ProductID DESC"
    );

    $stmt->execute([
        $category
    ]);

} else {

    $stmt = $pdo->query(
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
            s.bussinessName

         FROM PRODUCT p

         INNER JOIN SELLER s
            ON p.SellerID = s.sellerID

         ORDER BY p.ProductID DESC"
    );

}

$products = $stmt->fetchAll();

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


    <!-- Category Filter -->

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

                <?php foreach (
                    $categories
                    as $item
                ): ?>

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


    <!-- Products -->

    <?php if (
        count($products) === 0
    ): ?>

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

            <?php foreach (
                $products
                as $product
            ): ?>


                <?php

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


                <div class="product-card">

                    <div class="product-card-content">

                        <span class="product-category">

                            <?= htmlspecialchars(
                                $product["Category"]
                            ) ?>

                        </span>


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


                        <p class="product-price">

                            ৳<?= number_format(
                                (float) $product["Price"],
                                2
                            ) ?>

                        </p>


                        <p class="product-stock">

                            Stock:
                            <?= $stock ?>

                        </p>


                        <p class="product-status">

                            <?= htmlspecialchars(
                                $display_status
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
```
