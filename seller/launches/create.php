<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sellerID = $_SESSION["user_id"];

    $productName = trim($_POST["ProductName"] ?? "");
    $description = trim($_POST["Description"] ?? "");
    $category = trim($_POST["Category"] ?? "");
    $price = $_POST["Price"] ?? "";
    $required = $_POST["RequiredReservations"] ?? "";
    $launchDate = $_POST["LaunchDate"] ?? "";
    $launchTime = $_POST["LaunchTime"] ?? "";
    $deadline = $_POST["Deadline"] ?? "";
    $location = trim($_POST["CampusLocation"] ?? "");


    if (
        $productName === "" ||
        $category === "" ||
        $price === "" ||
        $required === "" ||
        $launchDate === "" ||
        $launchTime === "" ||
        $deadline === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } elseif (!filter_var($required, FILTER_VALIDATE_INT) || $required < 1) {

        $error = "Required reservation quantity must be at least 1.";

    } else {

        $launchDateTime = $launchDate . " " . $launchTime . ":00";

        $deadlineDateTime = date(
            "Y-m-d H:i:s",
            strtotime($deadline)
        );


        if (strtotime($deadlineDateTime) > strtotime($launchDateTime)) {

            $error = "Reservation deadline cannot be after the launch date and time.";

        } else {

            try {

                $stmt = $pdo->prepare("
                    INSERT INTO product_launch
                    (
                        ProductName,
                        Description,
                        Category,
                        Price,
                        LaunchDate,
                        LaunchTime,
                        CampusLocation,
                        Deadline,
                        Status,
                        RequiredReservations,
                        CurrentReservation,
                        sellerID,
                        productID
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?, ?, ?, ?,
                        'Upcoming',
                        ?, 0, ?, NULL
                    )
                ");

                $stmt->execute([
                    $productName,
                    $description,
                    $category,
                    $price,
                    $launchDateTime,
                    $launchTime . ":00",
                    $location,
                    $deadlineDateTime,
                    $required,
                    $sellerID
                ]);


                header(
                    "Location: index.php?success=" .
                    urlencode("Product launch created successfully.")
                );

                exit;

            } catch (PDOException $e) {

                $error = "Failed to create product launch.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Create Product Launch</title>

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
            max-width: 850px;
            margin: 40px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(91, 59, 130, 0.10);
        }

        h1 {
            color: #5b3b82;
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #5b3b82;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #d9cde6;
            border-radius: 7px;
            font-size: 14px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #76529a;
        }

        .error {
            background: #fde8e8;
            color: #a33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            padding: 11px 18px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .save {
            background: #5b3b82;
            color: white;
        }

        .cancel {
            background: #eee5f7;
            color: #5b3b82;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Create Product Launch</h1>


        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>Product Name *</label>

                <input
                    type="text"
                    name="ProductName"
                    required
                    value="<?= htmlspecialchars($_POST["ProductName"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea name="Description"><?= htmlspecialchars($_POST["Description"] ?? "") ?></textarea>

            </div>


            <div class="form-group">

                <label>Category *</label>

                <input
                    type="text"
                    name="Category"
                    required
                    value="<?= htmlspecialchars($_POST["Category"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Price *</label>

                <input
                    type="number"
                    name="Price"
                    step="0.01"
                    min="0"
                    required
                    value="<?= htmlspecialchars($_POST["Price"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Required Reservation Quantity *</label>

                <input
                    type="number"
                    name="RequiredReservations"
                    min="1"
                    required
                    value="<?= htmlspecialchars($_POST["RequiredReservations"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Launch Date *</label>

                <input
                    type="date"
                    name="LaunchDate"
                    required
                    value="<?= htmlspecialchars($_POST["LaunchDate"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Launch Time *</label>

                <input
                    type="time"
                    name="LaunchTime"
                    required
                    value="<?= htmlspecialchars($_POST["LaunchTime"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Reservation Deadline *</label>

                <input
                    type="datetime-local"
                    name="Deadline"
                    required
                    value="<?= htmlspecialchars($_POST["Deadline"] ?? "") ?>"
                >

            </div>


            <div class="form-group">

                <label>Campus Location</label>

                <input
                    type="text"
                    name="CampusLocation"
                    value="<?= htmlspecialchars($_POST["CampusLocation"] ?? "") ?>"
                >

            </div>


            <div class="buttons">

                <button
                    type="submit"
                    class="btn save"
                >
                    Create Product Launch
                </button>

                <a
                    href="index.php"
                    class="btn cancel"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>