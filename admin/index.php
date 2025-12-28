<?php
session_start();

// Agar admin already login hai
if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit();
} else {
    // Nahi login hai to login page
    header("Location: login.php");
    exit();
}
