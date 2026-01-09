<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$username = $_SESSION['user'];

$query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user = mysqli_fetch_assoc($result);
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Kiddy Spoon</title>
<link rel="stylesheet" href="./index.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>

<div class="dashboard">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="profile">
      <img src="../../asset/tfd.png" alt="User" width="100%">
    </div>

    <nav>
      <a href="./">Dashboard</a>
      <a class="active" href="./cart.php">Cart</a>
      <a href="./order.php">My Orders</a>
      <a href="./account.php">Account</a>
    </nav>

    <button class="logout" onclick="window.location.href='./logout.php'">Logout</button>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="content">

    <!-- TOP BAR -->
    <div class="topbar">
      <span onclick="window.location.href='./account.php'"><?php echo htmlspecialchars($user['username']); ?></span>
      <img src="https://i.pravatar.cc/40" class="avatar" onclick="window.location.href='./account.php'">
    </div>

    <!-- GRID -->
    <div class="grid">

      <!-- WELCOME CARD -->
      <div class="card welcome">
        <div>
          <h2>Welcome back, <strong><?php echo htmlspecialchars($user['username']); ?></strong></h2>
          <p>Use Below Code to Avail Extra 5% Off</p>
          <button class="primary">KS100</button>
        </div>
        <img src="../../asset/dash.png">
      </div>

      <!-- UPGRADE -->
      <div class="upgrade">
        <img src="../../asset/b.png" width="100%">
      </div>
    </div>



  </main>
</div>

<script>
document.querySelectorAll('.qty-box').forEach(box => {
  const input = box.querySelector('.qty-input');
  const plus = box.querySelector('.plus');
  const minus = box.querySelector('.minus');

  plus.addEventListener('click', () => {
    input.value = parseInt(input.value) + 1;
  });

  minus.addEventListener('click', () => {
    if (parseInt(input.value) > 1) {
      input.value = parseInt(input.value) - 1;
    }
  });
});
</script>


</body>
</html>

