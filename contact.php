<?php
header('Content-Type: application/json; charset=UTF-8');

function sendJsonResponse($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ]);
    exit;
}

function logContactFailure($message, $details = '') {
    $timestamp = date('c');
    $entry = $timestamp . ' - ' . $message;
    if ($details !== '') {
        $entry .= ' - ' . $details;
    }
    error_log($entry . PHP_EOL, 3, __DIR__ . '/contact-errors.log');
}

function appendMailLog($subject, $body, $headers, $to) {
    $timestamp = date('c');
    $entry = "[$timestamp]\nTo: $to\nSubject: $subject\nHeaders:\n$headers\nBody:\n$body\n---\n";
    file_put_contents(__DIR__ . '/mail_log.txt', $entry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logContactFailure('Method not allowed.');
    sendJsonResponse(false, 'Method not allowed.', 405);
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    logContactFailure('Missing required fields.', 'name=' . $name . '; email=' . $email . '; message=' . $message);
    sendJsonResponse(false, 'Please complete the required fields.', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logContactFailure('Invalid email address.', $email);
    sendJsonResponse(false, 'Please provide a valid email address.', 400);
}

$to = 'AkariOtu@ocanenergy.com.ng';
$fromAddress = 'info@ocanenergy.com.ng';
$subject = 'New enquiry from ' . $name;
$body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n";
$headers = "From: OCAN Energy <{$fromAddress}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$attemptedMail = [
    'to' => $to,
    'subject' => $subject,
    'body' => $body,
    'headers' => $headers,
];

$sent = mail($to, $subject, $body, $headers);
if ($sent) {
    sendJsonResponse(true, 'Thanks — we’ll be in touch.');
}

appendMailLog($subject, $body, $headers, $to);
$reason = 'mail() returned false';
logContactFailure('mail() failed.', 'to=' . $to . '; from=' . $fromAddress . '; email=' . $email . '; reason=' . $reason);
sendJsonResponse(false, 'Mail failed: ' . $reason, 500);
