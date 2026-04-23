<?php
session_start();
include "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

$fullName = strip_tags(trim($_POST['fullName'] ?? ''));
$email = strip_tags(trim($_POST['email'] ?? ''));
$phone = strip_tags(trim($_POST['phone'] ?? ''));
$username = strip_tags(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

$treatmentType = strip_tags(trim($_POST['treatment_type'] ?? ''));
$sessionType = strip_tags(trim($_POST['session_type'] ?? ''));
$sessionTime = strip_tags(trim($_POST['session_time'] ?? ''));

$surveyTreatmentType = strip_tags(trim($_POST['survey_treatment_type'] ?? ''));
$surveySymptoms = strip_tags(trim($_POST['survey_symptoms'] ?? ''));
$surveyRepeatedSymptoms = strip_tags(trim($_POST['survey_repeated_symptoms'] ?? ''));
$surveyPrevTherapy = strip_tags(trim($_POST['survey_prev_therapy'] ?? ''));
$surveyAge = intval($_POST['survey_age'] ?? 0);
$surveyGender = strip_tags(trim($_POST['survey_gender'] ?? ''));
$surveyNationality = strip_tags(trim($_POST['survey_nationality'] ?? ''));
$surveyTherapistGender = strip_tags(trim($_POST['survey_therapist_gender'] ?? ''));
$surveyFamilyHistory = strip_tags(trim($_POST['survey_family_history'] ?? ''));
$surveyPhysicalIssues = strip_tags(trim($_POST['survey_physical_issues'] ?? ''));
$surveyPhysicalDetails = strip_tags(trim($_POST['survey_physical_details'] ?? ''));
$surveyMaritalStatus = strip_tags(trim($_POST['survey_marital_status'] ?? ''));
$surveyEducationLevel = strip_tags(trim($_POST['survey_education_level'] ?? ''));
$surveySmoking = strip_tags(trim($_POST['survey_smoking'] ?? ''));
$surveyAlcohol = strip_tags(trim($_POST['survey_alcohol'] ?? ''));
$surveyDrugs = strip_tags(trim($_POST['survey_drugs'] ?? ''));
$surveyContactPreference = strip_tags(trim($_POST['survey_contact_preference'] ?? ''));

$prevTherapyBool = ($surveyPrevTherapy === 'YES') ? 1 : 0;

$errors = [];

if (empty($fullName)) {
    $errors[] = "الاسم الكامل مطلوب";
}
if (empty($email)) {
    $errors[] = "البريد الإلكتروني مطلوب";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "البريد الإلكتروني غير صالح";
}
if (empty($phone)) {
    $errors[] = "رقم الهاتف مطلوب";
}
if (empty($username)) {
    $errors[] = "اسم المستخدم مطلوب";
}
if (empty($password)) {
    $errors[] = "كلمة المرور مطلوبة";
} elseif (strlen($password) < 8) {
    $errors[] = "كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل";
}
if ($password !== $confirmPassword) {
    $errors[] = "كلمتا المرور غير متطابقتين";
}

$oldData = [
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'username' => $username,
    'treatment_type' => $treatmentType,
    'session_type' => $sessionType,
    'session_time' => $sessionTime,
];

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['errors'] = ["البريد الإلكتروني مسجل مسبقاً"];
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}
$stmt->close();

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['errors'] = ["اسم المستخدم مستخدم مسبقاً"];
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}
$stmt->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO client_surveys
            (treatment_type, symptoms, repeated_symptoms, prev_therapy, age, gender, nationality,
             therapist_gender, family_history, physical_issues, physical_details, marital_status,
             education_level, smoking, alcohol, drugs, contact_preference)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssississssssssss",
        $surveyTreatmentType,
        $surveySymptoms,
        $surveyRepeatedSymptoms,
        $prevTherapyBool,
        $surveyAge,
        $surveyGender,
        $surveyNationality,
        $surveyTherapistGender,
        $surveyFamilyHistory,
        $surveyPhysicalIssues,
        $surveyPhysicalDetails,
        $surveyMaritalStatus,
        $surveyEducationLevel,
        $surveySmoking,
        $surveyAlcohol,
        $surveyDrugs,
        $surveyContactPreference
    );
    $stmt->execute();
    $surveyId = $stmt->insert_id;
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = 'CLIENT';

    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, username, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullName, $email, $phone, $username, $hashedPassword, $role);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("
        INSERT INTO clients (client_id, survey_id, gender, treatment_type, preferred_session_type, preferred_session_time)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iissss",
        $userId,
        $surveyId,
        $surveyGender,
        $treatmentType,
        $sessionType,
        $sessionTime
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['role'] = $role;
    header("Location: ../../homepage/index.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['errors'] = ["حدث خطأ أثناء التسجيل، حاول مرة أخرى"];
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}

$conn->close();
?>
