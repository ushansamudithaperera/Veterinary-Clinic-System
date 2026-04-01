<?php
session_start();
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$role = isset($_SESSION["role"]) ? $_SESSION["role"] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Happy Paws Veterinary Clinic - Your Pet's Health Partner</title>
  <link rel="stylesheet" href="../css/vet_app.css?v=12" />
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <?php include 'header.php'; ?>


  <main>
    <!-- Hero Section -->
    <section class="hero-section">
      <h1>🐕 Welcome to Happy Paws! 🐱</h1>
      <p>Your trusted partner in pet health and happiness. Providing compassionate veterinary care for over 15 years.</p>
      
      <?php if (!$isLoggedIn): ?>
      <div style="margin: 20px 0;">
        <a href="user_login.php" class="cta-button hero-glassy-btn" style="margin-right: 15px; text-decoration: none;"></i> LOGIN</a>
        <a href="user_register.php" class="cta-button hero-glassy-btn" style="text-decoration: none;"></i> REGISTER </a>
      </div>
      <?php else: ?>
      <div style="margin: 20px 0;">
        <h2 style="color:white;">Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
      </div>
      <?php endif; ?>

      <div class="hours-today">
        <strong>Today's Hours:</strong> 
        <span class="status-open">OPEN</span> 8:00 AM - 8:00 PM
      </div>
    </section>

    <!-- Services Grid -->
    <section>
      <h2 style="text-align: center; margin-bottom: 30px; font-size: 2em;">🏥 Our Premium Services</h2>
      <div class="services-grid">
        <div class="service-card">
          <div class="service-icon">🩺</div>
          <h3>General Checkups</h3>
          <p>Comprehensive health examinations to keep your pet in optimal condition. Regular checkups help prevent diseases and ensure early detection of any health issues.</p>
          <a href="appoinment.php?service=general-checkup" class="cta-button">Book Checkup</a>
        </div>

        <div class="service-card">
          <div class="service-icon">💉</div>
          <h3>Vaccinations</h3>
          <p>Complete vaccination programs to protect your pets from dangerous diseases. We follow the latest vaccination schedules recommended by veterinary associations.</p>
          <a href="vaccination.php" class="cta-button">View Schedule</a>
        </div>

        <div class="service-card">
          <div class="service-icon">🏠</div>
          <h3>Pet Adoption</h3>
          <p>Find your perfect companion from our adoption program. We help match loving pets with caring families, including full health screening and support.</p>
          <a href="adoption.php" class="cta-button">Find a Pet</a>
        </div>

        <div class="service-card">
          <div class="service-icon">🚨</div>
          <h3>Emergency Care</h3>
          <p>24/7 emergency services for critical situations. Our experienced team is always ready to provide life-saving care when your pet needs it most.</p>
          <a href="appoinment.php?service=emergency" class="cta-button">Emergency Info</a>
        </div>

        <div class="service-card">
          <div class="service-icon">🦷</div>
          <h3>Dental Care</h3>
          <p>Professional dental cleaning and oral health maintenance. Dental health is crucial for your pet's overall wellbeing and quality of life.</p>
          <a href="appoinment.php?service=dental-care" class="cta-button">Book Dental</a>
        </div>

        <div class="service-card">
          <div class="service-icon">✂️</div>
          <h3>Grooming Services</h3>
          <p>Professional grooming services to keep your pet looking and feeling their best. From basic baths to full styling and nail trimming.</p>
          <a href="appoinment.php?service=grooming" class="cta-button">Book Grooming</a>
        </div>
      </div>
    </section>

    <!-- Pet Showcase -->
    <section>
      <h2 style="text-align: center; margin-bottom: 40px; font-size: 2em;">🐾 We Care for All Types of Pets</h2>
      <div class="pet-showcase">
        <div class="pet-item pet-dog">
          <div class="pet-info">
            <h4>Dogs</h4>
            <p>All breeds welcome</p>
          </div>
        </div>
        <div class="pet-item pet-cat">
          <div class="pet-info">
            <h4>Cats</h4>
            <p>Feline specialists</p>
          </div>
        </div>
        <div class="pet-item pet-rabbit">
          <div class="pet-info">
            <h4>Rabbits</h4>
            <p>Exotic pet care</p>
          </div>
        </div>
        <div class="pet-item pet-bird">
          <div class="pet-info">
            <h4>Birds</h4>
            <p>Avian medicine</p>
          </div>
        </div>
        <div class="pet-item pet-hamster">
          <div class="pet-info">
            <h4>Small Pets</h4>
            <p>Hamsters, guinea pigs</p>
          </div>
        </div>
        <div class="pet-item pet-reptile">
          <div class="pet-info">
            <h4>Reptiles</h4>
            <p>Specialized care</p>
          </div>
        </div>
      </div>
    </section>



    <!-- Contact Information -->
    <section class="contact-info">
      <h3><i class="fas fa-map-marker-alt"></i> Visit Our Modern Facility</h3>
      <p>Conveniently located in the heart of Pet City with ample parking and easy access.</p>
      
      <div class="contact-details">
        <div class="contact-item">
          <strong><i class="fas fa-map-marker-alt"></i> Address</strong>
          123 Pet Care Avenue<br>
          Animal City, AC 12345
        </div>
        
        <div class="contact-item">
          <strong><i class="fas fa-phone"></i> Phone Numbers</strong>
          Main: (123) 456-7890<br>
          Emergency: (123) 456-PETS
        </div>
        
        <div class="contact-item">
          <strong><i class="fas fa-envelope"></i> Email</strong>
          info@happypaws.com<br>
          appointments@happypaws.com
        </div>
        
        <div class="contact-item">
          <strong><i class="fas fa-clock"></i> Operating Hours</strong>
          Mon-Fri: 8:00 AM - 8:00 PM<br>
          Sat-Sun: 9:00 AM - 6:00 PM<br>
          <span style="color: #dc3545;">Emergency: 24/7</span>
        </div>
      </div>
    </section>

  </main>

  <footer>
    <p>&copy; 2025 Happy Paws Veterinary Clinic. All rights reserved.</p>
    <p>
      <span><i class="fas fa-shield-alt"></i> Licensed & Insured</span>
      <span><i class="fas fa-award"></i> AAHA Accredited</span>
      <span><i class="fas fa-heart"></i> Serving the community since 2010</span>
    </p>
  </footer>
</body>
</html>
