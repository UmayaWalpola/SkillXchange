let messagePollingInterval = null;
let lastMessageId = 0;


/**
* Open chat window with a specific partner
*/
function openChatWindow(partnerId) {
   window.location.href = `${URLROOT}/chat/user/${partnerId}`;
}


/**
* Load messages for current chat
*/
function loadMessages() {
   if (!CURRENT_CHAT_ID) return;


   fetch(`${URLROOT}/chat/fetchUserMessages?chat_id=${CURRENT_CHAT_ID}`)
       .then(response => response.json())
       .then(data => {
           console.log('Messages received:', data);


           if (data.success) {
               displayMessages(data.messages, data.current_user_id);


               if (data.messages.length > 0) {
                   lastMessageId = data.messages[data.messages.length - 1].id;
               }
           } else {
               console.error('Failed to load messages:', data.message);
           }
       })
       .catch(error => {
           console.error('Error loading messages:', error);
       });
}


/**
* Display messages in the chat window
*/
function displayMessages(messages, currentUserId) {
   const container = document.getElementById('messagesContainer');


   if (!container) {
       console.error('messagesContainer not found');
       return;
   }


   if (!messages || messages.length === 0) {
       container.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
       return;
   }


   let html = '';
   messages.forEach(msg => {
       const isOwn = msg.sender_id == currentUserId;
       const messageClass = isOwn ? 'message own-message' : 'message';


       let avatarHTML = '';
       if (msg.sender_profile_pic && msg.sender_profile_pic.includes('uploads/')) {
           avatarHTML = `<img src="${URLROOT}/${msg.sender_profile_pic}" alt="Avatar">`;
       } else {
           const initials = msg.sender_name
               ? msg.sender_name.substring(0, 2).toUpperCase()
               : '??';
           avatarHTML = initials;
       }


       html += `
           <div class="${messageClass}" data-message-id="${msg.id}">
               <div class="message-avatar">
                   ${avatarHTML}
               </div>
               <div class="message-content">
                   ${!isOwn ? `<div class="message-sender">${msg.sender_name}</div>` : ''}
                   <div class="message-text">${escapeHtml(msg.message)}</div>
                   <div class="message-time">${formatMessageTime(msg.created_at)}</div>
               </div>
           </div>
       `;
   });


   container.innerHTML = html;
   scrollToBottom();
}


/**
* Send a new message
*/
function sendMessage(event) {
   event.preventDefault();


   const input = document.getElementById('messageInput');
   const message = input.value.trim();
   const chatId = document.getElementById('chatId')?.value;


   if (!message || !chatId) return;


   const formData = new FormData();
   formData.append('chat_id', chatId);
   formData.append('message', message);


   fetch(`${URLROOT}/chat/sendUserMessage`, {
       method: 'POST',
       body: formData
   })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               input.value = '';
               loadMessages();
           } else {
               showNotification(data.message || 'Failed to send message.', 'error');
           }
       })
       .catch(error => {
           console.error('Error sending message:', error);
           showNotification('Network error. Please try again.', 'error');
       });
}


/**
* Poll for new messages
*/
function pollForNewMessages() {
   if (!CURRENT_CHAT_ID) return;


   fetch(`${URLROOT}/chat/fetchUserMessages?chat_id=${CURRENT_CHAT_ID}`)
       .then(response => response.json())
       .then(data => {
           if (data.success && data.messages.length > 0) {
               const latestId = data.messages[data.messages.length - 1].id;


               if (latestId > lastMessageId) {
                   displayMessages(data.messages, data.current_user_id);
                   lastMessageId = latestId;
               }
           }
       })
       .catch(error => {
           console.error('Error polling messages:', error);
       });
}


/**
* View partner's profile
*/
function viewPartnerProfile(partnerId) {
   window.location.href = `${URLROOT}/userdashboard/viewProfile/${partnerId}`;
}


/**
* Open/close transaction modal
*/
function openTransactionModal() {
   const modal = document.getElementById('transactionModal');
   if (!modal) {
       console.error('transactionModal not found');
       return;
   }
   modal.style.display = 'flex';
}


function closeTransactionModal() {
   const modal = document.getElementById('transactionModal');
   if (!modal) return;
   modal.style.display = 'none';
}


