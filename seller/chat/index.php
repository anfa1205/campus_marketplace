<?php

require_once "../../config/database.php";
require_once "../../includes/seller_check.php";

$seller_id = $_SESSION["user_id"];

$selected_buyer_id = isset($_GET["buyer_id"])
    ? intval($_GET["buyer_id"])
    : 0;

$message_error = "";


/* =========================================================
   SEND MESSAGE
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $buyer_id = intval($_POST["buyer_id"] ?? 0);
    $text = trim($_POST["text"] ?? "");

    if ($buyer_id <= 0 || $text === "") {

        $message_error = "Please enter a message.";

    } else {

        /*
         * IMPORTANT:
         * Find the existing conversation first.
         * We do NOT delete or recreate old conversations.
         */

        $stmt = $pdo->prepare(
            "SELECT ChatID
             FROM chat
             WHERE sellerID = ?
             AND buyerID = ?
             LIMIT 1"
        );

        $stmt->execute([
            $seller_id,
            $buyer_id
        ]);

        $chat = $stmt->fetch();


        /*
         * Only create a chat if one does not already exist.
         */

        if ($chat) {

            $chat_id = $chat["ChatID"];

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO chat (sellerID, buyerID)
                 VALUES (?, ?)"
            );

            $stmt->execute([
                $seller_id,
                $buyer_id
            ]);

            $chat_id = $pdo->lastInsertId();
        }


        /*
         * Add the new seller message to the
         * existing conversation.
         */

        $stmt = $pdo->prepare(
            "INSERT INTO message
            (Text, SenderType, chatID)
            VALUES (?, 'Seller', ?)"
        );

        $stmt->execute([
            $text,
            $chat_id
        ]);


        header(
            "Location: index.php?buyer_id=" . $buyer_id
        );

        exit;
    }
}


/* =========================================================
   GET ALL SELLER CONVERSATIONS
   ========================================================= */

$stmt = $pdo->prepare(
    "SELECT
        c.ChatID,
        c.buyerID,
        b.Name,

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

     INNER JOIN buyer b
        ON c.buyerID = b.BuyerID

     WHERE c.sellerID = ?

     ORDER BY
        CASE
            WHEN last_time IS NULL THEN 1
            ELSE 0
        END,
        last_time DESC,
        c.ChatID DESC"
);

$stmt->execute([
    $seller_id
]);

$chats = $stmt->fetchAll();


/* =========================================================
   GET SELECTED BUYER
   ========================================================= */

$selected_buyer = null;
$messages = [];
$selected_chat_id = 0;

if ($selected_buyer_id > 0) {

    /*
     * Get buyer information.
     */

    $stmt = $pdo->prepare(
        "SELECT
            BuyerID,
            Name,
            Phone,
            Email
         FROM buyer
         WHERE BuyerID = ?
         LIMIT 1"
    );

    $stmt->execute([
        $selected_buyer_id
    ]);

    $selected_buyer = $stmt->fetch();


    if ($selected_buyer) {

        /*
         * Find the EXISTING conversation.
         */

        $stmt = $pdo->prepare(
            "SELECT ChatID
             FROM chat
             WHERE sellerID = ?
             AND buyerID = ?
             LIMIT 1"
        );

        $stmt->execute([
            $seller_id,
            $selected_buyer_id
        ]);

        $existing_chat = $stmt->fetch();


        if ($existing_chat) {

            $selected_chat_id =
                $existing_chat["ChatID"];


            /*
             * Get ALL old messages.
             */

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
        }
    }
}


include "../../includes/header.php";

?>


<div class="chat-page">


    <!-- PAGE HEADER -->

    <div class="chat-page-header">

        <p class="dashboard-label">
            SELLER CHAT
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
                Buyer Conversations
            </div>


            <?php if (empty($chats)): ?>

                <div class="empty-chat">
                    No buyers have contacted you yet.
                </div>

            <?php else: ?>


                <?php foreach ($chats as $chat): ?>

                    <a
                        href="index.php?buyer_id=<?= $chat["buyerID"] ?>"
                        class="chat-list-item <?= ($selected_buyer_id == $chat["buyerID"]) ? "active" : "" ?>"
                    >

                        <div class="chat-avatar">

                            <?= strtoupper(
                                substr(
                                    $chat["Name"],
                                    0,
                                    1
                                )
                            ) ?>

                        </div>


                        <div class="chat-list-info">

                            <h3>
                                <?= htmlspecialchars(
                                    $chat["Name"]
                                ) ?>
                            </h3>


                            <p>

                                <?=
                                $chat["last_message"]
                                    ? htmlspecialchars(
                                        $chat["last_message"]
                                    )
                                    : "New conversation"
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


            <?php if ($selected_buyer): ?>


                <div class="chat-window-header">

                    <div>

                        <h2>
                            <?= htmlspecialchars(
                                $selected_buyer["Name"]
                            ) ?>
                        </h2>

                        <p>
                            Buyer
                        </p>

                    </div>

                </div>


                <div class="messages-box">


                    <?php if (empty($messages)): ?>

                        <div class="empty-messages">

                            <div class="empty-message-icon">
                                💬
                            </div>

                            <h3>
                                No messages yet
                            </h3>

                            <p>
                                Start the conversation with this buyer.
                            </p>

                        </div>


                    <?php else: ?>


                        <?php foreach ($messages as $message): ?>


                            <div
                                class="message-row <?= $message["SenderType"] === "Seller"
                                    ? "seller-message"
                                    : "buyer-message" ?>"
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
                        name="buyer_id"
                        value="<?= $selected_buyer["BuyerID"] ?>"
                    >


                    <input
                        type="text"
                        name="text"
                        placeholder="Type your reply..."
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
                        Choose a buyer from the left to view messages.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>


<?php

include "../../includes/footer.php";

?>