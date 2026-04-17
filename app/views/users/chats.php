<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/chats.css">

<main class="site-main">
<div class="dashboard-container">
   <div class="dashboard-main">
      
       <div class="chats-page">
           <div class="page-header">
               <h1>Your Sessions</h1>
               <p>Connect and communicate with your matches</p>
           </div>

           <div class="chats-layout">
               <!-- Chat List Sidebar -->
               <div class="chat-list">
                   <div class="chat-search">
                       <input type="text" id="searchChats" placeholder="Search conversations..." class="search-input">
                   </div>
                   <div class="conversations">
                       <?php if (!empty($data['allChats'])): ?>
                           <?php foreach ($data['allChats'] as $chat): ?>
                               <a class="chat-item <?= $chat['unread'] ? 'unread' : ''; ?> <?= isset($data['partnerId']) && $chat['partner_id'] == $data['partnerId'] ? 'active' : ''; ?>"
                                  data-chat-id="<?= $chat['id']; ?>"
                                  data-partner-id="<?= $chat['partner_id']; ?>"
                                  href="<?= URLROOT ?>/chat/user/<?= $chat['partner_id']; ?>"
                                  style="text-decoration: none; color: inherit;">
                                   <div class="chat-avatar">
                                       <?php if (!empty($chat['avatar']) && strpos($chat['avatar'], 'uploads/') === 0): ?>
                                           <img src="<?= URLROOT ?>/<?= htmlspecialchars($chat['avatar']) ?>" alt="Avatar">
                                       <?php else: ?>
                                           <?= htmlspecialchars($chat['avatar']); ?>
                                       <?php endif; ?>
                                   </div>
                                   <div class="chat-info">
                                       <div class="chat-header-row">
                                           <h3 class="chat-name"><?= htmlspecialchars($chat['name']); ?></h3>
                                           <span class="chat-time"><?= htmlspecialchars($chat['time']); ?></span>
                                       </div>
                                       <div class="chat-preview-row">
                                           <p class="chat-preview"><?= htmlspecialchars($chat['lastMessage']); ?></p>
                                           <?php if ($chat['unread']): ?>
                                               <span class="unread-badge"><?= $chat['unreadCount']; ?></span>
                                           <?php endif; ?>
                                       </div>
                                   </div>
                               </a>
                           <?php endforeach; ?>
                       <?php else: ?>
                           <div class="no-chats">
                               <p>No conversations yet</p>
                               <small>Connect with matches to start a session!</small>
                           </div>
                       <?php endif; ?>
                   </div>
               </div>

               <!-- Chat Window -->
               <div class="chat-window" id="chatWindow">
                   <?php if (isset($data['partnerId'])): ?>
                       <!-- Active Chat Header -->
                       <div class="chat-header">
                           <div class="chat-partner-info">
                               <div class="partner-avatar">
                                   <?php if (!empty($data['partnerAvatar']) && strpos($data['partnerAvatar'], 'uploads/') === 0): ?>
                                       <img src="<?= URLROOT ?>/<?= htmlspecialchars($data['partnerAvatar']) ?>" alt="Avatar">
                                   <?php else: ?>
                                       <?= htmlspecialchars($data['partnerAvatar']); ?>
                                   <?php endif; ?>
                               </div>
                               <div>
                                   <h3><?= htmlspecialchars($data['partnerName']); ?></h3>
                                   <span class="status-online">Active</span>
                               </div>
                           </div>

                           <div class="chat-actions">
                               <?php if (!isset($data['activeTransaction'])): ?>
                                   <div class="chat-action-item">
                                       <button class="btn-icon" onclick="openTransactionModal()" title="Create Transaction Offer">
                                           <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                               <circle cx="12" cy="12" r="10"></circle>
                                               <path d="M12 6v12M6 12h12"></path>
                                           </svg>
                                       </button>
                                       <span class="chat-action-label">Start Session</span>
                                   </div>
                               <?php endif; ?>
                               <div class="chat-action-item">
                                   <button class="btn-icon" onclick="viewPartnerProfile(<?= $data['partnerId']; ?>)" title="View Profile">
                                       <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                           <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                           <circle cx="12" cy="7" r="4"></circle>
                                       </svg>
                                   </button>
                                   <span class="chat-action-label">Profile</span>
                               </div>
                           </div>
                       </div>

                       <!-- Transaction Status Banner -->
                       <?php if (isset($data['activeTransaction'])): ?>
                           <?php
                           $tx = $data['activeTransaction'];
                           $status = $tx['status'];
                           $userRole = $tx['user_role'];

                           $timeframeLabel = '';
                           if (!empty($tx['timeframe_hours'])) {
                               $timeframeLabel = ($tx['timeframe_hours'] % 24 === 0)
                                   ? ($tx['timeframe_hours'] / 24) . ' day(s)'
                                   : $tx['timeframe_hours'] . ' hour(s)';
                           }
                           ?>
                           <div class="transaction-banner" id="transactionBanner"
                                data-transaction='<?= json_encode($tx); ?>'>

                               <?php if ($status === 'pending_learner' || $status === 'pending_teacher'): ?>
                                   <div class="transaction-pending">
                                       <div class="transaction-info">
                                           <strong>Transaction Offer</strong>
                                           <p>
                                               <?php if ($tx['payment_type'] === 'buckx'): ?>
                                                   <?= number_format($tx['amount'], 2); ?> BuckX
                                               <?php else: ?>
                                                   <?= $tx['skill_debt_hours']; ?> hours of <?= htmlspecialchars($tx['skill_name']); ?>
                                               <?php endif; ?>
                                               &bull; <?= htmlspecialchars($timeframeLabel ?: 'Unknown'); ?> timeframe
                                           </p>
                                       </div>
                                       <div class="transaction-actions">
                                           <?php if ($tx['is_creator']): ?>
                                               <span class="transaction-status">Waiting for response...</span>
                                           <?php else: ?>
                                               <button class="btn-accept" onclick="respondToOffer(<?= $tx['id']; ?>, 'accept')">Accept</button>
                                               <button class="btn-reject" onclick="respondToOffer(<?= $tx['id']; ?>, 'reject')">Reject</button>
                                           <?php endif; ?>
                                       </div>
                                   </div>

                               <?php elseif ($status === 'active'): ?>
                                   <div class="transaction-active">
                                       <div class="transaction-info">
                                           <strong>Active Session</strong>
                                           <p>
                                               <?php if ($tx['payment_type'] === 'buckx'): ?>
                                                   <?= number_format($tx['amount'], 2); ?> BuckX
                                               <?php else: ?>
                                                   <?= $tx['skill_debt_hours']; ?> hours
                                               <?php endif; ?>
                                               &bull; <?= htmlspecialchars($timeframeLabel); ?> session
                                           </p>
                                           <p id="sessionCountdown"
                                              data-expires-at="<?= htmlspecialchars($tx['expires_at']); ?>"
                                              data-expires-at-unix="<?= !empty($tx['expires_at']) ? strtotime($tx['expires_at']) : '' ?>"
                                              data-server-now-unix="<?= time() ?>">
                                               Calculating remaining time...
                                           </p>
                                       </div>
                                       <div class="transaction-actions">
                                           <?php if ($userRole === 'teacher'): ?>
                                               <button class="btn-accept" onclick="markCompleted(<?= $tx['id']; ?>)">Mark Completed</button>
                                               <button class="btn-leave" onclick="leaveLesson(<?= $tx['id']; ?>)">Leave</button>
                                           <?php else: ?>
                                               <button class="btn-leave" onclick="leaveLesson(<?= $tx['id']; ?>)">Leave</button>
                                           <?php endif; ?>
                                       </div>
                                   </div>

                               <?php elseif ($status === 'teacher_completed'): ?>
                                   <div class="transaction-verification">
                                       <div class="transaction-info">
                                           <strong>Session Completed by Teacher</strong>
                                           <p>Please verify that the session was completed successfully.</p>
                                       </div>
                                       <div class="transaction-actions">
                                           <?php if ($userRole === 'learner'): ?>
                                               <button class="btn-accept" onclick="verifyCompletion(<?= $tx['id']; ?>, 'agreed')">Confirm &amp; Pay</button>
                                               <button class="btn-report" onclick="openReportModal(<?= $tx['id']; ?>)">Report Issue</button>
                                           <?php else: ?>
                                               <span class="transaction-status">Waiting for learner verification...</span>
                                           <?php endif; ?>
                                       </div>
                                   </div>
                               <?php endif; ?>
                           </div>
                       <?php endif; ?>

                       <!-- Messages -->
                       <div class="messages-container" id="messagesContainer">
                           <div class="loading-messages">Loading messages...</div>
                       </div>

                       <!-- Message Input -->
                       <div class="message-input-container">
                           <form id="messageForm" onsubmit="sendMessage(event)">
                               <input type="hidden" id="chatId" value="<?= $data['chatId']; ?>">
                               <input type="text"
                                      id="messageInput"
                                      placeholder="Type a message..."
                                      autocomplete="off"
                                      required>
                               <button type="submit" class="btn-send">
                                   <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                       <line x1="22" y1="2" x2="11" y2="13"></line>
                                       <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                   </svg>
                                   Send
                               </button>
                           </form>
                       </div>
                   <?php else: ?>
                       <!-- Empty State -->
                       <div class="empty-state">
                           <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                               <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                           </svg>
                           <h2>Select a conversation</h2>
                           <p>Choose a chat from the list to start messaging</p>
                       </div>
                   <?php endif; ?>
               </div>
           </div>
       </div>
   </div>
