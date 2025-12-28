<?php
session_start();
require_once "../php/db.php";

if(!isset($_SESSION['admin_id'])) { echo "Unauthorized"; exit(); }

$tool_id = intval($_POST['tool_id']);
$rent = floatval($_POST['rent']);

$stmt = $conn->prepare("UPDATE tools SET rent=? WHERE id=?");
$stmt->bind_param("di",$rent,$tool_id);
if($stmt->execute()){
    echo "✅ Rent updated successfully";
} else {
    echo "❌ Failed to update rent";
}
$stmt->close();
?>
