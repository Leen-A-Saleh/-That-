-- ============================================================
-- Seed data for That / ذات
-- ============================================================
-- Usage:
--   mysql -u root that_db < seed.sql
--
-- Default password for EVERY seeded account: password123
--
-- Login samples:
--   Therapist  → email: sara@that.com     / password: password123
--   Therapist  → email: rami@that.com     / password: password123
--   Client     → email: omar@that.com     / password: password123
--   Admin      → email: admin@that.com    / password: password123
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER_SET_CLIENT = utf8mb4;
SET CHARACTER_SET_RESULTS = utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

USE that_db;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ------------------------------------------------------------
-- Wipe everything (schema stays intact)
-- ------------------------------------------------------------
TRUNCATE TABLE therapist_reviews;
TRUNCATE TABLE notifications;
TRUNCATE TABLE alerts;
TRUNCATE TABLE focus_results;
TRUNCATE TABLE focus_tests;
TRUNCATE TABLE assessment_results;
TRUNCATE TABLE assessments;
TRUNCATE TABLE activity_submissions;
TRUNCATE TABLE case_activities;
TRUNCATE TABLE activities;
TRUNCATE TABLE session_notes;
TRUNCATE TABLE sessions;
TRUNCATE TABLE messages;
TRUNCATE TABLE appointments;
TRUNCATE TABLE cases;
TRUNCATE TABLE therapist_availability;
TRUNCATE TABLE therapists;
TRUNCATE TABLE clients;
TRUNCATE TABLE client_surveys;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE users;

-- ============================================================
-- 1. USERS
-- Password for all: password123
-- ============================================================
INSERT INTO users (user_id, name, username, email, password, phone, avatar, role, is_active) VALUES
(1,  'أحمد الخليلي',      'admin',   'admin@that.com',   '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100001', NULL, 'ADMIN',     1),
(2,  'د. سارة الجعبري',    'sara',    'sara@that.com',    '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100002', NULL, 'THERAPIST', 1),
(3,  'د. رامي النجار',     'rami',    'rami@that.com',    '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100003', NULL, 'THERAPIST', 1),
(4,  'عمر الخطيب',         'omar',    'omar@that.com',    '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100004', NULL, 'CLIENT',    1),
(5,  'ليلى الحسيني',       'laila',   'laila@that.com',   '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100005', NULL, 'CLIENT',    1),
(6,  'محمود درويش',        'mahmoud', 'mahmoud@that.com', '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100006', NULL, 'CLIENT',    1),
(7,  'نور البرغوثي',       'nour',    'nour@that.com',    '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100007', NULL, 'CLIENT',    1),
(8,  'يوسف الزيتاوي',      'youssef', 'youssef@that.com', '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100008', NULL, 'CLIENT',    1),
(9,  'فاطمة العجوري',      'fatima',  'fatima@that.com',  '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100009', NULL, 'CLIENT',    1),
(10, 'طارق أبو عرب',       'tareq',   'tareq@that.com',   '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100010', NULL, 'CLIENT',    1),
(11, 'سلمى الشوا',         'salma',   'salma@that.com',   '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100011', NULL, 'CLIENT',    1),
(12, 'رنا شاهين',          'rana',    'rana@that.com',    '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100012', NULL, 'CLIENT',    1),
(13, 'خالد الصالحي',       'khaled',  'khaled@that.com',  '$2y$12$g4rAyUqHvjWs1u/cWR.yAOXdSEIfZs65yFUDwzGq31fwDb8yzLLpe', '+970-599-100013', NULL, 'CLIENT',    1);

