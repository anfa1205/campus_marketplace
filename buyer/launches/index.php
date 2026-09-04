<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyerID =
    (int) $_SESSION["user_id"];


/*
 * GET ALL UPCOMING PRODUCT LAUNCHES
 *
 * IMPORTANT:
 * We use product_launch directly.
 *
 * We DO NOT require productID.
 *
 * This means a newly announced product
 * can be seen by buyers before it is
 * officially added to Product Management.
 */

$stmt = $pdo->prepare("
    SELECT
        pl.*,

        s.Name AS SellerName,
        s.bussinessName

    FROM product_launch pl

    INNER JOIN seller s
        ON pl.sellerID = s.sellerID

    WHERE pl.Status = 'Upcoming'

    ORDER BY
        pl.LaunchDate ASC,
        pl.LaunchTime ASC
");

$stmt->execute();

$launches =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


include "../../includes/header.php";

?>


<div class="seller-dashboard">


    <!-- DECORATIONS -->

    <div class="academic-decoration decoration-top-left">

        <span>✦</span>

        <span>⌁</span>

    </div>


    <div class="academic-decoration decoration-bottom-right">

        <span>✦</span>

        <span>⌁</span>

    </div>


    <!-- PAGE INTRO -->

    <div class="dashboard-intro">

        <p class="dashboard-label">
            CAMPUS MARKETPLACE
        </p>


        <h1>
            Product Launches
        </h1>


        <div class="title-line"></div>


        <p class="dashboard-description">
            Discover new products announced by student sellers and reserve before launch.
        </p>

    </div>


    <?php if (isset($_GET["success"])): ?>

        <div class="reservation-success">

            <span class="success-icon">
                ✓
            </span>

            <div>

                <strong>
                    Reservation Successful!
                </strong>

                <p>
                    <?= htmlspecialchars(
                        $_GET["success"]
                    ) ?>
                </p>

            </div>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET["error"])): ?>

        <div class="reservation-error">

            <span class="error-icon">
                !
            </span>

            <div>

                <strong>
                    Reservation Failed
                </strong>

                <p>
                    <?= htmlspecialchars(
                        $_GET["error"]
                    ) ?>
                </p>

            </div>

        </div>

    <?php endif; ?>


    <?php if (empty($launches)): ?>


        <div class="dashboard-card empty-card">

            <div class="card-icon">
                🚀
            </div>

            <h3>
                No Product Launches
            </h3>

            <p>
                There are currently no new product launches available.
            </p>

        </div>


    <?php else: ?>


        <div class="announcement-list">


            <?php foreach ($launches as $launch): ?>


                <?php

                $current =
                    (int) $launch["CurrentReservation"];

                $required =
                    (int) $launch["RequiredReservations"];


                if ($required > 0) {

                    $percentage =
                        ($current / $required) * 100;

                } else {

                    $percentage = 0;

                }


                if ($percentage > 100) {
                    $percentage = 100;
                }


                $remaining =
                    max(
                        0,
                        $required - $current
                    );


                $launchID =
                    (int) $launch["LaunchID"];


                /*
                 * Check whether this buyer
                 * already reserved this launch.
                 */

                $reserveStmt = $pdo->prepare("
                    SELECT Quantity
                    FROM reserves
                    WHERE BuyerID = ?
                    AND launchID = ?
                ");

                $reserveStmt->execute([
                    $buyerID,
                    $launchID
                ]);

                $myReservation =
                    $reserveStmt->fetchColumn();


                $myReservation =
                    $myReservation !== false
                    ? (int)$myReservation
                    : 0;

                ?>


                <div class="announcement-card">


                    <!-- HEADER -->

                    <div class="announcement-header">

                        <div>

                            <p class="dashboard-label">
                                NEW PRODUCT LAUNCH
                            </p>


                            <h2>
                                <?= htmlspecialchars(
                                    $launch["ProductName"]
                                ) ?>
                            </h2>


                            <p class="seller-name">

                                Seller:
                                <?= htmlspecialchars(
                                    $launch["SellerName"]
                                ) ?>

                                <?php if (
                                    !empty(
                                        $launch["bussinessName"]
                                    )
                                ): ?>

                                    -
                                    <?= htmlspecialchars(
                                        $launch["bussinessName"]
                                    ) ?>

                                <?php endif; ?>

                            </p>

                        </div>


                        <span class="status-badge">

                            <?= htmlspecialchars(
                                $launch["Status"]
                            ) ?>

                        </span>

                    </div>


                    <!-- PRODUCT INFORMATION -->

                    <div class="announcement-info">


                        <div class="info-box">

                            <strong>
                                📦 Product
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $launch["ProductName"]
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                🏷 Category
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $launch["Category"]
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                💰 Price
                            </strong>

                            <p>
                                ৳<?= number_format(
                                    (float)$launch["Price"],
                                    2
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                📝 Description
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $launch["Description"]
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                📅 Launch Date
                            </strong>

                            <p>
                                <?= date(
                                    "d M Y",
                                    strtotime(
                                        $launch["LaunchDate"]
                                    )
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                ⏰ Launch Time
                            </strong>

                            <p>
                                <?= date(
                                    "h:i A",
                                    strtotime(
                                        $launch["LaunchTime"]
                                    )
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                ⏳ Reservation Deadline
                            </strong>

                            <p>
                                <?= date(
                                    "d M Y h:i A",
                                    strtotime(
                                        $launch["Deadline"]
                                    )
                                ) ?>
                            </p>

                        </div>


                        <div class="info-box">

                            <strong>
                                📍 Campus Location
                            </strong>

                            <p>
                                <?= htmlspecialchars(
                                    $launch["CampusLocation"]
                                ) ?>
                            </p>

                        </div>


                    </div>


                    <!-- RESERVATION PROGRESS -->

                    <div style="
                        margin-top:25px;
                        padding:20px;
                        background:#f7f3fb;
                        border-radius:12px;
                    ">


                        <div style="
                            display:flex;
                            justify-content:space-between;
                            color:#5b3b82;
                            font-weight:bold;
                            margin-bottom:8px;
                        ">

                            <span>
                                Reservation Progress
                            </span>

                            <span>
                                <?= $current ?>
                                /
                                <?= $required ?>
                            </span>

                        </div>


                        <div style="
                            width:100%;
                            height:12px;
                            background:#eee5f7;
                            border-radius:10px;
                            overflow:hidden;
                        ">

                            <div style="
                                width:<?= $percentage ?>%;
                                height:100%;
                                background:#76529a;
                                border-radius:10px;
                            "></div>

                        </div>


                        <p style="
                            color:#666;
                            margin-bottom:0;
                        ">

                            <?php if ($remaining > 0): ?>

                                <?= $remaining ?>
                                more reservation(s)
                                needed to launch.

                            <?php else: ?>

                                Reservation target reached.
                                Waiting for seller to launch.

                            <?php endif; ?>

                        </p>

                    </div>


                    <!-- BUYER RESERVATION -->

                    <?php if ($myReservation > 0): ?>

                        <div style="
                            margin-top:20px;
                            background:#eee5f7;
                            color:#5b3b82;
                            padding:14px;
                            border-radius:10px;
                            font-weight:bold;
                        ">

                            You have reserved
                            <?= $myReservation ?>
                            item(s).

                        </div>

                    <?php endif; ?>


                    <?php if ($remaining > 0): ?>

                        <div style="margin-top:20px;">

                            <a
                                href="reserve.php?id=<?= $launchID ?>"
                                class="btn btn-primary"
                            >
                                Reserve Product
                            </a>

                        </div>

                    <?php else: ?>

                        <div style="
                            margin-top:20px;
                            color:#287328;
                            font-weight:bold;
                        ">

                            ✓ Reservation limit reached

                        </div>

                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


<?php include "../../includes/footer.php"; ?>