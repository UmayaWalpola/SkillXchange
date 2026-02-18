-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 01:24 PM
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
(7, 5, 1, 'admin', '2024-01-25 10:00:00');

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
(13, 37, 'kithsara project', 'Pretty software project', 'web', 'active', 'HTML5 , CSS3, JavaScript, PHP, MySql', 7, 3, '2025-11-29', '2026-10-18', '2025-11-18 10:55:15', '2025-11-26 13:57:29'),
(14, 37, 'Devinda Web Project', 'This project is a modern and responsive web application designed to provide users with an easy-to-use and interactive online experience. It includes key features such as user authentication, dynamic content display, and a well-structured interface built with best web development practices. The system ensures smooth navigation, mobile-friendly layouts, and efficient data handling through backend integration. The project focuses on scalability, maintainability, and clean UI/UX design to offer a seamless workflow for both users and administrators.', 'web', 'active', 'HTML5 , CSS3, JavaScript, PHP, MySql', 7, 0, '2025-11-20', '2026-10-18', '2025-11-18 17:11:40', '2025-11-18 17:11:51'),
(15, 37, 'SmartConnect Mobile App (PS software)', 'SmartConnect is a modern mobile application designed to help users connect, collaborate, and share skills effortlessly. The app provides a clean and responsive interface with real-time interactions, profile management, skill listings, messaging, and project collaboration features.\r\nIt aims to deliver fast performance, smooth navigation, and a user-friendly experience across Android and iOS platforms.', 'mobile', 'active', 'Flutter / Dart, React Native, Java / Kotlin ,Git/GitHub', 10, 1, '2025-11-29', '2025-12-31', '2025-11-19 19:03:48', '2025-11-19 19:51:05'),
(16, 37, 'Online Bookstore Management System', 'A web application that allows users to browse, search, and purchase books online with secure payment integration.', 'web', 'active', 'PHP, MySQL, Laravel, HTML, CSS, JavaScript', 5, 0, '2025-11-23', '2025-11-29', '2025-11-23 10:28:30', '2025-11-23 10:28:30'),
(17, 37, 'Kithsara Mobile App 2', 'The Mobile App Development Project is focused on designing and building a fully–functional, user-friendly, and efficient mobile application tailored to meet specific user needs. This project involves creating a high-quality mobile solution that delivers seamless performance, attractive UI/UX design, and practical features that solve real-world problems.\r\n\r\nThe application will be developed using modern mobile technologies and frameworks, ensuring cross-platform compatibility, scalability, and long-term maintainability. Throughout the project, industry-best practices such as version control, clean architecture, responsive design, and secure coding standards will be followed.\r\n\r\nKey project tasks include requirement gathering, designing wireframes, developing core features, integrating APIs, testing for bugs, and finally deploying the app to platforms such as Google Play Store or Apple App Store. The project also aims to provide an admin or backend system if required, enabling data management and real-time updates.\r\n\r\nOverall, this mobile app development project will deliver a high-quality, modern application that enhances the user experience, supports business goals, and ensures continuous improvement based on user feedback.', 'mobile', 'active', 'React Native , Flutter, JavaScript / TypeScript  , HTML & CSS , UI/UX Design , API Integration , RESTful API Development , Node.js , Express.js ,   MongoDB / MySQL,  Firebase Services', 10, 0, '2025-12-30', '2026-05-06', '2025-12-06 09:51:12', '2025-12-06 09:52:46');

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
(12, 13, 51, 'Advanced Application', 'I have hands-on experience in web development, including both frontend and backend technologies.\r\nI’ve worked on several projects involving:\r\n\r\nReact.js interfaces with clean UI components\r\n\r\nNode.js / Express backend APIs\r\n\r\nMySQL database design and integration\r\n\r\nREST API development\r\n\r\nAuthentication and user management systems\r\n\r\nProject planning, component diagrams, WBS creation, and UI/UX wireframing\r\n\r\nI have also contributed to group projects at university, where I handled UI/UX, component architecture, and key development tasks. These projects helped me improve my teamwork, problem-solving, and software engineering fundamentals.', 'I have the technical skills directly relevant to this project:\r\n\r\nReact.js – Intermediate: Component-based architecture, routing, state management\r\n\r\nJavaScript/ES6 – Intermediate: DOM manipulation, async programming, API consumption\r\n\r\nNode.js/Express – Beginner/Intermediate: Backend routes, controllers, middleware\r\n\r\nMySQL – Intermediate: Database relations, CRUD operations\r\n\r\nUI/UX – Intermediate: Wireframing, layout design, responsive UI\r\n\r\nGit & GitHub – Intermediate: Branching, merging, pull requests\r\n\r\nThese skills align well with the project’s frontend, backend, and architecture requirements.', 'I can contribute in several key areas:\r\n\r\nBuilding clean, reusable frontend components\r\n\r\nHandling React Router, forms, validation, and state management\r\n\r\nDesigning backend APIs and database structures\r\n\r\nImproving UI/UX and creating responsive layouts\r\n\r\nWriting clean, maintainable code with proper documentation\r\n\r\nSupporting team members in debugging and feature implementation\r\n\r\nParticipating in planning, stand-ups, and collaborative tasks\r\n\r\nI will actively support the project until completion.', '20-30', '6-12', 'I am passionate about building real-world applications and collaborating with a team.\r\nThis project matches my long-term goal of improving my full-stack development skills while contributing to something meaningful.\r\nI enjoy teamwork, problem-solving, and learning new technologies, so I’m highly motivated to be part of this project and help deliver a polished final product.', 'https://github.com/kithsara_devinda', 'accepted', '2025-11-26 13:57:15', NULL, NULL, NULL, NULL, NULL);

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
(5, 13, 50, 'Frontend Engineer', '2025-11-26 12:09:26', 'active'),
(6, 13, 51, 'Project Lead', '2025-11-26 13:57:29', 'active');

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
  `role` enum('individual','organization','admin','quiz_manager','manager','community_admin') DEFAULT 'individual',
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
(51, 'Kithsara Devinda', 'kithsaradevinda@gmail.com', 'uploads/profile_pictures/user_51_6927067b1d216.jpg', NULL, '$2y$10$CH1MfWc7yz2Otq3FSQn.MeqsMijcT7sjMYYQfD4UbGkAfQJNu1urW', 'individual', NULL, 1, '2025-11-26 13:53:32', 'active', NULL);

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
  `badge_name` varchar(100) NOT NULL,
  `badge_icon` varchar(10) DEFAULT '?',
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_name`, `badge_icon`, `earned_at`) VALUES
(11, 41, 'Early Adopter', '🌟', '2025-11-17 11:03:28'),
(12, 45, 'Early Adopter', '🌟', '2025-11-19 18:29:17'),
(13, 46, 'Early Adopter', '🌟', '2025-11-19 18:29:47'),
(14, 47, 'Early Adopter', '🌟', '2025-11-19 18:30:41'),
(15, 48, 'Early Adopter', '🌟', '2025-11-19 18:31:13'),
(16, 49, 'Early Adopter', '🌟', '2025-11-19 18:31:39'),
(17, 50, 'Early Adopter', '🌟', '2025-11-26 12:02:27'),
(18, 51, 'Early Adopter', '🌟', '2025-11-26 13:53:32');

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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_feedback`
--