/**
* Open/close report modal
*/
function openReportModal(eventId) {
   const modal = document.getElementById('reportModal');
   const eventInput = document.getElementById('reportEventId');


   if (!modal || !eventInput) {
       console.error('report modal elements not found');
       return;
   }


   eventInput.value = eventId;
   modal.style.display = 'flex';
}


function closeReportModal() {
   const modal = document.getElementById('reportModal');
   if (!modal) return;
   modal.style.display = 'none';
}


/**
* Respond to offer
*/
function respondToOffer(eventId, action) {
   const formData = new FormData();
   formData.append('event_id', eventId);
   formData.append('action', action);


   fetch(`${URLROOT}/transaction/respondToOffer`, {
       method: 'POST',
       body: formData
   })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               showNotification(data.message || 'Offer updated.', 'success');
               setTimeout(() => location.reload(), 500);
           } else {
               showNotification(data.message || 'Failed to update offer.', 'error');
           }
       })
       .catch(error => {
           console.error('Error responding to offer:', error);
           showNotification('Network error.', 'error');
       });
}


/**
* Leave lesson
*/
function leaveLesson(eventId) {
   if (!confirm('Are you sure you want to leave this lesson?')) return;


   const formData = new FormData();
   formData.append('event_id', eventId);


   fetch(`${URLROOT}/transaction/leaveLesson`, {
       method: 'POST',
       body: formData
   })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               showNotification(data.message || 'Lesson left successfully.', 'success');
               setTimeout(() => location.reload(), 500);
           } else {
               showNotification(data.message || 'Failed to leave lesson.', 'error');
           }
       })
       .catch(error => {
           console.error('Error leaving lesson:', error);
           showNotification('Network error.', 'error');
       });
}


/**
* Mark session completed
*/
function markCompleted(eventId) {
   if (!confirm('Mark this session as completed?')) return;


   const formData = new FormData();
   formData.append('event_id', eventId);


   fetch(`${URLROOT}/transaction/markCompleted`, {
       method: 'POST',
       body: formData
   })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               showNotification(data.message || 'Marked as completed.', 'success');
               setTimeout(() => location.reload(), 500);
           } else {
               showNotification(data.message || 'Failed to mark completed.', 'error');
           }
       })
       .catch(error => {
           console.error('Error marking completed:', error);
           showNotification('Network error.', 'error');
       });
}


/**
* Verify completion
*/
function verifyCompletion(eventId, action) {
   const formData = new FormData();
   formData.append('event_id', eventId);
   formData.append('action', action);


   fetch(`${URLROOT}/transaction/verifyCompletion`, {
       method: 'POST',
       body: formData
   })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               showNotification(data.message || 'Verification completed.', 'success');
               setTimeout(() => location.reload(), 500);
           } else {
               showNotification(data.message || 'Failed to verify.', 'error');
           }
       })
       .catch(error => {
           console.error('Error verifying completion:', error);
           showNotification('Network error.', 'error');
       });
}


/**
* Search chats
*/
function searchChats() {
   const searchInput = document.getElementById('searchChats');
   if (!searchInput) return;


   const query = searchInput.value.toLowerCase();
   const chatItems = document.querySelectorAll('.chat-item');


   chatItems.forEach(item => {
       const name = item.querySelector('.chat-name')?.textContent.toLowerCase() || '';
       const preview = item.querySelector('.chat-preview')?.textContent.toLowerCase() || '';


       if (name.includes(query) || preview.includes(query)) {
           item.style.display = 'flex';
       } else {
           item.style.display = 'none';
       }
   });
}


/**
* Scroll to bottom
*/
function scrollToBottom() {
   const container = document.getElementById('messagesContainer');
   if (container) {
       container.scrollTop = container.scrollHeight;
   }
}


/**
* Format message time
*/
function formatMessageTime(timestamp) {
   const date = new Date(timestamp);
   const now = new Date();
   const diff = now - date;


   if (diff < 60000) {
       return 'Just now';
   }


   if (diff < 3600000) {
       const mins = Math.floor(diff / 60000);
       return `${mins}m ago`;
   }


   if (date.toDateString() === now.toDateString()) {
       return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
   }


   const yesterday = new Date(now);
   yesterday.setDate(yesterday.getDate() - 1);
   if (date.toDateString() === yesterday.toDateString()) {
       return 'Yesterday ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
   }


   return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}


