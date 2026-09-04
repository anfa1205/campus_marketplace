<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$message = "";
$error = "";

if (isset($_GET["success"])) {
    $message = $_GET["success"];
}

if (isset($_GET["error"])) {
    $error = $_GET["error"];
}


/*
 * Get all launches created by this seller.
 */

$stmt = $pdo->prepare("
    SELECT *
    FROM product_launch
    WHERE sellerID = ?
    ORDER BY LaunchDate ASC
");

$stmt->execute([
    $sellerID
]);

$launches = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Product Launch</title>

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
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }

        h1 {
            color: #5b3b82;
            margin: 0;
        }

        .add-btn {
            background: #5b3b82;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .add-btn:hover {
            background: #76529a;
        }

        .message {
            background: #e7f6e7;
            color: #287328;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fde8e8;
            color: #a33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .launch-card {
            background: white;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 18px rgba(91, 59, 130, 0.10);
            border-left: 5px solid #76529a;
        }

        .launch-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .launch-header h2 {
            margin: 0;
            color: #5b3b82;
        }

        .status {
            padding: 7px 13px;
            border-radius: 20px;
            background: #eee5f7;
            color: #5b3b82;
            font-weight: bold;
            font-size: 13px;
        }

        .details {
            margin-top: 18px;
            line-height: 1.8;
        }

        .details strong {
            color: #5b3b82;
        }

        .progress-box {
            margin-top: 20px;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            color: #5b3b82;
            margin-bottom: 8px;
        }

        .progress {
            width: 100%;
            height: 12px;
            background: #eee5f7;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #76529a;
            border-radius: 10px;
        }

        .actions {
            margin-top: 22px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .edit {
            background: #eee5f7;
            color: #5b3b82;
        }

        .delete {
            background: #f8dddd;
            color: #a33;
        }

        .launch {
            background: #5b3b82;
            color: white;
        }

        .waiting {
            color: #777;
            font-weight: bold;
        }

        .launched {
            color: #287328;
            font-weight: bold;
        }

        .empty {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 14px;
            color: #777;
        }

        @media(max-width:650px) {

            .top {
                flex-direction: column;
                align-items: flex-start;
            }

        }

    </style>

</head>


<body>

<div class="container">


    <div class="top">

        <h1>
            Product Launch
        </h1>

        <a
            href="create.php"
            class="add-btn"
        >
            + Create New Product
        </a>

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


    <?php if (empty($launches)): ?>

        <div class="empty">
            No Product Launches Yet
        </div>

    <?php endif; ?>


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


        $targetReached =
            $current >= $required;

        ?>


        <div class="launch-card">


            <div class="launch-header">

                <h2>
                    <?= htmlspecialchars(
                        $launch["ProductName"]
                    ) ?>
                </h2>


                <span class="status">

                    <?= htmlspecialchars(
                        $launch["Status"]
                    ) ?>

                </span>

            </div>


            <div class="details">


                <div>

                    <strong>
                        Category:
                    </strong>

                    <?= htmlspecialchars(
                        $launch["Category"]
                    ) ?>

                </div>


                <div>

                    <strong>
                        Price:
                    </strong>

                    ৳<?= number_format(
                        (float)$launch["Price"],
                        2
                    ) ?>

                </div>


                <div>

                    <strong>
                        Description:
                    </strong>

                    <?= htmlspecialchars(
                        $launch["Description"]
                    ) ?>

                </div>


                <div>

                    <strong>
                        Launch Date:
                    </strong>

                    <?= date(
                        "d M Y",
                        strtotime(
                            $launch["LaunchDate"]
                        )
                    ) ?>

                </div>


                <div>

                    <strong>
                        Launch Time:
                    </strong>

                    <?= date(
                        "h:i A",
                        strtotime(
                            $launch["LaunchTime"]
                        )
                    ) ?>

                </div>


                <div>

                    <strong>
                        Reservation Deadline:
                    </strong>

                    <?= date(
                        "d M Y h:i A",
                        strtotime(
                            $launch["Deadline"]
                        )
                    ) ?>

                </div>


                <div>

                    <strong>
                        Campus Location:
                    </strong>

                    <?= htmlspecialchars(
                        $launch["CampusLocation"]
                    ) ?>

                </div>


            </div>


            <div class="progress-box">


                <div class="progress-text">

                    <span>
                        Reservations
                    </span>

                    <span>
                        <?= $current ?>
                        /
                        <?= $required ?>
                    </span>

                </div>


                <div class="progress">

                    <div
                        class="progress-bar"
                        style="width: <?= $percentage ?>%;"
                    ></div>

                </div>


            </div>


            <div class="actions">


                <?php if ($launch["Status"] === "Upcoming"): ?>


                    <a
                        href="edit.php?id=<?= (int)$launch["LaunchID"] ?>"
                        class="btn edit"
                    >
                        Edit
                    </a>


                    <a
                        href="delete.php?id=<?= (int)$launch["LaunchID"] ?>"
                        class="btn delete"
                        onclick="return confirm('Delete this product launch?');"
                    >
                        Delete
                    </a>


                    <?php if ($targetReached): ?>

                        <a
                            href="launch.php?id=<?= (int)$launch["LaunchID"] ?>"
                            class="btn launch"
                            onclick="return confirm('Confirm and launch this product?');"
                        >
                            Launch Product
                        </a>

                    <?php else: ?>

                        <span class="waiting">

                            Need
                            <?= $required - $current ?>
                            more reservation(s)

                        </span>

                    <?php endif; ?>


                <?php elseif ($launch["Status"] === "Launched"): ?>


                    <span class="launched">
                        Product Launched
                    </span>


                <?php endif; ?>


            </div>


        </div>


    <?php endforeach; ?>


</div>

</body>

</html>