INSERT INTO `user_feedback` (`id`, `user_id`, `reviewer_id`, `project_id`, `context_type`, `context_id`, `rating`, `comment`, `tags`, `created_at`, `updated_at`) VALUES
(1, 50, 37, 13, 'project', 13, 2, 'kkk', 'teamwork', '2026-02-17 19:30:09', '2026-02-17 19:30:09'),
(2, 41, 37, 13, 'project', 13, 3, 'It was a pleasure working with Devinda. He communicated clearly, delivered quality work, and completed tasks on time. Very reliable and professional throughout the project.', 'quality,ontime,teamwork,communication', '2026-02-18 12:19:54', '2026-02-18 12:19:54');

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
(79, 51, 'data-analytics', 'learn', 'advanced', '2025-11-26 13:54:03');

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
(20, 51, 0, 0, 0, 0);

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
(11, 51, 250.00, '2025-12-06 10:59:43', '2025-12-06 10:59:43');

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
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `project_applications`
--
ALTER TABLE `project_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_applied_at` (`applied_at`);

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
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_reported_user` (`reported_user_id`),
  ADD KEY `idx_reporter_org` (`reporter_org_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `community_members`
--
ALTER TABLE `community_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exchanges`
--
ALTER TABLE `exchanges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `project_applications`
--
ALTER TABLE `project_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `project_chat_messages`
--
ALTER TABLE `project_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_feedback`
--
ALTER TABLE `user_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_projects`
--
ALTER TABLE `user_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `user_stats`
--
ALTER TABLE `user_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `fk_report_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_reported_user` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_reporter_org` FOREIGN KEY (`reporter_org_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
