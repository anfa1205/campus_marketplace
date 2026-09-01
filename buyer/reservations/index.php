```php
<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

include "../../includes/header.php";

$buyer_id = $_SESSION["user_id"];


/* Get buyer reservations */

$stmt = $pdo->prepare(
    "SELECT
        r.ReservationID,
        r.ReservationDate,
        r.ReservationTarget,
        r.Status,
        r.quantity,
        r.contactNumber,
        r.announcementID,
        p.ProductName,
        p.Price,
        s.bussinessName
     FROM reservation r
     INNER JOIN PRODUCT p
        ON r.productID = p.ProductID
     INNER JOIN SELLER s
        ON r.sellerID = s.sellerID
     WHERE r.buyerID = ?
     ORDER BY r.ReservationDate DESC"
);

$stmt->execute([
    $buyer_id
]);

$reservations = $stmt->fetchAll();

?>

<div class="card">

    <h1>
        My Reservations
    </h1>

    <?php if (count($reservations) === 0): ?>

        <p>
            You have no reservations yet.
        </p>

    <?php else: ?>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Seller
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Reservation Date
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($reservations as $reservation): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $reservation["ProductName"]
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation["bussinessName"]
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation["quantity"]
                            ) ?>
                        </td>


                        <td>
                            <?= number_format(
                                $reservation["Price"],
                                2
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation["ReservationDate"]
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $reservation["Status"]
                            ) ?>
                        </td>


                        <td>

                            <?php if (
                                strtolower(
                                    $reservation["Status"]
                                ) === "pending"
                            ): ?>

                                <a
                                    href="delete.php?id=<?= (int) $reservation["ReservationID"] ?>"
                                    onclick="return confirm('Are you sure you want to cancel this reservation?');"
                                    class="btn"
                                >
                                    Cancel
                                </a>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>


    <br>


    <a
        href="../dashboard.php"
        class="btn"
    >
        Back to Dashboard
    </a>

</div>


<?php

include "../../includes/footer.php";

?>
```
