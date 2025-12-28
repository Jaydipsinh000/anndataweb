<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: register.php");
    exit();
}

require_once "../php/db.php";

$user_id = $_SESSION['user_id'];

// યુઝરનો સ્ટેટસ અને ટૂલ લિમિટ મેળવો
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
    $_SESSION['error'] = "❌ તમારું એકાઉન્ટ એડમિનની મંજૂરીની રાહમાં છે. તમે હજી ટૂલ ઉમેરી શકતા નથી.";
} elseif($user_status == 'blocked'){
    $_SESSION['error'] = "❌ તમારું એકાઉન્ટ બ્લોક થયું છે. કૃપા કરીને એડમિનનો સંપર્ક કરો.";
}

// ટૂલ ઉમેરવા માટેનું ફોર્મ સબમિટ હેન્ડલિંગ
if(isset($_POST['submit'])){
    if($user_status == 'approved'){
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM tools WHERE user_id = ?");
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        $existing_tools = intval($res_count->fetch_assoc()['total']);
        $stmt_count->close();

        if($existing_tools >= $tool_limit){
            $_SESSION['error'] = "❌ તમે તમારા ટૂલ લિમિટ ($tool_limit) સુધી પહોંચી ગયા છો. વધુ ઉમેરવા માટે એડમિનની મંજૂરીની રાહ જુઓ.";
        } else {
            $tool_name = $_POST['tool_name'];
            $quantity = intval($_POST['quantity']);
            if($quantity < 1) $quantity = 1;
            $rent = floatval($_POST['rent']);
            $stmt = $conn->prepare("INSERT INTO tools (user_id, tool_name, quantity, rent, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("isid", $user_id, $tool_name, $quantity, $rent);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "❌ તમારું એકાઉન્ટ મંજૂર નથી. તમે ટૂલ ઉમેરી શકતા નથી.";
    }
    header("Location: tools.php");
    exit();
}

// યુઝરના ટૂલ્સ મેળવો
$stmt = $conn->prepare("SELECT id, tool_name, quantity, rent, status, created_at FROM tools WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tools = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// માસ્ટર ટૂલ્સ મેળવો
$master_tools_res = $conn->query("SELECT id, tool_name, rent FROM master_tools ORDER BY tool_name ASC");
$master_tools = $master_tools_res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="gu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>મારા સાધનો | અન્નદાતા</title>
<link rel="stylesheet" href="../styles.css">
<style>
body { font-family:'Noto Sans Gujarati', sans-serif; background:#f4f6f9; margin:0; padding:0; }
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
.qty-controls { display:flex; justify-content:flex-start; align-items:center; margin-top:5px; gap:5px; }
.qty-controls button { width:30px; height:30px; font-size:18px; cursor:pointer; border:none; background:#2980b9; color:white; border-radius:6px; transition:0.2s; display:flex; align-items:center; justify-content:center; }
.qty-controls button:hover { background:#1f669e; }
.qty-controls input { width:50px; text-align:center; border:1px solid #ddd; border-radius:6px; padding:5px; height:30px; }
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

<h2>🔧 નવું સાધન ઉમેરો</h2>
<?php if($user_status == 'approved' && count($tools) < $tool_limit): ?>
<form method="POST" action="tools.php">
  <select name="tool_name" id="toolSelect" required>
    <option value="">સાધન પસંદ કરો</option>
    <?php foreach($master_tools as $mt): ?>
        <option value="<?= htmlspecialchars($mt['tool_name']) ?>" data-rent="<?= $mt['rent'] ?>">
            <?= htmlspecialchars($mt['tool_name']) ?> (ભાડું: ₹<?= number_format($mt['rent'],2) ?>/દિવસ)
        </option>
    <?php endforeach; ?>
  </select>

  <div class="qty-controls">
    <button type="button" onclick="changeAddQty(-1)">-</button>
    <input type="text" id="add-qty" name="quantity" value="1" readonly>
    <button type="button" onclick="changeAddQty(1)">+</button>
  </div>

  <input type="hidden" id="rentInput" name="rent" value="0">

  <button type="submit" name="submit" style="margin-top:10px;">➕ ઉમેરો</button>
</form>

<script>
let baseRent = 0;

function updateRent(){
    const quantity = parseInt(document.getElementById('add-qty').value);
    document.getElementById('rentInput').value = baseRent * quantity;
}

function changeAddQty(delta){
    let input = document.getElementById('add-qty');
    let newQty = parseInt(input.value) + delta;
    if(newQty < 1) newQty = 1;
    input.value = newQty;
    updateRent();
}

const toolSelect = document.getElementById('toolSelect');
toolSelect.addEventListener('change', ()=>{
    const selected = toolSelect.selectedOptions[0];
    baseRent = parseFloat(selected.dataset.rent) || 0;
    updateRent();
});
</script>
<?php elseif(count($tools) >= $tool_limit): ?>
<p class="flash-error">❌ તમે તમારા ટૂલ લિમિટ (<?= $tool_limit ?>) સુધી પહોંચી ગયા છો. વધુ ઉમેરવા માટે એડમિનની મંજૂરીની રાહ જુઓ.</p>
<?php endif; ?>

<h2>🛠 મારા સાધનો</h2>
<?php if(count($tools) > 0): ?>
<table>
<tr>
  <th>સાધન નામ</th>
  <th>જથ્થો</th>
  <th>ભાડું</th>
  <th>સ્થિતિ</th>
  <th>ઉમેરેલ તારીખ</th>
</tr>
<?php foreach($tools as $tool): ?>
<tr>
  <td><?= htmlspecialchars($tool['tool_name']) ?></td>
  <td><?= htmlspecialchars($tool['quantity']) ?></td>
  <td>₹<?= number_format($tool['rent'],2) ?></td>
  <td><span class="status-badge status-<?= htmlspecialchars($tool['status']) ?>"><?= ucfirst($tool['status']) ?></span></td>
  <td><?= htmlspecialchars($tool['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p class="no-data">હજુ સુધી કોઈ સાધન ઉમેરાયેલ નથી. ઉપરથી ઉમેરો 🔧</p>
<?php endif; ?>

<a href="profile.php" class="back-link">⬅ પ્રોફાઇલ પર પાછા જાઓ</a>
</div>
</body>
</html>
