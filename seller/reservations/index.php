<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$sellerID = (int) $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT
        r.ReservationID,
        r.ReservationDate,
        r.ReservationTarget,
        r.Status,

        b.Name AS BuyerName,
        b.Email AS BuyerEmail,
        b.Phone AS BuyerPhone,

        p.ProductName,
        c.quantity AS Quantity,

        sa.SellingDate,
        sa.SellingTime,
        sa.CampusLocation

    FROM reservation r

    INNER JOIN buyer b
        ON r.buyerID = b.BuyerID

    INNER JOIN contains c
        ON r.ReservationID = c.reservationID

    INNER JOIN product p
        ON c.productID = p.ProductID

    INNER JOIN sales_announcement sa
        ON r.announcementID = sa.AnnouncementId

    WHERE r.sellerID = :sellerID

    ORDER BY r.ReservationID DESC
");

$stmt->execute([
    ":sellerID" => $sellerID
]);

$reservations = $stmt->fetchAll();

include "../../includes/header.php";

?>

<div class="seller-dashboard">

    <div class="academic-decoration decoration-top-left">
        <span>✦</span>
        <span>⌁</span>
    </div>

    <div class="academic-decoration decoration-bottom-right">
        <span>✦</span>
        <span>⌁</span>
    </div>


    <div class="dashboard-intro">

        <p class="dashboard-label">
            RESERVATION MANAGEMENT
        </p>

        <h1>Reservations</h1>

        <div class="title-line"></div>

        <p class="dashboard-description">
            View buyer reservations and manage pickup.
        </p>

    </div>


    <?php if (isset($_GET["success"])): ?>

        <div class="reservation-message success-message">
            <?= htmlspecialchars($_GET["success"]) ?>
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["error"])): ?>

        <div class="reservation-message error-message">
            <?= htmlspecialchars($_GET["error"]) ?>
        </div>

    <?php endif; ?>


    <?php if (empty($reservations)): ?>

        <div class="dashboard-card empty-card">

            <div class="card-icon">📋</div>

            <h3>No Reservations Yet</h3>

            <p>
                Reservations made by buyers will appear here.
            </p>

        </div>

    <?php else: ?>


        <div class="reservation-list">

            <?php foreach ($reservations as $reservation): ?>

                <div class="reservation-card">


                    <!-- RESERVATION HEADER -->

                    <div class="reservation-header">

                        <div>

                            <p class="dashboard-label">
                                RESERVATION #
                                <?= (int) $reservation["ReservationID"] ?>
                            </p>

                            <h2>
                                <?= htmlspecialchars(
                                    $reservation["ProductName"]
                                ) ?>
                            </h2>

                        </div>


                        <span class="status-badge">

                            <?= htmlspecialchars(
                                $reservation["Status"]
                            ) ?>

                        </span>

                    </div>


                    <!-- BUYER DETAILS -->

                    <div class="reservation-details">

                        <div class="detail-box">

                            <strong>Buyer</strong>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation["BuyerName"]
                                ) ?>
                            </p>

                        </div>


                        <div class="detail-box">

                            <strong>Email</strong>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation["BuyerEmail"]
                                ) ?>
                            </p>

                        </div>


                        <div class="detail-box">

                            <strong>Phone</strong>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation["BuyerPhone"]
                                ) ?>
                            </p>

                        </div>


                        <div class="detail-box">

                            <strong>Quantity</strong>

                            <p>
                                <?= (int) $reservation["Quantity"] ?>
                            </p>

                        </div>


                        <div class="detail-box">

                            <strong>Reserved On</strong>

                            <p>
                                <?= htmlspecialchars(
                                    $reservation["ReservationDate"]
                                ) ?>
                            </p>

                        </div>

                    </div>


                    <!-- PICKUP INFORMATION -->

                    <div class="pickup-section">

                        <h3>Pickup Information</h3>

                        <p>
                            <strong>Date:</strong>
                            <?= htmlspecialchars(
                                $reservation["SellingDate"]
                            ) ?>
                        </p>

                        <p>
                            <strong>Time:</strong>
                            <?= htmlspecialchars(
                                $reservation["SellingTime"]
                            ) ?>
                        </p>

                        <p>
                            <strong>Location:</strong>
                            <?= htmlspecialchars(
                                $reservation["CampusLocation"]
                            ) ?>
                        </p>

                    </div>


                    <!-- ACTIONS -->

                    <div class="reservation-actions">


                        <?php if (
                            $reservation["Status"] === "Pending"
                        ): ?>

                            <!-- ACCEPT -->

                            <form
                                method="POST"
                                action="update_status.php"
                            >

                                <input
                                    type="hidden"
                                    name="reservation_id"
                                    value="<?= (int) $reservation["ReservationID"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="status"
                                    value="Accepted"
                                >

                                <button
                                    type="submit"
                                    class="btn"
                                >
                                    Accept
                                </button>

                            </form>


                            <!-- REJECT -->

                            <form
                                method="POST"
                                action="update_status.php"
                            >

                                <input
                                    type="hidden"
                                    name="reservation_id"
                                    value="<?= (int) $reservation["ReservationID"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="status"
                                    value="Rejected"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                >
                                    Reject
                                </button>

                            </form>


                        <?php elseif (
                            $reservation["Status"] === "Accepted"
                        ): ?>

                            <!-- COMPLETE -->

                            <form
                                method="POST"
                                action="update_status.php"
                            >

                                <input
                                    type="hidden"
                                    name="reservation_id"
                                    value="<?= (int) $reservation["ReservationID"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="status"
                                    value="Completed"
                                >

                                <button
                                    type="submit"
                                    class="btn"
                                >
                                    Mark Completed
                                </button>

                            </form>

                            <p class="accepted-message">
                                ✓ Reservation accepted.
                                Buyer can collect the product at the
                                announced date, time and location.
                            </p>


                        <?php elseif (
                            $reservation["Status"] === "Completed"
                        ): ?>

                            <p class="completed-message">
                                ✓ Reservation completed.
                                Product has been collected.
                            </p>


                        <?php elseif (
                            $reservation["Status"] === "Rejected"
                        ): ?>

                            <p class="rejected-message">
                                ✕ Reservation rejected.
                            </p>

                        <?php endif; ?>


                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<style>

