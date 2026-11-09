<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
if (!is_post()) json_out(['ok' => false], 405);
csrf_check();

$email = strtolower(post('email'));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(['ok' => false, 'message' => 'Please enter a valid email address.'], 422);
if (!throttle('subscribe', 5, 300)) json_out(['ok' => false, 'message' => 'Too many attempts. Please try again later.'], 429);

if (val('SELECT id FROM subscribers WHERE email = ?', [$email])) {
    json_out(['ok' => true, 'message' => 'You are already on the list — thank you!']);
}
q('INSERT INTO subscribers (email) VALUES (?)', [$email]);
json_out(['ok' => true, 'message' => 'Welcome aboard. Watch your inbox for seasonal tips and offers.']);
