<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

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

    $launchDate =
        trim($_POST["launchDate"] ?? "");

    $launchTime =
        trim($_POST["launchTime"] ?? "");

    $deadline =
        trim($_POST["deadline"] ?? "");

    $campusLocation =
        trim($_POST["campusLocation"] ?? "");

    $requiredReservations =
        (int) ($_POST["requiredReservations"] ?? 0);


    /*
     * VALIDATION
     */

    if (
        $productName === "" ||
        $description === "" ||
        $category === "" ||
        $price === "" ||
        $launchDate === "" ||
        $launchTime === "" ||
        $deadline === "" ||
        $campusLocation === "" ||
        $requiredReservations <= 0
    ) {

        $error =
            "Please fill in all required fields.";

    } elseif (
        !is_numeric($price) ||
        (float)$price < 0
    ) {

        $error =
            "Please enter a valid price.";

    } else {

        /*
         * Create launch datetime
         */

        $launchDateTime =
            $launchDate . " " . $launchTime . ":00";


        /*
         * Check launch date
         */

        if (
            strtotime($launchDateTime)
            <= time()
        ) {

            $error =
                "Launch date and time must be in the future.";

        }

        /*
         * Check reservation deadline
         */

        elseif (
            strtotime($deadline)
            <= time()
        ) {

            $error =
                "Reservation deadline must be in the future.";

        }

        /*
         * Deadline must be before launch
         */

        elseif (
            strtotime($deadline)
            >= strtotime($launchDateTime)
        ) {

            $error =
                "Reservation deadline must be before the launch date and time.";

        }

        else {

            try {

                /*
                 * Create a NEW product launch.
                 *
                 * productID remains NULL because
                 * the actual product will only be
                 * created after the reservation target
                 * is reached and seller launches it.
                 */

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
                    $campusLocation,
                    $deadline,
                    $requiredReservations,
                    $sellerID
                ]);


                header(
                    "Location: index.php?success=" .
                    urlencode(
                        "Product launch created successfully."
                    )
                );

                exit;


            } catch (PDOException $e) {

                $error =
                    "Unable to create product launch.";

            }

        }

    }

}

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
.form-group textarea,
.form-group select {
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

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #7b4f96;
}

.form-error {
    background: #f8e8ea;
    color: #9b4d59;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
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

.form-submit:hover {
    background: #65407d;
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
            Create New Product Launch
        </h1>

        <p>
            Announce a new product and collect reservations before launching it.
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
                    placeholder="Enter new product name"
                    value="<?= htmlspecialchars(
                        $_POST["productName"] ?? ""
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
                    placeholder="Enter product description"
                    required
                ><?= htmlspecialchars(
                    $_POST["description"] ?? ""
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
                    placeholder="Example: Drink, Food, Bakery"
                    value="<?= htmlspecialchars(
                        $_POST["category"] ?? ""
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
                    placeholder="Enter price"
                    value="<?= htmlspecialchars(
                        $_POST["price"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <!-- LAUNCH DATE + TIME -->

            <div class="form-row">

                <div class="form-group">

                    <label>
                        Launch Date
                    </label>

                    <input
                        type="date"
                        name="launchDate"
                        value="<?= htmlspecialchars(
                            $_POST["launchDate"] ?? ""
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
                            $_POST["launchTime"] ?? ""
                        ) ?>"
                        required
                    >

                </div>

            </div>


            <!-- RESERVATION DEADLINE -->

            <div class="form-group">

                <label>
                    Reservation Deadline
                </label>

                <input
                    type="datetime-local"
                    name="deadline"
                    value="<?= htmlspecialchars(
                        $_POST["deadline"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <!-- CAMPUS LOCATION -->

            <div class="form-group">

                <label>
                    Campus Location
                </label>

                <input
                    type="text"
                    name="campusLocation"
                    placeholder="Example: Cafeteria 6th floor pillar 04"
                    value="<?= htmlspecialchars(
                        $_POST["campusLocation"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <!-- REQUIRED RESERVATIONS -->

            <div class="form-group">

                <label>
                    Required Reservation Quantity
                </label>

                <input
                    type="number"
                    name="requiredReservations"
                    min="1"
                    placeholder="Example: 20"
                    value="<?= htmlspecialchars(
                        $_POST["requiredReservations"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="form-submit"
                >
                    Create Product Launch
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