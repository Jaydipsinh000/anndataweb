<?php
session_start();
require 'db.php';

$currentLang = 'hi'; // Hindi

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' && $password === '') {
        header("Location: ../hi/register.php?error_top=" . urlencode("❌ कृपया ईमेल और पासवर्ड दर्ज करें"));
        exit;
    }
    if ($email === '') {
        header("Location: ../hi/register.php?error_email=" . urlencode("❌ कृपया अपना ईमेल दर्ज करें"));
        exit;
    }
    if ($password === '') {
        header("Location: ../hi/register.php?error_password=" . urlencode("❌ कृपया पासवर्ड दर्ज करें"));
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, role, password, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {

            if ($user['status'] === 'pending') {
                $stmt->close(); $conn->close();
                header("Location: ../hi/register.php?error_top=" . urlencode("❌ आपका अकाउंट अनुमोदन के लिए प्रतीक्षारत है। कृपया एडमिन की मंजूरी का इंतजार करें।"));
                exit;
            } elseif ($user['status'] === 'blocked') {
                $stmt->close(); $conn->close();
                header("Location: ../hi/register.php?error_top=" . urlencode("❌ आपका अकाउंट ब्लॉक कर दिया गया है। एडमिन से संपर्क करें।"));
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['lang'] = $currentLang;

            $stmt->close(); $conn->close();
            header("Location: ../hi/profile.php");
            exit;

        } else {
            $stmt->close(); $conn->close();
            header("Location: ../hi/register.php?error_password=" . urlencode("❌ पासवर्ड गलत है"));
            exit;
        }
    } else {
        $stmt->close(); $conn->close();
        header("Location: ../hi/register.php?error_email=" . urlencode("❌ अकाउंट नहीं मिला"));
        exit;
    }
} else {
    header("Location: ../hi/register.php?error_top=" . urlencode("❌ अमान्य अनुरोध"));
    exit;
}
?>
