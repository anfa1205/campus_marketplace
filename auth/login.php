<?php

require_once "../config/database.php";

session_start();

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role =
        $_POST["role"] ?? "";

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    if (
        $role === "" ||
        $email === "" ||
        $password === ""
    ) {

        $error =
            "Please fill in all fields.";

    } elseif ($role === "seller") {

        $stmt = $pdo->prepare(
            "SELECT *
             FROM SELLER
             WHERE Mail = ?"
        );

        $stmt->execute([
            $email
        ]);

        $user = $stmt->fetch();


        if (
            $user &&
            password_verify(
                $password,
                $user["password"]
            )
        ) {

            $_SESSION["user_id"] =
                $user["sellerID"];

            $_SESSION["name"] =
                $user["Name"];

            $_SESSION["role"] =
                "seller";


            header(
                "Location: ../seller/dashboard.php"
            );

            exit;

        } else {

            $error =
                "Invalid email or password.";

        }

    } elseif ($role === "buyer") {

        $stmt = $pdo->prepare(
            "SELECT *
             FROM BUYER
             WHERE Email = ?"
        );

        $stmt->execute([
            $email
        ]);

        $user = $stmt->fetch();


        if (
            $user &&
            password_verify(
                $password,
                $user["password"]
            )
        ) {

            $_SESSION["user_id"] =
                $user["BuyerID"];

            $_SESSION["name"] =
                $user["Name"];

            $_SESSION["role"] =
                "buyer";


            header(
                "Location: ../buyer/dashboard.php"
            );

            exit;

        } else {

            $error =
                "Invalid email or password.";

        }

    }

}


include "../includes/header.php";

?>


<div class="card">

    <div class="login-icon">
        ✦
    </div>

    <h1>
        Welcome Back
    </h1>

    <p class="login-subtitle">
        Sign in to your Campus Marketplace account
    </p>


    <?php if ($error !== ""): ?>

        <p>
            <?= htmlspecialchars($error) ?>
        </p>

        <br>

    <?php endif; ?>


    <form method="POST">


        <label>
            Account Type
        </label>

        <select
            name="role"
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


        <button
            type="submit"
            class="btn"
        >
            Login
        </button>

    </form>

</div>


<?php

include "../includes/footer.php";

?>