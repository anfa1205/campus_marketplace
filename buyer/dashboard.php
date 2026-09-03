```php
<?php

require_once "../config/database.php";

require_once "../includes/buyer_check.php";

include "../includes/header.php";


$buyer_id =
    $_SESSION["user_id"];

$buyerName =
    $_SESSION["name"] ?? "Buyer";

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
            BUYER DASHBOARD
        </p>

        <h1>
            Welcome back
        </h1>

        <h2>
            <?= htmlspecialchars($buyerName) ?>
        </h2>

        <div class="title-line"></div>

    </div>


    <!-- Dashboard Functions -->

    <div class="dashboard-grid">


        <!-- Browse Products -->

        <a
            href="products/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ◈
            </div>

            <h3>
                Browse Products
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>

        <a
    href="launches/index.php"
    class="dashboard-card"
>

    <div class="card-icon">
        ✦
    </div>

    <h3>
        Product Launches
    </h3>

    <p>
        Discover upcoming products and reserve them before launch.
    </p>

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


        <!-- Seller Profiles & Chat -->

        <a
            href="sellers/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ♢
            </div>

            <h3>
                Seller Profiles & Chat
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
                ✧
            </div>

            <h3>
                My Reservations
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Offers -->

        <a
            href="promotions/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ✦
            </div>

            <h3>
                Offers
            </h3>

            <p>
               
            </p>

            <span class="card-arrow">
        →
            </span>

        </a>


        <!-- Product Launch Campaigns -->

        <a
            href="launches/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ◌
            </div>

            <h3>
                Product Launch Campaigns
            </h3>

            <span class="card-arrow">
                →
            </span>

        </a>


        <!-- Feedback & Rating -->

        <a
            href="feedback/index.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ♧
            </div>

            <h3>
                Feedback & Rating
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
```
