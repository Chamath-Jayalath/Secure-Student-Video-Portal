<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $phone = trim($_POST['phone']);
  $password = $_POST['password'];

  // Prepare statement to prevent SQL injection
  $stmt = $conn->prepare("SELECT id, first_name, last_name, phone, password, role FROM users WHERE phone = ?");
  $stmt->bind_param("s", $phone);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    // Verify password using password_verify
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['first_name'] = $user['first_name'];
      $_SESSION['last_name'] = $user['last_name'];
      $_SESSION['phone'] = $user['phone'];
      $_SESSION['role'] = $user['role'];

      // Redirect based on role
      if ($user['role'] === 'admin') {
        header("Location: dashboard_admin.php");
      } else if ($user['role'] === 'student') {
        header("Location: dashboard_user.php");
      } else {
        $error = "Unknown user role.";
      }
      exit();
    } else {
      $error = "Invalid phone number or password.";
    }
  } else {
    $error = "Invalid phone number or password.";
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
  <style>
    /* Your existing CSS styles */
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background: #f0f2f5;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
      color: #333;
    }

    .navbar {
      background-color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
    }

    .logo img {
      height: 40px;
      margin-right: 10px;
    }

    .logo h1 {
      font-size: 1.5rem;
      color: #2b6cb0;
      user-select: none;
    }

    .nav-buttons a {
      text-decoration: none;
      display: inline-block;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 600;
      margin: 0.25rem;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-login {
      background-color: #fff;
      color: #2b6cb0;
      border: 2px solid #2b6cb0;
    }

    .btn-login:hover {
      background-color: #2b6cb0;
      color: white;
    }

    .btn-register {
      background-color: #2b6cb0;
      color: #fff;
      border: 2px solid #2b6cb0;
    }

    .btn-register:hover {
      background-color: #1e40af;
      border-color: #1e40af;
    }

    .main-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    .login-form {
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      position: relative;
    }

    .login-form h2 {
      margin-top: 0;
      color: #333;
      text-align: center;
      font-weight: 700;
      letter-spacing: 0.05em;
      user-select: none;
    }

    .login-form input[type="text"],
    .login-form input[type="password"],
    .login-form input[type="tel"] {
      width: 100%;
      padding: 12px 14px;
      margin-top: 15px;
      margin-bottom: 25px;
      border: 1.8px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.4s ease, box-shadow 0.4s ease;
      outline-offset: 2px;
      outline-color: transparent;
    }

    .login-form input[type="password"]:focus,
    .login-form input[type="text"]:focus,
    .login-form input[type="tel"]:focus {
      border-color: #1976d2;
      box-shadow: 0 0 8px rgba(25, 118, 210, 0.6);
      outline-color: #1976d2;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .login-form button {
      width: 100%;
      padding: 14px;
      background-color: #1976d2;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 17px;
      cursor: pointer;
      font-weight: 700;
      letter-spacing: 0.03em;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
    }

    .login-form button:hover {
      background-color: #1565c0;
      box-shadow: 0 4px 12px rgba(21, 101, 192, 0.6);
    }

    .error {
      color: #e53e3e;
      text-align: center;
      margin-bottom: 12px;
      font-weight: 600;
    }

    .login-form p {
      text-align: center;
      margin-top: 12px;
      font-size: 14px;
    }

    .login-form p a {
      color: #1976d2;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
      user-select: none;
    }

    .login-form p a:hover {
      color: #1565c0;
      text-decoration: underline;
    }

    @media (max-width: 600px) {
      .navbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .nav-buttons {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">
      <img src="logo.png" alt="Logo" />
      <!-- Optional: <h1>YourApp</h1> -->
    </div>
    <div class="nav-buttons">
      <a href="index.php" class="btn-login">Home</a>
      <a href="login.php" class="btn-login">Login</a>
      <a href="register.php" class="btn-register">Register</a>
    </div>
  </nav>

  <div class="main-container">
    <form method="POST" class="login-form" novalidate>
      <h2>Login</h2>
      <?php if (isset($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
      
      <input
        type="tel"
        name="phone"
        placeholder="Phone Number (e.g., 94771234567)"
        pattern="94[0-9]{9}"
        required
        autocomplete="tel"
      />
      
      <input
        type="password"
        name="password"
        placeholder="Password"
        required
        autocomplete="current-password"
      />
      
      <button type="submit">Login</button>
      <p><a href="forgot_password.php">Forgot Password?</a></p>
    </form>
  </div>
</body>
</html>
