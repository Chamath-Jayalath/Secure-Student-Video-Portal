<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Check for admin with matching phone
    $stmt = $conn->prepare("SELECT id, password, role, first_name, last_name FROM users WHERE phone = ? AND role = 'admin'");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['first_name'] = $admin['first_name'];
            $_SESSION['last_name'] = $admin['last_name'];

            header("Location: dashboard_admin.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Admin not found or phone number invalid.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
    }
    .login-box h2 {
      margin-bottom: 20px;
      color: #3f51b5;
      text-align: center;
    }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }
    button {
      width: 100%;
      background: #3f51b5;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background: #303f9f;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Admin Login</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
      <input type="text" name="phone" placeholder="Admin Phone Number" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
