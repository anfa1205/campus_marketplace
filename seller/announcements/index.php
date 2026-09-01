<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

include "../../includes/header.php";

$seller_id =
    $_SESSION["user_id"];


/*
    Get announcements created by this seller
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM sales_announcement
     WHERE SellerId = ?
     ORDER BY SellingDate DESC, SellingTime DESC"
);

$stmt->execute([
    $seller_id
]);

$announcements =
    $stmt->fetchAll();

?>


<div class="seller-dashboard">


    <div class="dashboard-intro">

        <p class="dashboard-label">
            SALES ANNOUNCEMENTS
        </p>

        <h1>
            My Sales Announcements
        </h1>

        <div class="title-line"></div>

    </div>


    <!-- Create Announcement -->

    <div class="announcement-actions">

        <a
            href="create.php"
            class="btn"
        >
            + Create Announcement
        </a>

    </div>


    <?php if (count($announcements) == 0): ?>


        <div class="card">

            <h2>
                No Sales Announcements Yet
            </h2>

            <p>
                You have not created any sales announcements.
            </p>

        </div>


    <?php else: ?>


        <div class="dashboard-grid">


            <?php foreach ($announcements as $announcement): ?>


                <?php

                /*
                    Get products included
                    in this announcement
                */

                $product_stmt = $pdo->prepare(
                    "SELECT
                        p.ProductName,
                        p.Price,
                        p.Stock,
                        i.quantity
                     FROM includes i
                     INNER JOIN product p
                        ON i.productID = p.ProductID
                     WHERE i.announcementID = ?"
                );

                $product_stmt->execute([
                    $announcement["AnnouncementId"]
                ]);

                $products =
                    $product_stmt->fetchAll();

                ?>


                <div class="dashboard-card">


                    <div class="card-icon">
                        ◇
                    </div>


                    <h3>
                        <?= htmlspecialchars(
                            $announcement["CampusLocation"]
                        ) ?>
                    </h3>


                    <p>
                        Date:
                        <?= htmlspecialchars(
                            $announcement["SellingDate"]
                        ) ?>
                    </p>


                    <p>
                        Time:
                        <?= htmlspecialchars(
                            $announcement["SellingTime"]
                        ) ?>
                    </p>


                    <p>
                        Status:
                        <?= htmlspecialchars(
                            $announcement["Status"]
                        ) ?>
                    </p>


                    <hr>


                    <h4>
                        Products Included
                    </h4>


                    <?php if (count($products) == 0): ?>


                        <p>
                            No products added.
                        </p>


                    <?php else: ?>


                        <?php foreach ($products as $product): ?>


                            <p class="announcement-product-display">

                                <strong>
                                    <?= htmlspecialchars(
                                        $product["ProductName"]
                                    ) ?>
                                </strong>

                                <br>

                                ৳<?= htmlspecialchars(
                                    $product["Price"]
                                ) ?>

                                &nbsp; — &nbsp;

                                Quantity:
                                <?= htmlspecialchars(
                                    $product["quantity"]
                                ) ?>


                                <?php if (
                                    (int) $product["Stock"] <= 0
                                ): ?>

                                    <br>

                                    <strong>
                                        OUT OF STOCK
                                    </strong>

                                <?php endif; ?>

                            </p>


                        <?php endforeach; ?>


                    <?php endif; ?>


                    <br>


                    <div class="announcement-card-actions">


                        <a
                            href="edit.php?id=<?= $announcement["AnnouncementId"] ?>"
                            class="edit-button"
                        >
                            Edit
                        </a>


                        <a
                            href="delete.php?id=<?= $announcement["AnnouncementId"] ?>"
                            class="delete-button"
                            onclick="return confirm('Are you sure you want to delete this announcement?');"
                        >
                            Delete
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