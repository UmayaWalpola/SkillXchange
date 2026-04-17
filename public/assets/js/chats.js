let messagePollingInterval = null;
let lastMessageId = 0;

function openChatWindow(partnerId) {
    window.location.href = `${URLROOT}/chat/user/${partnerId}`;
}

// ── Messages ──────────────────────────────────────────────────────────────────

function loadMessages() {
    if (!CURRENT_CHAT_ID) return;

    fetch(`${URLROOT}/chat/fetchUserMessages?chat_id=${CURRENT_CHAT_ID}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages, data.current_user_id);
                if (data.messages.length > 0) {
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
            }
        })
        .catch(err => console.error('Error loading messages:', err));
}

function displayMessages(messages, currentUserId) {
    const container = document.getElementById('messagesContainer');
    if (!container) return;

    if (!messages || messages.length === 0) {
        container.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
        return;
    }

    let html = '';
    messages.forEach(msg => {
        const isOwn = msg.sender_id == currentUserId;
        let avatarHTML = msg.sender_profile_pic && msg.sender_profile_pic.includes('uploads/')
            ? `<img src="${URLROOT}/${msg.sender_profile_pic}" alt="Avatar">`
            : (msg.sender_name ? msg.sender_name.substring(0, 2).toUpperCase() : '??');

        html += `
            <div class="${isOwn ? 'message own-message' : 'message'}" data-message-id="${msg.id}">
                <div class="message-avatar">${avatarHTML}</div>
                <div class="message-content">
                    ${!isOwn ? `<div class="message-sender">${msg.sender_name}</div>` : ''}
                    <div class="message-text">${escapeHtml(msg.message)}</div>
                    <div class="message-time">${formatMessageTime(msg.created_at)}</div>
                </div>
            </div>`;
    });

    container.innerHTML = html;
    scrollToBottom();
}

function sendMessage(event) {
    event.preventDefault();
    const input  = document.getElementById('messageInput');
    const message = input.value.trim();
    const chatId  = document.getElementById('chatId')?.value;
    if (!message || !chatId) return;

    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('message', message);

    fetch(`${URLROOT}/chat/sendUserMessage`, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadMessages();
            } else {
                showNotification(data.message || 'Failed to send message.', 'error');
            }
        })
        .catch(() => showNotification('Network error. Please try again.', 'error'));
}

function pollForNewMessages() {
    if (!CURRENT_CHAT_ID) return;
    fetch(`${URLROOT}/chat/fetchUserMessages?chat_id=${CURRENT_CHAT_ID}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                const latestId = data.messages[data.messages.length - 1].id;
                if (latestId > lastMessageId) {
                    displayMessages(data.messages, data.current_user_id);
                    lastMessageId = latestId;
                }
            }
        })
        .catch(() => {});
}

// ── Profile ───────────────────────────────────────────────────────────────────

function viewPartnerProfile(partnerId) {
    window.location.href = `${URLROOT}/userdashboard/viewProfile/${partnerId}`;
}

// ── Transaction modal ─────────────────────────────────────────────────────────

function openTransactionModal() {
    const modal = document.getElementById('transactionModal');
    if (!modal) { console.error('transactionModal not found'); return; }
    modal.style.display = 'flex';
}

function closeTransactionModal() {
    const modal = document.getElementById('transactionModal');
    if (modal) modal.style.display = 'none';
}

// ── Report modal ──────────────────────────────────────────────────────────────

function openReportModal(eventId) {
    const modal      = document.getElementById('reportModal');
    const eventInput = document.getElementById('reportEventId');
    if (!modal || !eventInput) { console.error('report modal elements not found'); return; }
    eventInput.value = eventId;
    modal.style.display = 'flex';
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) modal.style.display = 'none';
}

// ── Transaction actions ───────────────────────────────────────────────────────

function respondToOffer(eventId, action) {
    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('action', action);

    fetch(`${URLROOT}/transaction/respondToOffer`, { method: 'POST', body: formData })
        .then(async r => {
            const raw = await r.text();

            try {
                return JSON.parse(raw);
            } catch (parseError) {
                console.error('respondToOffer returned non-JSON response:', raw);
                return {
                    success: false,
                    message: raw && raw.trim()
                        ? raw.trim()
                        : 'Unexpected server response while responding to the offer.'
                };
            }
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Offer updated.', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification(data.message || 'Failed to update offer.', 'error');
            }
        })
        .catch((error) => {
            console.error('respondToOffer network error:', error);
            showNotification('Network error.', 'error');
        });
}

function parseJsonResponse(response, contextLabel) {
    return response.text().then(raw => {
        try {
            return JSON.parse(raw);
        } catch (parseError) {
            console.error(`${contextLabel} returned non-JSON response:`, raw);
            return {
                success: false,
                message: raw && raw.trim()
                    ? raw.trim()
                    : `Unexpected server response while ${contextLabel}.`
            };
        }
    });
}

