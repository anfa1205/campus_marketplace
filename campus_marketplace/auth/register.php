<?php

require_once "../config/database.php";

session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role = $_POST["role"] ?? "";

    $name = trim($_POST["name"] ?? "");

    $phone = trim($_POST["phone"] ?? "");

    $email = trim($_POST["email"] ?? "");

    $password = $_POST["password"] ?? "";


    if (
        $name === "" ||
        $phone === "" ||
        $email === "" ||
        $password === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif ($role === "seller") {

        $student_id =
            trim($_POST["student_id"] ?? "");

        $department =
            trim($_POST["department"] ?? "");

        $business_name =
            trim($_POST["business_name"] ?? "");


        if (
            $student_id === "" ||
            $department === "" ||
            $business_name === ""
        ) {

            $error =
                "Please fill in all seller information.";

        } else {

            $check = $pdo->prepare(
                "SELECT sellerID
                 FROM SELLER
                 WHERE StudentID = ?
                 OR Mail = ?"
            );

            $check->execute([
                $student_id,
                $email
            ]);


            if ($check->fetch()) {

                $error =
                    "Student ID or email already exists.";

            } else {

                $hashed_password =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                $stmt = $pdo->prepare(
                    "INSERT INTO SELLER
                    (
                        StudentID,
                        department,
                        Name,
                        Mail,
                        bussinessName,
                        Phone,
                        password
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );


                $stmt->execute([

                    $student_id,
                    $department,
                    $name,
                    $email,
                    $business_name,
                    $phone,
                    $hashed_password

                ]);


                $success =
                    "Seller registration successful.";

            }

        }

    } elseif ($role === "buyer") {

        $check = $pdo->prepare(
            "SELECT BuyerID
             FROM BUYER
             WHERE Email = ?"
        );

        $check->execute([
            $email
        ]);


        if ($check->fetch()) {

            $error =
                "Email already exists.";

        } else {

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $stmt = $pdo->prepare(
                "INSERT INTO BUYER
                (
                    Name,
                    Phone,
                    Email,
                    password
                )
                VALUES (?, ?, ?, ?)"
            );


            $stmt->execute([

                $name,
                $phone,
                $email,
                $hashed_password

            ]);


            $success =
                "Buyer registration successful.";

        }

    } else {

        $error =
            "Please select an account type.";

    }

}


include "../includes/header.php";

?>


<div class="card">

    <h1>Create Account</h1>

    <br>


    <?php if ($error !== ""): ?>

        <p>
            <?= htmlspecialchars($error) ?>
        </p>

        <br>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <p>
            <?= htmlspecialchars($success) ?>
        </p>

        <br>

    <?php endif; ?>


    <form method="POST">

        <label>
            Account Type
        </label>

        <select
            name="role"
            id="role"
            onchange="showSellerFields()"
            required
        >

            <option value="">
                Select Account Type
            </option>

            <option value="buyer">
                Buyer
            </option>

            <option value="seller">
                Seller
            </option>

        </select>


        <label>
            Name
        </label>

        <input
            type="text"
            name="name"
            required
        >


        <label>
            Phone
        </label>

        <input
            type="text"
            name="phone"
            required
        >


        <label>
            Email
        </label>

        <input
            type="email"
            name="email"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            required
        >


        <div
            id="sellerFields"
            style="display: none;"
        >

            <label>
                Student ID
            </label>

            <input
                type="text"
                name="student_id"
            >


            <label>
                Department
            </label>

            <input
                type="text"
                name="department"
            >


            <label>
                Business Name
            </label>

            <input
                type="text"
                name="business_name"
            >

        </div>


        <button
            type="submit"
            class="btn"
        >
            Register
        </button>

    </form>

</div>


<script>

function showSellerFields() {

    const role =
        document.getElementById("role").value;

    const sellerFields =
        document.getElementById("sellerFields");


    if (role === "seller") {

        sellerFields.style.display = "block";

    } else {

        sellerFields.style.display = "none";

    }

}

</script>


<?php

include "../includes/footer.php";

?>