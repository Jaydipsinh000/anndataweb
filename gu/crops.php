<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: register.php");
    exit();
}

require_once "../php/db.php";

$user_id = $_SESSION['user_id'];

// 🔹 વપરાશકર્તા સ્ટેટસ અને ક્રોપ લિમિટ મેળવવું
$query = "SELECT status, crop_limit FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$stmt->close();

$user_status = $user_data['status'];
$crop_limit = intval($user_data['crop_limit']);

// સ્ટેટસ પ્રમાણે મેસેજ
if($user_status == 'pending'){
    $_SESSION['error'] = "❌ તમારું એકાઉન્ટ મંજૂરી માટે પ્રતીક્ષામાં છે. તમારે ફળ/ફસલ ઉમેરવાની મંજૂરી નથી.";
} elseif($user_status == 'blocked'){
    $_SESSION['error'] = "❌ તમારું એકાઉન્ટ બ્લોક છે. કૃપા કરીને એડમિનનો સંપર્ક કરો.";
}

// ફોર્મ સબમિટ હેન્ડલ
if(isset($_POST['submit'])){
    if($user_status == 'approved'){

        // હાજર ક્રોપની ગણના
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM crops WHERE user_id = ?");
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        $existing_crops = intval($res_count->fetch_assoc()['total']);
        $stmt_count->close();

        if($existing_crops >= $crop_limit){
            $_SESSION['error'] = "❌ તમે તમારી ફળ/ફસલની મર્યાદા ($crop_limit) પૂર્ણ કરી છે. વધુ ઉમેરવા માટે એડમિનની મંજૂરી માટે રાહ જુઓ.";
        } else {
            $crop_name = $_POST['crop_name'];
            $area_value = $_POST['area_value'];
            $area_unit = $_POST['area_unit'];
            $season = $_POST['season'];
            $expected_yield = $_POST['expected_yield'];
            $area_size = $area_value . " " . $area_unit;

            $stmt = $conn->prepare("INSERT INTO crops (user_id, crop_name, area_size, season, expected_yield, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issss", $user_id, $crop_name, $area_size, $season, $expected_yield);
            $stmt->execute();
            $stmt->close();
        }

    } else {
        $_SESSION['error'] = "❌ તમારે તમારું એકાઉન્ટ મંજૂર કર્યા પછી જ ફળ/ફસલ ઉમેરવાની મંજૂરી મળશે.";
    }
    header("Location: crops.php");
    exit();
}

// વપરાશકર્તા ફસલો લાવવી
$stmt = $conn->prepare("SELECT crop_name, area_size, season, expected_yield, status, created_at FROM crops WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$crops = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="gu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>મારી ફસલો | અન્નદાતા</title>
<link rel="stylesheet" href="../styles.css">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
.container { max-width: 950px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 20px; color: #2c3e50; }
form input, form select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
form button { width: 100%; background: linear-gradient(45deg, #27ae60, #2ecc71); color: #fff; border: none; padding: 12px; font-size: 16px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
form button:hover { background: linear-gradient(45deg, #219150, #27ae60); }
table { width: 100%; margin-top: 30px; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
table th, table td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: center; }
table th { background: #27ae60; color: white; }
table tr:nth-child(even) { background: #f9f9f9; }
table tr:hover { background: #f1f1f1; }
.back-link { display: block; text-align: center; margin-top: 20px; color: #2980b9; text-decoration: none; }
.no-data { text-align: center; margin-top: 20px; color: #888; }
.flash-error { color: red; font-weight: bold; text-align: center; margin-bottom: 15px; }
.status-badge { padding: 4px 8px; border-radius: 6px; color: #fff; font-weight: bold; }
.status-pending { background: #f39c12; }
.status-approved { background: #27ae60; }
.status-rejected { background: #e74c3c; }
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

<h2>🌱 નવી ફસલ ઉમેરો</h2>
<?php if($user_status == 'approved' && count($crops) < $crop_limit): ?>
<form method="POST" action="crops.php">
  <select name="crop_name" required>
    <option value="">ફસલ પસંદ કરો</option>
    <option value="Wheat">ગહૂં</option>
    <option value="Rice">ચોખા</option>
    <option value="Maize">મકાઈ</option>
    <option value="Sugarcane">ખાંડકામળ</option>
    <option value="Cotton">કપાસ</option>
    <option value="Pulses">ડાળ</option>
    <option value="Groundnut">મૂંગફળી</option>
    <option value="Mustard">સરસો</option>
    <option value="Barley">જૌ</option>
    <option value="Soybean">સોયાબીન</option>
  </select>
  <input type="number" name="area_value" placeholder="અરિયા લખો" required>
  <select name="area_unit" required>
    <option value="Bigha">બીઘા</option>
    <option value="Acre">એકર</option>
    <option value="Hectare">હેક્ટર</option>
  </select>
  <select name="season" required>
    <option value="">રસતીઓ પસંદ કરો</option>
    <option value="Winter">શિયાળો</option>
    <option value="Summer">ઉનાળો</option>
    <option value="Monsoon">ચોમાસું</option>
    <option value="All Season">બધા રુતુઓ</option>
  </select>
  <input type="text" name="expected_yield" placeholder="અંદાજિત ઉપજ (જેમ કે 20 ક્વિન્ટલ)" required>
  <button type="submit" name="submit">➕ ઉમેરો</button>
</form>
<?php elseif(count($crops) >= $crop_limit): ?>
<p class="flash-error">❌ તમે તમારી ફસલ મર્યાદા (<?= $crop_limit ?>) પૂર્ણ કરી છે. વધુ ઉમેરવા માટે એડમિનની મંજૂરી માટે રાહ જુઓ.</p>
<?php endif; ?>

<h2>🌾 મારી ફસલો</h2>
<?php if(count($crops) > 0): ?>
<table>
<tr>
  <th>ફસલ નામ</th>
  <th>અરિયા</th>
  <th>રસતો</th>
  <th>અંદાજિત ઉપજ</th>
  <th>સ્થિતિ</th>
  <th>તારીખ</th>
</tr>
<?php foreach($crops as $crop): ?>
<tr>
  <td><?= htmlspecialchars($crop['crop_name']) ?></td>
  <td><?= htmlspecialchars($crop['area_size']) ?></td>
  <td><?= htmlspecialchars($crop['season']) ?></td>
  <td><?= htmlspecialchars($crop['expected_yield']) ?></td>
  <td><span class="status-badge status-<?= $crop['status'] ?>"><?= ucfirst($crop['status']) ?></span></td>
  <td><?= htmlspecialchars($crop['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p class="no-data">હજી સુધી કોઈ ફસલ ઉમેરાઈ નથી. ઉપરથી શરૂ કરો 🌱</p>
<?php endif; ?>

<a href="profile.php" class="back-link">⬅ પ્રોફાઇલ પર પાછા જાઓ</a>
</div>
</body>
</html>
