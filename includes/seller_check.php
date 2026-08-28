<?php

require_once "auth_check.php";

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "seller"
) {

    header(
        "Location: /campus_marketplace/index.php"
    );

    exit;

}

?>