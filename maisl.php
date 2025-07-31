<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $name = strip_tags(trim($_POST["name"]));
    $name = str_replace(["\r", "\n"], " ", $name);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subjectInput = trim($_POST["subject"]);
    $number = trim($_POST["number"]);
    $message = trim($_POST["message"]);

    // Validate required fields
    if (empty($name) || empty($message) || empty($number) || empty($subjectInput) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Please complete the form and try again.";
        exit;
    }

    // Resend configuration
    $apiKey = 're_jdnoZMph_PbjrfQ6rB3sMkosJpt7SKW65'; // ✅ Replace with your actual API key
    $from = "onboarding@resend.dev"; // ✅ Must be verified in Resend dashboard
    $to = "office.mapleitfirm@gmail.com"; // ✅ Destination email

    $subject = "New contact from $subjectInput";
    $body = "Name: $name\n";
    $body .= "Subject: $subjectInput\n";
    $body .= "Email: $email\n";
    $body .= "Phone: $number\n\n";
    $body .= "Message:\n$message";

    // Send request to Resend
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "from" => "$name <$from>",
        "to" => [$to],
        "subject" => $subject,
        "text" => $body
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 202) {
        http_response_code(200);
        echo "Thank you! Your message has been sent.";
    } else {
        http_response_code(500);
        echo "Oops! Something went wrong with Resend.";
    }
} else {
    http_response_code(403);
    echo "Invalid request.";
}
?>
