<?php
session_start();
include "config.php";

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and trim inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $grade = $_POST['grade'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = "student";

    // Server-side validation
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($grade) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!preg_match('/^94\d{9}$/', $phone)) {
        $error = "Phone number must be in the format 94771234567.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if phone exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Phone number already registered.";
        } else {
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (first_name, last_name, phone, grade, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssss", $first_name, $last_name, $phone, $grade, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Error creating user: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
  <style>
    /* === Your original CSS === */
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

    .register-form {
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      position: relative;
    }

    .register-form h2 {
      margin-top: 0;
      color: #333;
      text-align: center;
      font-weight: 700;
      letter-spacing: 0.05em;
      user-select: none;
    }

    .register-form input[type="text"],
    .register-form input[type="password"],
    .register-form input[type="tel"],
    .register-form select {
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

    .register-form input[type="text"]:focus,
    .register-form input[type="password"]:focus,
    .register-form input[type="tel"]:focus,
    .register-form select:focus {
      border-color: #1976d2;
      box-shadow: 0 0 8px rgba(25, 118, 210, 0.6);
      outline-color: #1976d2;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .register-form button {
      background-color: #2196F3;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 8px;
      font-size: 17px;
      cursor: pointer;
      font-weight: 700;
      letter-spacing: 0.03em;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
      margin-top: 10px;
    }

    .register-form button:hover {
      background-color: #1976d2;
      box-shadow: 0 4px 12px rgba(25, 118, 210, 0.6);
    }

    .error {
      color: #e53e3e;
      text-align: center;
      margin-bottom: 12px;
      font-weight: 600;
    }

    /* Multi-step form styles */
    .tab {
      display: none;
    }

    .step {
      height: 15px;
      width: 15px;
      margin: 0 5px;
      background-color: #bbbbbb;
      border-radius: 50%;
      display: inline-block;
      opacity: 0.5;
    }

    .step.active {
      opacity: 1;
      background-color: #2196F3;
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
    <form id="regForm" method="POST" class="register-form" novalidate>
      <h2>Register</h2>

      <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
      <p id="errorMsg" class="error"></p>

      <!-- Step 1 -->
      <div class="tab">
        <input
          type="text"
          name="first_name"
          placeholder="First Name"
          required
          value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
        />
        <input
          type="text"
          name="last_name"
          placeholder="Last Name"
          required
          value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
        />
      </div>

      <!-- Step 2 -->
      <div class="tab">
        <input
          type="tel"
          name="phone"
          placeholder="Phone Number (e.g., 94771234567)"
          pattern="94[0-9]{9}"
          required
          value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
        />
        <select name="grade" required>
          <option value="">Select Grade</option>
          <?php
            $grades = ['6', '7', '8', '9', '10', '11'];
            foreach ($grades as $g) {
              $selected = (isset($_POST['grade']) && $_POST['grade'] == $g) ? "selected" : "";
              $label = $g == '11' ? "Grade 11 (O/L)" : "Grade $g";
              echo "<option value=\"$g\" $selected>$label</option>";
            }
          ?>
        </select>
      </div>

      <!-- Step 3 -->
      <div class="tab">
        <input
          type="password"
          name="password"
          placeholder="Password"
          required
        />
        <input
          type="password"
          name="confirm_password"
          placeholder="Confirm Password"
          required
        />
      </div>

      <div style="overflow:auto;">
        <div style="float:right;">
          <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
          <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
        </div>
      </div>

      <div style="text-align:center;margin-top:20px;">
        <span class="step"></span>
        <span class="step"></span>
        <span class="step"></span>
      </div>
    </form>
  </div>

<script>
  var currentTab = 0; // Current tab is set to first step
  showTab(currentTab); // Display it

  function showTab(n) {
    var tabs = document.getElementsByClassName("tab");
    tabs[n].style.display = "block";

    // Hide Prev button on first tab
    document.getElementById("prevBtn").style.display = n == 0 ? "none" : "inline";

    // Change Next to Submit on last tab
    document.getElementById("nextBtn").innerHTML = (n == (tabs.length - 1)) ? "Submit" : "Next";

    fixStepIndicator(n);
  }

  function nextPrev(n) {
    var tabs = document.getElementsByClassName("tab");
    var errorMsg = document.getElementById("errorMsg");

    // Validate current tab inputs before moving on
    var inputs = tabs[currentTab].querySelectorAll("input, select");
    for (var i = 0; i < inputs.length; i++) {
      if (!inputs[i].checkValidity()) {
        errorMsg.innerText = inputs[i].validationMessage;
        inputs[i].focus();
        return false;
      }
    }

    // Additional password confirmation check on last step
    if (currentTab == tabs.length - 1 && n == 1) {
      var pwd = document.querySelector("input[name='password']").value;
      var cpwd = document.querySelector("input[name='confirm_password']").value;
      if (pwd !== cpwd) {
        errorMsg.innerText = "Passwords do not match.";
        return false;
      }
    }

    errorMsg.innerText = "";

    // Hide current tab
    tabs[currentTab].style.display = "none";

    // Change current tab index
    currentTab = currentTab + n;

    // If at end, submit form
    if (currentTab >= tabs.length) {
      document.getElementById("regForm").submit();
      return false;
    }

    // Show new tab
    showTab(currentTab);
  }

  function fixStepIndicator(n) {
    var steps = document.getElementsByClassName("step");
    for (var i = 0; i < steps.length; i++) {
      steps[i].className = steps[i].className.replace(" active", "");
    }
    steps[n].className += " active";
  }
</script>
</body>
</html>
