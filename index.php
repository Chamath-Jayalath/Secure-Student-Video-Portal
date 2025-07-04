<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recording Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: linear-gradient(to bottom right, #ebf8ff, #bee3f8);
      min-height: 100vh;
    }

   .navbar {
  background-color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

@media (max-width: 600px) {
  .navbar {
    flex-direction: column;
    align-items: flex-start;
  }
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
    }

    .nav-buttons a {
  text-decoration: none;
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
}

.btn-login {
  background-color: #fff;
  color: #2b6cb0;
  border: 2px solid #2b6cb0;
}

.btn-register {
  background-color: #2b6cb0;
  color: #fff;
}


    main {
      text-align: center;
      padding: 6rem 2rem;
    }

    main h2 {
      font-size: 2.5rem;
      color: #2c5282;
      margin-bottom: 1rem;
    }

    main p {
      color: #4a5568;
      font-size: 1.125rem;
      max-width: 600px;
      margin: 0 auto 2rem;
    }

    .action-buttons button {
      margin: 0 0.5rem;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 600;
    }

    .btn-primary {
      background-color: #2b6cb0;
      color: #fff;
    }

    .btn-secondary {
      background-color: #fff;
      color: #2b6cb0;
      border: 2px solid #2b6cb0;
    }
    
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">
      <img src="logo.png" alt="Logo" />
      <h1></h1>
    </div>
    <div class="nav-buttons">
      <a href="index.php" class="btn-login">Home</a>
  <a href="login.php" class="btn-login">Login</a>
  <a href="register.php" class="btn-register">Register</a>
</div>

  </nav>

  <main>
    <h2> Secure Video Recording Portal</h2>
    <p>Your secure platform for smarter video recording. Join us and experience the next level of privacy and productivity.</p>
    <div class="action-buttons">
      <button class="btn-primary">Get Started</button>
      <button class="btn-secondary">Learn More</button>
    </div>
  </main>
</body>
</html>