function leaveLesson(eventId) {
    if (!confirm('Are you sure you want to leave this lesson?')) return;

    const formData = new FormData();
    formData.append('event_id', eventId);

    fetch(`${URLROOT}/transaction/leaveLesson`, { method: 'POST', body: formData })
        .then(async r => JSON.parse(await r.text()))
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Lesson left successfully.', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification(data.message || 'Failed to leave lesson.', 'error');
            }
        })
        .catch(() => showNotification('Network error.', 'error'));
}

function markCompleted(eventId) {
    if (!confirm('Mark this session as completed?')) return;

    const formData = new FormData();
    formData.append('event_id', eventId);

    fetch(`${URLROOT}/transaction/markCompleted`, { method: 'POST', body: formData })
        .then(r => parseJsonResponse(r, 'marking session as completed'))
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Marked as completed.', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification(data.message || 'Failed.', 'error');
            }
        })
        .catch((error) => {
            console.error('markCompleted network error:', error);
            showNotification('Network error.', 'error');
        });
}

function verifyCompletion(eventId, action) {
    if (action === 'agreed' && !confirm('Confirm the session was completed and release payment?')) return;

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('action', action);

    fetch(`${URLROOT}/transaction/verifyCompletion`, { method: 'POST', body: formData })
        .then(r => parseJsonResponse(r, 'verifying session completion'))
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Done.', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification(data.message || 'Failed.', 'error');
            }
        })
        .catch((error) => {
            console.error('verifyCompletion network error:', error);
            showNotification('Network error.', 'error');
        });
}

// ── Search chats sidebar ──────────────────────────────────────────────────────

function searchChats() {
    const query = document.getElementById('searchChats')?.value.toLowerCase() || '';
    document.querySelectorAll('.chat-item').forEach(item => {
        const name = item.querySelector('.chat-name')?.textContent.toLowerCase() || '';
        item.style.display = name.includes(query) ? '' : 'none';
    });
}

// ── Utilities ─────────────────────────────────────────────────────────────────

function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    if (container) container.scrollTop = container.scrollHeight;
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now  = new Date();
    const diff = now - date;
    if (diff < 60000)    return 'Just now';
    if (diff < 3600000)  return `${Math.floor(diff / 60000)}m ago`;
    if (date.toDateString() === now.toDateString())
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString())
        return 'Yesterday ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification-banner');
    if (existing) existing.remove();

    const n = document.createElement('div');
    n.className = `notification-banner notification-${type}`;
    n.innerHTML = `<span class="notification-message">${message}</span>
                   <button class="notification-close" onclick="this.parentElement.remove()">×</button>`;
    document.body.appendChild(n);

    setTimeout(() => {
        if (n.parentElement) {
            n.style.opacity = '0';
            setTimeout(() => n.remove(), 300);
        }
    }, 5000);
}

// ── Session countdown ─────────────────────────────────────────────────────────
// When the countdown hits zero, the server-side cron (process_transaction_timeouts.php)
// handles the actual transfer. The countdown is purely visual — it alerts the user
// that time is up and triggers a page reload so the banner updates.

