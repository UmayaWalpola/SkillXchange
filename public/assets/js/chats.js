/* Chats Page JavaScript - Working Version */

console.log('Chats JavaScript loaded!');

let currentChatId = null;
let currentChatData = null;

// Make openChat globally accessible - MUST be window.openChat
window.openChat = function(chat) {
    console.log('=== openChat called ===');
    console.log('Chat data received:', chat);
    
    if (!chat) {
        console.error('No chat data provided!');
        return;
    }
    
    currentChatId = chat.id;
    currentChatData = chat;
    
    // Remove unread styling from sidebar
    const clickedChat = document.querySelector(`[data-chat-id="${chat.id}"]`);
    if (clickedChat) {
        clickedChat.classList.remove('unread');
        const badge = clickedChat.querySelector('.unread-badge');
        if (badge) {
            badge.remove();
        }
    }
    
    // Get messages
    const messages = chat.messages || [];
    const userInitials = chat.name.substring(0, 2).toUpperCase();
    
    console.log('Building chat window for:', chat.name);
    console.log('Messages count:', messages.length);
    
    // Build messages HTML
    let messagesHTML = '';
    if (messages.length > 0) {
        messages.forEach(msg => {
            const isSent = msg.sender === 'me';
            const avatar = isSent ? 'Me' : userInitials;
            messagesHTML += `
                <div class="message ${isSent ? 'sent' : 'received'}">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <div class="message-text">${msg.text}</div>
                        <div class="message-time">${msg.time}</div>
                    </div>
                </div>
            `;
        });
    } else {
        messagesHTML = `
            <div style="text-align: center; padding: 2rem; opacity: 0.7;">
                <p>No messages yet. Start the conversation!</p>
            </div>
        `;
    }
    
    // Build complete chat window
    const chatWindowHTML = `
        <div class="chat-header">
            <div class="chat-avatar">${userInitials}</div>
            <div class="chat-header-info">
                <h2>${chat.name}</h2>
            </div>
        </div>
        
        <div class="messages-area" id="messagesArea">
            ${messagesHTML}
        </div>
        
        <div class="message-input-area">
            <div class="input-row">
                <textarea 
                    class="message-input" 
                    id="messageInput" 
                    placeholder="Type your message..." 
                    rows="1"
                ></textarea>
                <button class="send-message-btn" onclick="sendMessage()">Send</button>
            </div>
        </div>
    `;
    
    // Update the chat window
    const chatWindow = document.getElementById('chatWindow');
    if (chatWindow) {
        chatWindow.innerHTML = chatWindowHTML;
        console.log('✓ Chat window updated successfully');
        
        // Setup event listeners for the input
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            messageInput.addEventListener('input', function(e) {
                e.target.style.height = 'auto';
                e.target.style.height = Math.min(e.target.scrollHeight, 120) + 'px';
            });
            
            // Focus on input
            messageInput.focus();
        }
        
        // Scroll to bottom
        setTimeout(function() {
            const messagesArea = document.getElementById('messagesArea');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        }, 100);
    } else {
        console.error('Chat window element not found!');
    }
}

// Send a message
window.sendMessage = function() {
    console.log('sendMessage called');
    
    const input = document.getElementById('messageInput');
    if (!input) {
        console.error('Message input not found!');
        return;
    }
    
    const messageText = input.value.trim();
    
    if (!messageText) {
        console.log('No message text');
        return;
    }
    
    if (!currentChatData) {
        console.error('No current chat data!');
        return;
    }
    
    console.log('Sending message:', messageText);
    
    // Get current time
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    
    // Add to messages array
    if (!currentChatData.messages) {
        currentChatData.messages = [];
    }
    
    currentChatData.messages.push({
        sender: 'me',
        text: messageText,
        time: timeString
    });
    
    // Add message to DOM
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        const messageHTML = `
            <div class="message sent">
                <div class="message-avatar">Me</div>
                <div class="message-content">
                    <div class="message-text">${messageText}</div>
                    <div class="message-time">${timeString}</div>
                </div>
            </div>
        `;
        
        messagesArea.insertAdjacentHTML('beforeend', messageHTML);
        
        // Scroll to bottom
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
    
    // Clear input
    input.value = '';
    input.style.height = 'auto';
    
    // Update chat preview in sidebar
    const chatItem = document.querySelector(`[data-chat-id="${currentChatId}"]`);
    if (chatItem) {
        const preview = chatItem.querySelector('.chat-preview');
        const timeElement = chatItem.querySelector('.chat-time');
        
        if (preview) preview.textContent = 'You: ' + messageText;
        if (timeElement) timeElement.textContent = timeString;
    }
    
    console.log('✓ Message sent successfully');
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up search');
    
    const searchInput = document.getElementById('searchChats');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const chatItems = document.querySelectorAll('.chat-item');
            
            chatItems.forEach(item => {
                const name = item.querySelector('.chat-name').textContent.toLowerCase();
                const preview = item.querySelector('.chat-preview').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || preview.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    console.log('✓ Search setup complete');
});