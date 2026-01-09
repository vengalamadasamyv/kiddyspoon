<?php
session_start();
include 'db.php';
include 'send-otp.php';

$exists = false;
$otpError = false;
$registerSuccess = false;

/* =====================
   STEP 1: SEND OTP
===================== */
if (isset($_POST['register'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile   = mysqli_real_escape_string($conn, $_POST['mobile']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = "SELECT id FROM users WHERE email='$email' OR mobile='$mobile'";
    $res = mysqli_query($conn, $check);

    if (mysqli_num_rows($res) > 0) {
        $exists = true;
    } else {

        $otp = rand(100000, 999999);

        $_SESSION['otp_data'] = [
            'username' => $username,
            'email'    => $email,
            'mobile'   => $mobile,
            'password' => $password,
            'otp'      => $otp,
            'expires'  => time() + 300
        ];

        sendOTP($email, $otp);
    }
}

/* =====================
   STEP 2: VERIFY OTP
===================== */
if (isset($_POST['verify_otp'])) {

    if (!isset($_SESSION['otp_data'])) {
        header("Location: index.php");
        exit;
    }

    if (time() > $_SESSION['otp_data']['expires']) {
        session_destroy();
        header("Location: register.php");
        exit;
    }

    if ($_POST['otp'] == $_SESSION['otp_data']['otp']) {

        $d = $_SESSION['otp_data'];

        mysqli_query($conn, "
            INSERT INTO users (username, email, password, mobile)
            VALUES (
              '{$d['username']}',
              '{$d['email']}',
              '{$d['password']}',
              '{$d['mobile']}'
            )
        ");

        unset($_SESSION['otp_data']);
        $registerSuccess = true;

    } else {
        $otpError = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register | Kiddy Spoon</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body{background:#f06a2f;font-family:Poppins;  
  display:flex;
  justify-content:center;
  align-items:center;
  flex-direction:column;}
.box{
  max-width:360px;
  margin:30px auto;
  background:#fff;
  padding:30px;
  border-radius:16px;
  display:flex;
  justify-content:center;
  align-items:center;
  flex-direction:column;
}
input,button{
  width:90%;
  padding:12px;
  border:1px solid #ddd;
  border-radius:8px;
  margin:10px 0;
}
button{
  background:#f06a2f;
  color:#fff;
  border:none;
  width:100%;
  border-radius:25px;
}
</style>
</head>

<body>
<img src="../asset/tfd.png" width="200px" style="  padding: 5px 10px;
  border-radius:20px;
  background-color:#fff;">
<div class="box">
<h2>Create Account</h2>

<?php if (!isset($_SESSION['otp_data'])): ?>
<form method="post">
  <input name="username" placeholder="Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="text" name="mobile" placeholder="Mobile Number" pattern="[0-9]{10}" required>
  <input type="password" name="password" placeholder="Password" required>
  <button name="register">Register</button>
</form>
<?php endif; ?>

<?php if (isset($_SESSION['otp_data'])): ?>
<form method="post">
  <input type="text" name="otp" placeholder="Enter Email OTP" required>
  <button name="verify_otp">Verify OTP</button>
</form>
<?php endif; ?>

<a href="index.php" style="text-decoration:none; color:#f06a2f;"><span style="color:#000;">Have any account ?</span>&nbsp;Back to Login</a>
</div>

<!-- SWEET ALERTS -->
<?php if ($exists): ?>
<script>
Swal.fire({
  icon:'warning',
  title:'Already Exists!',
  text:'Email or Mobile already registered',
  confirmButtonColor:'#f06a2f'
});
</script>
<?php endif; ?>

<?php if ($otpError): ?>
<script>
Swal.fire({
  icon:'error',
  title:'Invalid OTP',
  text:'Please enter correct OTP',
  confirmButtonColor:'#f06a2f'
});
</script>
<?php endif; ?>

<?php if ($registerSuccess): ?>
<script>
Swal.fire({
  icon:'success',
  title:'Registration Successful!',
  text:'Email verified. Redirecting to login...',
  confirmButtonColor:'#f06a2f'
}).then(() => {
  window.location.href = 'index.php';
});
</script>
<?php endif; ?>

</body>
</html>
