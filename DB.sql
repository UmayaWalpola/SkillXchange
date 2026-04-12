-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 09:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillxchange`
--

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT '?',
  `type` enum('quiz','project','community','skill') DEFAULT 'quiz',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `type`, `created_at`) VALUES
(1, 'Quiz Master', 'Complete 10 quizzes', '🎓', 'quiz', '2026-02-18 19:13:43'),
(2, 'Perfect Score', 'Get 100% on any quiz', '💯', 'quiz', '2026-02-18 19:13:43'),
(3, 'Quick Learner', 'Complete a quiz in under 5 minutes', '⚡', 'quiz', '2026-02-18 19:13:43'),
(4, 'Web Developer', 'Complete all web development quizzes', '💻', 'quiz', '2026-02-18 19:13:43'),
(5, 'Data Expert', 'Complete all data science quizzes', '📊', 'quiz', '2026-02-18 19:13:43'),
(6, 'Early Adopter', 'Platform achievement badge', '🌟', 'community', '2026-04-10 10:02:29');

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `privacy` enum('public','private') DEFAULT 'public',
  `rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rules`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `communities`
--

INSERT INTO `communities` (`id`, `name`, `description`, `privacy`, `rules`, `tags`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Web Developers Hub', 'A community for web develop', 'public', '[]', '[\"web\",\"development\",\"coding\"]', 'active', 1, '2024-01-15 10:00:00', '2025-10-24 11:07:02'),
(5, 'Online Learning Community', 'i', 'public', '[]', '[\"education\",\"learning\",\"courses\"]', 'active', 1, '2024-01-25 10:00:00', '2025-10-24 10:26:46'),
(9, 'Cloud Computing', 'hi', 'private', '[]', '[]', 'active', 18, '2025-10-24 01:17:14', '2025-10-24 12:05:30');

-- --------------------------------------------------------

--
-- Table structure for table `community_members`
--

CREATE TABLE `community_members` (
  `id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','moderator','member') DEFAULT 'member',
  `joined_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_members`
--

