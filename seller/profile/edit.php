<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$error = "";
$success = "";


/* =========================================================
   GET SELLER
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        sellerID,
        StudentID,
        department,
        Name,
        Mail,
        bussinessName,
        Phone
    FROM seller
    WHERE sellerID = ?
");

$stmt->execute([
    $sellerID
]);

$seller = $stmt->fetch();


if (!$seller) {

    die("Seller profile not found.");

}


/* =========================================================
   UPDATE PROFILE
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $name =
        trim(
            $_POST["Name"] ?? ""
        );

    $businessName =
        trim(
            $_POST["bussinessName"] ?? ""
        );

    $department =
        trim(
            $_POST["department"] ?? ""
        );

    $email =
        trim(
            $_POST["Mail"] ?? ""
        );

    $phone =
        trim(
            $_POST["Phone"] ?? ""
        );


    /* ---------------------------------------------
       VALIDATION
    --------------------------------------------- */

    if (
        $name === "" ||
        $businessName === "" ||
        $department === "" ||
        $email === "" ||
        $phone === ""
    ) {

        $error =
            "Please fill in all fields.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Please enter a valid email address.";

    } else {


        /* -----------------------------------------
           CHECK EMAIL
        ----------------------------------------- */

        $stmt = $pdo->prepare("
            SELECT sellerID

            FROM seller

            WHERE Mail = ?

              AND sellerID != ?

            LIMIT 1
        ");

        $stmt->execute([
            $email,
            $sellerID
        ]);

        $emailExists =
            $stmt->fetch();


        if ($emailExists) {

            $error =
                "This email address is already being used by another seller.";

        } else {


            /* -------------------------------------
               UPDATE
            ------------------------------------- */

            $stmt = $pdo->prepare("
                UPDATE seller

                SET
                    Name = ?,
                    bussinessName = ?,
                    department = ?,
                    Mail = ?,
                    Phone = ?

                WHERE sellerID = ?
            ");

            $stmt->execute([

                $name,

                $businessName,

                $department,

                $email,

                $phone,

                $sellerID

            ]);


            $success =
                "Profile updated successfully.";


            /* -------------------------------------
               REFRESH SELLER DATA
            ------------------------------------- */

            $stmt = $pdo->prepare("
                SELECT
                    sellerID,
                    StudentID,
                    department,
                    Name,
                    Mail,
                    bussinessName,
                    Phone
                FROM seller
                WHERE sellerID = ?
            ");

            $stmt->execute([
                $sellerID
            ]);

            $seller =
                $stmt->fetch();

        }

    }

}


include "../../includes/header.php";

?>


<div class="edit-profile-page">


    <div class="academic-decoration decoration-top-left">

        <span>✦</span>
        <span>⌁</span>

    </div>


    <div class="academic-decoration decoration-bottom-right">

        <span>✦</span>
        <span>⌁</span>

    </div>


    <!-- HEADER -->

    <div class="edit-profile-header">

        <p class="dashboard-label">
            MY PROFILE
        </p>

        <h1>
            Edit Profile
        </h1>

        <div class="title-line"></div>

        <p>
            Update your seller information.
        </p>

    </div>


    <!-- FORM CARD -->

    <div class="edit-profile-card">


        <?php if ($error !== ""): ?>

            <div class="edit-alert error">

                <?= htmlspecialchars(
                    $error
                ) ?>

            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="edit-alert success">

                <?= htmlspecialchars(
                    $success
                ) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
        >


            <!-- STUDENT ID -->

            <div class="edit-field">

                <label>
                    Student ID
                </label>

                <input
                    type="text"
                    value="<?= htmlspecialchars(
                        $seller["StudentID"]
                    ) ?>"
                    readonly
                >

                <small>
                    Student ID cannot be changed.
                </small>

            </div>


            <!-- NAME -->

            <div class="edit-field">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="Name"
                    value="<?= htmlspecialchars(
                        $seller["Name"]
                    ) ?>"
                    required
                >

            </div>


            <!-- BUSINESS -->

            <div class="edit-field">

                <label>
                    Business Name
                </label>

                <input
                    type="text"
                    name="bussinessName"
                    value="<?= htmlspecialchars(
                        $seller["bussinessName"]
                    ) ?>"
                    required
                >

            </div>


            <!-- DEPARTMENT -->

            <div class="edit-field">

                <label>
                    Department
                </label>

                <input
                    type="text"
                    name="department"
                    value="<?= htmlspecialchars(
                        $seller["department"]
                    ) ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="edit-field">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="Mail"
                    value="<?= htmlspecialchars(
                        $seller["Mail"]
                    ) ?>"
                    required
                >

            </div>


            <!-- PHONE -->

            <div class="edit-field">

                <label>
                    Phone
                </label>

                <input
                    type="text"
                    name="Phone"
                    value="<?= htmlspecialchars(
                        $seller["Phone"]
                    ) ?>"
                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="edit-profile-actions">

                <a
                    href="index.php"
                    class="cancel-profile-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="save-profile-button"
                >
                    Save Changes
                </button>

            </div>


        </form>


    </div>