function startSessionCountdown() {
    const countdownEl = document.getElementById('sessionCountdown');
    if (!countdownEl) return;

    const expiresAt = countdownEl.dataset.expiresAt;
    if (!expiresAt) return;

    let expiredNotified = false;

    function updateCountdown() {
        const end  = new Date(expiresAt.replace(' ', 'T'));
        const now  = new Date();
        const diff = end - now;

        if (diff <= 0) {
            countdownEl.textContent = '⏰ Session time expired — processing...';
            if (!expiredNotified) {
                expiredNotified = true;
                showNotification('Session time has expired. The transaction is being processed.', 'info');
                // Reload after a short delay so the banner reflects the updated DB status
                setTimeout(() => location.reload(), 4000);
            }
            return;
        }

        const totalSecs = Math.floor(diff / 1000);
        const days    = Math.floor(totalSecs / 86400);
        const hours   = Math.floor((totalSecs % 86400) / 3600);
        const minutes = Math.floor((totalSecs % 3600) / 60);
        const seconds = totalSecs % 60;

        countdownEl.textContent = days > 0
            ? `Time remaining: ${days}d ${hours}h ${minutes}m ${seconds}s`
            : `Time remaining: ${hours}h ${minutes}m ${seconds}s`;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// ── Init ──────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    console.log('Chats page loaded. Chat ID:', CURRENT_CHAT_ID, '| Match type:', MATCH_TYPE);

    if (CURRENT_CHAT_ID) {
        loadMessages();
        messagePollingInterval = setInterval(pollForNewMessages, 3000);
    }

    const searchInput = document.getElementById('searchChats');
    if (searchInput) searchInput.addEventListener('input', searchChats);

    const messageInput = document.getElementById('messageInput');
    if (messageInput) messageInput.focus();

    // Payment type toggle
    const paymentType = document.getElementById('paymentType');
    if (paymentType) {
        paymentType.addEventListener('change', function () {
            const buckxGroup = document.getElementById('buckxAmountGroup');
            const skillxGroup = document.getElementById('skillxGroup');
            if (this.value === 'buckx') {
                if (buckxGroup)  buckxGroup.style.display  = 'block';
                if (skillxGroup) skillxGroup.style.display = 'none';
            } else if (this.value === 'skillx') {
                if (buckxGroup)  buckxGroup.style.display  = 'none';
                if (skillxGroup) skillxGroup.style.display = 'block';
            } else {
                if (buckxGroup)  buckxGroup.style.display  = 'none';
                if (skillxGroup) skillxGroup.style.display = 'none';
            }
        });
    }

    // Transaction form submit
    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitButton = transactionForm.querySelector('button[type="submit"]');

            if (transactionForm.dataset.submitting === 'true') {
                return;
            }

            if (!CURRENT_CHAT_ID) {
                showNotification('No active chat selected.', 'error');
                return;
            }

            // For single/multi, role is a hidden input already set to MATCH_DIR.
            // For mutual, role comes from the visible select.
            const role =
                document.querySelector('#transactionRole')?.value ||
                document.querySelector('#transactionRoleHidden')?.value ||
                '';
            if (!role) {
                showNotification('Please select your role for this session.', 'error');
                return;
            }

            const paymentTypeValue = document.getElementById('paymentType')?.value || '';
            const amount           = document.getElementById('buckxAmount')?.value  || '';
            const skillName        =
                document.getElementById('skillName')?.value ||
                document.getElementById('skillNameHidden')?.value ||
                '';
            const timeframeValue   = parseInt(document.getElementById('timeframeValue')?.value || '0', 10);
            const timeframeUnit    = document.getElementById('timeframeUnit')?.value || 'hours';
            const timeframeHours   = timeframeUnit === 'days' ? timeframeValue * 24 : timeframeValue;

            if (!paymentTypeValue) {
                showNotification('Please select a payment type.', 'error');
                return;
            }
            if (paymentTypeValue === 'buckx' && (!amount || parseFloat(amount) <= 0)) {
                showNotification('Please enter a valid BuckX amount.', 'error');
                return;
            }
            if (paymentTypeValue === 'skillx' && !skillName) {
                showNotification('Please select a skill for SkillX debt.', 'error');
                return;
            }
            if (!timeframeHours || timeframeHours <= 0) {
                showNotification('Please enter a valid timeframe.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('chat_id',        CURRENT_CHAT_ID);
            formData.append('role',           role);
            formData.append('payment_type',   paymentTypeValue);
            formData.append('amount',         amount);
            formData.append('skill_name',     skillName);
            formData.append('timeframe_hours', timeframeHours);

            transactionForm.dataset.submitting = 'true';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';
            }

            fetch(`${URLROOT}/transaction/createOffer`, { method: 'POST', body: formData })
                .then(async r => {
                    const raw = await r.text();

                    try {
                        return JSON.parse(raw);
                    } catch (parseError) {
                        console.error('createOffer returned non-JSON response:', raw);
                        return {
                            success: false,
                            message: raw && raw.trim()
                                ? raw.trim()
                                : 'Unexpected server response while creating offer.'
                        };
                    }
                })
                .then(data => {
                    if (data.success) {
                        showNotification(data.message || 'Offer created successfully.', 'success');
                        closeTransactionModal();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(data.message || 'Failed to create offer.', 'error');
                    }
                })
                .catch((error) => {
                    console.error('createOffer network error:', error);
                    showNotification('Network error while creating offer.', 'error');
                })
                .finally(() => {
                    transactionForm.dataset.submitting = 'false';
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Send Offer';
                    }
                });
        });
    }

    // Report form submit
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const eventId = document.getElementById('reportEventId')?.value;
            const reason  = document.getElementById('disputeReason')?.value.trim();
            if (!eventId || !reason) {
                showNotification('Please provide a reason for the report.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('event_id',       eventId);
            formData.append('action',         'report');
            formData.append('dispute_reason', reason);

            fetch(`${URLROOT}/transaction/verifyCompletion`, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message || 'Issue reported.', 'success');
                        closeReportModal();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(data.message || 'Failed to report issue.', 'error');
                    }
                })
                .catch(() => showNotification('Network error.', 'error'));
        });
    }

    startSessionCountdown();
});

window.addEventListener('beforeunload', function () {
    if (messagePollingInterval) clearInterval(messagePollingInterval);
});
