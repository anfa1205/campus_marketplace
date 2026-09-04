```php
<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyer_id =
    (int) $_SESSION["user_id"];

$reservation_id =
    (int) ($_GET["id"] ?? 0);


if ($reservation_id > 0) {

    $stmt = $pdo->prepare(
        "DELETE FROM reservation
         WHERE ReservationID = ?
         AND buyerID = ?
         AND Status IN
         (
             'Pending',
             'Accepted',
             'Ready for Pickup'
         )"
    );


    $stmt->execute([
        $reservation_id,
        $buyer_id
    ]);

}


header(
    "Location: index.php"
);

exit;

?>
```
