<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/client.php';


const RECO_ROLE_MAHA    = 'MAHA';    // مها الرفاعي  — نفسية اجتماعية، أسري، مشاكل العلاقات
const RECO_ROLE_ZAINAB  = 'ZAINAB';  // زينب كرمي    — أطفال، إرشاد والدي، صدمات، CBT
const RECO_ROLE_OMAR    = 'OMAR';    // عمر قدح      — حالات معقدة، اضطرابات متعددة، تقييمات
const RECO_ROLE_TASNEEM = 'TASNEEM'; // تسنيم زيدان  — مراهقون/بالغون، CBT، صدمات، حالات متقدمة
const RECO_ROLE_HADEEL  = 'HADEEL';  // هديل أبو رميلة — إرشاد فردي/زواجي، حالات خفيفة/متوسطة
const RECO_ROLE_NEROZ   = 'NEROZ';   // نيروز نجم الدين — سمع ونطق، لغة وتأتأة للأطفال

function recommend_role_keywords(): array
{
    return [
        RECO_ROLE_MAHA    => 'مها',
        RECO_ROLE_ZAINAB  => 'زينب',
        RECO_ROLE_OMAR    => 'عمر',
        RECO_ROLE_TASNEEM => 'تسنيم',
        RECO_ROLE_HADEEL  => 'هديل',
        RECO_ROLE_NEROZ   => 'نيروز',
    ];
}


function recommend_get_user_survey(int $userId): ?array
{
    $stmt = db()->prepare(
        'SELECT s.*
         FROM clients c
         INNER JOIN client_surveys s ON s.id = c.survey_id
         WHERE c.client_id = :user_id
         LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);

    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function recommend_load_therapists(): array
{
    $stmt = db()->prepare(
        'SELECT
            u.user_id,
            u.name,
            u.email,
            u.avatar,
            t.specialization,
            t.bio,
            t.certification,
            t.experience_years
         FROM users u
         INNER JOIN therapists t ON t.therapist_id = u.user_id
         WHERE u.role = :role AND u.is_active = 1'
    );
    $stmt->execute(['role' => 'THERAPIST']);

    $therapists = [];
    foreach ($stmt->fetchAll() as $row) {
        $id = (int) ($row['user_id'] ?? 0);
        if ($id <= 0) {
            continue;
        }
        $therapists[$id] = $row;
    }

    return $therapists;
}

function recommend_resolve_roles(array $therapists): array
{
    $map = [];

    foreach (recommend_role_keywords() as $role => $keyword) {
        foreach ($therapists as $id => $row) {
            $name = (string) ($row['name'] ?? '');
            if (mb_strpos($name, $keyword) !== false) {
                $map[$role] = (int) $id;
                break;
            }
        }
    }

    return $map;
}

function recommend_split_symptoms(?string $value): array
{
    $value = trim((string) $value);
    if ($value === '') {
        return [];
    }

    // الواجهة تخزّن الأعراض مفصولة بفاصلة عربية «،»؛ ندعم العادية احتياطاً.
    $parts = preg_split('/[،,]+/u', $value) ?: [];

    $clean = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
            $clean[] = $part;
        }
    }

    return $clean;
}

