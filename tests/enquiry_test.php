<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib/enquiry.php';
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$valid = ['name'=>'QA Lead','email'=>'qa@example.com','business'=>'https://example.com','project_type'=>'Website Diagnosis','budget'=>'$400 audit','message'=>'A legitimate project message long enough.'];
[$ok, $data] = wgs_validate_enquiry($valid); check($ok, 'valid schema rejected');
$bad = $valid; $bad['email'] = 'bad'; [$ok] = wgs_validate_enquiry($bad); check(!$ok, 'invalid email accepted');
check(!wgs_is_obvious_spam($data), 'business URL rejected');
$spam = $data; $spam['message'] = 'casino backlinks http://a.test http://b.test'; check(wgs_is_obvious_spam($spam), 'link farm accepted');
check(wgs_verify_turnstile('token','secret','127.0.0.1', fn()=>'{"success":true}'), 'Turnstile success rejected');
check(!wgs_verify_turnstile('token','secret','127.0.0.1', fn()=>'{"success":false}'), 'Turnstile failure accepted');
$dir = sys_get_temp_dir() . '/wgs-rate-' . bin2hex(random_bytes(4)); mkdir($dir);
check(wgs_rate_limit($dir, 'key', 100, 2, 60) && wgs_rate_limit($dir, 'key', 101, 2, 60) && !wgs_rate_limit($dir, 'key', 102, 2, 60), 'rate limit failed');
array_map('unlink', glob($dir.'/*')); rmdir($dir);
$attr = wgs_attribution(['utm_source'=>'cold_email','utm_content'=>'wgs-001','landing_path'=>'/?x']);
check($attr['lead_id'] === 'wgs-001', 'lead ID not derived');
$mail = wgs_mail_payload($data, $attr, 'WGS-TEST', new DateTimeImmutable('2026-08-26T00:00:00Z'));
check($mail['recipient'] === 'hello@webgirl.studio' && str_contains($mail['body'], 'Australia/Sydney: 2026-08-26T10:00:00+10:00'), 'mail payload incorrect');
echo "Enquiry tests passed.\n";
