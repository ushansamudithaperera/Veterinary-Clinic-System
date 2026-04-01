# 🐾 Happy Paws Veterinary Clinic
### BECS 31233 — Web and Internet Technologies | 5th Semester Group Project

> A full-stack veterinary clinic management web application built with PHP, MySQL, HTML, CSS, and JavaScript.

---

## 📋 Table of Contents

- [About the Project](#-about-the-project)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
- [User Roles](#-user-roles)
- [Screenshots](#-screenshots)
- [Team](#-team)

---

## 🏥 About the Project

**Happy Paws Veterinary Clinic** is a fully functional veterinary clinic management platform developed as the final project for the *Web and Internet Technologies* module (BECS 31233).

The system allows **pet owners** to register their pets, book appointments, manage their records, and browse pets available for adoption — while giving **clinic doctors** a dedicated dashboard to view registered patients, manage appointments, and track vaccination records.

The platform emphasizes a modern glassmorphism UI, data integrity, role-based access control, and a seamless user experience.

---

## ✨ Features

### 🔐 Authentication & Access Control
- Secure user registration and login system
- Role-based access: **Pet Owner** and **Doctor**
- Session-based authentication with protected routes

### 🐶 Pet Management
- Register pets with photo uploads 
- Flexible age input
- Owner's personal pet dashboard with card-based layout
- **Unregister** pets with one click

### 📅 Appointment System
- Registration-first workflow — pets must be registered before booking
- Appointments linked to registered pet records via dropdown selection
- Pet owners can cancel their own appointments
- Doctors can view and manage all appointments

### 🏠 Pet Adoption
- Browse pets available for adoption with images
- Pet owners can list homeless pets for adoption

### 🎨 UI / UX
- Premium glassmorphism design aesthetic
- Animated toast notifications 
- Smooth animations and hover micro-interactions
- Fully responsive layout

### 🔒 Security
- All database queries use `mysqli` prepared statements
- SQL injection prevention throughout
- Input sanitization with `htmlspecialchars()`

---

## 🛠️ Tech Stack

| Technology | Usage |
|---|---|
| **PHP** | Server-side logic, session management, database operations |
| **MySQL** | Relational database for users, pets, appointments |
| **HTML5** | Page structure and semantic markup |
| **CSS3** | Glassmorphism styling, animations, responsive layout |
| **JavaScript** | Client-side validation, live time slot filtering, toast notifications |
| **XAMPP** | Local development environment (Apache + MySQL) |
| **Font Awesome** | Icons |
| **Google Fonts** | Typography (Poppins) |

---

## 📁 Project Structure

```
Web-and-Internet-Technologies/
│
├── css/
│   └── vet_app.css              # Main stylesheet (glassmorphism theme)
│
├── db/
│   └── db_connect.php           # Database connection & schema migrations
│
├── html/
│   ├── main.php                 # Home page
│   ├── header.php               # Shared navigation header
│   ├── user_login.php           # Login page
│   ├── user_register.php        # User sign-up page
│   ├── register.php             # Pet registration + owner dashboard
│   ├── appoinment.php           # Appointment booking + owner dashboard
│   ├── process_appointment.php  # Doctor's appointment management panel
│   ├── adoption.php             # Pet adoption listing + submission form
│   ├── view_pets.php            # Doctor's registered pets viewer
│   ├── vaccination.php          # Vaccination records page
│   └── logout.php               # Session destruction & redirect
│
└── images/
    ├── registered_pets/         # Uploaded images for registered pets
    └── adoption_pets/           # Uploaded images for adoption listings
```

---

## 🚀 Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP + MySQL stack)
- A modern web browser

### Installation

1. **Clone or download** this repository into your XAMPP `htdocs` folder:
   ```
   C:\xampp\htdocs\Web-and-Internet-Technologies\
   ```

2. **Start XAMPP** — ensure **Apache** and **MySQL** are running.

3. **Create the database:**
   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Create a new database named `vet_clinic`
   - The tables are **auto-created** on first run via `db_connect.php`

4. **Configure the database connection** in `db/db_connect.php`:
   ```php
   $host = "localhost";
   $dbname = "vet_clinic";
   $username = "root";
   $password = "";
   ```

5. **Open the application** in your browser:
   ```
   http://localhost/Web-and-Internet-Technologies/html/main.php
   ```

6. **Register an account** and select your role (Pet Owner or Doctor) to get started.

---

## 👤 User Roles

### 🐾 Pet Owner
| Action | Access |
|---|---|
| Register & manage pets | ✅ |
| Book & cancel appointments | ✅ |
| List pets for adoption | ✅ |
| View doctor dashboard | ❌ |

### 🩺 Doctor
| Action | Access |
|---|---|
| View all registered pets | ✅ |
| Manage all appointments | ✅ |
| View vaccination records | ✅ |
| Register pets | ❌ |

---

## 👥 Team

| Name | Role |
|---|---|
| **Ushan Perera** | Group Member |
| **Sandu Senevirathna** | Group Member |
| **Dinujaya Thamara** | Group Member |
| **Nishen Fernando** | Group Member |

---

## 📄 License

This project was developed for academic purposes as part of the **BECS 31233 — Web and Internet Technologies** module.

---

<p align="center">Made with ❤️ by Team Happy Paws | 5th Semester | 2025</p>
