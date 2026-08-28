```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

include "../../includes/header.php";

$seller_id = $_SESSION["user_id"];


/*
    Get products belonging to logged-in seller
*/

$stmt = $pdo->prepare(
    "SELECT ProductID, ProductName, Stock, Price
     FROM product
     WHERE SellerID = ?
     ORDER BY ProductName"
);

$stmt->execute([$seller_id]);

$products = $stmt->fetchAll();


/*
    Create announcement
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $selling_date =
        $_POST["selling_date"];

    $selling_time =
        $_POST["selling_time"];

    $campus_location =
        trim($_POST["campus_location"]);

    $available_quantity =
        (int) $_POST["available_quantity"];

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
        $available_quantity <= 0 ||
        count($selected_products) == 0
    ) {

        $error =
            "Please fill in all fields and select at least one product.";

    } else {

        try {

            $pdo->beginTransaction();


            /*
                Insert announcement
            */

            $stmt = $pdo->prepare(
                "INSERT INTO sales_announcement
                (
                    SellingTime,
                    SellingDate,
                    AvailableQuantity,
                    CampusLocation,
                    Status,
                    SellerId
                )
                VALUES (?, ?, ?, ?, 'Upcoming', ?)"
            );

            $stmt->execute([
                $selling_time,
                $selling_date,
                $available_quantity,
                $campus_location,
                $seller_id
            ]);


            $announcement_id =
                $pdo->lastInsertId();


            /*
                Insert products with their quantities
            */

            $stmt = $pdo->prepare(
                "INSERT INTO includes
                (
                    announcementID,
                    productID,
                    quantity
                )
                VALUES (?, ?, ?)"
            );


            foreach ($selected_products as $product_id) {

                $quantity =
                    (int) ($product_quantities[$product_id] ?? 0);


                if ($quantity <= 0) {

                    throw new Exception(
                        "Invalid product quantity."
                    );
                }


                $stmt->execute([
                    $announcement_id,
                    (int) $product_id,
                    $quantity
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
                "Something went wrong. Please check the quantities.";
        }
    }
}

?>


<div class="seller-dashboard">

    <div class="dashboard-intro">

        <p class="dashboard-label">
            SALES ANNOUNCEMENTS
        </p>

        <h1>
            Create Sales Announcement
        </h1>

        <div class="title-line"></div>

    </div>


    <?php if (isset($error)): ?>

        <div class="card">

            <p>
                <?= htmlspecialchars($error) ?>
            </p>

        </div>

        <br>

    <?php endif; ?>


    <div class="card">

        <form method="POST">


            <!-- Selling Date -->

            <label>
                Selling Date
            </label>

            <br>

            <input
                type="date"
                name="selling_date"
                required
            >

            <br>
            <br>


            <!-- Selling Time -->

            <label>
                Selling Time
            </label>

            <br>

            <input
                type="time"
                name="selling_time"
                required
            >

            <br>
            <br>


            <!-- Campus Location -->

            <label>
                Campus Location
            </label>

            <br>

            <input
                type="text"
                name="campus_location"
                placeholder="Example: BRAC University Campus"
                required
            >

            <br>
            <br>


            <!-- Total Available Quantity -->

            <label>
                Total Available Quantity
            </label>

            <br>

            <input
                type="number"
                name="available_quantity"
                min="1"
                required
            >

            <br>
            <br>


            <!-- Products -->

            <label>
                Products Included in Sale
            </label>

            <br>
            <br>


            <?php if (count($products) == 0): ?>

                <p>
                    You have no products yet.
                    Please add a product first.
                </p>

            <?php else: ?>

                <?php foreach ($products as $product): ?>

                    <div style="margin-bottom: 15px;">

                        <label>

                            <input
                                type="checkbox"
                                name="products[]"
                                value="<?= $product["ProductID"] ?>"
                            >

                            <?= htmlspecialchars(
                                $product["ProductName"]
                            ) ?>

                            —
                            ৳<?= htmlspecialchars(
                                $product["Price"]
                            ) ?>

                            (Stock:
                            <?= htmlspecialchars(
                                $product["Stock"]
                            ) ?>)

                        </label>


                        <br>


                        <label>
                            Quantity:
                        </label>

                        <input
                            type="number"
                            name="quantity[<?= $product["ProductID"] ?>]"
                            min="1"
                            max="<?= $product["Stock"] ?>"
                            value="1"
                        >

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>


            <br>


            <button
                type="submit"
                class="btn"
            >
                Create Announcement
            </button>


            <a
                href="index.php"
                class="btn"
            >
                Cancel
            </a>


        </form>

    </div>

</div>


<?php

include "../../includes/footer.php";

?>
```
