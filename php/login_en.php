<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Both empty → show top error
    if ($email === '' && $password === '') {
        header("Location: ../en/register.php?error_top=" . urlencode("❌ Please enter email and password"));
        exit;
    }

    // Only email empty
    if ($email === '') {
        header("Location: ../en/register.php?error_email=" . urlencode("❌ Please enter your email"));
        exit;
    }

    // Only password empty
    if ($password === '') {
        header("Location: ../en/register.php?error_password=" . urlencode("❌ Please enter your password"));
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {

    // 🔹 Status check
    if ($user['status'] === 'pending') {
        $stmt->close();
        $conn->close();
        header("Location: ../en/register.php?error_top=" . urlencode("❌ Your account is pending approval. Please wait for admin approval."));
        exit;
    }
    elseif ($user['status'] === 'blocked') {
        $stmt->close();
        $conn->close();
        header("Location: ../en/register.php?error_top=" . urlencode("❌ Your account is blocked. Contact admin."));
        exit;
    }

    // 🔹 Only if approved
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    $stmt->close();
    $conn->close();
    header("Location: ../en/profile.php");
    exit;
    }   else {
            $stmt->close();
            $conn->close();
            header("Location: ../en/register.php?error_password=" . urlencode("❌ Incorrect password"));
            exit;
        }
    } else {
        $stmt->close();
        $conn->close();
        header("Location: ../en/register.php?error_email=" . urlencode("❌ Account not found"));
        exit;
    }
} else {
    header("Location: ../en/register.php?error_top=" . urlencode("❌ Invalid request"));
    exit;
}
