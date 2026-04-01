<?php
require_once '../db/db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];

    if (empty($username) || empty($password) || empty($confirm_password) || empty($role)) {     
        $error = "Please fill all fields.";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");      
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "This username is already taken. Please choose another one.";
        } else {
            // Close the previous statement
            $stmt->close();

            // Insert new user
            $param_password = password_hash($password, PASSWORD_DEFAULT);       
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $param_password, $role);
            if ($stmt->execute()) {
                header("Location: user_login.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again later.";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Registration - Happy Paws</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=10" />
  <style>
      .auth-container { max-width: 400px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
      .auth-container input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
      .auth-container input[type="submit"] { background: #4caf50; color: white; border: none; cursor: pointer; font-size: 16px; }
      .auth-container input[type="submit"]:hover { background: #45a049; }
      .error-msg { color: red; margin-bottom: 15px; }
      .success-msg { color: green; margin-bottom: 15px; }
      .text-center { text-align: center; }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <main>
    <div class="auth-container">
      <h2 class="text-center">Create an Account</h2>
      <?php 
        if(!empty($error)){ echo '<div class="error-msg">'.$error.'</div>'; } 
        if(!empty($success)){ echo '<div class="success-msg">'.$success.'</div>'; } 
      ?>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label>Username</label>
        <input type="text" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <label>Account Type</label>
        <select name="role" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <option value="Pet Owner">Pet Owner</option>
            <option value="Doctor">Doctor</option>
        </select>

        <input type="submit" value="Register" class="glassy-btn">
        <p class="text-center">Already have an account? <a href="user_login.php">Login here</a>.</p>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2026 Happy Paws Veterinary Clinic. All rights reserved.</p>
  </footer>
</body>
</html>
