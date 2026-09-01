<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyerID = $_SESSION["user_id"];

$message = "";
$error = "";

if (isset($_GET["success"])) {
    $message = $_GET["success"];
}

if (isset($_GET["error"])) {
    $error = $_GET["error"];
}


/*
 * Get all upcoming product launches
 * from every seller.
 */

$stmt = $pdo->prepare("
    SELECT
        pl.*,
        s.bussinessName,
        s.Name AS SellerName
    FROM product_launch pl
    INNER JOIN seller s
        ON pl.sellerID = s.sellerID
    WHERE pl.Status = 'Upcoming'
    ORDER BY pl.LaunchDate ASC
");

$stmt->execute();

$launches = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Product Launches</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f3fb;
            color: #333;
        }

        .container {
            width: 92%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 8px;
            color: #5b3b82;
        }

        .page-header p {
            margin: 0;
            color: #777;
        }

        .message {
            background: #e7f6e7;
            color: #287328;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fde8e8;
            color: #a33;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .launch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
        }

        .launch-card {
            background: white;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 5px 18px rgba(91, 59, 130, 0.10);
            border-top: 5px solid #76529a;
        }

        .launch-card h2 {
            margin: 0 0 8px;
            color: #5b3b82;
        }

        .seller {
            color: #76529a;
            font-weight: bold;
            margin-bottom: 18px;
        }

        .description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .details {
            line-height: 1.9;
            margin-bottom: 18px;
        }

        .details strong {
            color: #5b3b82;
        }

        .progress-title {
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
            font-weight: bold;
            color: #5b3b82;
        }

        .progress {
            width: 100%;
            height: 11px;
            background: #eee5f7;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #76529a;
            border-radius: 10px;
        }

        .reserve-btn {
            display: block;
            width: 100%;
            text-align: center;
            background: #5b3b82;
            color: white;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }

        .reserve-btn:hover {
            background: #76529a;
        }

        .empty {
            background: white;
            padding: 50px;
            text-align: center;
            border-radius: 14px;
            color: #777;
            box-shadow: 0 5px 18px rgba(91, 59, 130, 0.08);
        }

    </style>

</head>

<body>

<div class="container">

    <div class="page-header">

        <h1>Product Launches</h1>

        <p>
            Explore upcoming products from campus sellers.
        </p>

    </div>


    <?php if ($message): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <?php if (count($launches) > 0): ?>

        <div class="launch-grid">

            <?php foreach ($launches as $launch): ?>

                <?php

                $current = (int)$launch["CurrentReservation"];
                $required = (int)$launch["RequiredReservations"];

                if ($required > 0) {

                    $percentage =
                        ($current / $required) * 100;

                } else {

                    $percentage = 0;

                }

                if ($percentage > 100) {
                    $percentage = 100;
                }

                ?>

                <div class="launch-card">

                    <h2>
                        <?= htmlspecialchars($launch["ProductName"]) ?>
                    </h2>


                    <div class="seller">

                        <?= htmlspecialchars(
                            $launch["bussinessName"]
                        ) ?>

                    </div>


                    <div class="description">

                        <?= nl2br(
                            htmlspecialchars(
                                $launch["Description"]
                            )
                        ) ?>

                    </div>


                    <div class="details">

                        <div>
                            <strong>Category:</strong>
                            <?= htmlspecialchars(
                                $launch["Category"]
                            ) ?>
                        </div>


                        <div>
                            <strong>Price:</strong>
                            ৳<?= number_format(
                                $launch["Price"],
                                2
                            ) ?>
                        </div>


                        <div>
                            <strong>Launch Date:</strong>
                            <?= date(
                                "d M Y",
                                strtotime(
                                    $launch["LaunchDate"]
                                )
                            ) ?>
                        </div>


                        <div>
                            <strong>Launch Time:</strong>
                            <?= date(
                                "h:i A",
                                strtotime(
                                    $launch["LaunchTime"]
                                )
                            ) ?>
                        </div>


                        <div>
                            <strong>Location:</strong>
                            <?= htmlspecialchars(
                                $launch["CampusLocation"]
                            ) ?>
                        </div>


                        <div>
                            <strong>Deadline:</strong>
                            <?= date(
                                "d M Y h:i A",
                                strtotime(
                                    $launch["Deadline"]
                                )
                            ) ?>
                        </div>

                    </div>


                    <div class="progress-title">

                        <span>
                            Confirmed
                        </span>

                        <span>
                            <?= $current ?> /
                            <?= $required ?>
                        </span>

                    </div>


                    <div class="progress">

                        <div
                            class="progress-bar"
                            style="width: <?= $percentage ?>%;"
                        ></div>

                    </div>


                    <a
                        href="reserve.php?id=<?= $launch["LaunchID"] ?>"
                        class="reserve-btn"
                    >
                        Confirm Product
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            No upcoming product launches available.

        </div>

    <?php endif; ?>

</div>

</body>

</html>