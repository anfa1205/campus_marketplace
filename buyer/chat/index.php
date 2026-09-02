<?php

require_once "../../config/database.php";
require_once "../../includes/buyer_check.php";

$buyer_id = $_SESSION["user_id"];

$selected_seller_id = isset($_GET["seller_id"])
    ? intval($_GET["seller_id"])
    : 0;

$message_error = "";


/* =========================================================
   SEND MESSAGE
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $seller_id = intval($_POST["seller_id"] ?? 0);
    $text = trim($_POST["text"] ?? "");

    if ($seller_id <= 0 || $text === "") {

        $message_error = "Please enter a message.";

    } else {

        /* Check whether chat already exists */

        $stmt = $pdo->prepare(
            "SELECT ChatID
             FROM chat
             WHERE sellerID = ?
             AND buyerID = ?"
        );

        $stmt->execute([
            $seller_id,
            $buyer_id
        ]);

        $chat = $stmt->fetch();


        /* Create chat if it does not exist */

        if (!$chat) {

            $stmt = $pdo->prepare(
                "INSERT INTO chat
                (sellerID, buyerID)
                VALUES (?, ?)"
            );

            $stmt->execute([
                $seller_id,
                $buyer_id
            ]);

            $chat_id = $pdo->lastInsertId();

        } else {

            $chat_id = $chat["ChatID"];

        }


        /* Insert message */

        $stmt = $pdo->prepare(
            "INSERT INTO message
            (Text, SenderType, chatID)
            VALUES (?, 'Buyer', ?)"
        );

        $stmt->execute([
            $text,
            $chat_id
        ]);


        header(
            "Location: index.php?seller_id=" .
            $seller_id
        );

        exit;
    }
}


/* =========================================================
   GET BUYER'S CHATS
   ========================================================= */

$stmt = $pdo->prepare(
    "SELECT
        c.ChatID,
        c.sellerID,
        s.bussinessName,
        s.Name,

        (
            SELECT m.Text
            FROM message m
            WHERE m.chatID = c.ChatID
            ORDER BY m.timeStamp DESC, m.MessageID DESC
            LIMIT 1
        ) AS last_message,

        (
            SELECT m.timeStamp
            FROM message m
            WHERE m.chatID = c.ChatID
            ORDER BY m.timeStamp DESC, m.MessageID DESC
            LIMIT 1
        ) AS last_time

     FROM chat c

     INNER JOIN seller s
        ON c.sellerID = s.sellerID

     WHERE c.buyerID = ?

     ORDER BY
        COALESCE(last_time, '0000-00-00 00:00:00') DESC,
        c.ChatID DESC"
);

$stmt->execute([
    $buyer_id
]);

$chats = $stmt->fetchAll();


/* =========================================================
   GET SELECTED SELLER
   ========================================================= */

$selected_seller = null;

if ($selected_seller_id > 0) {

    $stmt = $pdo->prepare(
        "SELECT
            sellerID,
            Name,
            department,
            bussinessName
         FROM seller
         WHERE sellerID = ?"
    );

    $stmt->execute([
        $selected_seller_id
    ]);

    $selected_seller = $stmt->fetch();


    /* Make sure a chat exists */

    if ($selected_seller) {

        $stmt = $pdo->prepare(
            "SELECT ChatID
             FROM chat
             WHERE sellerID = ?
             AND buyerID = ?"
        );

        $stmt->execute([
            $selected_seller_id,
            $buyer_id
        ]);

        $existing_chat = $stmt->fetch();


        if (!$existing_chat) {

            $stmt = $pdo->prepare(
                "INSERT INTO chat
                (sellerID, buyerID)
                VALUES (?, ?)"
            );

            $stmt->execute([
                $selected_seller_id,
                $buyer_id
            ]);

            $selected_chat_id =
                $pdo->lastInsertId();

        } else {

            $selected_chat_id =
                $existing_chat["ChatID"];
        }


        /* Get messages */

        $stmt = $pdo->prepare(
            "SELECT
                MessageID,
                Text,
                SenderType,
                timeStamp
             FROM message
             WHERE chatID = ?
             ORDER BY timeStamp ASC, MessageID ASC"
        );

        $stmt->execute([
            $selected_chat_id
        ]);

        $messages = $stmt->fetchAll();

    } else {

        $messages = [];
    }

} else {

    $messages = [];
}


