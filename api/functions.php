<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($config['mail']['password']) {
    respond(['email_password' => $config['mail']['password']], 200);
    exit;
}else {
    respond(['error' => 'Email password not set'], 500);
    exit;
}

function isValidEmail($email) {
    // 1️⃣ Check email format using PHP's built-in filter
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false; // Invalid format
    }

    return true; // Email is valid
}
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1"); // Fast check
    $stmt->execute([$email]);
    return $stmt->fetchColumn() ? true : false;
}
function phoneExists($pdo, $phone) {
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE phone = ? LIMIT 1"); // Fast check
    $stmt->execute([$phone]);
    return $stmt->fetchColumn() ? true : false;
}
function isAccountVerified($pdo, $phone) {
    $stmt = $pdo->prepare("SELECT is_verified FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $verified = $stmt->fetchColumn();

    return $verified === 1; // Returns true if verified, false otherwise
}

function sanitizeInput($input) {
    $input = strip_tags($input); // Remove all HTML and PHP tags
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // Convert special characters to prevent XSS
    return $input;
}
function outputData($status, $message){
    return json_encode(["status" =>  $status, "message" => $message]);
}
function generateUUID($figure) {
    return bin2hex(random_bytes($figure)); // 32-character unique ID
}

function generateTimedToken($email, $expiryTimeInSeconds) {
    global $config;

    $secretKey = $config['secret']['SECRET_KEY'];
    $expiresAt = time() + $expiryTimeInSeconds;

    // Create the data array (email and expiration time)
    $data = json_encode([
        "email" => $email,
        "expires_at" => $expiresAt
    ]);

    // Generate the HMAC signature
    $signature = hash_hmac('sha512', $data, $secretKey, true);

    // Encode the data and signature separately in base64 and join with a dot
    $encodedData = base64_encode($data);
    $encodedSignature = base64_encode($signature);

    // Return the combined token
    return $encodedData . '.' . $encodedSignature;
}

function getEmailFromToken($token) {
    global $config;

    $secretKey = $config['secret']['SECRET_KEY'];

    // Split the token into data and signature
    $parts = explode('.', $token);
    if (count($parts) !== 2) return "Invalid token structure"; // Invalid token format

    list($encodedData, $encodedSignature) = $parts;

    // Decode the data and the signature
    $data = base64_decode($encodedData);
    $providedSignature = base64_decode($encodedSignature);

  

    if (!$data || !$providedSignature) return "Base64 decode error"; // Failed to decode parts

    // Validate the signature (HMAC SHA-512)
    /*$expectedSignature = hash_hmac('sha512', $data, $secretKey, true);

    // Debugging: Output expected signature for comparison
    echo "Expected Signature: " . bin2hex($expectedSignature) . "\n"; // Log expected signature

    if (!hash_equals($expectedSignature, $providedSignature)) return "Signature mismatch"; // Invalid signature
    */

    // Decode the JSON payload
    $payload = json_decode($data, true);
    if (!is_array($payload) || !isset($payload['email'], $payload['expires_at'])) return "Invalid payload"; // Payload is invalid

    // Check if the token has expired
    if (time() > $payload['expires_at']) return; // Token has expired

    return $payload['email']; // Return the email if valid
}

function emailVerifyMessage($imageLink, $message){
    return [
        "image-link" => $imageLink,
        "message" => $message
    ];
}

function sendHTMLEmail($toEmail, $toName, $verificationLink, $myTemplate) {
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
    require __DIR__ . '/PHPMailer/src/Exception.php';
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com"; // SMTP server (e.g., hirepurchase.ng)
        $mail->SMTPAuth = true;
        $mail->Username = "support@trendsaf.co"; // SMTP Username
        $mail->Password = $config['mail']['password']; // SMTP Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption
        $mail->Port = 587; // SMTP Port (Gmail: 587, Outlook: 587, SSL: 465)

        // Sender and Recipient
        $mail->setFrom('support@trendsaf.co', 'Trendsaf BaseFood');
        $mail->addAddress($toEmail, $toName);
        #$mail->addReplyTo('admin@hirepurchase.ng', 'Support');

        // Load HTML Template
        $htmlTemplate = file_get_contents($myTemplate);
        $htmlTemplate = str_replace('{{name}}', $toName, $htmlTemplate);
        $htmlTemplate = str_replace('{{link}}', $verificationLink, $htmlTemplate);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Email Verification";
        $mail->Body = $htmlTemplate;
        $mail->AltBody = "Hello $toName, please verify your email by clicking this: $verificationLink"; // Fallback for text-only clients
        $mail->SMTPDebug = 3; // Or 3 for more details
        $mail->Debugoutput = 'error_log'; // Sends debug info to PHP error log
        // Send Email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return "Error: {$mail->ErrorInfo}";
    }
}

