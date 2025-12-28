<?php
// /admin/login.php
session_start();

// Agar admin already login hai → dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Flash messages
$error = $_GET['error'] ?? '';
$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login - Anndata</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* Basic Reset */
    * { box-sizing: border-box; margin:0; padding:0; }

    body {
      font-family: Arial, Helvetica, sans-serif;
      background: linear-gradient(135deg, #74ebd5, #acb6e5);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .wrap {
      width: 100%;
      max-width: 380px;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #2c3e50;
    }

    label {
      display: block;
      margin: 10px 0 6px;
      font-weight: 600;
      color: #34495e;
    }

    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }

    button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: #2980b9;
      color: #fff;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #1f669e;
    }

    .err {
      color: #b00020;
      margin: 10px 0;
      text-align: center;
    }

    .info {
      color: #006400;
      margin: 10px 0;
      text-align: center;
    }

    @media (max-width: 480px) {
      .wrap {
        padding: 20px;
        margin: 10px;
      }
      button { font-size: 15px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Admin Login</h2>

    <?php if ($error === 'invalid'): ?>
      <div class="err">❌ Invalid username/email or password.</div>
    <?php elseif ($error === 'server'): ?>
      <div class="err">❌ Server error. Try again.</div>
    <?php endif; ?>

    <?php if ($msg === 'loggedout'): ?>
      <div class="info">✅ You have been logged out.</div>
    <?php endif; ?>

    <form method="POST" action="../php/admin_auth.php">
      <input type="hidden" name="action" value="login">

      <label for="username">Username or Email</label>
      <input id="username" name="username" type="text" required placeholder="Username or email">

      <label for="password">Password</label>
      <input id="password" name="password" type="password" required placeholder="Password">

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
