<?php
// register.php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'farmer';

    if ($name === '' || $email === '' || $password === '') {
        die("Please fill all fields");
    }

    // check existing
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        die("Account with this email already exists");
    }
    $stmt->close();

    // insert new user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hash, $role);

    if ($stmt->execute()) {
        // set consistent session variables
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;

        $stmt->close();
        $conn->close();
        header("Location: ../en/profile.php");
        exit;
    } else {
        $err = $stmt->error;
        $stmt->close();
        $conn->close();
        die("Error creating account: " . $err);
    }
} else {
    die("Invalid request");
}
