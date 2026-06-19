<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Database/appointments-database.php';

/**
 * Handles therapist accept/reject POST actions. Exits after sending JSON.
 */
function therapist_handle_appointment_request_post(int $therapistId): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (!isset($_POST['action'], $_POST['appointment_id'])) {
        return;
    }

    $appointmentId = (int) $_POST['appointment_id'];
    $action = trim((string) $_POST['action']);

    try {
        $result = handleTherapistAppointmentRequestAction($therapistId, $action, $appointmentId);
    } catch (Throwable) {
        $result = [
            'success' => false,
            'message' => 'حدث خطأ أثناء معالجة الطلب.',
        ];
    }

    if (!$result['success']) {
        http_response_code(422);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
