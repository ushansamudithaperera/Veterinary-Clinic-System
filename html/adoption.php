<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Pet Owner"){
    header("location: user_login.php");
    exit;
}
require_once '../db/db_connect.php';

$current_user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$status_message = "";
$status_type = "";

// ── Handle DELETE ─────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = (int)$_POST['pet_id'];

    // Verify ownership before deleting
    $chk = $conn->prepare("SELECT owner_id, image_path FROM adoption_pets WHERE id = ?");
    $chk->bind_param("i", $delete_id);
    $chk->execute();
    $chk->bind_result($owner_id_db, $img_path_db);
    $chk->fetch();
    $chk->close();

    if ($owner_id_db === $current_user_id) {
        // Delete image file if not the default
        if ($img_path_db && $img_path_db !== '../images/default_pet.png' && file_exists($img_path_db)) {
            unlink($img_path_db);
        }
        $del = $conn->prepare("DELETE FROM adoption_pets WHERE id = ? AND owner_id = ?");
        $del->bind_param("ii", $delete_id, $current_user_id);
        if ($del->execute()) {
            $status_message = "Pet listing removed successfully.";
            $status_type = "success";
        } else {
            $status_message = "Error removing pet: " . $del->error;
            $status_type = "error";
        }
        $del->close();
    } else {
        $status_message = "You are not authorised to delete this listing.";
        $status_type = "error";
    }
}

// ── Handle ADD ────────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['pet_name'];
    $type = $_POST['pet_type'];
    $age_unit = isset($_POST['age_unit']) ? $_POST['age_unit'] : '';
    if ($age_unit === 'Unknown') {
        $age = 'Unknown';
    } else {
        $age_num = isset($_POST['age']) ? intval($_POST['age']) : 0;
        $age = trim($age_num . ' ' . $age_unit);
    }
    $desc = $_POST['description'];

    // Handle image upload
    $image_path = '../images/default_pet.png';
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../images/adoption_pets/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $file_extension = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('adopt_') . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $sql = "INSERT INTO adoption_pets (owner_id, pet_name, pet_type, age, description, image_path)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $current_user_id, $name, $type, $age, $desc, $image_path);

    if ($stmt->execute()) {
        $status_message = "Adoption pet added successfully!";
        $status_type = "success";
    } else {
        $status_message = "Error: " . $stmt->error;
        $status_type = "error";
    }
    $stmt->close();
}

