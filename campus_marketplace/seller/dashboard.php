<?php

require_once "../config/database.php";

require_once "../includes/seller_check.php";

include "../includes/header.php";


$seller_id =
    $_SESSION["user_id"];


$stmt = $pdo->prepare(
    "SELECT bussinessName
     FROM SELLER
     WHERE sellerID = ?"
);

$stmt->execute([
    $seller_id
]);

$seller =
    $stmt->fetch();

?>


<div class="seller-dashboard">

    <!-- Decorative academic elements -->
    <div class="academic-decoration decoration-top-left">
        <span>✦</span>
        <span>⌁</span>
    </div>

    <div class="academic-decoration decoration-bottom-right">
        <span>✦</span>
        <span>⌁</span>
    </div>


    <!-- Dashboard Introduction -->

    <div class="dashboard-intro">

        <p class="dashboard-label">
            SELLER DASHBOARD
        </p>

        <h1>
            Welcome back
        </h1>

        <h2>
            <?= htmlspecialchars(
                $seller["bussinessName"]
            ) ?>
        </h2>

        <div class="title-line"></div>

    </div>


    <!-- Dashboard Functions -->

    <div class="dashboard-grid">


        <!-- Product Management -->

        <a
            href="products/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ◈
            </div>

            <h3>
                Product Management
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Sales Announcements -->

        <a
            href="announcements/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ◇
            </div>

            <h3>
                Sales Announcements
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Reservations -->

        <a
            href="reservations/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ♢
            </div>

            <h3>
                Reservations
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Promotions -->

        <a
            href="promotions/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ✧
            </div>

            <h3>
                Promotions
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Product Launch -->

        <a
            href="launches/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ✦
            </div>

            <h3>
                Product Launch
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Chat -->

        <a
            href="chat/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ◌
            </div>

            <h3>
                Chat
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Customer Feedback -->

        <a
            href="feedback/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ♧
            </div>

            <h3>
                Customer Feedback
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Manage Profile -->

        <a
            href="profile/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ◉
            </div>

            <h3>
                Manage Profile
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


    </div>

</div>


<?php

include "../includes/footer.php";

?>