/**
* Escape HTML
*/
function escapeHtml(text) {
   const div = document.createElement('div');
   div.textContent = text;
   return div.innerHTML;
}


/**
* Show notification
*/
function showNotification(message, type = 'info') {
   const existing = document.querySelector('.notification-banner');
   if (existing) {
       existing.remove();
   }


   const notification = document.createElement('div');
   notification.className = `notification-banner notification-${type}`;
   notification.innerHTML = `
       <span class="notification-message">${message}</span>
       <button class="notification-close" onclick="this.parentElement.remove()">×</button>
   `;


   document.body.appendChild(notification);


   setTimeout(() => {
       if (notification.parentElement) {
           notification.style.opacity = '0';
           setTimeout(() => notification.remove(), 300);
       }
   }, 5000);
}


/**
* Init on page load
*/
document.addEventListener('DOMContentLoaded', function () {
   console.log('Chats page loaded');
   console.log('Current chat ID:', CURRENT_CHAT_ID);


   if (CURRENT_CHAT_ID) {
       loadMessages();
       messagePollingInterval = setInterval(pollForNewMessages, 3000);
   }


   const searchInput = document.getElementById('searchChats');
   if (searchInput) {
       searchInput.addEventListener('input', searchChats);
   }


   const messageInput = document.getElementById('messageInput');
   if (messageInput) {
       messageInput.focus();
   }


   const paymentType = document.getElementById('paymentType');
   if (paymentType) {
       paymentType.addEventListener('change', function () {
           const buckxGroup = document.getElementById('buckxAmountGroup');
           const skillxGroup = document.getElementById('skillxGroup');


           if (this.value === 'buckx') {
               if (buckxGroup) buckxGroup.style.display = 'block';
               if (skillxGroup) skillxGroup.style.display = 'none';
           } else if (this.value === 'skillx') {
               if (buckxGroup) buckxGroup.style.display = 'none';
               if (skillxGroup) skillxGroup.style.display = 'block';
           } else {
               if (buckxGroup) buckxGroup.style.display = 'none';
               if (skillxGroup) skillxGroup.style.display = 'none';
           }
       });
   }


   const transactionForm = document.getElementById('transactionForm');
   if (transactionForm) {
       transactionForm.addEventListener('submit', function (e) {
           e.preventDefault();


           if (!CURRENT_CHAT_ID) {
               showNotification('No active chat selected.', 'error');
               return;
           }


           const formData = new FormData(transactionForm);
           formData.append('chat_id', CURRENT_CHAT_ID);


           fetch(`${URLROOT}/transaction/createOffer`, {
               method: 'POST',
               body: formData
           })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       showNotification(data.message || 'Offer created successfully.', 'success');
                       closeTransactionModal();
                       setTimeout(() => location.reload(), 500);
                   } else {
                       showNotification(data.message || 'Failed to create offer.', 'error');
                   }
               })
               .catch(error => {
                   console.error('Error creating offer:', error);
                   showNotification('Network error while creating offer.', 'error');
               });
       });
   }


   const reportForm = document.getElementById('reportForm');
   if (reportForm) {
       reportForm.addEventListener('submit', function (e) {
           e.preventDefault();


           const eventId = document.getElementById('reportEventId')?.value;
           const reason = document.getElementById('disputeReason')?.value.trim();


           if (!eventId || !reason) {
               showNotification('Please provide a reason for the report.', 'error');
               return;
           }


           const formData = new FormData();
           formData.append('event_id', eventId);
           formData.append('action', 'report');
           formData.append('dispute_reason', reason);


           fetch(`${URLROOT}/transaction/verifyCompletion`, {
               method: 'POST',
               body: formData
           })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       showNotification(data.message || 'Issue reported.', 'success');
                       closeReportModal();
                       setTimeout(() => location.reload(), 500);
                   } else {
                       showNotification(data.message || 'Failed to report issue.', 'error');
                   }
               })
               .catch(error => {
                   console.error('Error reporting issue:', error);
                   showNotification('Network error.', 'error');
               });
       });
   }
});


/**
* Cleanup
*/
window.addEventListener('beforeunload', function () {
   if (messagePollingInterval) {
       clearInterval(messagePollingInterval);
   }
});
