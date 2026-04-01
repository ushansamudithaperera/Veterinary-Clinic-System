<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Doctor"){
    header("location: user_login.php");
    exit;
}
require_once '../db/db_connect.php';

$sql = "SELECT * FROM pets";
$sql_appointment = "SELECT * FROM appointments";
$result = $conn->query($sql);
$result_appointment = $conn->query($sql_appointment);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Pets & Appointments - Happy Paws</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=10" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>


  <?php include 'header.php'; ?>

  <!-- Main Content -->
  <main>
    <section>
      <h2>Registered Pets</h2>
      <?php if ($result->num_rows > 0): ?>
        <div class="pet-showcase" style="margin-top:20px; display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
               // Fallback image if missing
               $img_src = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : '../images/default_pet.png';
            ?>
            <div class="pet-card-modern" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 280px; text-align: center; border: 1px solid rgba(0,0,0,0.05);">
              <div style="background: #f8fafc; height: 220px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <img src="<?= $img_src . (file_exists($img_src) ? '?v='.filemtime($img_src) : '') ?>" alt="Pet Image" style="max-width: 100%; max-height: 220px; object-fit: contain; display: block;">
              </div>
              <div style="padding: 20px;">
                <h3 style="margin: 0 0 5px 0; color: #1e293b; font-size: 1.4rem;"><?= htmlspecialchars($row['pet_name']) ?></h3>
                <p style="margin: 0 0 15px 0; color: #10b981; font-weight: bold; font-size: 0.95rem; text-transform: uppercase;"><?= htmlspecialchars($row['pet_type']) ?> &bull; <?= htmlspecialchars($row['breed']) ?></p>
                
                <div style="text-align: left; font-size: 0.9rem; color: #475569; margin-bottom: 0px; line-height: 1.4; background: rgba(0,0,0,0.03); padding: 10px; border-radius: 8px;">
                  <strong style="color: #334155;">Owner:</strong> <?= htmlspecialchars($row['owner_name']) ?> <br>
                  <strong style="color: #334155;">Age:</strong> <?= htmlspecialchars($row['age']) ?> <br>
                  <strong style="color: #334155;">Notes:</strong> <?= htmlspecialchars($row['notes']) ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="warning-box">No pet records found.</div>
      <?php endif; ?>
    </section>

    <section>
      <h2>Upcoming Appointments</h2>
      <?php if ($result_appointment->num_rows > 0): ?>
        <table>
          <tr>
            <th>Owner Name</th>
            <th>Email</th>
            <th>Pet Name</th>
            <th>Appointment Date</th>
            <th>Appointment Time</th>
            <th>Service Type</th>
          </tr>
          <?php while ($row = $result_appointment->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['owner_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['pet_name']) ?></td>
              <td><?= htmlspecialchars($row['appointment_date']) ?></td>
              <td><?= htmlspecialchars($row['appointment_time']) ?></td>
              <td><?= htmlspecialchars($row['service_type']) ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <div class="warning-box">No upcoming appointments found.</div>
      <?php endif; ?>
    </section>
  </main>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
    <p style="margin-top: 5px; font-size: 0.9em;">
      <i class="fas fa-shield-alt"></i> Licensed & Insured |
      <i class="fas fa-award"></i> AAHA Accredited |
      <i class="fas fa-heart"></i> Serving the community since 2010
    </p>
  </footer>

</body>
</html>
<?php $conn->close(); ?>
