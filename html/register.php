<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Pet Owner"){
    header("location: user_login.php");
    exit;
}
require_once '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Intercept unregister requests
    if (isset($_POST["unregister_pet"]) && isset($_POST["unregister_id"])) {
        $unregister_id = intval($_POST["unregister_id"]);
        $username = $_SESSION["username"];
        $del_sql = "DELETE FROM pets WHERE id = ? AND owner_name = ?";
        $del_stmt = $conn->prepare($del_sql);
        $del_stmt->bind_param("is", $unregister_id, $username);
        if (!$del_stmt->execute()) {
            die("Unregister failed! Database Error: " . $del_stmt->error);
        }
        if ($del_stmt->affected_rows === 0) {
            die("Critical Error: Database execute succeeded but NO rows were affected. Pet ID: " . $unregister_id . " Username: " . $username);
        }
        $del_stmt->close();
        
        // Output JS to trigger a clean frontend reload with status parameter
        echo "<script>window.location.replace('register.php?msg=unregistered');</script>";
        exit();
    }

    $owner_name = $_POST["owner-name"];
    $email = $_POST["email"];
    $pet_name = $_POST["pet-name"];
    $pet_type = $_POST["pet-type"];
    $breed = $_POST["breed"];
    $age_num = trim($_POST["age"]);
    $age_unit = isset($_POST["age_unit"]) ? $_POST["age_unit"] : "Years";
    $age = $age_num . ' ' . $age_unit;
    $notes = $_POST["notes"];

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../images/registered_pets/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $file_extension = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('pet_') . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $status_message = "";
    $status_type = "";
    
    // Check for Duplicates
    $dup_sql = "SELECT id FROM pets WHERE owner_name = ? AND pet_name = ?";
    $dup_stmt = $conn->prepare($dup_sql);
    $dup_stmt->bind_param("ss", $owner_name, $pet_name);
    $dup_stmt->execute();
    $dup_result = $dup_stmt->get_result();
    
    if ($dup_result->num_rows > 0) {
        $status_message = "A pet named '" . htmlspecialchars($pet_name) . "' is already registered to your account!";
        $status_type = "error";
    } else {
        // Prepare the SQL query
        $stmt = $conn->prepare("INSERT INTO pets (owner_name, email, pet_name, pet_type, breed, age, notes, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $owner_name, $email, $pet_name, $pet_type, $breed, $age, $notes, $image_path);

        if ($stmt->execute()) {
            $status_message = "Pet registered successfully!";
            $status_type = "success";
        } else {
            $status_message = "Error: " . $stmt->error;
            $status_type = "error";
        }
        $stmt->close();
    }
    $dup_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register Your Pet</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=10" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* ── Delete Confirmation Modal ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(15,23,42,0.6); backdrop-filter: blur(6px);
      z-index: 10000; align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
      background: #fff; border-radius: 16px; padding: 36px 32px 28px;
      max-width: 400px; width: 90%; text-align: center;
      box-shadow: 0 25px 60px rgba(0,0,0,0.25);
      animation: modalPop 0.3s cubic-bezier(0.175,0.885,0.32,1.275) forwards;
    }
    @keyframes modalPop {
      0%   { transform: scale(0.8); opacity: 0; }
      100% { transform: scale(1);   opacity: 1; }
    }
    .modal-icon {
      width: 64px; height: 64px;
      background: linear-gradient(135deg,#fee2e2,#fecaca);
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px; font-size: 1.8rem; color: #ef4444;
    }
    .modal-title  { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .modal-subtitle { font-size: 0.9rem; color: #64748b; margin-bottom: 24px; line-height: 1.5; }
    .modal-actions { display: flex; gap: 12px; justify-content: center; }
    .modal-btn-cancel {
      flex: 1; padding: 11px 0; background: #f1f5f9; color: #475569;
      border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
      transition: background 0.15s;
    }
    .modal-btn-cancel:hover { background: #e2e8f0; }
    .modal-btn-confirm {
      flex: 1; padding: 11px 0;
      background: linear-gradient(135deg,#ef4444,#dc2626); color: #fff;
      border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
      box-shadow: 0 4px 12px rgba(239,68,68,0.4);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .modal-btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239,68,68,0.55); }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <?php if (!empty($status_message)): ?>
    <div id="toast-notification" style="position: fixed; top: 100px; right: 20px; padding: 15px 25px; border-radius: 8px; font-weight: bold; background: <?= $status_type == 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)' ?>; color: white; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 9999; animation: slideInToast 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;">
        <i class="fas <?= $status_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>" style="margin-right: 8px;"></i> <?= htmlspecialchars($status_message) ?>
    </div>
    <style>
      @keyframes slideInToast {
          0% { transform: translateX(120%); opacity: 0; }
          100% { transform: translateX(0); opacity: 1; }
      }
      @keyframes fadeOutToast {
          0% { transform: translateX(0); opacity: 1; }
          100% { transform: translateX(120%); opacity: 0; }
      }
    </style>
    <script>
      setTimeout(function() {
          var toast = document.getElementById('toast-notification');
          if(toast) {
              toast.style.animation = 'fadeOutToast 0.5s forwards';
              setTimeout(function(){ toast.remove(); }, 500);
          }
      }, 4000);
    </script>
  <?php endif; ?>

  <main>
    <!-- My Registered Pets -->
    <section style="margin-bottom: 60px;">
      <h2>Your Registered Pets</h2>
      <p style="margin-bottom: 20px;">Review your registered pets below. You can unregister them if they are no longer in your care.</p>
      
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unregistered'): ?>
        <div id="toast-unreg" style="position: fixed; top: 100px; right: 20px; padding: 15px 25px; border-radius: 8px; font-weight: bold; background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 9999; animation: slideInToast 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;">
            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Pet officially unregistered.
        </div>
        <script>
          if (window.history.replaceState) {
              window.history.replaceState(null, null, window.location.pathname);
          }
          setTimeout(function() {
              var t = document.getElementById('toast-unreg');
              if(t) { t.style.animation = 'fadeOutToast 0.5s forwards'; setTimeout(function(){ t.remove(); }, 500); }
          }, 4000);
        </script>
      <?php endif; ?>

      <?php
      $pets_sql = "SELECT * FROM pets WHERE owner_name = ?";
      $p_stmt = $conn->prepare($pets_sql);
      $p_stmt->bind_param("s", $_SESSION["username"]);
      $p_stmt->execute();
      $my_pets = $p_stmt->get_result();
      
      if ($my_pets->num_rows > 0):
          echo "<div class='pet-showcase' style='display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;'>";
          while($row = $my_pets->fetch_assoc()):
             $base_src = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : '../images/default_pet.png';
             $img_src  = file_exists($base_src) ? $base_src . '?v=' . filemtime($base_src) : $base_src;
             $pet_id   = (int)$row['id'];
             $pet_name_js = addslashes(htmlspecialchars($row['pet_name']));
        ?>
          <div class="pet-card-modern" style="background: rgba(255,255,255,0.9); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 280px; text-align: center; border: 1px solid rgba(0,0,0,0.05);">
            <div style="background: #f8fafc; height: 220px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
              <img src="<?= $img_src ?>" alt="Pet Image" style="max-width: 100%; max-height: 220px; object-fit: contain; display: block;">
            </div>
            <div style="padding: 20px;">
              <h3 style="margin: 0 0 5px 0; color: #1e293b; font-size: 1.4rem;"><?= htmlspecialchars($row['pet_name']) ?></h3>
              <p style="margin: 0 0 15px 0; color: #10b981; font-weight: bold; font-size: 0.95rem; text-transform: uppercase;">
                <?= htmlspecialchars($row['pet_type']) ?> &bull; <?= htmlspecialchars($row['breed']) ?>
              </p>
              <div style="text-align: left; font-size: 0.9rem; color: #475569; margin-bottom: 20px; line-height: 1.4; background: rgba(0,0,0,0.03); padding: 10px; border-radius: 8px;">
                <strong>Age:</strong> <?= htmlspecialchars($row['age']) ?><br>
                <strong>Notes:</strong> <?= htmlspecialchars($row['notes']) ?>
              </div>

              <form id="unreg-form-<?= $pet_id ?>" method="POST" action="register.php"
                    style="background:transparent;padding:0;box-shadow:none;border:none;margin:0;">
                <input type="hidden" name="unregister_pet"  value="1">
                <input type="hidden" name="unregister_id"   value="<?= $pet_id ?>">
                <button type="button"
                  onclick="openDeleteModal(<?= $pet_id ?>, '<?= $pet_name_js ?>')"
                  style="width:100%;background:linear-gradient(135deg,#ef4444,#dc2626);color:white;padding:12px;
                         border:none;border-radius:8px;font-weight:bold;cursor:pointer;text-transform:uppercase;
                         transition:transform 0.15s,box-shadow 0.15s;box-shadow:0 4px 12px rgba(239,68,68,0.35);">
                  <i class="fas fa-paw" style="margin-right:6px;"></i> Unregister Pet
                </button>
              </form>
            </div>
          </div>
        <?php
          endwhile;
          echo "</div>";
      else:
          echo "<p style='padding:20px;background:rgba(0,0,0,0.02);text-align:center;border-radius:8px;font-weight:bold;'>You have no registered pets.</p>";
      endif;
      $p_stmt->close();
      ?>

    </section>

    <section>
      <h2>Register Your Pet</h2>
      <form id="register-form" action="#" method="post" enctype="multipart/form-data" novalidate>
        <label for="owner-name">Owner's Name:</label><br />
        <input type="text" id="owner-name" name="owner-name" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly style="background-color: rgba(0,0,0,0.05); color: #666; cursor: not-allowed; border-color: transparent;"><br /><br />

        <label for="email">Email:</label><br />
        <input type="text" id="email" name="email"><br /><br />

        <label for="pet-name">Pet's Name:</label><br />
        <input type="text" id="pet-name" name="pet-name"><br /><br />

        <label for="pet-type">Pet Type:</label><br />
        <select id="pet-type" name="pet-type">
          <option value="">--Select--</option>
          <option value="dog">Dog</option>
          <option value="cat">Cat</option>
          <option value="bird">Bird</option>
          <option value="other">Other</option>
        </select><br /><br />

        <label for="breed">Breed:</label><br />
        <input type="text" id="breed" name="breed"><br /><br />

        <label for="age">Age:</label><br />
        <div style="display: flex; gap: 10px;">
           <input type="number" id="age" name="age" min="0" required style="flex: 2; margin-bottom: 0;">
           <select name="age_unit" required style="flex: 1; margin-bottom: 0px; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 8px; background: rgba(255, 255, 255, 0.6); font-family: 'Poppins', sans-serif;">
             <option value="Days">Days</option>
             <option value="Weeks">Weeks</option>
             <option value="Months">Months</option>
             <option value="Years" selected>Years</option>
           </select>
        </div><br /><br />

        <label for="notes">Additional Notes:</label><br />
        <textarea id="notes" name="notes" rows="4"></textarea><br /><br />

        <label for="pet_image">Upload Pet Picture (Optional):</label><br />
        <input type="file" id="pet_image" name="pet_image" accept="image/*"><br /><br />

        <input type="submit" value="Register Pet" class="glassy-btn">
      </form>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
  </footer>

  <!-- ── Delete Confirmation Modal ── -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon"><i class="fas fa-paw"></i></div>
      <div class="modal-title">Unregister Pet?</div>
      <div class="modal-subtitle">
        Are you sure you want to unregister<br>
        <strong id="modal-item-name" style="color:#1e293b;"></strong>?<br>
        <span style="color:#ef4444;font-size:0.82rem;">All appointments for this pet will also be removed.</span>
      </div>
      <div class="modal-actions">
        <button class="modal-btn-cancel" onclick="closeDeleteModal()">
          <i class="fas fa-times" style="margin-right:5px;"></i> Cancel
        </button>
        <button class="modal-btn-confirm" id="modal-confirm-btn">
          <i class="fas fa-paw" style="margin-right:5px;"></i> Yes, Unregister
        </button>
      </div>
    </div>
  </div>

  <script>
    var _pendingFormId = null;

    function openDeleteModal(id, name) {
      _pendingFormId = 'unreg-form-' + id;
      document.getElementById('modal-item-name').textContent = name;
      document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('active');
      _pendingFormId = null;
    }
    document.getElementById('modal-confirm-btn').addEventListener('click', function() {
      if (_pendingFormId) document.getElementById(_pendingFormId).submit();
    });
    document.getElementById('deleteModal').addEventListener('click', function(e) {
      if (e.target === this) closeDeleteModal();
    });
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeDeleteModal();
    });

    function validateForm(event) {
      var owner = document.getElementById('owner-name').value.trim();
      var email = document.getElementById('email').value.trim();
      var petName = document.getElementById('pet-name').value.trim();
      var petType = document.getElementById('pet-type').value;
      var age = document.getElementById('age').value;

      if (owner === "") { alert("Owner's Name is required."); event.preventDefault(); return false; }
      if (email === "") { alert("Email is required."); event.preventDefault(); return false; }
      var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) { alert("Please enter a valid email address."); event.preventDefault(); return false; }
      if (petName === "") { alert("Pet's Name is required."); event.preventDefault(); return false; }
      if (petType === "") { alert("Pet Type is required."); event.preventDefault(); return false; }
      if (age !== "" && (isNaN(age) || age < 0)) { alert("Please enter a valid age (0 or greater)."); event.preventDefault(); return false; }
      return true;
    }
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('register-form').addEventListener('submit', validateForm);
    });
  </script>
</body>
</html>
