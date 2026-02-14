<!-- Feedback Modal -->
<div id="feedbackModal" class="modal-overlay" style="display: none;">
    <div class="modal-container feedback-modal">
        <!-- Modal Header -->
        <div class="modal-header">
            <h3 class="modal-title">Performance Feedback</h3>
            <button type="button" class="modal-close" id="closeFeedbackModal" aria-label="Close">&times;</button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <form id="feedbackForm">
                <!-- Hidden Fields -->
                <input type="hidden" id="feedback_user_id" name="user_id">
                <input type="hidden" id="feedback_project_id" name="project_id">

                <!-- Member Info Display -->
                <div class="feedback-member-info" style="margin-bottom: 20px; padding: 15px; background: var(--blue-bg); border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                    <div class="member-avatar-small" id="feedback_member_avatar"></div>
                    <div>
                        <div style="font-weight: 600; color: var(--dark-bg); font-size: 15px;" id="feedback_member_name"></div>
                        <div style="font-size: 13px; color: #666;">Team Member</div>
                    </div>
                </div>

                <!-- Star Rating -->
                <div class="form-group">
                    <label for="feedback_rating" style="display: block; font-weight: 600; margin-bottom: 10px; color: var(--dark-bg);">
                        Rating <span style="color: #ef4444;">*</span>
                    </label>
                    <div class="star-rating" id="starRating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" class="star" title="Excellent - 5 stars">☆</label>
                        
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" class="star" title="Good - 4 stars">☆</label>
                        
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" class="star" title="Average - 3 stars">☆</label>
                        
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" class="star" title="Fair - 2 stars">☆</label>
                        
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" class="star" title="Poor - 1 star">☆</label>
                    </div>
                    <div id="ratingValue" style="margin-top: 8px; font-size: 13px; color: #666; font-weight: 500;"></div>
                </div>

                <!-- Comment/Feedback Text -->
                <div class="form-group">
                    <label for="feedback_comment" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark-bg);">
                        Your Feedback <span style="font-size: 12px; font-weight: 400; color: #666;">(Optional)</span>
                    </label>
                    <textarea 
                        id="feedback_comment" 
                        name="comment" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Share your experience working with this team member..."
                        style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif; resize: vertical; min-height: 100px;"
                    ></textarea>
                    <div style="margin-top: 5px; font-size: 12px; color: #999; text-align: right;">
                        <span id="commentCount">0</span>/500 characters
                    </div>
                </div>

                <!-- Submit Actions -->
                <div class="modal-actions" style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" id="submitFeedbackBtn" style="flex: 1; padding: 12px; border-radius: 8px; font-weight: 600; background: var(--primary-blue); color: white; border: none; cursor: pointer; transition: all 0.3s ease;">
                        Submit Feedback
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelFeedbackBtn" style="flex: 1; padding: 12px; border-radius: 8px; font-weight: 600; background: #e5e7eb; color: var(--dark-bg); border: none; cursor: pointer; transition: all 0.3s ease;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