-- ============================================================
-- 2. CLIENT SURVEYS
-- ============================================================
INSERT INTO client_surveys (id, treatment_type, symptoms, repeated_symptoms, prev_therapy, age, gender, nationality, therapist_gender, family_history, physical_issues, physical_details, marital_status, education_level, smoking, alcohol, drugs, contact_preference) VALUES
(1,  'INDIVIDUAL_THERAPY',                         'قلق، أرق، توتر',              'قلق متكرر قبل النوم',            0, 28, 'MALE',   'فلسطيني', 'NO_PREFERENCE', 'NO',  'NO',  NULL,             'SINGLE',            'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(2,  'INDIVIDUAL_THERAPY',                         'اكتئاب، فقدان شهية',          'مزاج منخفض لفترات طويلة',         1, 34, 'FEMALE', 'فلسطيني', 'FEMALE',        'YES', 'NO',  NULL,             'MARRIED',           'MASTER',     'NO',  'NO', 'NO', 'WHATSAPP'),
(3,  'INDIVIDUAL_THERAPY',                         'اكتئاب بعد حادث',             'أفكار سلبية متكررة',              0, 45, 'MALE',   'فلسطيني', 'MALE',          'NO',  'YES', 'آلام ظهر مزمنة', 'MARRIED',           'HIGH_SCHOOL','YES', 'NO', 'NO', 'EMAIL'),
(4,  'INDIVIDUAL_THERAPY',                         'اضطرابات النوم',              'استيقاظ متكرر ليلاً',              0, 29, 'FEMALE', 'فلسطيني', 'FEMALE',        'NO',  'NO',  NULL,             'SINGLE',            'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(5,  'INDIVIDUAL_THERAPY',                         'توتر في العمل',               'صعوبة في التركيز',                0, 32, 'MALE',   'فلسطيني', 'NO_PREFERENCE', 'NO',  'NO',  NULL,             'MARRIED',           'MASTER',     'NO',  'NO', 'NO', 'WHATSAPP'),
(6,  'COUPLES_THERAPY',                            'خلافات زوجية',                'نزاعات متكررة',                   1, 31, 'FEMALE', 'فلسطيني', 'FEMALE',        'NO',  'NO',  NULL,             'MARRIED',           'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(7,  'CHILD_ADOLESCENT_BEHAVIORAL_THERAPY',        'سلوك عدواني لدى الابن',       'نوبات غضب متكررة',                0, 38, 'MALE',   'فلسطيني', 'MALE',          'NO',  'NO',  NULL,             'MARRIED',           'BACHELOR',   'YES', 'NO', 'NO', 'EMAIL'),
(8,  'INDIVIDUAL_THERAPY',                         'قلق اجتماعي',                 'خوف من التجمعات',                 0, 24, 'FEMALE', 'فلسطيني', 'FEMALE',        'NO',  'NO',  NULL,             'SINGLE',            'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(9,  'INDIVIDUAL_THERAPY',                         'صدمة بعد حادث',               'كوابيس متكررة',                   1, 40, 'MALE',   'فلسطيني', 'MALE',          'YES', 'YES', 'آثار جرح قديم', 'WIDOWED',           'HIGH_SCHOOL','NO',  'NO', 'NO', 'EMAIL'),
(10, 'INDIVIDUAL_THERAPY',                         'وسواس قهري',                  'أفكار تطفّلية',                   0, 27, 'FEMALE', 'فلسطيني', 'NO_PREFERENCE', 'NO',  'NO',  NULL,             'SINGLE',            'MASTER',     'NO',  'NO', 'NO', 'WHATSAPP');

-- ============================================================
-- 3. THERAPISTS
-- ============================================================
INSERT INTO therapists (therapist_id, specialization, bio, certification, experience_years, rating, rating_count) VALUES
(2, 'علاج القلق والاكتئاب',           'أخصائية نفسية معتمدة مع خبرة واسعة في علاج القلق والاكتئاب واضطرابات المزاج. أؤمن بالعلاج المتمركز حول المريض وأوفر بيئة آمنة وداعمة للعلاج والشفاء.', 'CBT Certified',  8, 4.8, 5),
(3, 'العلاج الأسري والعلاقات الزوجية', 'معالج نفسي متخصص في العلاج الأسري والعلاج السلوكي للأطفال والمراهقين. خبرة تزيد عن 10 سنوات في المجال.',                                       'ACBT Certified', 10, 4.6, 3);

-- ============================================================
-- 4. CLIENTS
-- ============================================================
INSERT INTO clients (client_id, survey_id, gender, date_of_birth, city, treatment_type, preferred_session_type, preferred_session_time) VALUES
(4,  1,  'MALE',   '1997-04-12', 'رام الله',  'INDIVIDUAL_THERAPY',                    'ONLINE',    'EVENING'),
(5,  2,  'FEMALE', '1991-09-25', 'نابلس',     'INDIVIDUAL_THERAPY',                    'BOTH',      'MORNING'),
(6,  3,  'MALE',   '1980-02-08', 'الخليل',    'INDIVIDUAL_THERAPY',                    'IN_PERSON', 'AFTERNOON'),
(7,  4,  'FEMALE', '1996-11-30', 'بيت لحم',   'INDIVIDUAL_THERAPY',                    'ONLINE',    'EVENING'),
(8,  5,  'MALE',   '1993-07-14', 'جنين',      'INDIVIDUAL_THERAPY',                    'BOTH',      'FLEXIBLE'),
(9,  6,  'FEMALE', '1994-03-19', 'طولكرم',    'COUPLES_THERAPY',                       'IN_PERSON', 'AFTERNOON'),
(10, 7,  'MALE',   '1987-08-05', 'غزة',       'CHILD_ADOLESCENT_BEHAVIORAL_THERAPY',   'ONLINE',    'MORNING'),
(11, 8,  'FEMALE', '2001-05-22', 'القدس',     'INDIVIDUAL_THERAPY',                    'BOTH',      'EVENING'),
(12, 9,  'MALE',   '1985-12-03', 'أريحا',     'INDIVIDUAL_THERAPY',                    'IN_PERSON', 'AFTERNOON'),
(13, 10, 'FEMALE', '1998-10-09', 'قلقيلية',   'INDIVIDUAL_THERAPY',                    'ONLINE',    'MORNING');

-- ============================================================
-- 5. THERAPIST AVAILABILITY
-- ============================================================
INSERT INTO therapist_availability (therapist_id, day_of_week, start_time, end_time, is_active) VALUES
(2, 'SUNDAY',    '09:00:00', '10:00:00', 1),
(2, 'SUNDAY',    '10:00:00', '11:00:00', 1),
(2, 'SUNDAY',    '11:00:00', '12:00:00', 1),
(2, 'SUNDAY',    '14:00:00', '15:00:00', 1),
(2, 'SUNDAY',    '15:00:00', '16:00:00', 1),
(2, 'MONDAY',    '09:00:00', '10:00:00', 1),
(2, 'MONDAY',    '11:00:00', '12:00:00', 1),
(2, 'MONDAY',    '14:00:00', '15:00:00', 1),
(2, 'MONDAY',    '15:00:00', '16:00:00', 1),
(2, 'TUESDAY',   '09:00:00', '10:00:00', 1),
(2, 'TUESDAY',   '10:00:00', '11:00:00', 1),
(2, 'TUESDAY',   '14:00:00', '15:00:00', 1),
(2, 'WEDNESDAY', '09:00:00', '10:00:00', 1),
(2, 'WEDNESDAY', '10:00:00', '11:00:00', 1),
(2, 'WEDNESDAY', '14:00:00', '15:00:00', 1),
(2, 'THURSDAY',  '09:00:00', '10:00:00', 1),
(2, 'THURSDAY',  '14:00:00', '15:00:00', 1),
(2, 'THURSDAY',  '16:00:00', '17:00:00', 1),
(3, 'SUNDAY',    '10:00:00', '11:00:00', 1),
(3, 'MONDAY',    '10:00:00', '11:00:00', 1),
(3, 'TUESDAY',   '11:00:00', '12:00:00', 1),
(3, 'WEDNESDAY', '13:00:00', '14:00:00', 1),
(3, 'THURSDAY',  '13:00:00', '14:00:00', 1);

-- ============================================================
-- 6. CASES
-- ============================================================
INSERT INTO cases (case_id, client_id, therapist_id, title, description, status, priority, is_flagged, created_at, last_updated, closed_at) VALUES
(1, 4,  2, 'اضطراب القلق العام',           'قلق متكرر وأرق، يحتاج إلى علاج معرفي سلوكي',         'IN_THERAPY',   'MEDIUM', 0, '2026-02-01 10:00:00', '2026-04-15 10:00:00', NULL),
(2, 5,  2, 'اكتئاب متوسط',                 'أعراض اكتئابية متوسطة مع فقدان شهية',                 'IN_THERAPY',   'HIGH',   1, '2026-01-15 11:00:00', '2026-04-18 09:30:00', NULL),
(3, 6,  2, 'اكتئاب بعد حادث عمل',          'أعراض اكتئاب بعد التعرّض لحادث في العمل',             'IN_ASSESSMENT','MEDIUM', 0, '2026-03-10 09:00:00', '2026-04-10 09:30:00', NULL),
(4, 7,  2, 'اضطرابات النوم',               'صعوبة في النوم والاستيقاظ المتكرر',                    'IN_THERAPY',   'LOW',    0, '2026-02-20 14:00:00', '2026-04-19 14:00:00', NULL),
(5, 8,  2, 'توتر مهني وضغط نفسي',          'ضغوط عمل متواصلة أدت لتوتر ومشاكل تركيز',              'IN_THERAPY',   'MEDIUM', 0, '2026-03-01 12:00:00', '2026-04-08 12:00:00', NULL),
(6, 9,  2, 'خلافات زوجية',                 'نزاعات متكررة مع الزوج وحاجة لإعادة بناء الثقة',       'IN_ASSESSMENT','MEDIUM', 0, '2026-03-15 10:00:00', '2026-04-12 10:00:00', NULL),
(7, 10, 3, 'سلوك عدواني لدى الابن',         'الابن بعمر 8 سنوات يظهر نوبات غضب في المدرسة',          'NEW',          'HIGH',   0, '2026-04-05 09:00:00', '2026-04-05 09:00:00', NULL),
(8, 11, 3, 'قلق اجتماعي',                  'خوف من التحدث أمام مجموعات وصعوبة في المواقف الاجتماعية','IN_THERAPY',   'MEDIUM', 0, '2026-02-10 15:00:00', '2026-04-14 15:00:00', NULL),
(9, 12, 3, 'اضطراب ما بعد الصدمة',         'أعراض ما بعد الصدمة بعد فقدان شخص عزيز',               'IN_THERAPY',   'HIGH',   1, '2026-02-15 16:00:00', '2026-04-14 16:00:00', NULL),
(10,13, 3, 'وسواس قهري',                   'أفكار تطفّلية متكررة وسلوكيات قسرية',                  'RECOVERED',    'LOW',    0, '2025-10-01 10:00:00', '2026-03-20 10:00:00', '2026-03-20 10:00:00');

-- ============================================================
-- 7. APPOINTMENTS
-- ============================================================
INSERT INTO appointments (appointment_id, case_id, therapist_id, client_id, date_time, duration_min, mode, room_number, zoom_link, status, cancel_reason, created_at, last_updated) VALUES
(1,  1, 2, 4,  DATE_SUB(CURDATE(), INTERVAL 14 DAY) + INTERVAL 10 HOUR, 60, 'ONLINE',    NULL, 'https://zoom.us/j/111222333', 'COMPLETED', NULL, '2026-04-01 09:00:00', '2026-04-09 11:00:00'),
(2,  1, 2, 4,  CURDATE() + INTERVAL 10 HOUR,                             60, 'ONLINE',    NULL, 'https://zoom.us/j/111222333', 'CONFIRMED', NULL, '2026-04-10 09:00:00', '2026-04-10 09:00:00'),
(3,  1, 2, 4,  CURDATE() + INTERVAL 7 DAY + INTERVAL 10 HOUR,             60, 'ONLINE',    NULL, NULL,                          'REQUESTED', NULL, '2026-04-20 09:00:00', '2026-04-20 09:00:00'),

(4,  2, 2, 5,  DATE_SUB(CURDATE(), INTERVAL 21 DAY) + INTERVAL 11 HOUR, 60, 'IN_CENTER', '305',NULL,                          'COMPLETED', NULL, '2026-03-25 10:00:00', '2026-04-02 12:00:00'),
(5,  2, 2, 5,  DATE_SUB(CURDATE(), INTERVAL 7 DAY) + INTERVAL 11 HOUR,  60, 'IN_CENTER', '305',NULL,                          'COMPLETED', NULL, '2026-04-05 10:00:00', '2026-04-16 12:00:00'),
(6,  2, 2, 5,  CURDATE() + INTERVAL 1 DAY + INTERVAL 11 HOUR,            60, 'IN_CENTER', '305',NULL,                          'CONFIRMED', NULL, '2026-04-18 10:00:00', '2026-04-18 10:00:00'),

(7,  3, 2, 6,  CURDATE() + INTERVAL 14 HOUR,                             60, 'IN_CENTER', '204',NULL,                          'CONFIRMED', NULL, '2026-04-15 09:00:00', '2026-04-15 09:00:00'),
(8,  3, 2, 6,  CURDATE() + INTERVAL 2 DAY + INTERVAL 14 HOUR,            60, 'IN_CENTER', '204',NULL,                          'REQUESTED', NULL, '2026-04-22 09:00:00', '2026-04-22 09:00:00'),

(9,  4, 2, 7,  DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 15 HOUR,  60, 'ONLINE',    NULL, 'https://zoom.us/j/444555666', 'COMPLETED', NULL, '2026-04-10 10:00:00', '2026-04-20 16:00:00'),
(10, 4, 2, 7,  CURDATE() + INTERVAL 5 DAY + INTERVAL 15 HOUR,            60, 'ONLINE',    NULL, NULL,                          'REQUESTED', NULL, '2026-04-21 09:00:00', '2026-04-21 09:00:00'),

(11, 5, 2, 8,  DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 12 HOUR,  60, 'ONLINE',    NULL, 'https://zoom.us/j/777888999', 'COMPLETED', NULL, '2026-04-05 08:00:00', '2026-04-17 13:00:00'),
(12, 5, 2, 8,  CURDATE() + INTERVAL 3 DAY + INTERVAL 12 HOUR,            60, 'ONLINE',    NULL, NULL,                          'CONFIRMED', NULL, '2026-04-18 08:00:00', '2026-04-18 08:00:00'),

(13, 6, 2, 9,  CURDATE() + INTERVAL 10 HOUR,                             60, 'IN_CENTER', '110',NULL,                          'REQUESTED', NULL, '2026-04-20 10:00:00', '2026-04-20 10:00:00'),

(14, 7, 3, 10, CURDATE() + INTERVAL 9 HOUR,                              60, 'ONLINE',    NULL, NULL,                          'REQUESTED', NULL, '2026-04-20 10:00:00', '2026-04-20 10:00:00'),

(15, 8, 3, 11, DATE_SUB(CURDATE(), INTERVAL 10 DAY) + INTERVAL 15 HOUR, 60, 'ONLINE',    NULL, 'https://zoom.us/j/123123123', 'COMPLETED', NULL, '2026-04-01 10:00:00', '2026-04-12 16:00:00'),
(16, 8, 3, 11, CURDATE() + INTERVAL 1 DAY + INTERVAL 15 HOUR,            60, 'ONLINE',    NULL, NULL,                          'CONFIRMED', NULL, '2026-04-19 10:00:00', '2026-04-19 10:00:00'),

(17, 9, 3, 12, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR,  60, 'IN_CENTER', '410',NULL,                          'COMPLETED', NULL, '2026-04-08 10:00:00', '2026-04-18 17:00:00'),
(18, 9, 3, 12, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 16 HOUR,  60, 'IN_CENTER', '410',NULL,                          'CANCELLED', 'المريض كان مريضاً', '2026-04-15 10:00:00', '2026-04-22 10:00:00');

-- ============================================================
-- 8. SESSIONS (created from COMPLETED appointments)
-- ============================================================
INSERT INTO sessions (session_id, appointment_id, case_id, start_time, end_time, media_type, room_token, meeting_link, therapist_notes) VALUES
(1, 1,  1, DATE_SUB(CURDATE(), INTERVAL 14 DAY) + INTERVAL 10 HOUR, DATE_SUB(CURDATE(), INTERVAL 14 DAY) + INTERVAL 11 HOUR, 'VIDEO', NULL, 'https://zoom.us/j/111222333', 'ركزنا على تمارين التنفس ومناقشة مصادر القلق'),
(2, 4,  2, DATE_SUB(CURDATE(), INTERVAL 21 DAY) + INTERVAL 11 HOUR, DATE_SUB(CURDATE(), INTERVAL 21 DAY) + INTERVAL 12 HOUR, 'VIDEO', NULL, NULL,                          'جلسة تقييم أولى، المريضة أظهرت أعراض اكتئاب متوسطة'),
(3, 5,  2, DATE_SUB(CURDATE(), INTERVAL 7 DAY) + INTERVAL 11 HOUR,  DATE_SUB(CURDATE(), INTERVAL 7 DAY) + INTERVAL 12 HOUR,  'VIDEO', NULL, NULL,                          'تحسن ملحوظ في الشهية، بدأنا تقنيات العلاج المعرفي السلوكي'),
(4, 9,  4, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 15 HOUR,  DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 16 HOUR,  'VIDEO', NULL, 'https://zoom.us/j/444555666', 'ناقشنا عادات النوم وأعطيتها جدول تتبع'),
(5, 11, 5, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 12 HOUR,  DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 13 HOUR,  'VIDEO', NULL, 'https://zoom.us/j/777888999', 'ركزنا على تقنيات إدارة التوتر وتحديد أولويات العمل'),
(6, 15, 8, DATE_SUB(CURDATE(), INTERVAL 10 DAY) + INTERVAL 15 HOUR, DATE_SUB(CURDATE(), INTERVAL 10 DAY) + INTERVAL 16 HOUR, 'VIDEO', NULL, 'https://zoom.us/j/123123123', 'جلسة تقييم أولي للقلق الاجتماعي'),
(7, 17, 9, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR,  DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR,  'VIDEO', NULL, NULL,                          'جلسة علاج EMDR، تقدم بطيء ولكن إيجابي');

-- ============================================================
-- 9. SESSION NOTES
-- ============================================================
INSERT INTO session_notes (note_id, session_id, session_goals, mood, topics, techniques, progress, risk_assessment, therapist_notes, homework, next_plan) VALUES
(1, 1, 'تقليل حدة نوبات القلق وتعلّم أدوات تنظيم ذاتي', 'قلق متوسط، المريض متعاون',         'القلق قبل النوم، ضغط العمل',   'تمارين تنفس 4-7-8، إعادة بناء معرفي',    'تحسن طفيف في جودة النوم',        'لا يوجد خطر',          'يبدي المريض التزاماً جيداً بالتمارين', 'ممارسة تمارين التنفس يومياً لمدة 10 دقائق', 'متابعة بعد أسبوع، إدخال مذكّرات يومية'),
(2, 2, 'بناء علاقة علاجية وفهم تاريخ الحالة',         'مزاج منخفض، بكاء خلال الجلسة',   'فقدان الشهية، العزلة',          'الاستماع الفعّال، تقييم PHQ-9',          'جلسة تقييم فقط',                'انتباه لاحتمال تردي', 'تحتاج إلى متابعة لصيقة الأسبوعين القادمين', 'تسجيل يومي للمزاج من 1 إلى 10',               'جلسة قادمة لمراجعة PHQ-9 وبدء CBT'),
(3, 3, 'تقديم تقنيات CBT أساسية',                      'أفضل من الجلسة السابقة',         'الأفكار السلبية التلقائية',      'إعادة هيكلة معرفية، تحدي الأفكار',        'تحسن ملحوظ، درجة PHQ-9 انخفضت', 'لا يوجد',             'المريضة تتفاعل جيداً مع الأدوات',           'ملء سجل الأفكار السلبية يومياً',              'التعمق في تقنية التفعيل السلوكي'),
(4, 4, 'تقييم عادات النوم',                            'هادئ، متعاون',                   'الأرق، الكوابيس',               'نظافة النوم، تقييد النوم',                'مرحلة أولية',                    'لا يوجد',             'نوم متقطع وسعة يومية للنوم ساعة واحدة فقط',  'جدول تتبع للنوم لمدة أسبوع',                  'مراجعة الجدول وتعديل الخطة'),
(5, 5, 'تعلّم إدارة التوتر',                            'متوتر لكنه منفتح',               'ضغط العمل، قلة التركيز',        'استرخاء عضلي متدرج، جدولة أولويات',        'تحسن في التعامل مع المهام',      'لا يوجد',             'يحتاج إلى رسم حدود مع العمل',                 'تطبيق تقنية بومودورو في العمل',                'متابعة خلال 10 أيام'),
(6, 6, 'تقييم أولي للقلق الاجتماعي',                    'قلق مرتفع في بداية الجلسة',       'الخوف من التجمعات',             'مقياس LSAS، حوار دافع',                   'جلسة تقييم',                    'لا يوجد',             'المريضة تتجنب مواقف كثيرة',                   'قائمة مواقف مقلقة مرتبة من 1 إلى 10',           'بدء التعرض التدريجي بالأسبوع القادم'),
(7, 7, 'معالجة ذاكرة صدمة الفقد',                      'ضاغط، بكاء خلال المعالجة',        'ذاكرة الحادث، مشاعر الذنب',     'EMDR، استقرار جسدي',                      'تقدم بطيء ولكن إيجابي',          'متابعة لأفكار الخطر',  'المريض تفاعل مع المعالجة رغم الصعوبة',       'تمارين مكان آمن يومياً',                      'متابعة EMDR الأسبوع القادم');

-- ============================================================
-- 10. MESSAGES
-- ============================================================
INSERT INTO messages (sender_id, receiver_id, case_id, content, type, is_read, sent_at) VALUES
(4,  2, 1, 'صباح الخير دكتورة سارة',                              'TEXT', 1, DATE_SUB(NOW(), INTERVAL 150 MINUTE)),
(2,  4, 1, 'صباح النور عمر، كيف حالك اليوم؟',                     'TEXT', 1, DATE_SUB(NOW(), INTERVAL 145 MINUTE)),
(4,  2, 1, 'الحمد لله أفضل بكثير، تمارين التنفس ساعدتني جداً',     'TEXT', 1, DATE_SUB(NOW(), INTERVAL 140 MINUTE)),
(2,  4, 1, 'ممتاز! استمر عليها يومياً',                            'TEXT', 1, DATE_SUB(NOW(), INTERVAL 135 MINUTE)),
(4,  2, 1, 'شكراً جزيلاً دكتورة',                                   'TEXT', 0, DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(5,  2, 2, 'دكتورة، متى يمكنني حجز جلسة مراجعة؟',                   'TEXT', 0, DATE_SUB(NOW(), INTERVAL 180 MINUTE)),
(7,  2, 4, 'السلام عليكم دكتورة',                                   'TEXT', 1, DATE_SUB(NOW(), INTERVAL 1440 MINUTE)),
(2,  7, 4, 'وعليكم السلام نور، كيف حالك مع جدول النوم؟',            'TEXT', 1, DATE_SUB(NOW(), INTERVAL 1435 MINUTE)),
(7,  2, 4, 'تحسّن كثير، الحمد لله',                                 'TEXT', 1, DATE_SUB(NOW(), INTERVAL 1430 MINUTE)),
(2,  8, 5, 'يوسف، هل جربت تقنية بومودورو اليوم؟',                   'TEXT', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(8,  2, 5, 'نعم دكتورة، أول مرة أنجز فيها كل المهام',                'TEXT', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(11, 3, 8, 'دكتور رامي، أواجه صعوبة في تمرين التعرض',                'TEXT', 0, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(12, 3, 9, 'شكراً على الدعم المستمر',                                'TEXT', 1, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ============================================================
-- 11. ACTIVITIES (created by therapists)
-- ============================================================
INSERT INTO activities (activity_id, title, description, category, activity_type, duration_min, difficulty, status, created_by) VALUES
(1, 'تمرين التنفس العميق 4-7-8',      'تقنية تنفس تساعد على الاسترخاء وتخفيف القلق',          'تنفس واسترخاء', 'EXERCISE', 10, 'EASY',   'ACTIVE', 2),
(2, 'مذكرات الامتنان اليومية',        'كتابة 3 أشياء تشعر بالامتنان لها كل يوم',              'كتابة',          'TASK',     15, 'EASY',   'ACTIVE', 2),
(3, 'استرخاء العضلات التدريجي',        'تمرين استرخاء جسدي لتقليل التوتر العضلي',             'تنفس واسترخاء', 'EXERCISE', 20, 'MEDIUM', 'ACTIVE', 2),
(4, 'سجل الأفكار السلبية',            'توثيق الأفكار السلبية التلقائية وتحديّها',              'CBT',           'TASK',     15, 'MEDIUM', 'ACTIVE', 2),
(5, 'لعبة التركيز الذهني',             'لعبة تنمي مهارات التركيز والانتباه',                   'ألعاب ذهنية',   'GAME',     15, 'EASY',   'DRAFT',  2),
(6, 'تمرين التعرض التدريجي',            'سلم مواقف مقلقة مرتب من الأسهل إلى الأصعب',           'CBT',           'EXERCISE', 30, 'HARD',   'ACTIVE', 3),
(7, 'تمارين اليقظة الذهنية (Mindfulness)', 'ممارسة يومية لليقظة والتواجد في اللحظة الحاضرة',     'يقظة ذهنية',    'EXERCISE', 20, 'MEDIUM', 'ACTIVE', 3);

-- ============================================================
-- 12. CASE ACTIVITIES (assignments)
-- ============================================================
INSERT INTO case_activities (id, case_id, activity_id, assigned_by, assigned_at, due_date, instructions) VALUES
(1, 1, 1, 2, '2026-04-05 10:00:00', '2026-04-25 23:59:59', 'مارس التمرين 3 مرات يومياً لمدة 10 دقائق'),
(2, 1, 4, 2, '2026-04-10 10:00:00', '2026-04-30 23:59:59', 'سجّل الأفكار السلبية التي تراودك قبل النوم'),
(3, 2, 2, 2, '2026-03-20 11:00:00', '2026-04-30 23:59:59', 'اكتبها كل ليلة قبل النوم'),
(4, 2, 4, 2, '2026-04-05 11:00:00', '2026-05-05 23:59:59', 'استخدم السجل 3 مرات في الأسبوع على الأقل'),
(5, 3, 3, 2, '2026-03-15 09:30:00', '2026-04-15 23:59:59', 'مارس التمرين قبل النوم'),
(6, 4, 1, 2, '2026-03-01 15:00:00', '2026-04-30 23:59:59', 'استخدمها عند الاستيقاظ ليلاً'),
(7, 4, 3, 2, '2026-03-15 15:00:00', '2026-04-30 23:59:59', 'مارسها قبل النوم بساعة'),
(8, 5, 2, 2, '2026-03-10 12:00:00', '2026-04-30 23:59:59', 'ركز على الإنجازات المهنية والشخصية'),
(9, 8, 6, 3, '2026-03-01 15:00:00', '2026-05-01 23:59:59', 'ابدأ من الموقف الأقل إثارة للقلق'),
(10,8, 7, 3, '2026-03-15 15:00:00', '2026-05-15 23:59:59', '10 دقائق صباحاً ومساءً'),
(11,9, 7, 3, '2026-02-20 16:00:00', '2026-04-20 23:59:59', 'اتبع التسجيلات الصوتية المرفقة');

-- ============================================================
-- 13. ACTIVITY SUBMISSIONS
-- ============================================================
INSERT INTO activity_submissions (submission_id, case_activity_id, client_id, file_path, submission_type, text_response, status, therapist_feedback, submitted_at, reviewed_at) VALUES
(1,  1, 4, NULL, 'TEXT',  'مارست التمرين 3 أيام، شعرت بتحسن',                    'COMPLETED', 'ممتاز! استمر',                '2026-04-08 22:00:00', '2026-04-09 10:00:00'),
(2,  1, 4, NULL, 'TEXT',  'اليوم الرابع، القلق قبل النوم خفّ كثيراً',             'COMPLETED', 'نتيجة رائعة',                 '2026-04-10 22:30:00', '2026-04-11 09:00:00'),
(3,  2, 4, NULL, 'TEXT',  'أفكار تدور حول ضغط العمل ومهلة مشروع قادم',            'REVIEWED',  NULL,                          '2026-04-15 21:00:00', NULL),
(4,  3, 5, NULL, 'TEXT',  'اليوم: ممتنة لأسرتي، لصحتي، لفنجان قهوتي الصباحي',    'COMPLETED', 'جميل! مهم أن تنتبهي للتفاصيل الصغيرة', '2026-03-25 22:00:00', '2026-03-26 09:00:00'),
(5,  3, 5, NULL, 'TEXT',  'اليوم: الهواء النقي، مكالمة مع صديقة، ابتسامة ابنتي',  'COMPLETED', 'استمري',                       '2026-04-01 22:15:00', '2026-04-02 10:00:00'),
(6,  4, 5, NULL, 'TEXT',  'فكرة: أنا فاشلة. تحدي: لم أكن أتخيل أنني سأعود للعمل، والآن أنجح',        'REVIEWED', NULL, '2026-04-12 20:00:00', NULL),
(7,  6, 7, NULL, 'TEXT',  'جربت التمرين ليلتين، الاستيقاظ ليلاً أقل',            'COMPLETED', 'تقدم جيد',                     '2026-03-10 23:00:00', '2026-03-11 09:00:00'),
(8,  7, 7, NULL, 'TEXT',  'ساعدني التمرين على الاسترخاء قبل النوم',               'COMPLETED', 'ممتاز!',                      '2026-04-01 22:00:00', '2026-04-02 10:00:00'),
(9,  8, 8, NULL, 'TEXT',  'ممتن: الصحة، العائلة، الفرصة الجديدة في العمل',        'COMPLETED', 'رائع',                        '2026-03-18 21:00:00', '2026-03-19 10:00:00'),
(10, 9, 11,NULL, 'TEXT',  'اليوم الأول: طلبت قهوة من محل جديد، قلق متوسط',       'COMPLETED', 'انجاز عظيم!',                 '2026-03-05 19:00:00', '2026-03-06 10:00:00'),
(11, 9, 11,NULL, 'TEXT',  'تحدثت مع زميلة جديدة في العمل',                        'COMPLETED', 'خطوة مهمة',                   '2026-03-15 19:30:00', '2026-03-16 10:00:00'),
(12, 10,11,NULL, 'TEXT',  'أمارسها يومياً، أشعر بهدوء أكبر',                     'REVIEWED',  NULL,                          '2026-04-12 08:00:00', NULL),
(13, 11,12,NULL, 'TEXT',  'الأسبوع الأول: صعب ولكن أفضل من قبل',                 'COMPLETED', 'استمر، أنت تتقدم',            '2026-03-01 20:00:00', '2026-03-02 10:00:00'),
(14, 11,12,NULL, 'TEXT',  'الأسبوع الرابع: الكوابيس أقل بكثير',                  'PENDING',   NULL,                          '2026-04-18 21:00:00', NULL);

-- ============================================================
-- 14. ASSESSMENTS
-- ============================================================
INSERT INTO assessments (assessment_id, code, title, description, category, max_score, duration_min, question_count, is_active) VALUES
(1, 'BDI-II',  'مقياس بيك للاكتئاب',          'تقييم شدة أعراض الاكتئاب',                 'اكتئاب',    63, 15, 21, 1),
(2, 'GAD-7',   'مقياس القلق العام (GAD-7)',   'قياس شدة أعراض القلق العام',               'قلق',       21, 10,  7, 1),
(3, 'PSS-10',  'مقياس الضغط النفسي',          'قياس مستوى التوتر المدرك',                 'ضغط نفسي', 40, 10, 10, 1),
(4, 'PCL-5',   'مقياس اضطراب ما بعد الصدمة',  'فحص أعراض ما بعد الصدمة',                 'صدمة',      80, 15, 20, 1),
(5, 'Y-BOCS',  'مقياس الوسواس القهري',        'تقييم شدة الأعراض الوسواسية',              'وسواس',     40, 15, 10, 1),
(6, 'LSAS',    'مقياس الرهاب الاجتماعي',      'قياس الخوف والتجنب في المواقف الاجتماعية',   'رهاب',      60, 15, 24, 1);

-- ============================================================
-- 15. ASSESSMENT RESULTS
-- ============================================================
INSERT INTO assessment_results (assessment_id, client_id, case_id, trait_score, level, status, reviewed_at, created_at) VALUES
(2, 4, 1, 18, 'HIGH',   'DISCUSSED', '2026-03-10 10:00:00', '2026-03-05 10:00:00'),
(2, 4, 1, 15, 'HIGH',   'REVIEWED',  '2026-03-28 10:00:00', '2026-03-25 10:00:00'),
(2, 4, 1, 11, 'MEDIUM', 'PENDING',   NULL,                  '2026-04-15 10:00:00'),
(1, 5, 2, 28, 'MEDIUM', 'DISCUSSED', '2026-02-25 11:00:00', '2026-02-20 11:00:00'),
(1, 5, 2, 25, 'MEDIUM', 'REVIEWED',  '2026-03-22 11:00:00', '2026-03-18 11:00:00'),
(1, 5, 2, 22, 'MEDIUM', 'PENDING',   NULL,                  '2026-04-12 11:00:00'),
(1, 6, 3, 32, 'HIGH',   'PENDING',   NULL,                  '2026-04-10 09:30:00'),
(3, 7, 4, 28, 'HIGH',   'DISCUSSED', '2026-03-05 14:00:00', '2026-03-01 14:00:00'),
(3, 7, 4, 21, 'MEDIUM', 'REVIEWED',  '2026-04-08 14:00:00', '2026-04-05 14:00:00'),
(3, 7, 4, 14, 'LOW',    'PENDING',   NULL,                  '2026-04-19 14:00:00'),
(3, 8, 5, 22, 'MEDIUM', 'REVIEWED',  '2026-03-15 12:00:00', '2026-03-12 12:00:00'),
(3, 8, 5, 18, 'MEDIUM', 'REVIEWED',  '2026-04-10 12:00:00', '2026-04-08 12:00:00'),
(6, 11, 8, 72, 'SEVERE','DISCUSSED', '2026-02-15 15:00:00', '2026-02-10 15:00:00'),
(6, 11, 8, 55, 'HIGH',  'REVIEWED',  '2026-03-20 15:00:00', '2026-03-15 15:00:00'),
(6, 11, 8, 42, 'MEDIUM','PENDING',   NULL,                  '2026-04-14 15:00:00'),
(4, 12, 9, 48, 'HIGH',  'DISCUSSED', '2026-02-20 16:00:00', '2026-02-15 16:00:00'),
(4, 12, 9, 55, 'SEVERE','REVIEWED',  '2026-03-15 16:00:00', '2026-03-10 16:00:00'),
(4, 12, 9, 55, 'SEVERE','PENDING',   NULL,                  '2026-04-14 16:00:00'),
(5, 13, 10, 30, 'HIGH',  'DISCUSSED','2025-11-05 10:00:00', '2025-11-01 10:00:00'),
(5, 13, 10, 18, 'MEDIUM','DISCUSSED','2026-02-01 10:00:00', '2026-01-25 10:00:00'),
(5, 13, 10,  8, 'LOW',   'DISCUSSED','2026-03-20 10:00:00', '2026-03-15 10:00:00');

-- ============================================================
-- 16. FOCUS TESTS
-- ============================================================
INSERT INTO focus_tests (focus_test_id, title, description, test_type, is_active) VALUES
(1, 'اختبار الانتباه المستمر', 'قياس قدرة الانتباه على فترات طويلة', 'ATTENTION',  1),
(2, 'اختبار سرعة التفاعل',    'قياس سرعة الاستجابة للمنبهات',       'REACTION',   1),
(3, 'اختبار التركيز البصري',   'قياس التركيز على الحفزات البصرية',    'VISUAL',     1);

-- ============================================================
-- 17. FOCUS RESULTS
-- ============================================================
INSERT INTO focus_results (focus_test_id, client_id, case_id, duration_seconds, completion_pct, score, improvement_pct, created_at) VALUES
(1, 4, 1, 300, 85, 78, 12, '2026-03-15 10:00:00'),
(1, 4, 1, 300, 90, 85,  9, '2026-04-10 10:00:00'),
(2, 5, 2, 180, 92, 88,  5, '2026-03-28 11:00:00'),
(3, 8, 5, 240, 78, 72, -3, '2026-04-05 12:00:00');

-- ============================================================
-- 18. ALERTS
-- ============================================================
INSERT INTO alerts (user_id, case_id, description, level, is_handled, created_at, handled_at) VALUES
(2, 2,  'مريضة تعاني اكتئاب متوسط - يتطلب متابعة أسبوعية',  'WARNING',  1, '2026-03-25 10:00:00', '2026-03-26 11:00:00'),
(2, 9,  'حالة صدمة - خطر انتحاري محتمل',                     'CRITICAL', 0, '2026-04-10 16:00:00', NULL),
(3, 7,  'حالة جديدة تحتاج إلى تقييم عاجل',                   'INFO',     0, '2026-04-05 09:00:00', NULL),
(2, 3,  'لم يحضر الجلسة الأخيرة دون إشعار',                  'WARNING',  0, '2026-04-18 10:00:00', NULL);

-- ============================================================
-- 19. NOTIFICATIONS
-- ============================================================
INSERT INTO notifications (user_id, title, body, type, priority, is_read, created_at) VALUES
(2, 'موعد جديد تم تأكيده',           'تم تأكيد موعد مع عمر الخطيب في الغد الساعة 10:00 صباحاً',         'APPOINTMENT_REMINDER', 'URGENT', 0, DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(2, 'رسالة جديدة من مريضة',           'أرسلت ليلى الحسيني رسالة تطلب فيها استشارة',                     'GENERAL',              'URGENT', 0, DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(2, 'تنبيه: اختبار نفسي جديد',        'أكملت نور البرغوثي مقياس PSS-10 ويحتاج إلى مراجعة',               'ASSESSMENT_READY',     'NORMAL', 0, DATE_SUB(NOW(), INTERVAL 60 MINUTE)),
(2, 'تذكير: جلسة قادمة',              'لديك جلسة مع محمود درويش خلال ساعة واحدة',                        'APPOINTMENT_REMINDER', 'URGENT', 1, DATE_SUB(NOW(), INTERVAL 120 MINUTE)),
(2, 'مريض جديد تم تعيينه',             'تم تعيين المريضة فاطمة العجوري لك، يرجى مراجعة ملفها',             'ACTIVITY_ASSIGNED',    'NORMAL', 1, DATE_SUB(NOW(), INTERVAL 180 MINUTE)),
(2, 'طلب إلغاء موعد',                  'طلبت نور البرغوثي إلغاء الموعد المحدد لغد',                       'APPOINTMENT_REMINDER', 'NORMAL', 1, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(2, 'رد على استفسار',                  'رد يوسف الزيتاوي على استفسارك بخصوص تطبيق التمارين',              'GENERAL',              'LOW',    1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'تذكير: تقرير شهري',               'حان موعد إعداد التقرير الشهري لجلسات هذا الشهر',                    'GENERAL',              'NORMAL', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'حالة جديدة تحتاج إلى تقييم',      'تم تعيين حالة طارق أبو عرب - سلوك الابن العدواني',                'ACTIVITY_ASSIGNED',    'URGENT', 0, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(3, 'رسالة جديدة',                     'سلمى الشوا أرسلت لك رسالة جديدة',                                  'GENERAL',              'NORMAL', 0, DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- ============================================================
-- 20. THERAPIST REVIEWS
-- ============================================================
INSERT INTO therapist_reviews (therapist_id, client_id, rating, comment, created_at) VALUES
(2, 4, 5, 'تجربة رائعة جداً، الدكتورة سارة محترفة ومتفهمة وساعدتني كثيراً في تجاوز القلق. أنصح بها بشدة.',  '2026-04-05 18:00:00'),
(2, 5, 5, 'أخصائية ممتازة وصبورة، الجلسات مفيدة جداً وأشعر بتحسن كبير في حالتي.',                           '2026-04-10 19:30:00'),
(2, 7, 5, 'تعاملها راقٍ وجدولها مرن. خطة علاج واضحة وعملية.',                                               '2026-04-12 12:00:00'),
(2, 8, 4, 'دكتورة متميزة، ساعدتني في التعامل مع ضغط العمل. الجلسات قد تكون قصيرة بعض الشيء.',                '2026-04-15 21:00:00'),
(2, 4, 5, 'شكراً دكتورة، حياتي تغيرت للأفضل بفضل خطة العلاج.',                                              '2026-04-18 20:00:00'),
(3, 11, 5, 'الدكتور رامي متفهم جداً ويعطيني أدوات عملية للتعامل مع القلق الاجتماعي.',                        '2026-04-05 17:00:00'),
(3, 12, 4, 'تحسن ملحوظ بعد عدة جلسات، الدكتور يفهم حالتي بعمق.',                                              '2026-04-10 18:00:00'),
(3, 13, 5, 'نتيجة مذهلة، خرجت من الوسواس تدريجياً بفضل خطته.',                                                '2026-03-22 14:00:00');

-- ============================================================
-- Reset AUTO_INCREMENTs to continue from the max seeded id
-- ============================================================
ALTER TABLE users                  AUTO_INCREMENT = 14;
ALTER TABLE client_surveys         AUTO_INCREMENT = 11;
ALTER TABLE therapist_availability AUTO_INCREMENT = 24;
ALTER TABLE cases                  AUTO_INCREMENT = 11;
ALTER TABLE appointments           AUTO_INCREMENT = 19;
ALTER TABLE sessions               AUTO_INCREMENT = 8;
ALTER TABLE session_notes          AUTO_INCREMENT = 8;
ALTER TABLE messages               AUTO_INCREMENT = 14;
ALTER TABLE activities             AUTO_INCREMENT = 8;
ALTER TABLE case_activities        AUTO_INCREMENT = 12;
ALTER TABLE activity_submissions   AUTO_INCREMENT = 15;
ALTER TABLE assessments            AUTO_INCREMENT = 7;
ALTER TABLE assessment_results     AUTO_INCREMENT = 22;
ALTER TABLE focus_tests            AUTO_INCREMENT = 4;
ALTER TABLE focus_results          AUTO_INCREMENT = 5;
ALTER TABLE alerts                 AUTO_INCREMENT = 5;
ALTER TABLE notifications          AUTO_INCREMENT = 11;
ALTER TABLE therapist_reviews      AUTO_INCREMENT = 9;

SET FOREIGN_KEY_CHECKS = 1;

-- Done. Log in with any of the accounts listed at the top of this file.
