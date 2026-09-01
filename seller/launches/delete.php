<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";


$sellerID = (int) $_SESSION["user_id"];

$launchID =
    isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


$stmt = $pdo->prepare(
    "SELECT LaunchID
     FROM product_launch
     WHERE LaunchID = ?
     AND sellerID = ?"
);

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


try {


    $pdo->beginTransaction();


    /* Delete buyer launch reservations */

    $stmt = $pdo->prepare(
        "DELETE FROM reserves
         WHERE launchID = ?"
    );

    $stmt->execute([
        $launchID
    ]);


    /* Delete launch */

    $stmt = $pdo->prepare(
        "DELETE FROM product_launch
         WHERE LaunchID = ?
         AND sellerID = ?"
    );

    $stmt->execute([
        $launchID,
        $sellerID
    ]);


    $pdo->commit();


    header(
        "Location: index.php?success=" .
        urlencode(
            "Product launch deleted successfully."
        )
    );

    exit;


} catch (Exception $e) {


    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    header(
        "Location: index.php?error=" .
        urlencode(
            "Unable to delete the product launch."
        )
    );

    exit;

}