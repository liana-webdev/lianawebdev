<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/lib/enquiry.php';

function redirect_with_status(string $status, ?string $receipt = null): never
{
    $query = ['status' => $status]; if ($receipt) $query['lead'] = $receipt;
    header('Location: ./?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) . '#contact', true, 303); exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') redirect_with_status('invalid');
if (!empty($_POST['company_website'] ?? '')) redirect_with_status('invalid');

$csrf = (string) ($_SESSION['wgs_csrf'] ?? '');
if ($csrf === '' || !hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) redirect_with_status('invalid');
$started = (int) ($_POST['form_started'] ?? 0);
if ($started < 1 || !hash_equals((string) ($_SESSION['wgs_form_started'] ?? ''), (string) $started) || time() - $started < 3 || time() - $started > 86400) redirect_with_status('invalid');

$testMode = getenv('WGS_TEST_MODE') === '1';
$turnstileOk = $testMode || wgs_verify_turnstile((string) ($_POST['cf-turnstile-response'] ?? ''), (string) getenv('WGS_TURNSTILE_SECRET_KEY'), (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
if (!$turnstileOk) redirect_with_status('invalid');

[$valid, $data] = wgs_validate_enquiry($_POST);
if (!$valid || wgs_is_obvious_spam($data)) redirect_with_status('invalid');
$rateKey = ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '|' . $data['email'];
if (!wgs_rate_limit(__DIR__ . '/storage', $rateKey, time())) redirect_with_status('invalid');

$attribution = wgs_attribution($_POST);
$submissionId = 'WGS-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));
$payload = wgs_mail_payload($data, $attribution, $submissionId, new DateTimeImmutable('now', new DateTimeZone('UTC')));
$fromEmail = getenv('WGS_FORM_FROM_EMAIL') ?: 'website@webgirl.studio';
if (filter_var($fromEmail, FILTER_VALIDATE_EMAIL) === false || !str_ends_with(strtolower($fromEmail), '@webgirl.studio')) redirect_with_status('error');
$headers = ['From: Liana | Web Girl Studio <' . $fromEmail . '>', 'Reply-To: ' . str_replace(["\r", "\n"], '', $data['email']), 'MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8'];
$sent = $testMode || @mail($payload['recipient'], $payload['subject'], $payload['body'], implode("\r\n", $headers), '-f' . $fromEmail);
if (!$sent) redirect_with_status('error');

$_SESSION['wgs_last_lead_context'] = ['project_type' => $data['project_type'], 'budget' => $data['budget'], 'lead_source' => $attribution['utm_source'], 'lead_id' => $attribution['lead_id']];
$receipt = bin2hex(random_bytes(16)); $_SESSION['wgs_lead_receipt'] = $receipt;
$_SESSION['wgs_csrf'] = bin2hex(random_bytes(24)); unset($_SESSION['wgs_form_started']);
redirect_with_status('sent', $receipt);
