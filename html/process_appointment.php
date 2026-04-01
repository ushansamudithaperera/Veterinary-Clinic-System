<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "Pet Owner"){
    header("location: user_login.php");
    exit;
}
require_once '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle Cancellation Request First
    if (isset($_POST['cancel_appointment']) && isset($_POST['cancel_id'])) {
        $cancel_id = intval($_POST['cancel_id']);
        $username = $_SESSION['username'];
        
        $cancel_sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND owner_name = ?";
        $c_stmt = $conn->prepare($cancel_sql);
        $c_stmt->bind_param("is", $cancel_id, $username);
        $c_stmt->execute();
        $c_stmt->close();
        
        header("Location: appoinment.php?msg=cancelled");
        exit();
    }

    // Get form data for Bookings
    $owner_name = mysqli_real_escape_string($conn, $_POST['owner-name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pet_name = mysqli_real_escape_string($conn, $_POST['pet-name']);
    $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment-date']);
    $appointment_time = mysqli_real_escape_string($conn, $_POST['appointment-time']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service-type']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // First, verify that the pet is registered in the system
    $verify_sql = "SELECT id FROM pets WHERE owner_name = ? AND email = ? AND pet_name = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("sss", $owner_name, $email, $pet_name);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows == 0) {
        // Pet not found in system
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Appointment Booking - Error</title>
            <link rel='stylesheet' href='../css/vet_app.css'>
        </head>
        <body>
            <header>
                <h1>Happy Paws Veterinary Clinic</h1>
                <nav>
                    <ul>
                        <li><a href='main.php'><i class='fas fa-home'></i> Home</a></li>
                        <li><a href='adoption.php'><i class='fas fa-heart'></i> Pet Adoption</a></li>
                        <li><a href='vaccination.php'><i class='fas fa-syringe'></i> Vaccination</a></li>
                        <li><a href='appoinment.php'><i class='fas fa-calendar-plus'></i> Book Appointment</a></li>
                        <li><a href='register.php'><i class='fas fa-user-plus'></i> Register Your Pet</a></li>
                        <li><a href='view_pets.php'><i class='fas fa-paw'></i> View Registered Pets</a></li>
                    </ul>
                </nav>
            </header>
            <main>
                <section>
                    <div style='padding: 20px; background-color: #ffebee; border-left: 4px solid #f44336; margin: 20px 0;'>
                        <h2>Pet Not Found!</h2>
                        <p>We could not find a pet registered with the following details:</p>
                        <ul>
                            <li><strong>Owner:</strong> $owner_name</li>
                            <li><strong>Email:</strong> $email</li>
                            <li><strong>Pet Name:</strong> $pet_name</li>
                        </ul>
                        <p>Please make sure:</p>
                        <ul>
                            <li>Your pet is registered in our system</li>
                            <li>The owner name, email, and pet name match exactly</li>
                            <li>There are no spelling errors</li>
                        </ul>
                        <p><a href='register.php'>Register your pet first</a> or <a href='appoinment.php'>try booking again</a> with the correct information.</p>
                    </div>
                </section>
            </main>
            <footer>
                <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
            </footer>
        </body>
        </html>";
        exit();
    }
    
    $pet_id = $verify_result->fetch_assoc()['id'];
    
    // Check if there's already an appointment at the same date and time
    $check_sql = "SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $appointment_date, $appointment_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Time slot already booked
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Appointment Booking - Time Conflict</title>
            <link rel='stylesheet' href='../css/vet_app.css'>
        </head>
        <body>
            <header>
                <h1>Happy Paws Veterinary Clinic</h1>
                <nav>
                    <ul>
                        <li><a href='main.php'>Home</a></li>
                        <li><a href='adoption.php'>Pet Adoption</a></li>
                        <li><a href='vaccination.php'>Vaccination</a></li>
                        <li><a href='appoinment.php'>Book Appointment</a></li>
                        <li><a href='register.php'>Register Your Pet</a></li>
                    </ul>
                </nav>
            </header>
            <main>
                <section>
                    <div style='padding: 20px; background-color: #fff3cd; border-left: 4px solid #ffc107; margin: 20px 0;'>
                        <h2>Time Slot Already Booked!</h2>
                        <p>The selected appointment time ($appointment_date at $appointment_time) is already booked.</p>
                        <p>Please <a href='appoinment.php'>choose a different time slot</a>.</p>
                    </div>
                </section>
            </main>
            <footer>
                <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
            </footer>
        </body>
        </html>";
        exit();
    }
    
    // Insert appointment into database
    $sql = "INSERT INTO appointments (pet_id, owner_name, email, pet_name, appointment_date, appointment_time, service_type, reason, phone, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $pet_id, $owner_name, $email, $pet_name, $appointment_date, $appointment_time, $service_type, $reason, $phone);
    
    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        // Success page
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Appointment Booked Successfully</title>
            <link rel='stylesheet' href='../css/vet_app.css'>
        </head>
        <body>
            <header>
                <h1>Happy Paws Veterinary Clinic</h1>
                <nav>
                    <ul>
                        <li><a href='main.php'>Home</a></li>
                        <li><a href='adoption.php'>Pet Adoption</a></li>
                        <li><a href='vaccination.php'>Vaccination</a></li>
                        <li><a href='appoinment.php'>Book Appointment</a></li>
                        <li><a href='register.php'>Register Your Pet</a></li>
                    </ul>
                </nav>
            </header>
            <main>
                <section>
                    <div style='padding: 20px; background-color: #d4edda; border-left: 4px solid #28a745; margin: 20px 0;'>
                        <h2>Appointment Booked Successfully!</h2>
                        <p><strong>Appointment ID:</strong> #$appointment_id</p>
                        <p>Your appointment has been scheduled with the following details:</p>
                        <ul>
                            <li><strong>Owner:</strong> $owner_name</li>
                            <li><strong>Pet:</strong> $pet_name</li>
                            <li><strong>Date:</strong> $appointment_date</li>
                            <li><strong>Time:</strong> $appointment_time</li>
                            <li><strong>Service:</strong> $service_type</li>
                        </ul>
                        <p><strong>Important:</strong> Please save your appointment ID for reference.</p>
                        <p>We will send a confirmation email to: <strong>$email</strong></p>
                    </div>
                    <div style='margin-top: 20px;'>
                        <a href='appoinment.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Book Another Appointment</a>
                        <a href='main.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Go to Home</a>
                    </div>
                </section>
            </main>
            <footer>
                <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
            </footer>
        </body>
        </html>";
    } else {
        // Error occurred
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Appointment Booking - Error</title>
            <link rel='stylesheet' href='../css/vet_app.css'>
        </head>
        <body>
            <header>
                <h1>Happy Paws Veterinary Clinic</h1>
                <nav>
                    <ul>
                        <li><a href='main.php'>Home</a></li>
                        <li><a href='adoption.php'>Pet Adoption</a></li>
                        <li><a href='vaccination.php'>Vaccination</a></li>
                        <li><a href='appoinment.php'>Book Appointment</a></li>
                        <li><a href='register.php'>Register Your Pet</a></li>
                    </ul>
                </nav>
            </header>
            <main>
                <section>
                    <div style='padding: 20px; background-color: #ffebee; border-left: 4px solid #f44336; margin: 20px 0;'>
                        <h2>Booking Failed!</h2>
                        <p>There was an error booking your appointment. Please try again later.</p>
                        <p>If the problem persists, please contact us directly.</p>
                        <p><a href='appoinment.php'>Try Again</a></p>
                    </div>
                </section>
            </main>
            <footer>
                <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
            </footer>
        </body>
        </html>";
    }
    
    $stmt->close();
    $verify_stmt->close();
    $check_stmt->close();
} else {
    // If accessed directly without POST
    header("Location: appoinment.php");
    exit();
}

$conn->close();
?>
