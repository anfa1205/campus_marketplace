<?php

require_once "../../config/database.php";

require_once "../../includes/seller_check.php";

include "../../includes/header.php";


$seller_id = $_SESSION["user_id"];


/*
    Get only the products belonging
    to the currently logged-in seller.
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM PRODUCT
     WHERE SellerID = ?
     ORDER BY ProductID DESC"
);

$stmt->execute([
    $seller_id
]);

$products = $stmt->fetchAll();

?>


<div class="product-page">


    <!-- =========================
         PAGE HEADER
         ========================= -->

    <div class="product-page-header">

        <div>

            <p class="page-label">
                SELLER AREA
            </p>

            <h1>
                Product Management
            </h1>

            <p class="page-description">
                Manage the products available in your campus store.
            </p>

        </div>


        <a
            href="add.php"
            class="product-add-button"
        >
            + Add New Product
        </a>

    </div>



    <!-- =========================
         PRODUCT TABLE
         ========================= -->

    <div class="product-table-container">


        <?php if (count($products) > 0): ?>


            <table class="product-table">

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Stock
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($products as $product): ?>


                        <?php

                        /*
                            Determine the status shown
                            to the seller.
                        */

                        if (
                            $product["Status"] === "Unavailable"
                        ) {

                            $display_status =
                                "Unavailable";

                        } elseif (
                            $product["Stock"] == 0
                        ) {

                            $display_status =
                                "Out of Stock";

                        } else {

                            $display_status =
                                "Available";

                        }


                        $status_class =
                            strtolower(
                                str_replace(
                                    " ",
                                    "-",
                                    $display_status
                                )
                            );

                        ?>


                        <tr>


                            <!-- Product Name -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $product["ProductName"]
                                    ) ?>

                                </strong>

                            </td>


                            <!-- Category -->

                            <td>

                                <?= htmlspecialchars(
                                    $product["Category"]
                                ) ?>

                            </td>


                            <!-- Price -->

                            <td>

                                ৳<?= number_format(
                                    $product["Price"],
                                    2
                                ) ?>

                            </td>


                            <!-- Stock -->

                            <td>

                                <?= htmlspecialchars(
                                    $product["Stock"]
                                ) ?>

                            </td>


                            <!-- Status -->

                            <td>

                                <span
                                    class="status-badge <?= $status_class ?>"
                                >

                                    <?= htmlspecialchars(
                                        $display_status
                                    ) ?>

                                </span>

                            </td>


                            <!-- Actions -->

                            <td>

                                <div class="product-actions">


                                    <a
                                        href="edit.php?id=<?= $product["ProductID"] ?>"
                                        class="edit-button"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="delete.php?id=<?= $product["ProductID"] ?>"
                                        class="delete-button"
                                        onclick="return confirm('Are you sure you want to delete this product?');"
                                    >
                                        Delete
                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>

            </table>


        <?php else: ?>


            <!-- =========================
                 NO PRODUCTS
                 ========================= -->

            <div class="empty-products">

                <div class="empty-icon">
                    ◇
                </div>


                <h2>
                    No Products Yet
                </h2>


                <p>
                    You have not added any products to your store.
                </p>


                <a
                    href="add.php"
                    class="product-add-button"
                >
                    Add Your First Product
                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


<?php

include "../../includes/footer.php";

?>