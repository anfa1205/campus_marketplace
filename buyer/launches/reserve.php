<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyerID = $_SESSION["user_id"];

$launchID = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;

if ($launchID <= 0) {

    header(
        "Location: index.php?error=" .
        urlencode("Invalid product launch.")
    );

    exit;
}


/*
 * Get launch details.
 */

$stmt = $pdo->prepare("
    SELECT
        pl.*,
        s.bussinessName,
        s.Name AS SellerName
    FROM product_launch pl
    INNER JOIN seller s
        ON pl.sellerID = s.sellerID
    WHERE pl.LaunchID = ?
    AND pl.Status = 'Upcoming'
");

$stmt->execute([
    $launchID
]);

$launch = $stmt->fetch();


if (!$launch) {

    header(
        "Location: index.php?error=" .
        urlencode("Product launch is no longer available.")
    );

    exit;
}


$error = "";


/*
 * Check whether this buyer
 * already confirmed this launch.
 */

$stmt = $pdo->prepare("
    SELECT Quantity
    FROM reserves
    WHERE BuyerID = ?
    AND launchID = ?
");

$stmt->execute([
    $buyerID,
    $launchID
]);

$existing = $stmt->fetch();

$existingQuantity = $existing
    ? (int)$existing["Quantity"]
    : 0;


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $quantity = $_POST["Quantity"] ?? "";


    if (
        !filter_var(
            $quantity,
            FILTER_VALIDATE_INT
        ) ||
        $quantity < 1
    ) {

        $error =
            "Please enter a valid quantity.";

    } else {

        try {

            $pdo->beginTransaction();


            /*
             * Lock the launch row.
             */

            $stmt = $pdo->prepare("
                SELECT *
                FROM product_launch
                WHERE LaunchID = ?
                AND Status = 'Upcoming'
                FOR UPDATE
            ");

            $stmt->execute([
                $launchID
            ]);

            $lockedLaunch = $stmt->fetch();


            if (!$lockedLaunch) {

                throw new Exception(
                    "This product launch is no longer available."
                );

            }


            /*
 * Check deadline.
 */

if (
    strtotime($lockedLaunch["Deadline"])
    < time()
) {

    throw new Exception(
        "The confirmation deadline has passed."
    );

}


/*
 * Check reservation limit.
 *
 * The buyer cannot reserve if the launch
 * has already reached its required reservation limit.
 */

$requiredReservations = (int) (
    $lockedLaunch["RequiredReservations"]
    ?? $lockedLaunch["Capacity"]
    ?? 0
);

$currentReservation = (int) (
    $lockedLaunch["CurrentReservation"]
    ?? 0
);

if (
    $requiredReservations > 0 &&
    $currentReservation >= $requiredReservations
) {

    throw new Exception(
        "This product launch has reached its reservation limit."
    );

}


            /*
             * Check existing buyer record.
             */

            $stmt = $pdo->prepare("
                SELECT Quantity
                FROM reserves
                WHERE BuyerID = ?
                AND launchID = ?
                FOR UPDATE
            ");

            $stmt->execute([
                $buyerID,
                $launchID
            ]);

            $existingRow = $stmt->fetch();


            if ($existingRow) {

                /*
                 * Add the new quantity
                 * to the buyer's previous quantity.
                 */

                $newQuantity =
                    (int)$existingRow["Quantity"]
                    + (int)$quantity;


                $stmt = $pdo->prepare("
                    UPDATE reserves
                    SET Quantity = ?
                    WHERE BuyerID = ?
                    AND launchID = ?
                ");

                $stmt->execute([
                    $newQuantity,
                    $buyerID,
                    $launchID
                ]);

            } else {

                /*
                 * First confirmation.
                 */

                $stmt = $pdo->prepare("
                    INSERT INTO reserves
                    (
                        BuyerID,
                        launchID,
                        Quantity
                    )
                    VALUES
                    (?, ?, ?)
                ");

                $stmt->execute([
                    $buyerID,
                    $launchID,
                    $quantity
                ]);

            }


            /*
             * Calculate total confirmed quantity
             * from all buyers.
             */

            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(Quantity), 0) AS total
                FROM reserves
                WHERE launchID = ?
            ");

            $stmt->execute([
                $launchID
            ]);

            $result = $stmt->fetch();

            $total =
                (int)$result["total"];


            /*
             * Update live total.
             */

            $stmt = $pdo->prepare("
                UPDATE product_launch
                SET CurrentReservation = ?
                WHERE LaunchID = ?
            ");

            $stmt->execute([
                $total,
                $launchID
            ]);


            $pdo->commit();


            header(
                "Location: index.php?success=" .
                urlencode(
                    "Your product confirmation was recorded successfully."
                )
            );

            exit;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();

            }

            $error = $e->getMessage();

        }

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Confirm Product</title>

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
            max-width: 600px;
            margin: 50px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(91, 59, 130, 0.10);
        }

        h1 {
            margin-top: 0;
            color: #5b3b82;
        }

        .seller {
            color: #76529a;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .details {
            line-height: 1.9;
            margin-bottom: 25px;
        }

        .details strong {
            color: #5b3b82;
        }

        .confirmation-box {
            background: #f7f3fb;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #5b3b82;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d9cde6;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #76529a;
        }

        .btn {
            width: 100%;
            border: none;
            padding: 12px;
            border-radius: 8px;
            background: #5b3b82;
            color: white;
            font-weight: bold;
            cursor: pointer;
            font-size: 15px;
        }

        .btn:hover {
            background: #76529a;
        }

        .cancel {
            display: block;
            text-align: center;
            margin-top: 12px;
            padding: 11px;
            border-radius: 8px;
            background: #eee5f7;
            color: #5b3b82;
            text-decoration: none;
            font-weight: bold;
        }

        .error {
            background: #fde8e8;
            color: #a33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .already {
            background: #eee5f7;
            color: #5b3b82;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>
            Confirm Product
        </h1>


        <div class="seller">

            <?= htmlspecialchars(
                $launch["bussinessName"]
            ) ?>

        </div>


        <div class="details">

            <div>
                <strong>Product:</strong>
                <?= htmlspecialchars(
                    $launch["ProductName"]
                ) ?>
            </div>


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
                <strong>Confirmation Deadline:</strong>
                <?= date(
                    "d M Y h:i A",
                    strtotime(
                        $launch["Deadline"]
                    )
                ) ?>
            </div>


            <div>
                <strong>Current Confirmations:</strong>
                <?= (int)$launch["CurrentReservation"] ?>
                /
                <?= (int)$launch["RequiredReservations"] ?>
            </div>

        </div>


        <?php if ($error): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($existingQuantity > 0): ?>

            <div class="already">

                You have already confirmed
                <?= $existingQuantity ?>
                unit(s) of this product.

            </div>

        <?php endif; ?>


        <div class="confirmation-box">

            <form method="POST">

                <label>
                    Quantity
                </label>

                <input
                    type="number"
                    name="Quantity"
                    min="1"
                    value="1"
                    required
                >

                <button
                    type="submit"
                    class="btn"
                >
                    Confirm Product
                </button>

            </form>

        </div>


        <a
            href="index.php"
            class="cancel"
        >
            Back to Product Launches
        </a>

    </div>

</div>

</body>

</html>