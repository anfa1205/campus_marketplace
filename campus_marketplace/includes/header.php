<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Campus Student Marketplace
    </title>

    <link
        rel="stylesheet"
        href="/campus_marketplace/assets/css/style.css"
    >

</head>

<body>

<header>

    <div class="navbar">

        <a
            href="/campus_marketplace/index.php"
            class="logo"
        >
            Campus Marketplace
        </a>


        <nav>

            <!-- Home -->
            <a
                href="/campus_marketplace/index.php"
            >
                Home
            </a>


            <?php if (isset($_SESSION["role"])): ?>


                <!-- ========================= -->
                <!-- SELLER NAVIGATION -->
                <!-- ========================= -->

                <?php if ($_SESSION["role"] === "seller"): ?>

                    <a
                        href="/campus_marketplace/seller/dashboard.php"
                    >
                        Dashboard
                    </a>

                <?php endif; ?>


                <!-- ========================= -->
                <!-- BUYER NAVIGATION -->
                <!-- ========================= -->

                <?php if ($_SESSION["role"] === "buyer"): ?>

                    <a
                        href="/campus_marketplace/buyer/products/index.php"
                    >
                        Products
                    </a>


                    <a
                        href="/campus_marketplace/buyer/announcements/index.php"
                    >
                        Announcements
                    </a>


                    <a
                        href="/campus_marketplace/buyer/sellers/index.php"
                    >
                        Sellers
                    </a>


                    <a
                        href="/campus_marketplace/buyer/promotions/index.php"
                    >
                        Promotions
                    </a>


                    <a
                        href="/campus_marketplace/buyer/launches/index.php"
                    >
                        Launches
                    </a>


                    <a
                        href="/campus_marketplace/buyer/reservations/index.php"
                    >
                        My Reservations
                    </a>


                    <a
                        href="/campus_marketplace/buyer/chat/index.php"
                    >
                        Chat
                    </a>


                    <a
                        href="/campus_marketplace/buyer/profile/index.php"
                    >
                        My Profile
                    </a>

                <?php endif; ?>


                <!-- Logout -->
                <a
                    href="/campus_marketplace/auth/logout.php"
                >
                    Logout
                </a>


            <?php else: ?>


                <!-- ========================= -->
                <!-- NOT LOGGED IN -->
                <!-- ========================= -->

                <a
                    href="/campus_marketplace/auth/login.php"
                >
                    Login
                </a>


                <a
                    href="/campus_marketplace/auth/register.php"
                >
                    Register
                </a>


            <?php endif; ?>

        </nav>

    </div>

</header>


<main class="container">