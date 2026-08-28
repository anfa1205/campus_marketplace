<?php

require_once "auth_check.php";

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "buyer"
) {

    header(
        "Location: /campus_marketplace/index.php"
    );

    exit;

}

?>