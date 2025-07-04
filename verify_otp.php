<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userOtp = trim($_POST['otp']);
    if ($userOtp == $_SESSION['otp']) {
        header("Location: reset_password.php"); // Go to password reset
        exit();
    } else {
        $error = "❌ Incorrect OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
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
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #2b6cb0;
            margin-bottom: 20px;
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

        .error {
            color: #e53e3e;
            background: #fff5f5;
            border: 1px solid #feb2b2;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Enter OTP</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="otp">OTP:</label>
            <input type="text" id="otp" name="otp" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
