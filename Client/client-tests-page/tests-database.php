<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/db.php';

start_secure_session();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if (!is_authenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'غير مصرح']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'بيانات غير صالحة']);
        exit;
    }

    $assessmentId  = (int) ($input['assessment_id'] ?? 0);
    $traitScore    = isset($input['trait_score']) ? (float) $input['trait_score'] : null;
    $level         = isset($input['level']) && !is_array($input['level']) ? trim((string) $input['level']) : null;
    $rawAnswers    = isset($input['raw_answers']) ? json_encode($input['raw_answers'], JSON_UNESCAPED_UNICODE) : null;
    $rawResultData = $input['raw_result'] ?? null;

    if ($assessmentId === 2) {
        $level         = null;
        $rawResultData = tests_normalize_hopkins_raw_result($input);
    }

    if (tests_is_children_mental_health_result($assessmentId, $input)) {
        $assessmentId  = 3;
        $level         = tests_children_mental_health_level($traitScore ?? 0.0);
        $rawResultData = tests_normalize_children_mental_health_raw_result($input, $traitScore ?? 0.0);
    }

    if (tests_is_snap_result($assessmentId, $input)) {
        $assessmentId  = 4;
        $level         = null;
        $rawResultData = tests_normalize_snap_raw_result($input);
    }

    if (tests_is_stress_result($assessmentId, $input)) {
        $assessmentId  = 5;
        $level         = tests_stress_level($traitScore ?? 0.0);
        $rawResultData = null;
    }

    if ($assessmentId === 6) {
        $level         = tests_social_anxiety_level($traitScore ?? 0.0);
        $rawResultData = null;
    }

    $rawResult = $rawResultData !== null
        ? json_encode($rawResultData, JSON_UNESCAPED_UNICODE)
        : null;

    if ($assessmentId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'معرف الاختبار مطلوب']);
        exit;
    }

    $clientId = (int) $_SESSION['auth']['user_id'];
    $caseId   = tests_get_client_active_case_id($clientId);

    try {
        $resultId = tests_save_assessment_result(
            $assessmentId,
            $clientId,
            $caseId,
            $traitScore,
            $level,
            $rawAnswers,
            $rawResult
        );
        echo json_encode(['success' => true, 'result_id' => $resultId]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'حدث خطأ أثناء الحفظ']);
    }
    exit;
}

// ─── Route: GET ?action=stats

if (($_GET['action'] ?? '') === 'stats') {
    header('Content-Type: application/json; charset=utf-8');

    if (!is_authenticated()) {
        echo json_encode(['success' => false]);
        exit;
    }

    $clientId = (int) $_SESSION['auth']['user_id'];
    $stats    = tests_get_client_assessment_stats($clientId);

    echo json_encode(['success' => true, ...$stats]);
    exit;
}

// ─── Functions 

function tests_get_client_active_case_id(int $clientId): ?int
{
    $stmt = db()->prepare(
        "SELECT case_id
         FROM cases
         WHERE client_id = :client_id
         ORDER BY created_at DESC, case_id DESC
         LIMIT 1"
    );
    $stmt->execute(['client_id' => $clientId]);
    $row = $stmt->fetch();

    return $row ? (int) $row['case_id'] : null;
}

function tests_start_assessment_result(int $assessmentId, int $clientId): int
{
    tests_complete_legacy_pending_results($assessmentId, $clientId);

    $stmt = db()->prepare(
        "SELECT result_id
         FROM assessment_results
         WHERE client_id = :client_id
           AND assessment_id = :assessment_id
           AND status = 'PENDING'
         LIMIT 1"
    );
    $stmt->execute([
        'client_id' => $clientId,
        'assessment_id' => $assessmentId,
    ]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        return (int) $existingId;
    }

    $caseId = tests_get_client_active_case_id($clientId);

    $stmt = db()->prepare(
        "INSERT INTO assessment_results
            (assessment_id, client_id, case_id, trait_score, level, status, reviewed_at, raw_answers, raw_result)
         VALUES
            (:assessment_id, :client_id, :case_id, NULL, NULL, 'PENDING', NULL, NULL, NULL)"
    );
    $stmt->execute([
        'assessment_id' => $assessmentId,
        'client_id' => $clientId,
        'case_id' => $caseId,
    ]);

    return (int) db()->lastInsertId();
}

