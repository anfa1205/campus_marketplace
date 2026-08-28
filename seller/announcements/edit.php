```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

include "../../includes/header.php";

$seller_id = $_SESSION["user_id"];

$announcement_id = $_GET["id"] ?? 0;


/*
    Get announcement
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM sales_announcement
     WHERE AnnouncementId = ?
     AND SellerId = ?"
);

$stmt->execute([
    $announcement_id,
    $seller_id
]);

$announcement = $stmt->fetch();


if (!$announcement) {

    echo "<div class='card'>";
    echo "<h2>Announcement not found.</h2>";
    echo "</div>";

    include "../../includes/footer.php";
    exit;
}


/*
    Get seller products
*/

$product_stmt = $pdo->prepare(
    "SELECT ProductID, ProductName, Stock, Price
     FROM product
     WHERE SellerID = ?
     ORDER BY ProductName"
);

$product_stmt->execute([$seller_id]);

$products = $product_stmt->fetchAll();


/*
    Get included products and quantities
*/

$included_stmt = $pdo->prepare(
    "SELECT productID, quantity
     FROM includes
     WHERE announcementID = ?"
);

$included_stmt->execute([
    $announcement_id
]);

$included_data = [];

while ($row = $included_stmt->fetch()) {

    $included_data[
        $row["productID"]
    ] = $row["quantity"];
}


/*
    Update announcement
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
                Update announcement
            */

            $stmt = $pdo->prepare(
                "UPDATE sales_announcement
                 SET SellingDate = ?,
                     SellingTime = ?,
                     AvailableQuantity = ?,
                     CampusLocation = ?
                 WHERE AnnouncementId = ?
                 AND SellerId = ?"
            );

            $stmt->execute([
                $selling_date,
                $selling_time,
                $available_quantity,
                $campus_location,
                $announcement_id,
                $seller_id
            ]);


            /*
                Delete old product records
            */

            $stmt = $pdo->prepare(
                "DELETE FROM includes
                 WHERE announcementID = ?"
            );

            $stmt->execute([
                $announcement_id
            ]);


            /*
                Insert updated products
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
            Edit Sales Announcement
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
                value="<?= htmlspecialchars(
                    $announcement["SellingDate"]
                ) ?>"
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
                value="<?= htmlspecialchars(
                    $announcement["SellingTime"]
                ) ?>"
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
                value="<?= htmlspecialchars(
                    $announcement["CampusLocation"]
                ) ?>"
                required
            >

            <br>
            <br>


            <!-- Available Quantity -->

            <label>
                Total Available Quantity
            </label>

            <br>

            <input
                type="number"
                name="available_quantity"
                min="1"
                value="<?= htmlspecialchars(
                    $announcement["AvailableQuantity"]
                ) ?>"
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


            <?php foreach ($products as $product): ?>

                <div style="margin-bottom: 15px;">

                    <label>

                        <input
                            type="checkbox"
                            name="products[]"
                            value="<?= $product["ProductID"] ?>"
                            <?= array_key_exists(
                                $product["ProductID"],
                                $included_data
                            ) ? "checked" : "" ?>
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
                        value="<?= htmlspecialchars(
                            $included_data[
                                $product["ProductID"]
                            ] ?? 1
                        ) ?>"
                    >

                </div>

            <?php endforeach; ?>


            <br>


            <button
                type="submit"
                class="btn"
            >
                Save Changes
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
