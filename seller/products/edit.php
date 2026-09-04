```php
<?php

require_once "../../config/database.php";

require_once "../../includes/seller_check.php";


$seller_id =
    $_SESSION["user_id"];


$product_id =
    $_GET["id"] ?? "";


/*
    If no product ID exists,
    return to product list.
*/

if ($product_id === "") {

    header(
        "Location: index.php"
    );

    exit;
}



/*
    =========================
    GET PRODUCT
    =========================

    We check both ProductID
    and SellerID.

    This prevents a seller
    from editing another
    seller's product.
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM PRODUCT
     WHERE ProductID = ?
     AND SellerID = ?"
);

$stmt->execute([

    $product_id,

    $seller_id

]);


$product =
    $stmt->fetch();



/*
    Product doesn't exist
    or doesn't belong to
    this seller.
*/

if (!$product) {

    header(
        "Location: index.php"
    );

    exit;
}


$error = "";



/*
    =========================
    FORM SUBMISSION
    =========================
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $product_name =
        trim($_POST["product_name"] ?? "");


    $category =
        trim($_POST["category"] ?? "");


    $description =
        trim($_POST["description"] ?? "");


    $price =
        trim($_POST["price"] ?? "");


    $stock =
        trim($_POST["stock"] ?? "");


    $status =
        $_POST["status"] ?? "";



    /*
        =========================
        VALIDATION
        =========================
    */


    if (
        $product_name === "" ||
        $category === "" ||
        $price === "" ||
        $stock === "" ||
        $status === ""
    ) {

        $error =
            "Please fill in all required fields.";

    }


    elseif (
        !is_numeric($price) ||
        $price < 0
    ) {

        $error =
            "Please enter a valid price.";

    }


    elseif (
        !is_numeric($stock) ||
        $stock < 0
    ) {

        $error =
            "Please enter a valid stock quantity.";

    }


    /*
        Available cannot be selected
        when stock is zero.
    */

    elseif (
        $status === "Available" &&
        $stock == 0
    ) {

        $error =
            "A product with zero stock cannot be Available. Choose Unavailable instead.";

    }


    else {

        /*
            =========================
            UPDATE PRODUCT
            =========================
        */
        $stmt = $pdo->prepare(
            "UPDATE PRODUCT
             SET
                ProductName = ?,
                Category = ?,
                Description = ?,
                Price = ?,
                Stock = ?,
                Status = ?
             WHERE ProductID = ?
             AND SellerID = ?"
        );


        $stmt->execute([
            $product_name,
            $category,
            $description,
            $price,
            $stock,
            $status,
            $product_id,
            $seller_id
        ]);


        /*
            Return to product list
        */

        header(
            "Location: index.php"
        );

        exit;

    }

}



include "../../includes/header.php";

?>


<div class="product-form-page">


    <div class="product-form-card">


        <!-- =========================
             FORM HEADER
             ========================= -->

        <div class="form-heading">

            <p class="page-label">
                PRODUCT MANAGEMENT
            </p>


            <h1>
                Edit Product
            </h1>


            <p>
                Update the information of your product.
            </p>

        </div>



        <!-- =========================
             ERROR
             ========================= -->

        <?php if ($error !== ""): ?>

            <div class="form-error">

                <?= htmlspecialchars(
                    $error
                ) ?>

            </div>

        <?php endif; ?>



        <!-- =========================
             FORM
             ========================= -->

        <form method="POST">


            <!-- Product Name -->

            <div class="form-group">

                <label>
                    Product Name
                </label>


                <input
                    type="text"
                    name="product_name"
                    value="<?= htmlspecialchars(
                        $product["ProductName"]
                    ) ?>"
                    required
                >

            </div>



            <!-- Category -->

            <div class="form-group">

                <label>
                    Category
                </label>


                <input
                    type="text"
                    name="category"
                    value="<?= htmlspecialchars(
                        $product["Category"]
                    ) ?>"
                    required
                >

            </div>



            <!-- Description -->

            <div class="form-group">

                <label>
                    Description
                </label>


                <textarea
                    name="description"
                    rows="5"
                    placeholder="Describe your product (optional)"
                ><?= htmlspecialchars(
                    $product["Description"] ?? ""
                ) ?></textarea>

            </div>



            <!-- Price and Stock -->

            <div class="form-row">


                <div class="form-group">

                    <label>
                        Price
                    </label>


                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        min="0"
                        value="<?= htmlspecialchars(
                            $product["Price"]
                        ) ?>"
                        required
                    >

                </div>



                <div class="form-group">

                    <label>
                        Stock
                    </label>


                    <input
                        type="number"
                        name="stock"
                        min="0"
                        value="<?= htmlspecialchars(
                            $product["Stock"]
                        ) ?>"
                        required
                    >

                </div>


            </div>



            <!-- Status -->

            <div class="form-group">

                <label>
                    Status
                </label>


                <select
                    name="status"
                    required
                >


                    <option
                        value="Available"
                        <?= (
                            $product["Status"]
                            === "Available"
                        )
                            ? "selected"
                            : "" ?>
                    >
                        Available
                    </option>


                    <option
                        value="Unavailable"
                        <?= (
                            $product["Status"]
                            === "Unavailable"
                        )
                            ? "selected"
                            : "" ?>
                    >
                        Unavailable
                    </option>


                </select>

            </div>



            <!-- Buttons -->

            <div class="form-buttons">


                <button
                    type="submit"
                    class="primary-form-button"
                >
                    Save Changes
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
```