</div>
</main>

<!-- ============================================================
     Transaction Offer Modal
     Adapts based on MATCH_TYPE passed from ChatController:
       mutual         → show role selector (user picks teacher/learner per session)
       single / multi → hide role selector, role is predetermined and auto-submitted
     ============================================================ -->
<div class="modal-overlay" id="transactionModal" style="display: none;">
   <div class="modal-container transaction-modal">
       <div class="modal-header">
           <h3>Create Transaction Offer</h3>
           <button class="modal-close" onclick="closeTransactionModal()">×</button>
       </div>

       <form id="transactionForm" class="modal-body">

           <?php
           $matchType  = $data['matchType']  ?? 'single';
           $matchSkill = $data['matchSkill'] ?? '';
           $matchDir   = $data['matchDir']   ?? 'teacher'; // 'teacher'|'learner' for single/multi
                                                           // or learn-skill name for mutual
           $isMutual   = ($matchType === 'mutual');
           $availableSkills = $data['availableSkills'] ?? [];
           $isSingleFixedSkill = ($matchType === 'single' && !empty($matchSkill));
           ?>

           <!-- Role selector: only shown for mutual matches -->
           <?php if ($isMutual): ?>
           <div class="form-group" id="roleGroup">
               <label for="transactionRole">Your Role for This Session</label>
               <select name="role" id="transactionRole" required>
                   <option value="">Select your role</option>
                   <?php if (!empty($matchSkill)): ?>
                       <option value="teacher">
                           I'm teaching <?= htmlspecialchars(ucwords(str_replace('-', ' ', $matchSkill))); ?> (I receive payment)
                       </option>
                   <?php else: ?>
                       <option value="teacher">I'm teaching (I receive payment)</option>
                   <?php endif; ?>
                   <?php if (!empty($matchDir)): ?>
                       <option value="learner">
                           I'm learning <?= htmlspecialchars(ucwords(str_replace('-', ' ', $matchDir))); ?> (I pay)
                       </option>
                   <?php else: ?>
                       <option value="learner">I'm learning (I pay)</option>
                   <?php endif; ?>
               </select>
               <small>Mutual matches let you switch roles each session.</small>
           </div>
           <?php else: ?>
           <!-- Single / multi: role is fixed — hidden input, label shown for context -->
           <input type="hidden" name="role" id="transactionRoleHidden" value="<?= htmlspecialchars($matchDir); ?>">
           <div class="form-group">
               <label>Your Role</label>
               <div class="role-fixed-display">
                   <?php if ($matchDir === 'teacher'): ?>
                       <span class="role-badge role-teacher">🎓 You are the Teacher</span>
                       <small>You will receive payment for this session.</small>
                   <?php else: ?>
                       <span class="role-badge role-learner">📚 You are the Learner</span>
                       <small>You will pay for this session.</small>
                   <?php endif; ?>
               </div>
           </div>
           <?php endif; ?>

           <div class="form-group">
               <label for="paymentType">Payment Type</label>
               <select name="payment_type" id="paymentType" required>
                   <option value="">Select payment type</option>
                   <option value="buckx">BuckX (Virtual Currency)</option>
                   <option value="skillx">SkillX (Skill Debt)</option>
               </select>
           </div>

           <div class="form-group" id="buckxAmountGroup" style="display: none;">
               <label for="buckxAmount">BuckX Amount</label>
               <input type="number" name="amount" id="buckxAmount" step="0.01" min="0.01">
               <small>Your balance: <strong><?= number_format($data['buckxBalance'], 2); ?> BuckX</strong></small>
           </div>

           <div class="form-group" id="skillxGroup" style="display: none;">
               <label<?= $isSingleFixedSkill ? '' : ' for="skillName"' ?>>Skill Name</label>
               <?php if ($isSingleFixedSkill): ?>
                   <input type="hidden" name="skill_name" id="skillNameHidden" value="<?= htmlspecialchars($matchSkill) ?>">
                   <div class="role-fixed-display">
                       <span class="role-badge role-learner">
                           <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $matchSkill))) ?>
                       </span>
                       <small>This matched skill will be used automatically for SkillX debt.</small>
                   </div>
               <?php else: ?>
                   <select name="skill_name" id="skillName">
                       <option value="">Select a skill</option>
                       <?php foreach ($availableSkills as $skill): ?>
                           <?php $skillNameValue = $skill['skill_name'] ?? ''; ?>
                           <option value="<?= htmlspecialchars($skillNameValue) ?>"
                               <?= ($matchSkill === $skillNameValue) ? 'selected' : '' ?>>
                               <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $skillNameValue))) ?>
                           </option>
                       <?php endforeach; ?>
                   </select>
                   <small>Select the skill that will be recorded as the SkillX debt.</small>
               <?php endif; ?>
           </div>

           <div class="form-group">
               <label for="timeframeValue">Timeframe</label>
               <div style="display:flex; gap:10px;">
                   <input type="number" id="timeframeValue" required min="1" value="24">
                   <select id="timeframeUnit" required>
                       <option value="hours">Hours</option>
                       <option value="days">Days</option>
                   </select>
               </div>
               <small>How long until the session must be completed</small>
           </div>

           <div class="modal-actions">
               <button type="button" class="btn-secondary" onclick="closeTransactionModal()">Cancel</button>
               <button type="submit" class="btn-primary">Send Offer</button>
           </div>
       </form>
   </div>
