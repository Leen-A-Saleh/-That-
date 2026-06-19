-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 05:01 AM
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
-- Database: `that_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(80) DEFAULT NULL,
  `activity_type` varchar(20) NOT NULL DEFAULT 'TASK' CHECK (`activity_type` in ('TASK','GAME','EXERCISE')),
  `duration_min` smallint(5) UNSIGNED DEFAULT NULL,
  `difficulty` varchar(10) NOT NULL DEFAULT 'EASY' CHECK (`difficulty` in ('EASY','MEDIUM','HARD')),
  `status` varchar(10) NOT NULL DEFAULT 'ACTIVE' CHECK (`status` in ('ACTIVE','DRAFT')),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `game_key` varchar(50) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `title`, `description`, `category`, `activity_type`, `duration_min`, `difficulty`, `status`, `created_by`, `created_at`, `views`, `game_key`, `link`) VALUES
(1, 'تمرين التنفس العميق 4-7-8', 'تقنية تنفس تساعد على الاسترخاء وتخفيف القلق', 'تنفس واسترخاء', 'EXERCISE', 10, 'EASY', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(2, 'مذكرات الامتنان اليومية', 'كتابة 3 أشياء تشعر بالامتنان لها كل يوم', 'كتابة', 'TASK', 15, 'EASY', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(3, 'استرخاء العضلات التدريجي', 'تمرين استرخاء جسدي لتقليل التوتر العضلي', 'تنفس واسترخاء', 'EXERCISE', 20, 'MEDIUM', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(4, 'سجل الأفكار السلبية', 'توثيق الأفكار السلبية التلقائية وتحديّها', 'CBT', 'TASK', 15, 'MEDIUM', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(5, 'لعبة التركيز الذهني', 'لعبة تنمي مهارات التركيز والانتباه', 'ألعاب ذهنية', 'GAME', 15, 'EASY', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(6, 'تمرين التعرض التدريجي', 'سلم مواقف مقلقة مرتب من الأسهل إلى الأصعب', 'CBT', 'EXERCISE', 30, 'HARD', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(7, 'تمارين اليقظة الذهنية (Mindfulness)', 'ممارسة يومية لليقظة والتواجد في اللحظة الحاضرة', 'يقظة ذهنية', 'EXERCISE', 20, 'MEDIUM', 'ACTIVE', NULL, '2026-05-06 03:04:09', 0, NULL, NULL),
(8, 'تمرين التنفس', 'لعبة تنفس واسترخاء تساعد على تهدئة التوتر وتحسين التركيز', 'تنفس واسترخاء', 'GAME', NULL, 'EASY', 'ACTIVE', 1, '2026-05-30 14:57:59', 128, 'Breathing', 'http://localhost/That-Copy/Client/client-games-page/breathing-game/index.php'),
(9, 'الفروقات بين الصور', 'لعبة العثور على الاختلافات بين الصور لتعزيز التركيز والانتباه', 'ألعاب ذهنية', 'GAME', NULL, 'EASY', 'ACTIVE', 1, '2026-05-30 14:57:59', 110, 'Difference', 'http://localhost/That-Copy/Client/client-games-page/difference-game/index.php'),
(10, 'حدد المكان', 'لعبة تحديد موقع العنصر الصحيح لتعزيز الإدراك البصري', 'ألعاب ذهنية', 'GAME', NULL, 'EASY', 'ACTIVE', 1, '2026-05-30 14:57:59', 64, 'Misplacedpin', 'http://localhost/That-Copy/Client/client-games-page/misplacedpin/index.php'),
(11, 'لعبة البطاقات', 'لعبة الذاكرة باستخدام البطاقات لتحسين الذاكرة قصيرة المدى', 'يقظة ذهنية', 'GAME', NULL, 'EASY', 'ACTIVE', 1, '2026-05-30 14:57:59', 146, 'Cards', 'http://localhost/That-Copy/Client/client-games-page/memory-game/index.php'),
(12, 'الكلمات المتقاطعة', 'لعبة الكلمات المتقاطعة لتعزيز المفردات والذاكرة اللغوية', 'ألعاب ذهنية', 'GAME', NULL, 'EASY', 'ACTIVE', 1, '2026-05-30 14:57:59', 112, 'Crossword', 'http://localhost/That-Copy/Client/client-games-page/crossword-game/index.php'),
(13, 'الأسئلة', 'لعبة أسئلة تعتمد على الإجابة الصحيحة لتعزيز المعرفة والتركيز', 'ألعاب ذهنية', 'GAME', NULL, 'EASY', 'ACTIVE', 1, '2026-05-30 14:57:59', 92, 'Questions', 'http://localhost/That-Copy/Client/client-games-page/questions/index.php');

-- --------------------------------------------------------

--
-- Table structure for table `activity_submissions`
--

CREATE TABLE `activity_submissions` (
  `submission_id` int(11) NOT NULL,
  `case_activity_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `submission_type` varchar(10) NOT NULL DEFAULT 'TEXT' CHECK (`submission_type` in ('TEXT','PDF','VIDEO','IMAGE','AUDIO')),
  `text_response` text DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'PENDING' CHECK (`status` in ('PENDING','REVIEWED','COMPLETED')),
  `therapist_feedback` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_uploads`
--

CREATE TABLE `activity_uploads` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `file_path` text NOT NULL,
  `file_type` enum('IMAGE','VIDEO') NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `alert_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `level` varchar(10) NOT NULL DEFAULT 'WARNING' CHECK (`level` in ('INFO','WARNING','CRITICAL')),
  `is_handled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `handled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `therapist_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `duration_min` smallint(5) UNSIGNED NOT NULL DEFAULT 60,
  `mode` varchar(10) NOT NULL CHECK (`mode` in ('ONLINE','IN_CENTER')),
  `room_number` varchar(20) DEFAULT NULL,
  `zoom_link` varchar(500) DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'REQUESTED' CHECK (`status` in ('REQUESTED','CONFIRMED','COMPLETED','CANCELLED')),
  `cancel_reason` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_type` enum('CONSULTATION','THERAPY') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `max_score` decimal(5,2) DEFAULT NULL,
  `question_count` smallint(5) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `title_ar` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`assessment_id`, `code`, `title`, `description`, `category`, `max_score`, `question_count`, `is_active`, `title_ar`) VALUES
