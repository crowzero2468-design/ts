<?php
$this->extend('layout/main');
$this->section('body');
?>

<style>
.messenger-container {
    height: 85vh;
    background: #f0f2f5;
    border-radius: 15px;
    overflow: hidden;
    display: flex;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

/* Sidebar */
.chat-sidebar {
    width: 30%;
    background: #ffffff;
    border-right: 1px solid #ddd;
    overflow-y: auto;
}

.chat-user {
    padding: 15px;
    border-bottom: 1px solid #f1f1f1;
    cursor: pointer;
    transition: 0.2s;
}

.chat-user:hover {
    background: #f5f5f5;
}

/* Chat Area */
.chat-area {
    width: 70%;
    display: flex;
    flex-direction: column;
}

/* Header */
.chat-header {
    padding: 15px;
    background: #ffffff;
    border-bottom: 1px solid #ddd;
    font-weight: 600;
}

/* Messages */
.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.message {
    max-width: 60%;
    padding: 10px 15px;
    border-radius: 20px;
    margin-bottom: 10px;
    font-size: 14px;
    word-wrap: break-word;
}

.sent {
    align-self: flex-end;
    background: #0084ff;
    color: #fff;
    border-bottom-right-radius: 5px;
}

.received {
    align-self: flex-start;
    background: #e4e6eb;
    color: #000;
    border-bottom-left-radius: 5px;
}

/* Input */
.chat-input {
    padding: 10px;
    background: #ffffff;
    border-top: 1px solid #ddd;
    display: flex;
}

.chat-input input {
    flex: 1;
    border-radius: 25px;
    border: 1px solid #ccc;
    padding: 10px 15px;
    outline: none;
}

.chat-input button {
    background: #0084ff;
    border: none;
    color: #fff;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    margin-left: 10px;
}

.search-wrapper {
    position: relative;
    padding: 10px;
    background: #f0f2f5;
}

.search-input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border-radius: 25px;
    border: none;
    outline: none;
    font-size: 14px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.2s ease-in-out;
}

.search-input:focus {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #fff;
}

.search-icon {
    position: absolute;
    top: 50%;
    left: 22px;
    transform: translateY(-50%);
    font-size: 14px;
    color: #888;
}

</style>

<div class="messenger-container">

    <!-- Sidebar -->
        <div class="chat-sidebar">

            <!-- 🔎 Search Box -->
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="chatSearch" placeholder="Search user..." class="search-input">
            </div>


            <!-- 👥 Users List -->
            <div id="chatUserList">
                <?php foreach ($users as $user): ?>
                    <?php if ($user['name'] != $currentUser): ?>
                        <a href="<?= base_url('message/' . $user['name']) ?>" 
                        class="chat-link"
                        style="text-decoration:none; color:inherit;">

                            <div class="chat-user <?= ($receiver == $user['name']) ? 'bg-light' : '' ?>">
                                <strong class="chat-username">
                                    <?= esc($user['name']) ?>
                                    <span class="badge bg-danger unread-badge"
                                        id="badge-<?= $user['name'] ?>"
                                        style="display:none;">
                                    </span>
                                </strong>
                            </div>

                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        </div>


    <!-- Chat Area -->
    <div class="chat-area">

        <div class="chat-header">
            <?= $receiver ? esc($receiver) : 'Select a user to start chat' ?>
        </div>

        <div class="chat-messages" id="chatBox">
            <div id="typingIndicator" style="font-size:12px; color:gray;"></div>

            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender'] == $currentUser ? 'sent' : 'received' ?>">
                        <?= esc($msg['message']) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($receiver): ?>
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button type="button" onclick="sendMessage()">➤</button>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>

