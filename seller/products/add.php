```php
<?php

require_once "../../config/database.php";

require_once "../../includes/seller_check.php";


$error = "";

$seller_id = $_SESSION["user_id"];


/*
    When the seller submits the form
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
        Available is only allowed
        when stock is greater than 0.
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
            INSERT PRODUCT
            =========================
        */

        $stmt = $pdo->prepare(
            "INSERT INTO PRODUCT
            (
                SellerID,
                ProductName,
                Category,
                Description,
                Price,
                Stock,
                Status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->execute([

            $seller_id,

            $product_name,

            $category,

            $description,

            $price,

            $stock,

            $status

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
                Add New Product
            </h1>


            <p>
                Add a new product to your campus store.
            </p>

        </div>



        <!-- =========================
             ERROR
             ========================= -->

        <?php if ($error !== ""): ?>

            <div class="form-error">

                <?= htmlspecialchars($error) ?>

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
                    placeholder="Enter product name"
                    value="<?= htmlspecialchars(
                        $_POST["product_name"] ?? ""
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
                    placeholder="Example: Bakery, Books, Clothing"
                    value="<?= htmlspecialchars(
                        $_POST["category"] ?? ""
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
                    placeholder="Describe your product (optional)"
                    rows="5"
                ><?= htmlspecialchars(
                    $_POST["description"] ?? ""
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
                        placeholder="0.00"
                        value="<?= htmlspecialchars(
                            $_POST["price"] ?? ""
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
                        placeholder="0"
                        value="<?= htmlspecialchars(
                            $_POST["stock"] ?? ""
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


                    <option value="">
                        Select Status
                    </option>


                    <option
                        value="Available"
                        <?= (
                            ($_POST["status"] ?? "")
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
                            ($_POST["status"] ?? "")
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
                    Add Product
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
