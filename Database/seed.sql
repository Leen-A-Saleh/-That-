/*
-- ============================================================
-- Seed data for That / ذات (updated schema)
-- ============================================================
-- Usage:
--   mysql -u root that_db < seed.sql
--
-- Default password for EVERY seeded account: that123
--
-- Login samples:
--   Admin      → email: admin@that.com    / password: that123
--   Therapist  → email: mahaalrefaie96@gmail.com     / password: that123
--   Therapist  → email: Zainab.karmi@hotmail.com     / password: that123
--   Client     → email: omar@that.com     / password: that123
-- ============================================================
*/

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
TRUNCATE TABLE login_activities;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE payments;
TRUNCATE TABLE notifications;
TRUNCATE TABLE alerts;
TRUNCATE TABLE game_results;
TRUNCATE TABLE focus_results;
TRUNCATE TABLE focus_tests;
TRUNCATE TABLE assessment_suggestions;
TRUNCATE TABLE assessment_results;
TRUNCATE TABLE assessments;
TRUNCATE TABLE activity_uploads;
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
TRUNCATE TABLE users;

-- ============================================================
-- 1. USERS  (1 admin, 5 therapists, 10 clients)
-- Password for all: that123
-- ============================================================
INSERT INTO users (user_id, name, username, email, password, phone, avatar, role, is_active) VALUES
(1,  'أحمد الخليلي',      'admin',   'admin@that.com',   '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100001', NULL, 'ADMIN',     1),

-- Therapists
(2,  'مها الرفاعي',        'maha',    'mahaalrefaie96@gmail.com', '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100002', NULL, 'THERAPIST', 1),
(3,  'زينب كرمي',          'zainab',  'Zainab.karmi@hotmail.com', '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100003', NULL, 'THERAPIST', 1),
(4,  'عمر قدح',            'omarq',   'omaraaq1989@gmail.com',    '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100004', NULL, 'THERAPIST', 1),
(5,  'تسنيم زيدان',        'tasneem', 'tasneemtherapist@gmail.com','$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100005', NULL, 'THERAPIST', 1),
(6,  'هديل أبو رميلة',     'hadeel',  'hadeel.basman98@gmail.com', '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100006', NULL, 'THERAPIST', 1),
(17, 'نيروز نجم الدين',    'niroz',   'n.nijem.aldeen98@gmail.com','$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100017', NULL, 'THERAPIST', 1),

-- Clients
(7,  'عمر الخطيب',         'omar',    'omar@that.com',    '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100007', NULL, 'CLIENT',    1),
(8,  'ليلى الحسيني',       'laila',   'laila@that.com',   '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100008', NULL, 'CLIENT',    1),
(9,  'محمود درويش',        'mahmoud', 'mahmoud@that.com', '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100009', NULL, 'CLIENT',    1),
(10, 'نور البرغوثي',       'nour',    'nour@that.com',    '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100010', NULL, 'CLIENT',    1),
(11, 'يوسف الزيتاوي',      'youssef', 'youssef@that.com', '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100011', NULL, 'CLIENT',    1),
(12, 'فاطمة العجوري',      'fatima',  'fatima@that.com',  '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100012', NULL, 'CLIENT',    1),
(13, 'طارق أبو عرب',       'tareq',   'tareq@that.com',   '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100013', NULL, 'CLIENT',    1),
(14, 'سلمى الشوا',         'salma',   'salma@that.com',   '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100014', NULL, 'CLIENT',    1),
(15, 'رنا شاهين',          'rana',    'rana@that.com',    '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100015', NULL, 'CLIENT',    1),
(16, 'خالد الصالحي',       'khaled',  'khaled@that.com',  '$2b$12$iWhlM4QUzI5gbni1wkMH5.cZfuGyRcQVMqxzJ5q4oVFnpKFSnFBpS', '+970-599-100016', NULL, 'CLIENT',    1);

