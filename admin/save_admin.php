<?php
session_start();
require_once "../php/db.php";

if(!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'superadmin'){
    header("Location: login.php");
    exit();
}

$id = $_POST['id'] ?? null;
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'] ?? '';
$role = $_POST['role'];

if(empty($username) || empty($email) || empty($role)){
    $_SESSION['flash'] = "Please fill all required fields.";
    header("Location: admins.php");
    exit();
}

if($id){ // edit
    if(!empty($password)){
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $email, $hashed, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $role, $id);
    }
    $stmt->execute();
    $_SESSION['flash'] = "Admin updated successfully.";
} else { // add new
    $res = $conn->prepare("SELECT id FROM admins WHERE email=?");
    $res->bind_param("s", $email);
    $res->execute();
    $res->store_result();
    if($res->num_rows > 0){
        $_SESSION['flash'] = "Email already exists!";
        header("Location: admins.php");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $email, $hashed, $role);
    $stmt->execute();
    $_SESSION['flash'] = "Admin added successfully.";
}

header("Location: admins.php");
exit();
