<?php
session_start();
require_once '../db/db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;
            header("location: main.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that username.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Login - Happy Paws</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=10" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
      .auth-container { max-width: 400px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
      .auth-container input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
      .auth-container input[type="submit"] { background: #4caf50; color: white; border: none; cursor: pointer; font-size: 16px; }
      .auth-container input[type="submit"]:hover { background: #45a049; }
      .error-msg { color: red; margin-bottom: 15px; }
      .text-center { text-align: center; }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <main>
    <div class="auth-container">
      <h2 class="text-center">Account Login</h2>
      <?php if(!empty($error)){ echo '<div class="error-msg">'.$error.'</div>'; } ?>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label>Username</label>
        <input type="text" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login" class="glassy-btn">
        <p class="text-center">Don't have an account? <a href="user_register.php">Register now</a>.</p>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2026 Happy Paws Veterinary Clinic. All rights reserved.</p>
  </footer>
</body>
</html>
