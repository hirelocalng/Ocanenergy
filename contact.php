<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    exit('Please complete the required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Please provide a valid email address.');
}

$to = 'AkariOtu@ocanenergy.com.ng';
$subject = 'New enquiry from ' . $name;
$body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n";
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $body, $headers)) {
    http_response_code(200);
    echo 'Thanks — we’ll be in touch.';
    exit;
}

http_response_code(500);
echo 'Submission failed. Please try again.';