function recommend_text_has(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function recommend_array_has(array $haystack, array $needles): bool
{
    foreach ($haystack as $item) {
        foreach ($needles as $needle) {
            if ($needle !== '' && ($item === $needle || mb_strpos($item, $needle) !== false)) {
                return true;
            }
        }
    }

    return false;
}

function recommend_calculate_scores(array $survey): array
{
    $scores = [];
    foreach (array_keys(recommend_role_keywords()) as $role) {
        $scores[$role] = ['score' => 0, 'reasons' => []];
    }

    // دالة داخلية مختصرة لإضافة نقاط + سبب
    $add = static function (string $role, int $points, string $reason) use (&$scores): void {
        if (!isset($scores[$role])) {
            return;
        }
        $scores[$role]['score'] += $points;
        $scores[$role]['reasons'][] = $reason;
    };

    $treatmentType   = trim((string) ($survey['treatment_type'] ?? ''));
    $symptomsRaw     = (string) ($survey['symptoms'] ?? '');
    $repeatedRaw     = (string) ($survey['repeated_symptoms'] ?? '');
    $physicalDetails = (string) ($survey['physical_details'] ?? '');

    $symptoms      = recommend_split_symptoms($symptomsRaw);
    $repeated      = recommend_split_symptoms($repeatedRaw);
    $symptomsCount = count($symptoms);

    $allText = $treatmentType . ' ' . $symptomsRaw . ' ' . $repeatedRaw . ' ' . $physicalDetails;

    // نوع العلاج 

    // إرشاد والدي → زينب +5
    if (recommend_text_has($treatmentType, ['إرشاد والدي', 'ارشاد والدي'])) {
        $add(RECO_ROLE_ZAINAB, 5, 'مختصة بالإرشاد الوالدي الذي طلبته في الاستبيان.');
    }

    $wantsChild  = recommend_text_has($treatmentType, ['أطفال', 'اطفال', 'طفل']);
    $wantsSpeech = recommend_text_has($allText, ['نطق', 'تأتأة', 'تاتاة', 'تلعثم', 'لغة', 'لفظ', 'كلام']);

    if ($wantsSpeech) {
        $add(RECO_ROLE_NEROZ, 5, 'مختصة في السمع والنطق وعلاج صعوبات اللغة والتأتأة.');
    }
    if ($wantsChild) {
        $add(RECO_ROLE_ZAINAB, 5, 'لديها خبرة في التعامل مع الأطفال والإرشاد الوالدي.');
        if (!$wantsSpeech) {
            $add(RECO_ROLE_NEROZ, 3, 'متخصصة في تطوير اللغة والنطق لدى الأطفال.');
        }
    }


    //  عدد الأعراض

    if ($symptomsCount >= 6) {
        // عدد كبير من الأعراض → حالة معقّدة/متعددة الاضطرابات
        $add(RECO_ROLE_OMAR, 5, 'متخصص بالحالات المعقدة والاضطرابات المتعددة (اخترت ' . $symptomsCount . ' أعراض).');
        $add(RECO_ROLE_TASNEEM, 5, 'مناسبة للحالات المتوسطة والمتقدمة (اخترت ' . $symptomsCount . ' أعراض).');
    } elseif ($symptomsCount > 0) {
        // عدد أقل من 6 → حالة خفيفة/متوسطة
        $add(RECO_ROLE_HADEEL, 4, 'مناسبة للحالات الخفيفة والمتوسطة المطابقة لأعراضك.');
        $add(RECO_ROLE_ZAINAB, 4, 'مناسبة للحالات الخفيفة والمتوسطة مع خبرة في العلاج السلوكي المعرفي.');
    }

    // أعراض اجتماعية/عائلية → مها (مختصة نفسية اجتماعية وعلاقات أسرية)
    if (recommend_array_has($symptoms, ['صعوبة تكوين علاقات اجتماعية'])
        || recommend_array_has($symptoms, ['مشاكل عائلية مستمرة', 'مشاكل مع شخص من أفراد العائلة'])
    ) {
        $add(RECO_ROLE_MAHA, 5, 'مختصة نفسية اجتماعية ومناسبة للمشاكل الاجتماعية والعلاقات العائلية.');
    }

    // وساوس/نوبات هلع → عمر + تسنيم
    if (recommend_array_has($symptoms, ['وساوس مزعجة']) || recommend_array_has($symptoms, ['نوبات هلع'])) {
        $add(RECO_ROLE_OMAR, 4, 'متخصص في تقييم وعلاج الوساوس ونوبات الهلع.');
        $add(RECO_ROLE_TASNEEM, 4, 'تقدّم العلاج السلوكي المعرفي المناسب للوساوس ونوبات الهلع.');
    }

    // سلوك إدماني 

    if (recommend_array_has($repeated, ['سلوك إدماني'])
        || recommend_text_has($repeatedRaw, ['إدمان', 'ادمان', 'مخدرات', 'كحول'])
    ) {
        $add(RECO_ROLE_OMAR, 5, 'متخصص بالحالات المعقدة ومنها السلوك الإدماني.');
        $add(RECO_ROLE_TASNEEM, 5, 'لديها خبرة في الحالات المتقدمة ومنها السلوك الإدماني.');
    }

    return $scores;
}


function recommend_rank_and_select(array $scores, array $therapists, array $roleToId, int $limit = 3): array
{
    $ranked = [];

    foreach ($scores as $role => $data) {
        if ($data['score'] <= 0) {
            continue;
        }
        if (!isset($roleToId[$role])) {
            continue;
        }

        $therapistId = $roleToId[$role];
        if (!isset($therapists[$therapistId])) {
            continue;
        }

        $therapist       = $therapists[$therapistId];
        $experienceYears = (int) ($therapist['experience_years'] ?? 0);

        $ranked[] = [
            'id'               => $therapistId,
            'role'             => $role,
            'score'            => (int) $data['score'],
            'experience_years' => $experienceYears,
            'reasons'          => $data['reasons'],
            'name'             => trim((string) ($therapist['name'] ?? '')),
            'specialization'   => trim((string) ($therapist['specialization'] ?? '')),
            'certification'    => trim((string) ($therapist['certification'] ?? '')),
            'image'            => client_normalize_therapist_avatar((string) ($therapist['avatar'] ?? '')),
            'email'            => trim((string) ($therapist['email'] ?? '')),
            'experience_text'  => client_build_experience_text($experienceYears, (string) ($therapist['bio'] ?? '')),\
            'consult_price'    => '150 شيكل',
            'therapy_price'    => '120 شيكل',
        ];
    }

    usort($ranked, static function (array $a, array $b): int {
        if ($a['score'] !== $b['score']) {
            return $b['score'] <=> $a['score'];
        }
        return $b['experience_years'] <=> $a['experience_years'];
    });

    return array_slice($ranked, 0, $limit);
}

function recommend_for_user(int $userId, int $limit = 3): array
{
    $survey = recommend_get_user_survey($userId);

    if ($survey === null) {
        return ['minor' => false, 'has_survey' => false, 'age' => 0, 'recommendations' => []];
    }

    $age = (int) ($survey['age'] ?? 0);

    $isMinor = ($age > 0 && $age < 18);

    $scores      = recommend_calculate_scores($survey);
    $therapists  = recommend_load_therapists();
    $roleToId    = recommend_resolve_roles($therapists);
    $recommendations = recommend_rank_and_select($scores, $therapists, $roleToId, $limit);

    return [
        'minor'           => $isMinor,
        'has_survey'      => true,
        'age'             => $age,
        'recommendations' => $recommendations,
    ];
}
