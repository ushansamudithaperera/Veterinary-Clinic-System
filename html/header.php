<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$role = isset($_SESSION["role"]) ? $_SESSION["role"] : '';
?>
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Emergency Banner -->
<div class="emergency-banner">
  <div class="emergency-marquee">
    <i class="fas fa-phone-alt"></i> 24/7 Emergency Hotline: (123) 456-PETS | For life-threatening emergencies
  </div>
</div>

<header style="display: flex; justify-content: space-between; align-items: center;">
  <div class="clinic-logo">
    <span class="paw-print">🐾</span>
    <h1>Happy Paws Veterinary Clinic</h1>
    <span class="paw-print">🐾</span>
  </div>

  <div class="header-right" style="display: flex; align-items: center; gap: 40px; margin-left: auto;">
    <nav>
      <ul style="display: flex; gap: 15px; list-style: none; margin: 0; padding: 0;">
        <li><a href="main.php"><i class="fas fa-home"></i> Home</a></li>
        
        <?php if ($isLoggedIn && $role === 'Pet Owner'): ?>
          <li><a href="adoption.php"><i class="fas fa-heart"></i> Pet Adoption</a></li>
          <li><a href="appoinment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a></li>
          <li><a href="register.php"><i class="fas fa-user-plus"></i> Register Your Pet</a></li>
        <?php endif; ?>

        <?php if ($isLoggedIn && $role === 'Doctor'): ?>
          <li><a href="vaccination.php"><i class="fas fa-syringe"></i> Vaccination</a></li>
          <li><a href="view_pets.php"><i class="fas fa-paw"></i> View Registered Pets</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <div class="auth-buttons" style="display: flex; gap: 15px;">
      <?php if (!$isLoggedIn): ?>
        <a href="user_login.php" style="color: #F3F4F6; text-decoration: none; font-weight: 500; font-size: 0.95rem; padding: 10px 16px; display: flex; align-items: center; gap: 8px;"><i class="fas fa-sign-in-alt" style="color: var(--secondary);"></i> Login</a>
        <a href="user_register.php" style="color: #F3F4F6; text-decoration: none; font-weight: 500; font-size: 0.95rem; padding: 10px 16px; display: flex; align-items: center; gap: 8px;"><i class="fas fa-user-circle" style="color: var(--secondary);"></i> Create Account</a>
      <?php else: ?>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a>
      <?php endif; ?>
    </div>
  </div>
</header>
