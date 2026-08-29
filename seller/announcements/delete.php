```php
<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$seller_id = $_SESSION["user_id"];

$announcement_id = $_GET["id"] ?? 0;


/* Delete announcement belonging to this seller */

$stmt = $pdo->prepare(
    "DELETE FROM sales_announcement
     WHERE AnnouncementId = ?
     AND SellerId = ?"
);

$stmt->execute([
    $announcement_id,
    $seller_id
]);


/* Return to announcement list */

header("Location: index.php");
exit;

?>
```