function generateAccessToken($userId, $secret, $expiresIn = 900) {
    $issuedAt = time();
    $payload = [
        'iss' => 'trendsaf-api',
        'iat' => $issuedAt,
        'exp' => $issuedAt + $expiresIn,
        'sub' => $userId
    ];
    return JWT::encode($payload, $secret, 'HS256');
}
function generateRefreshToken() {
    return bin2hex(random_bytes(64)); // 128 chars hex string
}

/*function investmentEmail($toEmail, $toName, $invoice_number, $product_name, $investment_amount, $expected_returns, $start_date, $one_year_later, $payment_ref, $myTemplate) {
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
    require __DIR__ . '/PHPMailer/src/Exception.php';
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'hirepurchase.ng'; // SMTP server (e.g., hirepurchase.ng)
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@hirepurchase.ng'; // SMTP Username
        $mail->Password = HIRE_EMAIL_PASSWORD; // SMTP Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption
        $mail->Port = 587; // SMTP Port (Gmail: 587, Outlook: 587, SSL: 465)

        // Sender and Recipient
        $mail->setFrom('admin@hirepurchase.ng', 'Hire Purchase Investments');
        $mail->addAddress($toEmail, $toName);
        #$mail->addReplyTo('admin@hirepurchase.ng', 'Support');

        // Load HTML Template
        $htmlTemplate = file_get_contents($myTemplate);
        $htmlTemplate = str_replace('{{name}}', $toName, $htmlTemplate);
        $htmlTemplate = str_replace('{{invoice_number}}', $invoice_number, $htmlTemplate);
        $htmlTemplate = str_replace('{{product_name}}', $product_name, $htmlTemplate);
        $htmlTemplate = str_replace('{{investment_amount}}', $investment_amount, $htmlTemplate);
        $htmlTemplate = str_replace('{{expected_returns}}', $expected_returns, $htmlTemplate);
        $htmlTemplate = str_replace('{{start_date}}', $start_date, $htmlTemplate);
        $htmlTemplate = str_replace('{{one_year_later}}', $one_year_later, $htmlTemplate);
        $htmlTemplate = str_replace('{{payment_ref}}', $payment_ref, $htmlTemplate);


        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Investment Message";
        $mail->Body = $htmlTemplate;
        #$mail->AltBody = "Hello $toName, please verify your email by clicking this: $verificationLink"; // Fallback for text-only clients

        // Send Email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return "Error: {$mail->ErrorInfo}";
    }
}

function passwordEmail($toEmail, $reset_code, $expiresAt,$myTemplate) {
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
    require __DIR__ . '/PHPMailer/src/Exception.php';
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'hirepurchase.ng'; // SMTP server (e.g., hirepurchase.ng)
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@hirepurchase.ng'; // SMTP Username
        $mail->Password = HIRE_EMAIL_PASSWORD; // SMTP Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption
        $mail->Port = 587; // SMTP Port (Gmail: 587, Outlook: 587, SSL: 465)

        // Sender and Recipient
        $mail->setFrom('admin@hirepurchase.ng', 'Password Reset');
        $mail->addAddress($toEmail);
        #$mail->addReplyTo('admin@hirepurchase.ng', 'Support');

        // Load HTML Template
        $htmlTemplate = file_get_contents($myTemplate);
        $htmlTemplate = str_replace('{{reset_code}}', $reset_code, $htmlTemplate);
        $htmlTemplate = str_replace('{{expiresAt}}', $expiresAt, $htmlTemplate);


        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Hire Purchase Password Reset";
        $mail->Body = $htmlTemplate;
        #$mail->AltBody = "Hello $toName, please verify your email by clicking this: $verificationLink"; // Fallback for text-only clients

        // Send Email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return "Error: {$mail->ErrorInfo}";
    }
}

function resetPasswordEmail($toEmail, $myTemplate) {
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
    require __DIR__ . '/PHPMailer/src/Exception.php';
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'hirepurchase.ng'; // SMTP server (e.g., hirepurchase.ng)
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@hirepurchase.ng'; // SMTP Username
        $mail->Password = HIRE_EMAIL_PASSWORD; // SMTP Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption
        $mail->Port = 587; // SMTP Port (Gmail: 587, Outlook: 587, SSL: 465)

        // Sender and Recipient
        $mail->setFrom('admin@hirepurchase.ng', 'Successful Password Reset');
        $mail->addAddress($toEmail);
        #$mail->addReplyTo('admin@hirepurchase.ng', 'Support');

        // Load HTML Template
        $htmlTemplate = file_get_contents($myTemplate);
        $htmlTemplate = str_replace('{{email}}', $toEmail, $htmlTemplate);


        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Hire Purchase Password Reset Successful";
        $mail->Body = $htmlTemplate;

        // Send Email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return "Error: {$mail->ErrorInfo}";
    }
}*/