-- ============================================================
-- 2. CLIENT SURVEYS (one per client, 10 total)
-- ============================================================
INSERT INTO client_surveys (id, treatment_type, symptoms, repeated_symptoms, prev_therapy, age, gender, nationality, therapist_gender, family_history, physical_issues, physical_details, marital_status, education_level, smoking, alcohol, drugs, contact_preference) VALUES
(1,  'INDIVIDUAL_THERAPY',                  'قلق، أرق، توتر',          'قلق متكرر قبل النوم',     0, 28, 'MALE',   'فلسطيني', 'NO_PREFERENCE', 'NO',  'NO',  NULL,             'SINGLE',  'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(2,  'INDIVIDUAL_THERAPY',                  'اكتئاب، فقدان شهية',      'مزاج منخفض لفترات طويلة', 1, 34, 'FEMALE', 'فلسطيني', 'FEMALE',        'YES', 'NO',  NULL,             'MARRIED', 'MASTER',     'NO',  'NO', 'NO', 'WHATSAPP'),
(3,  'INDIVIDUAL_THERAPY',                  'اكتئاب بعد حادث',         'أفكار سلبية متكررة',      0, 45, 'MALE',   'فلسطيني', 'MALE',          'NO',  'YES', 'آلام ظهر مزمنة', 'MARRIED', 'HIGH_SCHOOL','YES', 'NO', 'NO', 'EMAIL'),
(4,  'INDIVIDUAL_THERAPY',                  'اضطرابات النوم',          'استيقاظ متكرر ليلاً',     0, 29, 'FEMALE', 'فلسطيني', 'FEMALE',        'NO',  'NO',  NULL,             'SINGLE',  'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(5,  'INDIVIDUAL_THERAPY',                  'توتر في العمل',           'صعوبة في التركيز',        0, 32, 'MALE',   'فلسطيني', 'NO_PREFERENCE', 'NO',  'NO',  NULL,             'MARRIED', 'MASTER',     'NO',  'NO', 'NO', 'WHATSAPP'),
(6,  'COUPLES_THERAPY',                     'خلافات زوجية',            'نزاعات متكررة',           1, 31, 'FEMALE', 'فلسطيني', 'FEMALE',        'NO',  'NO',  NULL,             'MARRIED', 'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(7,  'CHILD_ADOLESCENT_BEHAVIORAL_THERAPY', 'سلوك عدواني لدى الابن',   'نوبات غضب متكررة',        0, 38, 'MALE',   'فلسطيني', 'MALE',          'NO',  'NO',  NULL,             'MARRIED', 'BACHELOR',   'YES', 'NO', 'NO', 'EMAIL'),
(8,  'INDIVIDUAL_THERAPY',                  'قلق اجتماعي',             'خوف من التجمعات',         0, 24, 'FEMALE', 'فلسطيني', 'FEMALE',        'NO',  'NO',  NULL,             'SINGLE',  'BACHELOR',   'NO',  'NO', 'NO', 'WHATSAPP'),
(9,  'INDIVIDUAL_THERAPY',                  'صدمة بعد حادث',           'كوابيس متكررة',           1, 40, 'MALE',   'فلسطيني', 'MALE',          'YES', 'YES', 'آثار جرح قديم', 'WIDOWED', 'HIGH_SCHOOL','NO',  'NO', 'NO', 'EMAIL'),
(10, 'INDIVIDUAL_THERAPY',                  'وسواس قهري',              'أفكار تطفّلية',           0, 27, 'FEMALE', 'فلسطيني', 'NO_PREFERENCE', 'NO',  'NO',  NULL,             'SINGLE',  'MASTER',     'NO',  'NO', 'NO', 'WHATSAPP');

-- ============================================================
-- 3. THERAPISTS (user_id 2-6)
-- ============================================================
INSERT INTO therapists (therapist_id, specialization, bio, certification, experience_years, rating, rating_count, status, color) VALUES
(2, 'استشارية نفسية اجتماعية', 'بكالوريوس علم نفس (فرعي علم اجتماع). مختصة نفسية اجتماعية بخبرة أكثر من 6 سنوات في الإرشاد الفردي والإرشاد الأسري وإدارة المجموعات. ساعات العمل: الاثنين-الخميس بعد الساعة 3:00 عصراً. تكلفة الجلسة الاستشارية 150 شيكل، وتكلفة الجلسات العلاجية 120 شيكل.', 'بكالوريوس علم نفس - فرعي علم اجتماع', 6, 0.0, 0, 'AVAILABLE', '#4F46E5'),
(3, 'علاج الصدمات والعلاج السلوكي المعرفي', 'ماجستير إرشاد نفسي وتوجيه، مرخصة لتقديم علاج الصدمات والعلاج السلوكي المعرفي. خبرة أكثر من 8 سنوات في جلسات إرشادية للأطفال والبالغين والإرشاد الوالدي. ساعات العمل: السبت-الخميس من 9:00 صباحاً إلى 3:30 عصراً. تكلفة الجلسة الاستشارية 150 شيكل، وتكلفة الجلسات العلاجية 120 شيكل.', 'ماجستير إرشاد نفسي وتوجيه - مرخصة علاج الصدمات والعلاج السلوكي المعرفي', 8, 0.0, 0, 'AVAILABLE', '#16A34A'),
(4, 'الاختبارات النفسية واختبارات الذكاء', 'ماجستير الصحة النفسية العلاجية، مرخص لإدارة الاختبارات النفسية واختبارات الذكاء. خبرة أكثر من 10 سنوات في التعامل مع مختلف الأعمار والاضطرابات النفسية. ساعات العمل: الأحد-الأربعاء (حسب الطلب). تكلفة الجلسة الاستشارية 200 شيكل، وتكلفة الجلسات العلاجية 150 شيكل.', 'ماجستير الصحة النفسية العلاجية - مرخص لإدارة الاختبارات النفسية واختبارات الذكاء', 10, 0.0, 0, 'AVAILABLE', '#DB2777'),
(5, 'علاج الصدمات والعلاج السلوكي المعرفي', 'ماجستير الصحة النفسية العلاجية، مرخصة لتقديم العلاج السلوكي المعرفي وعلاج الصدمات. خبرة أكثر من 6 سنوات في التعامل مع المراهقين والبالغين. ساعات العمل: الأحد والخميس من 8:30 صباحاً إلى 3:00 عصراً. تكلفة الجلسة الاستشارية 200 شيكل، وتكلفة الجلسات العلاجية 150 شيكل.', 'ماجستير الصحة النفسية العلاجية - مرخصة علاج السلوكي المعرفي وعلاج الصدمات', 6, 0.0, 0, 'AVAILABLE', '#EA580C'),
(6, 'الإرشاد الفردي والزواجي (عن بُعد)', 'ماجستير إرشاد نفسي وتوجيه. خبرة أكثر من 4 سنوات في الإرشاد الفردي والزواجي. تقدم جلساتها أونلاين حسب الطلب. تكلفة الجلسة الاستشارية 170 شيكل، وتكلفة الجلسات العلاجية 130 شيكل.', 'ماجستير إرشاد نفسي وتوجيه', 4, 0.0, 0, 'AVAILABLE', '#0891B2'),
(17, 'علاج اضطرابات النطق واللغة عند الأطفال', 'بكالوريوس السمع والنطق. خبرة أكثر من 6 سنوات في تطوير اللغة وتصحيح النطق وعلاج التأتأة عند الأطفال. ساعات العمل: السبت والأحد والثلاثاء والأربعاء من 9:00 إلى 4:00. تكلفة الجلسة الاستشارية (التقييمية) 150 شيكل، وتكلفة الجلسات العلاجية 80 شيكل.', 'بكالوريوس السمع والنطق', 6, 0.0, 0, 'AVAILABLE', '#7C3AED');

-- ============================================================
-- 4. CLIENTS (user_id 7-16, survey_id 1-10)
-- ============================================================
INSERT INTO clients (client_id, survey_id, gender, date_of_birth, city, treatment_type, preferred_session_type, preferred_session_time) VALUES
(7,  1,  'MALE',   '1997-04-12', 'رام الله',  'INDIVIDUAL_THERAPY',                  'ONLINE',    'EVENING'),
(8,  2,  'FEMALE', '1991-09-25', 'نابلس',     'INDIVIDUAL_THERAPY',                  'BOTH',      'MORNING'),
(9,  3,  'MALE',   '1980-02-08', 'الخليل',    'INDIVIDUAL_THERAPY',                  'IN_PERSON', 'AFTERNOON'),
(10, 4,  'FEMALE', '1996-11-30', 'بيت لحم',   'INDIVIDUAL_THERAPY',                  'ONLINE',    'EVENING'),
(11, 5,  'MALE',   '1993-07-14', 'جنين',      'INDIVIDUAL_THERAPY',                  'BOTH',      'FLEXIBLE'),
(12, 6,  'FEMALE', '1994-03-19', 'طولكرم',    'COUPLES_THERAPY',                     'IN_PERSON', 'AFTERNOON'),
(13, 7,  'MALE',   '1987-08-05', 'غزة',       'CHILD_ADOLESCENT_BEHAVIORAL_THERAPY', 'ONLINE',    'MORNING'),
(14, 8,  'FEMALE', '2001-05-22', 'القدس',     'INDIVIDUAL_THERAPY',                  'BOTH',      'EVENING'),
(15, 9,  'MALE',   '1985-12-03', 'أريحا',     'INDIVIDUAL_THERAPY',                  'IN_PERSON', 'AFTERNOON'),
(16, 10, 'FEMALE', '1998-10-09', 'قلقيلية',   'INDIVIDUAL_THERAPY',                  'ONLINE',    'MORNING');

-- ============================================================
-- 5. THERAPIST AVAILABILITY
-- ============================================================
INSERT INTO therapist_availability (availability_id, therapist_id, day_of_week, start_time, end_time, is_active) VALUES
-- 2: مها الرفاعي - الاثنين إلى الخميس بعد الساعة 3 عصراً
(1,  2, 'MONDAY',    '15:00:00', '16:00:00', 1),
(2,  2, 'MONDAY',    '16:00:00', '17:00:00', 1),
(3,  2, 'TUESDAY',   '15:00:00', '16:00:00', 1),
(4,  2, 'TUESDAY',   '16:00:00', '17:00:00', 1),
(5,  2, 'WEDNESDAY', '15:00:00', '16:00:00', 1),
(6,  2, 'WEDNESDAY', '16:00:00', '17:00:00', 1),
(7,  2, 'THURSDAY',  '15:00:00', '16:00:00', 1),
(8,  2, 'THURSDAY',  '16:00:00', '17:00:00', 1),

-- 3: زينب كرمي - السبت إلى الخميس 9:00-3:30
(9,  3, 'SATURDAY',  '09:00:00', '11:00:00', 1),
(10, 3, 'SUNDAY',    '09:00:00', '11:00:00', 1),
(11, 3, 'MONDAY',    '09:00:00', '11:00:00', 1),
(12, 3, 'TUESDAY',   '09:00:00', '11:00:00', 1),
(13, 3, 'WEDNESDAY', '09:00:00', '11:00:00', 1),
(14, 3, 'THURSDAY',  '09:00:00', '11:00:00', 1),

-- 4: عمر قدح - الأحد إلى الأربعاء حسب الطلب
(15, 4, 'SUNDAY',    '10:00:00', '12:00:00', 1),
(16, 4, 'MONDAY',    '10:00:00', '12:00:00', 1),
(17, 4, 'TUESDAY',   '10:00:00', '12:00:00', 1),
(18, 4, 'WEDNESDAY', '10:00:00', '12:00:00', 1),

-- 5: تسنيم زيدان - الأحد والخميس 8:30-3:00
(19, 5, 'SUNDAY',    '08:30:00', '10:30:00', 1),
(20, 5, 'SUNDAY',    '13:00:00', '15:00:00', 1),
(21, 5, 'THURSDAY',  '08:30:00', '10:30:00', 1),
(22, 5, 'THURSDAY',  '13:00:00', '15:00:00', 1),

-- 6: هديل أبو رميلة - أونلاين حسب الطلب
(23, 6, 'SUNDAY',    '10:00:00', '12:00:00', 1),
(24, 6, 'TUESDAY',   '10:00:00', '12:00:00', 1),
(25, 6, 'THURSDAY',  '10:00:00', '12:00:00', 1),

-- 17: نيروز نجم الدين - السبت والأحد والثلاثاء والأربعاء 9:00-4:00
(26, 17, 'SATURDAY',  '09:00:00', '12:00:00', 1),
(27, 17, 'SATURDAY',  '13:00:00', '16:00:00', 1),
(28, 17, 'SUNDAY',    '09:00:00', '12:00:00', 1),
(29, 17, 'SUNDAY',    '13:00:00', '16:00:00', 1),
(30, 17, 'TUESDAY',   '09:00:00', '12:00:00', 1),
(31, 17, 'TUESDAY',   '13:00:00', '16:00:00', 1),
(32, 17, 'WEDNESDAY', '09:00:00', '12:00:00', 1),
(33, 17, 'WEDNESDAY', '13:00:00', '16:00:00', 1);

-- ============================================================
-- 6. CASES
-- ============================================================
INSERT INTO cases (case_id, client_id, therapist_id, title, description, status, priority, is_flagged, created_at, last_updated, closed_at, progress) VALUES
(1,  7,  2, 'اضطراب القلق العام',           'قلق متكرر وأرق، يحتاج إلى علاج معرفي سلوكي',            'IN_PROGRESS',  'MEDIUM', 0, '2026-02-01 10:00:00', '2026-04-15 10:00:00', NULL, 55),
(2,  8,  2, 'اكتئاب متوسط',                 'أعراض اكتئابية متوسطة مع فقدان شهية',                   'IN_PROGRESS',  'HIGH',   1, '2026-01-15 11:00:00', '2026-04-18 09:30:00', NULL, 40),
(3,  9,  2, 'اكتئاب بعد حادث عمل',          'أعراض اكتئاب بعد التعرّض لحادث في العمل',               'UNDER_REVIEW', 'MEDIUM', 0, '2026-03-10 09:00:00', '2026-04-10 09:30:00', NULL, 20),
(4,  10, 2, 'اضطرابات النوم',               'صعوبة في النوم والاستيقاظ المتكرر',                     'IN_PROGRESS',  'LOW',    0, '2026-02-20 14:00:00', '2026-04-19 14:00:00', NULL, 60),
(5,  11, 2, 'توتر مهني وضغط نفسي',          'ضغوط عمل متواصلة أدت لتوتر ومشاكل تركيز',                'IN_PROGRESS',  'MEDIUM', 0, '2026-03-01 12:00:00', '2026-04-08 12:00:00', NULL, 45),
(6,  12, 3, 'خلافات زوجية',                 'نزاعات متكررة مع الزوج وحاجة لإعادة بناء الثقة',         'UNDER_REVIEW', 'MEDIUM', 0, '2026-03-15 10:00:00', '2026-04-12 10:00:00', NULL, 15),
(7,  13, 3, 'سلوك عدواني لدى الابن',         'الابن بعمر 8 سنوات يظهر نوبات غضب في المدرسة',           'IN_PROGRESS',  'HIGH',   0, '2026-04-05 09:00:00', '2026-04-05 09:00:00', NULL, 5),
(8,  14, 5, 'قلق اجتماعي',                  'خوف من التحدث أمام مجموعات وصعوبة في المواقف الاجتماعية','IN_PROGRESS',  'MEDIUM', 0, '2026-02-10 15:00:00', '2026-04-14 15:00:00', NULL, 50),
(9,  15, 4, 'اضطراب ما بعد الصدمة',         'أعراض ما بعد الصدمة بعد فقدان شخص عزيز',                 'IN_PROGRESS',  'HIGH',   1, '2026-02-15 16:00:00', '2026-04-14 16:00:00', NULL, 35),
(10, 16, 5, 'وسواس قهري',                   'أفكار تطفّلية متكررة وسلوكيات قسرية',                   'CLOSED',       'LOW',    0, '2025-10-01 10:00:00', '2026-03-20 10:00:00', '2026-03-20 10:00:00', 100);

-- ============================================================
-- 7. APPOINTMENTS
-- ============================================================
INSERT INTO appointments (appointment_id, case_id, therapist_id, client_id, date_time, duration_min, mode, room_number, zoom_link, status, cancel_reason, created_at, last_updated, session_type) VALUES
(1,  1, 2, 7,  DATE_SUB(CURDATE(), INTERVAL 14 DAY) + INTERVAL 10 HOUR, 60, 'ONLINE',    NULL,  'https://zoom.us/j/111222333', 'COMPLETED', NULL, '2026-04-01 09:00:00', '2026-04-09 11:00:00', 'THERAPY'),
(2,  1, 2, 7,  CURDATE() + INTERVAL 10 HOUR,                             60, 'ONLINE',    NULL,  'https://zoom.us/j/111222333', 'CONFIRMED', NULL, '2026-04-10 09:00:00', '2026-04-10 09:00:00', 'THERAPY'),
(3,  1, 2, 7,  CURDATE() + INTERVAL 7 DAY + INTERVAL 10 HOUR,            60, 'ONLINE',    NULL,  NULL,                          'REQUESTED', NULL, '2026-04-20 09:00:00', '2026-04-20 09:00:00', 'THERAPY'),

(4,  2, 2, 8,  DATE_SUB(CURDATE(), INTERVAL 21 DAY) + INTERVAL 11 HOUR, 60, 'IN_CENTER', '305', NULL,                          'COMPLETED', NULL, '2026-03-25 10:00:00', '2026-04-02 12:00:00', 'CONSULTATION'),
(5,  2, 2, 8,  DATE_SUB(CURDATE(), INTERVAL 7 DAY) + INTERVAL 11 HOUR,  60, 'IN_CENTER', '305', NULL,                          'COMPLETED', NULL, '2026-04-05 10:00:00', '2026-04-16 12:00:00', 'THERAPY'),
(6,  2, 2, 8,  CURDATE() + INTERVAL 1 DAY + INTERVAL 11 HOUR,            60, 'IN_CENTER', '305', NULL,                          'CONFIRMED', NULL, '2026-04-18 10:00:00', '2026-04-18 10:00:00', 'THERAPY'),

(7,  3, 2, 9,  CURDATE() + INTERVAL 14 HOUR,                             60, 'IN_CENTER', '204', NULL,                          'CONFIRMED', NULL, '2026-04-15 09:00:00', '2026-04-15 09:00:00', 'THERAPY'),
(8,  3, 2, 9,  CURDATE() + INTERVAL 2 DAY + INTERVAL 14 HOUR,            60, 'IN_CENTER', '204', NULL,                          'REQUESTED', NULL, '2026-04-22 09:00:00', '2026-04-22 09:00:00', 'THERAPY'),

(9,  4, 2, 10, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 15 HOUR,  60, 'ONLINE',    NULL,  'https://zoom.us/j/444555666', 'COMPLETED', NULL, '2026-04-10 10:00:00', '2026-04-20 16:00:00', 'THERAPY'),
(10, 4, 2, 10, CURDATE() + INTERVAL 5 DAY + INTERVAL 15 HOUR,            60, 'ONLINE',    NULL,  NULL,                          'REQUESTED', NULL, '2026-04-21 09:00:00', '2026-04-21 09:00:00', 'THERAPY'),

(11, 5, 2, 11, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 12 HOUR,  60, 'ONLINE',    NULL,  'https://zoom.us/j/777888999', 'COMPLETED', NULL, '2026-04-05 08:00:00', '2026-04-17 13:00:00', 'THERAPY'),
(12, 5, 2, 11, CURDATE() + INTERVAL 3 DAY + INTERVAL 12 HOUR,            60, 'ONLINE',    NULL,  NULL,                          'CONFIRMED', NULL, '2026-04-18 08:00:00', '2026-04-18 08:00:00', 'THERAPY'),

(13, 6, 3, 12, CURDATE() + INTERVAL 10 HOUR,                             60, 'IN_CENTER', '110', NULL,                          'REQUESTED', NULL, '2026-04-20 10:00:00', '2026-04-20 10:00:00', 'CONSULTATION'),

(14, 7, 3, 13, CURDATE() + INTERVAL 9 HOUR,                              60, 'ONLINE',    NULL,  NULL,                          'REQUESTED', NULL, '2026-04-20 10:00:00', '2026-04-20 10:00:00', 'CONSULTATION'),

(15, 8, 5, 14, DATE_SUB(CURDATE(), INTERVAL 10 DAY) + INTERVAL 15 HOUR, 60, 'ONLINE',    NULL,  'https://zoom.us/j/123123123', 'COMPLETED', NULL, '2026-04-01 10:00:00', '2026-04-12 16:00:00', 'CONSULTATION'),
(16, 8, 5, 14, CURDATE() + INTERVAL 1 DAY + INTERVAL 15 HOUR,            60, 'ONLINE',    NULL,  NULL,                          'CONFIRMED', NULL, '2026-04-19 10:00:00', '2026-04-19 10:00:00', 'THERAPY'),

(17, 9, 4, 15, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR,  60, 'IN_CENTER', '410', NULL,                          'COMPLETED', NULL, '2026-04-08 10:00:00', '2026-04-18 17:00:00', 'THERAPY'),
(18, 9, 4, 15, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 16 HOUR,  60, 'IN_CENTER', '410', NULL,                          'CANCELLED', 'المريض كان مريضاً', '2026-04-15 10:00:00', '2026-04-22 10:00:00', 'THERAPY'),

(19, 10, 5, 16, '2025-10-05 10:00:00',                                   60, 'ONLINE',    NULL,  'https://zoom.us/j/555666777', 'COMPLETED', NULL, '2025-10-01 09:00:00', '2025-10-06 10:00:00', 'CONSULTATION');

-- ============================================================
-- 8. SESSIONS (created from COMPLETED appointments)
-- ============================================================
INSERT INTO sessions (session_id, appointment_id, case_id, start_time, end_time, media_type, room_token, meeting_link, therapist_notes, status) VALUES
(1, 1,  1, DATE_SUB(CURDATE(), INTERVAL 14 DAY) + INTERVAL 10 HOUR, DATE_SUB(CURDATE(), INTERVAL 14 DAY) + INTERVAL 11 HOUR, 'VIDEO', NULL, 'https://zoom.us/j/111222333', 'ركزنا على تمارين التنفس ومناقشة مصادر القلق',              'CLOSED'),
(2, 4,  2, DATE_SUB(CURDATE(), INTERVAL 21 DAY) + INTERVAL 11 HOUR, DATE_SUB(CURDATE(), INTERVAL 21 DAY) + INTERVAL 12 HOUR, 'VIDEO', NULL, NULL,                          'جلسة تقييم أولى، المريضة أظهرت أعراض اكتئاب متوسطة',         'CLOSED'),
(3, 5,  2, DATE_SUB(CURDATE(), INTERVAL 7 DAY) + INTERVAL 11 HOUR,  DATE_SUB(CURDATE(), INTERVAL 7 DAY) + INTERVAL 12 HOUR,  'VIDEO', NULL, NULL,                          'تحسن ملحوظ في الشهية، بدأنا تقنيات العلاج المعرفي السلوكي', 'CLOSED'),
(4, 9,  4, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 15 HOUR,  DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 16 HOUR,  'VIDEO', NULL, 'https://zoom.us/j/444555666', 'ناقشنا عادات النوم وأعطيتها جدول تتبع',                     'CLOSED'),
(5, 11, 5, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 12 HOUR,  DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 13 HOUR,  'VIDEO', NULL, 'https://zoom.us/j/777888999', 'ركزنا على تقنيات إدارة التوتر وتحديد أولويات العمل',        'CLOSED'),
(6, 15, 8, DATE_SUB(CURDATE(), INTERVAL 10 DAY) + INTERVAL 15 HOUR, DATE_SUB(CURDATE(), INTERVAL 10 DAY) + INTERVAL 16 HOUR, 'VIDEO', NULL, 'https://zoom.us/j/123123123', 'جلسة تقييم أولي للقلق الاجتماعي',                           'CLOSED'),
(7, 17, 9, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR,  DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR,  'VIDEO', NULL, NULL,                          'جلسة علاج EMDR، تقدم بطيء ولكن إيجابي',                     'CLOSED'),
(8, 19, 10,'2025-10-05 10:00:00',                                    '2025-10-05 11:00:00',                                  'VIDEO', NULL, 'https://zoom.us/j/555666777', 'جلسة تقييم أولى للوسواس القهري',                            'CLOSED');

-- ============================================================
-- 9. SESSION NOTES
-- ============================================================
INSERT INTO session_notes (note_id, session_id, session_goals, mood, topics, techniques, progress, risk_assessment, therapist_notes, homework, next_plan) VALUES
(1, 1, 'تقليل حدة نوبات القلق وتعلّم أدوات تنظيم ذاتي', 'قلق متوسط، المريض متعاون',       'القلق قبل النوم، ضغط العمل',  'تمارين تنفس 4-7-8، إعادة بناء معرفي',   'تحسن طفيف في جودة النوم',       'لا يوجد خطر',         'يبدي المريض التزاماً جيداً بالتمارين',        'ممارسة تمارين التنفس يومياً لمدة 10 دقائق', 'متابعة بعد أسبوع، إدخال مذكّرات يومية'),
(2, 2, 'بناء علاقة علاجية وفهم تاريخ الحالة',           'مزاج منخفض، بكاء خلال الجلسة',   'فقدان الشهية، العزلة',        'الاستماع الفعّال، تقييم PHQ-9',          'جلسة تقييم فقط',                 'انتباه لاحتمال تردي', 'تحتاج إلى متابعة لصيقة الأسبوعين القادمين',  'تسجيل يومي للمزاج من 1 إلى 10',             'جلسة قادمة لمراجعة PHQ-9 وبدء CBT'),
(3, 3, 'تقديم تقنيات CBT أساسية',                       'أفضل من الجلسة السابقة',         'الأفكار السلبية التلقائية',    'إعادة هيكلة معرفية، تحدي الأفكار',       'تحسن ملحوظ، درجة PHQ-9 انخفضت',  'لا يوجد',             'المريضة تتفاعل جيداً مع الأدوات',             'ملء سجل الأفكار السلبية يومياً',            'التعمق في تقنية التفعيل السلوكي'),
(4, 4, 'تقييم عادات النوم',                             'هادئ، متعاون',                   'الأرق، الكوابيس',              'نظافة النوم، تقييد النوم',               'مرحلة أولية',                     'لا يوجد',             'نوم متقطع وسعة يومية للنوم ساعة واحدة فقط',   'جدول تتبع للنوم لمدة أسبوع',                 'مراجعة الجدول وتعديل الخطة'),
(5, 5, 'تعلّم إدارة التوتر',                            'متوتر لكنه منفتح',               'ضغط العمل، قلة التركيز',       'استرخاء عضلي متدرج، جدولة أولويات',       'تحسن في التعامل مع المهام',       'لا يوجد',             'يحتاج إلى رسم حدود مع العمل',                  'تطبيق تقنية بومودورو في العمل',              'متابعة خلال 10 أيام'),
(6, 6, 'تقييم أولي للقلق الاجتماعي',                     'قلق مرتفع في بداية الجلسة',       'الخوف من التجمعات',            'مقياس LSAS، حوار دافع',                   'جلسة تقييم',                      'لا يوجد',             'المريضة تتجنب مواقف كثيرة',                    'قائمة مواقف مقلقة مرتبة من 1 إلى 10',          'بدء التعرض التدريجي بالأسبوع القادم'),
(7, 7, 'معالجة ذاكرة صدمة الفقد',                       'ضاغط، بكاء خلال المعالجة',        'ذاكرة الحادث، مشاعر الذنب',    'EMDR، استقرار جسدي',                      'تقدم بطيء ولكن إيجابي',           'متابعة لأفكار الخطر', 'المريض تفاعل مع المعالجة رغم الصعوبة',        'تمارين مكان آمن يومياً',                     'متابعة EMDR الأسبوع القادم'),
(8, 8, 'تقييم أولي لأعراض الوسواس القهري',               'متعاون، يشعر بالحرج من أعراضه',   'الأفكار التطفلية، السلوكيات القسرية', 'مقياس Y-BOCS، تعليم نفسي',         'جلسة تقييم',                      'لا يوجد',             'استجابة جيدة للتثقيف النفسي',                  'تتبع عدد مرات السلوك القسري يومياً',         'بدء خطة العلاج المعرفي السلوكي');

-- ============================================================
-- 10. MESSAGES
-- ============================================================
INSERT INTO messages (sender_id, receiver_id, case_id, content, type, file_path, is_read, sent_at) VALUES
(7,  2, 1, 'صباح الخير دكتورة مها',                          'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 150 MINUTE)),
(2,  7, 1, 'صباح النور عمر، كيف حالك اليوم؟',                 'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 145 MINUTE)),
(7,  2, 1, 'الحمد لله أفضل بكثير، تمارين التنفس ساعدتني جداً', 'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 140 MINUTE)),
(2,  7, 1, 'ممتاز! استمر عليها يومياً',                        'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 135 MINUTE)),
(7,  2, 1, 'شكراً جزيلاً دكتورة',                               'TEXT', NULL, 0, DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(8,  2, 2, 'دكتورة، متى يمكنني حجز جلسة مراجعة؟',               'TEXT', NULL, 0, DATE_SUB(NOW(), INTERVAL 180 MINUTE)),
(10, 2, 4, 'السلام عليكم دكتورة',                               'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 1440 MINUTE)),
(2,  10,4, 'وعليكم السلام نور، كيف حالك مع جدول النوم؟',        'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 1435 MINUTE)),
(10, 2, 4, 'تحسّن كثير، الحمد لله',                             'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 1430 MINUTE)),
(2,  11,5, 'يوسف، هل جربت تقنية بومودورو اليوم؟',               'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(11, 2, 5, 'نعم دكتورة، أول مرة أنجز فيها كل المهام',            'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(14, 5, 8, 'دكتورة، أواجه صعوبة في تمرين التعرض',                'TEXT', NULL, 0, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(15, 4, 9, 'شكراً على الدعم المستمر',                            'TEXT', NULL, 1, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ============================================================
-- 11. ACTIVITIES (created by therapists)
-- ============================================================
INSERT INTO activities (activity_id, title, description, category, activity_type, duration_min, difficulty, status, created_by, views, game_key, link) VALUES
(1, 'تمرين التنفس العميق 4-7-8',          'تقنية تنفس تساعد على الاسترخاء وتخفيف القلق',         'تنفس واسترخاء', 'EXERCISE', 10, 'EASY',   'ACTIVE', 2, 24, NULL,           NULL),
(2, 'مذكرات الامتنان اليومية',            'كتابة 3 أشياء تشعر بالامتنان لها كل يوم',             'كتابة',          'TASK',     15, 'EASY',   'ACTIVE', 2, 18, NULL,           NULL),
(3, 'استرخاء العضلات التدريجي',            'تمرين استرخاء جسدي لتقليل التوتر العضلي',            'تنفس واسترخاء', 'EXERCISE', 20, 'MEDIUM', 'ACTIVE', 2, 11, NULL,           NULL),
(4, 'سجل الأفكار السلبية',                'توثيق الأفكار السلبية التلقائية وتحديّها',             'CBT',           'TASK',     15, 'MEDIUM', 'ACTIVE', 2, 9,  NULL,           NULL),
(5, 'لعبة التركيز الذهني',                 'لعبة تنمي مهارات التركيز والانتباه',                  'ألعاب ذهنية',   'GAME',     15, 'EASY',   'DRAFT',  2, 0,  'Difference',   NULL),
(6, 'تمرين التعرض التدريجي',                'سلم مواقف مقلقة مرتب من الأسهل إلى الأصعب',          'CBT',           'EXERCISE', 30, 'HARD',   'ACTIVE', 5, 6,  NULL,           NULL),
(7, 'تمارين اليقظة الذهنية (Mindfulness)', 'ممارسة يومية لليقظة والتواجد في اللحظة الحاضرة',     'يقظة ذهنية',    'EXERCISE', 20, 'MEDIUM', 'ACTIVE', 5, 7,  NULL,           NULL),
(8, 'لعبة الورق التذكيرية',                'لعبة تدريب على الذاكرة والتركيز باستخدام البطاقات',  'ألعاب ذهنية',   'GAME',     10, 'EASY',   'ACTIVE', 6, 3,  'Cards',        NULL),
(9, 'كلمات متقاطعة للاسترخاء',              'لعبة كلمات متقاطعة لتشتيت الانتباه عن القلق',         'ألعاب ذهنية',   'GAME',     15, 'EASY',   'ACTIVE', 6, 2,  'Crossword',    NULL);

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
(9, 8, 6, 5, '2026-03-01 15:00:00', '2026-05-01 23:59:59', 'ابدأ من الموقف الأقل إثارة للقلق'),
(10,8, 7, 5, '2026-03-15 15:00:00', '2026-05-15 23:59:59', '10 دقائق صباحاً ومساءً'),
(11,9, 7, 4, '2026-02-20 16:00:00', '2026-04-20 23:59:59', 'اتبع التسجيلات الصوتية المرفقة'),
(12,10,4, 5, '2025-11-01 10:00:00', '2025-12-01 23:59:59', 'استخدم سلم المواقف المرفق'),
(13,7, 8, 3, '2026-04-06 09:00:00', '2026-04-30 23:59:59', 'العب مع طفلك 10 دقائق يومياً وسجل ملاحظاتك');

-- ============================================================
-- 13. ACTIVITY SUBMISSIONS
-- ============================================================
INSERT INTO activity_submissions (submission_id, case_activity_id, client_id, file_path, submission_type, text_response, status, therapist_feedback, submitted_at, reviewed_at) VALUES
(1,  1, 7, NULL, 'TEXT', 'مارست التمرين 3 أيام، شعرت بتحسن',                                       'COMPLETED', 'ممتاز! استمر',                          '2026-04-08 22:00:00', '2026-04-09 10:00:00'),
(2,  1, 7, NULL, 'TEXT', 'اليوم الرابع، القلق قبل النوم خفّ كثيراً',                                'COMPLETED', 'نتيجة رائعة',                           '2026-04-10 22:30:00', '2026-04-11 09:00:00'),
(3,  2, 7, NULL, 'TEXT', 'أفكار تدور حول ضغط العمل ومهلة مشروع قادم',                               'REVIEWED',  NULL,                                    '2026-04-15 21:00:00', NULL),
(4,  3, 8, NULL, 'TEXT', 'اليوم: ممتنة لأسرتي، لصحتي، لفنجان قهوتي الصباحي',                       'COMPLETED', 'جميل! مهم أن تنتبهي للتفاصيل الصغيرة', '2026-03-25 22:00:00', '2026-03-26 09:00:00'),
(5,  3, 8, NULL, 'TEXT', 'اليوم: الهواء النقي، مكالمة مع صديقة، ابتسامة ابنتي',                     'COMPLETED', 'استمري',                                '2026-04-01 22:15:00', '2026-04-02 10:00:00'),
(6,  4, 8, NULL, 'TEXT', 'فكرة: أنا فاشلة. تحدي: لم أكن أتخيل أنني سأعود للعمل، والآن أنجح',         'REVIEWED',  NULL,                                    '2026-04-12 20:00:00', NULL),
(7,  6, 10,NULL, 'TEXT', 'جربت التمرين ليلتين، الاستيقاظ ليلاً أقل',                                'COMPLETED', 'تقدم جيد',                              '2026-03-10 23:00:00', '2026-03-11 09:00:00'),
(8,  7, 10,NULL, 'TEXT', 'ساعدني التمرين على الاسترخاء قبل النوم',                                  'COMPLETED', 'ممتاز!',                                '2026-04-01 22:00:00', '2026-04-02 10:00:00'),
(9,  8, 11,NULL, 'TEXT', 'ممتن: الصحة، العائلة، الفرصة الجديدة في العمل',                           'COMPLETED', 'رائع',                                  '2026-03-18 21:00:00', '2026-03-19 10:00:00'),
(10, 9, 14,NULL, 'TEXT', 'اليوم الأول: طلبت قهوة من محل جديد، قلق متوسط',                          'COMPLETED', 'انجاز عظيم!',                           '2026-03-05 19:00:00', '2026-03-06 10:00:00'),
(11, 9, 14,NULL, 'TEXT', 'تحدثت مع زميلة جديدة في العمل',                                           'COMPLETED', 'خطوة مهمة',                             '2026-03-15 19:30:00', '2026-03-16 10:00:00'),
(12, 10,14,NULL, 'TEXT', 'أمارسها يومياً، أشعر بهدوء أكبر',                                         'REVIEWED',  NULL,                                    '2026-04-12 08:00:00', NULL),
(13, 11,15,NULL, 'TEXT', 'الأسبوع الأول: صعب ولكن أفضل من قبل',                                     'COMPLETED', 'استمر، أنت تتقدم',                      '2026-03-01 20:00:00', '2026-03-02 10:00:00'),
(14, 11,15,NULL, 'TEXT', 'الأسبوع الرابع: الكوابيس أقل بكثير',                                      'PENDING',   NULL,                                    '2026-04-18 21:00:00', NULL),
(15, 12,16,NULL, 'TEXT', 'استخدمت سلم المواقف، تحسن واضح في القلق من اللمس',                        'COMPLETED', 'تقدم ممتاز، استمر في تطبيق الخطة',     '2025-11-15 18:00:00', '2025-11-16 09:00:00'),
(16, 13,13,NULL, 'TEXT', 'لعبنا معاً 10 دقائق، كان أقل عصبية من المعتاد',                          'COMPLETED', 'بداية جيدة',                            '2026-04-10 19:00:00', '2026-04-11 09:00:00');

-- ============================================================
-- 14. ASSESSMENTS
-- ============================================================
INSERT INTO assessments (assessment_id, code, title, description, category, max_score, question_count, is_active, title_ar) VALUES
(1, 'BDI-II', 'Beck Depression Inventory', 'تقييم شدة أعراض الاكتئاب',                'اكتئاب',    63.00, 21, 1, 'مقياس بيك للاكتئاب'),
(2, 'GAD-7',  'Generalized Anxiety Disorder Scale', 'قياس شدة أعراض القلق العام',     'قلق',       21.00,  7, 1, 'مقياس القلق العام'),
(3, 'PSS-10', 'Perceived Stress Scale',    'قياس مستوى التوتر المدرك',                'ضغط نفسي', 40.00, 10, 1, 'مقياس الضغط النفسي'),
(4, 'PCL-5',  'PTSD Checklist for DSM-5',  'فحص أعراض ما بعد الصدمة',                  'صدمة',      80.00, 20, 1, 'مقياس اضطراب ما بعد الصدمة'),
(5, 'Y-BOCS', 'Yale-Brown Obsessive Compulsive Scale', 'تقييم شدة الأعراض الوسواسية', 'وسواس',     40.00, 10, 1, 'مقياس الوسواس القهري'),
(6, 'LSAS',   'Liebowitz Social Anxiety Scale', 'قياس الخوف والتجنب في المواقف الاجتماعية', 'رهاب', 60.00, 24, 1, 'مقياس الرهاب الاجتماعي');

-- ============================================================
-- 15. ASSESSMENT RESULTS
-- ============================================================
INSERT INTO assessment_results (result_id, assessment_id, client_id, case_id, trait_score, level, status, reviewed_at, raw_answers, created_at, raw_result) VALUES
(1,  2, 7,  1, 18, 'HIGH',   'COMPLETED', '2026-03-10 10:00:00', NULL, '2026-03-05 10:00:00', NULL),
(2,  2, 7,  1, 15, 'HIGH',   'COMPLETED', '2026-03-28 10:00:00', NULL, '2026-03-25 10:00:00', NULL),
(3,  2, 7,  1, 11, 'MEDIUM', 'PENDING',   NULL,                  NULL, '2026-04-15 10:00:00', NULL),
(4,  1, 8,  2, 28, 'MEDIUM', 'COMPLETED', '2026-02-25 11:00:00', NULL, '2026-02-20 11:00:00', NULL),
(5,  1, 8,  2, 25, 'MEDIUM', 'COMPLETED', '2026-03-22 11:00:00', NULL, '2026-03-18 11:00:00', NULL),
(6,  1, 8,  2, 22, 'MEDIUM', 'PENDING',   NULL,                  NULL, '2026-04-12 11:00:00', NULL),
(7,  1, 9,  3, 32, 'HIGH',   'PENDING',   NULL,                  NULL, '2026-04-10 09:30:00', NULL),
(8,  3, 10, 4, 28, 'HIGH',   'COMPLETED', '2026-03-05 14:00:00', NULL, '2026-03-01 14:00:00', NULL),
(9,  3, 10, 4, 21, 'MEDIUM', 'COMPLETED', '2026-04-08 14:00:00', NULL, '2026-04-05 14:00:00', NULL),
(10, 3, 10, 4, 14, 'LOW',    'PENDING',   NULL,                  NULL, '2026-04-19 14:00:00', NULL),
(11, 3, 11, 5, 22, 'MEDIUM', 'COMPLETED', '2026-03-15 12:00:00', NULL, '2026-03-12 12:00:00', NULL),
(12, 3, 11, 5, 18, 'MEDIUM', 'COMPLETED', '2026-04-10 12:00:00', NULL, '2026-04-08 12:00:00', NULL),
(13, 6, 14, 8, 72, 'SEVERE', 'COMPLETED', '2026-02-15 15:00:00', NULL, '2026-02-10 15:00:00', NULL),
(14, 6, 14, 8, 55, 'HIGH',   'COMPLETED', '2026-03-20 15:00:00', NULL, '2026-03-15 15:00:00', NULL),
(15, 6, 14, 8, 42, 'MEDIUM', 'PENDING',   NULL,                  NULL, '2026-04-14 15:00:00', NULL),
(16, 4, 15, 9, 48, 'HIGH',   'COMPLETED', '2026-02-20 16:00:00', NULL, '2026-02-15 16:00:00', NULL),
(17, 4, 15, 9, 55, 'SEVERE', 'COMPLETED', '2026-03-15 16:00:00', NULL, '2026-03-10 16:00:00', NULL),
(18, 4, 15, 9, 55, 'SEVERE', 'PENDING',   NULL,                  NULL, '2026-04-14 16:00:00', NULL),
(19, 5, 16,10, 30, 'HIGH',   'COMPLETED', '2025-11-05 10:00:00', NULL, '2025-11-01 10:00:00', NULL),
(20, 5, 16,10, 18, 'MEDIUM', 'COMPLETED', '2026-02-01 10:00:00', NULL, '2026-01-25 10:00:00', NULL),
(21, 5, 16,10,  8, 'LOW',    'COMPLETED', '2026-03-20 10:00:00', NULL, '2026-03-15 10:00:00', NULL);

-- ============================================================
-- 16. ASSESSMENT SUGGESTIONS
-- ============================================================
INSERT INTO assessment_suggestions (suggestion_id, client_id, therapist_id, assessment_id, suggested_at) VALUES
(1, 9,  2, 1, '2026-04-10 09:30:00'),
(2, 12, 3, 3, '2026-04-12 10:00:00'),
(3, 13, 3, 4, '2026-04-05 09:30:00');

-- ============================================================
-- 17. FOCUS TESTS
-- ============================================================
INSERT INTO focus_tests (focus_test_id, title, description, test_type, is_active) VALUES
(1, 'اختبار الانتباه المستمر', 'قياس قدرة الانتباه على فترات طويلة', 'ATTENTION', 1),
(2, 'اختبار سرعة التفاعل',    'قياس سرعة الاستجابة للمنبهات',       'REACTION',  1),
(3, 'اختبار التركيز البصري',   'قياس التركيز على الحفزات البصرية',    'VISUAL',    1);

-- ============================================================
-- 18. FOCUS RESULTS
-- ============================================================
INSERT INTO focus_results (focus_result_id, focus_test_id, client_id, case_id, duration_seconds, completion_pct, score, improvement_pct, created_at) VALUES
(1, 1, 7,  1, 300, 85.00, 78.00, 12.00, '2026-03-15 10:00:00'),
(2, 1, 7,  1, 300, 90.00, 85.00,  9.00, '2026-04-10 10:00:00'),
(3, 2, 8,  2, 180, 92.00, 88.00,  5.00, '2026-03-28 11:00:00'),
(4, 3, 11, 5, 240, 78.00, 72.00, -3.00, '2026-04-05 12:00:00');

-- ============================================================
-- 19. GAME RESULTS
-- ============================================================
INSERT INTO game_results (id, client_id, game_name, played_at, is_completed, time_seconds, points, moves, difficulty, level, time_per_level, levels_passed) VALUES
(1, 7,  'Breathing',    DATE_SUB(NOW(), INTERVAL 2 DAY), 1, 600, 100, NULL, 'EASY',   1, NULL, NULL),
(2, 11, 'Cards',        DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 240, 80,  24,   'EASY',   2, NULL, NULL),
(3, 14, 'Crossword',    DATE_SUB(NOW(), INTERVAL 5 DAY), 0, 300, 40,  NULL, 'MEDIUM', 1, NULL, NULL),
(4, 13, 'Misplacedpin', DATE_SUB(NOW(), INTERVAL 3 DAY), 1, 180, 60,  10,   'EASY',   1, NULL, NULL);

-- ============================================================
-- 20. ALERTS
-- ============================================================
INSERT INTO alerts (alert_id, user_id, case_id, description, level, is_handled, created_at, handled_at) VALUES
(1, 2, 2, 'مريضة تعاني اكتئاب متوسط - يتطلب متابعة أسبوعية', 'WARNING',  1, '2026-03-25 10:00:00', '2026-03-26 11:00:00'),
(2, 4, 9, 'حالة صدمة - خطر انتحاري محتمل',                    'CRITICAL', 0, '2026-04-10 16:00:00', NULL),
(3, 3, 7, 'حالة جديدة تحتاج إلى تقييم عاجل',                  'INFO',     0, '2026-04-05 09:00:00', NULL),
(4, 2, 3, 'لم يحضر الجلسة الأخيرة دون إشعار',                 'WARNING',  0, '2026-04-18 10:00:00', NULL);

-- ============================================================
-- 21. NOTIFICATIONS
-- ============================================================
INSERT INTO notifications (notification_id, user_id, title, body, type, priority, is_read, created_at) VALUES
(1,  2, 'موعد جديد تم تأكيده',     'تم تأكيد موعد مع عمر الخطيب في الغد الساعة 10:00 صباحاً',  'APPOINTMENT_REMINDER', 'URGENT', 0, DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(2,  2, 'رسالة جديدة من مريضة',     'أرسلت ليلى الحسيني رسالة تطلب فيها استشارة',              'MESSAGE',              'URGENT', 0, DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(3,  2, 'تنبيه: اختبار نفسي جديد',  'أكملت نور البرغوثي مقياس PSS-10 ويحتاج إلى مراجعة',        'ASSESSMENT_READY',     'NORMAL', 0, DATE_SUB(NOW(), INTERVAL 60 MINUTE)),
(4,  2, 'تذكير: جلسة قادمة',        'لديك جلسة مع محمود درويش خلال ساعة واحدة',                  'APPOINTMENT_REMINDER', 'URGENT', 1, DATE_SUB(NOW(), INTERVAL 120 MINUTE)),
(5,  2, 'مريض جديد تم تعيينه',       'تم تعيين المريضة فاطمة العجوري لك، يرجى مراجعة ملفها',       'ACTIVITY_ASSIGNED',    'NORMAL', 1, DATE_SUB(NOW(), INTERVAL 180 MINUTE)),
(6,  2, 'طلب إلغاء موعد',            'طلبت نور البرغوثي إلغاء الموعد المحدد لغد',                 'APPOINTMENT_REMINDER', 'NORMAL', 1, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(7,  2, 'رد على استفسار',            'رد يوسف الزيتاوي على استفسارك بخصوص تطبيق التمارين',        'GENERAL',              'LOW',    1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8,  2, 'تذكير: تقرير شهري',         'حان موعد إعداد التقرير الشهري لجلسات هذا الشهر',             'GENERAL',              'NORMAL', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(9,  3, 'حالة جديدة تحتاج إلى تقييم', 'تم تعيين حالة طارق أبو عرب - سلوك الابن العدواني',          'ACTIVITY_ASSIGNED',    'URGENT', 0, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(10, 5, 'رسالة جديدة',               'سلمى الشوا أرسلت لك رسالة جديدة',                            'MESSAGE',              'NORMAL', 0, DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- ============================================================
-- 22. THERAPIST REVIEWS
-- ============================================================
INSERT INTO therapist_reviews (review_id, therapist_id, client_id, rating, comment, created_at) VALUES
(1, 2, 7,  5, 'تجربة رائعة جداً، الدكتورة مها محترفة ومتفهمة وساعدتني كثيراً في تجاوز القلق. أنصح بها بشدة.',  '2026-04-05 18:00:00'),
(2, 2, 8,  5, 'أخصائية ممتازة وصبورة، الجلسات مفيدة جداً وأشعر بتحسن كبير في حالتي.',                          '2026-04-10 19:30:00'),
(3, 2, 10, 5, 'تعاملها راقٍ وجدولها مرن. خطة علاج واضحة وعملية.',                                              '2026-04-12 12:00:00'),
(4, 2, 11, 4, 'دكتورة متميزة، ساعدتني في التعامل مع ضغط العمل. الجلسات قد تكون قصيرة بعض الشيء.',               '2026-04-15 21:00:00'),
(5, 2, 7,  5, 'شكراً دكتورة، حياتي تغيرت للأفضل بفضل خطة العلاج.',                                             '2026-04-18 20:00:00'),
(6, 5, 14, 5, 'الدكتور ماجد متفهم جداً ويعطيني أدوات عملية للتعامل مع القلق الاجتماعي.',                       '2026-04-05 17:00:00'),
(7, 3, 12, 4, 'تحسن ملحوظ بعد عدة جلسات، الدكتور يفهم حالتي بعمق.',                                             '2026-04-10 18:00:00'),
(8, 5, 16, 5, 'نتيجة مذهلة، خرجت من الوسواس تدريجياً بفضل خطته.',                                              '2025-03-22 14:00:00');

-- ============================================================
-- 23. PAYMENTS
-- ============================================================
INSERT INTO payments (payment_id, client_id, therapist_id, amount, created_at) VALUES
(1, 7,  2, 50.00, '2026-04-01 09:00:00'),
(2, 8,  2, 50.00, '2026-03-25 10:00:00'),
(3, 8,  2, 50.00, '2026-04-05 10:00:00'),
(4, 10, 2, 50.00, '2026-04-10 10:00:00'),
(5, 11, 2, 50.00, '2026-04-05 08:00:00'),
(6, 14, 5, 40.00, '2026-04-01 10:00:00'),
(7, 15, 4, 60.00, '2026-04-08 10:00:00'),
(8, 16, 5, 40.00, '2025-10-01 09:00:00');

-- ============================================================
-- 24. LOGIN ACTIVITIES
-- ============================================================
INSERT INTO login_activities (login_id, user_id, login_date, login_time, browser, os, ip_address, country, city, created_at) VALUES
(1, 1, CURDATE(), '08:00:00', 'Chrome',  'Windows', '10.0.0.1',   'Palestine', 'Ramallah', NOW()),
(2, 2, CURDATE(), '08:30:00', 'Chrome',  'Windows', '10.0.0.2',   'Palestine', 'Nablus',   NOW()),
(3, 3, CURDATE(), '09:00:00', 'Safari',  'macOS',   '10.0.0.3',   'Palestine', 'Hebron',   NOW()),
(4, 7, CURDATE(), '09:45:00', 'Chrome',  'Android', '10.0.0.10',  'Palestine', 'Ramallah', NOW()),
(5, 8, CURDATE(), '10:15:00', 'Safari',  'iOS',     '10.0.0.11',  'Palestine', 'Nablus',   NOW());

-- ============================================================
-- Reset AUTO_INCREMENTs to continue from the max seeded id
-- ============================================================
ALTER TABLE users                  AUTO_INCREMENT = 18;
ALTER TABLE client_surveys         AUTO_INCREMENT = 11;
ALTER TABLE therapist_availability AUTO_INCREMENT = 34;
ALTER TABLE cases                  AUTO_INCREMENT = 11;
ALTER TABLE appointments           AUTO_INCREMENT = 20;
ALTER TABLE sessions               AUTO_INCREMENT = 9;
ALTER TABLE session_notes          AUTO_INCREMENT = 9;
ALTER TABLE messages               AUTO_INCREMENT = 14;
ALTER TABLE activities             AUTO_INCREMENT = 10;
ALTER TABLE case_activities         AUTO_INCREMENT = 14;
ALTER TABLE activity_submissions   AUTO_INCREMENT = 17;
ALTER TABLE assessments            AUTO_INCREMENT = 7;
ALTER TABLE assessment_results     AUTO_INCREMENT = 22;
ALTER TABLE assessment_suggestions AUTO_INCREMENT = 4;
ALTER TABLE focus_tests            AUTO_INCREMENT = 4;
ALTER TABLE focus_results          AUTO_INCREMENT = 5;
ALTER TABLE game_results            AUTO_INCREMENT = 5;
ALTER TABLE alerts                 AUTO_INCREMENT = 5;
ALTER TABLE notifications          AUTO_INCREMENT = 11;
ALTER TABLE therapist_reviews      AUTO_INCREMENT = 9;
ALTER TABLE payments               AUTO_INCREMENT = 9;
ALTER TABLE login_activities       AUTO_INCREMENT = 6;

SET FOREIGN_KEY_CHECKS = 1;

-- Done. Log in with any of the accounts listed at the top of this file.