function tests_complete_legacy_pending_results(int $assessmentId, int $clientId): void
{
    $stmt = db()->prepare(
        "UPDATE assessment_results
         SET status = 'COMPLETED'
         WHERE client_id = :client_id
           AND assessment_id = :assessment_id
           AND status = 'PENDING'
           AND (
               trait_score IS NOT NULL
               OR level IS NOT NULL
               OR raw_answers IS NOT NULL
               OR raw_result IS NOT NULL
           )"
    );
    $stmt->execute([
        'client_id' => $clientId,
        'assessment_id' => $assessmentId,
    ]);
}


function tests_normalize_hopkins_raw_result(array $input): array
{
    $source = $input['raw_result'] ?? null;

    if (!is_array($source) && isset($input['level']) && is_array($input['level'])) {
        $source = $input['level'];
    }

    $anxietyScore = tests_nested_numeric_value($source, 'anxiety', 'score');
    $depressionScore = tests_nested_numeric_value($source, 'depression', 'score');

    if ($anxietyScore === null || $depressionScore === null) {
        [$anxietyScore, $depressionScore] = tests_scores_from_hopkins_answers($input['raw_answers'] ?? []);
    }

    $anxietyAvg = $anxietyScore / 10;
    $depressionAvg = $depressionScore / 15;

    return [
        'anxiety' => [
            'score' => $anxietyScore,
            'level' => tests_hopkins_level_from_average($anxietyAvg),
        ],
        'depression' => [
            'score' => $depressionScore,
            'level' => tests_hopkins_level_from_average($depressionAvg),
        ],
    ];
}

function tests_nested_numeric_value(mixed $source, string $group, string $key): ?float
{
    if (!is_array($source) || !isset($source[$group]) || !is_array($source[$group])) {
        return null;
    }

    return isset($source[$group][$key]) && is_numeric($source[$group][$key])
        ? (float) $source[$group][$key]
        : null;
}

function tests_scores_from_hopkins_answers(mixed $answers): array
{
    $anxietyScore = 0.0;
    $depressionScore = 0.0;

    if (!is_array($answers)) {
        return [$anxietyScore, $depressionScore];
    }

    foreach ($answers as $answer) {
        if (!is_array($answer)) {
            continue;
        }

        $question = (int) ($answer['q'] ?? 0);
        $value = isset($answer['value']) && is_numeric($answer['value'])
            ? (float) $answer['value']
            : 0.0;

        if ($question >= 1 && $question <= 10) {
            $anxietyScore += $value;
        } elseif ($question >= 11 && $question <= 25) {
            $depressionScore += $value;
        }
    }

    return [$anxietyScore, $depressionScore];
}

function tests_hopkins_level_from_average(float $value): string
{
    if ($value < 1.75) {
        return 'MINIMAL';
    }

    if ($value < 2.5) {
        return 'MEDIUM';
    }

    return 'HIGH';
}

function tests_is_children_mental_health_result(int $assessmentId, array $input): bool
{
    if (!in_array($assessmentId, [3, 8], true)) {
        return false;
    }

    $rawAnswers = $input['raw_answers'] ?? null;
    if (is_array($rawAnswers) && count($rawAnswers) === 17) {
        return true;
    }

    $source = $input['raw_result'] ?? null;
    if (!is_array($source) && isset($input['level']) && is_array($input['level'])) {
        $source = $input['level'];
    }

    return is_array($source)
        && isset($source['max_score'])
        && (int) $source['max_score'] === 34;
}

function tests_children_mental_health_level(float $totalScore): string
{
    if ($totalScore <= 11) {
        return 'MINIMAL';
    }

    if ($totalScore <= 22) {
        return 'MEDIUM';
    }

    return 'HIGH';
}

function tests_normalize_children_mental_health_raw_result(array $input, float $totalScore): array
{
    $source = $input['raw_result'] ?? null;
    if (!is_array($source) && isset($input['level']) && is_array($input['level'])) {
        $source = $input['level'];
    }

    if (is_array($source) && isset($source['result']) && is_string($source['result'])) {
        return ['result' => $source['result']];
    }

    if (is_array($source) && isset($source['level']) && is_string($source['level'])) {
        return ['result' => $source['level']];
    }

    return ['result' => tests_children_mental_health_result_text($totalScore)];
}

function tests_children_mental_health_result_text(float $totalScore): string
{
    if ($totalScore <= 11) {
        return json_decode('"\u0627\u0644\u062d\u0627\u0644\u0629 \u0627\u0644\u0646\u0641\u0633\u064a\u0629 \u0645\u0633\u062a\u0642\u0631\u0629"', true);
    }

    if ($totalScore <= 22) {
        return json_decode('"\u0645\u0624\u0634\u0631\u0627\u062a \u062a\u062d\u062a\u0627\u062c \u0645\u062a\u0627\u0628\u0639\u0629"', true);
    }

    return json_decode('"\u064a\u062d\u062a\u0627\u062c \u0625\u0644\u0649 \u062f\u0639\u0645 \u0646\u0641\u0633\u064a"', true);
}

