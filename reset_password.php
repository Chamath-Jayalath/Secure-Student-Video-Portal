<?php
session_start();
require 'config.php'; // 🔑 DB connection

if (!isset($_SESSION['phone'])) {
    header("Location: index.php"); // Redirect if no session
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword === $confirmPassword) {
        $phone = $_SESSION['phone'];

        // ❗ Legacy hash method (used in your registration)
        $hashedPassword = md5($newPassword);

        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE phone = ?");
        $stmt->bind_param('ss', $hashedPassword, $phone);

        if ($stmt->execute()) {
            $message = "✅ Password has been successfully reset. Redirecting to login...";
            session_destroy();
            header("refresh:3;url=login.php");
        } else {
            $error = "❌ Failed to update password. Please try again.";
        }

        $stmt->close();
    } else {
        $error = "❌ Passwords do not match.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
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

        .message {
            color: #2f855a;
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Reset Your Password</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
