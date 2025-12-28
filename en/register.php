<?php
session_start();
include '../php/db.php';

$fieldErrors = [];
$loginErrors = [];
$successMsg = "";

// ---------------- Registration ----------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? '') === "register") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $mobile = trim($_POST["mobile"] ?? '');
    $state = $_POST["state"] ?? '';
    $district = $_POST["district"] ?? '';
    $taluka = $_POST["taluka"] ?? '';
    $village = $_POST["village"] ?? '';
    $role = $_POST["role"] ?? '';

    // ✅ Combine address instead of using non-existing 'state' column
    $address = trim("$state, $district, $taluka, $village");

    // ---- Validations ----
    if (empty($name)) $fieldErrors["name"] = "Name is required.";
    if (empty($email)) $fieldErrors["email"] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fieldErrors["email"] = "Enter a valid email address.";
    if (empty($password)) $fieldErrors["password"] = "Password is required.";
    elseif (strlen($password) < 6) $fieldErrors["password"] = "Password must be at least 6 characters long.";
    if (empty($mobile)) $fieldErrors["mobile"] = "Mobile number is required.";
    if (empty($district)) $fieldErrors["district"] = "Please select district.";
    if (empty($taluka)) $fieldErrors["taluka"] = "Please select taluka.";
    if (empty($village)) $fieldErrors["village"] = "Please select village.";
    if (empty($role)) $fieldErrors["role"] = "Please select your role.";

    // Check if email already exists
    if (empty($fieldErrors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $fieldErrors['email'] = "Email is already registered.";
        $stmt->close();
    }

    // Insert into DB
    if (empty($fieldErrors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // ✅ Use combined $address instead of missing 'state'
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, mobile, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $role, $mobile, $address);
        if ($stmt->execute()) {
            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["user_name"] = $name;
            $_SESSION["user_role"] = $role;
            header("Location: ../en/profile.php");
            exit;
        } else {
            $fieldErrors['general'] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

// ---------------- Login ----------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? '') === "login") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email)) $loginErrors['email'] = "Email is required.";
    if (empty($password)) $loginErrors['password'] = "Password is required.";

    if (empty($loginErrors)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashedPass, $role);
            $stmt->fetch();
            if (password_verify($password, $hashedPass)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                header("Location: ../en/profile.php");
                exit;
            } else {
                $loginErrors['general'] = "Incorrect password.";
            }
        } else {
            $loginErrors['general'] = "Email is not registered.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register / Login | Anndata</title>
<link rel="stylesheet" href="../styles.css">
<style>
.form-box { max-width:400px; margin:20px auto; padding:20px; background:#f9f9f9; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.2);}
.form-box h2{text-align:center;}
.form-box label{font-weight:bold; margin-top:10px; display:block;}
.form-box input,.form-box select{width:100%; padding:8px; margin-top:5px; border-radius:6px; border:1px solid #ccc;}
.form-box button{width:100%; margin-top:15px; padding:10px; border:none; border-radius:6px; background:green; color:white; font-size:1rem;}
.error { color:red; font-size:14px; margin-top:4px;}
.success{color:green; font-size:16px; margin-bottom:10px;}
.toggle-text{text-align:center; margin-top:10px;}
.toggle-text a{color:blue; cursor:pointer;}
</style>
</head>
<body>
<header>
    <div style="display: flex; align-items: center;">
      <img src="../images/logo.jpeg" alt="Anndata Logo" class="logo" />
      <div class="logo-title" style="line-height: 1;">
        <span style="font-size: 2rem; font-weight: bold; color: #fff;">Anndata</span>
        <span style="font-size: 1rem; color: #e0ffe0;">True Friend of Farmers</span>
      </div>
    </div>
    <nav>
      <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <span></span><span></span><span></span>
      </div>
      <ul class="navbar" id="nav-links">
        <li><a href="index.html" >Home</a></li>
        <li><a href="tools.html" >Tools</a></li>
        <li><a href="organic.html" >Organic Farming</a></li>
        <li><a href="crops.html" >Crop Info</a></li>
        <li><a href="register.php" class="active">Register / Login</a></li>
        
      </ul>
    </nav>
  </header>

<main class="content-section">
<h1 style="text-align:center;">Register / Login</h1>
<div class="form-container">

<!-- Register -->
<div id="register-box" class="form-box" style="<?= isset($_POST['action']) && $_POST['action']==='login'?'display:none;':'' ?>">
<h2>Register</h2>
<?php if(!empty($successMsg)) echo "<p class='success'>$successMsg</p>"; ?>
<?php if(!empty($fieldErrors['general'])) echo "<p class='error'>{$fieldErrors['general']}</p>"; ?>
<form method="POST" id="registerForm">
<input type="hidden" name="action" value="register">

<label>Name:</label>
<input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
<div class="error"></div>

<label>Email:</label>
<input type="text" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
<div class="error"></div>

<label>Password:</label>
<div style="position:relative;">
<input type="password" name="password" id="password">
<span id="togglePassword" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">👁️</span>
</div>
<div class="error"></div>

<label>Mobile:</label>
<input type="text" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>">
<div class="error"></div>

<label>State:</label>
<select name="state">
<option value="Gujarat" <?= (($_POST['state']??'')=="Gujarat"?"selected":"") ?>>Gujarat</option>
</select>

<label>District:</label>
<select name="district" id="district">
<option value="">Select District</option>
</select>
<div class="error"></div>

<label>Taluka:</label>
<select name="taluka" id="taluka">
<option value="">Select Taluka</option>
</select>
<div class="error"></div>

<label>Village:</label>
<select name="village" id="village">
<option value="">Select Village</option>
</select>
<div class="error"></div>

<label>Role:</label>
<select name="role">
<option value="">-- Select Role --</option>
<option value="farmer" <?= (($_POST['role'] ?? '')=="farmer"?"selected":"") ?>>Farmer</option>
<option value="worker" <?= (($_POST['role'] ?? '')=="worker"?"selected":"") ?>>Worker</option>
</select>
<div class="error"></div>

<button type="submit">Register</button>
</form>
<p class="toggle-text">Already have an account? <a onclick="showLogin()">Login here</a></p>
</div>

<!-- Login -->
<div id="login-box" class="form-box" style="<?= isset($_POST['action']) && $_POST['action']==='login'?'':'display:none;' ?>">
<h2>Login</h2>
<?php if(!empty($loginErrors['general'])) echo "<p class='error'>{$loginErrors['general']}</p>"; ?>
<form method="POST">
<input type="hidden" name="action" value="login">
<label>Email:</label>
<input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
<?php if(!empty($loginErrors['email'])): ?><div class="error"><?= $loginErrors['email'] ?></div><?php endif; ?>
<label>Password:</label>
<div style="position:relative;">
<input type="password" name="password" id="password-login">
<span id="togglePasswordLogin" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">👁️</span>
<?php if(!empty($loginErrors['password'])): ?><div class="error"><?= $loginErrors['password'] ?></div><?php endif; ?>
</div>
<button type="submit">Login</button>
</form>
<p class="toggle-text">Don’t have an account? <a onclick="showRegister()">Register here</a></p>
</div>
</div>
</main>

<script>
// Show/hide forms
function showLogin(){ 
    document.getElementById("register-box").style.display="none"; 
    document.getElementById("login-box").style.display="block";
}
function showRegister(){ 
    document.getElementById("login-box").style.display="none"; 
    document.getElementById("register-box").style.display="block";
}

// Password toggle - Register
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');
if(togglePassword && passwordField){
    togglePassword.addEventListener('click', function(){
        const type = passwordField.type==='password'?'text':'password';
        passwordField.type = type;
        this.textContent = type==='password'?'👁️':'🙈';
    });
}

// Password toggle - Login
const togglePasswordLogin = document.getElementById('togglePasswordLogin');
const passwordFieldLogin = document.getElementById('password-login');
if(togglePasswordLogin && passwordFieldLogin){
    togglePasswordLogin.addEventListener('click', function(){
        const type = passwordFieldLogin.type==='password'?'text':'password';
        passwordFieldLogin.type = type;
        this.textContent = type==='password'?'👁️':'🙈';
    });
}

// ---------------- Dependent dropdowns ----------------
const gujaratData = {
    "Ahmedabad": {
    "Ahmedabad City": ["VillageA","VillageB"],
    "Baval": ["Adroda","Amipura","Bagodara","Baldana","Bhamsara","Bhayla","Chhabasar","Chiyada","Dahegamda","Devadthal","Devdholera","Dhanwada","Dhedhal","Dhingda","Dumali","Durgi","Gangad","Gundanapara","Hasannagar","Juval Rupavati","Kaliveji","Kalyangadh","Kanotar","Kavitha","Kavla","Kerala","Kesaradi","Kochariya","Lagdana","Memar","Meni","Metal","Mithapur","Nanodara","Ranesar","Rohika","Rupal","Sakodara","Saljada","Sankod","Sarala","Shiyal","Vasna Dhedhal","Vasna Nanodara","Zekda"],
    "Daskroi": ["Aslali","Badodara","Bakrol Bujrang","Barejadi","Bharkunda","Bhat","Bhavda","Bhuval","Bhuvaldi","Bibipur","Chandial","Chavlaj","Chosar","Devdi","Dhamatvan","Gamdi","Gatrad","Geratnagar","Geratpur","Giramtha","Govindada","Harnivav","Hathijan","Hirapur","Huka","Istolabad","Jetalpur","Kaniyel","Kasindra","Khodiyar","Kubadthal","Kuha","Lalpur","Lapkaman","Lilapur","Mahijda","Memadpur","Miroli","Muthiya","Navapura","Navarangpura","Naz","Ode","Paldi Kankaj","Pardhol","Pasunj","Ranodara","Ropda","Timba","Undrel","Vadod","Vahelal","Vanch","Vasai","Visalpur","Zanu"],
    "Detroj-Rampura": ["Abasna","Aghar (Ashoknagar)","Amarpura","Balsasan","Bamroli","Bantai","Bhagapura","Bhankoda","Bhatariya","Bhonyni","Bhonynipura","Boska","Chhaniyar","Dabhsar","Damodaripura","Dangarva","Dekavada","Detroj","Fatepura","Gamanpura","Ghatisana","Ghelda","Gunjala","Hathipura","Indrapura","Jaspura","Jethipura","Kantrodi","Kanz","Kointiya","Kukvav","Madrisana","Marusana","Mota Karanpura","Moti Rantai","Nadishala","Nana Karanpura","Nani Rantai","Nathpura","Odhav","Odhav Paru","Panar","Rajpura","Rampura","Ratanpura","Rudatal","Sadatpura","Sangpara","Shihor","Shobhasan","Sujpura","Sunvala","Telavi","Umedpura","Vasna (Chhaniyar)"],
    "Dhandhuka": ["Adval","Akru","Ambli","Anandpur","Aniyali Bhimji","Bajarda","Bavliyari","Bhadiyad","Bhalgamda","Bhangadh","Bhimtalav","Buranpur","Chandarva","Cher","Chharodiya","Chhasiyana","Dhanala","Dholera","Fattepur","Fedra","Galsana","Gamph","Gogla","Gorasu","Gunjar","Haripura","Jaliya","Jaska","Kadipur","Kamatalav","Kamiyala","Kasindra","Khadol","Kharad","Khasta","Khun","Kotda","Kothadiya","Mahadevpura","Mingalpur","Morasiya","Mota Tradiya","Mundi","Nana Tradiya","Navagam","Otariya","Pachchham","Padana","Panchi","Parabdi","Pipal","Pipli","Rahtalav","Ratanpur","Rayka","Rojka","Salasar","Sandhida","Sarwal","Shela","Tagadi","Umargadh","Unchdi","Vagad","Valinda","Vasana","Zankhi","Zanzarka","Zinzar"],
    "Dholka": ["Ambaliyara","Ambareli","Ambethi","Anandpura","Andhari","Arnej","Badarkha","Begva","Bhetawada","Bholad","Bhumli","Bhurkhi","Chaloda","Chandisar","Dadusar","Dholi","Dholka (Rural)","Ganesar","Ganol","Girand","Gundi","Ingoli","Jakhda","Jalalpur Godhaneshvar","Jalalpur Vazifa","Javaraj","Kadipur","Kaliyapura","Kalyanpur","Kariyana","Kauka","Kesargadh","Khanpur","Kharanti","Khatripur","Koth","Lana","Loliya","Moti Boru","Mujpur","Nani Boru","Nesda","Paldi","Pisawada","Rajpur","Rampur","Rampura","Ranoda","Raypur","Rupgadh","Sahij","Samani","Saragvala","Sarandi","Saroda","Sathal","Shekhdi","Shiyawada","Simej","Sindhraj","Transad","Uteliya","Valthera","Varna","Vasna Keliya","Vataman","Vautha","Vejalka","Virdi","Virpur"],
    "Mandal": ["Anandpura","Dadhana","Dalod","Dhedhasana","Endla","Hansalpur Becharaji","Jalisana","Kachrol","Kadvasan","Kanpura (Sinaj)","Karshanpura","Kunpur","Mandal","Manpura","Mithapur","Nana Ubhada","Navagam","Nayakpur","Odaki","Rakhiyana","Ribdi","Sadra","Sher","Sinaj","Sitapur","Solgam","Trent","Ughroj","Ughrojpura","Ukardi","Vanpardi","Varmor","Vasna Kunpur","Vinchhan","Vinzuvada","Vitthapur","Zanzarava"],
	  "Ranpur": ["Alampur","Alau","Aniyali Kasbati","Aniyali Kathi","Bagad","Baraniya","Bodiya","Bubavav","Charanki","Derdi","Devaliya","Devgana","Dharpipla","Gadhiya","Godhawata","Gunda","Hadmatala","Jalila","Keriya","Khas","Khokharnesh","Kinara","Kundli","Malanpur","Moti Vavdi","Nani Vavdi","Panvi","Patna","Rajpara","Sanganpur","Sundariyana","Umrala","Vejalka"],
    "Sanand": ["Anadej","Aniyali","Bakrana","Bhavanpur","Bol","Charal","Chekhla","Chharodi","Daduka","Daran","Dodar","Fangdi","Garodiya","Goraj","Govinda","Hirapur","Iyava","Juda","Juwal","Kalana","Khicha","Khoda","Khoraj","Kodaliya","Kolat","Kundal","Kunvar","Lekhamba","Lodariyal","Lilapur","Makhiyav","Mankol","Melasana","Modasar","Moti Devti","Nani Devti","Naranpura","Palwada","Pipan","Rampura","Rethal","Rupavati","Shiyawada","Soyla","Tajpur","Upardal","Vanaliya","Vasna Iyava","Vasodara","Vinchhiya","Virochannagar","Zamp","Zolapur"],
    "Viramgam": ["Asalgam","Bhadana","Bhavda","Bhojva","Chanothiya","Chuninapura","Dalsana","Dediyasan","Devpura","Dhakdi","Dumana","Ghoda","Goraiya","Hansalpur Sereshvar","Jakhwada","Jaksi","Jalampura","Jetapur","Juna Padar","Kadipur","Kaliyana","Kalyanpur (Shiyal)","Kamijla","Kankaravadi","Kanpura (Dalsana)","Karakathal","Karangadh","Kariyana","Kayla","Khengariya","Khudad","Kokta","Kumarkhan","Limbad","Liya","Melaj","Memadpura","Moti Kishol","Moti Kumad","Nadiyana","Nani Kishol","Nani Kumad","Nilki","Ogan","Rahemalpur","Rangpur","Rupavati","Sabalpura","Sachana","Sarsavadi","Shahpur","Shivpura","Sokali","Thori Mubarak","Thori Thambha","Thori Vadgas","Thuleta","Ukhalod","Vadgas","Valana","Vani","Vanthal","Varsava","Vasan","Vasveliya","Vekariya","Viramgam (Rural)","Zezara"]
  },
  "Amreli": {
  "Amreli": ["Amarpur(Varudi)","Ankadiya Mota","Ankadiya Nana","Babapur","Baxipur","Bhandariya Mota","Bhandariya Nana","Chadiya","Chakkargadh","Champathal","Chandgadh","Chital","Dahida","Devaliya","Devrajiya","Dholarva","Fattepur","Gavadka","Giriya","Gokharvala Mota","Gokharvala Nana","Haripura","Ishvariya","Jaliya","Jasvantgadh","Kamigadh","Kathma","Kerala","Keriyachad","Keriyanagas","Khad Khambhaliya","Khijadiya Khari","Khijadiya Radadiya","Lalavadar","Lapaliya","Machiyala Mota","Machiyala Nana","Malila","Malvan","Mandavda Mota","Mandavda Nana","Mangvapal","Medi","Monpur","Navakhijadiya","Paniya","Pipllag","Pithavajal","Pratappara","Rajasthali","Randhiya","Rangpur","Rikadiya","Sajiyavadar","Sangaderi","Sanosara","Sarambhda","Shambhupura","Shedubhar","Sonariya","Suragpur","Taraktalav","Taravda","Thordi","Timba","Timbla","Vadera","Vankiya","Varasda","Venivadar","Vithalpur"],
  "Babra": ["Amarvalpar","Balel Pipariya","Barvala","Bhila","Bhildi","Chamardi","Charkha","Dared","Devaliya Mota","Dharai","Fuljhar","Galkotdi","Gamapipaliya","Garni","Ghughrala","Hathigadh","Ingorala","Isapar","Ishvariya","Jivapar","Kalorana","Kariyana","Karnuki","Khakhariya","Khambhala","Khanpar","Khijadiya Kotda","Kidi","Kotda Pitha","Kundal Nani","Kunvargadh","Lalka","Lonkotda","Lunki","Miya Khijadiya","Nadala","Navaniya","Nilavala","Nonghanvadar","Pansada","Pir Khijadiya","Ranpar","Raypar","Samadhiyala","Sirvaniya","Sukavala","Sukhpar","Taivadar","Thorkhan","Tramboda","Untvad","Valardi","Vandaliya","Vankiya","Vavda","Vavdi"],
  "Bagasara": ["Adpur","Balapur","Charan Pipali","Deri Pipaliya","Ghantiyan","Hadala","Halariya","Haliyad Juni","Haliyad Navi","Hamapur","Hulariya","Jamka","Janjariya Juna","Janjariya Nava","Jethiavadar","Kadaya","Kagdadi","Khari","Khijadiya","Manekvada","Mavjinjva","Munjiasar Mota","Munjiasar Nana","Pipaliya Nava","Pithadiya","Rafala","Samadhiyala","Sanaliya","Shilana","Vaghaniya Juna","Vaghaniya Nava"],
  "Dhari": ["Amaratpur","Ambardi","Bhader","Bharad","Bhayavadar","Bordi","Chanchai","Chhatradiya","Dabhali","Dahida","Dalkhaniya","Dangavadar","Devla","Dhargani","Dhari","Dholarva","Ditla","Dudhala","Facharia","Fategadh","Gadhiya","Gadhiya Chavand","Garamali Moti","Garamali Nani","Garamli (Charkha)","Gigasan","Gopalgram","Govindpur","Hirava","Hudli","Ingorala(Dungri)","Jaljivadi","Jira","Juna Charkha","Kami","Kaner","Karamdadi","Kathirvadar","Kathrota","Kerala","Khambhaliya","Khicha","Khisri","Kotda","Kotha Pipariya","Krangsa","Kubda","Lakhapadar","Madhupur","Malshika","Manavav","Matan Mala","Mithapur(Dungri)","Mithapur Nakki","Monvel","Morzar","Nagadhra","Nava Charkha","Padargadh","Paniya(Devasthan)","Paniya Dungri","Parbadi","Patla","Rajsthali","Rampur","Ravna","Sakhpur","Samadhiyala Nana","Sarasiya","Shemardi","Shivad","Tarsingada","Trambakpur","Vaghvadi","Vavdi","Virpur","Zar"],
  "Jafrabad": ["Babarkot","Balana","Balanivav","Bhada","Bhankodar","Bhatvadar","Chhelana","Chitrasar","Dharabandar","Dholadri","Dudhala","Ebhalvad","Fachariya","Ghenspur","Hemal","Jikadri Juni","Jikadri Navi","Kadiyali","Kagvadar","Kanthariya Khalsa","Kanthariya Koli","Kerala","Lor","Lothpur","Lunsapur","Mithapur","Mitiyala","Mota Mansa","Nageshri","Pati Mansa (Nana)","Pichhadi","Rohisa","Sakariya Mota","Sakariya Nana","Sarovarda","Shiyalbet","Sokhda","Timbi","Vadhera","Vadli","Vandh","Varahsvarup"],
  "Khambha": ["Ambaliyala","Anida","Babarpur","Barman Mota","Barman Nana","Bhad","Bhaniya","Bhavardi","Bhundani","Borala","Chakrava","Dadhiyali","Dadli","Dedan","Dhari Nani","Dhavadiya","Dhundhavana","Gidardi","Gorana","Hanumanpur","Ingorala","Jamka","Jikiyali","Jivapar","Juna Malaknes","Kantala","Katarpara","Khadadhar","Khambha","Kodiya","Kotda","Lasa","Munjiyasar","Nanudi","Nava Malaknes","Nesdi No - 2","Ningala No - 2","Pachapachiya","Pati","Pipalava","Pipariya","Rabarika","Raningpara","Raydi","Rugnathpur","Salva","Samadhiyala Mota","Samadhiyala No - 2","Sarakadiya","Sarakadiya Divan","Talda","Tantaniya","Trakuda","Umariya","Vangadhara","Vankiya","Visavadar Nana"],
  "Kunkavav Vadia": ["Amrapur","Anida","Arjansukh","Badanpur Juna","Badanpur Nava","Bambhaniya","Bantwa - Devli","Barvala Baval","Barvala Bavishi","Bhayavadar","Bhukhli - Santhali","Dadva(Randal)","Devalki","Devgam","Ishvariya","Jithudi","Jungar","Khadkhad","Khajuri","Khajuri - Pipaliya","Khakhariya","Khijadiya Hanuman","Khijadiya Khan","Kolda","Kunkavav Moti","Kunkavav Nani","Lakhapadar","Luni - Dhar","Maya Padar","Megha - Pipaliya","Morvada","Najapur","Pipaliya Dhundhiya","Rampur","Sanala","Sanali","Sarangpur","Surya Pratapgadh","Talali","Targhari","Tori","Ujala - Mota","Ujala - Nava","Vadia","Vavdi Road"],
  "Lathi": ["Adtala","Akala","Aliudepur","Ambardi","Ansodar","Bhalvav","Bhatvadar","Bhingrad","Bhurakhiya","Chavand","Chhabhadiya","Dahinthara","Derdi - Janbai","Dhamel","Dhrufania","Dudhala Bai","Dudhala Lathi","Hajiradhar","Harsurpur","Havtad","Hirana","Ingorala","Jarakhiya","Kanchardi","Karkoliya","Kerala","Keriya","Krishna Gadh","Luvariya","Malaviya Pipariya","Matirala","Memda","Methli","Muliyapat","Narangadh","Padarshinga","Pipalva","Pratapgadh","Punjapar","Rabhda","Rajkot Nana","Rampar","Shakhpur","Shekhpipariya","Suvagadh","Tajpar","Thansa","Toda","Virpur"],
  "Lilia": ["Amba","Antaliya","Bavada","Bavadi","Bhensan","Bhensvadi","Bhoringda","Bodiya","Dhangla","Eklera","Godhavadar","Gundran","Haripur","Hathigadh","Ingorala","Jatroda","Kalyanpar","Kankot Mota","Kankot Nana","Khara","Krankach","Kuntana","Lilia Nana","Lonka","Lonki","Panch Talavda","Pipalva","Punjapadar","Putaliya","Rajkot Nana","Saldi","Sanaliya","Sanjantimba","Shedhavadar","Timbdi","Vaghaniya"],
  "Rajula": ["Agariya Dhudiya","Agariya Mota","Agariya Nava","Amuli","Babariyadhar","Balapar","Barbatana","Barpatoli","Bhachadar","Bhakshi","Bherai","Chanch","Charodiya","Chhapri","Chhatadiya","Chotra","Dantardi","Devka","Dharano Nes","Dhareshvar","Dipadiya","Doliya","Dungar","Dungarparda","Ganjavadar","Hadmatiya","Hindorna","Jholapar","Kadiyali","Katar","Kathivadar","Khakhbai","Khambhaliya","Khari","Khera","Kherali Moti","Kherali Nani","Kotdi","Kovaya","Kumbhariya","Kundaliyala","Majadar","Mandal","Mandardi Navi - Juni","Masundada Nana - Mota","Mobhiyana Mota","Mobhiyana Nana","Morangi","Navagam(Mariana)","Nesdi No - 1","Ningala No - 1","Patva","Pipavav","Rabhda","Rajparda","Rampara No - 1","Rampara No - 2","Ringaniyala Mota","Ringaniyala Nana","Sajanavav","Samadhiyala No - 1","Uchaiya","Untiya","Vad","Vadli","Vavdi","Vavera","Victar","Visaliya","Zampodar","Zanzarda","Zinzka"],
  "Savar Kundla": ["Abhrampara","Absang","Ambardi","Amrutvel","Ankolada","Badhada","Bagoya","Bhamar","Bhenkra","Bhonkarva","Bhuva","Borala","Charkhadiya","Chhapri","Chikhali","Dadhiya","Dedkadi","Detad","Dhajdi","Dhar","Dolti","Fachariya","Fifad","Gadhakda","Ghandla","Ghoba","Ghobapati","Giniya","Goradka","Hadida","Hathasani","Hipavadli","Jabal","Jambuda","Jejad","Jira","Juna Savar","Kanatalav","Kantrodi","Karjala","Kedariya","Kerala","Khadkala","Khadsali","Khalpar","Khodiyana","Kunkavav","Likhala","Luvara","Madhada","Mekda","Meriyana","Mevasa","Mitiyala","Moldi","Mota Bhamodra","Mota Zinzuda","Nal","Nana Bhamodra","Nana Zinzuda","Nani Vadal","Nesdi","Oliya","Piparadi","Pithvadi","Piyava","Rabarika","Ramgadh","Senjal","Shelana","Simaran","Thavi","Thordi","Vanda","Vanot","Vanshiyali","Vijapdi","Vijayanagar","Virdi","Zadkala"]
  },
  
  "Anand": {
  "Anand": ["Adas","Ajarpura","Anklavdi","Bedva","Chikhodra","Gana","Gopalpura","Jakhariya","Jitodiya (Part)","Jol","Kasor","Khambholaj","Khandhali","Khanpur","Kherda","Kunjrao","Lambhvel","Meghva Gana","Mogar","Napad Talpad","Napad Vanto","Navli","Rahtalav","Rajupura","Ramnagar","Rasnol","Samarkha","Sandesar","Sarsa","Sundan","Tarnol","Vadod","Vaghasi","Vaherakhadi","Valasan","Vans Khiliya","Vasad"],
  "Anklav": [ "Ambali","Ambav","Amrol","Asarma","Asodar","Bamangam","Bhanpura","Bhetasi Ba Bhag","Bhetasi (Talpad)","Bhetasi Vanta", "Bilpad","Chamara","Devapura","Gambhira","Haldari","Hathipura","Jhilod","Joshikuva","Kahanvadi","Kanthariya", "Khadol (Haldari)","Khadol (Umeta)","Kosindra","Lalpura","Manpura","Mujkuva","Narpura","Navakhal","Navapura","Sankhyad", "Umeta"],
  "Borsad": ["Alarsa","Amiyad","Badalpur","Banejda","Bhadran","Bhadraniya","Bochasan","Bodal","Chuva","Dabhasi","Dahemi","Dahewan","Dali","Davol","Dedarda","Dhanavasi","Dhobikui","Dhundakuva","Divel","Gajana","Gorel","Gorva","Harkhapura","Jantral","Kalu","Kanbha","Kandhroti","Kankapura","Kasari","Kasumbad","Kathana","Kathol","Kavitha","Khanpur","Khedasa","Kinkhlod","Kothiya Khad","Moti Sherdi","Naman","Nani Sherdi","Napa Talpad","Napa Vanto","Nisaraya","Pamol","Pipli","Ranoli","Ras","Rudel","Saijpur","Salol","Santokpura","Singlav","Sisva","Sur Kuva","Umlav","Uneli","Vachhiyel","Vadeli","Vahera","Valvod","Vasna ( Borsad)","Vasna ( Ras)","Virsad","Zarola"],
  "Khambhat": ["Akhol","Bamanva","Bhat Talavadi","Bhimtalav","Bhuvel","Chhatardi","Daheda","Dhuvaran","Finav","Golana","Gudel","Haripura","Hariyan","Hasanpura","Jahaj","Jalsan","Jalundh","Jhalapur","Jinaj","Kalamsar","Kali Talavadi","Kanisa","Kanzat","Khadodhi","Khatnal","Kodva","Lunej","Malasoni","Malu","Mitli","Motipura","Nagra","Nana Kalodra","Nandeli","Navagam Bara","Navagam Vanta","Neja","Paldi","Pandad","Piploi","Popatvav","Rajpur","Ralaj","Rangpur","Rohni","Sayama","Sokhada","Tamsa","Tarakpur","Timba","Undel","Vadgam","Vadola","Vainaj","Vasna","Vatadra","Vatra"],
  "Petlad": ["Agas","Amod","Ardi","Ashi","Bamroli","Bandhni","Bharel","Bhatiel","Bhavanipura","Bhurakui","Boriya","Changa","Dantali","Danteli","Davalpura","Demol","Dhairyapura","Dharmaj","Fangani","Ghunteli","Isarama","Jesarva","Jogan","Kaniya","Khadana","Lakkadpura","Mahelav","Manej","Manpura","Morad","Nar","Padgol","Palaj","Pandoli","Porda","Ramodadi","Ramol","Rangaipura","Ravipura","Ravli","Rupiyapura","Sanjaya","Sansej","Shahpur","Shekhadi","Sihol","Silvai","Simarada","Sunav","Sundara","Sundarana","Vadadala","Vatav","Virol(Simarada)","Vishnoli","Vishrampura"],
  "Sojitra": ["Balinta","Bantwa","Bhadkad","Dabhou","Dali","Deva Talpad","Deva Vanta","Devataj","Gada","Isnav","Kasor","Khansol","Kothavi","Limbali","Magharol","Malataj","Meghalpur","Palol","Petli","Piplav","Run","Runaj","Trambovad","Virol (Sojitra)"],
  "Tarapur": ["Adruj","Amaliyara","Bhanderaj","Budhej","Changada","Chikhaliya","Chitarwada","Dugari","Fatepura","Galiyana","Gorad","Indranaj","Isanpur","Isarwada","Jafarganj","Jafrabad","Jalla","Jichka","Kanavada","Kasbara","Khada","Khakhsar","Khanpur","Mahiyari","Malpur","Mil Rampura","Mobha","Moraj","Mota Kalodra","Nabhoi","Pachegam","Padra","Rel","Rinza","Sath","Tarapur","Tol","Untwada","Valandapura","Valli","Vank Talav","Varsada"],
  "Umreth": ["Ahima","Ardi","Ashipura","Badapura","Bechari","Bhalej","Bharoda","Bhatpura","Dagjipura","Dholi","Dhuleta","Fatepura","Gangapura","Ghora","Hamidpura","Jakhala","Khankhanpur","Khankuva","Khorwad","Lingda","Meghva - Badapura","Navapura","Pansora","Parvata","Pratappura","Ratanpura","Saiyadpura","Sardarpura","Shili","Sundalpura","Sureli","Tarpura","Thamna","Untkhari","Vansol","Zala Bordi"]
},
 "Banaskantha": {
  "Amirgadh": ["Ajapur Mota","Ajapur Vanka","Ambapani","Amirgadh","Awal","Awala (Arniwada)","Balundra","Bantawada","Bhamariya","Bhayla","Chikanvas","Dabhchatra","Dabhela","Dabheli","Deri","Dhanpura","Dhanpura (Dholiya)","Dholia","Dungarpura","Gadhada","Ganji","Gavara","Ghanghu","Ghanta","Ghoda","Iqbalgadh","Isvani","Jethi","Jorapura (Amirgadh)","Juni Roh","Juni Roh Sarotri","Kakwada","Kali Mati","Kanpura","Kansaravid","Kapasiya","Karaza","Karmadi","Kengora","Khajuriya","Khapa","Khapara","Khara","Khari","Khemarajiya","Khuniya","Kidotar","Laxmipura (Amirgadh)","Mandaliya","Manpuriya","Nichlo Bandh","Pedcholi","Rabaran","Rabariya","Rajpuriya","Rampura (Vadla)","Sarotra","Savaniya","Sonwadi","Surela","Tadholi","Umarkot","Uplo Bandh","Vagdadi","Vaghoriya","Vera","Virampur","Zaba","Zanzarvav"],
  "Bhabhar": ["Abala","Abasana","Asana","Balodhan","Barvala","Beda","Bhem Bordi","Bhodaliya","Buretha","Chachasana","Chaladara","Chatara","Chembuva","Chichodara","Devkapdi","Dhenkwadi","Gangun","Gosan","Harkudiya","Indarva Juna","Indarva Nava","Jasanwada","Jorvada","Kaprupur","Karela","Khadosan","Khara","Khari Paldi","Kuvala","Lunsela","Manpura Bhabhar","Mera","Mespura","Mitha","Moti Sari","Nesda","Radakiya","Roita","Runi","Sanesda","Sanva","Suthar Nesdi","Tanvad","Tetarva","Ujjanwada","Undai","Vadana","Vadpag","Vajapur Juna","Vajapur Nava","Vavdi"],
  "Danta": ["Abhapura","Aderan (Danta)","Aderan (Mankadi)","Amarpura","Ambaghanta","Amblimal","Amloi","Balvantpura","Bamaniya","Bamnoj","Banodara","Barvas","Beda","Bedapani","Begadiyavas","Bhachadiya","Bhadramal","Bhankhri","Bhanpur","Bhavangadh","Bordiyala","Chhota Bamodara","Chhota Pipodara","Chikhla","Chokibor","Chorasan","Chori","Dabhchatra","Dalpura","Danta","Dericharda","Devaliyavali Vav","Dhabani Vav","Dhagadiya","Dhamanva","Dhareda","Dhrangivas","Dhunali","Divdi","Gadh (Danta)","Gadh (Mahudi)","Gajipur","Ganapipli","Ganchhera","Gangva","Ghantodi","Ghodatankani","Ghorad","Godhani","Gotha","Guda","Hadad","Harigadh","Harivav","Hathi Pagala","Hedo","Jagatapura","Jalana","Jambera","Jamru","Jasvantgadh","Jasvantpura (Danta)","Jasvantpura (Hadad)","Jasvapura (Mankdi)","Javara","Jetvas","Jharivav","Jhumfali","Jitpur","Jodhsar","Jorapura","Kanabiyavas","Kanagar","Kansa","Kantivas","Karanpur","Kengora","Kesarpura","Khaivad","Khandhora","Khantani Magari","Khatal","Kherani Umbari","Khermal","Kheroj","Khokhar Bili","Khokhariyavas","Kodaravi Ranpur","Koteshvar","Koylapur","Kukadi","Kumbhariya","Kundel","Kunvarsi","Lotol","Machakoda","Madhusudanpura","Magvas","Mahobatgadh (Danta)","Mahobatgadh (Hadad)","Mahuda","Mahudi","Mal","Manchhla","Mandali","Mankanchampa","Mankdi","Manpur (Ghorad)","Manpur (Pethapur)","Miranvas","Mor Dungara","Mota Bamodara","Mota Pipodara","Motasada","Motipura","Nagel","Naivada","Nanasada","Nani Tudiya","Nargadh","Navaniya","Navanu Padar","Navavas (Danta)","Navovas (Hadad)","Padaliya","Panchha","Paniyari","Panudara","Pasiya","Pataliya","Pethapur","Pipalavali Vav","Pith (Navanagar)","Pruthvirajgadh","Punjpur","Raghpur","Rangpur","Rani Umbari","Ranika","Ranol","Ranpur","Ratanpur","Rayaniya","Rinchhadi","Ruppura","Rupvas","Samaiya","Sanali","Sandhosi","Santpur","Sarakala","Sarhad Chhapri","Savaipura","Sembal","Sembaliya","Sembalpani","Senkda","Siyavada","Solsanda","Sultanpur","Taleti","Tarangda","Tekari","Thalvada","Thana","Toda","Toraniya","Udavas","Umbara","Umedpura","Unodara","Vadnal","Vadusan","Vadvera","Vagada Kyari","Vaghdacha","Vajasana","Vasi","Vekari","Velvada","Vijalasan","Viramveri","Virpur(Hadad)","Virpur (Lotol)"],
  "Dantiwada": ["Akoli","Arkhi","Atal","Bhadali (Zat)","Bhadli Kotha","Bhakhar Moti","Bhakhar Nani","Bhakodar","Bhandotra","Bhilachal","Bhilada","Chodungri","Dangiya","Dantiwada","Deri","Dhaneri","Dhaniyawada","Fatepura (Dhanawada)","Gangudara","Ganguwada","Ganodara","Godh","Gundari","Hariyawada","Jegol","Jorapura Bhadli","Jorapura (Lodpa)","Kotda (Jegol)","Lakhanasar","Lodpa","Mahudi Moti","Mahudi Nani","Malpuriya","Marwada","Nandotra (Brahmanvas)","Nandotra (Thakorvas)","Nilpur","Odhava","Panswal","Panthawada","Rajkot","Rampura Mahudi","Rampura (Panswal)","Ramsida (Chhapra)","Ranol","Ratanpur","Santarwada","Satsan","Shergadh Odhava","Sikariya","Talenagar","Vadvas","Vaghrol","Vagor","Vavdhara","Velavas","Zat"],
  "Deesa": ["Agdol","Agthala","Akhol Moti","Akhol Nani","Aseda","Baiwada","Balodhar","Bhachalva","Bhadath","Bhadra","Bhadramali","Bhakadiyal","Bhildi","Bhoyan","Bodal","Bural","Chandaji Golia","Chatrala","Chekra","Chhatrala","Chitroda","Chora","Dama","Dasanavas","Davas","Dedol","Deka","Devsari","Dhanavada","Dhanpura","Dharanva","Dharisana","Dharpada","Dhedhal","Dhroba","Dhunsol","Dhuva","Dodana","Fagudra","Fatepura","Gamdi","Genaji Rabari Golia","Ghada","Ghana","Gharnal Moti","Gharnal Nani","Godha","Gugal","Jadiyali","Jasara","Javal","Jhabadiya","Jhakol","Jherda","Jorapura","Juna Deesa","Kamoda","Kamodi","Kanajhara","Kant","Kasari","Katarva","Khadosan","Khentva","Kherola","Kochasana","Kotda","Kotha","Kuchavada","Kuda","Kumpat","Kunvara Padar","Lakhani","Latiya","Laxmipura","Lorvada","Lunpur","Mahadeviya","Malgadh","Manaki","Manekpura","Matu","Meda","Moral","Morthal Golia","Mota Kapra","Mudetha","Nagafana","Nana Kapra","Nandla","Nani","Nava","Nesda Juna","Nesda Nava","Odhava","Paldi","Pamaru","Pechhdal","Peplu","Rampura","Ramsan","Ramun","Ramvas","Ranpur Athamno Vas","Ranpur Ugamno Vas","Ranpur Vachlovas","Rasana Mota","Rasana Nana","Ratanpura","Robas Moti","Robas Nani","Sadarpur","Samau Motavas","Samau Nanavas","Sanath","Sandiya","Sarat","Saviyana","Shamsherpura","Shergadh","Sherganj","Sherpura","Sodapur","Sotambla","Soyla","Sunthiya","Talegadh","Taleganj","Talepura","Tetoda","Thervada","Vadaval","Vadli Farm","Vahara","Vakvada","Varan","Varnoda","Vasada","Vasna (Juna Deesa)","Vasna (Kuda)","Vasna (Vatam)","Velavapura","Viruna","Viruvada","Vithodar","Yavarganj","Yavarpura","Zenal"],
  "Deodar": ["Achhavadiya","Bhadkasar","Bhagwanpura","Bhesana","Boda","Chagwada","Chalva","Chamanpura","Chibhda","Daua","Delwada","Dera","Dhanakwada","Dhrandav","Dhrandvada","Dhunsol","Duchakwada","Forna","Gangol","Goda","Golvi","Golvo","Jada","Jalodha","Jasali","Khanodar","Kotarwada","Kotda Deodar","Kotda Forna","Kunvarva","Kunvata","Kuwana","Lavana","Lembau","Liladhar","Ludara","Makdala","Makhanu","Manpura Dhunsol","Manpura Jalodha","Mesra","Mojru Juna","Mojru Nava","Mulakpur","Narana","Nava","Navapura","Nokha","Odha","Ogadpura","Paldi","Raiya","Rampura","Rantila","Ravel","Salpura","Samla Vadana","Sanadar","Sanav","Sardarpura (Jasali)","Sardarpura (Ravel)","Sesan Juna","Sesan Nava","Soni","Surana","Vadiya","Vajegadh","Vakha","Vatam Juna","Vatam Nava"],
  "Dhanera": ["Aeta","Alwada","Anapur Chhota","Anapurgadh","Asiya","Bapla","Bhajna","Bhatib","Bhatram","Charda","Chhindivadi","Dedha","Dhakha","Dhanpura (Kheda)","Dharnodhar","Dugdol Moti","Dugdol Nani","Edal","Fatepura (Malotra)","Gola","Hadta","Jadi","Jadiya","Janali","Jiwana","Jorapura (Dhakha)","Karadhani","Khangan","Khaprol","Khimat","Kotda (Dhakha)","Kotda (Raviya)","Kumar","Kundi","Kunvarla","Lawara","Lelava","Magarawa","Malotra","Mandal","Mewada","Mota Meda","Nana Meda","Nanuda","Negala","Nenava","Pengiya","Rajoda","Rampura Chhota","Rampura Mota","Rampura (Vaghpura)","Ramuna","Ravi","Raviya","Runi","Sabawadi","Samalwada","Sankad","Saral","Sera","Shergadh (Jadiya)","Silasana","Siya","Sodal","Sotwada","Talegadh","Thawar","Vachhdal","Vachhol","Vaktapura","Valer","Vasan","Vasda","Vinchhivadi","Virol","Voda","Yavarpura"],
  "Kankrej": ["Adhgam","Akoli Maharajvas","Akoli Thakorvas","Amarnesda","Amblivas","Amblun","Anandpura","Anganwada","Arduvada","Arniwada","Balochpura","Bhadrevadi","Bhalgam","Bhavnagar","Bukoli","Changa","Chekhala","Chembla","Chimangadh","Devdarbar","Devpura","Dhanera","Dudasan","Dugrasan","Fategadh","Fatepura","Gothada","Gunthawada(Dalpatpura)","Indramana","Isarva","Jakhel","Jaliya","Jamana Padar","Jotada","Kakar","Kamboi","Kantheriya","Karsanpura","Kasalpura","Kasara","Kashipura","Katediya","Khariya","Khasa","Khengarpura","Khimana(Palodar Na Vas)","Khoda","Khodla","Kudva","Kunvarava","Laxmipura","Maidkol","Mandala","Mangalpura Nagot","Manpur (Shihori)","Manpura (Un)","Mota Jampur","Nagot","Nana Jampur","Nanota","Nasaratpura","Nathpura","Nava","Nekariya","Nekoi","Odha","Padar","Padardi","Rajpur","Ranakpur","Ranawada (Jagiri)","Ranawada (Khalsa)","Raner","Ratangadh","Ratanpura (Shihori)","Ratanpura (Un)","Raviyana","Runi","Ruppura","Ruvel","Samanva","Savpura","Shihori","Shirwada","Shiya","Sohanpura","Sudrosan","Tana","Tatiyana","Tervada","Thali","Totana","Ucharpi","Umbri","Un","Vada","Valpura","Varasada","Vibhanesda","Vithlod","Zalmor"],
  "Palanpur": ["Akedi","Akesan","Aligadh","Ambaliyal","Ambetha","Angola","Antroli","Asmapura (Gola)","Asmapura (Karjoda)","Badargadh","Badarpura (Bhutedi)","Badarpura (Kalusana)","Badarpura (Khodla)","Bhagal (Pipli)","Bhatamal Moti","Bhatamal Nani","Bhatwadi","Bhavisana","Bhutedi","Chadotar","Chandisar","Chekhala","Chitrasani","Dalwada","Delwada","Dhandha","Dhaniyana","Dhelana","Esbipura","Fatepur","Gadh","Galwada","Gathaman","Godh","Gopalpura","Hasanpur","Hathidra","Hebatpur","Hoda","Jadial","Jagana","Jasleni","Jaspuriya","Jorapura Bhakhar","Juvol","Kamalpur","Karjoda","Kharodiya","Khasa","Khemana","Khodla","Kotda (Bhakhar)","Kotda (Chand Gadh)","Kumbhalmer","Kumbhasan","Kumpar","Kushakal","Lalawada","Laxmanpura","Lunwa","Madana (Dangiya)","Madana (Gadh)","Malan","Malana","Malpuriya","Manaka","Manpur (Karjoda)","Merwada (Mahajan)","Merwada (Ratanpur)","Moriya","Mota","Nalasar","Pakhanwa","Parpada","Patosan","Pedagara","Pipli","Pirojpura(Tankani)","Rajpur (Pakhanva)","Rampura (Karaza)","Ranawas","Ranpuriya","Ratanpur","Ruppura","Sadarpur","Sagrosana","Salempura","Salla","Sambarda","Samdhi (Motavas)","Samdhi (Nadhanivas)","Samdhi Ranajivas","Sangla","Sangra","Saripada","Sasam","Sedrasana","Semodra","Songadh","Sundha","Surajpura (Khe)","Takarwada","Talepura (Madana)","Tokariya","Ukarda","Vadhana","Vagda","Varwadia","Vasana","Vasani","Vasda (Fatepur)","Vasda (Mujpur)","Vasna (Jagana)","Vedancha","Virpur"],
  "Tharad": ["Abhepura","Ajawada","Antrol","Arantva","Asasan","Asodar","Betaliya","Bevata","Bhachar","Bhadodar","Bhalasara","Bhapdi","Bhapi","Bhardasar","Bhimgadh","Bhimpura","Bhordu","Bhorol","Bhuriya","Budhanpur","Changada","Charda","Chhanasara","Chotapa","Chudmer","Dantiya","Dedudi","Deduva","Del","Delankot","Detal Darbari","Detal Duva","Didarada","Dipda","Dodgam","Dodiya","Dolatpura","Dudhva","Duva","Gadsisar","Gagana","Ganeshpura","Gela","Ghantiyali","Ghesda","Ghodasar","Hathawada","Idhata","Jadara","Jamda","Jampur","Janadi","Jandla","Jetda","Kamali","Karanpura","Karbun","Karnasar","Kasavi","Kesargam","Khanpur","Kharakhoda","Khengarpura","Khoda","Khorda","Kiyal","Kochala","Kothigam","Kumbhara","Lakhapura","Lalpur","Lendau","Lodhnor","Lorwada","Lunal","Lunawa","Luvara (K)","Madal","Mahadevpura","Mahajanpura","Malupur","Mangrol","Medhala","Meghpura","Miyal","Morila","Morthal","Mota Mesara","Moti Pavad","Nagala","Nana Mesara","Nani Pavad","Nanol","Naroli","Padadar","Pathamda","Patiyasara","Pavadasan","Pepar","Peparal","Piluda","Pirgadh","Radka","Rah","Rajkot","Rampura","Ranesari","Ranpur","Ratanpura","Saba","Sanadhar","Sanavia","Savarakha","Savpura","Sedla","Sherau","Sidhotara","Takhuva","Taruwa","Terol","Thara","Therwada","Undrana","Untveliya","Vadgamda","Vaghasan","Vajegadh","Valadar","Vami","Vantdau","Vara","Vedala","Zenta"],
  "Vadgam": ["Amadpura (Ghodiyal)","Amadpura (Mumanvas)","Amirpura","Andhariya","Badarpura","Basu","Bavalchudi","Bhakhari","Bhalgam","Bhangrodiya","Bharkawada","Bharod","Bhatvas","Bhukhla","Changa","Changwada","Chhaniyana","Chitroda","Dalvana","Dhanali","Dhanpura","Dharewada","Dhori","Dhota","Edrana","Fategadh","Ghodiyal","Gidasan Moti","Gidasan Nani","Hadmatiya","Harde Vasana","Hasanpur","Hatavad","Iqbalgadh","Iqbalpura","Islampura","Jalotra","Joita","Juni Nagari","Juni Sendhani","Kabirpura","Kaleda","Kamalpura","Karasanpura","Karnala","Kodarali","Kodaram","Kotadi","Limboi","Magarwada","Mahi","Majadar","Majatpur","Malosana","Manpura","Megal","Mejarpura","Memadpur","Mepada","Meta","Mokeshvar","Moriya","Moteta","Motipura","Mumanvas","Nagana","Nagarpura","Nalasar","Nandotra","Nanosana","Navi Nagari","Navi Sendhani","Navisana","Navo Vas","Nizampura","Paldi","Panchada","Pandva","Parkhadi","Pasvadal","Pavthi","Pepol","Pilucha","Pirojpura","Rajosana","Rupal","Sabalpura","Sakalana","Salemkot","Samsherpura","Sardarpura","Sherpura (Majadar)","Sherpura (Sembhar)","Sisrana","Sukhpura","Tajpura","Teniwada","Thalwada","Thuvar","Timbachudi","Umrecha","Vadgam","Vagadadi","Vansol","Varasada","Varnawada","Varvadiya","Vasana (Sembhar)","Vesa"],
  "Vav": ["Achhuva","Akoli","Arjanpura","Asaragam","Asaravas","Bahisara","Baiyak","Baluntri","Baradvi","Benap","Bhachali","Bhadvel","Bhakhari","Bhankhod","Bharadava","Bhatasana","Bhatvar Vas","Bhatvargam","Boru","Bukna","Chala","Chandangadh","Chandarva","Chatarpura","Chothar Nesda","Chotil","Chuva","Dabhi","Daiyap","Dendava","Dethali","Devpura (Suigam)","Devpura (Talsari)","Dhanana","Dharadhara","Dheriana","Dhima","Dhrechana","Dudhva","Dudosan","Dungala","Eta","Fangadi","Gambhirpura","Garambadi","Golap","Golgam","Haripura","Harsad","Ishvariya","Jaloya","Janavada","Jelana","Jorawargadh","Jordiyali","Kalyanpura","Kanothi","Kareli","Katav","Khadol","Khardol","Khimana Padar","Khimanavas","Kolava","Koreti","Kumbhardi","Kumbharkha","Kundaliya","Lalpura","Limbala","Limbuni","Lodrani","Madhpura","Madka","Malsan","Mamana","Masali","Mavsari","Meghpura","Mithavi Charan","Mithavi Rana","Morikha","Morwada","Motipura","Nadabet","Nalodar","Navapura","Nesda (Go)","Padan","Panesada","Pratappura","Rabadi Padar","Rachhava","Raiyavan","Rampur","Sajoi","Sangasar","Sankarpura","Simamoi","Singawali","Surpur (Umariya)","Taramkach","Tokarva","Udhal Mahuda","Ulkadar","Umariya","Undar","Vakasiya","Vakota","Vasiya Dungari","Ved","Zabu"],
  "Fatepura": ["Affava","Apatalai","Aspur","Bachkariya (East)","Balaiya","Bariyani Hathod","Barsaleda","Bavani Hathod","Bhat Muvadi","Bhichor","Bhitodi","Bhojela","Chhalor","Chikhali","Dablara","Dhadhela","Dungar","Dungra","Dungrana Pani","Fategadhi","Fatepura Alias Valunda","Gadra","Gava Dungra","Ghani Khunt","Ghata Vadiya (East)","Ghughas","Hadmat","Hindoliya","Hingla","Inta","Jagola","Jalai","Jatanana Muvada","Javesi","Jhab (East)","Kaliya (Lakhanpur)","Kankasiya","Kanthagar","Karmel","Karodiya Purva Fatepura)","Khakhariya","Khatarpur Na Muvada","Kumana Muvada","Kundla","Kupda","Lakhanpur","Limadiya","Madhva","Makwana Na Varuna","Manawala Borida","Margala","Mor Mahudi","Mota Borida","Mota Natava","Moti Bara","Nana Borida","Nana Natava","Nani Bara","Nani Charoli","Nani Dhadheli","Nani Nandukan","Nanirel (East)","Nava Talav","Navagam","Nes Damorni","Nindka (East)","Padaliya","Patadiya","Pati","Patisara","Patvel","Piplara","Pipliya","Ratanpur (Nes)","Raval Na Varuna","Rupakheda","Sagdapada","Salara","Sarsawa (East)","Sukhsar","Tadhigoli","Vadvas","Vaghvadla","Valundi","Vankiya","Vansiyada","Vasna (East)","Vavdi (East)","Zer"],
  "Garbada": ["Abhlod","Ambli","Bharsada","Bhe","Bhutardi","Boriyala","Chandla","Chharchhoda","Dadur","Devdha","Gangarda","Gangardi","Garbada","Gulbar","Jambua","Jesawada","Matwa","Minakyar","Nadhelav","Nalwai","Nandva","Nelsur","Nimach","Panchwada","Pandadi","Patiya","Patiya Zol","Sahada","Simaliya Bujarg","Tunki Anop","Tunki Vaju","Vadva","Vajelav","Zari Bujarg"],
  "Jhalod": ["Amba","Amba Jharan","Anika","Anvarpura","Bachkariya","Bajarvada","Bambela","Bhaman","Bhamela","Bhanpur","Bilwani","Boda Dungar","Bodiya Bhint","Chakaliya","Chakisana","Chamariya","Chandana Muvada","Chatka","Chhasiya","Chhayan","Chitrodiya","Dageriya","Dantiya","Devjini Sarasvani","Dhalsimal","Dhamena","Dhara Dungar","Dhavadiya","Dhavdi Faliya","Dhedhiya","Dhedhiyano Nalo","Dhola Khakhara","Doka Talavdi","Doki","Dungra","Dungri","Fulpura","Galana Pad","Gamdi","Garadiya","Garadu","Gasali","Ghensva","Ghodiya","Golana","Govinda Talai","Gultora","Hadmat Khunta","Hirola","Itadi","Jafarpura","Jaror","Jasuni","Jetpur","Jitpura","Kachaldhara","Kadval","Kadvana Pad","Kakreli","Kaligam (Gujar)","Kaligam (Inami)","Kaliya Talav","Kaljini Sarsavani","Kalyanpura","Kanji Khedi","Kankara Kuva","Karamba","Karath","Kavdana Muvada","Khakhariya","Kharsana","Kharvani","Kheda","Kota","Kotda","Kunda","Kuni","Lavariya","Lilva Deva","Lilva Pokar","Lilva Thakor","Limdi","Lunjana Muvada","Maghanisar","Mahudi","Malvasi","Mandli","Melaniya","Mirakhedi","Moli","Mota Kaliya","Moti Handi","Mudaheda","Munavani","Nana Kaliya","Nana Handi","Nana Mal","Nani Bandibar","Nani Sanjeli","Nani Vasvani","Nani Vav","Nava Vadiya","Navagam","Navipuri","Ninamana Khakhariya","Ninamani Vav","Pada","Padaliya","Padola","Pahad","Palla","Palli","Pania","Panivela","Panta","Parmarna Dungarpur","Parmarna Kharkhariya","Parpata","Patangadi","Patdi","Patwan","Pipaliya","Piplapani","Pipli","Pisoi","Polisimal","Pratappura","Rai","Randhikpur","Ranipura","Rathodna Dungarpur","Sakariya","Sangiya","Sarjumi","Sati Faliya","Shasta","Singapur","Singvad","Sudiya","Surpur (Randhikpur)","Tarmi","Timba","Toyani","Tunta Ghati","Umedpura","Usra","Vadapipla","Vadela","Vaghnala","Vala Gota","Valundi","Vanjhariya","Vateda","Vislanga","Zaliya Pada","Zarola (Du)","Zarola (Randhikpur)"],
  },
   
 "Gandhinagar": {
  "Dehgam": ["Ahmadpur","Amarjina Mavda","Anguthala","Antoli","Antroli","Arajanjina Mavda","Babra","Badpur","Bahiyel","Bardoli (Bariya)","Bardoli (Kothi)","Bariya","Bhadroda","Bilamna","Chamla","Chekhalpagi","Chiskari","Demalia","Devkaran Na Mavda","Dharisana","Dod","Dumecha","Ghamij","Halisa","Harakhjina Mavda","Harsoli","Hathijan","Hilol","Hilol Vasna","Isanpur Dodiya","Jaliano Math","Jalundra Mot","Jindva","Jivajini Mavadi","Kadadra","Kajodra","Kalyanji Na Mavda","Kamalband Vasna","Kanipur","Kantharpur","Karoli","Khadiya","Khanpur","Kodrali","Krishnanagar","Lawad","Lihoda","Machhang Mot","Machhang Nani","Mahudiya","Meghraj Na Mavda","Mirpur","Mirjapur","Mithana Mavda","Mosampur","Moti Pawthi","Motipura","Najupura","Nana Jalundra","Nandol","Navanagar","Ottampur","Pahadiya","Paliya","Pallano Math","Palundra","Pasuniya","Patna Kunva","Piplaj","Rakhiyaal","Ramnagar","Sagdalpur","Sahebji Na Mavda","Salki","Sambela","Sametri","Sampa","Sanoda","Shiyapur","Shiyawada","Sujana Mavda","Thadakunva","Udan","Vadod","Vadwasa","Vardhna Mavda","Vasna Chaudhri","Vasna Rathod","Vasna Sogthi","Vatva","Velpura","Zhak"],
  "Gandhinagar": ["Adraj Mot","Alampur","Bhoyan Rathod","Chandrala","Chekhalarani","Chhala","Chiloda (Dabhoda)","Dabhoda","Dantali","Dashela","Dhanap","Dolrana Vasna","Galudan","Giyod","Isanpur Mot","Jakhora","Jalund","Khoraj","Lawarpur","Lekavada","Limbadia","Madhavgadh","Magodi","Mahudara","Medra","Nava Dharmpur","Pindrada","Piplaj","Pirojpur","Prantiy","Pundarsan","Rajpur","Randhija","Ratanpur","Rupal","Sadra","Saradhav","Shahpur","Shiholi Mot","Sonarda","Sonipur","Titoda","Unava","Vadodra","Valad","Vankanerda","Vasan","Veera Talavdi"],
  "Kalol": ["Pratappura","Rampura","Adhna","Aluva","Amja","Balva","Bhadol","Bhavpura","Bhimsan","Bhoyan Mot","Bileshvarpura","Chandisa","Dantali","Dhamasna","Dhanaj","Dhendu","Dingucha","Ganpatpura","Golthara","Hajipur","Himmatpura","Isand","Itla","Jamla","Jaspur","Jethlaj","Kantha","Karoli","Khatraj","Khorajdabhi","Limbodara","Mokhasan","Mubarkpura","Mulasana","Nadri","Nandoli","Nardipur","Nasmed","Nava","Paliyad","Palodia","Palsana","Pansar","Piyaj","Pratappura","Rakanpur","Ramnagar","Rancharada","Ranchodpura","Sabasapur","Sanavad","Santej","Sherisa","Sobasan","Soja","Unli","Usmanabad","Vadavaswami","Vadsar","Vagosana","Vansjada","Vansjada Dhediya","Vyana","Veda"]
}

};

const districtSelect = document.getElementById('district');
const talukaSelect = document.getElementById('taluka');
const villageSelect = document.getElementById('village');

if(districtSelect && talukaSelect && villageSelect){
    districtSelect.innerHTML = '<option value="">Select District</option>';
    for(let d in gujaratData){
        let opt = document.createElement('option'); 
        opt.value=d; 
        opt.textContent=d; 
        districtSelect.appendChild(opt);
    }
    districtSelect.addEventListener('change', function(){
        talukaSelect.innerHTML='<option value="">Select Taluka</option>'; 
        villageSelect.innerHTML='<option value="">Select Village</option>';
        const talukas = gujaratData[this.value]||{};
        for(let t in talukas){ 
            let opt = document.createElement('option'); 
            opt.value=t; 
            opt.textContent=t; 
            talukaSelect.appendChild(opt);
        }
    });
    talukaSelect.addEventListener('change', function(){
        villageSelect.innerHTML='<option value="">Select Village</option>';
        const selectedDistrict = districtSelect.value;
        const villages = (gujaratData[selectedDistrict] && gujaratData[selectedDistrict][this.value])||[];
        for(let v of villages){ 
            let opt = document.createElement('option'); 
            opt.value=v; 
            opt.textContent=v; 
            villageSelect.appendChild(opt);
        }
    });
}

// ---------------- Real-time Validation ----------------
document.addEventListener('DOMContentLoaded', ()=>{
    const form = document.getElementById('registerForm');
    const fields = ['name','email','password','mobile','district','taluka','village','role'];

    // Password hint below password box
    const passwordInput = document.getElementById('password');
    let passwordHint = passwordInput.nextElementSibling;
    if(!passwordHint || !passwordHint.classList.contains('password-hint')){
        passwordHint = document.createElement('div');
        passwordHint.classList.add('password-hint');
        passwordInput.parentNode.insertBefore(passwordHint, passwordInput.nextSibling);
    }
    passwordHint.style.fontSize = "14px";
    passwordHint.style.marginTop = "4px";

    fields.forEach(f=>{
        const input = form.querySelector(`[name="${f}"]`);
        if(input){
            input.addEventListener('input',()=>validateField(input));
            input.addEventListener('change',()=>validateField(input));
        }
    });

    function validateField(input){
        let val = input.value.trim();
        let msg = '';
        switch(input.name){
            case 'name': if(val==='') msg='Name is required'; break;
            case 'email': 
                if(val==='') msg='Email is required'; 
                else if(!/^[^@]+@[^@]+\.[^@]+$/.test(val)) msg='Enter valid email'; 
                break;
            case 'password':
                const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/;
                if(val==='') msg='Password is required';
                else if(val.length<6) msg='Password must be at least 6 characters';
                else if(!pattern.test(val)) msg='Password must include uppercase, lowercase & number';
                else msg='valid'; // indicates password valid
                break;
            case 'mobile': 
                if(val==='') msg='Mobile is required'; 
                else if(!/^\d{10}$/.test(val)) msg='Enter valid 10-digit mobile'; 
                break;
            case 'district': if(val==='') msg='Select District'; break;
            case 'taluka': if(val==='') msg='Select Taluka'; break;
            case 'village': if(val==='') msg='Select Village'; break;
            case 'role': if(val==='') msg='Select Role'; break;
        }

        // Show message below field
        let errDiv = input.nextElementSibling;
        if(input.name==='password'){ 
            errDiv = passwordHint; // password uses hint div
        }
        if(errDiv){
            if(msg==='valid'){
                errDiv.style.color = 'green';
                errDiv.textContent = 'Password format is valid ✅';
            } else {
                errDiv.style.color = 'red';
                errDiv.textContent = msg;
            }
        }
    }

    // Prevent submit if any field invalid
    form.addEventListener('submit', e=>{
        let hasError=false;
        fields.forEach(f=>{
            const input=form.querySelector(`[name="${f}"]`);
            if(input && input.value.trim()===''){ 
                validateField(input); 
                hasError=true;
            }
        });
        if(hasError) e.preventDefault();
    });
});
</script>
</body>
</html>