function tests_is_snap_result(int $assessmentId, array $input): bool
{
    if (!in_array($assessmentId, [4, 9], true)) {
        return false;
    }

    $rawAnswers = $input['raw_answers'] ?? null;
    if (is_array($rawAnswers) && count($rawAnswers) === 26) {
        return true;
    }

    $source = $input['raw_result'] ?? null;
    if (!is_array($source) && isset($input['level']) && is_array($input['level'])) {
        $source = $input['level'];
    }

    return is_array($source)
        && isset($source['inattention'], $source['hyperactivity'])
        && (isset($source['oppositional_defiant']) || isset($source['odd']));
}

function tests_normalize_snap_raw_result(array $input): array
{
    $source = $input['raw_result'] ?? null;
    if (!is_array($source) && isset($input['level']) && is_array($input['level'])) {
        $source = $input['level'];
    }

    $inScore = tests_nested_numeric_value($source, 'inattention', 'score');
    $hyperScore = tests_nested_numeric_value($source, 'hyperactivity', 'score');
    $oddScore = tests_nested_numeric_value($source, 'oppositional_defiant', 'score');

    if ($oddScore === null) {
        $oddScore = tests_nested_numeric_value($source, 'odd', 'score');
    }

    if ($inScore === null || $hyperScore === null || $oddScore === null) {
        [$inScore, $hyperScore, $oddScore] = tests_scores_from_snap_answers($input['raw_answers'] ?? []);
    }

    return [
        'inattention' => [
            'score' => $inScore,
            'level' => tests_snap_level($inScore, 'IN'),
        ],
        'hyperactivity' => [
            'score' => $hyperScore,
            'level' => tests_snap_level($hyperScore, 'H'),
        ],
        'oppositional_defiant' => [
            'score' => $oddScore,
            'level' => tests_snap_level($oddScore, 'ODD'),
        ],
    ];
}

function tests_scores_from_snap_answers(mixed $answers): array
{
    $inScore = 0.0;
    $hyperScore = 0.0;
    $oddScore = 0.0;

    if (!is_array($answers)) {
        return [$inScore, $hyperScore, $oddScore];
    }

    foreach ($answers as $answer) {
        if (!is_array($answer)) {
            continue;
        }

        $question = (int) ($answer['q'] ?? 0);
        $value = isset($answer['value']) && is_numeric($answer['value'])
            ? (float) $answer['value']
            : 0.0;

        if ($question >= 1 && $question <= 9) {
            $inScore += $value;
        } elseif ($question >= 10 && $question <= 18) {
            $hyperScore += $value;
        } elseif ($question >= 19 && $question <= 26) {
            $oddScore += $value;
        }
    }

    return [$inScore, $hyperScore, $oddScore];
}

function tests_snap_level(float $score, string $type): string
{
    if ($type === 'ODD') {
        if ($score < 8) {
            return tests_snap_level_text('none');
        }

        if ($score <= 13) {
            return tests_snap_level_text('low');
        }

        if ($score <= 18) {
            return tests_snap_level_text('medium');
        }

        return tests_snap_level_text('high');
    }

    if ($score < 13) {
        return tests_snap_level_text('none');
    }

    if ($score <= 17) {
        return tests_snap_level_text('low');
    }

    if ($score <= 22) {
        return tests_snap_level_text('medium');
    }

    return tests_snap_level_text('high');
}

function tests_snap_level_text(string $level): string
{
    $levels = [
        'none' => '"\u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0634\u0643\u0644\u0629 \u0648\u0627\u0636\u062d\u0629"',
        'low' => '"\u0623\u0639\u0631\u0627\u0636 \u0628\u0633\u064a\u0637\u0629"',
        'medium' => '"\u0623\u0639\u0631\u0627\u0636 \u0645\u062a\u0648\u0633\u0637\u0629"',
        'high' => '"\u0623\u0639\u0631\u0627\u0636 \u0634\u062f\u064a\u062f\u0629"',
    ];

    return json_decode($levels[$level], true);
}

function tests_is_stress_result(int $assessmentId, array $input): bool
{
    if ($assessmentId === 5) {
        return true;
    }

    if ($assessmentId !== 3) {
        return false;
    }

    $rawAnswers = $input['raw_answers'] ?? null;
    if (is_array($rawAnswers) && count($rawAnswers) === 35) {
        return true;
    }

    $source = $input['raw_result'] ?? null;
    if (!is_array($source) && isset($input['level']) && is_array($input['level'])) {
        $source = $input['level'];
    }

    return is_array($source)
        && isset($source['max_score'])
        && (int) $source['max_score'] === 70;
}

