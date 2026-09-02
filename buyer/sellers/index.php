<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";


/* =========================================================
   GET ALL SELLERS + RATING
   ========================================================= */

$stmt = $pdo->query(
    "SELECT
        s.sellerID,
        s.Name,
        s.department,
        s.bussinessName,

        COALESCE(
            (
                SELECT AVG(f.Rating)
                FROM feedback f
                WHERE f.sellerID = s.sellerID
            ),
            0
        ) AS average_rating,

        (
            SELECT COUNT(p.ProductID)
            FROM product p
            WHERE p.SellerID = s.sellerID
        ) AS product_count

     FROM seller s

     ORDER BY s.bussinessName"
);

$sellers = $stmt->fetchAll();


include "../../includes/header.php";

?>


<div class="sellers-page">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="sellers-page-header">

        <p class="dashboard-label">
            CAMPUS MARKETPLACE
        </p>

        <h1>
            Seller Profiles
        </h1>

        <p>
            Explore sellers, their products and ratings.
        </p>

        <div class="title-line"></div>

    </div>


    <!-- =====================================================
         SELLER CARDS
         ===================================================== -->

    <?php if (empty($sellers)): ?>

        <div class="no-products">

            <h3>
                No sellers available
            </h3>

        </div>


    <?php else: ?>


        <div class="seller-directory-grid">


            <?php foreach ($sellers as $seller): ?>


                <?php

                $rating = round(
                    floatval(
                        $seller["average_rating"]
                    ),
                    1
                );

                $full_stars = floor($rating);

                ?>


                <div class="seller-directory-card">


                    <div class="seller-directory-top">

                        <div class="seller-directory-icon">

                            <?= strtoupper(
                                substr(
                                    $seller["bussinessName"],
                                    0,
                                    1
                                )
                            ) ?>

                        </div>


                        <div>

                            <h2>

                                <?= htmlspecialchars(
                                    $seller["bussinessName"]
                                ) ?>

                            </h2>

                            <p>

                                <?= htmlspecialchars(
                                    $seller["Name"]
                                ) ?>

                            </p>

                        </div>

                    </div>


                    <div class="seller-directory-rating">

                        <div class="directory-stars">

                            <?php for (
                                $i = 1;
                                $i <= 5;
                                $i++
                            ): ?>

                                <span
                                    class="<?= $i <= $full_stars
                                        ? "filled"
                                        : "empty" ?>"
                                >
                                    ★
                                </span>

                            <?php endfor; ?>

                        </div>


                        <span>

                            <?= number_format(
                                $rating,
                                1
                            ) ?>

                        </span>

                    </div>


                    <div class="seller-directory-details">

                        <p>

                            <strong>
                                Department:
                            </strong>

                            <?= htmlspecialchars(
                                $seller["department"]
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Products:
                            </strong>

                            <?= intval(
                                $seller["product_count"]
                            ) ?>

                        </p>

                    </div>


                    <a
                        href="profile.php?id=<?= $seller["sellerID"] ?>"
                        class="btn seller-view-button"
                    >
                        View Seller Profile →
                    </a>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


<?php

include "../../includes/footer.php";

?>