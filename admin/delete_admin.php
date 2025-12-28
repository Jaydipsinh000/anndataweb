<?php
session_start();
require_once "../php/db.php";

if(!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'superadmin'){
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if($id){
    $stmt = $conn->prepare("DELETE FROM admins WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['flash'] = "Admin deleted successfully.";
}

header("Location: admins.php");
exit();
