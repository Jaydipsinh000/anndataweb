<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: register.php");
    exit();
}

require_once "../php/db.php";

$user_id = $_SESSION['user_id'];

// 🔹 उपयोगकर्ता स्थिति और फसल सीमा प्राप्त करें
$query = "SELECT status, crop_limit FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$stmt->close();

$user_status = $user_data['status'];
$crop_limit = intval($user_data['crop_limit']);

// स्थिति अनुसार संदेश
if($user_status == 'pending'){
    $_SESSION['error'] = "❌ आपका खाता अनुमोदन के लिए प्रतीक्षारत है। आप अभी फसल नहीं जोड़ सकते।";
} elseif($user_status == 'blocked'){
    $_SESSION['error'] = "❌ आपका खाता ब्लॉक है। कृपया एडमिन से संपर्क करें।";
}

// फ़ॉर्म सबमिट हैंडल
if(isset($_POST['submit'])){
    if($user_status == 'approved'){

        // मौजूदा फसलों की गिनती
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM crops WHERE user_id = ?");
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        $existing_crops = intval($res_count->fetch_assoc()['total']);
        $stmt_count->close();

        if($existing_crops >= $crop_limit){
            $_SESSION['error'] = "❌ आपने अपनी फसल सीमा ($crop_limit) पूरी कर ली है। अधिक जोड़ने के लिए एडमिन की अनुमोदन का इंतजार करें।";
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
        $_SESSION['error'] = "❌ आपका खाता अनुमोदित होने के बाद ही आप फसल जोड़ सकते हैं।";
    }
    header("Location: crops.php");
    exit();
}

// उपयोगकर्ता की फसलें प्राप्त करें
$stmt = $conn->prepare("SELECT crop_name, area_size, season, expected_yield, status, created_at FROM crops WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$crops = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>मेरी फसलें | Anndata</title>
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

<h2>🌱 नई फसल जोड़ें</h2>
<?php if($user_status == 'approved' && count($crops) < $crop_limit): ?>
<form method="POST" action="crops.php">
  <select name="crop_name" required>
    <option value="">फसल चुनें</option>
    <option value="Wheat">गेहूं</option>
    <option value="Rice">चावल</option>
    <option value="Maize">मकई</option>
    <option value="Sugarcane">गन्ना</option>
    <option value="Cotton">कपास</option>
    <option value="Pulses">दालें</option>
    <option value="Groundnut">मूंगफली</option>
    <option value="Mustard">सरसों</option>
    <option value="Barley">जौ</option>
    <option value="Soybean">सोयाबीन</option>
  </select>
  <input type="number" name="area_value" placeholder="क्षेत्रफल दर्ज करें" required>
  <select name="area_unit" required>
    <option value="Bigha">बीघा</option>
    <option value="Acre">एकड़</option>
    <option value="Hectare">हेक्टेयर</option>
  </select>
  <select name="season" required>
    <option value="">ऋतु चुनें</option>
    <option value="Monsoon">मानसून</option>
    <option value="Winter">सर्दी</option>
    <option value="Summer">गर्मी</option>
    <option value="All Season">सभी ऋतुएँ</option>
  </select>
  <input type="text" name="expected_yield" placeholder="अनुमानित उपज (जैसे 20 क्विंटल)" required>
  <button type="submit" name="submit">➕ जोड़ें</button>
</form>
<?php elseif(count($crops) >= $crop_limit): ?>
<p class="flash-error">❌ आपने अपनी फसल सीमा (<?= $crop_limit ?>) पूरी कर ली है। अधिक जोड़ने के लिए एडमिन की अनुमोदन का इंतजार करें।</p>
<?php endif; ?>

<h2>🌾 मेरी फसलें</h2>
<?php if(count($crops) > 0): ?>
<table>
<tr>
  <th>फसल का नाम</th>
  <th>क्षेत्रफल</th>
  <th>ऋतु</th>
  <th>अनुमानित उपज</th>
  <th>स्थिति</th>
  <th>तिथि</th>
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
<p class="no-data">अभी तक कोई फसल नहीं जोड़ी गई। ऊपर से शुरू करें 🌱</p>
<?php endif; ?>

<a href="profile.php" class="back-link">⬅ प्रोफाइल पर वापस जाएं</a>
</div>
</body>
</html>
