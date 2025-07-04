<?php
session_start();

$username = 'charithjayalath8@gmail.com';
$password = 'yb@!35VQ';
$smsApiUrl = 'https://bsms.hutch.lk/api/sendsms';
$loginUrl = 'https://bsms.hutch.lk/api/login';

// === FUNCTION: Get Access Token ===
function getAccessToken($username, $password) {
    $postData = json_encode([
        'username' => $username,
        'password' => $password
    ]);

    $ch = curl_init($GLOBALS['loginUrl']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: */*',
            'X-API-VERSION: v1'
        ],
        CURLOPT_POSTFIELDS => $postData
    ]);
    $response = curl_exec($ch);
    if (!$response) {
        die("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['accessToken'] ?? false;
}

// === FUNCTION: Send SMS ===
function sendSms($accessToken, $campaignName, $mask, $number, $message) {
    $postData = json_encode([
        'campaignName' => $campaignName,
        'mask' => $mask,
        'numbers' => $number,
        'content' => $message,
        'deliveryReportRequest' => true
    ]);

    $ch = curl_init($GLOBALS['smsApiUrl']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: */*',
            'X-API-VERSION: v1',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_POSTFIELDS => $postData
    ]);
    $response = curl_exec($ch);
    if (!$response) {
        die("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);

    return $response;
}

// === FUNCTION: Format any input phone number to 94XXXXXXXXX ===
function formatPhoneNumberTo94($inputNumber) {
    // Remove all non-digit characters (spaces, +, -, etc.)
    $number = preg_replace('/\D/', '', $inputNumber);

    // If number starts with 0 (local format), replace with 94
    if (substr($number, 0, 1) === '0') {
        $number = '94' . substr($number, 1);
    }
    // If number starts with 7 or 1 digit (missing country code and leading zero), add 94
    else if (strlen($number) == 9 && substr($number, 0, 1) === '7') {
        $number = '94' . $number;
    }
    // If number already starts with 94 and length is 11, do nothing
    // Otherwise invalid length - return false
    if (strlen($number) !== 11 || substr($number, 0, 2) !== '94') {
        return false; // invalid format
    }
    return $number;
}

// === HANDLE FORM SUBMISSION ===
$result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneRaw = trim($_POST['number']);
    $mask = isset($_POST['mask']) ? trim($_POST['mask']) : 'Gomo'; // default mask
    $otp = rand(100000, 999999);
    $message = "Your SecureRec OTP is: $otp. Do not share it with anyone.";

    // Format the phone number
    $phone = formatPhoneNumberTo94($phoneRaw);

    if (!$phone) {
        $result = "❌ Invalid phone number format. Please enter a valid Sri Lankan phone number.";
    } else {
        $accessToken = getAccessToken($username, $password);
        if ($accessToken) {
            $smsResult = sendSms($accessToken, 'SecureRecOTP', $mask, $phone, $message);
            $_SESSION['otp'] = $otp;
            $_SESSION['phone'] = $phone;
            header("Location: verify_otp.php");
            exit();
        } else {
            $result = "❌ Failed to get access token. Please check API credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send OTP via Hutch</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #edf2f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .box {
            background-color: #fff;
            padding: 30px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }

        h2 {
            text-align: center;
            color: #2b6cb0;
            margin-bottom: 25px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #2d3748;
        }

        input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #3182ce;
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background: #2b6cb0;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #2c5282;
        }

        h3 {
            margin-top: 25px;
            font-size: 18px;
            color: #2d3748;
        }

        pre {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Send OTP via SMS</h2>
        <form method="POST">
            <label for="number">Phone Number (e.g., 94771234567)</label>
            <input type="text" id="number" name="number" placeholder="94771234567" required>
            <button type="submit">Send OTP</button>
        </form>

        <?php if (!empty($result)): ?>
            <h3>Result:</h3>
            <pre><?php echo htmlspecialchars($result); ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>
