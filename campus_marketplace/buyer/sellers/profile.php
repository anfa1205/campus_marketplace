<?php

require_once "../../config/database.php";

include "../../includes/header.php";


$seller_id =
    isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


$stmt = $pdo->prepare(
    "SELECT
        sellerID,
        StudentID,
        department,
        Name,
        Mail,
        bussinessName,
        Phone
     FROM SELLER
     WHERE sellerID = ?"
);

$stmt->execute([
    $seller_id
]);

$seller = $stmt->fetch();


if (!$seller) {

    echo "<h2>Seller not found.</h2>";

    include "../../includes/footer.php";

    exit;

}


$stmt = $pdo->prepare(
    "SELECT
        ProductID,
        ProductName,
        price,
        stock
     FROM PRODUCT
     WHERE sellerID = ?"
);

$stmt->execute([
    $seller_id
]);

$products = $stmt->fetchAll();


?>


<h1>
    <?= htmlspecialchars(
        $seller["bussinessName"]
    ) ?>
</h1>

<br>


<div class="card">

    <h2>
        Seller Information
    </h2>

    <br>

    <p>
        Seller Name:
        <?= htmlspecialchars(
            $seller["Name"]
        ) ?>
    </p>

    <p>
        Department:
        <?= htmlspecialchars(
            $seller["department"]
        ) ?>
    </p>

    <p>
        Business:
        <?= htmlspecialchars(
            $seller["bussinessName"]
        ) ?>
    </p>

    <br>

    <a
        class="btn"
        href="../chat/index.php?seller_id=<?= $seller["sellerID"] ?>"
    >
        Chat with Seller
    </a>

</div>


<br><br>


<h2>
    Seller Products
</h2>

<br>


<div
    style="
        display:grid;
        grid-template-columns:
        repeat(3, 1fr);
        gap:20px;
    "
>

<?php foreach ($products as $product): ?>

    <div class="card">

        <h3>
            <?= htmlspecialchars(
                $product["ProductName"]
            ) ?>
        </h3>

        <br>

        <p>
            Price:
            ৳<?= htmlspecialchars(
                $product["price"]
            ) ?>
        </p>

        <p>
            Stock:
            <?= htmlspecialchars(
                $product["stock"]
            ) ?>
        </p>

    </div>

<?php endforeach; ?>

</div>


<?php

include "../../includes/footer.php";

?>