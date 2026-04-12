CREATE DATABASE IF NOT EXISTS That_db CHARACTER
SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE That_db;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;
-- ============================================================
-- DROP TABLES
-- ============================================================
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS focus_results;
DROP TABLE IF EXISTS focus_tests;
DROP TABLE IF EXISTS assessment_results;
DROP TABLE IF EXISTS assessments;
DROP TABLE IF EXISTS activity_submissions;
DROP TABLE IF EXISTS case_activities;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS therapist_availability;
DROP TABLE IF EXISTS therapists;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;
-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('CLIENT', 'THERAPIST', 'ADMIN')),
    is_active TINYINT (1) DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 2. CLIENTS
-- ============================================================
CREATE TABLE clients (
    client_id INT,
    gender VARCHAR(10) NULL CHECK (gender IN ('MALE', 'FEMALE')),
    date_of_birth DATE NULL,
    city VARCHAR(100) NULL,
    PRIMARY KEY (client_id),
    CONSTRAINT fk_client_user FOREIGN KEY (client_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ============================================================
-- 3. THERAPISTS
-- ============================================================
CREATE TABLE therapists (
    therapist_id INT,
    specialization VARCHAR(150) NOT NULL,
    bio TEXT NOT NULL,
    certification VARCHAR(50) NOT NULL,
    experience_years TINYINT UNSIGNED NULL,
    rating DECIMAL(3, 1) NULL DEFAULT 0.0,
    rating_count INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (therapist_id),
    CONSTRAINT fk_therapist_user FOREIGN KEY (therapist_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 4. THERAPIST AVAILABILITY
-- ============================================================
CREATE TABLE therapist_availability (
    availability_id INT AUTO_INCREMENT,
    therapist_id INT,
    day_of_week VARCHAR(10) NOT NULL CHECK (
        day_of_week IN (
            'SUNDAY',
            'MONDAY',
            'TUESDAY',
            'WEDNESDAY',
            'THURSDAY',
            'FRIDAY',
            'SATURDAY'
        )
    ),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    PRIMARY KEY (availability_id),
    CONSTRAINT fk_avail_therapist FOREIGN KEY (therapist_id) REFERENCES therapists (therapist_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 5. CASES
-- ============================================================
CREATE TABLE cases (
    case_id INT AUTO_INCREMENT,
    client_id INT,
    therapist_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'NEW' CHECK (
        status IN (
            'NEW',
            'IN_ASSESSMENT',
            'IN_THERAPY',
            'ON_HOLD',
            'RECOVERED'
        )
    ),
    priority VARCHAR(10) NOT NULL DEFAULT 'MEDIUM' CHECK (priority IN ('LOW', 'MEDIUM', 'HIGH')),
    is_flagged TINYINT (1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,
    PRIMARY KEY (case_id),
    CONSTRAINT fk_case_client FOREIGN KEY (client_id) REFERENCES clients (client_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_case_therapist FOREIGN KEY (therapist_id) REFERENCES therapists (therapist_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 6. APPOINTMENTS
-- ============================================================
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT,
    case_id INT,
    therapist_id INT ,
    client_id INT,
    date_time DATETIME NOT NULL,
    duration_min SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    mode VARCHAR(10) NOT NULL CHECK (mode IN ('ONLINE', 'IN_CENTER')),
    room_number VARCHAR(20) NULL,
    status VARCHAR(15) NOT NULL DEFAULT 'REQUESTED' CHECK (
        status IN (
            'REQUESTED',
            'CONFIRMED',
            'COMPLETED',
            'CANCELLED'
        )
    ),
    cancel_reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (appointment_id),
    CONSTRAINT fk_appt_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_appt_therapist FOREIGN KEY (therapist_id) REFERENCES therapists (therapist_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_appt_client FOREIGN KEY (client_id) REFERENCES clients (client_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 7. SESSIONS
-- ============================================================
CREATE TABLE sessions (
    session_id INT AUTO_INCREMENT,
    appointment_id INT UNIQUE,
    case_id INT,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    media_type VARCHAR(10) NOT NULL DEFAULT 'VIDEO' CHECK (media_type IN ('VIDEO', 'AUDIO')),
    room_token VARCHAR(255) NULL,
    meeting_link VARCHAR(500) NULL,
    therapist_notes TEXT NULL,
    PRIMARY KEY (session_id),
    CONSTRAINT fk_session_appt FOREIGN KEY (appointment_id) REFERENCES appointments (appointment_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_session_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 8. MESSAGES
-- ============================================================
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT,
    sender_id INT,
    receiver_id INT,
    case_id INT,
    content TEXT NULL,
    type VARCHAR(10) NOT NULL DEFAULT 'TEXT' CHECK (type IN ('TEXT', 'VOICE', 'IMAGE', 'FILE')),
    file_path VARCHAR(500) NULL,
    is_read TINYINT (1) NOT NULL DEFAULT 0,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id),
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_msg_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE
    SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 9. ACTIVITIES
-- ============================================================
CREATE TABLE activities (
    activity_id INT AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    category VARCHAR(80) NULL,
    activity_type VARCHAR(20) NOT NULL DEFAULT 'TASK' CHECK (activity_type IN ('TASK', 'GAME', 'EXERCISE')),
    created_by INT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_id),
    CONSTRAINT fk_activity_creator FOREIGN KEY (created_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 10. CASE_ACTIVITIES
-- ============================================================
CREATE TABLE case_activities (
    id INT AUTO_INCREMENT,
    case_id INT,
    activity_id INT,
    assigned_by INT,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    due_date DATETIME NULL,
    instructions TEXT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_ca_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ca_activity FOREIGN KEY (activity_id) REFERENCES activities (activity_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ca_assigned_by FOREIGN KEY (assigned_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 11. ACTIVITY_SUBMISSIONS
-- ============================================================
CREATE TABLE activity_submissions (
    submission_id INT AUTO_INCREMENT,
    case_activity_id INT,
    client_id INT,
    file_path VARCHAR(500) NULL,
    submission_type VARCHAR(10) NOT NULL DEFAULT 'TEXT' CHECK (
        submission_type IN ('TEXT', 'PDF', 'VIDEO', 'IMAGE', 'AUDIO')
    ),
    text_response TEXT NULL,
    status VARCHAR(15) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'REVIEWED', 'COMPLETED')),
    therapist_feedback TEXT NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    PRIMARY KEY (submission_id),
    CONSTRAINT fk_sub_case_activity FOREIGN KEY (case_activity_id) REFERENCES case_activities (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_sub_client FOREIGN KEY (client_id) REFERENCES clients (client_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 12. ASSESSMENTS
-- ============================================================
CREATE TABLE assessments (
    assessment_id INT AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    duration_min TINYINT UNSIGNED NULL,
    question_count SMALLINT UNSIGNED NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    PRIMARY KEY (assessment_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 13. ASSESSMENT_RESULTS
-- ============================================================
CREATE TABLE assessment_results (
    result_id INT AUTO_INCREMENT,
    assessment_id INT,
    client_id INT,
    case_id INT,
    trait_score DECIMAL(5, 2) NULL,
    level VARCHAR(20) NULL,
    raw_answers JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (result_id),
    CONSTRAINT fk_ar_assessment FOREIGN KEY (assessment_id) REFERENCES assessments (assessment_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ar_client FOREIGN KEY (client_id) REFERENCES clients (client_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ar_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE
    SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 14. FOCUS_TESTS
-- ============================================================
CREATE TABLE focus_tests (
    focus_test_id INT AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    test_type VARCHAR(50) NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    PRIMARY KEY (focus_test_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 15. FOCUS_RESULTS
-- ============================================================
CREATE TABLE focus_results (
    focus_result_id INT AUTO_INCREMENT,
    focus_test_id INT,
    client_id INT,
    case_id INT,
    duration_seconds INT UNSIGNED NULL,
    completion_pct DECIMAL(5, 2) NULL,
    score DECIMAL(5, 2) NULL,
    improvement_pct DECIMAL(5, 2) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (focus_result_id),
    CONSTRAINT fk_fr_test FOREIGN KEY (focus_test_id) REFERENCES focus_tests (focus_test_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_fr_client FOREIGN KEY (client_id) REFERENCES clients (client_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_fr_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE
    SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 16. ALERTS
-- ============================================================
CREATE TABLE alerts (
    alert_id INT AUTO_INCREMENT,
    user_id INT,
    case_id INT,
    description TEXT NOT NULL,
    level VARCHAR(10) NOT NULL DEFAULT 'WARNING' CHECK (level IN ('INFO', 'WARNING', 'CRITICAL')),
    is_handled TINYINT (1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    handled_at DATETIME NULL,
    PRIMARY KEY (alert_id),
    CONSTRAINT fk_alert_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_alert_case FOREIGN KEY (case_id) REFERENCES cases (case_id) ON DELETE
    SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- 17. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'GENERAL' CHECK (
        type IN (
            'GENERAL',
            'APPOINTMENT_REMINDER',
            'SESSION_CONFIRMATION',
            'ALERT',
            'ACTIVITY_ASSIGNED',
            'ASSESSMENT_READY'
        )
    ),
    is_read TINYINT (1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- ============================================================
-- INDEXES
-- ============================================================
CREATE INDEX idx_users_role ON users (role);
CREATE INDEX idx_cases_client ON cases (client_id);
CREATE INDEX idx_cases_therapist ON cases (therapist_id);
CREATE INDEX idx_cases_status ON cases (status);
CREATE INDEX idx_appt_datetime ON appointments (date_time);
CREATE INDEX idx_appt_status ON appointments (status);
CREATE INDEX idx_msg_sender ON messages (sender_id);
CREATE INDEX idx_msg_receiver ON messages (receiver_id);
CREATE INDEX idx_notif_user ON notifications (user_id);
CREATE INDEX idx_notif_read ON notifications (is_read);
CREATE INDEX idx_alerts_user ON alerts (user_id);