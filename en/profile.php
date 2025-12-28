<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: register.php");
    exit();
}
require_once "../php/db.php"; // database connection

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch user crops
$crops_sql = "SELECT * FROM crops WHERE user_id = ?";
$stmt = $conn->prepare($crops_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$crops = $stmt->get_result();

// Fetch user tools
$tools_sql = "SELECT * FROM tools WHERE user_id = ?";
$stmt = $conn->prepare($tools_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tools = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | Anndata</title>
<link rel="stylesheet" href="../styles.css">
<style>
body { font-family: Arial, sans-serif; background:#f4fdf7; margin:0; padding:0; }
main { max-width:1100px; margin: 20px auto; padding:20px; }
h1,h2,h3 { color:#2e7d32; }
section { background:#fff; padding:20px; margin-bottom:20px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
.profile-header { display:flex; justify-content: space-between; align-items:center; margin-bottom:20px; }
.profile-header h1 { margin:0; }
.button { background:#4CAF50; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; text-decoration:none; font-weight:bold; transition:0.3s; }
.button:hover { background:#45a049; }
.list { list-style:none; padding:0; }
.list li { padding:10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; }
.list li:last-child { border-bottom:none; }
.empty-msg { color:#888; }
@media(max-width:768px){ .profile-header{flex-direction:column; align-items:flex-start;} }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<main>
  <div class="profile-header">
    <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
    <a href="../php/logout.php" class="button">Logout</a>
  </div>

  <!-- Personal Info -->
  <section>
    <h2>Personal Info</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
    <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']); ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
  </section>

  <!-- Crops -->
  <section>
    <h2>Your Crops</h2>
    <?php if($crops->num_rows > 0): ?>
    <ul class="list">
      <?php while($crop = $crops->fetch_assoc()): ?>
      <li>
        <span><?= htmlspecialchars($crop['crop_name']); ?> (<?= htmlspecialchars($crop['area_size']); ?>) - <?= htmlspecialchars($crop['season']); ?></span>
        <a href="edit_crop.php?id=<?= $crop['id'] ?>" class="button" style="padding:5px 10px;font-size:0.8rem;">Edit</a>
      </li>
      <?php endwhile; ?>
    </ul>
    <?php else: ?>
      <p class="empty-msg">No crops added yet. <a href="crops.php" class="button" style="padding:5px 10px;font-size:0.8rem;">➕ Add Crop</a></p>
    <?php endif; ?>
  </section>

  <!-- Tools -->
  <section>
    <h2>Your Tools</h2>
    <?php if($tools->num_rows > 0): ?>
    <ul class="list">
      <?php while($tool = $tools->fetch_assoc()): ?>
      <li>
        <span><?= htmlspecialchars($tool['tool_name']); ?></span>
        <a href="edit_tool.php?id=<?= $tool['id'] ?>" class="button" style="padding:5px 10px;font-size:0.8rem;">Edit</a>
      </li>
      <?php endwhile; ?>
    </ul>
    <?php else: ?>
      <p class="empty-msg">No tools added yet. <a href="tools.php" class="button" style="padding:5px 10px;font-size:0.8rem;">➕ Add Tool</a></p>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
