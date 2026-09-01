<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

include "../../includes/header.php";

$seller_id = $_SESSION["user_id"];

$error = "";


/*
    Get all products belonging to this seller
*/

$product_stmt = $pdo->prepare(
    "SELECT
        ProductID,
        ProductName,
        Price,
        Stock,
        Status
     FROM product
     WHERE SellerId = ?
     ORDER BY ProductName ASC"
);

$product_stmt->execute([$seller_id]);

$products = $product_stmt->fetchAll();


/*
    Create announcement
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $selling_date =
        trim($_POST["selling_date"] ?? "");

    $selling_time =
        trim($_POST["selling_time"] ?? "");

    $campus_location =
        trim($_POST["campus_location"] ?? "");

    $selected_products =
        $_POST["products"] ?? [];

    $product_quantities =
        $_POST["quantity"] ?? [];


    /*
        Basic validation
    */

    if (
        empty($selling_date) ||
        empty($selling_time) ||
        empty($campus_location) ||
        count($selected_products) == 0
    ) {

        $error =
            "Please fill in all required fields and select at least one product.";

    } else {

        try {

            $pdo->beginTransaction();


            /*
                Validate every selected product
            */

            $validated_products = [];


            foreach ($selected_products as $product_id) {

                $product_id = (int) $product_id;

                $quantity =
                    (int) ($product_quantities[$product_id] ?? 0);


                /*
                    Quantity must be greater than 0
                */

                if ($quantity <= 0) {

                    throw new Exception(
                        "Please enter a valid quantity for every selected product."
                    );
                }


                /*
                    Check that the product belongs
                    to the logged-in seller
                */

                $check_stmt = $pdo->prepare(
                    "SELECT
                        ProductID,
                        ProductName,
                        Stock
                     FROM product
                     WHERE ProductID = ?
                     AND SellerId = ?"
                );

                $check_stmt->execute([
                    $product_id,
                    $seller_id
                ]);

                $product = $check_stmt->fetch();


                if (!$product) {

                    throw new Exception(
                        "Invalid product selected."
                    );
                }


                /*
                    Product with stock 0 cannot
                    be added to an announcement
                */

                if ((int) $product["Stock"] <= 0) {

                    throw new Exception(
                        $product["ProductName"] .
                        " is out of stock and cannot be added to the sale."
                    );
                }


                /*
                    Sale quantity cannot be
                    greater than current stock
                */

                if ($quantity > (int) $product["Stock"]) {

                    throw new Exception(
                        "Quantity for " .
                        $product["ProductName"] .
                        " cannot be greater than its stock."
                    );
                }


                $validated_products[] = [
                    "product_id" => $product_id,
                    "quantity" => $quantity
                ];
            }


            /*
                Create the announcement
            */

            $stmt = $pdo->prepare(
                "INSERT INTO sales_announcement
                (
                    SellingTime,
                    SellingDate,
                    CampusLocation,
                    Status,
                    SellerId
                )
                VALUES (?, ?, ?, 'Upcoming', ?)"
            );

            $stmt->execute([
                $selling_time,
                $selling_date,
                $campus_location,
                $seller_id
            ]);


            /*
                Get the new announcement ID
            */

            $announcement_id =
                $pdo->lastInsertId();


            /*
                Insert products into includes table
            */

            $include_stmt = $pdo->prepare(
                "INSERT INTO includes
                (
                    announcementID,
                    productID,
                    quantity
                )
                VALUES (?, ?, ?)"
            );


            foreach ($validated_products as $item) {

                $include_stmt->execute([
                    $announcement_id,
                    $item["product_id"],
                    $item["quantity"]
                ]);
            }


            $pdo->commit();


            header("Location: index.php");

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

?>


<div class="product-form-page">

    <div class="product-form-card">


        <div class="form-heading">

            <p class="dashboard-label">
                SALES ANNOUNCEMENT
            </p>

            <h1>
                Create Sales Announcement
            </h1>

            <p>
                Add the products you want to offer in this sale.
            </p>

        </div>


        <?php if (!empty($error)): ?>

            <div class="form-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- Selling Date -->

            <div class="form-group">

                <label>
                    Selling Date
                </label>

                <input
                    type="date"
                    name="selling_date"
                    value="<?= htmlspecialchars(
                        $_POST["selling_date"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <!-- Selling Time -->

            <div class="form-group">

                <label>
                    Selling Time
                </label>

                <input
                    type="time"
                    name="selling_time"
                    value="<?= htmlspecialchars(
                        $_POST["selling_time"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <!-- Campus Location -->

            <div class="form-group">

                <label>
                    Campus Location
                </label>

                <input
                    type="text"
                    name="campus_location"
                    value="<?= htmlspecialchars(
                        $_POST["campus_location"] ?? ""
                    ) ?>"
                    placeholder="Enter campus location"
                    required
                >

            </div>


            <!-- Products -->

            <div class="form-group">

                <label>
                    Products Included in This Sale
                </label>


                <?php if (count($products) == 0): ?>

                    <p>
                        You do not have any products yet.
                        Please add a product first.
                    </p>


                <?php else: ?>


                    <div class="announcement-products">


                        <?php foreach ($products as $product): ?>

                            <?php

                            $product_id =
                                $product["ProductID"];

                            $stock =
                                (int) $product["Stock"];

                            $checked =
                                isset($_POST["products"]) &&
                                in_array(
                                    $product_id,
                                    $_POST["products"]
                                );

                            /*
                                If stock becomes 0,
                                it cannot remain selected.
                            */

                            if ($stock <= 0) {

                                $checked = false;
                            }

                            $entered_quantity =
                                $_POST["quantity"][$product_id]
                                ?? 1;

                            ?>


                            <div class="announcement-product-row">


                                <div class="announcement-product-info">


                                    <label class="product-check">

                                        <input
                                            type="checkbox"
                                            name="products[]"
                                            value="<?= $product_id ?>"
                                            <?= $checked
                                                ? "checked"
                                                : "" ?>
                                            <?= $stock <= 0
                                                ? "disabled"
                                                : "" ?>
                                        >

                                        <span>
                                            <?= htmlspecialchars(
                                                $product["ProductName"]
                                            ) ?>
                                        </span>

                                    </label>


                                    <small>

                                        ৳<?= htmlspecialchars(
                                            $product["Price"]
                                        ) ?>

                                        &nbsp; | &nbsp;


                                        <?php if ($stock <= 0): ?>

                                            <strong>
                                                OUT OF STOCK
                                            </strong>


                                        <?php else: ?>

                                            Stock:
                                            <?= $stock ?>

                                        <?php endif; ?>

                                    </small>


                                </div>


                                <div class="announcement-quantity">

                                    <label>
                                        Quantity
                                    </label>

                                    <input
                                        type="number"
                                        name="quantity[<?= $product_id ?>]"
                                        min="1"
                                        max="<?= $stock ?>"
                                        value="<?= htmlspecialchars(
                                            $entered_quantity
                                        ) ?>"
                                        <?= $stock <= 0
                                            ? "disabled"
                                            : "" ?>
                                    >

                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php endif; ?>


            </div>


            <!-- Buttons -->

            <div class="form-buttons">

                <button
                    type="submit"
                    class="primary-form-button"
                >
                    Create Announcement
                </button>


                <a
                    href="index.php"
                    class="cancel-button"
                >
                    Cancel
                </a>

            </div>


        </form>


    </div>

</div>


<?php

include "../../includes/footer.php";

?>