include "../../includes/header.php";

?>


<div class="chat-page">


    <!-- =====================================================
         PAGE TITLE
         ===================================================== -->

    <div class="chat-page-header">

        <p class="dashboard-label">
            BUYER CHAT
        </p>

        <h1>
            Messages
        </h1>

        <div class="title-line"></div>

    </div>


    <div class="chat-layout">


        <!-- =================================================
             CHAT LIST
             ================================================= -->

        <div class="chat-list-panel">

            <div class="chat-panel-title">
                Your Conversations
            </div>


            <?php if (empty($chats)): ?>

                <div class="empty-chat">
                    You have no conversations yet.
                </div>

            <?php else: ?>

                <?php foreach ($chats as $chat): ?>

                    <a
                        href="index.php?seller_id=<?= $chat["sellerID"] ?>"
                        class="chat-list-item
                        <?= ($selected_seller_id == $chat["sellerID"])
                            ? "active"
                            : "" ?>"
                    >

                        <div class="chat-avatar">
                            <?= strtoupper(
                                substr(
                                    $chat["bussinessName"],
                                    0,
                                    1
                                )
                            ) ?>
                        </div>


                        <div class="chat-list-info">

                            <h3>
                                <?= htmlspecialchars(
                                    $chat["bussinessName"]
                                ) ?>
                            </h3>

                            <p>
                                <?=
                                $chat["last_message"]
                                    ? htmlspecialchars(
                                        $chat["last_message"]
                                      )
                                    : "Start a conversation"
                                ?>
                            </p>

                        </div>

                    </a>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>


        <!-- =================================================
             CHAT WINDOW
             ================================================= -->

        <div class="chat-window">


            <?php if ($selected_seller): ?>


                <div class="chat-window-header">

                    <div>

                        <h2>
                            <?= htmlspecialchars(
                                $selected_seller["bussinessName"]
                            ) ?>
                        </h2>

                        <p>
                            <?= htmlspecialchars(
                                $selected_seller["Name"]
                            ) ?>

                            ·

                            <?= htmlspecialchars(
                                $selected_seller["department"]
                            ) ?>
                        </p>

                    </div>


                    <a
                        href="../sellers/profile.php?id=<?= $selected_seller["sellerID"] ?>"
                        class="chat-profile-link"
                    >
                        View Profile
                    </a>

                </div>


                <div class="messages-box">


                    <?php if (empty($messages)): ?>

                        <div class="empty-messages">

                            <div class="empty-message-icon">
                                💬
                            </div>

                            <h3>
                                Start a conversation
                            </h3>

                            <p>
                                Send a message to this seller.
                            </p>

                        </div>


                    <?php else: ?>


                        <?php foreach ($messages as $message): ?>


                            <div
                                class="message-row
                                <?= $message["SenderType"] === "Buyer"
                                    ? "buyer-message"
                                    : "seller-message" ?>"
                            >

                                <div class="message-bubble">

                                    <p>
                                        <?= nl2br(
                                            htmlspecialchars(
                                                $message["Text"]
                                            )
                                        ) ?>
                                    </p>

                                    <span class="message-time">
                                        <?= date(
                                            "d M, h:i A",
                                            strtotime(
                                                $message["timeStamp"]
                                            )
                                        ) ?>
                                    </span>

                                </div>

                            </div>


                        <?php endforeach; ?>


                    <?php endif; ?>


                </div>


                <?php if ($message_error): ?>

                    <div class="chat-error">
                        <?= htmlspecialchars($message_error) ?>
                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    class="chat-input-area"
                >

                    <input
                        type="hidden"
                        name="seller_id"
                        value="<?= $selected_seller["sellerID"] ?>"
                    >


                    <input
                        type="text"
                        name="text"
                        placeholder="Type your message..."
                        autocomplete="off"
                        required
                    >


                    <button
                        type="submit"
                        class="chat-send-button"
                    >
                        Send
                    </button>

                </form>


            <?php else: ?>


                <div class="no-chat-selected">

                    <div class="empty-message-icon">
                        💬
                    </div>

                    <h2>
                        Select a conversation
                    </h2>

                    <p>
                        Choose a seller from the left to start chatting.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>

</div>


<?php

include "../../includes/footer.php";

?>