(1, 'BECK', 'Beck Depression Inventory', 'قياس شدة أعراض الاكتئاب', 'الاكتئاب', 40.00, 21, 1, 'بيك للاكتئاب'),
(2, 'HOPKINS', 'Hopkins Symptom Checklist', 'تقييم أعراض القلق والاكتئاب', 'القلق والاكتئاب', 25.00, 25, 1, 'هوبكنز'),
(3, 'CHILD-MH', 'Child Mental Health Scale', 'تقييم الحالة النفسية للأطفال', 'الأطفال', 13.00, 17, 1, 'الحالة النفسية للأطفال'),
(4, 'SNAP', 'SNAP-IV ADHD Scale', 'تقييم أعراض فرط الحركة وتشتت الانتباه', 'ADHD', 39.00, 26, 1, 'SNAP'),
(5, 'STRESS', 'Perceived Stress Scale', 'قياس مستوى التوتر العام', 'ضغط نفسي', 0.00, 35, 1, 'التوتر العام'),
(6, 'SOCIAL_ANXIETY', 'Social Anxiety Scale', 'تقييم القلق في المواقف الاجتماعية', 'القلق الاجتماعي', 0.00, 29, 1, 'القلق الاجتماعي');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_results`
--

CREATE TABLE `assessment_results` (
  `result_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `trait_score` decimal(5,2) DEFAULT NULL,
  `level` enum('MINIMAL','LOW','MEDIUM','HIGH','SEVERE') DEFAULT NULL,
  `status` enum('PENDING','COMPLETED','FAILED','CANCELED') NOT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `raw_answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_answers`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `raw_result` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_suggestions`
--

CREATE TABLE `assessment_suggestions` (
  `suggestion_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `suggested_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `case_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `therapist_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('IN_PROGRESS','UNDER_REVIEW','CLOSED') NOT NULL DEFAULT 'IN_PROGRESS',
  `priority` varchar(10) NOT NULL DEFAULT 'MEDIUM' CHECK (`priority` in ('LOW','MEDIUM','HIGH')),
  `is_flagged` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 CHECK (`progress` between 0 and 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_activities`
