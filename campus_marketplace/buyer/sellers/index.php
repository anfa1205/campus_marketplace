<?php

require_once "../../config/database.php";

include "../../includes/header.php";


$stmt = $pdo->query(
    "SELECT
        sellerID,
        bussinessName,
        department
     FROM SELLER
     ORDER BY bussinessName"
);

$sellers = $stmt->fetchAll();

?>


<h1>
    Sellers
</h1>

<br>

<p>
    Browse sellers and view their public profiles.
</p>

<br>


<div
    style="
        display:grid;
        grid-template-columns:
        repeat(3, 1fr);
        gap:20px;
    "
>

<?php foreach ($sellers as $seller): ?>

    <div class="card">

        <h2>
            <?= htmlspecialchars(
                $seller["bussinessName"]
            ) ?>
        </h2>

        <br>

        <p>
            Department:
            <?= htmlspecialchars(
                $seller["department"]
            ) ?>
        </p>

        <br>

        <a
            class="btn"
            href="profile.php?id=<?= $seller["sellerID"] ?>"
        >
            View Seller Profile
        </a>

    </div>

<?php endforeach; ?>

</div>


<?php

include "../../includes/footer.php";

?>