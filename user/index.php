<?php
session_start();
include 'db.php';

$loginSuccess = false;
$loginError = "";

if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username' OR email='$username' OR mobile='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = $row['username'];
            session_regenerate_id(true); // security
            $loginSuccess = true;
        } else {
            $loginError = "Invalid password";
        }
    } else {
        $loginError = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login | Kiddy Spoon</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- ✅ SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body{
  background:#f06a2f;
  font-family:Poppins,sans-serif;
  min-height:100vh;
  display:flex;
  justify-content:center;
  flex-direction:column;
  align-items:center;
  width:auto;
  gap:30px;
}
.log{
  width:200px;    
  padding: 5px 10px;
  border-radius:20px;
  background-color:#fff;
}
.login-box{
  max-width:360px;
  background:#fff;
  padding:30px;
  border-radius:16px;
}
input{
  width:90%;
  padding:12px;
  margin:10px 0;
  border-radius:8px;
  border:1px solid #ddd;
}
button{
  width:100%;
  padding:12px;
  background:#f06a2f;
  border:none;
  color:#fff;
  border-radius:25px;
  font-weight:600;
}
a{
  color:#f06a2f;
  text-decoration:none;
  font-size:14px;
}
</style>
</head>

<body>
<a href="../"><img src="../asset/tfd.png" class="log"></a>
<div class="login-box">
<h2 style="text-align:center;">Login</h2>

<form method="post">
<input type="text" name="username" placeholder="Registered Gmail" required>
<input type="password" name="password" placeholder="Password" required>
<a href="forgot.php">Forgot Password?</a>
<button name="login" style="margin-top:10px;">Login</button>
</form>

<p style="margin-top:10px;">
<a href="register.php"><span style="color:black;">New User ?</span>&nbsp;Create New Account</a>
</p>
</div>

<?php if ($loginSuccess): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Login Successful!',
  text: 'Welcome to Kiddy Spoon Admin',
  confirmButtonColor: '#f06a2f'
}).then(() => {
  window.location.href = "./dashboard/index.php";
});
</script>
<?php endif; ?>

<?php if (!empty($loginError)): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Login Failed',
  text: '<?= $loginError ?>',
  confirmButtonColor: '#f06a2f'
});
</script>
<?php endif; ?>

</body>
</html>