--

CREATE TABLE `case_activities` (
  `id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `due_date` datetime DEFAULT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL CHECK (`gender` in ('MALE','FEMALE')),
  `date_of_birth` date DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `treatment_type` enum('INDIVIDUAL_THERAPY','COUPLES_THERAPY','CHILD_ADOLESCENT_BEHAVIORAL_THERAPY') NOT NULL,
  `preferred_session_type` enum('ONLINE','IN_PERSON','BOTH') NOT NULL,
  `preferred_session_time` enum('MORNING','AFTERNOON','EVENING','FLEXIBLE') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_surveys`
--

CREATE TABLE `client_surveys` (
  `id` int(11) NOT NULL,
  `treatment_type` varchar(255) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `repeated_symptoms` text DEFAULT NULL,
  `prev_therapy` tinyint(1) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('MALE','FEMALE') NOT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `therapist_gender` enum('MALE','FEMALE','NO_PREFERENCE') NOT NULL,
  `family_history` enum('YES','NO') NOT NULL,
  `physical_issues` enum('YES','NO') NOT NULL,
  `physical_details` text DEFAULT NULL,
  `marital_status` enum('SINGLE','MARRIED','WIDOWED','DIVORCED','IN_RELATIONSHIP','SEPARATED','PREFER_NOT_TO_SAY') NOT NULL,
  `education_level` enum('LESS_THAN_HIGH_SCHOOL','HIGH_SCHOOL','BACHELOR','MASTER','PHD','OTHER') NOT NULL,
  `smoking` enum('YES','NO') NOT NULL,
  `alcohol` enum('YES','NO') NOT NULL,
  `drugs` enum('YES','NO') NOT NULL,
  `contact_preference` enum('WHATSAPP','EMAIL') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `focus_results`
--

CREATE TABLE `focus_results` (
  `focus_result_id` int(11) NOT NULL,
  `focus_test_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `completion_pct` decimal(5,2) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `improvement_pct` decimal(5,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `focus_tests`
--

CREATE TABLE `focus_tests` (
  `focus_test_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `test_type` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `focus_tests`
--

INSERT INTO `focus_tests` (`focus_test_id`, `title`, `description`, `test_type`, `is_active`) VALUES
(1, 'اختبار الانتباه المستمر', 'قياس قدرة الانتباه على فترات طويلة', 'ATTENTION', 1),
(2, 'اختبار سرعة التفاعل', 'قياس سرعة الاستجابة للمنبهات', 'REACTION', 1),
(3, 'اختبار التركيز البصري', 'قياس التركيز على الحفزات البصرية', 'VISUAL', 1);

-- --------------------------------------------------------

--
-- Table structure for table `game_results`
--

CREATE TABLE `game_results` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` int(11) NOT NULL,
  `game_name` enum('Breathing','Difference','Misplacedpin','Cards','Crossword','Questions') NOT NULL,
  `played_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `time_seconds` smallint(5) UNSIGNED DEFAULT NULL,
  `points` smallint(5) UNSIGNED DEFAULT NULL,
  `moves` smallint(5) UNSIGNED DEFAULT NULL,
  `difficulty` enum('EASY','MEDIUM') DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `time_per_level` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`time_per_level`)),
  `levels_passed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`levels_passed`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_activities`
--

CREATE TABLE `login_activities` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_date` date NOT NULL,
  `login_time` time NOT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_activities`
--

INSERT INTO `login_activities` (`login_id`, `user_id`, `login_date`, `login_time`, `browser`, `os`, `ip_address`, `country`, `city`, `created_at`) VALUES
(3, 2, '2026-05-10', '04:03:34', 'Chrome 147.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-10 05:03:34'),
(5, 4, '2026-05-10', '04:19:34', 'Chrome 147.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-10 05:19:34'),
(6, 4, '2026-05-10', '04:19:58', 'Chrome 147.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-10 05:19:58'),
(9, 4, '2026-05-10', '04:39:16', 'Chrome 147.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-10 05:39:16'),
(11, 4, '2026-05-10', '04:40:08', 'Chrome 147.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-10 05:40:08'),
(43, 3, '2026-05-25', '14:30:41', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-25 15:30:41'),
(49, 3, '2026-05-25', '20:35:27', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-25 21:35:27'),
(51, 3, '2026-05-25', '20:49:09', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-25 21:49:09'),
(56, 3, '2026-05-25', '21:26:15', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-25 22:26:15'),
(88, 3, '2026-05-30', '13:32:02', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-30 14:32:02'),
(90, 2, '2026-05-30', '16:28:44', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-05-30 17:28:44'),
(112, 1, '2026-06-06', '17:35:37', 'Chrome 148.0.0.0', 'Windows 10/11', '::1', NULL, NULL, '2026-06-06 18:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'TEXT' CHECK (`type` in ('TEXT','VOICE','IMAGE','FILE')),
  `file_path` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `type` enum('GENERAL','APPOINTMENT_REMINDER','MESSAGE','AD','SESSION_CONFIRMATION','ALERT','ACTIVITY_ASSIGNED','ASSESSMENT_READY','APPOINTMENT','ACTIVITY_REMINDER') NOT NULL DEFAULT 'GENERAL',
  `priority` varchar(10) NOT NULL DEFAULT 'NORMAL' CHECK (`priority` in ('LOW','NORMAL','URGENT')),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `body`, `type`, `priority`, `is_read`, `created_at`) VALUES
(35, 3, 'طلب موعد جديد', 'أرسل يوسف الزيتاوي طلب جلسة علاجية بتاريخ 2026-06-17 الساعة 1:00 مساءً. يرجى مراجعة طلبات المواعيد.', 'APPOINTMENT', 'NORMAL', 0, '2026-05-30 14:31:28');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `media_type` varchar(10) NOT NULL DEFAULT 'VIDEO' CHECK (`media_type` in ('VIDEO','AUDIO')),
  `room_token` varchar(255) DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `therapist_notes` text DEFAULT NULL,
  `status` enum('IN_PROGRESS','UNDER_REVIEW','CLOSED') NOT NULL DEFAULT 'IN_PROGRESS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_notes`
--

CREATE TABLE `session_notes` (
  `note_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `session_goals` text DEFAULT NULL,
  `mood` text DEFAULT NULL,
  `topics` text DEFAULT NULL,
  `techniques` text DEFAULT NULL,
  `progress` text DEFAULT NULL,
  `risk_assessment` text DEFAULT NULL,
  `therapist_notes` text DEFAULT NULL,
  `homework` text DEFAULT NULL,
  `next_plan` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `therapists`
--

CREATE TABLE `therapists` (
  `therapist_id` int(11) NOT NULL,
  `specialization` varchar(150) NOT NULL,
  `bio` text NOT NULL,
  `certification` varchar(50) NOT NULL,
  `experience_years` tinyint(3) UNSIGNED DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `rating_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(15) NOT NULL DEFAULT 'AVAILABLE' CHECK (`status` in ('AVAILABLE','BUSY','ON_LEAVE')),
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `therapists`
--

INSERT INTO `therapists` (`therapist_id`, `specialization`, `bio`, `certification`, `experience_years`, `rating`, `rating_count`, `status`, `color`) VALUES
(2, 'ماجستير إرشاد نفسي وتوجيه', 'خبرة أكثر من 8 سنوات في جلسات إرشادية للأطفال والبالغين والإرشاد الوالدي. تعمل من السبت إلى الخميس من 9 إلى 3:30.', 'مرخصة لعلاج الصدمات والعلاج السلوكي المعرفي', 8, 5.0, 0, 'AVAILABLE', '#00A63E'),
(3, 'ماجستير الصحة النفسية العلاجية', 'خبرة لأكثر من 6 سنوات في التعامل مع المراهقين والبالغين. تعمل الأحد والخميس من 8:30 إلى 3.', 'مرخصة لتقديم العلاج السلوكي المعرفي وعلاج الصدمات', 6, 5.0, 0, 'AVAILABLE', '#D08700'),
(4, 'ماجستير إرشاد نفسي وتوجيه333', 'خبرة أكثر من 4 سنوات في مجال الإرشاد. جلسات Online حسب الطلب.', 'إرشاد فردي وزواجي', 4, 5.0, 0, 'AVAILABLE', '#E11D48'),
(5, 'مختصة نفسية اجتماعية', 'خبرة أكثر من 6 سنوات في الإرشاد الفردي والأسري وإدارة المجموعات. تعمل من الاثنين إلى الخميس بعد الساعة 3.', 'بكالوريوس علم نفس فرعي علم اجتماع', 6, 5.0, 0, 'AVAILABLE', NULL),
(6, 'بكالوريوس السمع والنطق77', 'خبرة أكثر من 6 سنوات في علاج اضطرابات النطق عند الأطفال. تعمل السبت والأحد والثلاثاء والأربعاء من 9 إلى 4.', 'تطوير اللغة وتصحيح النطق وعلاج التأتأة', 6, 5.0, 0, 'AVAILABLE', NULL),
(7, 'ماجستير الصحة النفسية العلاجية', 'خبرة لأكثر من 10 سنوات في التعامل مع مختلف الأعمار والاضطرابات النفسية. حسب الطلب من الأحد إلى الأربعاء.', 'مرخص لإدارة اختبارات نفسية واختبارات الذكاء', 10, 5.0, 0, 'AVAILABLE', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `therapist_availability`
--

CREATE TABLE `therapist_availability` (
  `availability_id` int(11) NOT NULL,
  `therapist_id` int(11) DEFAULT NULL,
  `day_of_week` varchar(10) NOT NULL CHECK (`day_of_week` in ('SUNDAY','MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY')),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `therapist_availability`
--

INSERT INTO `therapist_availability` (`availability_id`, `therapist_id`, `day_of_week`, `start_time`, `end_time`, `is_active`) VALUES
(24, 3, 'WEDNESDAY', '13:00:00', '14:00:00', 1),
(25, 4, 'SUNDAY', '09:00:00', '10:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `therapist_reviews`
--

CREATE TABLE `therapist_reviews` (
  `review_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL CHECK (`role` in ('CLIENT','THERAPIST','ADMIN')),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_2fa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(50) DEFAULT NULL,
  `appointment_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `message_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `activity_reminder_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `session_version` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `avatar`, `role`, `is_active`, `created_at`, `updated_at`, `is_2fa_enabled`, `username`, `appointment_notifications`, `message_notifications`, `activity_reminder_notifications`, `session_version`) VALUES
(1, 'أحمد الخليلي', 'admin@that.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', '+970-599-100001', 'storage/ready_avatars/male/1.png', 'ADMIN', 1, '2026-05-06 03:04:09', '2026-06-06 18:35:32', 1, NULL, 1, 1, 1, 5),
(2, 'زينب كرمي', 'Zainab.karmi@hotmail.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', NULL, '../images/zaineb.png', 'THERAPIST', 1, '2026-04-15 18:44:14', '2026-06-07 05:59:40', 0, NULL, 1, 1, 1, 0),
(3, 'تسنيم زيدان', 'tasneemtherapist@gmail.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', NULL, '/That-Copy/storage/avatars/avatar_32_1779734955.jpg', 'THERAPIST', 1, '2026-04-15 18:44:14', '2026-06-07 05:59:48', 0, NULL, 1, 1, 1, 0),
(4, 'هديل ابو رميلة', 'hadeel.basman98@gmail.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', NULL, '../images/hadeel.png', 'THERAPIST', 1, '2026-04-15 18:44:14', '2026-06-07 05:59:58', 0, NULL, 1, 1, 1, 0),
(5, 'مها الرفاعي', 'mahaalrefaie96@gmail.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', NULL, '../images/maha.jpeg', 'THERAPIST', 1, '2026-04-15 18:44:14', '2026-06-07 06:00:05', 0, NULL, 1, 1, 1, 0),
(6, 'نيروز نجم الدين', 'n.nijem.aldeen98@gmail.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', NULL, '../images/neroz.png', 'THERAPIST', 1, '2026-04-15 18:44:14', '2026-06-07 06:00:12', 0, NULL, 1, 1, 1, 0),
(7, 'عمر قدح', 'omaraaq1989@gmail.com', '$2y$10$CwQQCjGjAizDrWPO6vfi0e1H3OqNublV5qHBZWLL9B9g38avLZXPS', NULL, '../images/omar.png', 'THERAPIST', 1, '2026-04-15 18:44:14', '2026-06-07 06:00:19', 0, NULL, 1, 1, 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `fk_activity_creator` (`created_by`);

--
-- Indexes for table `activity_submissions`
--
ALTER TABLE `activity_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `fk_sub_client` (`client_id`),
  ADD KEY `fk_sub_case_activity` (`case_activity_id`);

--
-- Indexes for table `activity_uploads`
--
ALTER TABLE `activity_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_uploads_client` (`client_id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `fk_alert_case` (`case_id`),
  ADD KEY `idx_alerts_user` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `fk_appt_therapist` (`therapist_id`),
  ADD KEY `idx_appt_datetime` (`date_time`),
  ADD KEY `idx_appt_status` (`status`),
  ADD KEY `fk_appt_client` (`client_id`),
  ADD KEY `fk_appt_case` (`case_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `uq_assessment_code` (`code`);

--
-- Indexes for table `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `fk_ar_assessment` (`assessment_id`),
  ADD KEY `fk_ar_client` (`client_id`),
  ADD KEY `fk_ar_case` (`case_id`);

--
-- Indexes for table `assessment_suggestions`
--
ALTER TABLE `assessment_suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `fk_assessment_suggestion_client` (`client_id`),
  ADD KEY `fk_assessment_suggestion_therapist` (`therapist_id`),
  ADD KEY `fk_assessment_suggestion_assessment` (`assessment_id`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`case_id`),
  ADD KEY `idx_cases_client` (`client_id`),
  ADD KEY `idx_cases_therapist` (`therapist_id`),
  ADD KEY `idx_cases_status` (`status`);

--
-- Indexes for table `case_activities`
--
ALTER TABLE `case_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ca_case` (`case_id`),
  ADD KEY `fk_ca_activity` (`activity_id`),
  ADD KEY `fk_ca_assigned_by` (`assigned_by`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `fk_client_survey` (`survey_id`);

--
-- Indexes for table `client_surveys`
--
ALTER TABLE `client_surveys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `focus_results`
--
ALTER TABLE `focus_results`
  ADD PRIMARY KEY (`focus_result_id`),
  ADD KEY `fk_fr_test` (`focus_test_id`),
  ADD KEY `fk_fr_case` (`case_id`),
  ADD KEY `fk_fr_client` (`client_id`);

--
-- Indexes for table `focus_tests`
--
ALTER TABLE `focus_tests`
  ADD PRIMARY KEY (`focus_test_id`);

--
-- Indexes for table `game_results`
--
ALTER TABLE `game_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gr_client` (`client_id`);

--
-- Indexes for table `login_activities`
--
ALTER TABLE `login_activities`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `fk_login_user` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_msg_case` (`case_id`),
  ADD KEY `idx_msg_sender` (`sender_id`),
  ADD KEY `idx_msg_receiver` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notif_user` (`user_id`),
  ADD KEY `idx_notif_read` (`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_client` (`client_id`),
  ADD KEY `fk_payment_therapist` (`therapist_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD KEY `fk_session_case` (`case_id`);

--
-- Indexes for table `session_notes`
--
ALTER TABLE `session_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `fk_note_session` (`session_id`);

--
-- Indexes for table `therapists`
--
ALTER TABLE `therapists`
  ADD PRIMARY KEY (`therapist_id`),
  ADD UNIQUE KEY `color` (`color`);

--
-- Indexes for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `fk_avail_therapist` (`therapist_id`);

--
-- Indexes for table `therapist_reviews`
--
ALTER TABLE `therapist_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_client` (`client_id`),
  ADD KEY `idx_reviews_therapist` (`therapist_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `activity_submissions`
--
ALTER TABLE `activity_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `activity_uploads`
--
ALTER TABLE `activity_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `assessment_results`
--
ALTER TABLE `assessment_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `assessment_suggestions`
--
ALTER TABLE `assessment_suggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `case_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `case_activities`
--
ALTER TABLE `case_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `client_surveys`
--
ALTER TABLE `client_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `focus_results`
--
ALTER TABLE `focus_results`
  MODIFY `focus_result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `focus_tests`
--
ALTER TABLE `focus_tests`
  MODIFY `focus_test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `game_results`
--
ALTER TABLE `game_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `login_activities`
--
ALTER TABLE `login_activities`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `session_notes`
--
ALTER TABLE `session_notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `therapist_reviews`
--
ALTER TABLE `therapist_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activity_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `activity_submissions`
--
ALTER TABLE `activity_submissions`
  ADD CONSTRAINT `fk_sub_case_activity` FOREIGN KEY (`case_activity_id`) REFERENCES `case_activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sub_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `activity_uploads`
--
ALTER TABLE `activity_uploads`
  ADD CONSTRAINT `fk_activity_uploads_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `fk_alert_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appt_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appt_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`) ON UPDATE CASCADE;

--
-- Constraints for table `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD CONSTRAINT `fk_ar_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ar_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ar_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assessment_suggestions`
--
ALTER TABLE `assessment_suggestions`
  ADD CONSTRAINT `fk_assessment_suggestion_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assessment_suggestion_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assessment_suggestion_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_case_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_case_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`) ON UPDATE CASCADE;

--
-- Constraints for table `case_activities`
--
ALTER TABLE `case_activities`
  ADD CONSTRAINT `fk_ca_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ca_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ca_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `fk_client_survey` FOREIGN KEY (`survey_id`) REFERENCES `client_surveys` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_client_user` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `focus_results`
--
ALTER TABLE `focus_results`
  ADD CONSTRAINT `fk_fr_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fr_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fr_test` FOREIGN KEY (`focus_test_id`) REFERENCES `focus_tests` (`focus_test_id`) ON UPDATE CASCADE;

--
-- Constraints for table `game_results`
--
ALTER TABLE `game_results`
  ADD CONSTRAINT `fk_gr_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `login_activities`
--
ALTER TABLE `login_activities`
  ADD CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_session_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_session_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `session_notes`
--
ALTER TABLE `session_notes`
  ADD CONSTRAINT `fk_note_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `therapists`
--
ALTER TABLE `therapists`
  ADD CONSTRAINT `fk_therapist_user` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `therapist_availability`
--
ALTER TABLE `therapist_availability`
  ADD CONSTRAINT `fk_avail_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `therapist_reviews`
--
ALTER TABLE `therapist_reviews`
  ADD CONSTRAINT `fk_review_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`therapist_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
