<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Pet Owner"){
    header("location: user_login.php");
    exit;
}
require_once '../db/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Book Appointment</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=10" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script>
    function validateAppointmentForm(event) {
      var ownerName = document.getElementById('owner-name').value.trim();
      var email = document.getElementById('email').value.trim();
      var petName = document.getElementById('pet-name').value.trim();
      var appointmentDate = document.getElementById('appointment-date').value;
      var appointmentTime = document.getElementById('appointment-time').value;
      var serviceType = document.getElementById('service-type').value;

      if (ownerName === "") {
        alert("Owner's Name is required.");
        event.preventDefault();
        return false;
      }
      if (email === "") {
        alert("Email is required.");
        event.preventDefault();
        return false;
      }
      var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        event.preventDefault();
        return false;
      }
      if (petName === "") {
        alert("Pet's Name is required.");
        event.preventDefault();
        return false;
      }
      if (appointmentDate === "") {
        alert("Appointment Date is required.");
        event.preventDefault();
        return false;
      }
      if (appointmentTime === "") {
        alert("Appointment Time is required.");
        event.preventDefault();
        return false;
      }
      if (serviceType === "") {
        alert("Service Type is required.");
        event.preventDefault();
        return false;
      }
      
      // Check if appointment date is not in the past
      var selectedDate = new Date(appointmentDate);
      var today = new Date();
      today.setHours(0, 0, 0, 0);
      
      if (selectedDate < today) {
        alert("Appointment date cannot be in the past.");
        event.preventDefault();
        return false;
      } else if (selectedDate.getTime() === today.getTime()) {
        // Check if the appointment time has already passed today
        var now = new Date();
        var timeParts = appointmentTime.split(':');
        var selectedHour = parseInt(timeParts[0], 10);
        var selectedMinute = parseInt(timeParts[1], 10);
        
        if (selectedHour < now.getHours() || (selectedHour === now.getHours() && selectedMinute < now.getMinutes())) {
          alert("Appointment time cannot be in the past for today's date.");
          event.preventDefault();
          return false;
        }
      }
      
      return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('appointment-form').addEventListener('submit', validateAppointmentForm);
      
    function updateTimeSlots() {
        var dateInput = document.getElementById('appointment-date');
        var timeSelect = document.getElementById('appointment-time');
        var options = timeSelect.options;
        
        if (!dateInput.value) return;
        
        // Parse "YYYY-MM-DD" strictly in local time to avoid UTC bugs
        var parts = dateInput.value.split('-');
        var selectedDate = new Date(parts[0], parts[1] - 1, parts[2]);
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate.getTime() === today.getTime()) {
            var now = new Date();
            var currentHour = now.getHours();
            var currentMinute = now.getMinutes();
            
            for (var i = 1; i < options.length; i++) {
                var timeParts = options[i].value.split(':');
                var optionHour = parseInt(timeParts[0], 10);
                var optionMinute = parseInt(timeParts[1], 10);
                
                if (optionHour < currentHour || (optionHour === currentHour && optionMinute < currentMinute)) {
                    options[i].disabled = true;
                    // Reset if the selected one just got disabled
                    if (options[i].selected) {
                        timeSelect.selectedIndex = 0;
                    }
                } else {
                    options[i].disabled = false;
                }
            }
        } else {
            // Future date: enable all time options
            for (var i = 1; i < options.length; i++) {
                options[i].disabled = false;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('appointment-form').addEventListener('submit', validateAppointmentForm);
      
      // Set minimum date to today locally
      var today = new Date();
      var yyyy = today.getFullYear();
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var dd = String(today.getDate()).padStart(2, '0');
      var todayStr = yyyy + '-' + mm + '-' + dd;
      
      var dateInput = document.getElementById('appointment-date');
      dateInput.setAttribute('min', todayStr);
      
      // Dynamically disable past times
      dateInput.addEventListener('change', updateTimeSlots);
      document.getElementById('appointment-time').addEventListener('click', updateTimeSlots);
    });
  </script>