function tests_stress_level(float $totalScore): string
{
    if ($totalScore <= 23) {
        return 'LOW';
    }

    if ($totalScore <= 46) {
        return 'MEDIUM';
    }

    return 'HIGH';
}

function tests_social_anxiety_level(float $totalScore): string
{
    if ($totalScore <= 19) {
        return 'LOW';
    }

    if ($totalScore <= 38) {
        return 'MEDIUM';
    }

    return 'HIGH';
}

/**
 * Complete the client's pending assessment result and update assessments.max_score
 * if the new trait_score exceeds the current max.
 */
function tests_save_assessment_result(
    int     $assessmentId,
    int     $clientId,
    ?int    $caseId,
    ?float  $traitScore,
    ?string $level,
    ?string $rawAnswers,
    ?string $rawResult
): int {
    $resultId = tests_start_assessment_result($assessmentId, $clientId);

    $stmt = db()->prepare(
        "UPDATE assessment_results
         SET case_id = :case_id,
             trait_score = :trait_score,
             level = :level,
             status = 'COMPLETED',
             reviewed_at = NULL,
             raw_answers = :raw_answers,
             raw_result = :raw_result
         WHERE result_id = :result_id
           AND client_id = :client_id
           AND assessment_id = :assessment_id
           AND status = 'PENDING'"
    );
    $stmt->execute([
        'case_id'       => $caseId,
        'trait_score'   => $traitScore,
        'level'         => $level,
        'raw_answers'   => $rawAnswers,
        'raw_result'    => $rawResult,
        'result_id'     => $resultId,
        'assessment_id' => $assessmentId,
        'client_id'     => $clientId,
    ]);

    // Update assessments.max_score if the new score exceeds the current max
    if ($traitScore !== null) {
        $stmtUpdate = db()->prepare(
            "UPDATE assessments
             SET max_score = :new_score
             WHERE assessment_id = :assessment_id
               AND (max_score IS NULL OR max_score < :new_score2)"
        );
        $stmtUpdate->execute([
            'new_score'     => $traitScore,
            'assessment_id' => $assessmentId,
            'new_score2'    => $traitScore,
        ]);
    }

    return $resultId;
}


function tests_get_client_assessment_stats(int $clientId): array
{
    $stmt = db()->prepare(
        "SELECT
            (SELECT COUNT(*) FROM assessments) AS total_tests,
            COALESCE(SUM(status = 'COMPLETED'), 0) AS total_completed,
            CASE
                WHEN COUNT(*) = 0 THEN 0
                ELSE ROUND(
                    (COALESCE(SUM(status = 'COMPLETED'), 0) / COUNT(*)) * 100
                )
            END AS completion_rate
         FROM assessment_results
         WHERE client_id = :client_id"
    );
    $stmt->execute(['client_id' => $clientId]);
    $row = $stmt->fetch() ?: [];

    return [
        'total_tests'     => (int) ($row['total_tests'] ?? 0),
        'total_completed' => (int) ($row['total_completed'] ?? 0),
        'completion_rate' => (int) ($row['completion_rate'] ?? 0),
    ];
}

/**
 * Get the most recent therapist-suggested assessment for a client.
 * Returns null if no suggestion exists.
 */
function tests_get_latest_suggestion(int $clientId): ?array
{
    $stmt = db()->prepare(
        "SELECT s.assessment_id, a.title_ar, a.title
         FROM assessment_suggestions s
         JOIN assessments a ON a.assessment_id = s.assessment_id
         WHERE s.client_id = :client_id
         ORDER BY s.suggested_at DESC
         LIMIT 1"
    );
    $stmt->execute(['client_id' => $clientId]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return [
        'assessment_id' => (int) $row['assessment_id'],
        'title_ar'      => $row['title_ar'] ?? '',
        'title'         => $row['title'] ?? '',
        'page_url'      => tests_assessment_page_url((int) $row['assessment_id']),
    ];
}


function tests_assessment_page_url(int $assessmentId): ?string
{
    $map = [
        1 => 'beck.php',
        2 => 'hopkins.php',
        3 => 'childrenmentalhealth.php',
        4 => 'snap.php',
        5 => 'stress.php',
        6 => 'social-anxiety.php',
    ];

    return $map[$assessmentId] ?? null;
}
