<?php
// Simple form handler for the audit request form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Helper to safely get POST values
function post_value($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

function strip_newlines($value) {
    return str_replace(["\r", "\n"], '', $value);
}

$name = strip_tags(post_value('name'));
$business = strip_tags(post_value('business'));
$website = strip_tags(post_value('website'));
$email = filter_var(post_value('email'), FILTER_SANITIZE_EMAIL);
$note = strip_tags(post_value('note'));

$name = strip_newlines($name);
$business = strip_newlines($business);
$website = strip_newlines($website);
$email = strip_newlines($email);

if (
    !$name ||
    !$business ||
    !$website ||
    !$email ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !filter_var($website, FILTER_VALIDATE_URL)
) {
    header('Location: index.html?success=0');
    exit;
}

$to = 'liana.webdev@gmail.com';
$subject = "Website audit request from {$name}";

$body = "Name: {$name}\n";
$body .= "Business: {$business}\n";
$body .= "Website: {$website}\n";
$body .= "Email: {$email}\n\n";
$body .= "Note:\n{$note}\n";

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'webgirlstudio.com';
$from_domain = preg_replace('/[^a-z0-9.-]/i', '', $host);
$from_address = 'no-reply@' . $from_domain;

$headers = [];
$headers[] = 'From: Web Girl Studio <' . $from_address . '>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

header('Location: index.html?success=' . ($sent ? '1' : '0'));
exit;
?>
