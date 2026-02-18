<!-- Report Feedback Modal -->
<div id="reportModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:500px;">
        <!-- Modal Header -->
        <div class="modal-header">
            <h2 style="display:flex;align-items:center;gap:10px;margin:0;">
                <i class="ph ph-flag" style="color:#e74c3c;font-size:28px;"></i>
                Report Feedback
            </h2>
            <button type="button" class="modal-close" onclick="closeReportModal()">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <form id="reportFeedbackForm">
                <input type="hidden" id="reportFeedbackId" name="feedback_id">

                <!-- Warning Message -->
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:15px;margin-bottom:20px;display:flex;gap:12px;">
                    <i class="ph ph-warning" style="color:#856404;font-size:24px;flex-shrink:0;"></i>
                    <div style="font-size:13px;color:#856404;line-height:1.5;">
                        <strong>Before reporting:</strong> Please ensure the feedback genuinely violates our guidelines. False reports may result in account restrictions.
                    </div>
                </div>

                <!-- Reason Selection -->
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="reportReason" style="display:block;margin-bottom:8px;color:#333;font-weight:600;font-size:14px;">
                        Why are you reporting this? <span style="color:#e74c3c;">*</span>
                    </label>
                    <select id="reportReason" name="reason" required style="width:100%;padding:12px;border:2px solid #e1eefb;border-radius:8px;font-size:14px;background:white;cursor:pointer;">
                        <option value="">Select a reason...</option>
                        <option value="abusive">🚫 Abusive/Harassment</option>
                        <option value="fake">🎭 Fake/False Information</option>
                        <option value="spam">📧 Spam</option>
                        <option value="inappropriate">⚠️ Inappropriate Content</option>
                        <option value="other">❓ Other</option>
                    </select>
                </div>

                <!-- Additional Details (Optional) -->
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="reportDetails" style="display:block;margin-bottom:8px;color:#333;font-weight:600;font-size:14px;">
                        Additional Details <span style="color:#999;font-weight:400;">(Optional)</span>
                    </label>
                    <textarea id="reportDetails" name="details" rows="4" placeholder="Provide more context about why this feedback is problematic..." style="width:100%;padding:12px;border:2px solid #e1eefb;border-radius:8px;font-size:14px;resize:vertical;font-family:inherit;"></textarea>
                    <div style="font-size:12px;color:#666;margin-top:5px;">
                        Maximum 500 characters
                    </div>
                </div>

                <!-- Info Box -->
                <div style="background:#e1eefb;border-radius:8px;padding:15px;margin-bottom:20px;">
                    <div style="font-size:13px;color:#6583aa;line-height:1.6;">
                        <i class="ph ph-info" style="font-size:18px;"></i>
                        <strong>What happens next?</strong><br>
                        Our moderation team will review your report within 24-48 hours. You'll be notified of the outcome.
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" onclick="closeReportModal()" style="padding:12px 24px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;color:#666;cursor:pointer;transition:all 0.2s;">
                        Cancel
                    </button>
                    <button type="submit" id="submitReportBtn" style="padding:12px 24px;background:#e74c3c;border:none;border-radius:8px;font-weight:600;color:white;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:8px;">
                        <i class="ph ph-paper-plane-tilt"></i>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