</head>
<body>
  <?php include 'header.php'; ?>

  <main>
    <section>
      <h2>Book an Appointment</h2>
      <p>Schedule an appointment for your registered pet. Please ensure your pet is already registered in our system.</p>
      
      <?php
      // Fetch user's registered pets
      $pets_sql = "SELECT id, pet_name, owner_name, email FROM pets WHERE owner_name = ?";
      $pets_stmt = $conn->prepare($pets_sql);
      $pets_stmt->bind_param("s", $_SESSION["username"]);
      $pets_stmt->execute();
      $my_pets = $pets_stmt->get_result();
      $registered_pets = [];
      while ($row = $my_pets->fetch_assoc()) {
          $registered_pets[] = $row;
      }
      $pets_stmt->close();
      ?>

      <?php if (count($registered_pets) === 0): ?>
          <div style="padding: 30px; background: rgba(255, 243, 205, 0.8); border: 1px solid #ffeeba; border-left: 5px solid #ffc107; border-radius: 10px; margin: 40px 0; text-align: center; backdrop-filter: blur(5px);">
              <h2 style="color: #856404; margin-bottom: 15px;">🐾 Pet Registration Required!</h2>
              <p style="color: #856404; font-size: 1.1rem; margin-bottom: 20px;">You cannot book an appointment because you haven't natively registered a pet under your account yet.</p>
              <a href="register.php" class="glassy-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); display: inline-block; text-decoration: none;">Register Your Pet First</a>
          </div>
      <?php else: ?>
          <form id="appointment-form" action="process_appointment.php" method="post">
            <label for="pet-select">Select Your Registered Pet:</label><br />
            <select id="pet-select" required onchange="
                var opt = this.options[this.selectedIndex];
                document.getElementById('owner-name').value = opt.getAttribute('data-owner');
                document.getElementById('email').value = opt.getAttribute('data-email');
                document.getElementById('pet-name').value = opt.getAttribute('data-petname');
            ">
              <option value="">-- Choose a Pet --</option>
              <?php foreach ($registered_pets as $pet): ?>
                  <option value="<?= $pet['id'] ?>" data-owner="<?= htmlspecialchars($pet['owner_name']) ?>" data-email="<?= htmlspecialchars($pet['email']) ?>" data-petname="<?= htmlspecialchars($pet['pet_name']) ?>">
                      <?= htmlspecialchars($pet['pet_name']) ?>
                  </option>
              <?php endforeach; ?>
            </select><br /><br />
            
            <!-- Hidden verified pet data -->
            <input type="hidden" id="owner-name" name="owner-name" value="">
            <input type="hidden" id="email" name="email" value="">
            <input type="hidden" id="pet-name" name="pet-name" value="">

            <label for="appointment-date">Appointment Date:</label><br />
            <input type="date" id="appointment-date" name="appointment-date" required><br /><br />

            <label for="appointment-time">Appointment Time:</label><br />
            <select id="appointment-time" name="appointment-time" required>
              <option value="">--Select Time--</option>
              <option value="09:00">9:00 AM</option>
              <option value="09:30">9:30 AM</option>
              <option value="10:00">10:00 AM</option>
              <option value="10:30">10:30 AM</option>
              <option value="11:00">11:00 AM</option>
              <option value="11:30">11:30 AM</option>
              <option value="14:00">2:00 PM</option>
              <option value="14:30">2:30 PM</option>
              <option value="15:00">3:00 PM</option>
              <option value="15:30">3:30 PM</option>
              <option value="16:00">4:00 PM</option>
              <option value="16:30">4:30 PM</option>
              <option value="17:00">5:00 PM</option>
            </select><br /><br />

            <label for="service-type">Service Type:</label><br />
            <select id="service-type" name="service-type" required>
              <option value="">--Select Service--</option>
              <option value="general-checkup">General Checkup</option>
              <option value="vaccination">Vaccination</option>
              <option value="dental-care">Dental Care</option>
              <option value="surgery">Surgery Consultation</option>
              <option value="emergency">Emergency Care</option>
              <option value="grooming">Grooming</option>
              <option value="other">Other</option>
            </select><br /><br />

            <label for="reason">Reason for Visit:</label><br />
            <textarea id="reason" name="reason" rows="4" cols="50" placeholder="Please describe the reason for your visit or any specific concerns..."></textarea><br /><br />

            <label for="phone">Contact Phone (Optional):</label><br />
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number"><br /><br />

            <input type="submit" value="Book Appointment" class="glassy-btn">
          </form>
      <?php endif; ?>
      
      <div style="margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-left: 4px solid #4CAF50;">
        <h3>Important Notes:</h3>
        <ul>
          <li>Your pet must be registered in our system before booking an appointment</li>
          <li>Please arrive 15 minutes before your scheduled appointment</li>
          <li>Bring your pet's vaccination records if available</li>
          <li>For emergency cases, please call us directly</li>
          <li>Cancellations must be made at least 24 hours in advance</li>
        </ul>
      </div>
    </section>

    <!-- existing appointments panel -->
    <section style="margin-top: 60px;">
      <h2>Your Scheduled Appointments</h2>
      <p style="margin-bottom: 20px;">Review or cancel your upcoming veterinary visits below. Note: Only appointments where the Owner's Name matches your exact Username are displayed here.</p>
      
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
        <p style="padding: 15px; background-color: #fef2f2; border-left: 4px solid #ef4444; color: #b91c1c; font-weight: bold; margin-bottom: 20px;">
           <i class="fas fa-check-circle"></i> Appointment successfully cancelled.
        </p>
      <?php endif; ?>

      <?php
      require_once '../db/db_connect.php';
      $sql = "SELECT * FROM appointments WHERE owner_name = ? ORDER BY appointment_date DESC, appointment_time DESC";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $_SESSION["username"]);
      $stmt->execute();
      $my_appointments = $stmt->get_result();

      if ($my_appointments->num_rows > 0) {
          echo "<table>
                  <tr>
                    <th>ID</th>
                    <th>Pet Name</th>
                    <th>Date & Time</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>";
          while ($row = $my_appointments->fetch_assoc()) {
              $statusColor = $row['status'] === 'scheduled' ? '#3b82f6' : ($row['status'] === 'cancelled' ? '#ef4444' : '#10b981');
              echo "<tr>
                      <td>#" . $row['id'] . "</td>
                      <td>" . htmlspecialchars($row['pet_name']) . "</td>
                      <td>" . htmlspecialchars($row['appointment_date']) . " &bull; " . htmlspecialchars($row['appointment_time']) . "</td>
                      <td>" . htmlspecialchars($row['service_type']) . "</td>
                      <td>
                         <span style='font-weight:bold; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; background: rgba(" . ($row['status'] === 'scheduled' ? '59,130,246' : ($row['status'] === 'cancelled' ? '239,68,68' : '16,185,129')) . ", 0.1); color: " . $statusColor . ";'>" . strtoupper(htmlspecialchars($row['status'])) . "</span>
                      </td>
                      <td>";
              if ($row['status'] === 'scheduled') {
                  echo "<form method='POST' action='process_appointment.php' style='margin:0;'>
                          <input type='hidden' name='cancel_appointment' value='1'>
                          <input type='hidden' name='cancel_id' value='" . $row['id'] . "'>
                          <button type='submit' class='glassy-btn' style='background: linear-gradient(135deg, #f87171, #dc2626); font-size: 0.8rem; padding: 6px 12px; margin: 0;'>Cancel</button>
                        </form>";
              } else {
                  echo "<span style='color: #666;'>No Actions</span>";
              }
              echo "</td>
                    </tr>";
          }
          echo "</table>";
      } else {
          echo "<p style='padding: 20px; background: rgba(0,0,0,0.02); text-align: center; border-radius: 8px; font-weight: bold;'>You have no scheduled appointments currently.</p>";
      }
      $stmt->close();
      $conn->close();
      ?>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
  </footer>
</body>
</html>
