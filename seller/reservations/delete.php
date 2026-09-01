```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$seller_id = $_SESSION["user_id"];

$reservation_id =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


if ($reservation_id > 0) {

    $stmt = $pdo->prepare(
        "DELETE FROM reservation
         WHERE ReservationID = ?
         AND sellerID = ?
         AND Status IN ('Rejected', 'Picked Up')"
    );

    $stmt->execute([
        $reservation_id,
        $seller_id
    ]);
}


header(
    "Location: index.php"
);

exit;

?>
```
