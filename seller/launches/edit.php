<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$launchID =
    isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


$stmt = $pdo->prepare("
    SELECT *
    FROM product_launch
    WHERE LaunchID = ?
    AND sellerID = ?
");

$stmt->execute([
    $launchID,
    $sellerID
]);

$launch = $stmt->fetch();


if (!$launch) {

    header(
        "Location: index.php?error=" .
        urlencode("Product launch not found.")
    );

    exit;

}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $productName =
        trim($_POST["productName"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $category =
        trim($_POST["category"] ?? "");

    $price =
        trim($_POST["price"] ?? "");

    $required =
        (int) ($_POST["requiredReservations"] ?? 0);

    $launchDate =
        trim($_POST["launchDate"] ?? "");

    $launchTime =
        trim($_POST["launchTime"] ?? "");

    $deadline =
        trim($_POST["deadline"] ?? "");

    $location =
        trim($_POST["campusLocation"] ?? "");


    $currentReservation =
        (int) $launch["CurrentReservation"];


    if (
        $productName === "" ||
        $description === "" ||
        $category === "" ||
        $price === "" ||
        $required <= 0 ||
        $launchDate === "" ||
        $launchTime === "" ||
        $deadline === "" ||
        $location === ""
    ) {

        $error =
            "Please fill in all fields correctly.";

    }

    elseif (
        !is_numeric($price) ||
        (float)$price < 0
    ) {

        $error =
            "Please enter a valid price.";

    }

    elseif (
        $required < $currentReservation
    ) {

        $error =
            "Required reservation quantity cannot be smaller than the current reservations.";

    }

    else {

        $launchDateTime =
            $launchDate . " " . $launchTime . ":00";


        if (
            strtotime($deadline) <= time()
        ) {

            $error =
                "Reservation deadline must be in the future.";

        }

        elseif (
            strtotime($launchDateTime) <= time()
        ) {

            $error =
                "Launch date and time must be in the future.";

        }

        elseif (
            strtotime($deadline)
            >= strtotime($launchDateTime)
        ) {

            $error =
                "Reservation deadline must be before the launch date and time.";

        }

        else {

            $stmt = $pdo->prepare("
                UPDATE product_launch

                SET
                    ProductName = ?,
                    Description = ?,
                    Category = ?,
                    Price = ?,
                    RequiredReservations = ?,
                    LaunchDate = ?,
                    LaunchTime = ?,
                    Deadline = ?,
                    CampusLocation = ?

                WHERE LaunchID = ?
                AND sellerID = ?
            ");


            $stmt->execute([
                $productName,
                $description,
                $category,
                $price,
                $required,
                $launchDateTime,
                $launchTime . ":00",
                $deadline,
                $location,
                $launchID,
                $sellerID
            ]);


            header(
                "Location: index.php?success=" .
                urlencode(
                    "Product launch updated successfully."
                )
            );

            exit;

        }

    }

}


$launchDateValue =
    date(
        "Y-m-d",
        strtotime(
            $launch["LaunchDate"]
        )
    );


$launchTimeValue =
    date(
        "H:i",
        strtotime(
            $launch["LaunchTime"]
        )
    );


$deadlineValue =
    date(
        "Y-m-d\TH:i",
        strtotime(
            $launch["Deadline"]
        )
    );


include "../../includes/header.php";

?>


<style>

.launch-form-page {
    max-width: 850px;
    margin: 0 auto;
}

.launch-form-header {
    margin-bottom: 30px;
}

.launch-form-label {
    color: #8b5aa8;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 2px;
}

.launch-form-header h1 {
    color: #4a315d;
    margin: 7px 0;
}

.launch-form-header p {
    color: #777;
}

.launch-form-card {
    background: white;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(83,52,103,.10);
    border: 1px solid #eee5f3;
}

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    color: #4a315d;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 13px 14px;
    border: 1px solid #ddd2e4;
    border-radius: 10px;
    font-size: 15px;
    font-family: Arial, sans-serif;
}

.form-group textarea {
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-error {
    background: #f8e8ea;
    color: #9b4d59;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.form-actions {
    display: flex;
    gap: 12px;
}

.form-submit {
    border: none;
    background: #7b4f96;
    color: white;
    padding: 13px 23px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
}

.form-cancel {
    background: #eee5f5;
    color: #70478a;
    padding: 13px 23px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
}

@media(max-width:650px) {

    .form-row {
        grid-template-columns: 1fr;
    }

}

</style>


<div class="launch-form-page">


    <div class="launch-form-header">

        <span class="launch-form-label">
            SELLER AREA
        </span>

        <h1>
            Edit Product Launch
        </h1>

        <p>
            Update the details of your upcoming product launch.
        </p>

    </div>


    <div class="launch-form-card">


        <?php if ($error !== ""): ?>

            <div class="form-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- PRODUCT NAME -->

            <div class="form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="productName"
                    value="<?= htmlspecialchars(
                        $_POST["productName"]
                        ?? $launch["ProductName"]
                    ) ?>"
                    required
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    rows="4"
                    required
                ><?= htmlspecialchars(
                    $_POST["description"]
                    ?? $launch["Description"]
                ) ?></textarea>

            </div>


            <!-- CATEGORY -->

            <div class="form-group">

                <label>
                    Category
                </label>

                <input
                    type="text"
                    name="category"
                    value="<?= htmlspecialchars(
                        $_POST["category"]
                        ?? $launch["Category"]
                    ) ?>"
                    required
                >

            </div>


            <!-- PRICE -->

            <div class="form-group">

                <label>
                    Price
                </label>

                <input
                    type="number"
                    name="price"
                    min="0"
                    step="0.01"
                    value="<?= htmlspecialchars(
                        $_POST["price"]
                        ?? $launch["Price"]
                    ) ?>"
                    required
                >

            </div>


            <!-- RESERVATION QUANTITY -->

            <div class="form-group">

                <label>
                    Required Reservation Quantity
                </label>

                <input
                    type="number"
                    name="requiredReservations"
                    min="<?= max(1, $currentReservation) ?>"
                    value="<?= htmlspecialchars(
                        $_POST["requiredReservations"]
                        ?? $launch["RequiredReservations"]
                    ) ?>"
                    required
                >

            </div>


            <!-- DATE + TIME -->

            <div class="form-row">


                <div class="form-group">

                    <label>
                        Launch Date
                    </label>

                    <input
                        type="date"
                        name="launchDate"
                        value="<?= htmlspecialchars(
                            $_POST["launchDate"]
                            ?? $launchDateValue
                        ) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Launch Time
                    </label>

                    <input
                        type="time"
                        name="launchTime"
                        value="<?= htmlspecialchars(
                            $_POST["launchTime"]
                            ?? $launchTimeValue
                        ) ?>"
                        required
                    >

                </div>


            </div>


            <!-- DEADLINE -->

            <div class="form-group">

                <label>
                    Reservation Deadline
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    value="<?= htmlspecialchars(
                        $_POST["deadline"]
                        ?? $deadlineValue
                    ) ?>"
                    required
                >

            </div>


            <!-- LOCATION -->

            <div class="form-group">

                <label>
                    Campus Location
                </label>

                <input
                    type="text"
                    name="campusLocation"
                    value="<?= htmlspecialchars(
                        $_POST["campusLocation"]
                        ?? $launch["CampusLocation"]
                    ) ?>"
                    required
                >

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="form-submit"
                >
                    Save Changes
                </button>


                <a
                    href="index.php"
                    class="form-cancel"
                >
                    Cancel
                </a>

            </div>


        </form>


    </div>


</div>


<?php include "../../includes/footer.php"; ?>