INSERT INTO `community_members` (`id`, `community_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 1, 1, 'admin', '2024-01-15 10:00:00'),
(2, 1, 2, 'member', '2024-01-16 10:00:00'),
(7, 5, 1, 'admin', '2024-01-25 10:00:00'),
(8, 1, 41, 'member', '2026-04-02 13:16:03');

-- --------------------------------------------------------

--
-- Table structure for table `content_reports`
--

CREATE TABLE `content_reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `content_type` enum('post','chat_message') NOT NULL,
  `content_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_reports`
--

INSERT INTO `content_reports` (`id`, `reporter_id`, `content_type`, `content_id`, `reason`, `description`, `status`, `created_at`) VALUES
(1, 37, 'post', 1, 'Inappropriate content', NULL, 'pending', '2025-12-18 15:54:21');

-- --------------------------------------------------------

--
-- Table structure for table `exchanges`
--

CREATE TABLE `exchanges` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchanges`
--

INSERT INTO `exchanges` (`id`, `requester_id`, `receiver_id`, `skill_id`, `status`, `created_at`) VALUES
(1, 41, 47, 1, 'active', '2026-02-20 07:36:09'),
(2, 41, 50, 1, 'active', '2026-04-02 07:36:50'),
(3, 41, 45, 1, 'active', '2026-04-02 07:39:33'),
(4, 41, 48, 1, 'active', '2026-04-02 07:43:05');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_reports`
--

CREATE TABLE `feedback_reports` (
  `id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` enum('abusive','fake','spam','inappropriate','other') NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed','action_taken') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_reports`
--

INSERT INTO `feedback_reports` (`id`, `feedback_id`, `reporter_id`, `reason`, `details`, `status`, `created_at`, `reviewed_by`, `reviewed_at`, `admin_notes`) VALUES
(2, 2, 48, 'fake', 'Automated test fake', 'pending', '2026-04-01 14:43:28', NULL, NULL, NULL),
(3, 3, 47, 'fake', 'Fresh fake report test', 'pending', '2026-04-01 14:47:01', NULL, NULL, NULL),
(4, 2, 46, 'abusive', 'Post-fix success check', 'reviewed', '2026-04-01 14:48:13', 7, '2026-04-04 12:25:30', ''),
(6, 3, 44, 'abusive', 'Abusive reason success verification', 'pending', '2026-04-01 14:49:51', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `project_id`, `task_id`, `is_read`, `created_at`) VALUES
(1, 7, 'feedback_report', 'A feedback has been reported as fake. Please review.', NULL, NULL, 0, '2026-04-01 14:49:23'),
(2, 7, 'feedback_report', 'A feedback has been reported as abusive. Please review.', NULL, NULL, 0, '2026-04-01 14:49:51'),
(3, 41, 'task_assigned', 'You have been assigned a new task: Manage Team Members', 18, 1, 0, '2026-04-10 10:24:42'),
(5, 60, 'task_removed', 'A task assigned to you was removed: Build Baseline Prediction Model', 21, NULL, 0, '2026-04-11 06:55:45'),
(6, 57, 'task_update', 'Task \'Build Baseline Prediction Model\' status changed to In Progress', 21, 2, 0, '2026-04-11 06:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `community_id`, `user_id`, `title`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Welcome to Web Developers Hub', 'This is the first post in our community!', '2024-01-15 11:00:00', NULL),
(2, 1, 2, 'Learning React', 'Any tips for learning React?', '2024-01-17 14:00:00', NULL),
(5, 5, 1, 'Best Online Courses', 'What courses do you recommend?', '2024-01-26 10:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('web','mobile','data','design','other') NOT NULL,
  `status` enum('active','in-progress','completed','cancelled') DEFAULT 'active',
  `required_skills` text NOT NULL,
  `max_members` int(11) NOT NULL DEFAULT 5,
  `current_members` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `organization_id`, `name`, `description`, `category`, `status`, `required_skills`, `max_members`, `current_members`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(7, 37, 'Zcode', 'ZCode is a mobile application designed to simplify and streamline the process of coding, learning, and managing software development tasks directly from a smartphone. The project focuses on providing developers, students, and tech enthusiasts with a fast, lightweight, and user-friendly coding environment on mobile devices.\r\n\r\nZCode supports essential development features such as code editing, syntax highlighting, real-time previews, and project management tools. The main goal of the app is to allow users to write, test, and manage code on the go without needing a desktop computer. The project also integrates cloud support, enabling users to save files online and access their work from multiple devices.\r\n\r\nThe ZCode Mobile Development Project aims to deliver a productive mobile coding experience, improve accessibility to programming tools, and enable learners to practice coding anytime, anywhere.', 'mobile', 'in-progress', 'Flutter , NodeJs', 6, 0, '2025-11-17', '2026-10-20', '2025-11-17 12:17:49', '2025-12-08 08:11:30'),
(8, 37, 'CodeCollab Hub', 'mplement a module to create and manage community skill-sharing events, allowing users to RSVP and join online/offline workshops.', 'web', 'completed', 'HTML5 , CSS3, JavaScript, PHP, MySql', 5, 1, '2025-11-28', '2026-01-03', '2025-11-17 12:33:12', '2025-11-18 10:37:23'),
(9, 37, 'SkillMentor', 'A system to connect learners with expert mentors in various skills.', 'data', 'cancelled', 'Python, R, SQL, Machine Learning, Data Visualization, Pandas, NumPy, Scikit-learn, Matplotlib, Tableau', 10, 0, '2026-12-17', '2026-12-31', '2025-11-17 12:34:15', '2025-11-17 12:34:15'),
(10, 37, 'LearnLab', 'An interactive platform for project-based skill learning and exercises.', 'design', 'completed', 'UI/UX Design, Adobe Photoshop, Adobe Illustrator, Figma, Sketch, Wireframing, Prototyping, Interaction Design, Graphic Design, Color Theory', 8, 0, '2025-11-18', '2028-11-18', '2025-11-17 12:35:27', '2025-11-17 12:35:27'),
(13, 37, 'kithsara project', 'Pretty software project', 'web', 'active', 'HTML5 , CSS3, JavaScript, PHP, MySql', 7, 2, '2025-11-29', '2026-10-18', '2025-11-18 10:55:15', '2026-02-18 18:45:19'),
(14, 37, 'Devinda Web Project', 'This project is a modern and responsive web application designed to provide users with an easy-to-use and interactive online experience. It includes key features such as user authentication, dynamic content display, and a well-structured interface built with best web development practices. The system ensures smooth navigation, mobile-friendly layouts, and efficient data handling through backend integration. The project focuses on scalability, maintainability, and clean UI/UX design to offer a seamless workflow for both users and administrators.', 'web', 'active', 'HTML5 , CSS3, JavaScript, PHP, MySql', 7, 0, '2025-11-20', '2026-10-18', '2025-11-18 17:11:40', '2025-11-18 17:11:51'),
(15, 37, 'SmartConnect Mobile App (PS software)', 'SmartConnect is a modern mobile application designed to help users connect, collaborate, and share skills effortlessly. The app provides a clean and responsive interface with real-time interactions, profile management, skill listings, messaging, and project collaboration features.\r\nIt aims to deliver fast performance, smooth navigation, and a user-friendly experience across Android and iOS platforms.', 'mobile', 'active', 'Flutter / Dart, React Native, Java / Kotlin ,Git/GitHub', 10, 1, '2025-11-29', '2025-12-31', '2025-11-19 19:03:48', '2025-11-19 19:51:05'),
(16, 37, 'Online Bookstore Management System', 'A web application that allows users to browse, search, and purchase books online with secure payment integration.', 'web', 'active', 'PHP, MySQL, Laravel, HTML, CSS, JavaScript', 5, 0, '2025-11-23', '2025-11-29', '2025-11-23 10:28:30', '2025-11-23 10:28:30'),
(17, 37, 'Kithsara Mobile App 2', 'The Mobile App Development Project is focused on designing and building a fully–functional, user-friendly, and efficient mobile application tailored to meet specific user needs. This project involves creating a high-quality mobile solution that delivers seamless performance, attractive UI/UX design, and practical features that solve real-world problems.\r\n\r\nThe application will be developed using modern mobile technologies and frameworks, ensuring cross-platform compatibility, scalability, and long-term maintainability. Throughout the project, industry-best practices such as version control, clean architecture, responsive design, and secure coding standards will be followed.\r\n\r\nKey project tasks include requirement gathering, designing wireframes, developing core features, integrating APIs, testing for bugs, and finally deploying the app to platforms such as Google Play Store or Apple App Store. The project also aims to provide an admin or backend system if required, enabling data management and real-time updates.\r\n\r\nOverall, this mobile app development project will deliver a high-quality, modern application that enhances the user experience, supports business goals, and ensures continuous improvement based on user feedback.', 'mobile', 'active', 'React Native , Flutter, JavaScript / TypeScript  , HTML & CSS , UI/UX Design , API Integration , RESTful API Development , Node.js , Express.js ,   MongoDB / MySQL,  Firebase Services', 10, 0, '2025-12-30', '2026-05-06', '2025-12-06 09:51:12', '2025-12-06 09:52:46'),
(18, 52, 'SkillX Web Platform', 'A collaborative web-based platform where users can learn, teach, and exchange skills. This project includes user authentication, skill matching, project collaboration, feedback system, and reporting features.', 'web', 'active', 'HTML, CSS, JavaScript, PHP, MySQL', 5, 0, '2026-04-10', '2026-04-30', '2026-04-10 10:06:23', '2026-04-11 06:12:42'),
(19, 56, 'SkillBridge Web Portal', 'A responsive web portal for skill sharing with auth, profile, and project board.', 'web', 'active', 'Web Development, Frontend Frameworks', 8, 0, '2026-04-15', '2026-08-30', '2026-04-11 06:40:23', '2026-04-11 06:40:23'),
(20, 56, 'QuickTutor Mobile App', 'Mobile app for booking quick tutor sessions and progress tracking.', 'mobile', 'active', 'Mobile App Development, Backend Development', 6, 0, '2026-04-20', '2026-09-10', '2026-04-11 06:40:23', '2026-04-11 06:40:23'),
(21, 57, 'InsightFlow Analytics', 'Data analytics dashboard with prediction and visualization modules.', 'data', 'active', 'Data Science, Data Analysis & Visualization, AI and ML', 7, 1, '2026-04-18', '2026-10-01', '2026-04-11 06:40:23', '2026-04-11 06:50:36'),
(22, 57, 'SecureCloudOps Monitor', 'Monitoring and alerting platform for cloud infra and security.', 'other', 'active', 'Cloud Computing, Cybersecurity, Devops', 5, 1, '2026-04-22', '2026-09-25', '2026-04-11 06:40:23', '2026-04-11 06:50:39');

-- --------------------------------------------------------

--
-- Table structure for table `project_applications`
--

CREATE TABLE `project_applications` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `experience` longtext DEFAULT NULL,
  `skills` longtext DEFAULT NULL,
  `contribution` longtext DEFAULT NULL,
  `commitment` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `motivation` longtext DEFAULT NULL,
  `portfolio` varchar(500) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `relevant_experience` text DEFAULT NULL,
  `matching_skills` text DEFAULT NULL,
  `available_time` varchar(50) DEFAULT NULL,
  `expected_duration` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_applications`
--

INSERT INTO `project_applications` (`id`, `project_id`, `user_id`, `message`, `experience`, `skills`, `contribution`, `commitment`, `duration`, `motivation`, `portfolio`, `status`, `applied_at`, `reviewed_at`, `relevant_experience`, `matching_skills`, `available_time`, `expected_duration`) VALUES
(2, 7, 41, 'I have extensive experience with Flutter and NodeJS. I am very interested in contributing to the ZCode project!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'accepted', '2025-11-18 10:27:21', NULL, NULL, NULL, NULL, NULL),
(3, 8, 41, 'I am proficient in HTML5, CSS3, JavaScript, PHP and MySQL. I would love to work on CodeCollab Hub!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'accepted', '2025-11-18 10:27:21', NULL, NULL, NULL, NULL, NULL),
(7, 13, 41, 'Advanced Application', 'I have strong PHP and MySQL experience', 'PHP, JavaScript, MySQL, HTML/CSS', 'Backend development and database design', '20-30', '3-6', 'Interested in real-world project experience', 'https://github.com/devinda', 'accepted', '2025-11-18 17:03:02', NULL, NULL, NULL, NULL, NULL),
(8, 15, 37, NULL, 'I have experience developing mobile applications using Flutter, Dart, and React Native.\r\nI’ve built apps that include features such as user authentication, real-time data updates, profile handling, state management, responsive UI layouts, and API integration.\r\nI have also worked on small collaborative mobile projects and prototypes like chat applications, task management apps, and learning platforms.\r\nThese projects helped me understand mobile design patterns, component structuring, and optimization for different screen sizes.', 'Flutter / Dart – Intermediate (widget-based UI, state management, navigation, API integration)\r\n\r\nReact Native – Intermediate (functional components, hooks, styling, form handling)\r\n\r\nJava / Kotlin – Basic to Intermediate (Android Studio workflows, activities, services)\r\n\r\nGit / GitHub – Intermediate (branches, pull requests, version control, team collaboration)\r\n\r\nSince the SmartConnect app focuses on real-time collaboration, skill-sharing, and smooth navigation, my skills align well with the required technology stack and project goals.', 'I can contribute in multiple areas such as:\r\n\r\nCreating clean, responsive, and modern UI screens\r\n\r\nImplementing mobile features with proper state management\r\n\r\nIntegrating APIs, Firebase, or backend services\r\n\r\nEnsuring fast loading times and optimized performance\r\n\r\nTesting and debugging to deliver a smooth user experience\r\n\r\nCollaborating with the team using Git/GitHub workflows\r\n\r\nHelping design features that improve usability and functionality', '20-30', '6-12', 'I’m interested in SmartConnect because it focuses on collaboration, real-time interaction, and mobile-first design, which matches my current learning goals and skills.\r\nThis project allows me to gain more experience in mobile development while contributing meaningfully to a team project.\r\nI also enjoy building applications that help people connect and share knowledge—so the concept aligns perfectly with my interests.', 'https://github.com/yourprofile', 'accepted', '2025-11-19 19:50:31', NULL, NULL, NULL, NULL, NULL),
(9, 15, 46, NULL, 'I have experience developing mobile applications using Flutter, Dart, and React Native. I have worked on projects involving user authentication, profile management, real-time data updates, interactive UI components, and API integration.\r\nI have also developed small collaborative apps and prototypes such as task management apps, chat apps, and learning platforms. These projects helped me gain hands-on experience in mobile UI/UX design, state management, and database integration.', 'Flutter / Dart – Intermediate: I can create responsive, widget-based UIs, manage state effectively, and integrate APIs.\r\n-React Native – Intermediate: Comfortable with functional components, hooks, styling, and form handling.\r\n-Java / Kotlin – Beginner to Intermediate: Can implement Android activities, basic logic, and API communication.\r\n-Git/GitHub – Intermediate: Experienced in version control, branching, pull requests, and team collaboration.\r\n-My skills align well with the project’s requirements and tech stack.', 'I can contribute by:\r\n-Designing clean, responsive, and user-friendly mobile UI screens\r\n-Implementing key features using Flutter/React Native\r\n-Integrating backend APIs and Firebase for real-time functionality\r\n-Debugging, testing, and optimizing performance\r\n-Collaborating with team members to maintain code quality and project standards', '30+', 'ongoing', 'I am passionate about mobile development and enjoy building apps that connect people and enhance collaboration. This project allows me to apply and improve my skills while contributing to a meaningful application that provides real-time interaction and skill-sharing.', 'https://github.com/yourprofile', 'rejected', '2025-11-19 20:13:07', NULL, NULL, NULL, NULL, NULL),
(10, 15, 45, NULL, 'I have experience developing mobile applications using Flutter, Dart, and React Native. I have worked on projects involving user authentication, profile management, real-time data updates, interactive UI components, and API integration.\r\nI have also developed small collaborative apps and prototypes such as task management apps, chat apps, and learning platforms. These projects helped me gain hands-on experience in mobile UI/UX design, state management, and database integration.', '-Flutter / Dart – Intermediate: I can create responsive, widget-based UIs, manage state effectively, and integrate APIs.\r\n-React Native – Intermediate: Comfortable with functional components, hooks, styling, and form handling.\r\n-Java / Kotlin – Beginner to Intermediate: Can implement Android activities, basic logic, and API communication.\r\n-Git/GitHub – Intermediate: Experienced in version control, branching, pull requests, and team collaboration.\r\nMy skills align well with the project’s requirements and tech stack.', 'I can contribute by:\r\n-Designing clean, responsive, and user-friendly mobile UI screens\r\n-Implementing key features using Flutter/React Native\r\n-Integrating backend APIs and Firebase for real-time functionality\r\n-Debugging, testing, and optimizing performance\r\n-Collaborating with team members to maintain code quality and project standards', '20-30', '6-12', 'I am passionate about mobile development and enjoy building apps that connect people and enhance collaboration. This project allows me to apply and improve my skills while contributing to a meaningful application that provides real-time interaction and skill-sharing.', 'https://github.com/yourprofile', 'rejected', '2025-11-19 20:32:34', NULL, NULL, NULL, NULL, NULL),
(11, 13, 50, 'Advanced Application', 'I have hands-on experience in web development, including both frontend and backend technologies.\r\nI’ve worked on several projects involving:\r\n\r\nReact.js interfaces with clean UI components\r\n\r\nNode.js / Express backend APIs\r\n\r\nMySQL database design and integration\r\n\r\nREST API development\r\n\r\nAuthentication and user management systems\r\n\r\nProject planning, component diagrams, WBS creation, and UI/UX wireframing\r\n\r\nI have also contributed to group projects at university, where I handled UI/UX, component architecture, and key development tasks. These projects helped me improve my teamwork, problem-solving, and software engineering fundamentals.', 'I have the technical skills directly relevant to this project:\r\n\r\nReact.js – Intermediate: Component-based architecture, routing, state management\r\n\r\nJavaScript/ES6 – Intermediate: DOM manipulation, async programming, API consumption\r\n\r\nNode.js/Express – Beginner/Intermediate: Backend routes, controllers, middleware\r\n\r\nMySQL – Intermediate: Database relations, CRUD operations\r\n\r\nUI/UX – Intermediate: Wireframing, layout design, responsive UI\r\n\r\nGit & GitHub – Intermediate: Branching, merging, pull requests\r\n\r\nThese skills align well with the project’s frontend, backend, and architecture requirements.', '💡 How Will You Contribute?\r\n\r\nI can contribute in several key areas:\r\n\r\nBuilding clean, reusable frontend components\r\n\r\nHandling React Router, forms, validation, and state management\r\n\r\nDesigning backend APIs and database structures\r\n\r\nImproving UI/UX and creating responsive layouts\r\n\r\nWriting clean, maintainable code with proper documentation\r\n\r\nSupporting team members in debugging and feature implementation\r\n\r\nParticipating in planning, stand-ups, and collaborative tasks\r\n\r\nI will actively support the project until completion.', '10-20', '6-12', 'I am passionate about building real-world applications and collaborating with a team.\r\nThis project matches my long-term goal of improving my full-stack development skills while contributing to something meaningful.\r\nI enjoy teamwork, problem-solving, and learning new technologies, so I’m highly motivated to be part of this project and help deliver a polished final product.', 'https://github.com/kithsara_silva', 'accepted', '2025-11-26 12:08:47', NULL, NULL, NULL, NULL, NULL),
(12, 13, 51, 'Advanced Application', 'I have hands-on experience in web development, including both frontend and backend technologies.\r\nI’ve worked on several projects involving:\r\n\r\nReact.js interfaces with clean UI components\r\n\r\nNode.js / Express backend APIs\r\n\r\nMySQL database design and integration\r\n\r\nREST API development\r\n\r\nAuthentication and user management systems\r\n\r\nProject planning, component diagrams, WBS creation, and UI/UX wireframing\r\n\r\nI have also contributed to group projects at university, where I handled UI/UX, component architecture, and key development tasks. These projects helped me improve my teamwork, problem-solving, and software engineering fundamentals.', 'I have the technical skills directly relevant to this project:\r\n\r\nReact.js – Intermediate: Component-based architecture, routing, state management\r\n\r\nJavaScript/ES6 – Intermediate: DOM manipulation, async programming, API consumption\r\n\r\nNode.js/Express – Beginner/Intermediate: Backend routes, controllers, middleware\r\n\r\nMySQL – Intermediate: Database relations, CRUD operations\r\n\r\nUI/UX – Intermediate: Wireframing, layout design, responsive UI\r\n\r\nGit & GitHub – Intermediate: Branching, merging, pull requests\r\n\r\nThese skills align well with the project’s frontend, backend, and architecture requirements.', 'I can contribute in several key areas:\r\n\r\nBuilding clean, reusable frontend components\r\n\r\nHandling React Router, forms, validation, and state management\r\n\r\nDesigning backend APIs and database structures\r\n\r\nImproving UI/UX and creating responsive layouts\r\n\r\nWriting clean, maintainable code with proper documentation\r\n\r\nSupporting team members in debugging and feature implementation\r\n\r\nParticipating in planning, stand-ups, and collaborative tasks\r\n\r\nI will actively support the project until completion.', '20-30', '6-12', 'I am passionate about building real-world applications and collaborating with a team.\r\nThis project matches my long-term goal of improving my full-stack development skills while contributing to something meaningful.\r\nI enjoy teamwork, problem-solving, and learning new technologies, so I’m highly motivated to be part of this project and help deliver a polished final product.', 'https://github.com/kithsara_devinda', 'accepted', '2025-11-26 13:57:15', NULL, NULL, NULL, NULL, NULL),
(19, 18, 41, 'Advanced Application', 'I have experience working on web-based applications and have been involved in developing both frontend and backend features.\r\nI have worked on projects related to user authentication systems, database-driven applications, and responsive UI design.\r\nAdditionally, I have knowledge in AI and Data Science, which helps me approach problem-solving in a more analytical way.\r\nI have also explored DevOps basics, including project setup and deployment workflows.', 'I have advanced knowledge in web development concepts, especially in frontend technologies like HTML, CSS, and JavaScript.\r\nI also have intermediate experience in working with databases and backend logic using PHP and MySQL.\r\nMy background in AI and Data Science adds extra value when handling logic, data processing, and system improvements.\r\nOverall, my skill set aligns well with both frontend and backend requirements of this project.', 'I can contribute by taking responsibility for key parts of the system such as authentication, database design, and core feature development.\r\nI can also support frontend development to ensure a smooth and responsive user experience.\r\nAdditionally, I can help in structuring the project, solving complex issues, and guiding team members when needed to maintain code quality and efficiency.', '10-20', '3-6', 'I am interested in this project because it combines multiple areas I am passionate about, including web development, collaboration, and learning systems.\r\nI like the idea of building a platform that connects people and allows knowledge sharing.\r\nThis project is a great opportunity for me to apply my skills in a real-world scenario and contribute to building a meaningful system.', 'https://github.com/devinda', 'accepted', '2026-04-10 10:08:50', NULL, NULL, NULL, NULL, NULL),
(20, 19, 58, 'Matched skills test application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2026-04-11 06:43:33', NULL, NULL, NULL, NULL, NULL),
(21, 20, 59, 'Matched skills test application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2026-04-11 06:43:33', NULL, NULL, NULL, NULL, NULL),
(22, 21, 60, 'Matched skills test application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'accepted', '2026-04-11 06:43:33', NULL, NULL, NULL, NULL, NULL),
(23, 22, 61, 'Matched skills test application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'accepted', '2026-04-11 06:43:33', NULL, NULL, NULL, NULL, NULL),
(24, 19, 62, 'Matched skills test application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2026-04-11 06:43:33', NULL, NULL, NULL, NULL, NULL),
(25, 20, 62, 'Matched skills test application', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2026-04-11 06:43:33', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_chat_messages`
--

CREATE TABLE `project_chat_messages` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_chat_messages`
--

INSERT INTO `project_chat_messages` (`id`, `project_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 17, 37, 'hi', '2025-12-06 12:34:53'),
(2, 17, 37, 'hi', '2025-12-06 12:35:15'),
(3, 13, 41, 'hi', '2025-12-06 12:35:52'),
(4, 13, 37, 'Hi devinda', '2025-12-06 12:36:11'),
(5, 17, 37, 'hi suddh', '2025-12-07 06:30:23'),
(6, 17, 37, 'hi', '2025-12-08 08:04:40'),
(7, 13, 37, 'kithsara', '2025-12-08 08:05:54'),
(8, 13, 41, 'umaya', '2025-12-08 08:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(100) DEFAULT 'Member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `role`, `joined_at`, `status`) VALUES
(1, 7, 41, 'Member', '2025-11-18 10:32:02', ''),
(2, 8, 41, 'Member', '2025-11-18 10:37:23', 'active'),
(3, 13, 41, 'Developer', '2025-11-18 17:10:17', 'active'),
(4, 15, 37, 'Developer', '2025-11-19 19:51:05', 'active'),
(5, 13, 50, 'Frontend Engineer', '2025-11-26 12:09:26', ''),
(6, 13, 51, 'Project Lead', '2025-11-26 13:57:29', 'active'),
(7, 18, 41, 'Member', '2026-04-10 10:21:36', ''),
(8, 21, 60, 'Data Scientist', '2026-04-11 06:50:36', 'active'),
(9, 22, 61, 'Member', '2026-04-11 06:50:39', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('todo','in-progress','done') NOT NULL DEFAULT 'todo',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_tasks`
--

INSERT INTO `project_tasks` (`id`, `project_id`, `assigned_to`, `title`, `description`, `status`, `priority`, `deadline`, `created_at`, `updated_at`) VALUES
(1, 18, 41, 'Manage Team Members', 'Nice bro 😎 now we assign a clean, realistic task to Devinda 🔥\r\n\r\n📝 Assign New Task (Devinda)\r\n\r\nMember:\r\n👉 Devinda\r\n\r\n✅ Task Title *\r\n\r\n👉\r\nManage Team Members\r\n\r\n✅ Description *\r\n\r\n👉\r\nResponsible for managing and coordinating project team members effectively. This includes assigning tasks, monitoring member progress, and ensuring proper collaboration within the team.\r\n\r\nThe task also involves tracking task completion, identifying blockers, and maintaining clear communication among all members. Additionally, ensure that each member is contributing according to their role and skill level.\r\n\r\nWork closely with the project owner to improve team workflow and maintain productivity throughout the project lifecycle.', 'todo', 'high', '2026-04-24', '2026-04-10 10:24:42', '2026-04-10 10:24:42'),
(2, 21, 60, 'Build Baseline Prediction Model', 'Create a baseline ML prediction pipeline and include key performance metrics for the InsightFlow dashboard.', 'in-progress', 'high', '2026-05-10', '2026-04-11 06:54:24', '2026-04-11 06:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `passing_score` int(11) DEFAULT 70,
  `time_limit` int(11) DEFAULT NULL COMMENT 'Time limit in minutes',
  `is_premium` tinyint(1) DEFAULT 0,
  `badge_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') DEFAULT 'multiple_choice',
  `points` int(11) DEFAULT 1,
  `order_number` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question_options`
--

CREATE TABLE `quiz_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `order_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `reporter_user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed','warned') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `reported_user_id`, `reporter_user_id`, `reason`, `description`, `status`, `created_at`) VALUES
(1, 41, 37, 'Spam content', 'This user keeps posting the same message.', 'pending', '2025-12-18 09:02:57');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `skill_name`, `created_at`) VALUES
(1, 'Web Development', '2025-11-14 08:02:22'),
(2, 'Frontend Frameworks', '2025-11-14 08:02:22'),
(3, 'Backend Development', '2025-11-14 08:02:22'),
(4, 'Database Management', '2025-11-14 08:02:22'),
(5, 'Mobile App Development', '2025-11-14 08:02:22'),
(6, 'Cloud Computing', '2025-11-14 08:02:22'),
(7, 'Data Analysis & Visualization', '2025-11-14 08:02:22'),
(8, 'Cybersecurity', '2025-11-14 08:02:22'),
(9, 'DevOps', '2025-11-14 08:02:22'),
(10, 'GitHub and Git', '2025-11-14 08:02:22'),
(11, 'AI and ML', '2025-11-14 08:02:22'),
(12, 'Digital Marketing', '2025-11-14 08:02:22'),
(13, 'Data Science', '2025-11-14 08:02:22');

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_history`
--

INSERT INTO `task_history` (`id`, `task_id`, `user_id`, `action`, `timestamp`) VALUES
(1, 2, 60, 'marked_in_progress', '2026-04-11 06:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('individual','organization','admin','quiz_manager','manager','community_admin','moderator') DEFAULT 'individual',
  `org_cert` varchar(255) DEFAULT NULL,
  `profile_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','suspended','banned') DEFAULT 'active',
  `suspension_end_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `profile_picture`, `bio`, `password`, `role`, `org_cert`, `profile_completed`, `created_at`, `status`, `suspension_end_date`) VALUES
(7, 'admin', 'admin@skillxchange.com', NULL, NULL, '$2y$10$o9a..F1tmQQAVJ9IbEhDjuEW4PkRfHr8Vbza/.Z84PtZmJ0qx2Hsu', 'admin', NULL, 1, '2025-10-21 11:47:00', 'active', NULL),
(37, 'Pretty Software', 'ps@gmail.com', NULL, NULL, '$2y$10$6EnmKXj0bXGFv2IE90VKbOmYXq52Y6GQ3753ahq6BVzTFD3DuYlk.', 'organization', '../public/uploads/org_certs/org_691aff1615f5b6.93674405.jpg', 0, '2025-11-17 10:55:18', 'active', NULL),
(41, 'Devinda', 'Devinda@gmail.com', 'uploads/profile_pictures/user_41_691b012e89fbb.jpg', NULL, '$2y$10$VriGWUViBKQxUGM11kjR/ek7bmdgOV.4N0OlnkJ8CfN76sUd6VY3i', 'individual', NULL, 1, '2025-11-17 11:03:28', 'active', NULL),
(43, 'BlueWave Innovations', 'contact@bluewave.lk', NULL, NULL, '$2y$10$ghjV3bBSWrq2maR/6Tx9RedRRdYLQcMGrO1InhfKmdN.uyYpMJfZS', 'organization', '../public/uploads/org_certs/org_691e0c3b1ade60.91308093.jpg', 0, '2025-11-19 18:28:11', 'active', NULL),
(44, 'CodeCraft Labs', 'hello@codecraftlabs.com', NULL, NULL, '$2y$10$LxN1R/2Cm04hsCY1cV7YbOnkvrLV3KDMfZE9FdVvy6oLvXN7JbJ.6', 'organization', '../public/uploads/org_certs/org_691e0c5d2881a7.91665818.jpg', 0, '2025-11-19 18:28:45', 'active', NULL),
(45, 'Ayesh Fernando', 'ayesh.fernando98@gmail.com', 'uploads/profile_pictures/user_45_691e108948f56.jpg', NULL, '$2y$10$nZXUR5RIq3cYVTJkN8a9QeZK9xoInhzzamzoqomT4E.xY3dwhAdDm', 'individual', NULL, 1, '2025-11-19 18:29:17', 'active', NULL),
(46, 'Dilini Perera', 'dilini.perera21@yahoo.com', 'uploads/profile_pictures/user_46_691e112a42bb9.jpg', NULL, '$2y$10$9u516LmKJEIWwSj6rEgtQuvPGbaKtHnccEBPIYc9Rq.YO2kG54HMq', 'individual', NULL, 1, '2025-11-19 18:29:47', 'active', NULL),
(47, 'Ravindu Silva', 'ravindu.silva.dev@gmail.com', 'uploads/profile_pictures/user_47_691e115dd8246.jpg', NULL, '$2y$10$QpHVaqmxco0zSKoZLaQQPeMwPvllkJC4.HygUdZyX8vU3PBt2OfMC', 'individual', NULL, 1, '2025-11-19 18:30:41', 'active', NULL),
(48, 'Tharushi Wickramasin', 'tharushi.wickrama@gmail.com', 'uploads/profile_pictures/user_48_691e122f790bc.jpg', NULL, '$2y$10$Wv.F41j5KD6YZifiJvcu2uP.ClSyYmaLDgdjCsL.bVIZE/6YqQEUC', 'individual', NULL, 1, '2025-11-19 18:31:13', 'active', NULL),
(49, 'Nimesh Jayawardena', 'nimesh.jayawardena01@gmail.com', NULL, NULL, '$2y$10$R2m9TcftwQ//8VN7QySd6ud8G2jlWel.YkiiJ8r0PSpc8Fo6wzgeK', 'individual', NULL, 1, '2025-11-19 18:31:39', 'active', NULL),
(50, 'Kithsara Silva', 'kithsarasilva02@gmail.com', 'uploads/profile_pictures/user_50_6926ed4a9d055.jpg', NULL, '$2y$10$SrSdM.0gJWgltZfEYuPf4.EFWRd7mwEmu0izOFHcKl8.tB/zzenUi', 'individual', NULL, 1, '2025-11-26 12:02:27', 'active', NULL),
(51, 'Kithsara Devinda', 'kithsaradevinda@gmail.com', 'uploads/profile_pictures/user_51_6927067b1d216.jpg', NULL, '$2y$10$CH1MfWc7yz2Otq3FSQn.MeqsMijcT7sjMYYQfD4UbGkAfQJNu1urW', 'individual', NULL, 1, '2025-11-26 13:53:32', 'active', NULL),
(52, 'TestOrg1', 'testorg1@gmail.com', NULL, NULL, '$2y$10$s68MkXd/9AH7c6BjZt6M/eScvK7RxCVWD.Am8aXmNDBEQxJ4vjVTa', 'organization', 'uploads/org_certs/org_69d8c7155eca87.20969658.png', 0, '2026-04-10 09:47:01', 'active', NULL),
(53, 'IndTest1', 'indtest1@gmail.com', 'uploads/profile_pictures/user_53_69d8caf13f448.png', NULL, '$2y$10$PiewxEycgvtyHeIWnZxzx.vEWFu0FsmSUQiqPpJBETpT2SWQAgv86', 'individual', NULL, 1, '2026-04-10 10:02:29', 'active', NULL),
(54, 'TestOrg2', 'testorg2@gmail.com', NULL, NULL, '$2y$10$G/wzCMAJQsgeTrjK.8YFL.fUv9JvSPW.MWITwONhSIwJViHcEANYe', 'organization', '../public/uploads/org_certs/org_69d9dd14814e99.96439663.jpg', 0, '2026-04-11 05:33:08', 'active', NULL),
(55, 'IndTest3', 'indtest3@gmail.com', 'uploads/profile_pictures/user_55_69d9e1880dc83.jpg', NULL, '$2y$10$cgFUAOvHyP7WTZUa92Q9n.DMHTx9jy4pVFw7SZa4GMzi.kkKavrq6', 'individual', NULL, 1, '2026-04-11 05:36:05', 'active', NULL),
(56, 'WebNova Labs', 'webnova.org1@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'organization', '../public/uploads/org_certs/webnova_coc.pdf', 0, '2026-04-11 06:40:23', 'active', NULL),
(57, 'DataForge Hub', 'dataforge.org2@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'organization', '../public/uploads/org_certs/dataforge_coc.pdf', 0, '2026-04-11 06:40:23', 'active', NULL),
(58, 'Kasun Perera', 'kasun.u1@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'individual', NULL, 1, '2026-04-11 06:40:23', 'active', NULL),
(59, 'Nethmi Silva', 'nethmi.u2@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'individual', NULL, 1, '2026-04-11 06:40:23', 'active', NULL),
(60, 'Sithum Jayasena', 'sithum.u3@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'individual', NULL, 1, '2026-04-11 06:40:23', 'active', NULL),
(61, 'Tharushi Fernando', 'tharushi.u4@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'individual', NULL, 1, '2026-04-11 06:40:23', 'active', NULL),
(62, 'Iresha Madushani', 'iresha.u5@testmail.com', NULL, NULL, '$2y$10$HFuvcDlmgOZ.Q0AhFtqM8el/SBywTY3A96PJEAb3P5qYtcJKWP766', 'individual', NULL, 1, '2026-04-11 06:40:23', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `earned_at`) VALUES
(1, 53, 6, '2026-04-10 10:02:29'),
(2, 55, 6, '2026-04-11 05:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_feedback`
--

CREATE TABLE `user_feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `context_type` enum('project','session') NOT NULL DEFAULT 'project',
  `context_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `report_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_feedback`
--

INSERT INTO `user_feedback` (`id`, `user_id`, `reviewer_id`, `project_id`, `context_type`, `context_id`, `rating`, `comment`, `tags`, `created_at`, `updated_at`, `report_count`) VALUES
(2, 41, 37, 13, 'project', 13, 3, 'It was a pleasure working with Devinda. He communicated clearly, delivered quality work, and completed tasks on time. Very reliable and professional throughout the project.', 'quality,ontime,teamwork,communication', '2026-02-18 12:19:54', '2026-04-01 14:48:13', 2),
(3, 51, 37, 13, 'project', 13, 3, 'kkk', 'communication', '2026-02-18 18:44:57', '2026-04-01 14:49:51', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user_projects`
--

CREATE TABLE `user_projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('in_progress','completed') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_quiz_answers`
--

CREATE TABLE `user_quiz_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `points_earned` decimal(5,2) DEFAULT 0.00,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_quiz_attempts`
--

CREATE TABLE `user_quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` decimal(5,2) DEFAULT 0.00,
  `total_questions` int(11) DEFAULT 0,
  `correct_answers` int(11) DEFAULT 0,
  `passed` tinyint(1) DEFAULT 0,
  `time_taken` int(11) DEFAULT NULL COMMENT 'Time taken in seconds',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` enum('in_progress','completed','abandoned') DEFAULT 'in_progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `reporter_org_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_reports`
--

INSERT INTO `user_reports` (`id`, `project_id`, `reported_user_id`, `reporter_org_id`, `reason`, `details`, `status`, `reported_at`) VALUES
(1, 7, 41, 37, 'llll', '', 'pending', '2025-12-08 08:11:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_saved_quizzes`
--

CREATE TABLE `user_saved_quizzes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `skill_type` enum('teach','learn') NOT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_name`, `skill_type`, `proficiency_level`, `created_at`) VALUES
(32, 41, 'devops', 'teach', 'beginner', '2025-11-17 11:04:14'),
(33, 41, 'ai', 'teach', 'intermediate', '2025-11-17 11:04:14'),
(34, 41, 'data-science', 'teach', 'intermediate', '2025-11-17 11:04:14'),
(35, 41, 'web-development', 'learn', 'advanced', '2025-11-17 11:04:14'),
(36, 41, 'frontend', 'learn', 'intermediate', '2025-11-17 11:04:14'),
(37, 41, 'database', 'learn', 'advanced', '2025-11-17 11:04:14'),
(38, 49, 'web-development', 'teach', 'advanced', '2025-11-19 18:45:02'),
(39, 49, 'frontend', 'teach', 'advanced', '2025-11-19 18:45:02'),
(40, 49, 'github', 'teach', 'intermediate', '2025-11-19 18:45:02'),
(41, 49, 'backend', 'learn', 'beginner', '2025-11-19 18:45:02'),
(42, 49, 'cloud', 'learn', 'beginner', '2025-11-19 18:45:02'),
(43, 49, 'devops', 'learn', 'intermediate', '2025-11-19 18:45:02'),
(44, 45, 'web-development', 'teach', 'advanced', '2025-11-19 18:46:33'),
(45, 45, 'frontend', 'teach', 'advanced', '2025-11-19 18:46:33'),
(46, 45, 'github', 'teach', 'intermediate', '2025-11-19 18:46:33'),
(47, 45, 'backend', 'learn', 'beginner', '2025-11-19 18:46:33'),
(48, 45, 'cloud', 'learn', 'beginner', '2025-11-19 18:46:33'),
(49, 45, 'devops', 'learn', 'intermediate', '2025-11-19 18:46:33'),
(50, 46, 'data-analytics', 'teach', 'advanced', '2025-11-19 18:49:14'),
(51, 46, 'marketing', 'teach', 'intermediate', '2025-11-19 18:49:14'),
(52, 46, 'ai', 'teach', 'intermediate', '2025-11-19 18:49:14'),
(53, 46, 'web-development', 'learn', 'beginner', '2025-11-19 18:49:14'),
(54, 46, 'backend', 'learn', 'beginner', '2025-11-19 18:49:14'),
(55, 46, 'cybersecurity', 'learn', 'beginner', '2025-11-19 18:49:14'),
(56, 47, 'data-science', 'teach', 'advanced', '2025-11-19 18:50:05'),
(57, 47, 'ai', 'teach', 'advanced', '2025-11-19 18:50:05'),
(58, 47, 'database', 'teach', 'intermediate', '2025-11-19 18:50:05'),
(59, 47, 'cloud', 'learn', 'intermediate', '2025-11-19 18:50:05'),
(60, 47, 'devops', 'learn', 'beginner', '2025-11-19 18:50:05'),
(61, 47, 'mobile', 'learn', 'beginner', '2025-11-19 18:50:05'),
(62, 48, 'backend', 'teach', 'intermediate', '2025-11-19 18:53:35'),
(63, 48, 'data-science', 'teach', 'advanced', '2025-11-19 18:53:35'),
(64, 48, 'frontend', 'teach', 'advanced', '2025-11-19 18:53:35'),
(65, 48, 'github', 'learn', 'beginner', '2025-11-19 18:53:35'),
(66, 48, 'data-analytics', 'learn', 'intermediate', '2025-11-19 18:53:35'),
(67, 48, 'devops', 'learn', 'beginner', '2025-11-19 18:53:35'),
(68, 50, 'web-development', 'teach', 'intermediate', '2025-11-26 12:06:34'),
(69, 50, 'frontend', 'teach', 'intermediate', '2025-11-26 12:06:34'),
(70, 50, 'backend', 'teach', 'intermediate', '2025-11-26 12:06:34'),
(71, 50, 'cloud', 'learn', 'intermediate', '2025-11-26 12:06:34'),
(72, 50, 'devops', 'learn', 'intermediate', '2025-11-26 12:06:34'),
(73, 50, 'cybersecurity', 'learn', 'intermediate', '2025-11-26 12:06:34'),
(74, 51, 'web-development', 'teach', 'advanced', '2025-11-26 13:54:03'),
(75, 51, 'frontend', 'teach', 'intermediate', '2025-11-26 13:54:03'),
(76, 51, 'backend', 'teach', 'advanced', '2025-11-26 13:54:03'),
(77, 51, 'ai', 'learn', 'advanced', '2025-11-26 13:54:03'),
(78, 51, 'devops', 'learn', 'intermediate', '2025-11-26 13:54:03'),
(79, 51, 'data-analytics', 'learn', 'advanced', '2025-11-26 13:54:03'),
(80, 53, 'marketing', 'teach', 'advanced', '2026-04-10 10:03:29'),
(81, 53, 'data-science', 'teach', 'advanced', '2026-04-10 10:03:29'),
(82, 53, 'data-analytics', 'teach', 'advanced', '2026-04-10 10:03:29'),
(83, 53, 'web-development', 'learn', 'advanced', '2026-04-10 10:03:29'),
(84, 53, 'backend', 'learn', 'advanced', '2026-04-10 10:03:29'),
(85, 53, 'database', 'learn', 'advanced', '2026-04-10 10:03:29'),
(86, 55, 'mobile', 'teach', 'advanced', '2026-04-11 05:52:08'),
(87, 55, 'web-development', 'learn', 'advanced', '2026-04-11 05:52:08'),
(88, 55, 'cloud', 'learn', 'intermediate', '2026-04-11 05:52:08'),
(89, 55, 'devops', 'learn', 'intermediate', '2026-04-11 05:52:08'),
(106, 58, 'Web Development', 'teach', 'beginner', '2026-04-11 06:43:33'),
(107, 58, 'Frontend Frameworks', 'teach', 'intermediate', '2026-04-11 06:43:33'),
(108, 58, 'Backend Development', 'learn', 'beginner', '2026-04-11 06:43:33'),
(109, 59, 'Mobile App Development', 'teach', 'intermediate', '2026-04-11 06:43:33'),
(110, 59, 'Backend Development', 'teach', 'beginner', '2026-04-11 06:43:33'),
(111, 59, 'Frontend Frameworks', 'learn', 'beginner', '2026-04-11 06:43:33'),
(112, 60, 'Data Science', 'teach', 'advanced', '2026-04-11 06:43:33'),
(113, 60, 'AI and ML', 'teach', 'intermediate', '2026-04-11 06:43:33'),
(114, 60, 'Data Analysis & Visualization', 'learn', 'intermediate', '2026-04-11 06:43:33'),
(115, 61, 'Cloud Computing', 'teach', 'intermediate', '2026-04-11 06:43:33'),
(116, 61, 'Devops', 'teach', 'advanced', '2026-04-11 06:43:33'),
(117, 61, 'Cybersecurity', 'teach', 'intermediate', '2026-04-11 06:43:33'),
(118, 61, 'Backend Development', 'learn', 'beginner', '2026-04-11 06:43:33'),
(119, 62, 'Frontend Frameworks', 'teach', 'advanced', '2026-04-11 06:43:33'),
(120, 62, 'Mobile App Development', 'teach', 'beginner', '2026-04-11 06:43:33'),
(121, 62, 'Data Science', 'learn', 'beginner', '2026-04-11 06:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

CREATE TABLE `user_stats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `connections_count` int(11) DEFAULT 0,
  `skills_taught_count` int(11) DEFAULT 0,
  `skills_learning_count` int(11) DEFAULT 0,
  `hours_exchanged` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_stats`
--

INSERT INTO `user_stats` (`id`, `user_id`, `connections_count`, `skills_taught_count`, `skills_learning_count`, `hours_exchanged`) VALUES
(13, 41, 0, 0, 0, 0),
(14, 45, 0, 0, 0, 0),
(15, 46, 0, 0, 0, 0),
(16, 47, 0, 0, 0, 0),
(17, 48, 0, 0, 0, 0),
(18, 49, 0, 0, 0, 0),
(19, 50, 0, 0, 0, 0),
(20, 51, 0, 0, 0, 0),
(21, 53, 0, 0, 0, 0),
(22, 55, 0, 0, 0, 0),
(23, 58, 0, 2, 1, 0),
(24, 59, 0, 2, 1, 0),
(25, 60, 0, 2, 1, 0),
(26, 61, 0, 3, 1, 0),
(27, 62, 0, 2, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `created_at`, `updated_at`) VALUES
(1, 37, 1000.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(2, 41, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(3, 43, 1000.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(4, 44, 1000.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(5, 45, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(6, 46, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(7, 47, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(8, 48, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(9, 49, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(10, 50, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(11, 51, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43'),
(12, 52, 1000.00, '2026-04-10 10:41:59', '2026-04-10 10:41:59'),
(13, 53, 250.00, '2026-04-10 16:34:29', '2026-04-10 16:34:29');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_notifications`
--

CREATE TABLE `wallet_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('low_balance','payment_received','payment_sent','wallet_created') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `community_members`
--
ALTER TABLE `community_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `community_user` (`community_id`,`user_id`),
  ADD KEY `community_id` (`community_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content_lookup` (`content_type`,`content_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reporter` (`reporter_id`);

--
-- Indexes for table `exchanges`
--
ALTER TABLE `exchanges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `feedback_reports`
--
ALTER TABLE `feedback_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_reports_feedback` (`feedback_id`),
  ADD KEY `idx_feedback_reports_status` (`status`,`created_at`),
  ADD KEY `idx_feedback_reports_reporter` (`reporter_id`),
  ADD KEY `fk_feedback_reports_reviewer` (`reviewed_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_project` (`project_id`),
  ADD KEY `fk_notifications_task` (`task_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_id` (`community_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_organization` (`organization_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_projects_status_created` (`status`,`created_at`);

--
-- Indexes for table `project_applications`
--
ALTER TABLE `project_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_applied_at` (`applied_at`),
  ADD KEY `idx_project_app_user_project_status` (`user_id`,`project_id`,`status`);

--
-- Indexes for table `project_chat_messages`
--
ALTER TABLE `project_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_chat_project` (`project_id`),
  ADD KEY `fk_chat_sender` (`sender_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`project_id`,`user_id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_assigned_to` (`assigned_to`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_badge_id` (`badge_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `reporter_user_id` (`reporter_user_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task` (`task_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_badge_id` (`badge_id`);

--
-- Indexes for table `user_feedback`
--
ALTER TABLE `user_feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_feedback_context` (`user_id`,`reviewer_id`,`context_type`,`context_id`),
  ADD UNIQUE KEY `unique_feedback_per_context` (`user_id`,`reviewer_id`,`context_type`,`context_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `idx_project_feedback` (`project_id`,`created_at`),
  ADD KEY `idx_context` (`context_type`,`context_id`),
  ADD KEY `idx_user_feedback` (`user_id`),
  ADD KEY `idx_reviewer_feedback` (`reviewer_id`),
  ADD KEY `fk_feedback_context_project` (`context_id`),
  ADD KEY `idx_context_lookup` (`context_type`,`context_id`),
  ADD KEY `idx_user_ratings` (`user_id`,`context_type`),
  ADD KEY `idx_reviewer_given` (`reviewer_id`,`context_type`),
  ADD KEY `idx_feedback_user_context` (`user_id`,`context_type`,`context_id`,`created_at`),
  ADD KEY `idx_feedback_rating` (`user_id`,`rating`),
  ADD KEY `idx_feedback_created` (`user_id`,`created_at`),
  ADD KEY `idx_feedback_reviewer` (`reviewer_id`,`created_at`);

--
-- Indexes for table `user_projects`
--
ALTER TABLE `user_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_quiz_answers`
--
ALTER TABLE `user_quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempt_id` (`attempt_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `user_quiz_attempts`
--
ALTER TABLE `user_quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_quiz_id` (`quiz_id`),
  ADD KEY `idx_user_quiz` (`user_id`,`quiz_id`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_reported_user` (`reported_user_id`),
  ADD KEY `idx_reporter_org` (`reporter_org_id`);

--
-- Indexes for table `user_saved_quizzes`
--
ALTER TABLE `user_saved_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_quiz` (`user_id`,`quiz_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_quiz_id` (`quiz_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_skills_match` (`user_id`,`skill_type`,`skill_name`);

--
-- Indexes for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wallet_notifications`
--
ALTER TABLE `wallet_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `community_members`
--
ALTER TABLE `community_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exchanges`
--
ALTER TABLE `exchanges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback_reports`
--
ALTER TABLE `feedback_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `project_applications`
--
ALTER TABLE `project_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `project_chat_messages`
--
ALTER TABLE `project_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_feedback`
--
ALTER TABLE `user_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_projects`
--
ALTER TABLE `user_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_quiz_answers`
--
ALTER TABLE `user_quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_quiz_attempts`
--
ALTER TABLE `user_quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_saved_quizzes`
--
ALTER TABLE `user_saved_quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `user_stats`
--
ALTER TABLE `user_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wallet_notifications`
--
ALTER TABLE `wallet_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community_members`
--
ALTER TABLE `community_members`
  ADD CONSTRAINT `community_members_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `fk_content_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exchanges`
--
ALTER TABLE `exchanges`
  ADD CONSTRAINT `exchanges_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exchanges_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exchanges_ibfk_3` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_reports`
--
ALTER TABLE `feedback_reports`
  ADD CONSTRAINT `fk_feedback_reports_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `user_feedback` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_reports_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_task` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_applications`
--
ALTER TABLE `project_applications`
  ADD CONSTRAINT `project_applications_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_chat_messages`
--
ALTER TABLE `project_chat_messages`
  ADD CONSTRAINT `fk_chat_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD CONSTRAINT `task_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_user_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `fk_quiz_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD CONSTRAINT `fk_quiz_options_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `history_task_fk` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `history_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `fk_user_badges_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_badges_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_feedback`
--
ALTER TABLE `user_feedback`
  ADD CONSTRAINT `fk_feedback_context_project` FOREIGN KEY (`context_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_feedback_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `user_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_feedback_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_projects`
--
ALTER TABLE `user_projects`
  ADD CONSTRAINT `user_projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_quiz_answers`
--
ALTER TABLE `user_quiz_answers`
  ADD CONSTRAINT `fk_quiz_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `user_quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_quiz_answers_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_quiz_attempts`
--
ALTER TABLE `user_quiz_attempts`
  ADD CONSTRAINT `fk_quiz_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_quiz_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `fk_report_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_reported_user` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_reporter_org` FOREIGN KEY (`reporter_org_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_saved_quizzes`
--
ALTER TABLE `user_saved_quizzes`
  ADD CONSTRAINT `fk_saved_quizzes_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_quizzes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_notifications`
--
ALTER TABLE `wallet_notifications`
  ADD CONSTRAINT `wallet_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wallet_transactions_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
