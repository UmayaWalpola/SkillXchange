# Quiz Manager System - Complete Workflow Summary

## System Architecture

The quiz system now has a complete workflow: **Creation → Storage → Display → Playback → Scoring**

### 1. Quiz Creation (Quiz Manager)

**Flow**: Quiz Manager creates quiz → Questions stored → Published to users

**Files Modified**:
- `app/controllers/QuizmanagerController.php` - `save()` method accepts JSON payload
- `app/models/quiz.php` - `createQuiz()` and `addQuestion()` methods
- `public/assets/js/quizcreate.js` - Frontend question builder

**What happens**:
1. Quiz manager fills in title, difficulty, duration, description
2. Manager adds 1-20 questions with 4 options each
3. Manager selects one correct answer per question
4. On publish, frontend sends JSON to `/quizmanager/save`
5. Backend creates quiz record and stores all questions + options

### 2. Quiz Storage (Database)

**Tables Used**:
- `quizzes` - Main quiz metadata
  - `id`, `title`, `description`, `difficulty_level`, `duration`, `status`, `total_questions`, `manager_id`, `created_at`
- `quiz_questions` - Questions for each quiz
  - `id`, `quiz_id`, `question_text`, `question_order`
- `quiz_options` - Answer options for each question
  - `id`, `question_id`, `option_letter`, `option_text`, `is_correct`

### 3. Quiz Display (User Dashboard)

**Flow**: Users see published quizzes → Can filter, search, save for later

**Files**:
- `app/views/users/quiz.php` - Displays quiz list
- `public/assets/js/quiz.js` - Quiz listing and filtering logic
- `app/controllers/UserdashboardController.php` - `quiz()` method

**What happens**:
1. User navigates to Quizzes section
2. Backend calls `quiz->getQuizzesForUser()` which queries active quizzes
3. Returns: Quiz title, difficulty, question count, duration, reward amount, user's previous score (if any)
4. Frontend renders quiz cards with Start/Retake buttons
5. User can save quiz for later or start immediately

### 4. Quiz Playback (Taking Quiz)

**Flow**: User starts quiz → Questions displayed one per screen → Submit answers

**Files**:
- `app/views/users/take_quiz.php` - Quiz interface
- `public/assets/js/take_quiz.js` - Question rendering and answer tracking
- `app/controllers/UserdashboardController.php` - `takeQuiz()` and `submitQuiz()` methods

**What happens**:
1. User clicks "Start Quiz"
2. Backend calls `quiz->startAttempt()` - creates attempt record
3. Quiz questions loaded and displayed one per screen
4. User selects option for each question
5. Navigation between questions (Previous/Next)
6. Submit button appears on last question
7. User submits all answers

### 5. Scoring & Results

**Flow**: Submit answers → Calculate score → Award badge & reward → Show results

**Process**:
1. Backend receives answers via POST to `/userdashboard/submitQuiz`
2. Compare user answers against correct answers in database
3. Calculate percentage score
4. Check if score >= 70 (passing threshold)
5. If passed: Award badge and Buckx reward
6. Return results to frontend
7. Display score, correct/incorrect count, earned badge

## API Endpoints

### Quiz Manager
- `POST /quizmanager/save` - Save quiz (requires JSON payload with questions)
- `GET /quizmanager/getQuizzes` - Get manager's quizzes (JSON response)
- `GET /quizmanager` - Dashboard view
- `GET /quizmanager/create` - Create form

### Quiz Player
- `GET /userdashboard/quiz` - List available quizzes
- `GET /userdashboard/takeQuiz/{id}` - Start quiz
- `POST /userdashboard/submitQuiz` - Submit answers
- `POST /userdashboard/toggleSaveQuiz` - Save/unsave quiz

## Data Flow

```
Quiz Manager
    ↓
[quizcreate.js] builds quiz JSON
    ↓
POST /quizmanager/save with JSON
    ↓
QuizmanagerController->save()
    ↓
quiz->createQuiz() + addQuestion()
    ↓
INSERT INTO quizzes, quiz_questions, quiz_options
    ↓
Quiz stored with status='active'
    ↓
    ↓
User Dashboard
    ↓
GET /userdashboard/quiz
    ↓
UserdashboardController->quiz()
    ↓
quiz->getQuizzesForUser()
    ↓
SELECT * FROM quizzes WHERE status='active'
    ↓
[quiz.js] renders quiz cards
    ↓
User clicks "Start Quiz"
    ↓
GET /userdashboard/takeQuiz/{id}
    ↓
quiz->startAttempt() creates attempt record
    ↓
[take_quiz.js] loads questions
    ↓
quiz->getQuizQuestions() fetches all questions + options
    ↓
User answers all questions
    ↓
POST /userdashboard/submitQuiz with answers
    ↓
UserdashboardController->submitQuiz()
    ↓
Compare answers vs quiz_options.is_correct
    ↓
Calculate score and award if passed
    ↓
quiz->completeAttempt()
    ↓
UPDATE user_quiz_attempts with score, passed status
    ↓
Return results to [take_quiz.js]
    ↓
Display score, badge earned, Buckx reward
```

## Key Fixes Made

1. **Field Name Alignment**
   - Controller now sends `question` field (not `text`)
   - Mapped to `question_text` in database
   - Mapped `correctAnswer` → `correct` in frontend

2. **Question Storage**
   - Fixed `addQuestion()` to properly store 4 options with correct answer
   - Proper index mapping (0=A, 1=B, 2=C, 3=D)

3. **Quiz Manager Dashboard**
   - Now fetches real quizzes from `quiz->getAllQuizzesForManager()`
   - Displays actual quiz data instead of mock data
   - Added `getQuizzes()` endpoint for JSON response

4. **Answer Submission**
   - Properly normalizes answers format
   - Compares selected_answer index against database is_correct flag
   - Calculates percentage score
   - Awards badges and Buckx based on difficulty level

## Testing Workflow

To test the complete system:

1. **Login as Quiz Manager**
   - Go to Quiz Manager Dashboard
   - Click "Create New Quiz"

2. **Create a Quiz**
   - Enter title: "JavaScript Basics"
   - Select difficulty: "Beginner"
   - Set duration: 30 minutes
   - Add description: "Test your JavaScript knowledge"

3. **Add Questions**
   - Click "Add Question"
   - Question: "What is the correct way to declare a variable in JavaScript?"
   - Options:
     - A: var x = 5;
     - B: variable x = 5;
     - C: v x = 5;
     - D: declare x = 5;
   - Correct answer: A
   - Add at least 3-5 questions

4. **Publish Quiz**
   - Click "Publish Quiz"
   - Confirm the action
   - Should redirect to dashboard with success message

5. **Login as Regular User**
   - Go to Dashboard
   - Click "Quizzes" section
   - You should see the published quiz

6. **Take Quiz**
   - Click "Start Quiz"
   - Answer all questions
   - Click "Submit Quiz"
   - View results with score and badge (if passed)

## Expected Results

✅ Quiz saved to database with all questions
✅ Quiz appears in user's quiz list
✅ User can start and complete quiz
✅ Score calculated correctly (out of 100%)
✅ Badge awarded if score >= 70%
✅ Buckx reward credited (10 for Beginner, 20 for Intermediate, 30 for Expert)
✅ Quiz attempt saved for retakes
✅ Previous scores visible to user