$('#endorseModal form').on('submit', function(e){

    e.preventDefault();
    let form = this;

    Swal.fire({
        title: 'Confirm Endorse?',
        text: "This ticket will be endorsed to selected personnel.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, endorse it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });

});

let receiver    = "<?= $receiver ?>";
let currentUser = "<?= $currentUser ?>";
let chatBox     = document.getElementById("chatBox");

/* ===============================
   🔔 UNREAD NOTIFICATION
=================================*/
function loadUnread() {
    fetch("<?= base_url('message/unreadCount') ?>")
        .then(res => res.json())
        .then(data => {

            if (!Array.isArray(data)) return;

            document.querySelectorAll('.unread-badge')
                .forEach(b => b.style.display = 'none');

            data.forEach(row => {
                let badge = document.getElementById('badge-' + row.sender);
                if (badge && row.total > 0) {
                    badge.innerText = row.total;
                    badge.style.display = 'inline-block';
                }
            });
        })
        .catch(err => console.log("Unread error:", err));
}

setInterval(loadUnread, 2000);
loadUnread();


/* ===============================
   💬 CHAT FUNCTIONS
=================================*/
if (receiver && chatBox) {

    function scrollBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function loadMessages() {
    fetch("<?= base_url('message/fetch/') ?>" + receiver)
        .then(res => res.json())
        .then(data => {

            if (!Array.isArray(data)) return;

            let oldIndicator = document.getElementById("typingIndicator");
            let typingText = oldIndicator ? oldIndicator.innerHTML : "";

            chatBox.innerHTML = "";

            data.forEach(msg => {
                let div = document.createElement("div");
                div.classList.add("message");
                div.classList.add(msg.sender === currentUser ? "sent" : "received");
                div.innerText = msg.message;
                chatBox.appendChild(div);
            });

            // Re-add typing indicator at bottom
            let indicator = document.createElement("div");
            indicator.id = "typingIndicator";
            indicator.style.fontSize = "12px";
            indicator.style.color = "gray";
            indicator.style.marginTop = "5px";
            indicator.innerHTML = typingText;

            chatBox.appendChild(indicator);

            scrollBottom();
        })
        .catch(err => console.log("Fetch error:", err));
}


    function sendMessage() {
        let input = document.getElementById("messageInput");
        let message = input.value.trim();

        if (message === "") return;

        fetch("<?= base_url('message/send') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body:
                "message=" + encodeURIComponent(message) +
                "&receiver=" + encodeURIComponent(receiver) +
                "&<?= csrf_token() ?>=<?= csrf_hash() ?>"
        })
        .then(res => res.json())
        .then(() => {
            input.value = "";
            loadMessages();
        })
        .catch(err => console.log("Send error:", err));
    }

    /* ===============================
       ✍️ TYPING SYSTEM (Improved)
    =================================*/
    let typingTimeout;

    document.getElementById("messageInput")?.addEventListener("input", function () {

        clearTimeout(typingTimeout);

        fetch("<?= base_url('message/typing') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body:
                "receiver=" + receiver +
                "&<?= csrf_token() ?>=<?= csrf_hash() ?>"
        });

        typingTimeout = setTimeout(() => {
            fetch("<?= base_url('message/typing') ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body:
                    "receiver=&<?= csrf_token() ?>=<?= csrf_hash() ?>"
            });
        }, 2000);
    });

    function checkTyping() {
        fetch("<?= base_url('message/checkTyping/') ?>" + receiver)
            .then(res => res.json())
            .then(data => {
                let indicator = document.getElementById("typingIndicator");
                if (!indicator) return;

                indicator.innerHTML = data.typing
                    ? receiver + " is typing..."
                    : "";
            })
            .catch(err => console.log("Typing error:", err));
    }

    setInterval(() => {
        loadMessages();
        checkTyping();
    }, 2000);

    loadMessages();
}

$('#chatSearch').on('keyup', function () {
    let value = $(this).val().toLowerCase();

    $('.chat-link').filter(function () {
        $(this).toggle(
            $(this).text().toLowerCase().indexOf(value) > -1
        );
    });
});
</script>



<?php $this->endSection(); ?>