.reservation-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.reservation-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 8px 25px rgba(70, 54, 83, 0.08);
}

.reservation-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 24px;
}

.reservation-header h2 {
    color: var(--deep-purple);
    margin: 8px 0 0;
}

.reservation-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.detail-box {
    background: var(--light-lavender);
    padding: 15px;
    border-radius: 12px;
}

.detail-box strong {
    display: block;
    color: var(--purple);
    margin-bottom: 5px;
}

.detail-box p {
    margin: 0;
    color: var(--dark-text);
}

.pickup-section {
    margin-top: 22px;
    padding: 20px;
    background: var(--cream);
    border-left: 4px solid var(--gold);
    border-radius: 10px;
}

.pickup-section h3 {
    color: var(--deep-purple);
    margin-top: 0;
}

.pickup-section p {
    margin: 8px 0;
    color: var(--dark-text);
}

.reservation-actions {
    display: flex;
    gap: 12px;
    margin-top: 22px;
    flex-wrap: wrap;
    align-items: center;
}

.reservation-actions form {
    margin: 0;
}

.btn-danger {
    background: var(--danger) !important;
}

.accepted-message {
    color: var(--purple);
    font-weight: 600;
    margin: 0;
}

.completed-message {
    color: #356b43;
    font-weight: 600;
}

.rejected-message {
    color: var(--danger);
    font-weight: 600;
}

.reservation-message {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.success-message {
    background: #e9f5ec;
    color: #356b43;
}

.error-message {
    background: #f8e9eb;
    color: var(--danger);
}

.empty-card {
    text-align: center;
    padding: 50px;
}

@media (max-width: 700px) {

    .reservation-details {
        grid-template-columns: 1fr;
    }

    .reservation-header {
        flex-direction: column;
    }

}

</style>


<?php include "../../includes/footer.php"; ?>