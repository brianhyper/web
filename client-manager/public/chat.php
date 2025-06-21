<?php
// client-manager/public/chat.php
require '../app.php';
authenticate(['admin', 'staff']);

// Get users for chat list
$users = $pdo->query("
    SELECT id, name, role 
    FROM users 
    WHERE id != {$_SESSION['user_id']}
    ORDER BY name ASC
")->fetchAll();

$pageTitle = "Internal Chat";
include '../header.php';
?>

<main class="content">
    <div class="page-header">
        <h1><i class="fas fa-comments"></i> Internal Chat</h1>
    </div>

    <div class="chat-container">
        <div class="chat-sidebar">
            <div class="chat-search">
                <input type="text" placeholder="Search users...">
                <i class="fas fa-search"></i>
            </div>
            
            <div class="chat-users">
                <?php foreach ($users as $user): ?>
                    <div class="chat-user" data-id="<?= $user['id'] ?>">
                        <div class="avatar">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <strong><?= htmlspecialchars($user['name']) ?></strong>
                            <span class="user-role"><?= ucfirst($user['role']) ?></span>
                        </div>
                        <span class="badge badge-primary unread-count" style="display: none;">0</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="chat-main">
            <div class="chat-header">
                <div class="active-user">
                    <div class="avatar">
                        <span id="activeAvatar">S</span>
                    </div>
                    <div>
                        <strong id="activeUserName">Select a user to chat</strong>
                        <small id="activeUserStatus">Offline</small>
                    </div>
                </div>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="no-chat">
                    <i class="fas fa-comments fa-3x"></i>
                    <p>Select a conversation to start chatting</p>
                </div>
            </div>
            
            <div class="chat-input" style="display: none;">
                <textarea id="messageInput" placeholder="Type your message..."></textarea>
                <button id="sendMessageBtn" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeUserId = null;
    let lastMessageId = 0;
    
    // Select user to chat
    document.querySelectorAll('.chat-user').forEach(user => {
        user.addEventListener('click', function() {
            // Set active user
            activeUserId = this.dataset.id;
            document.getElementById('activeUserName').textContent = this.querySelector('strong').textContent;
            document.getElementById('activeAvatar').textContent = this.querySelector('.avatar').textContent;
            
            // Highlight selected user
            document.querySelectorAll('.chat-user').forEach(u => {
                u.classList.remove('active');
            });
            this.classList.add('active');
            
            // Show chat input
            document.querySelector('.chat-input').style.display = 'flex';
            
            // Load messages
            loadMessages();
        });
    });
    
    // Load messages for active user
    function loadMessages() {
        fetch(`/api/chat/messages?recipient=${activeUserId}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(messages => {
                const chatMessages = document.getElementById('chatMessages');
                
                if (messages.length > 0) {
                    chatMessages.innerHTML = '';
                    lastMessageId = messages[messages.length - 1].id;
                    
                    messages.forEach(msg => {
                        const messageClass = msg.sender_id == <?= $_SESSION['user_id'] ?> ? 'sent' : 'received';
                        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        
                        chatMessages.innerHTML += `
                            <div class="message ${messageClass}">
                                <div class="message-content">${msg.message}</div>
                                <div class="message-time">${time}</div>
                            </div>
                        `;
                    });
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
    }
    
    // Send message
    document.getElementById('sendMessageBtn').addEventListener('click', function() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        
        if (message && activeUserId) {
            fetch('/api/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    recipient_id: activeUserId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                }
            });
        }
    });
    
    // Allow sending with Enter key
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('sendMessageBtn').click();
        }
    });
    
    // Poll for new messages every 5 seconds
    setInterval(() => {
        if (activeUserId) {
            loadMessages();
        }
    }, 5000);
});
</script>

<style>
.chat-container {
    display: flex;
    height: 70vh;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.chat-sidebar {
    width: 300px;
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
}

.chat-search {
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    position: relative;
}

.chat-search input {
    width: 100%;
    padding: 8px 15px 8px 35px;
    border: 1px solid var(--border-color);
    border-radius: 30px;
}

.chat-search i {
    position: absolute;
    left: 30px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
}

.chat-users {
    flex: 1;
    overflow-y: auto;
}

.chat-user {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid var(--border-color);
    position: relative;
}

.chat-user:hover, .chat-user.active {
    background-color: rgba(26, 115, 232, 0.05);
}

.chat-user .avatar {
    width: 40px;
    height: 40px;
    background-color: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 12px;
}

.user-info {
    flex: 1;
}

.user-info strong {
    display: block;
    font-size: 0.95rem;
}

.user-role {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.unread-count {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-header {
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
}

.active-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.no-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    text-align: center;
}

.message {
    max-width: 75%;
    margin-bottom: 15px;
    position: relative;
}

.message.sent {
    align-self: flex-end;
    background-color: #d1e7ff;
    border-radius: 15px 15px 0 15px;
}

.message.received {
    align-self: flex-start;
    background-color: #f1f3f4;
    border-radius: 15px 15px 15px 0;
}

.message-content {
    padding: 10px 15px;
}

.message-time {
    font-size: 0.7rem;
    color: var(--text-secondary);
    text-align: right;
    padding: 0 10px 5px;
}

.chat-input {
    display: flex;
    padding: 15px;
    border-top: 1px solid var(--border-color);
    align-items: center;
    gap: 10px;
}

.chat-input textarea {
    flex: 1;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 20px;
    resize: none;
    height: 45px;
    max-height: 120px;
}

.chat-input button {
    width: 45px;
    height: 45px;
    border-radius: 50%;
}
</style>

<?php include '../footer.php'; ?>