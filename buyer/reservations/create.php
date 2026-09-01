```php
<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";

$buyer_id = $_SESSION["user_id"];

$error = "";
$success = "";


/* ---------------------------------
   GET PRODUCT ID
--------------------------------- */

$product_id = isset($_GET["productID"])
    ? (int) $_GET["productID"]
    : 0;


/* ---------------------------------
   CHECK PRODUCT
--------------------------------- */

if ($product_id <= 0) {

    $error = "Invalid product.";

} else {

    $stmt = $pdo->prepare(
        "SELECT
            p.ProductID,
            p.ProductName,
            p.Description,
            p.Category,
            p.Stock,
            p.Price,
            p.Status,
            p.SellerID,
            s.bussinessName
         FROM PRODUCT p
         INNER JOIN SELLER s
             ON p.SellerID = s.sellerID
         WHERE p.ProductID = ?"
    );

    $stmt->execute([
        $product_id
    ]);

    $product = $stmt->fetch();


    if (!$product) {

        $error = "Product not found.";

    }

}


/* ---------------------------------
   PROCESS RESERVATION
--------------------------------- */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $product &&
    $error === ""
) {

    $quantity =
        (int) ($_POST["quantity"] ?? 0);

    $contact_number =
        trim($_POST["contactNumber"] ?? "");


    /* Check quantity */

    if ($quantity <= 0) {

        $error =
            "Please enter a valid quantity.";

    }


    /* Check contact number */

    elseif ($contact_number === "") {

        $error =
            "Please enter your contact number.";

    }


    /* Check product status */

    elseif (
        strtolower(trim($product["Status"])) !== "available"
    ) {

        $error =
            "This product is currently unavailable.";

    }


    /* Check stock */

    elseif ($product["Stock"] <= 0) {

        $error =
            "This product is out of stock.";

    }


    /* Check requested quantity */

    elseif ($quantity > $product["Stock"]) {

        $error =
            "Requested quantity is greater than available stock.";

    }


    /* Save reservation */

    else {

        $stmt = $pdo->prepare(
            "INSERT INTO reservation
            (
                ReservationDate,
                ReservationTarget,
                Status,
                buyerID,
                announcementID,
                productID,
                quantity,
                contactNumber,
                sellerID
            )
            VALUES
            (
                NOW(),
                'Product',
                'Pending',
                ?,
                NULL,
                ?,
                ?,
                ?,
                ?
            )"
        );


        $stmt->execute([
            $buyer_id,
            $product["ProductID"],
            $quantity,
            $contact_number,
            $product["SellerID"]
        ]);


        $success =
            "Reservation submitted successfully.";
    }

}

?>


<div class="card">

    <h1>
        Reserve Product
    </h1>


    <?php if ($error !== ""): ?>

        <p>
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <p>
            <?= htmlspecialchars($success) ?>
        </p>

        <br>

        <a
            href="index.php"
            class="btn"
        >
            My Reservations
        </a>


    <?php elseif ($product): ?>


        <h2>
            <?= htmlspecialchars(
                $product["ProductName"]
            ) ?>
        </h2>


        <p>
            Category:
            <?= htmlspecialchars(
                $product["Category"]
            ) ?>
        </p>


        <p>
            Price:
            <?= number_format(
                $product["Price"],
                2
            ) ?>
        </p>


        <p>
            Available Stock:
            <?= htmlspecialchars(
                $product["Stock"]
            ) ?>
        </p>


        <p>
            Seller:
            <?= htmlspecialchars(
                $product["bussinessName"]
            ) ?>
        </p>


        <form method="POST">


            <label>
                Quantity
            </label>

            <input
                type="number"
                name="quantity"
                min="1"
                max="<?= (int) $product["Stock"] ?>"
                required
            >


            <label>
                Contact Number
            </label>

            <input
                type="text"
                name="contactNumber"
                maxlength="20"
                required
            >


            <br><br>


            <button
                type="submit"
                class="btn"
            >
                Submit Reservation
            </button>


            <a
                href="index.php"
                class="btn"
            >
                Cancel
            </a>


        </form>


    <?php endif; ?>

</div>


<?php

include "../../includes/footer.php";

?>
```
