<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: register.php");
    exit();
}

require_once "../php/db.php";

$user_id = $_SESSION['user_id'];

$query = "SELECT status, tool_limit FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$stmt->close();

$user_status = $user_data['status'];
$tool_limit = intval($user_data['tool_limit']);

if($user_status == 'pending'){
    $_SESSION['error'] = "❌ आपका खाता एडमिन की मंज़ूरी का इंतज़ार कर रहा है। आप अभी उपकरण नहीं जोड़ सकते।";
} elseif($user_status == 'blocked'){
    $_SESSION['error'] = "❌ आपका खाता ब्लॉक कर दिया गया है। कृपया एडमिन से संपर्क करें।";
}

if(isset($_POST['submit'])){
    if($user_status == 'approved'){
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM tools WHERE user_id = ?");
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        $existing_tools = intval($res_count->fetch_assoc()['total']);
        $stmt_count->close();

        if($existing_tools >= $tool_limit){
            $_SESSION['error'] = "❌ आपने अपनी उपकरण सीमा ($tool_limit) पूरी कर ली है। अधिक जोड़ने के लिए एडमिन की मंज़ूरी का इंतज़ार करें।";
        } else {
            $tool_name = $_POST['tool_name'];
            $stmt = $conn->prepare("INSERT INTO tools (user_id, tool_name, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("is", $user_id, $tool_name);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "❌ आपका खाता मंज़ूर नहीं है। आप उपकरण नहीं जोड़ सकते।";
    }
    header("Location: tools.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, tool_name, status, created_at FROM tools WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tools = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>मेरे उपकरण | अन्नदाता</title>
<link rel="stylesheet" href="../styles.css">
<style>
body { font-family:'Segoe UI', sans-serif; background:#f4f6f9; margin:0; padding:0; }
.container { max-width:950px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.1); }
h2 { text-align:center; margin-bottom:20px; color:#2c3e50; }
form select, form button { width:100%; padding:12px; margin:10px 0; border-radius:8px; font-size:16px; }
form select { border:1px solid #ddd; }
form button { border:none; color:#fff; background:linear-gradient(45deg, #2980b9, #3498db); cursor:pointer; transition:0.3s; }
form button:hover { background:linear-gradient(45deg, #1f669e, #2980b9); }
table { width:100%; margin-top:30px; border-collapse:collapse; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
table th, table td { padding:12px 15px; border-bottom:1px solid #ddd; text-align:center; }
table th { background:#2980b9; color:white; }
table tr:nth-child(even) { background:#f9f9f9; }
table tr:hover { background:#f1f1f1; }
.back-link { display:block; text-align:center; margin-top:15px; color:#27ae60; text-decoration:none; font-size:14px; }
.back-link:hover { text-decoration:underline; }
.no-data { text-align:center; margin-top:20px; color:#888; }
.flash-error { color:red; font-weight:bold; text-align:center; margin-bottom:15px; }
.status-badge { padding:5px 10px; border-radius:20px; color:white; font-size:13px; font-weight:bold; }
.status-approved { background:#27ae60; }
.status-pending { background:#f39c12; }
.status-rejected { background:#e74c3c; }
</style>
</head>
<body>
<div class="container">

<?php
if(isset($_SESSION['error'])){
    echo '<div class="flash-error">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}
?>

<h2>🔧 नया उपकरण जोड़ें</h2>
<?php if($user_status == 'approved' && count($tools) < $tool_limit): ?>
<form method="POST" action="tools.php">
  <select name="tool_name" required>
    <option value="">उपकरण चुनें</option>
    <option value="Tractor">ट्रैक्टर</option>
    <option value="Plough">हल</option>
    <option value="Harvester">हार्वेस्टर</option>
    <option value="Seed Drill">बीज ड्रिल</option>
    <option value="Sprayer">स्प्रेयर</option>
    <option value="Rotavator">रोटावेटर</option>
    <option value="Water Pump">वाटर पंप</option>
    <option value="Shovel">फावड़ा</option>
    <option value="Hoe">कुदाल</option>
    <option value="Sickle">दरांती</option>
  </select>
  <button type="submit" name="submit">➕ जोड़ें</button>
</form>
<?php elseif(count($tools) >= $tool_limit): ?>
<p class="flash-error">❌ आपने अपनी उपकरण सीमा (<?= $tool_limit ?>) पूरी कर ली है। अधिक जोड़ने के लिए एडमिन की मंज़ूरी का इंतज़ार करें।</p>
<?php endif; ?>

<h2>🛠 मेरे उपकरण</h2>
<?php if(count($tools) > 0): ?>
<table>
<tr>
  <th>उपकरण का नाम</th>
  <th>स्थिति</th>
  <th>जोड़ने की तारीख</th>
</tr>
<?php foreach($tools as $tool): ?>
<tr>
  <td><?= htmlspecialchars($tool['tool_name']) ?></td>
  <td><span class="status-badge status-<?= htmlspecialchars($tool['status']) ?>"><?= ucfirst($tool['status']) ?></span></td>
  <td><?= htmlspecialchars($tool['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p class="no-data">अभी तक कोई उपकरण नहीं जोड़ा गया है। ऊपर से जोड़ें 🔧</p>
<?php endif; ?>

<a href="profile.php" class="back-link">⬅ प्रोफाइल पर वापस जाएं</a>
</div>
</body>
</html>
