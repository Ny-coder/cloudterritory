<?php
/**
 * Cloud Territory — Contact Form Handler
 * ----------------------------------------
 * Receives POST data from the website contact form and emails it to you.
 *
 * SETUP:
 * 1. Upload this file to your web host (e.g. public_html/contact-form-handler.php).
 * 2. Set $to_email below to the address you want submissions sent to.
 * 3. Set $from_email to an address on YOUR domain (e.g. noreply@cloudterritory.com.ng).
 *    Most shared hosts (cPanel etc.) reject/flag mail() calls where the "From"
 *    address isn't on the same domain as the server.
 * 4. Point your HTML form's action="" at this file's URL, method="POST".
 */

// ---- CONFIG ----
$to_email   = "niyi@cloudterritory.com.ng";      // <-- change to where you want to receive messages
$from_email = "info@cloudterritory.com.ng";  // <-- change to an address on your domain
$subject_prefix = "New inquiry from Cloud Territory website";
$redirect_success = "thank-you.html"; // page to redirect to after successful send
$redirect_error   = "error.html";     // page to redirect to if something goes wrong

// ---- ONLY ALLOW POST REQUESTS ----
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Method not allowed.");
}

// ---- HONEYPOT SPAM CHECK ----
// Add a hidden field named "website" to your HTML form (see note below).
// Real visitors won't fill it in; bots usually will.
if (!empty($_POST["website"])) {
    // Silently pretend success so bots don't learn the trick worked/failed.
    header("Location: $redirect_success");
    exit;
}

// ---- COLLECT & SANITIZE FIELDS ----
function clean($value) {
    return htmlspecialchars(trim($value ?? ""), ENT_QUOTES, "UTF-8");
}

$name    = clean($_POST["name"] ?? "");
$email   = clean($_POST["email"] ?? "");
$phone   = clean($_POST["phone"] ?? "");
$message = clean($_POST["message"] ?? "");

// ---- VALIDATION ----
$errors = [];

if ($name === "") {
    $errors[] = "Name is required.";
}
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email is required.";
}
if ($message === "") {
    $errors[] = "Message is required.";
}

if (!empty($errors)) {
    http_response_code(400);
    // For a nicer experience, redirect back with an error flag instead of dying.
    header("Location: $redirect_error");
    exit;
}

// ---- BUILD EMAIL ----
$subject = "$subject_prefix — $name";

$body  = "You have a new inquiry from the Cloud Territory website:\n\n";
$body .= "Name:    $name\n";
$body .= "Email:   $email\n";
$body .= "Phone:   " . ($phone !== "" ? $phone : "Not provided") . "\n\n";
$body .= "Message:\n$message\n";

// Headers: From must be your domain; Reply-To lets you hit "reply" and email the visitor directly.
$headers  = "From: Cloud Territory Website <$from_email>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ---- SEND ----
$sent = mail($to_email, $subject, $body, $headers);

if ($sent) {
    header("Location: $redirect_success");
    exit;
} else {
    header("Location: $redirect_error");
    exit;
}
