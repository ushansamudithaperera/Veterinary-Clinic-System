<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Doctor"){
    header("location: user_login.php");
    exit;
}

require_once '../db/db_connect.php';

// Handle vaccine deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM vaccines WHERE id = $id");
}

// Handle vaccine addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_vaccine'])) {
    $pet_type = $conn->real_escape_string($_POST['pet_type']);
    $vaccine_name = $conn->real_escape_string($_POST['vaccine_name']);
    $age_range = $conn->real_escape_string($_POST['age_range']);
    $age_unit = isset($_POST['age_unit']) ? $conn->real_escape_string($_POST['age_unit']) : '';
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = intval($_POST['quantity']);
    
    $full_age_range = trim($age_range . ' ' . $age_unit);

    $sql = "INSERT INTO vaccines (pet_type, vaccine_name, age_range, description, quantity) 
            VALUES ('$pet_type', '$vaccine_name', '$full_age_range', '$description', $quantity)";
    $conn->query($sql);
}

// Fetch vaccine records
$result = $conn->query("SELECT * FROM vaccines ORDER BY pet_type, age_range");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Vaccination Info</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=11" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
     .delete-btn {
         background: linear-gradient(135deg,#ef4444,#dc2626) !important;
         color: #fff !important;
         border-radius: 6px !important;
         padding: 8px 12px !important;
         font-size: 0.85rem !important;
         border: none !important;
         cursor: pointer !important;
         margin: 0 !important;
         text-transform: uppercase;
         font-weight: bold;
         box-shadow: 0 2px 8px rgba(239,68,68,0.4);
         transition: transform 0.15s, box-shadow 0.15s;
     }
     .delete-btn:hover {
         transform: translateY(-1px);
         box-shadow: 0 4px 14px rgba(239,68,68,0.55) !important;
     }

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

  <main>
    <section>
      <h2>Available Vaccines</h2>
      <?php if ($result->num_rows > 0): ?>
        <table>
          <tr>
            <th>Pet Type</th>
            <th>Vaccine Name</th>
            <th>Recommended Age</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Delete</th>
          </tr>
          <?php while ($row = $result->fetch_assoc()):
              $vac_id      = (int)$row['id'];
              $vac_name_js = addslashes(htmlspecialchars($row['vaccine_name']));
          ?>
          <tr>
            <td><?= htmlspecialchars($row['pet_type']) ?></td>
            <td><?= htmlspecialchars($row['vaccine_name']) ?></td>
            <td><?= htmlspecialchars($row['age_range']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= (int)$row['quantity'] ?></td>
            <td>
              <form id="del-form-<?= $vac_id ?>" method="POST" style="display:inline-block;margin:0;">
                <input type="hidden" name="delete_id" value="<?= $vac_id ?>">
                <button type="button" class="delete-btn"
                  onclick="openDeleteModal(<?= $vac_id ?>, '<?= $vac_name_js ?>')">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>No vaccines found.</p>
      <?php endif; ?>
      

    </section>

    <section>
      <h2>Add New Vaccine</h2>
      <form method="POST">
        <label>Pet Type:</label><br>
        <input type="text" name="pet_type" required><br><br>

        <label>Vaccine Name:</label><br>
        <input type="text" name="vaccine_name" required><br><br>

        <label>Recommended Age:</label><br>
        <div style="display: flex; gap: 10px;">
           <input type="number" name="age_range" placeholder="e.g. 3" required style="flex: 2; margin-bottom: 0;">
           <select name="age_unit" required style="flex: 1; margin-bottom: 0;">
             <option value="Days">Days</option>
             <option value="Weeks">Weeks</option>
             <option value="Months">Months</option>
             <option value="Years">Years</option>
           </select>
        </div><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4"></textarea><br><br>

        <label>Quantity:</label><br>
        <input type="number" name="quantity" min="0" required><br><br>

        <button type="submit" name="add_vaccine" class="glassy-btn">Add Vaccine</button>
      </form>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
  </footer>

  <!-- ── Delete Confirmation Modal ── -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
      <div class="modal-title">Delete Vaccine?</div>
      <div class="modal-subtitle">
        Are you sure you want to delete<br>
        <strong id="modal-item-name" style="color:#1e293b;"></strong>?<br>
        <span style="color:#ef4444;font-size:0.82rem;">This action cannot be undone.</span>
      </div>
      <div class="modal-actions">
        <button class="modal-btn-cancel" onclick="closeDeleteModal()">
          <i class="fas fa-times" style="margin-right:5px;"></i> Cancel
        </button>
        <button class="modal-btn-confirm" id="modal-confirm-btn">
          <i class="fas fa-trash-alt" style="margin-right:5px;"></i> Yes, Delete
        </button>
      </div>
    </div>
  </div>

  <script>
    var _pendingFormId = null;

    function openDeleteModal(id, name) {
      _pendingFormId = 'del-form-' + id;
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
  </script>
</body>
</html>
