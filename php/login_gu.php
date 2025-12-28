<?php
session_start();
require 'db.php';

$currentLang = 'gu'; // Gujarati

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' && $password === '') {
        header("Location: ../gu/register.php?error_top=" . urlencode("❌ ઈમેઇલ અને પાસવર્ડ દાખલ કરો"));
        exit;
    }
    if ($email === '') {
        header("Location: ../gu/register.php?error_email=" . urlencode("❌ ઈમેઇલ દાખલ કરો"));
        exit;
    }
    if ($password === '') {
        header("Location: ../gu/register.php?error_password=" . urlencode("❌ પાસવર્ડ દાખલ કરો"));
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
                header("Location: ../gu/register.php?error_top=" . urlencode("❌ તમારું અકાઉન્ટ માન્યતા માટે રાહ જોઈ રહ્યું છે. કૃપા કરી એડમિનની મંજૂરીની રાહ જુઓ."));
                exit;
            } elseif ($user['status'] === 'blocked') {
                $stmt->close(); $conn->close();
                header("Location: ../gu/register.php?error_top=" . urlencode("❌ તમારું અકાઉન્ટ બ્લોક થઈ ગયું છે. એડમિનનો સંપર્ક કરો."));
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['lang'] = $currentLang;

            $stmt->close(); $conn->close();
            header("Location: ../gu/profile.php");
            exit;

        } else {
            $stmt->close(); $conn->close();
            header("Location: ../gu/register.php?error_password=" . urlencode("❌ ખોટો પાસવર્ડ"));
            exit;
        }
    } else {
        $stmt->close(); $conn->close();
        header("Location: ../gu/register.php?error_email=" . urlencode("❌ અકાઉન્ટ મળી નથી"));
        exit;
    }
} else {
    header("Location: ../gu/register.php?error_top=" . urlencode("❌ અમાન્ય વિનંતી"));
    exit;
}
?>
