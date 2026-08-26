<?php
declare(strict_types=1);

const WGS_PROJECT_TYPES = ['Website Diagnosis', 'New website', 'Website redesign', 'Brand + website', 'Landing page', 'Something more unusual'];
const WGS_BUDGETS = ['$400 audit', '$2.5–4k', '$5–8k', '$9–12k+', 'Not sure yet'];

function wgs_text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function wgs_normalize_text(mixed $value, int $limit): string
{
    $text = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', (string) $value) ?? '');
    return wgs_text_length($text) <= $limit ? $text : '';
}

function wgs_validate_lead_id(string $utmContent): string
{
    return preg_match('/^wgs-\d{3,6}$/i', $utmContent) === 1 ? strtolower($utmContent) : '';
}

function wgs_validate_enquiry(array $input): array
{
    $data = [
        'name' => wgs_normalize_text($input['name'] ?? '', 100),
        'email' => strtolower(wgs_normalize_text($input['email'] ?? '', 160)),
        'business' => wgs_normalize_text($input['business'] ?? '', 220),
        'project_type' => wgs_normalize_text($input['project_type'] ?? '', 100),
        'budget' => wgs_normalize_text($input['budget'] ?? '', 60),
        'message' => wgs_normalize_text($input['message'] ?? '', 4000),
    ];
    $valid = $data['name'] !== ''
        && filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false
        && in_array($data['project_type'], WGS_PROJECT_TYPES, true)
        && in_array($data['budget'], WGS_BUDGETS, true)
        && wgs_text_length($data['message']) >= 20;
    return [$valid, $data];
}

function wgs_is_obvious_spam(array $data): bool
{
    $combined = $data['business'] . ' ' . $data['message'];
    preg_match_all('~(?:https?://|www\.)\S+~i', $combined, $links);
    if (count($links[0]) > 4) return true;
    return preg_match('/\b(?:casino|viagra|payday loan|backlinks?|guest posts?|link building)\b/i', $combined) === 1
        && count($links[0]) > 1;
}

function wgs_verify_turnstile(string $token, string $secret, string $remoteIp, ?callable $request = null): bool
{
    if ($token === '' || $secret === '') return false;
    $request ??= static function (string $url, array $fields): string|false {
        $context = stream_context_create(['http' => [
            'method' => 'POST', 'timeout' => 8,
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($fields),
        ]]);
        return @file_get_contents($url, false, $context);
    };
    $response = $request('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'secret' => $secret, 'response' => $token, 'remoteip' => $remoteIp,
    ]);
    $decoded = is_string($response) ? json_decode($response, true) : null;
    return is_array($decoded) && ($decoded['success'] ?? false) === true;
}

function wgs_rate_limit(string $directory, string $key, int $now, int $limit = 5, int $window = 3600): bool
{
    if (!is_dir($directory) || !is_writable($directory)) return false;
    $path = $directory . '/rate-' . hash('sha256', $key) . '.json';
    $handle = @fopen($path, 'c+');
    if (!$handle || !flock($handle, LOCK_EX)) return false;
    $raw = stream_get_contents($handle);
    $times = is_string($raw) ? json_decode($raw, true) : [];
    $times = is_array($times) ? array_values(array_filter($times, static fn($time) => is_int($time) && $time > $now - $window)) : [];
    $allowed = count($times) < $limit;
    if ($allowed) $times[] = $now;
    ftruncate($handle, 0); rewind($handle); fwrite($handle, json_encode($times)); fflush($handle); flock($handle, LOCK_UN); fclose($handle);
    return $allowed;
}

function wgs_attribution(array $input): array
{
    $fields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'landing_path', 'initial_referrer'];
    $result = [];
    foreach ($fields as $field) $result[$field] = wgs_normalize_text($input[$field] ?? '', $field === 'initial_referrer' ? 500 : 200);
    $result['lead_id'] = wgs_validate_lead_id($result['utm_content']);
    return $result;
}

function wgs_mail_payload(array $data, array $attribution, string $submissionId, DateTimeImmutable $utc): array
{
    $sydney = $utc->setTimezone(new DateTimeZone('Australia/Sydney'));
    $lines = ['New Web Girl Studio project enquiry', '', 'Submission ID: ' . $submissionId,
        'Submitted UTC: ' . $utc->format(DateTimeInterface::ATOM), 'Submitted Australia/Sydney: ' . $sydney->format(DateTimeInterface::ATOM), '',
        'Name: ' . str_replace(["\r", "\n"], ' ', $data['name']), 'Email: ' . $data['email'],
        'Business / website: ' . ($data['business'] ?: 'Not supplied'), 'Project type: ' . $data['project_type'],
        'Investment range: ' . $data['budget'], '', 'Project:', $data['message'], '', 'Attribution:'];
    foreach ($attribution as $key => $value) $lines[] = $key . ': ' . ($value ?: 'Not supplied');
    return ['recipient' => 'hello@webgirl.studio', 'subject' => 'Web Girl Studio enquiry — ' . $data['project_type'] . ' [' . $submissionId . ']', 'body' => implode("\n", $lines)];
}
