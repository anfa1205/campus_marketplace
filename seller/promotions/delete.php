<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$promotionID =
    (int) ($_GET["id"] ?? 0);

if ($promotionID > 0) {

    $stmt = $pdo->prepare("
        DELETE FROM promotion
        WHERE PromotionId = ?
          AND SellerId = ?
    ");

    $stmt->execute([
        $promotionID,
        $sellerID
    ]);
}

header("Location: index.php?success=Offer deleted successfully.");
exit;