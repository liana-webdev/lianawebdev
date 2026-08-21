<?php
declare(strict_types=1);
session_start();

function redirect_with_status(string $status, ?string $leadReceipt = null): never
{
    $query = ['status' => $status];
    if ($leadReceipt !== null) {
        $query['lead'] = $leadReceipt;
    }
    header('Location: ./?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) . '#contact', true, 303);
    exit;
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_status('invalid');
}

if (!empty($_POST['company_website'] ?? '')) {
    redirect_with_status('sent');
}

$sessionToken = (string) ($_SESSION['wgs_csrf'] ?? '');
$requestToken = (string) ($_POST['csrf'] ?? '');
if ($sessionToken === '' || !hash_equals($sessionToken, $requestToken)) {
    redirect_with_status('invalid');
}

$lastSubmission = (int) ($_SESSION['wgs_last_submission'] ?? 0);
if ($lastSubmission > 0 && time() - $lastSubmission < 20) {
    redirect_with_status('invalid');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$business = trim((string) ($_POST['business'] ?? ''));
$projectType = trim((string) ($_POST['project_type'] ?? ''));
$budget = trim((string) ($_POST['budget'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$valid =
    $name !== '' &&
    text_length($name) <= 100 &&
    filter_var($email, FILTER_VALIDATE_EMAIL) !== false &&
    text_length($email) <= 160 &&
    text_length($business) <= 220 &&
    $projectType !== '' &&
    text_length($projectType) <= 100 &&
    $budget !== '' &&
    text_length($budget) <= 60 &&
    text_length($message) >= 20 &&
    text_length($message) <= 4000;

if (!$valid) {
    redirect_with_status('invalid');
}

$cleanName = preg_replace('/[\r\n]+/', ' ', $name) ?: 'Website lead';
$cleanEmail = str_replace(["\r", "\n"], '', $email);
$recipient = getenv('WGS_RECIPIENT_EMAIL') ?: 'liana@webgirl.studio';
$subject = 'Web Girl Studio enquiry — ' . $projectType;
$body = implode("\n", [
    'New Web Girl Studio project enquiry',
    '',
    'Name: ' . $cleanName,
    'Email: ' . $cleanEmail,
    'Business / website: ' . ($business !== '' ? $business : 'Not supplied'),
    'Project type: ' . $projectType,
    'Investment range: ' . $budget,
    '',
    'Project:',
    $message,
    '',
    'Submitted: ' . gmdate('c'),
]);

$host = preg_replace('/[^a-z0-9.-]/i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$fromDomain = $host !== '' && $host !== 'localhost' ? $host : 'example.com';
$headers = [
    'From: Web Girl Studio Website <website@' . $fromDomain . '>',
    'Reply-To: ' . $cleanName . ' <' . $cleanEmail . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . PHP_VERSION,
];

$testMode = getenv('WGS_TEST_MODE') === '1';
$mailSent = $testMode || @mail($recipient, $subject, $body, implode("\r\n", $headers));
$_SESSION['wgs_last_submission'] = time();
$_SESSION['wgs_csrf'] = bin2hex(random_bytes(24));

if ($mailSent) {
    $leadReceipt = bin2hex(random_bytes(16));
    $_SESSION['wgs_lead_receipt'] = $leadReceipt;
    redirect_with_status('sent', $leadReceipt);
}

$storageDirectory = __DIR__ . '/storage';
$saved = is_dir($storageDirectory) &&
    is_writable($storageDirectory) &&
    file_put_contents(
        $storageDirectory . '/enquiries.jsonl',
        json_encode([
            'submitted_at' => gmdate('c'),
            'name' => $cleanName,
            'email' => $cleanEmail,
            'business' => $business,
            'project_type' => $projectType,
            'budget' => $budget,
            'message' => $message,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    ) !== false;

if ($saved) {
    $leadReceipt = bin2hex(random_bytes(16));
    $_SESSION['wgs_lead_receipt'] = $leadReceipt;
    redirect_with_status('saved', $leadReceipt);
}

redirect_with_status('error');