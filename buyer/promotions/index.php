<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";
require_once "../../includes/offer_helper.php";


/*
 * IMPORTANT:
 *
 * Use CURRENT database time.
 *
 * An offer is Active when:
 *
 * StartDate <= NOW()
 * AND
 * EndDate >= NOW()
 */

$stmt = $pdo->query("
    SELECT
        pr.PromotionId,
        pr.OfferType,
        pr.DiscountValue,
        pr.BuyQuantity,
        pr.GetQuantity,
        pr.StartDate,
        pr.EndDate,

        s.sellerID,
        s.Name AS SellerName,
        s.bussinessName

    FROM promotion pr

    INNER JOIN seller s
        ON pr.SellerId = s.sellerID

    WHERE pr.StartDate <= NOW()
      AND pr.EndDate >= NOW()

    ORDER BY pr.EndDate ASC
");

$offers = $stmt->fetchAll();


include "../../includes/header.php";

?>


<div class="offers-page">


    <div class="offers-decoration offers-decoration-left">
        ✦
    </div>

    <div class="offers-decoration offers-decoration-right">
        ✧
    </div>


    <div class="offers-heading">

        <p class="page-label">
            CAMPUS MARKETPLACE
        </p>

        <h1>
            Special Offers
        </h1>

        <div class="title-line"></div>

        <p>
            Discover special offers from student sellers.
        </p>

    </div>


    <?php if (isset($_GET["success"])): ?>

        <div class="offer-message offer-success">

            <strong>
                ✓ Reservation Successful
            </strong>

            <span>
                <?= htmlspecialchars(
                    $_GET["success"]
                ) ?>
            </span>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET["error"])): ?>

        <div class="offer-message offer-error">

            <strong>
                ! Reservation Failed
            </strong>

            <span>
                <?= htmlspecialchars(
                    $_GET["error"]
                ) ?>
            </span>

        </div>

    <?php endif; ?>


    <?php if (empty($offers)): ?>


        <div class="offer-empty">

            <div class="offer-empty-icon">
                ✧
            </div>

            <h2>
                No Active Offers
            </h2>

            <p>
                There are currently no active offers.
            </p>

        </div>


    <?php else: ?>


        <div class="offers-grid">


            <?php foreach ($offers as $offer): ?>


                <?php

                $stmt = $pdo->prepare("
                    SELECT
                        p.ProductID,
                        p.ProductName,
                        p.Category,
                        p.Price,
                        p.Stock,
                        p.Status

                    FROM applies_to a

                    INNER JOIN product p
                        ON a.productID = p.ProductID

                    WHERE a.promotionID = ?

                    ORDER BY p.ProductName
                ");

                $stmt->execute([
                    $offer["PromotionId"]
                ]);

                $products =
                    $stmt->fetchAll();

                ?>


                <div class="offer-card">


                    <div class="offer-card-top">


                        <div class="offer-badge">

                            <?php if (
                                $offer["OfferType"]
                                === "Percentage"
                            ): ?>

                                <?= number_format(
                                    (float)
                                    $offer["DiscountValue"],
                                    0
                                ) ?>%

                                OFF

                            <?php else: ?>

                                BUY

                                <?= (int)
                                    $offer["BuyQuantity"] ?>

                                GET

                                <?= (int)
                                    $offer["GetQuantity"] ?>

                            <?php endif; ?>

                        </div>


                        <span class="offer-live">
                            ● LIVE
                        </span>

                    </div>


                    <div class="offer-card-title">

                        <?php if (
                            $offer["OfferType"]
                            === "Percentage"
                        ): ?>

                            <h2>
                                <?= number_format(
                                    (float)
                                    $offer["DiscountValue"],
                                    0
                                ) ?>% DISCOUNT
                            </h2>

                        <?php else: ?>

                            <h2>
                                BUY
                                <?= (int)
                                    $offer["BuyQuantity"] ?>

                                GET
                                <?= (int)
                                    $offer["GetQuantity"] ?>
                            </h2>

                        <?php endif; ?>


                        <p class="offer-business">

                            <?= htmlspecialchars(
                                $offer["bussinessName"]
                            ) ?>

                        </p>

                        <p class="offer-seller">

                            Seller:
                            <?= htmlspecialchars(
                                $offer["SellerName"]
                            ) ?>

                        </p>

                    </div>


                    <div class="offer-deadline">


                        <div>

                            <span>
                                STARTS
                            </span>

                            <strong>
                                <?= date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $offer["StartDate"]
                                    )
                                ) ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                ENDS
                            </span>

                            <strong>
                                <?= date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $offer["EndDate"]
                                    )
                                ) ?>
                            </strong>

                        </div>


                    </div>


                    <div class="offer-products">


                        <?php foreach (
                            $products
                            as $product
                        ): ?>


                            <?php

                            $basePrice =
                                (float)
                                $product["Price"];

                            $offerPrice =
                                getOfferPrice(
                                    $basePrice,
                                    $offer
                                );

                            $stock =
                                (int)
                                $product["Stock"];

                            $available =
                                $stock > 0 &&
                                strcasecmp(
                                    trim(
                                        $product["Status"]
                                    ),
                                    "Available"
                                ) === 0;

                            ?>


                            <div class="offer-product-card">


                                <div class="offer-product-details">

                                    <h3>

                                        <?= htmlspecialchars(
                                            $product["ProductName"]
                                        ) ?>

                                    </h3>


                                    <span>

                                        <?= htmlspecialchars(
                                            $product["Category"]
                                        ) ?>

                                    </span>


                                    <?php if (
                                        $offer["OfferType"]
                                        === "Percentage"
                                    ): ?>

                                        <div class="offer-price">

                                            <del>
                                                ৳<?= number_format(
                                                    $basePrice,
                                                    2
                                                ) ?>
                                            </del>

                                            <strong>
                                                ৳<?= number_format(
                                                    $offerPrice,
                                                    2
                                                ) ?>
                                            </strong>

                                        </div>

                                    <?php else: ?>

                                        <div class="offer-price">

                                            <strong>
                                                ৳<?= number_format(
                                                    $basePrice,
                                                    2
                                                ) ?>
                                            </strong>

                                            <span>
                                                per paid item
                                            </span>

                                        </div>

                                    <?php endif; ?>


                                    <small>

                                        Stock:
                                        <?= $stock ?>

                                    </small>

                                </div>


                                <?php if ($available): ?>


                                    <form
                                        method="POST"
                                        action="reserve.php"
                                        class="offer-reserve-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="promotion_id"
                                            value="<?= (int)
                                                $offer["PromotionId"] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int)
                                                $product["ProductID"] ?>"
                                        >


                                        <label>
                                            Quantity
                                        </label>


                                        <input
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            value="1"
                                            required
                                        >


                                        <?php if (
                                            $offer["OfferType"]
                                            === "BuyXGetY"
                                        ): ?>

                                            <small>

                                                Paid quantity.
                                                Free items are added
                                                automatically.

                                            </small>

                                        <?php endif; ?>


                                        <button
                                            type="submit"
                                            class="offer-reserve-button"
                                        >
                                            Reserve
                                        </button>

                                    </form>


                                <?php else: ?>


                                    <span class="offer-unavailable">
                                        Unavailable
                                    </span>


                                <?php endif; ?>


                            </div>


                        <?php endforeach; ?>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


<?php

include "../../includes/footer.php";

?>