</div>


<style>

.edit-profile-page {

    position: relative;

    min-height: calc(100vh - 180px);

    padding: 30px 10px 70px;

    overflow: hidden;

}


.edit-profile-header {

    position: relative;

    z-index: 2;

    text-align: center;

    margin-bottom: 30px;

}


.edit-profile-header h1 {

    color: var(--deep-purple);

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 36px;

    font-weight: 500;

}


.edit-profile-header p:last-child {

    color: var(--gray-text);

    font-size: 13px;

    margin-top: 10px;

}


.edit-profile-card {

    position: relative;

    z-index: 2;

    max-width: 700px;

    margin: 0 auto;

    padding: 35px;

    background: var(--white);

    border: 1px solid var(--border);

    border-radius: 18px;

    box-shadow:
        0 12px 35px rgba(70,54,83,.07);

}


.edit-field {

    margin-bottom: 20px;

}


.edit-field label {

    display: block;

    margin: 0 0 7px;

    color: var(--deep-purple);

    font-size: 12px;

    font-weight: 600;

}


.edit-field input {

    width: 100%;

    padding: 13px 15px;

    border: 1px solid var(--border);

    border-radius: 9px;

    background: #FDFCFE;

    color: var(--dark-text);

    font-size: 13px;

}


.edit-field input:focus {

    outline: none;

    border-color: var(--purple);

    box-shadow:
        0 0 0 3px rgba(112,87,127,.10);

}


.edit-field input[readonly] {

    background: var(--light-lavender);

    color: var(--gray-text);

    cursor: not-allowed;

}


.edit-field small {

    display: block;

    color: var(--light-text);

    font-size: 10px;

    margin-top: 5px;

}


.edit-alert {

    padding: 13px 16px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-size: 12px;

}


.edit-alert.error {

    background: #F6E9EB;

    border: 1px solid #E6CDD1;

    color: var(--danger);

}


.edit-alert.success {

    background: #E9F5EC;

    border: 1px solid #CBE2D0;

    color: #356B43;

}


.edit-profile-actions {

    display: flex;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 28px;

}


.cancel-profile-button {

    padding: 11px 20px;

    border-radius: 8px;

    border: 1px solid var(--border);

    color: var(--gray-text);

    font-size: 12px;

    font-weight: 600;

}


.cancel-profile-button:hover {

    background: var(--light-lavender);

}


.save-profile-button {

    padding: 11px 20px;

    border: none;

    border-radius: 8px;

    background: var(--deep-purple);

    color: var(--white);

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;

}


.save-profile-button:hover {

    background: var(--purple);

}


@media (max-width: 550px) {

    .edit-profile-card {

        padding: 25px 20px;

    }

    .edit-profile-actions {

        flex-direction: column-reverse;

    }

    .cancel-profile-button,
    .save-profile-button {

        text-align: center;

        width: 100%;

    }

}

</style>


<?php

include "../../includes/footer.php";

?>