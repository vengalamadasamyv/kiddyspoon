<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$msg = "";

/* ---------------- SEND OTP FUNCTION ---------------- */
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'vengalamadasamy.ctf@gmail.com';
        $mail->Password   = 'azmrrjheioeigciv'; // app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('kiddiyspoon@info.com', 'Kiddy Spoon');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <h3>Password Reset Request</h3>
            <p>Your OTP:</p>
            <h2 style='color:#f06a2f'>$otp</h2>
            <p>Valid for 5 minutes</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/* ---------------- STEP 1 : SEND OTP ---------------- */
if (isset($_POST['send_otp'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) == 0) {
        $msg = "Email not registered";
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['otp'] = rand(100000, 999999);
        $_SESSION['otp_time'] = time();

        sendOTP($email, $_SESSION['otp']);
        $msg = "OTP sent to your email";
    }
}

/* ---------------- RESEND OTP ---------------- */
if (isset($_POST['resend_otp']) && isset($_SESSION['email'])) {

    // Expire old OTP
    unset($_SESSION['otp'], $_SESSION['otp_time']);

    $_SESSION['otp'] = rand(100000, 999999);
    $_SESSION['otp_time'] = time();

    sendOTP($_SESSION['email'], $_SESSION['otp']);
    $msg = "New OTP sent to your email";
}

/* ---------------- RESET PASSWORD ---------------- */
if (isset($_POST['reset_password'])) {

    if (!isset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['email'])) {
        $msg = "Session expired. Please try again.";
    } else {

        $otp_entered = trim($_POST['otp']);
        $password = $_POST['password'];

        // Password strength validation (server-side)
        if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[^A-Za-z0-9]/', $password)
        ) {
            $msg = "Password too weak";
        }
        // OTP expiry (5 minutes)
        elseif (time() - $_SESSION['otp_time'] > 300) {
            $msg = "OTP expired";
        }
        // OTP match
        elseif ($otp_entered == $_SESSION['otp']) {

            $newpass = password_hash($password, PASSWORD_DEFAULT);
            $email = mysqli_real_escape_string($conn, $_SESSION['email']);

            mysqli_query($conn,
                "UPDATE users SET password='$newpass' WHERE email='$email'"
            );

            // Clear OTP data only
            unset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['email']);

            echo "<script>
                alert('Password reset successful');
                window.location.href='index.php';
            </script>";
            exit;
        } else {
            $msg = "Invalid OTP";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{background:#f06a2f;font-family:Poppins;  
  display:flex;
  justify-content:center;
  align-items:center;
  flex-direction:column;}
.card{
  max-width:380px;
  margin:30px auto;
  background:#fff;
  padding:30px;
  border-radius:16px;
}
input,button{
  width:92%;
  padding:12px;
  margin-top:10px;
}
button{
  background:#f06a2f;
  color:#fff;
  border:none;
  width:100%;
  border-radius:6px;
  cursor:pointer;
}
.resend{
  background:#ccc;
}
.timer{
  text-align:center;
  margin-top:10px;
  color:#555;
}
small{font-size:12px}
</style>
</head>

<body>
<img src="../asset/tfd.png" width="200px" style="  padding: 5px 10px;
  border-radius:20px;
  background-color:#fff;">
<div class="card">
<h2>Forgot Password</h2>
<p style="color:red"><?= $msg ?></p>

<?php if (!isset($_SESSION['otp'])): ?>

<form method="post">
  <input type="email" name="email" placeholder="Registered Email" required>
  <button name="send_otp" style="margin-bottom:20px">Send OTP</button>
</form>

<?php else: ?>

<form method="post">
  <input type="text" name="otp" placeholder="Enter OTP" required>
  <input type="password" name="password" placeholder="New Password" required>
  <button name="reset_password">Reset Password</button>
</form>

<form method="post">
  <button id="resendBtn" class="resend" name="resend_otp" disabled>
    Resend OTP
  </button>
</form>

<div class="timer" id="timer">Resend OTP in 120s</div>

<?php endif; ?>

<a href="index.php" style="text-decoration:none; color:#000;">Back to Login</a>
</div>

<script>
/* PASSWORD STRENGTH (CLIENT SIDE) */
const pwd = document.querySelector("input[name='password']");
if (pwd) {
  const msg = document.createElement("small");
  pwd.after(msg);

  pwd.addEventListener("keyup", () => {
    const v = pwd.value;
    const strong =
      v.length >= 8 &&
      /[A-Z]/.test(v) &&
      /[a-z]/.test(v) &&
      /[0-9]/.test(v) &&
      /[^A-Za-z0-9]/.test(v);

    msg.style.color = strong ? "green" : "red";
    msg.innerText = strong
      ? "Strong password ✔"
      : "8+ chars, upper, lower, number & symbol required";
  });
}

/* RESEND OTP TIMER */
let timeLeft = 120;
const timer = document.getElementById("timer");
const resendBtn = document.getElementById("resendBtn");

if (timer && resendBtn) {
  const interval = setInterval(() => {
    timeLeft--;
    timer.innerText = `Resend OTP in ${timeLeft}s`;

    if (timeLeft <= 0) {
      clearInterval(interval);
      resendBtn.disabled = false;
      resendBtn.style.background = "#25D366";
      timer.innerText = "";
    }
  }, 1000);
}
</script>

</body>
</html>