// ── Fetch all pets ─────────────────────────────────────────────────────────────
$fetch_sql = "SELECT * FROM adoption_pets ORDER BY id DESC";
$pets = $conn->query($fetch_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pet Adoption</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=11" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Delete button */
    .btn-delete-pet {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 12px;
      padding: 8px 16px;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.15s, box-shadow 0.15s;
      box-shadow: 0 4px 12px rgba(239,68,68,0.35);
    }
    .btn-delete-pet:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(239,68,68,0.5);
    }
    .btn-delete-pet:active { transform: scale(0.97); }

    /* Owner badge on card */
    .owner-badge {
      display: inline-block;
      margin-top: 6px;
      padding: 3px 10px;
      background: linear-gradient(135deg,#6366f1,#8b5cf6);
      color:#fff;
      border-radius: 20px;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    /* ── Delete Confirmation Modal ── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15,23,42,0.6);
      backdrop-filter: blur(6px);
      z-index: 10000;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
      background: #fff;
      border-radius: 16px;
      padding: 36px 32px 28px;
      max-width: 400px;
      width: 90%;
      text-align: center;
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
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px;
      font-size: 1.8rem; color: #ef4444;
    }
    .modal-title {
      font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;
    }
    .modal-subtitle {
      font-size: 0.9rem; color: #64748b; margin-bottom: 24px; line-height: 1.5;
    }
    .modal-actions {
      display: flex; gap: 12px; justify-content: center;
    }
    .modal-btn-cancel {
      flex: 1; padding: 11px 0;
      background: #f1f5f9; color: #475569;
      border: none; border-radius: 10px;
      font-size: 0.95rem; font-weight: 600; cursor: pointer;
      transition: background 0.15s;
    }
    .modal-btn-cancel:hover { background: #e2e8f0; }
    .modal-btn-confirm {
      flex: 1; padding: 11px 0;
      background: linear-gradient(135deg,#ef4444,#dc2626); color: #fff;
      border: none; border-radius: 10px;
      font-size: 0.95rem; font-weight: 600; cursor: pointer;
      box-shadow: 0 4px 12px rgba(239,68,68,0.4);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .modal-btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239,68,68,0.55); }

    @keyframes slideInToast {
        0%  { transform: translateX(120%); opacity: 0; }
        100%{ transform: translateX(0);    opacity: 1; }
    }
    @keyframes fadeOutToast {
        0%  { transform: translateX(0);    opacity: 1; }
        100%{ transform: translateX(120%); opacity: 0; }
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <?php if (!empty($status_message)): ?>
    <div id="toast-notification" style="position:fixed;top:100px;right:20px;padding:15px 25px;border-radius:8px;font-weight:bold;
      background:<?= $status_type=='success'?'linear-gradient(135deg,#10b981,#059669)':'linear-gradient(135deg,#ef4444,#dc2626)'?>;
      color:white;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:9999;animation:slideInToast 0.5s cubic-bezier(0.175,0.885,0.32,1.275) forwards;">
        <i class="fas <?= $status_type=='success'?'fa-check-circle':'fa-exclamation-circle'?>" style="margin-right:8px;"></i>
        <?= htmlspecialchars($status_message) ?>
    </div>
    <script>
      setTimeout(function(){
          var t=document.getElementById('toast-notification');
          if(t){ t.style.animation='fadeOutToast 0.5s forwards'; setTimeout(function(){t.remove();},500); }
      }, 4000);
    </script>
  <?php endif; ?>

  <main>
    <!-- ── Pet Adoption List ── -->
    <section>
      <h2>Available Pets for Adoption</h2>
      <p>These lovely pets are waiting for a forever home. Help give them a second chance!</p>

      <div class="pet-showcase" style="margin-top:20px;display:flex;flex-wrap:wrap;gap:20px;justify-content:center;">
        <?php
          $any = false;
          while ($row = $pets->fetch_assoc()):
            $any = true;
            $img_src = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : '../images/default_pet.png';
            $img_src_versioned = file_exists($img_src) ? $img_src . '?v=' . filemtime($img_src) : $img_src;
            $is_owner = ($current_user_id !== 0 && (int)$row['owner_id'] === $current_user_id);
        ?>
          <div class="pet-card-modern" style="background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);width:280px;text-align:center;border:1px solid rgba(0,0,0,0.05);">
            <div style="background:#f8fafc;height:220px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
              <img src="<?= $img_src_versioned ?>" alt="Pet Image" style="max-width:100%;max-height:220px;object-fit:contain;display:block;">
            </div>
            <div style="padding:20px;">
              <h3 style="margin:0 0 5px 0;color:#1e293b;font-size:1.4rem;"><?= htmlspecialchars($row['pet_name']) ?></h3>
              <p style="margin:0 0 15px 0;color:#10b981;font-weight:bold;font-size:0.95rem;text-transform:uppercase;">
                <?= htmlspecialchars($row['pet_type']) ?> &bull; <?= htmlspecialchars($row['age']) ?>
              </p>
              <div style="text-align:left;font-size:0.9rem;color:#475569;line-height:1.6;background:rgba(0,0,0,0.03);padding:10px;border-radius:8px;">
                <strong style="color:#334155;">Description:</strong> <?= htmlspecialchars($row['description']) ?>
              </div>

              <?php if ($is_owner): ?>
                <span class="owner-badge"><i class="fas fa-user-check" style="margin-right:4px;"></i>Your Listing</span>
                <!-- Delete button — only visible to the owner -->
                <form id="delete-form-<?= (int)$row['id'] ?>" method="post" action="adoption.php">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="pet_id" value="<?= (int)$row['id'] ?>">
                  <button type="button" class="btn-delete-pet"
                    onclick="openDeleteModal(<?= (int)$row['id'] ?>, '<?= addslashes(htmlspecialchars($row['pet_name'])) ?>')">
                    <i class="fas fa-trash-alt"></i> Remove
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>

        <?php if (!$any): ?>
          <p style="color:#64748b;font-style:italic;">No pets are listed for adoption yet. Be the first to help!</p>
        <?php endif; ?>
      </div>
    </section>

    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "Pet Owner"): ?>
    <!-- ── Register New Homeless Pet ── -->
    <section>
      <h2>Register a Pet for Adoption</h2>
      <form action="adoption.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <label for="pet_name">Pet Name:</label>
        <input type="text" name="pet_name" id="pet_name" required>

        <label for="pet_type">Pet Type:</label>
        <select name="pet_type" id="pet_type" required>
          <option value="Dog">Dog</option>
          <option value="Cat">Cat</option>
          <option value="Rabbit">Rabbit</option>
          <option value="Other">Other</option>
        </select>

        <label for="age">Age:</label>
        <div style="display:flex;gap:10px;align-items:center;">
           <input type="number" id="age_number_input" name="age" min="0" required style="flex:2;margin-bottom:0;">
           <select name="age_unit" id="age_unit_select" required style="flex:1;margin-bottom:0;" onchange="toggleAgeInput(this)">
             <option value="Years">Years</option>
             <option value="Months">Months</option>
             <option value="Weeks">Weeks</option>
             <option value="Days">Days</option>
             <option value="Unknown">Unknown</option>
           </select>
        </div><br>
        <script>
          function toggleAgeInput(select) {
            var numInput = document.getElementById('age_number_input');
            if (select.value === 'Unknown') {
              numInput.style.display = 'none';
              numInput.removeAttribute('required');
              numInput.value = '';
            } else {
              numInput.style.display = '';
              numInput.setAttribute('required', 'required');
            }
          }
        </script>

        <label for="description">Contact information, Conditions etc.:</label>
        <textarea name="description" id="description" rows="4"></textarea>

        <label for="pet_image">Upload Pet Image:</label>
        <input type="file" name="pet_image" id="pet_image" accept="image/*" required>

        <input type="submit" value="Add Pet for Adoption" class="glassy-btn">
      </form>
    </section>
    <?php endif; ?>
  </main>

  <footer>
    <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
  </footer>

  <!-- ── Delete Confirmation Modal ── -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon">
        <i class="fas fa-trash-alt"></i>
      </div>
      <div class="modal-title">Remove Listing?</div>
      <div class="modal-subtitle" id="modal-pet-label">
        Are you sure you want to remove this pet listing?<br>
        <strong id="modal-pet-name" style="color:#1e293b;"></strong><br>
        <span style="color:#ef4444;font-size:0.82rem;">This action cannot be undone.</span>
      </div>
      <div class="modal-actions">
        <button class="modal-btn-cancel" onclick="closeDeleteModal()">
          <i class="fas fa-times" style="margin-right:5px;"></i> Cancel
        </button>
        <button class="modal-btn-confirm" id="modal-confirm-btn">
          <i class="fas fa-trash-alt" style="margin-right:5px;"></i> Yes, Remove
        </button>
      </div>
    </div>
  </div>

  <script>
    var _pendingDeleteFormId = null;

    function openDeleteModal(petId, petName) {
      _pendingDeleteFormId = 'delete-form-' + petId;
      document.getElementById('modal-pet-name').textContent = petName;
      document.getElementById('deleteModal').classList.add('active');
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('active');
      _pendingDeleteFormId = null;
    }

    document.getElementById('modal-confirm-btn').addEventListener('click', function() {
      if (_pendingDeleteFormId) {
        document.getElementById(_pendingDeleteFormId).submit();
      }
    });

    // Close when clicking the backdrop
    document.getElementById('deleteModal').addEventListener('click', function(e) {
      if (e.target === this) closeDeleteModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeDeleteModal();
    });
  </script>
</body>
</html>

<?php $conn->close(); ?>