</div>

<!-- Report Issue Modal -->
<div class="modal-overlay" id="reportModal" style="display: none;">
   <div class="modal-container">
       <div class="modal-header">
           <h3>Report Issue</h3>
           <button class="modal-close" onclick="closeReportModal()">×</button>
       </div>
       <form id="reportForm" class="modal-body">
           <input type="hidden" id="reportEventId">
           <div class="form-group">
               <label>Describe the issue</label>
               <textarea name="dispute_reason" id="disputeReason" rows="4" required
                         placeholder="Please explain what went wrong..."></textarea>
           </div>
           <p style="color: var(--dark-bg); opacity: 0.7; font-size: 0.9rem; margin: 1rem 0;">
               Your payment will be frozen until an admin reviews this dispute.
           </p>
           <div class="modal-actions">
               <button type="button" class="btn-secondary" onclick="closeReportModal()">Cancel</button>
               <button type="submit" class="btn-danger">Submit Report</button>
           </div>
       </form>
   </div>
</div>

<script>
    const URLROOT        = '<?= URLROOT ?>';
    const CURRENT_CHAT_ID = <?= isset($data['chatId']) ? (int)$data['chatId'] : 'null' ?>;
    const CURRENT_USER_ID = <?= (int)$_SESSION['user_id'] ?>;
    const PARTNER_ID      = <?= isset($data['partnerId']) ? (int)$data['partnerId'] : 'null' ?>;
    // Match context — used by chats.js to auto-set role for single/multi
    const MATCH_TYPE      = '<?= htmlspecialchars($data['matchType'] ?? 'single') ?>';
    const MATCH_DIR       = '<?= htmlspecialchars($data['matchDir']  ?? 'teacher') ?>';
</script>
<script src="<?= URLROOT ?>/assets/js/chats.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
