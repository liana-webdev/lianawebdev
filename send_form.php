<?php
// Simple form handler for the audit request form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Helper to safely get POST values
function post($key){
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$name = strip_tags(post('name'));
$business = strip_tags(post('business'));
$website = strip_tags(post('website'));
$email = filter_var(post('email'), FILTER_SANITIZE_EMAIL);
$note = strip_tags(post('note'));

if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

$headers = [];
$headers[] = 'From: Web Girl Studio <no-reply@webgirlstudio.local>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if ($sent) {
    header('Location: index.html?success=1');
} else {
    header('Location: index.html?success=0');
}
exit;

?>
